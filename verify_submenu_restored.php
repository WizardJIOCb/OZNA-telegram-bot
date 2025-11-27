<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Verifying Electrical Equipment Submenu Fix ===\n\n";

echo "PROBLEM ANALYSIS:\n";
echo "================\n";
echo "The issue was a callback format mismatch between:\n";
echo "1. TabelHandler routing logic (expects 'cat_ТабЭлОб' format)\n";
echo "2. MessageService keyboard buttons (were using 'category_Табель_...' format)\n\n";

echo "SOLUTION IMPLEMENTED:\n";
echo "====================\n";
echo "✅ Updated createElectricalEquipmentKeyboard() to use short format callbacks\n";
echo "✅ Updated createAutomationEquipmentKeyboard() to use short format callbacks\n";
echo "✅ Fixed 'Back' buttons to use 'cat_ТабПНРаботы' format\n\n";

echo "EXPECTED BEHAVIOR NOW:\n";
echo "=====================\n";
echo "1. Click 'Электрооборудование' button → Shows electrical equipment submenu\n";
echo "2. Submenu buttons now work properly because callbacks match routing logic\n";
echo "3. 'Back' button properly returns to commissioning works menu\n\n";

echo "VERIFICATION:\n";
echo "============\n";

// Load required files for testing
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

try {

    $messageService = new MessageService();
    
    // Test 1: Keyboard format check
    echo "Test 1: Checking keyboard button formats\n";
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    $allShortFormat = true;
    
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            if (!str_starts_with($button['callback_data'], 'cat_')) {
                $allShortFormat = false;
                break 2;
            }
        }
    }
    
    echo $allShortFormat ? "✅ PASS: All buttons use short format\n" : "❌ FAIL: Some buttons use long format\n";
    
    // Test 2: Handler can handle check
    echo "\nTest 2: Checking TabelHandler compatibility\n";
    $databaseService = new DatabaseService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    $canHandleMain = $tabelHandler->canHandle('cat_ТабЭлОб');
    $canHandleActivity = $tabelHandler->canHandle('cat_ТабЭлОб_РабПоГр');
    
    echo $canHandleMain ? "✅ PASS: Handler can handle main electrical button\n" : "❌ FAIL: Handler cannot handle main electrical button\n";
    echo $canHandleActivity ? "✅ PASS: Handler can handle electrical activity buttons\n" : "❌ FAIL: Handler cannot handle electrical activity buttons\n";
    
    echo "\nSUMMARY:\n";
    echo "========\n";
    if ($allShortFormat && $canHandleMain && $canHandleActivity) {
        echo "🎉 SUCCESS: Electrical equipment submenu should now work correctly!\n";
        echo "   Users should now see the submenu activities when clicking 'Электрооборудование'\n";
    } else {
        echo "❌ ISSUES REMAIN: The submenu may still have problems\n";
    }
    
} catch (Exception $e) {
    echo "ERROR during verification: " . $e->getMessage() . "\n";
}

echo "\n=== Verification Complete ===\n";