<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== АНАЛИЗ: ПЕТРОВ ===\n\n";

// Найдем Петрова в базе
$conn = $dbService->getConnection();
$query = "SELECT user_id, data FROM ozna_data WHERE data LIKE '%Петров%'";
$result = $conn->query($query);

$petrovUsers = [];
while ($row = $result->fetch_assoc()) {
    $data = json_decode($row['data'], true);
    $fio = $data['fio'] ?? '';
    if (stripos($fio, 'Петров') !== false) {
        $petrovUsers[] = [
            'user_id' => $row['user_id'],
            'fio' => $fio,
            'fn' => $data['functional_direction'] ?? 'Не указано'
        ];
    }
}

if (empty($petrovUsers)) {
    echo "❌ Петров не найден в базе\n";
    exit;
}

echo "Найдено пользователей с фамилией Петров: " . count($petrovUsers) . "\n\n";

foreach ($petrovUsers as $user) {
    echo "=" . str_repeat("=", 70) . "\n";
    echo "ФИО: {$user['fio']}\n";
    echo "User ID: {$user['user_id']}\n";
    echo "ФН: {$user['fn']}\n";
    echo str_repeat("-", 70) . "\n\n";
    
    $userId = $user['user_id'];
    
    // Получаем ВСЕ записи из табеля
    $query = "SELECT dt, message FROM ozna_tabel 
              WHERE user_id = ? 
              AND dt >= DATE_SUB(NOW(), INTERVAL 180 DAY)
              ORDER BY dt DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }
    
    echo "📊 Всего записей за последние 180 дней: " . count($entries) . "\n\n";
    
    if (empty($entries)) {
        echo "⚠️  НЕТ ЗАПИСЕЙ В ТАБЕЛЕ\n\n";
        continue;
    }
    
    // Показываем все записи с классификацией
    echo "📋 ВСЕ ЗАПИСИ В ТАБЕЛЕ (от новых к старым):\n";
    echo str_repeat("-", 70) . "\n";
    
    $reflection = new ReflectionClass($personnelService);
    $isWorkMethod = $reflection->getMethod('isWorkActivity');
    $isWorkMethod->setAccessible(true);
    
    $workActivities = [];
    
    foreach ($entries as $i => $entry) {
        $isWork = $isWorkMethod->invoke($personnelService, $entry['message']);
        $type = $isWork ? "🔧 РАБОЧАЯ" : "🏢 НЕРАБОЧАЯ";
        
        if ($isWork) {
            $workActivities[] = new DateTime($entry['dt']);
        }
        
        // Извлекаем краткое название активности
        preg_match('/запись в табель:\s*<b>(.+?)<\/b>/', $entry['message'], $matches);
        $activity = $matches[1] ?? 'Неизвестно';
        
        $date = new DateTime($entry['dt']);
        echo sprintf("%2d. %s [%s] %s\n", 
            $i + 1, 
            $date->format('d.m.Y H:i'), 
            $type,
            $activity
        );
    }
    
    echo "\n" . str_repeat("-", 70) . "\n";
    echo "📈 СТАТИСТИКА:\n";
    echo "   Всего записей: " . count($entries) . "\n";
    echo "   Рабочих активностей: " . count($workActivities) . "\n";
    echo "   Нерабочих активностей: " . (count($entries) - count($workActivities)) . "\n\n";
    
    // Анализ через приватные методы
    $hasWorkMethod = $reflection->getMethod('hasAnyWorkActivities');
    $hasWorkMethod->setAccessible(true);
    $hasWork = $hasWorkMethod->invoke($personnelService, $userId);
    
    $firstSessionMethod = $reflection->getMethod('findFirstEntryInCurrentSession');
    $firstSessionMethod->setAccessible(true);
    $sessionStart = $firstSessionMethod->invoke($personnelService, $userId);
    
    $freeTimeMethod = $reflection->getMethod('findLastContinuousWorkPeriod');
    $freeTimeMethod->setAccessible(true);
    $freeTimeStart = $freeTimeMethod->invoke($personnelService, $userId);
    
    echo "🔍 АНАЛИЗ ЛОГИКИ:\n";
    echo str_repeat("-", 70) . "\n";
    echo "1️⃣  Есть рабочие активности: " . ($hasWork ? "ДА ✅" : "НЕТ ❌") . "\n";
    echo "2️⃣  Активная рабочая сессия: " . ($sessionStart ? "ДА ✅ (с " . $sessionStart->format('d.m.Y') . ")" : "НЕТ ❌") . "\n";
    echo "3️⃣  Начало свободной вахты: " . ($freeTimeStart ? $freeTimeStart->format('d.m.Y') : "НЕ ОПРЕДЕЛЕНО") . "\n\n";
    
    // Детальный анализ последних рабочих активностей
    if (!empty($workActivities)) {
        echo "📍 ПОСЛЕДНИЕ РАБОЧИЕ АКТИВНОСТИ:\n";
        echo str_repeat("-", 70) . "\n";
        
        $now = new DateTime();
        
        for ($i = 0; $i < min(5, count($workActivities)); $i++) {
            $workDate = $workActivities[$i];
            $daysSince = $personnelService->getDaysBetween($workDate, $now);
            echo sprintf("   %d. %s (%d %s назад)\n", 
                $i + 1,
                $workDate->format('d.m.Y'),
                $daysSince,
                $personnelService->getDaysWord($daysSince)
            );
        }
        
        echo "\n";
        
        // Анализ промежутков между работами
        if (count($workActivities) > 1) {
            echo "⏱️  ПРОМЕЖУТКИ МЕЖДУ РАБОЧИМИ АКТИВНОСТЯМИ:\n";
            echo str_repeat("-", 70) . "\n";
            
            for ($i = 0; $i < min(5, count($workActivities) - 1); $i++) {
                $gap = $personnelService->getDaysBetween($workActivities[$i + 1], $workActivities[$i]);
                $status = $gap <= 3 ? "✅ Непрерывно" : "❌ РАЗРЫВ";
                echo sprintf("   %s → %s: %d %s [%s]\n",
                    $workActivities[$i + 1]->format('d.m.Y'),
                    $workActivities[$i]->format('d.m.Y'),
                    $gap,
                    $personnelService->getDaysWord($gap),
                    $status
                );
            }
            echo "\n";
        }
    }
    
    // Финальный вердикт
    echo "⚖️  ИТОГОВОЕ РЕШЕНИЕ:\n";
    echo str_repeat("=", 70) . "\n";
    
    if ($sessionStart) {
        echo "✅ Петров НА ОБЪЕКТЕ\n";
        echo "   Начало вахты: " . $sessionStart->format('d.m.Y') . "\n";
        echo "   Причина: Последняя работа ≤3 дней назад\n";
    } else if ($freeTimeStart) {
        echo "🏖️  Петров НА СВОБОДНОЙ ВАХТЕ\n";
        echo "   Начало свободной вахты: " . $freeTimeStart->format('d.m.Y') . "\n";
        
        if (!$hasWork) {
            echo "   Причина: Только нерабочие активности (офис)\n";
            echo "   Логика: Первая нерабочая активность в табеле\n";
        } else {
            echo "   Причина: Последняя работа >3 дней назад\n";
            echo "   Логика: След. день после последней рабочей активности\n";
        }
    } else {
        echo "⚪ Петров БЕЗ ДАННЫХ\n";
        echo "   Причина: Нет достаточной информации\n";
    }
    
    echo "\n\n";
}
