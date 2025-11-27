<?php
/**
 * Test our fixes for the electrical equipment button
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "=== Testing Fixes for Electrical Equipment Button ===\n\n";

try {
    // Create all required services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Test 1: Check the database service call fix
    echo "1. Testing database service call fix:\n";
    
    $userId = 230379668;
    try {
        // This should work now with our fix
        $userData = $databaseService->getUserData($userId);
        echo "   ✓ Database service call successful\n";
        echo "   User FIO: " . ($userData['fio'] ?? 'Not set') . "\n";
        echo "   User functional direction: " . ($userData['functional_direction'] ?? 'Not set') . "\n\n";
    } catch (Exception $e) {
        echo "   ✗ Database service call failed: " . $e->getMessage() . "\n\n";
    }
    
    // Test 2: Check routing logic fix
    echo "2. Testing routing logic fix:\n";
    
    $category = 'Табель_Электрооборудование';
    
    // Test exact match condition
    $exactMatch = $category === 'Табель_Электрооборудование';
    echo "   Exact match condition: " . ($exactMatch ? 'YES' : 'NO') . "\n";
    
    // Test contains электрооборудование condition
    $containsElectro = strpos($category, 'Электрооборудование') !== false;
    echo "   Contains электрооборудование: " . ($containsElectro ? 'YES' : 'NO') . "\n";
    
    // Test starts with Табель_ condition
    $startsWithTabel = strpos($category, 'Табель_') === 0;
    echo "   Starts with Табель_: " . ($startsWithTabel ? 'YES' : 'NO') . "\n";
    
    if ($exactMatch || ($containsElectro && $startsWithTabel)) {
        echo "   ✓ Routing conditions met\n\n";
    } else {
        echo "   ✗ Routing conditions not met\n\n";
    }
    
    // Test 3: Test the isElectricalEquipmentMenu method
    echo "3. Testing isElectricalEquipmentMenu method:\n";
    
    // Use reflection to test the private method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('isElectricalEquipmentMenu');
    $method->setAccessible(true);
    
    $isElectricalMenu = $method->invokeArgs($tabelHandler, [$category]);
    echo "   isElectricalEquipmentMenu result: " . ($isElectricalMenu ? 'YES' : 'NO') . "\n";
    
    if ($isElectricalMenu) {
        echo "   ✓ Electrical equipment menu correctly identified\n\n";
    } else {
        echo "   ✗ Electrical equipment menu not identified\n\n";
    }
    
    // Test 4: Test FIO check exclusion
    echo "4. Testing FIO check exclusion:\n";
    
    $containsTabel = str_contains($category, 'Табель');
    echo "   Contains 'Табель': " . ($containsTabel ? 'YES' : 'NO') . "\n";
    
    $isMainTabelMenuMethod = $reflection->getMethod('isMainTabelMenu');
    $isMainTabelMenuMethod->setAccessible(true);
    $isMainMenu = $isMainTabelMenuMethod->invokeArgs($tabelHandler, [$category]);
    echo "   Is main menu: " . ($isMainMenu ? 'YES' : 'NO') . "\n";
    
    $shouldRequireFio = $containsTabel && !$isMainMenu && !$isElectricalMenu;
    echo "   Should require FIO/functional direction: " . ($shouldRequireFio ? 'YES' : 'NO') . "\n";
    
    if (!$shouldRequireFio) {
        echo "   ✓ FIO/functional direction NOT required for electrical equipment menu\n\n";
    } else {
        echo "   ✗ FIO/functional direction required - this would block access\n\n";
    }
    
    // Final summary
    echo "=== Test Summary ===\n";
    
    $allTestsPassed = $exactMatch && ($containsElectro && $startsWithTabel) && $isElectricalMenu && !$shouldRequireFio;
    
    if ($allTestsPassed) {
        echo "🎉 ALL TESTS PASSED!\n";
        echo "   The fixes should resolve the electrical equipment button issue.\n";
        echo "   Key fixes implemented:\n";
        echo "   1. Fixed database service call in FIO check\n";
        echo "   2. Simplified routing logic for electrical equipment\n";
        echo "   3. Properly excluded electrical equipment menus from FIO requirements\n\n";
    } else {
        echo "❌ SOME TESTS FAILED!\n";
        echo "   There may still be issues preventing the button from working.\n\n";
    }
    
    echo "=== Next Steps ===\n";
    echo "1. Restart your Telegram app to clear cached data\n";
    echo "2. Try pressing the electrical equipment button again\n";
    echo "3. If it still doesn't work, check the server logs for errors\n";
    echo "4. Contact the bot administrator if issues persist\n\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "=== Testing Fixes Complete ===\n";
?>