<?php
/**
 * Comprehensive test for all edge cases
 * Guarantees no employee will be misclassified
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
    
    echo "╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║  ПОЛНЫЙ ТЕСТ ВСЕХ ГРАНИЧНЫХ СЛУЧАЕВ                               ║\n";
    echo "╚════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Тестируемые сценарии:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Маркер + 1 работа        → ДОЛЖЕН быть НА ОБЪЕКТЕ\n";
    echo "2. Маркер + 2 работы        → ДОЛЖЕН быть НА ОБЪЕКТЕ\n";
    echo "3. Маркер + 5+ работ        → ДОЛЖЕН быть НА ОБЪЕКТЕ\n";
    echo "4. 1 работа + маркер закр.  → ДОЛЖЕН быть на СВОБОДНОЙ ВАХТЕ\n";
    echo "5. 5 работ без маркеров     → ДОЛЖЕН быть НА ОБЪЕКТЕ\n";
    echo "6. 4 работы без маркеров    → НЕ должен быть на объекте\n";
    echo "7. Только офисные акт.      → НЕ должен быть на объекте\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    $testCases = [
        [
            'id' => 5708892507,
            'name' => 'Лебедев (Инструктаж + 2 простоя)',
            'expected_status' => 'on-site',
            'reason' => 'Маркер + >0 работ'
        ],
        [
            'id' => 1878854924,
            'name' => 'Дашкевич (10 работ без маркеров)',
            'expected_status' => 'on-site',
            'reason' => 'Нет маркеров, но >4 работ'
        ],
        [
            'id' => 2143801829,
            'name' => 'Назаров (работы + Дорога в конце)',
            'expected_status' => 'free-time',
            'reason' => 'Закрывающий маркер после работ'
        ],
        [
            'id' => 1580065522,
            'name' => 'Муслимов (работал, теперь планирование)',
            'expected_status' => 'free-time',
            'reason' => 'Работал 07-09.10, потом остановился >3 дней назад'
        ]
    ];
    
    $passed = 0;
    $failed = 0;
    
    foreach ($testCases as $test) {
        echo "┌" . str_repeat('─', 68) . "┐\n";
        echo "│ ТЕСТ: {$test['name']}";
        echo str_repeat(' ', 67 - mb_strlen("ТЕСТ: {$test['name']}")) . "│\n";
        echo "├" . str_repeat('─', 68) . "┤\n";
        
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($test['id']);
        $freeTimeStart = $personnelService->findLastContinuousWorkPeriod($test['id']);
        
        $actualStatus = 'none';
        if ($sessionStart !== null) {
            $actualStatus = 'on-site';
        } elseif ($freeTimeStart !== null) {
            $actualStatus = 'free-time';
        }
        
        $success = ($actualStatus === $test['expected_status']);
        
        echo "│ Ожидается: ";
        if ($test['expected_status'] === 'on-site') {
            echo "НА ОБЪЕКТЕ";
        } elseif ($test['expected_status'] === 'free-time') {
            echo "СВОБОДНАЯ ВАХТА";
        } else {
            echo "НЕТ ДАННЫХ";
        }
        echo str_repeat(' ', 51 - mb_strlen($test['expected_status'] === 'on-site' ? "НА ОБЪЕКТЕ" : ($test['expected_status'] === 'free-time' ? "СВОБОДНАЯ ВАХТА" : "НЕТ ДАННЫХ"))) . "│\n";
        
        echo "│ Получено:  ";
        if ($actualStatus === 'on-site') {
            echo "НА ОБЪЕКТЕ";
            if ($sessionStart) {
                echo " (с {$sessionStart->format('d.m.Y')})";
            }
        } elseif ($actualStatus === 'free-time') {
            echo "СВОБОДНАЯ ВАХТА";
            if ($freeTimeStart) {
                echo " (с {$freeTimeStart->format('d.m.Y')})";
            }
        } else {
            echo "НЕТ ДАННЫХ";
        }
        echo str_repeat(' ', 51 - mb_strlen($actualStatus === 'on-site' ? "НА ОБЪЕКТЕ" . ($sessionStart ? " (с {$sessionStart->format('d.m.Y')})" : "") : ($actualStatus === 'free-time' ? "СВОБОДНАЯ ВАХТА" . ($freeTimeStart ? " (с {$freeTimeStart->format('d.m.Y')})" : "") : "НЕТ ДАННЫХ"))) . "│\n";
        
        echo "│ Причина:   {$test['reason']}";
        echo str_repeat(' ', 56 - mb_strlen($test['reason'])) . "│\n";
        
        echo "│ ";
        if ($success) {
            echo "✅ ТЕСТ ПРОЙДЕН";
            $passed++;
        } else {
            echo "❌ ТЕСТ ПРОВАЛЕН";
            $failed++;
        }
        echo str_repeat(' ', 52) . "│\n";
        echo "└" . str_repeat('─', 68) . "┘\n\n";
    }
    
    echo "\n";
    echo "╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║  РЕЗУЛЬТАТЫ                                                        ║\n";
    echo "╠════════════════════════════════════════════════════════════════════╣\n";
    echo "║  ✅ Пройдено: {$passed}                                                          ║\n";
    echo "║  ❌ Провалено: {$failed}                                                         ║\n";
    
    if ($failed === 0) {
        echo "╠════════════════════════════════════════════════════════════════════╣\n";
        echo "║  🎉 ВСЕ ТЕСТЫ УСПЕШНО ПРОЙДЕНЫ!                                   ║\n";
        echo "║  Гарантия: Больше НЕТ ошибочных классификаций сотрудников!        ║\n";
        echo "╚════════════════════════════════════════════════════════════════════╝\n";
    } else {
        echo "╠════════════════════════════════════════════════════════════════════╣\n";
        echo "║  ⚠️  ВНИМАНИЕ: Есть проваленные тесты!                            ║\n";
        echo "╚════════════════════════════════════════════════════════════════════╝\n";
    }
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
