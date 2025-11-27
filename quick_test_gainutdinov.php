<?php

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
    
    echo "\n=== QUICK TEST: Гайнутдинов ===\n\n";
    
    $userId = 1429455763; // Гайнутдинов
    
    // Check status
    $sessionStart = $personnelService->findFirstEntryInCurrentSession($userId);
    
    if ($sessionStart !== null) {
        echo "❌ FAIL: Employee is ON-SITE (should be free-time)\n";
        exit(1);
    }
    
    echo "✅ Employee is NOT on-site\n";
    
    // Check freetime list
    $freetimeEmployees = $personnelService->getFreetimeEmployees('Пусконаладка');
    
    $found = false;
    foreach ($freetimeEmployees as $emp) {
        if ($emp['user_id'] == $userId) {
            $found = true;
            echo "✅ SUCCESS: Found in freetime list!\n";
            echo "   {$emp['icon']} {$emp['fio']}\n";
            echo "   Days: {$emp['days']}\n";
            echo "   Last activity: {$emp['last_work_date']}\n";
            break;
        }
    }
    
    if (!$found) {
        echo "❌ FAIL: NOT found in freetime list!\n";
        echo "\nTotal employees in list: " . count($freetimeEmployees) . "\n";
        exit(1);
    }
    
    echo "\n✅ FIX SUCCESSFUL!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
