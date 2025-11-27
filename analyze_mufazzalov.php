<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== АНАЛИЗ: МУФАЗЗАЛОВ ===\n\n";

// Найдем Муфаззалова
$conn = $dbService->getConnection();
$query = "SELECT user_id, data FROM ozna_data WHERE data LIKE '%Муфаззалов%'";
$result = $conn->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    $fio = $data['fio'] ?? '';
    if (stripos($fio, 'Муфаззалов') !== false) {
        $users[] = [
            'user_id' => $row['user_id'],
            'fio' => $fio,
            'fn' => $data['functional_direction'] ?? 'Не указано'
        ];
    }
}

if (empty($users)) {
    echo "❌ Муфаззалов не найден\n";
    exit;
}

foreach ($users as $user) {
    echo str_repeat("=", 70) . "\n";
    echo "ФИО: {$user['fio']}\n";
    echo "User ID: {$user['user_id']}\n";
    echo "ФН: {$user['fn']}\n";
    echo str_repeat("-", 70) . "\n\n";
    
    $userId = $user['user_id'];
    
    // Последние 20 записей
    $query = "SELECT dt, message FROM ozna_tabel 
              WHERE user_id = ? 
              ORDER BY dt DESC
              LIMIT 20";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    
    echo "📊 Последние 20 записей:\n\n";
    
    $reflection = new ReflectionClass($personnelService);
    $isWorkMethod = $reflection->getMethod('isWorkActivity');
    $isWorkMethod->setAccessible(true);
    
    $now = new DateTime();
    
    foreach ($entries as $i => $entry) {
        $isWork = $isWorkMethod->invoke($personnelService, $entry['message']);
        $type = $isWork ? "🔧 РАБОЧАЯ" : "🏢 НЕРАБОЧАЯ";
        
        preg_match('/запись в табель:\s*<b>(.+?)<\/b>/', $entry['message'], $matches);
        $activity = $matches[1] ?? 'Неизвестно';
        
        $date = new DateTime($entry['dt']);
        $daysAgo = $personnelService->getDaysBetween($date, $now);
        
        echo sprintf("%2d. %s (%d %s назад) [%s] %s\n", 
            $i + 1, 
            $date->format('d.m.Y H:i'),
            $daysAgo,
            $personnelService->getDaysWord($daysAgo),
            $type,
            $activity
        );
    }
    
    echo "\n" . str_repeat("-", 70) . "\n";
    
    // Анализ рабочих активностей
    echo "📍 АНАЛИЗ РАБОЧИХ АКТИВНОСТЕЙ:\n";
    echo str_repeat("-", 70) . "\n";
    
    $workActivities = [];
    foreach ($entries as $entry) {
        if ($isWorkMethod->invoke($personnelService, $entry['message'])) {
            $workActivities[] = new DateTime($entry['dt']);
        }
    }
    
    if (!empty($workActivities)) {
        echo "Всего рабочих активностей: " . count($workActivities) . "\n\n";
        echo "Последние 5 рабочих активностей:\n";
        for ($i = 0; $i < min(5, count($workActivities)); $i++) {
            $daysAgo = $personnelService->getDaysBetween($workActivities[$i], $now);
            echo sprintf("   %d. %s (%d %s назад)\n",
                $i + 1,
                $workActivities[$i]->format('d.m.Y H:i'),
                $daysAgo,
                $personnelService->getDaysWord($daysAgo)
            );
        }
        
        echo "\nПромежутки между работами:\n";
        for ($i = 0; $i < min(4, count($workActivities) - 1); $i++) {
            $gap = $personnelService->getDaysBetween($workActivities[$i + 1], $workActivities[$i]);
            $status = $gap <= 3 ? "✅ ≤3 дней" : "❌ >3 дней";
            echo sprintf("   %s → %s: %d %s [%s]\n",
                $workActivities[$i + 1]->format('d.m.Y'),
                $workActivities[$i]->format('d.m.Y'),
                $gap,
                $personnelService->getDaysWord($gap),
                $status
            );
        }
    } else {
        echo "❌ Нет рабочих активностей\n";
    }
    
    echo "\n" . str_repeat("-", 70) . "\n";
    
    // Проверяем статус через методы
    $firstSessionMethod = $reflection->getMethod('findFirstEntryInCurrentSession');
    $firstSessionMethod->setAccessible(true);
    $sessionStart = $firstSessionMethod->invoke($personnelService, $userId);
    
    $freeTimeMethod = $reflection->getMethod('findLastContinuousWorkPeriod');
    $freeTimeMethod->setAccessible(true);
    $freeTimeStart = $freeTimeMethod->invoke($personnelService, $userId);
    
    echo "🔍 РЕЗУЛЬТАТ ЛОГИКИ:\n";
    echo str_repeat("-", 70) . "\n";
    
    if ($sessionStart) {
        echo "✅ НА ОБЪЕКТЕ\n";
        echo "   Начало вахты: " . $sessionStart->format('d.m.Y') . "\n";
        $days = $personnelService->getDaysBetween($sessionStart, $now);
        echo "   На объекте: $days " . $personnelService->getDaysWord($days) . "\n\n";
        
        echo "❓ ПОЧЕМУ НА ОБЪЕКТЕ:\n";
        if (!empty($workActivities)) {
            $lastWork = $workActivities[0];
            $daysSince = $personnelService->getDaysBetween($lastWork, $now);
            echo "   Последняя РАБОЧАЯ активность: " . $lastWork->format('d.m.Y') . "\n";
            echo "   Прошло дней: $daysSince\n";
            echo "   Правило: последняя работа ≤3 дней назад → НА ОБЪЕКТЕ\n";
        }
    } else if ($freeTimeStart) {
        echo "🏖️  НА СВОБОДНОЙ ВАХТЕ\n";
        echo "   Начало свободной вахты: " . $freeTimeStart->format('d.m.Y') . "\n";
        $days = $personnelService->getDaysBetween($freeTimeStart, $now);
        echo "   На отдыхе: $days " . $personnelService->getDaysWord($days) . "\n";
    } else {
        echo "⚪ БЕЗ ДАННЫХ\n";
    }
    
    echo "\n" . str_repeat("=", 70) . "\n\n";
}
