<?php

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

// Database connection
$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

try {
    $databaseService = new DatabaseService($host, $username, $password, $dbname);
    $personnelService = new PersonnelService($databaseService);
    $conn = $databaseService->getConnection();

$employees = ['Миндикаев', 'Шангин', 'Гайнутдинов', 'Ночевной', 'Муртазин'];

echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
echo "║  АНАЛИЗ ОТСУТСТВУЮЩИХ СОТРУДНИКОВ В СПИСКЕ СВОБОДНОЙ ВАХТЫ         ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";

foreach ($employees as $name) {
    $query = "SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM user WHERE CONCAT(first_name, ' ', last_name) LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = '%' . $name . '%';
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "Сотрудник: {$user['full_name']} (ID: {$user['id']})\n";
        echo str_repeat('=', 80) . "\n";
        
        // Get last 15 entries to see full context
        $query2 = "SELECT dt, message FROM ozna_tabel WHERE user_id = ? ORDER BY dt DESC LIMIT 15";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param('i', $user['id']);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $entries = [];
        while ($row = $result2->fetch_assoc()) {
            $entries[] = $row;
        }
        
        echo "\nПоследние 15 записей:\n";
        echo str_repeat('-', 80) . "\n";
        foreach ($entries as $idx => $entry) {
            $date = new DateTime($entry['dt']);
            $now = new DateTime();
            $diff = $now->diff($date);
            $daysAgo = $diff->days;
            
            echo sprintf("[%2d] %s (%d дней назад) | %s\n", 
                $idx + 1, 
                $entry['dt'], 
                $daysAgo,
                $entry['message']
            );
        }
        
        // Get status
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($user['id']);
        
        echo "\n" . str_repeat('-', 80) . "\n";
        
        if ($sessionStart !== null) {
            echo "ТЕКУЩИЙ СТАТУС: 🟢 НА ОБЪЕКТЕ\n";
            echo "⚠️  ПРОБЛЕМА: Сотрудник показан как НА ОБЪЕКТЕ, но должен быть на свободной вахте!\n";
            echo "Анализируем почему...\n";
            
            // Check for closing markers
            $hasClosingMarker = false;
            foreach ($entries as $entry) {
                if (strpos($entry['message'], 'Дорога') !== false) {
                    $hasClosingMarker = true;
                    echo "✓ Найден закрывающий маркер 'Дорога' в {$entry['dt']}\n";
                    break;
                }
                if (strpos($entry['message'], 'Сдача авансового отчета') !== false) {
                    $hasClosingMarker = true;
                    echo "✓ Найден закрывающий маркер 'Сдача авансового отчета' в {$entry['dt']}\n";
                    break;
                }
            }
            
            if (!$hasClosingMarker) {
                echo "✗ НЕТ закрывающих маркеров в последних записях\n";
            }
        } else {
            echo "ТЕКУЩИЙ СТАТУС: 🔴 СВОБОДНАЯ ВАХТА\n";
            echo "✅ ПРАВИЛЬНО: Сотрудник на свободной вахте\n";
        }
        
        echo "\n";
    } else {
        echo "\n⚠️  Сотрудник '{$name}' не найден в базе данных!\n";
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "АНАЛИЗ ЗАВЕРШЕН\n";
echo str_repeat('=', 80) . "\n";

} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
