<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== АНАЛИЗ: ГИЛЯЗТДИНОВ ===\n\n";

// Найдем Гилязтдинова
$conn = $dbService->getConnection();
$query = "SELECT user_id, data FROM ozna_data WHERE data LIKE '%Гилязтдинов%'";
$result = $conn->query($query);

$users = [];
while ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    $fio = $data['fio'] ?? '';
    if (stripos($fio, 'Гилязтдинов') !== false) {
        $users[] = [
            'user_id' => $row['user_id'],
            'fio' => $fio,
            'fn' => $data['functional_direction'] ?? 'Не указано'
        ];
    }
}

if (empty($users)) {
    echo "❌ Гилязтдинов не найден\n";
    exit;
}

foreach ($users as $user) {
    echo str_repeat("=", 70) . "\n";
    echo "ФИО: {$user['fio']}\n";
    echo "User ID: {$user['user_id']}\n";
    echo "ФН: {$user['fn']}\n";
    echo str_repeat("-", 70) . "\n\n";
    
    $userId = $user['user_id'];
    
    // Последние 15 записей
    $query = "SELECT dt, message FROM ozna_tabel 
              WHERE user_id = ? 
              ORDER BY dt DESC
              LIMIT 15";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    
    echo "📊 Последние 15 записей:\n\n";
    
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
    
    // Проверяем статус
    $firstSessionMethod = $reflection->getMethod('findFirstEntryInCurrentSession');
    $firstSessionMethod->setAccessible(true);
    $sessionStart = $firstSessionMethod->invoke($personnelService, $userId);
    
    $freeTimeMethod = $reflection->getMethod('findLastContinuousWorkPeriod');
    $freeTimeMethod->setAccessible(true);
    $freeTimeStart = $freeTimeMethod->invoke($personnelService, $userId);
    
    echo "🔍 ТЕКУЩИЙ СТАТУС:\n";
    echo str_repeat("-", 70) . "\n";
    
    if ($sessionStart) {
        echo "✅ НА ОБЪЕКТЕ\n";
        echo "   Начало вахты: " . $sessionStart->format('d.m.Y') . "\n";
        $days = $personnelService->getDaysBetween($sessionStart, $now);
        echo "   На объекте: $days " . $personnelService->getDaysWord($days) . "\n\n";
        
        echo "❓ ПОЧЕМУ НА ОБЪЕКТЕ:\n";
        echo "   Последняя РАБОЧАЯ активность была ≤3 дней назад\n";
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
