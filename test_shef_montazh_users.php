<?php
/**
 * Test with Шеф-монтаж employees who should have real work activities
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
    
    echo "=== ТЕСТ СОТРУДНИКОВ ШЕФ-МОНТАЖ ===\n\n";
    
    // Test users from Шеф-монтаж with recent activity
    $testUsers = [
        ['id' => 1878854924, 'name' => 'Дашкевич Александр Игоревич'], // 16 entries
        ['id' => 1344675399, 'name' => 'Колесников Артём Владимирович'], // 6 entries
        ['id' => 2143801829, 'name' => 'Назаров Андрей Юрьевич'] // 8 entries
    ];
    
    foreach ($testUsers as $testUser) {
        echo str_repeat('=', 70) . "\n";
        echo "СОТРУДНИК: {$testUser['name']} (ID: {$testUser['id']})\n";
        echo str_repeat('=', 70) . "\n";
        
        // Get last 20 entries
        $query = "SELECT dt, message FROM ozna_tabel 
                  WHERE user_id = ? 
                  ORDER BY dt DESC
                  LIMIT 20";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $testUser['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "\nПОСЛЕДНИЕ 20 ЗАПИСЕЙ:\n";
        echo str_repeat('-', 70) . "\n";
        
        $workCount = 0;
        $markerCount = 0;
        
        while ($row = $result->fetch_assoc()) {
            $message = $row['message'];
            
            // Extract activity from message
            if (preg_match('/табель: <b>(.*?)<\/b>/', $message, $matches)) {
                $activity = $matches[1];
                
                // Categorize activity
                $marker = '';
                $isWork = true;
                
                if (strpos($activity, 'Дорога') !== false) {
                    $marker = ' [🚗 МАРКЕР: ДОРОГА]';
                    $markerCount++;
                    $isWork = false;
                } elseif (strpos($activity, 'Инструктаж') !== false) {
                    $marker = ' [👷 МАРКЕР: ИНСТРУКТАЖ]';
                    $markerCount++;
                    $isWork = false;
                } elseif (strpos($activity, 'Сдача авансового отчета') !== false) {
                    $marker = ' [💰 МАРКЕР: АВАНС]';
                    $markerCount++;
                    $isWork = false;
                } elseif (strpos($activity, 'Текущая деятельность') !== false) {
                    $marker = ' [💻 ОФИС/УДАЛЕНКА - НЕ РАБОТА]';
                    $isWork = false;
                } elseif (strpos($activity, 'Участие в ЗПИ') !== false || strpos($activity, 'Участие в ОПИ') !== false) {
                    $marker = ' [🏢 ОФИС/УДАЛЕНКА - НЕ РАБОТА]';
                    $isWork = false;
                } else {
                    $marker = ' [✅ РАБОЧАЯ АКТИВНОСТЬ]';
                    $workCount++;
                }
                
                echo "{$row['dt']}: {$activity}{$marker}\n";
            }
        }
        
        echo "\nСТАТИСТИКА:\n";
        echo "  Рабочих активностей: {$workCount}\n";
        echo "  Маркеров сессии: {$markerCount}\n";
        
        // Check session status
        echo "\n";
        echo str_repeat('-', 70) . "\n";
        echo "РЕЗУЛЬТАТ АНАЛИЗА НОВОЙ ЛОГИКИ:\n";
        echo str_repeat('-', 70) . "\n";
        
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($testUser['id']);
        if ($sessionStart !== null) {
            $now = new DateTime();
            $days = $personnelService->getDaysBetween($sessionStart, $now);
            echo "✅ СТАТУС: НА ОБЪЕКТЕ\n";
            echo "   Дата начала вахты: {$sessionStart->format('d.m.Y')}\n";
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
                echo "   (Недостаточно рабочих активностей для определения статуса)\n";
            }
        }
        
        echo "\n\n";
    }
    
    echo "=== ТЕСТ ЗАВЕРШЕН ===\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
