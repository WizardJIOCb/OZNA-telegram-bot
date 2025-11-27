<?php
/**
 * Check Lebedev status with current logic
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

$databaseService = new DatabaseService($host, $username, $password, $dbname);
$personnelService = new PersonnelService($databaseService);

$userId = 5708892507; // Lebedev

echo "=== АНАЛИЗ ЛЕБЕДЕВА (ТЕКУЩАЯ ЛОГИКА) ===\n\n";

echo "ДАННЫЕ:\n";
echo "  17.10 - Инструктаж [МАРКЕР НАЧАЛА]\n";
echo "  19.10 - Простой [РАБОТА 1]\n";
echo "  19.10 - Простой [РАБОТА 2]\n\n";

echo "ПРОВЕРКА:\n";
echo "  Маркер начала: ДА (Инструктаж)\n";
echo "  Работ после маркера: 2\n";
echo "  Требуется: >2 (минимум 3)\n";
echo "  Достаточно? НЕТ (2 < 3)\n\n";

$sessionStart = $personnelService->findFirstEntryInCurrentSession($userId);

echo str_repeat('=', 70) . "\n";
echo "РЕЗУЛЬТАТ СИСТЕМЫ:\n";
echo str_repeat('=', 70) . "\n";

if ($sessionStart !== null) {
    $now = new DateTime();
    $days = $personnelService->getDaysBetween($sessionStart, $now);
    echo "✅ НА ОБЪЕКТЕ с {$sessionStart->format('d.m.Y')} ({$days} дней)\n";
} else {
    echo "❌ НЕ НА ОБЪЕКТЕ\n";
    
    $freeTimeStart = $personnelService->findLastContinuousWorkPeriod($userId);
    if ($freeTimeStart !== null) {
        $now = new DateTime();
        $days = $personnelService->getDaysBetween($freeTimeStart, $now);
        echo "🏖️ СВОБОДНАЯ ВАХТА с {$freeTimeStart->format('d.m.Y')} ({$days} дней)\n";
    }
}

echo "\n";
echo str_repeat('=', 70) . "\n";
echo "ПРОБЛЕМА:\n";
echo str_repeat('=', 70) . "\n";
echo "• Сотрудник РЕАЛЬНО на объекте (Инструктаж + работа)\n";
echo "• Но система требует >2 работ (минимум 3)\n";
echo "• У него только 2 работы → не проходит проверку\n";
echo "• ОШИБОЧНО показан как 'свободная вахта'\n\n";

echo "РЕШЕНИЕ:\n";
echo "• Изменить порог с >2 на >1 (минимум 2)\n";
echo "• Или с >2 на >=2 (минимум 2)\n";
