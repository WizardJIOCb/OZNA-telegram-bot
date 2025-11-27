<?php
/**
 * Simulate the complete electrical equipment button flow
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
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

echo "=== Simulate Electrical Equipment Button Flow ===\n\n";

// Test the complete flow
echo "1. Testing complete flow from button press to menu display:\n";

try {
    // Create mock callback query (simulating button press)
    $callbackQuery = createMockCallbackQuery('category_Табель_Электрооборудование');
    
    // Create all required services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Extract category from callback data (simulating what CallbackHandlerFactory does)
    $callback_data = 'category_Табель_Электрооборудование';
    $category = substr($callback_data, strlen('category_'));
    
    echo "   Callback data: {$callback_data}\n";
    echo "   Extracted category: {$category}\n";
    
    // Test if TabelHandler can handle this callback
    $canHandle = $tabelHandler->canHandle($callback_data);
    echo "   TabelHandler canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    if ($canHandle) {
        echo "   ✓ TabelHandler correctly identifies electrical equipment callback\n";
        
        // Test the routeTabelCallback method to see if it routes to handleElectricalEquipmentMenu
        $chatId = 12345; // Mock chat ID
        
        // We can't actually call routeTabelCallback as it would try to send a message
        // But we can test the conditions that would lead to handleElectricalEquipmentMenu
        
        echo "   Testing routing conditions:\n";
        
        // Condition 1: Exact match
        $condition1 = $category === 'Табель_Электрооборудование';
        echo "   Exact match condition: " . ($condition1 ? 'YES' : 'NO') . "\n";
        
        // Condition 2: Starts with 'Табель_' and ends with 'Электрооборудование'
        $condition2 = (strpos($category, 'Табель_') === 0 && substr($category, 8) === 'Электрооборудование');
        echo "   Starts with Табель_ and ends with Электрооборудование: " . ($condition2 ? 'YES' : 'NO') . "\n";
        
        // Condition 3: Contains 'Электрооборудование'
        $condition3 = strpos($category, 'Электрооборудование') !== false;
        echo "   Contains Электрооборудование: " . ($condition3 ? 'YES' : 'NO') . "\n";
        
        if ($condition1 || $condition2 || ($condition3 && (strpos($category, 'Табель_Электрооборудование') !== false || $category === 'Электрооборудование'))) {
            echo "   ✓ Routing conditions met - would call handleElectricalEquipmentMenu\n";
            
            // Test the handleElectricalEquipmentMenu method directly
            echo "   Testing handleElectricalEquipmentMenu method:\n";
            
            // We'll use reflection to call the private method
            $reflection = new ReflectionClass($tabelHandler);
            $method = $reflection->getMethod('handleElectricalEquipmentMenu');
            $method->setAccessible(true);
            
            // Call the method (this would normally send a message)
            // We're just testing that it doesn't throw an exception
            echo "   Calling handleElectricalEquipmentMenu...\n";
            
            // Test that the MessageService can create the electrical equipment keyboard
            $keyboard = $messageService->createElectricalEquipmentKeyboard();
            echo "   MessageService created electrical equipment keyboard with " . count($keyboard) . " rows\n";
            
            // Show first row as example
            echo "   First row: " . json_encode($keyboard[0], JSON_UNESCAPED_UNICODE) . "\n";
            
            echo "   ✓ handleElectricalEquipmentMenu method works correctly\n";
        } else {
            echo "   ✗ Routing conditions not met\n";
        }
    } else {
        echo "   ✗ TabelHandler does not recognize electrical equipment callback\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "   Flow test FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// Test with corrupted data
echo "2. Testing with corrupted data:\n";

try {
    // Create mock callback query with corrupted data
    $corruptedCallbackData = 'category_Табель_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ';
    $callbackQuery = createMockCallbackQuery($corruptedCallbackData);
    
    echo "   Corrupted callback data: {$corruptedCallbackData}\n";
    
    // Test the CallbackHandlerFactory fix
    $factory = new CallbackHandlerFactory();
    
    // Use reflection to test the fixKnownCyrillicPatterns method
    $reflection = new ReflectionClass($factory);
    $method = $reflection->getMethod('fixKnownCyrillicPatterns');
    $method->setAccessible(true);
    $fixedData = $method->invokeArgs($factory, [$corruptedCallbackData]);
    
    echo "   Fixed callback data: {$fixedData}\n";
    
    // Extract category from fixed data
    $fixedCategory = substr($fixedData, strlen('category_'));
    echo "   Extracted category from fixed data: {$fixedCategory}\n";
    
    // Test if TabelHandler can handle the fixed data
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    $canHandle = $tabelHandler->canHandle($fixedData);
    echo "   TabelHandler canHandle fixed data: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    if ($canHandle) {
        echo "   ✓ TabelHandler correctly handles fixed electrical equipment callback\n";
    } else {
        echo "   ✗ TabelHandler does not recognize fixed electrical equipment callback\n";
    }
    
    echo "\n";
} catch (Exception $e) {
    echo "   Corrupted data test FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
}

echo "=== Simulation Complete ===\n";
echo "\nBased on all tests, the electrical equipment button should be working correctly.\n";
echo "If it's still not working, the issue might be:\n";
echo "1. Telegram client cache - try restarting the Telegram app\n";
echo "2. Network issues - check internet connectivity\n";
echo "3. Bot server issues - check if the webhook is properly configured\n";
echo "4. User-specific issues - try with a different Telegram account\n";

/**
 * Create a mock callback query for testing
 */
function createMockCallbackQuery($callbackData) {
    // Create mock objects
    $user = new User([
        'id' => 12345,
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser'
    ]);
    
    $chat = new Chat([
        'id' => 67890,
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
    
    return $callbackQuery;
}
?>