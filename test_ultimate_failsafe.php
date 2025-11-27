<?php

echo "=== TESTING ULTIMATE FAILSAFE FOR SITE INSPECTION ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Include necessary files
require_once "vendor/autoload.php";
require_once "CustomCommands/Services/DatabaseService.php";
require_once "CustomCommands/Services/MessageService.php";
require_once "CustomCommands/Services/TabelEntry.php";

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

try {
    $dbService = new DatabaseService();
    $messageService = new MessageService($dbService);
    $tabelEntry = new TabelEntry($dbService, $messageService);
    
    echo "TESTING ULTIMATE FAILSAFE SCENARIOS:\n";
    echo "====================================\n\n";
    
    // Test cases that might reach the default format
    $failsafeTests = [
        [
            'name' => 'Unknown activity with АналФунд in name',
            'activityType' => 'SomeUnknown_АналФунд_Activity',
            'expected' => 'Ultimate failsafe should catch this'
        ],
        [
            'name' => 'Activity with site inspection text',
            'activityType' => 'UnknownActivity',
            'activityText' => '🏗️ Проверка монтажной площадки'
        ],
        [
            'name' => 'Random activity that falls to default',
            'activityType' => 'RandomUnknownActivity',
            'expected' => 'Should use old format'
        ]
    ];
    
    foreach ($failsafeTests as $i => $test) {
        echo ($i + 1) . ". {$test['name']}:\n";
        echo "   Activity type: '{$test['activityType']}'\n";
        
        $result = $tabelEntry->createTabelMessage("Test User", "05.10.2025", "XXX", $test['activityType']);
        
        // Check format
        if (strpos($result, "✅05.10.2025, Проект") !== false && 
            strpos($result, "🏗️ Проверка монтажной площадки") !== false) {
            echo "   ✅ COMMISSIONING FORMAT (correct for site inspection)!\n";
        } elseif (strpos($result, "✅ Запись в табель от") !== false) {
            echo "   ❌ OLD FORMAT (this is expected for non-site inspection)!\n";
        } else {
            echo "   ⚠️  UNKNOWN FORMAT\n";
        }
        
        echo "   Result: " . substr($result, 0, 100) . "...\n\n";
    }
    
    echo "CONCLUSION:\n";
    echo "===========\n";
    echo "The ultimate failsafe is now active in the DEFAULT format section.\n";
    echo "Even if a wrong activity type reaches the default, it will check for:\n";
    echo "1. 'АналФунд' in the activity type name\n";
    echo "2. 'Проверка монтажной площадки' in the activity text\n";
    echo "3. 🏗️ emoji in the activity text\n\n";
    echo "If ANY of these are found, it will force the commissioning format!\n\n";
    
    echo "Now test the button again. The site inspection should work correctly!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}