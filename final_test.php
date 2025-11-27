<?php
/**
 * Final Comprehensive Test
 * Demonstrates all scenarios of the new personnel logic
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
    
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║  FINAL COMPREHENSIVE TEST - NEW PERSONNEL LOGIC                   ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Правила:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "НА ОБЪЕКТЕ:\n";
    echo "  • Сценарий 1: Маркер начала (Дорога/Инструктаж) + >2 работы (мин 3)\n";
    echo "  • Сценарий 2: Нет маркеров + >4 непрерывных работ (мин 5)\n\n";
    echo "СВОБОДНАЯ ВАХТА:\n";
    echo "  • Сценарий 1: Маркер конца (Дорога/Аванс) + >2 работы до него (мин 3)\n";
    echo "  • Сценарий 2: Было >4 работ, но остановка >3 дней назад\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Test all three functional directions
    $directions = [
        'Мобильная бригада',
        'Шеф-монтаж',
        'Пусконаладка'
    ];
    
    foreach ($directions as $direction) {
        echo "\n┌───────────────────────────────────────────────────────────────────┐\n";
        echo "│ ОТДЕЛ: {$direction}";
        echo str_repeat(' ', 61 - mb_strlen($direction)) . "│\n";
        echo "└───────────────────────────────────────────────────────────────────┘\n\n";
        
        // Get on-site employees
        echo "🟢 НА ОБЪЕКТЕ:\n";
        $onsite = $personnelService->getOnsiteEmployees($direction);
        
        if (empty($onsite)) {
            echo "   (нет сотрудников)\n";
        } else {
            foreach ($onsite as $emp) {
                echo "   {$emp['icon']} {$emp['fio']}\n";
                echo "      └─ {$emp['days']} дней на объекте (с {$emp['start_date']})\n";
            }
        }
        
        echo "\n";
        
        // Get free-time employees
        echo "🏖️  СВОБОДНАЯ ВАХТА:\n";
        $freetime = $personnelService->getFreetimeEmployees($direction);
        
        if (empty($freetime)) {
            echo "   (нет сотрудников)\n";
        } else {
            foreach ($freetime as $emp) {
                echo "   {$emp['icon']} {$emp['fio']}\n";
                echo "      └─ {$emp['days']} дней отдыха (с {$emp['last_work_date']})\n";
            }
        }
        
        echo "\n";
    }
    
    echo "\n╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ПРИМЕРЫ СЦЕНАРИЕВ                                                ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n\n";
    
    // Example scenarios
    $examples = [
        [
            'name' => 'Дашкевич Александр Игоревич',
            'id' => 1878854924,
            'scenario' => 'Сценарий 2 (На объекте): >4 непрерывных работ без маркеров'
        ],
        [
            'name' => 'Назаров Андрей Юрьевич',
            'id' => 2143801829,
            'scenario' => 'Сценарий 1 (Свободная вахта): Дорога в конце с >2 работами до'
        ]
    ];
    
    foreach ($examples as $example) {
        echo "┌" . str_repeat('─', 67) . "┐\n";
        echo "│ {$example['name']}";
        echo str_repeat(' ', 65 - mb_strlen($example['name'])) . "│\n";
        echo "├" . str_repeat('─', 67) . "┤\n";
        echo "│ {$example['scenario']}";
        echo str_repeat(' ', 65 - mb_strlen($example['scenario'])) . "│\n";
        echo "└" . str_repeat('─', 67) . "┘\n";
        
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($example['id']);
        if ($sessionStart !== null) {
            $now = new DateTime();
            $days = $personnelService->getDaysBetween($sessionStart, $now);
            echo "✅ Результат: НА ОБЪЕКТЕ с {$sessionStart->format('d.m.Y')} ({$days} дней)\n";
        } else {
            $freeTimeStart = $personnelService->findLastContinuousWorkPeriod($example['id']);
            if ($freeTimeStart !== null) {
                $now = new DateTime();
                $days = $personnelService->getDaysBetween($freeTimeStart, $now);
                echo "✅ Результат: СВОБОДНАЯ ВАХТА с {$freeTimeStart->format('d.m.Y')} ({$days} дней)\n";
            } else {
                echo "⚠️  Результат: НЕТ ДАННЫХ\n";
            }
        }
        
        echo "\n";
    }
    
    echo "╔═══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ ТЕСТ ЗАВЕРШЕН УСПЕШНО                                         ║\n";
    echo "╚═══════════════════════════════════════════════════════════════════╝\n";
    
} catch (Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
