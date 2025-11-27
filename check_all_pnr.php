<?php
/**
 * Check all PNR employees with new logic
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

echo "=== CHECKING ALL PNR EMPLOYEES ===\n\n";

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

// Get all PNR employees
$employees = $personnelService->getUsersByFunctionalDirection('Пусконаладка');

echo "Total PNR employees: " . count($employees) . "\n\n";

$onSite = [];
$freeTime = [];
$other = [];

foreach ($employees as $emp) {
    $userId = $emp['user_id'];
    $fio = $emp['fio'];
    
    $sessionStart = $personnelService->findFirstEntryInCurrentSession($userId);
    $lastWorkEnd = $personnelService->findLastContinuousWorkPeriod($userId);
    
    if ($sessionStart) {
        $now = new DateTime();
        $days = $personnelService->getDaysBetween($sessionStart, $now);
        $onSite[] = "$fio ($days days since " . $sessionStart->format('d.m.Y') . ")";
    } elseif ($lastWorkEnd) {
        $now = new DateTime();
        $days = $personnelService->getDaysBetween($lastWorkEnd, $now);
        $freeTime[] = "$fio ($days days since " . $lastWorkEnd->format('d.m.Y') . ")";
    } else {
        $other[] = $fio;
    }
}

echo "=== ON-SITE (" . count($onSite) . ") ===\n";
foreach ($onSite as $item) {
    echo "  ✓ $item\n";
}

echo "\n=== FREE TIME (" . count($freeTime) . ") ===\n";
foreach ($freeTime as $item) {
    echo "  🏖️ $item\n";
}

echo "\n=== OTHER/NO SESSION DATA (" . count($other) . ") ===\n";
foreach ($other as $item) {
    echo "  ⚪ $item\n";
}
