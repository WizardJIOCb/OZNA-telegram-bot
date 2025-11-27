<?php
/**
 * Test NEW SIMPLIFIED personnel logic
 * 
 * ON-SITE: Any work activity with last work ≤3 days ago
 * FREE-TIME: Last work >3 days ago, starts NEXT DAY after last work
 * Non-work activities DON'T interrupt free-time
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

try {
    $databaseService = new DatabaseService($host, $username, $password, $dbname);
    $personnelService = new PersonnelService($databaseService);
    $conn = $databaseService->getConnection();
    
    echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║  ТЕСТ НОВОЙ УПРОЩЕННОЙ ЛОГИКИ                                      ║\n";
    echo "╚════════════════════════════════════════════════════════════════════╝\n\n";
    
    // Test cases
    $testCases = [
        [
            'name' => 'Гайнутдинов',
            'id' => 1429455763,
            'expected' => 'FREE-TIME',
            'reason' => 'Только офисные активности (ЗПИ), нет рабочих записей'
        ],
        [
            'name' => 'Дашкевич',
            'id' => 1878854924,
            'expected' => 'ON-SITE',
            'reason' => 'Есть рабочие активности ≤3 дней назад'
        ],
        [
            'name' => 'Назаров',
            'id' => 2143801829,
            'expected' => 'FREE-TIME',
            'reason' => 'Работал, но последняя работа >3 дней назад'
        ]
    ];
    
    foreach ($testCases as $test) {
        echo str_repeat('=', 70) . "\n";
        echo "СОТРУДНИК: {$test['name']} (ID: {$test['id']})\n";
        echo "ОЖИДАЕТСЯ: {$test['expected']}\n";
        echo "ПРИЧИНА: {$test['reason']}\n";
        echo str_repeat('=', 70) . "\n";
        
        // Get last 10 entries
        $query = "SELECT dt, message FROM ozna_tabel WHERE user_id = ? ORDER BY dt DESC LIMIT 10";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $test['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "\nПоследние записи:\n";
        $workCount = 0;
        $lastWorkDate = null;
        
        while ($row = $result->fetch_assoc()) {
            $message = $row['message'];
            $dt = $row['dt'];
            
            // Check if work
            $isWork = true;
            if (strpos($message, 'Дорога') !== false ||
                strpos($message, 'Инструктаж') !== false ||
                strpos($message, 'Сдача авансового отчета') !== false ||
                strpos($message, 'Текущая деятельность') !== false ||
                strpos($message, 'Участие в ЗПИ') !== false ||
                strpos($message, 'Участие в ОПИ') !== false ||
                strpos($message, 'Разработка программы работ') !== false ||
                strpos($message, 'Разработка графика работ') !== false) {
                $isWork = false;
            }
            
            $marker = $isWork ? '🏗️ РАБОТА' : '🏢 НЕ-РАБОТА';
            echo "  {$dt}: {$marker}\n";
            
            if ($isWork) {
                $workCount++;
                if ($lastWorkDate === null) {
                    $lastWorkDate = new DateTime($dt);
                }
            }
        }
        
        echo "\nАНАЛИЗ:\n";
        echo "  Рабочих записей: {$workCount}\n";
        
        if ($lastWorkDate) {
            $now = new DateTime();
            $diff = $now->diff($lastWorkDate);
            echo "  Последняя работа: {$lastWorkDate->format('d.m.Y')} ({$diff->days} дней назад)\n";
        } else {
            echo "  Последняя работа: НЕТ\n";
        }
        
        // Check status
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($test['id']);
        
        if ($sessionStart !== null) {
            $days = $personnelService->getDaysBetween($sessionStart, new DateTime());
            echo "\n✅ РЕЗУЛЬТАТ: НА ОБЪЕКТЕ\n";
            echo "   Начало: {$sessionStart->format('d.m.Y')}\n";
            echo "   Дней на объекте: {$days}\n";
            $actualStatus = 'ON-SITE';
        } else {
            $freeTimeStart = $personnelService->findLastContinuousWorkPeriod($test['id']);
            
            if ($freeTimeStart !== null) {
                $days = $personnelService->getDaysBetween($freeTimeStart, new DateTime());
                echo "\n🏖️ РЕЗУЛЬТАТ: СВОБОДНАЯ ВАХТА\n";
                echo "   Начало свободной вахты: {$freeTimeStart->format('d.m.Y')}\n";
                echo "   Дней отдыха: {$days}\n";
                $actualStatus = 'FREE-TIME';
            } else {
                echo "\n⚪ РЕЗУЛЬТАТ: НЕТ ДАННЫХ\n";
                $actualStatus = 'NO-DATA';
            }
        }
        
        // Compare
        if ($actualStatus === $test['expected']) {
            echo "\n✅ ТЕСТ ПРОЙДЕН!\n";
        } else {
            echo "\n❌ ТЕСТ ПРОВАЛЕН! Ожидалось {$test['expected']}, получено {$actualStatus}\n";
        }
        
        echo "\n";
    }
    
    echo "\n╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║  ТЕСТ ЗАВЕРШЕН                                                     ║\n";
    echo "╚════════════════════════════════════════════════════════════════════╝\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
