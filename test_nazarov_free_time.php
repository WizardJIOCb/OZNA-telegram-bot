<?php
/**
 * Test if Назаров should be on free-time
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
    
    $userId = 2143801829; // Назаров
    
    echo "=== ТЕСТ: НАЗАРОВ - ДОЛЖЕН БЫТЬ НА СВОБОДНОЙ ВАХТЕ ===\n\n";
    
    // Check on-site status
    echo "Проверка статуса 'На объекте':\n";
    $sessionStart = $personnelService->findFirstEntryInCurrentSession($userId);
    if ($sessionStart !== null) {
        echo "❌ НА ОБЪЕКТЕ (дата начала: {$sessionStart->format('d.m.Y')})\n";
        echo "   Это ОШИБКА! Последняя запись - 'Дорога', сессия должна быть закрыта!\n";
    } else {
        echo "✅ НЕ на объекте\n";
    }
    
    echo "\n";
    
    // Check free-time status
    echo "Проверка статуса 'Свободная вахта':\n";
    $freeTimeStart = $personnelService->findLastContinuousWorkPeriod($userId);
    if ($freeTimeStart !== null) {
        $now = new DateTime();
        $days = $personnelService->getDaysBetween($freeTimeStart, $now);
        echo "✅ НА СВОБОДНОЙ ВАХТЕ (с {$freeTimeStart->format('d.m.Y')}, {$days} дней)\n";
        echo "   Ожидаемая дата: 19.10.2025 (дата последней 'Дорога')\n";
    } else {
        echo "❌ НЕ на свободной вахте\n";
        echo "   Это ОШИБКА! Сессия закрыта маркером 'Дорога' с >2 активностями до него!\n";
    }
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
}
