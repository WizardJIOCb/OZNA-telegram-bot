<?php
/**
 * Quick test for specific users mentioned in conversation
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
    $conn = $databaseService->getConnection();
    
    echo "=== ТЕСТ КОНКРЕТНЫХ СОТРУДНИКОВ ===\n\n";
    echo "Новая логика:\n";
    echo "- Сессия с маркером: >2 активности (мин. 3) ПОСЛЕ маркера\n";
    echo "- Без маркера: >4 непрерывных активности (мин. 5)\n";
    echo "- Закрытие: >2 активности (мин. 3) ДО маркера\n";
    echo "- Остановка: было >4, прошло >3 дней\n\n";
    
    // Test users (correct IDs)
    $testUsers = [
        ['id' => 1429455763, 'name' => 'Гайнутдинов Роберт Рамильевич'],
        ['id' => 5396921781, 'name' => 'Шангин Вячеслав Юрьевич'],
        ['id' => 693548959, 'name' => 'Мамаев Дмитрий Анатольевич'],
        ['id' => 5850784048, 'name' => 'Ахунов Айдар Ришатович']
    ];
    
    foreach ($testUsers as $testUser) {
        echo str_repeat('=', 60) . "\n";
        echo "СОТРУДНИК: {$testUser['name']} (ID: {$testUser['id']})\n";
        echo str_repeat('=', 60) . "\n";
        
        // Get last 15 entries
        $query = "SELECT dt, message FROM ozna_tabel 
                  WHERE user_id = ? 
                  ORDER BY dt DESC
                  LIMIT 15";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $testUser['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        echo "\nПОСЛЕДНИЕ 15 ЗАПИСЕЙ:\n";
        echo str_repeat('-', 60) . "\n";
        
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
            $message = $row['message'];
            
            // Extract activity from message
            if (preg_match('/табель: <b>(.*?)<\/b>/', $message, $matches)) {
                $activity = $matches[1];
                
                // Highlight markers
                $marker = '';
                if (strpos($activity, 'Дорога') !== false) {
                    $marker = ' [МАРКЕР: ДОРОГА]';
                } elseif (strpos($activity, 'Инструктаж') !== false) {
                    $marker = ' [МАРКЕР: ИНСТРУКТАЖ]';
                } elseif (strpos($activity, 'Сдача авансового отчета') !== false) {
                    $marker = ' [МАРКЕР: АВАНС]';
                } elseif (strpos($activity, 'Текущая деятельность') !== false) {
                    $marker = ' [ОФИС/УДАЛЕНКА]';
                } elseif (strpos($activity, 'Участие в ЗПИ') !== false || strpos($activity, 'Участие в ОПИ') !== false) {
                    $marker = ' [ОФИС/УДАЛЕНКА]';
                }
                
                echo "{$row['dt']}: {$activity}{$marker}\n";
            }
        }
        
        // Check session status
        echo "\n";
        echo str_repeat('-', 60) . "\n";
        echo "РЕЗУЛЬТАТ АНАЛИЗА:\n";
        echo str_repeat('-', 60) . "\n";
        
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($testUser['id']);
        if ($sessionStart !== null) {
            $now = new DateTime();
            $days = $personnelService->getDaysBetween($sessionStart, $now);
            echo "✅ СТАТУС: НА ОБЪЕКТЕ\n";
            echo "   Дата начала: {$sessionStart->format('d.m.Y')}\n";
            echo "   Дней на объекте: {$days}\n";
        } else {
            $freeTimeStart = $personnelService->findLastContinuousWorkPeriod($testUser['id']);
            if ($freeTimeStart !== null) {
                $now = new DateTime();
                $days = $personnelService->getDaysBetween($freeTimeStart, $now);
                echo "🏖️ СТАТУС: СВОБОДНАЯ ВАХТА\n";
                echo "   Дата начала отдыха: {$freeTimeStart->format('d.m.Y')}\n";
                echo "   Дней отдыха: {$days}\n";
            } else {
                echo "❌ СТАТУС: НЕТ ДАННЫХ\n";
                echo "   (Недостаточно активностей для определения статуса)\n";
            }
        }
        
        echo "\n\n";
    }
    
    echo "=== ТЕСТ ЗАВЕРШЕН ===\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
