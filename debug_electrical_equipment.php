<?php
/**
 * Comprehensive debugging script for electrical equipment button issue
 * This script will test all aspects of the electrical equipment callback processing
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
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

echo "=== Electrical Equipment Button Debug Script ===\n\n";

// Test 1: Check callback data
echo "1. Testing callback data:\n";
$callback_data = 'category_Табель_Электрооборудование';
echo "   Callback data: {$callback_data}\n";
echo "   Length: " . strlen($callback_data) . " bytes\n";
echo "   UTF-8 valid: " . (mb_check_encoding($callback_data, 'UTF-8') ? 'YES' : 'NO') . "\n\n";

// Test 2: Test MessageService keyboard creation
echo "2. Testing MessageService keyboard creation:\n";
try {
    $messageService = new MessageService();
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "   Keyboard creation: SUCCESS\n";
    echo "   Number of rows: " . count($keyboard) . "\n";
    echo "   First row: " . json_encode($keyboard[0], JSON_UNESCAPED_UNICODE) . "\n\n";
} catch (Exception $e) {
    echo "   Keyboard creation: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Test TabelHandler canHandle method
echo "3. Testing TabelHandler canHandle method:\n";
try {
    // Initialize services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Test various callback data
    $test_cases = [
        'category_Табель_Электрооборудование',
        'category_Табель_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ',
        'category_Табель_ПусконаладочныеРаботы',
        'category_Табель_Прочее'
    ];
    
    foreach ($test_cases as $test_case) {
        $canHandle = $tabelHandler->canHandle($test_case);
        echo "   Callback: {$test_case}\n";
        echo "   Can handle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   TabelHandler canHandle test: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Test AdminHandler canHandle method
echo "4. Testing AdminHandler canHandle method:\n";
try {
    // Create AdminHandler with proper parameters
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    $adminHandler = new AdminHandler($adminService, $messageService);
    
    // Test electrical equipment callback with AdminHandler
    $callback_data = 'category_Табель_Электрооборудование';
    $canHandle = $adminHandler->canHandle($callback_data);
    echo "   Callback: {$callback_data}\n";
    echo "   AdminHandler can handle: " . ($canHandle ? 'YES' : 'NO') . "\n\n";
} catch (Exception $e) {
    echo "   AdminHandler canHandle test: FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Test 5: Manual verification of key files
echo "5. Manual verification of key files:\n";

// Check TabelHandler.php
$tabelHandlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (file_exists($tabelHandlerPath)) {
    $content = file_get_contents($tabelHandlerPath);
    echo "   TabelHandler.php exists: YES\n";
    
    // Check for special electrical equipment handling
    $hasSpecialHandling = strpos($content, 'category_Табель_Электрооборудование') !== false;
    echo "   Has electrical equipment special handling: " . ($hasSpecialHandling ? 'YES' : 'NO') . "\n";
    
    // Check for handleElectricalEquipmentMenu method
    $hasMethod = strpos($content, 'handleElectricalEquipmentMenu') !== false;
    echo "   Has handleElectricalEquipmentMenu method: " . ($hasMethod ? 'YES' : 'NO') . "\n\n";
} else {
    echo "   TabelHandler.php exists: NO\n\n";
}

// Check MessageService.php
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServicePath)) {
    $content = file_get_contents($messageServicePath);
    echo "   MessageService.php exists: YES\n";
    
    // Check for electrical equipment keyboard
    $hasKeyboard = strpos($content, 'createElectricalEquipmentKeyboard') !== false;
    echo "   Has createElectricalEquipmentKeyboard method: " . ($hasKeyboard ? 'YES' : 'NO') . "\n";
    
    // Check for electrical equipment button in commissioning works
    $hasButton = strpos($content, 'Электрооборудование') !== false && strpos($content, 'callback_data') !== false;
    echo "   Has electrical equipment button: " . ($hasButton ? 'YES' : 'NO') . "\n\n";
} else {
    echo "   MessageService.php exists: NO\n\n";
}

echo "=== Debug Script Completed ===\n";
?>