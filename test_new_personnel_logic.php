<?php
/**
 * Test script for new personnel logic
 * Tests the updated logic with >2 and >4 activity thresholds
 */

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
    
    echo "=== ТЕСТ НОВОЙ ЛОГИКИ ПЕРСОНАЛА ===\n\n";
    echo "Правила:\n";
    echo "- Сессия с маркером (Дорога/Инструктаж): нужно >2 активностей (мин. 3) ПОСЛЕ маркера\n";
    echo "- Сессия без маркера: нужно >4 непрерывных активностей (мин. 5)\n";
    echo "- Закрытие сессии (Дорога/Аванс): нужно >2 активностей (мин. 3) ДО маркера\n";
    echo "- Остановка работы: было >4 активностей, но прошло >3 дней\n\n";
    
    // Test each functional direction
    $directions = ['Мобильная бригада', 'Шеф-монтаж', 'Пусконаладка'];
    
    foreach ($directions as $direction) {
        echo "========================================\n";
        echo "ОТДЕЛ: {$direction}\n";
        echo "========================================\n\n";
        
        // Get on-site employees
        echo "--- НА ОБЪЕКТЕ ---\n";
        $onsite = $personnelService->getOnsiteEmployees($direction);
        
        if (empty($onsite)) {
            echo "Нет сотрудников на объекте\n\n";
        } else {
            foreach ($onsite as $emp) {
                echo "{$emp['icon']} {$emp['fio']} - {$emp['days']} дней (с {$emp['start_date']})\n";
            }
            echo "\n";
        }
        
        // Get free-time employees
        echo "--- СВОБОДНАЯ ВАХТА ---\n";
        $freetime = $personnelService->getFreetimeEmployees($direction);
        
        if (empty($freetime)) {
            echo "Нет сотрудников на свободной вахте\n\n";
        } else {
            foreach ($freetime as $emp) {
                echo "{$emp['icon']} {$emp['fio']} - {$emp['days']} дней отдыха (с {$emp['last_work_date']})\n";
            }
            echo "\n";
        }
    }
    
    echo "\n=== ДЕТАЛЬНЫЙ АНАЛИЗ НЕСКОЛЬКИХ СОТРУДНИКОВ ===\n\n";
    
    // Test specific employees mentioned in conversation
    $testUsers = [
        ['id' => 948045467, 'name' => 'Гайнутдинов'],
        ['id' => 1312335508, 'name' => 'Шангин'],
        ['id' => 864068917, 'name' => 'Мамаев'],
        ['id' => 1059435519, 'name' => 'Ахунов']
    ];
    
    $conn = $databaseService->getConnection();
    
    foreach ($testUsers as $testUser) {
        echo "--- {$testUser['name']} (ID: {$testUser['id']}) ---\n";
        
        // Get last 10 entries
        $query = "SELECT dt, message FROM ozna_tabel 
                  WHERE user_id = ? 
                  ORDER BY dt DESC
                  LIMIT 10";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $testUser['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "Последние 10 записей:\n";
        while ($row = $result->fetch_assoc()) {
            $message = $row['message'];
            // Extract activity from message
            if (preg_match('/табель: <b>(.*?)<\/b>/', $message, $matches)) {
                $activity = $matches[1];
                echo "  {$row['dt']}: {$activity}\n";
            }
        }
        
        // Check status
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($testUser['id']);
        if ($sessionStart !== null) {
            echo "СТАТУС: На объекте с {$sessionStart->format('d.m.Y')}\n";
        } else {
            $freeTimeStart = $personnelService->findLastContinuousWorkPeriod($testUser['id']);
            if ($freeTimeStart !== null) {
                echo "СТАТУС: Свободная вахта с {$freeTimeStart->format('d.m.Y')}\n";
            } else {
                echo "СТАТУС: Нет данных\n";
            }
        }
        
        echo "\n";
    }
    
    echo "=== ТЕСТ ЗАВЕРШЕН ===\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
