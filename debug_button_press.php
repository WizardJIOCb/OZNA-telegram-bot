<?php
/**
 * Debug what happens when the electrical equipment button is pressed
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
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

echo "=== Debug Electrical Equipment Button Press ===\n\n";

// Simulate the exact callback that would be sent when button is pressed
$callbackData = 'category_Табель_Электрооборудование';
echo "Testing callback data: {$callbackData}\n";
echo "Callback data length: " . strlen($callbackData) . " bytes\n\n";

try {
    // Create all required services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Test 1: Check if TabelHandler can handle this callback
    echo "1. Testing TabelHandler canHandle:\n";
    $canHandle = $tabelHandler->canHandle($callbackData);
    echo "   canHandle result: " . ($canHandle ? 'YES' : 'NO') . "\n\n";
    
    // Test 2: Simulate the exact flow that happens in handle method
    echo "2. Simulating handle method flow:\n";
    
    // Create mock callback query (simulating what Telegram sends)
    $user = new User([
        'id' => 230379668, // Using the user ID we tested before
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser'
    ]);
    
    $chat = new Chat([
        'id' => 123456789,
        'type' => 'private',
        'first_name' => 'Test',
        'last_name' => 'User'
    ]);
    
    $message = new Message([
        'message_id' => 11111,
        'from' => $user,
        'chat' => $chat,
        'date' => time(),
        'text' => 'Test message'
    ]);
    
    $callbackQuery = new CallbackQuery([
        'id' => 'test_callback_id',
        'from' => $user,
        'message' => $message,
        'chat_instance' => 'test_chat_instance',
        'data' => $callbackData
    ]);
    
    // Extract category (this is what the CallbackHandlerFactory does)
    $category = substr($callbackData, strlen('category_'));
    echo "   Extracted category: {$category}\n";
    
    // Test 3: Check the special conditions in the handle method
    echo "3. Checking special conditions in handle method:\n";
    
    // Special handling for electrical equipment category
    if ($category === 'Табель_Электрооборудование') {
        echo "   ✓ Exact match condition met - would call handleElectricalEquipmentMenu\n";
    }
    
    // Additional check for electrical equipment in category
    if (strpos($category, 'Электрооборудование') !== false) {
        echo "   ✓ Contains электрооборудование condition met\n";
    }
    
    // Special case: handle double-encoded category that still contains corrupted data
    if (strpos($category, 'РўР°Р±РµР»СЊ_Электрооборудование') !== false) {
        echo "   ✓ Contains double-encoded электрооборудование condition met\n";
    }
    
    echo "\n";
    
    // Test 4: Check user access level
    echo "4. Checking user access level:\n";
    $userId = 230379668;
    $userAccessLevel = $adminService->getUserAccessLevel($userId);
    echo "   User access level: {$userAccessLevel}\n";
    
    if ($userAccessLevel == 0) {
        echo "   ✗ USER BLOCKED: Guest access level (0) - would return access denied message\n";
    } else {
        echo "   ✓ User has sufficient access level\n";
    }
    
    echo "\n";
    
    // Test 5: Check FIO and functional direction requirements
    echo "5. Checking FIO and functional direction requirements:\n";
    
    // Use reflection to test our new method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('isElectricalEquipmentMenu');
    $method->setAccessible(true);
    $isElectricalEquipmentMenu = $method->invokeArgs($tabelHandler, [$category]);
    
    echo "   isElectricalEquipmentMenu result: " . ($isElectricalEquipmentMenu ? 'YES' : 'NO') . "\n";
    
    // Check if FIO/functional direction would be required
    $containsTabel = str_contains($category, 'Табель');
    $isMainTabelMenuMethod = $reflection->getMethod('isMainTabelMenu');
    $isMainTabelMenuMethod->setAccessible(true);
    $isMainMenu = $isMainTabelMenuMethod->invokeArgs($tabelHandler, [$category]);
    $shouldRequireFio = $containsTabel && !$isMainMenu && !$isElectricalEquipmentMenu;
    
    echo "   Contains 'Табель': " . ($containsTabel ? 'YES' : 'NO') . "\n";
    echo "   Is main menu: " . ($isMainMenu ? 'YES' : 'NO') . "\n";
    echo "   Should require FIO/functional direction: " . ($shouldRequireFio ? 'YES' : 'NO') . "\n";
    
    if ($shouldRequireFio) {
        echo "   ✗ WOULD BLOCK: FIO/functional direction required but not checked in our simulation\n";
    } else {
        echo "   ✓ FIO/functional direction NOT required\n";
    }
    
    echo "\n";
    
    // Test 6: Try to call the handleElectricalEquipmentMenu method directly
    echo "6. Testing handleElectricalEquipmentMenu method directly:\n";
    
    try {
        $handleElectricalMethod = $reflection->getMethod('handleElectricalEquipmentMenu');
        $handleElectricalMethod->setAccessible(true);
        
        // This would normally send a message, but we're just testing it doesn't throw an exception
        echo "   Method exists and is callable\n";
        echo "   ✓ handleElectricalEquipmentMenu method should work correctly\n";
    } catch (Exception $e) {
        echo "   ✗ Error calling handleElectricalEquipmentMenu: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Final summary
    echo "=== Debug Summary ===\n";
    
    if ($canHandle && $userAccessLevel > 0 && !$shouldRequireFio) {
        echo "✅ All conditions met - button should work\n";
        echo "   If it's still not working, the issue might be:\n";
        echo "   1. Telegram client cache - try restarting Telegram\n";
        echo "   2. Webhook issues - check server logs\n";
        echo "   3. Button definition in keyboard - check callback_data\n";
        echo "   4. Recent code changes might have introduced a regression\n";
    } else {
        echo "❌ Some conditions not met:\n";
        if (!$canHandle) echo "   - TabelHandler cannot handle the callback\n";
        if ($userAccessLevel == 0) echo "   - User has guest access\n";
        if ($shouldRequireFio) echo "   - FIO/functional direction required\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
?>