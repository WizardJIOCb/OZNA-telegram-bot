<?php
/**
 * Comprehensive debug test for electrical equipment button
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
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

echo "=== Comprehensive Debug Test for Electrical Equipment Button ===\n\n";

// Test the exact callback that would be sent when button is pressed
$callbackData = 'category_Табель_Электрооборудование';
echo "Testing callback data: {$callbackData}\n";
echo "Callback data length: " . strlen($callbackData) . " bytes\n";
echo "Callback data hex: " . bin2hex($callbackData) . "\n\n";

try {
    echo "=== Step 1: Testing CallbackHandlerFactory ===\n";
    
    // Create all required services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create all handlers with their correct constructors
    $handlers = [
        new AdminHandler($adminService, $messageService),
        new MainMenuHandler($messageService),
        new AccessLevelHandler($adminService, $messageService),
        new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService),
        new ProfileHandler($messageService),
        new FunctionalDirectionHandler($messageService),
        new HelpHandler($messageService)
    ];
    
    // Sort handlers by priority (highest first)
    usort($handlers, function($a, $b) {
        return $b->getPriority() - $a->getPriority();
    });
    
    echo "Handler priorities:\n";
    foreach ($handlers as $handler) {
        echo "  " . get_class($handler) . ": " . $handler->getPriority() . "\n";
    }
    echo "\n";
    
    // Test each handler's canHandle method
    echo "Testing each handler's canHandle method:\n";
    foreach ($handlers as $handler) {
        $canHandle = $handler->canHandle($callbackData);
        echo "  " . get_class($handler) . ": " . ($canHandle ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
    
    echo "=== Step 2: Testing CallbackHandlerFactory Processing ===\n";
    
    // Create CallbackHandlerFactory and register all handlers
    $callbackHandlerFactory = new CallbackHandlerFactory();
    foreach ($handlers as $handler) {
        $callbackHandlerFactory->registerHandler($handler);
    }
    
    // Create mock callback query
    $user = new User([
        'id' => 230379668,
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
    
    $conversation = new Conversation(230379668, 123456789);
    
    echo "=== Step 3: Testing TabelHandler Directly ===\n";
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Test TabelHandler canHandle
    $canHandle = $tabelHandler->canHandle($callbackData);
    echo "TabelHandler canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    // Test extracting category
    $category = substr($callbackData, strlen('category_'));
    echo "Extracted category: {$category}\n";
    
    // Test special conditions in handle method
    echo "Special conditions:\n";
    echo "  Category === 'Табель_Электрооборудование': " . (($category === 'Табель_Электрооборудование') ? 'YES' : 'NO') . "\n";
    echo "  Category contains 'Электрооборудование': " . ((strpos($category, 'Электрооборудование') !== false) ? 'YES' : 'NO') . "\n";
    echo "  Category contains 'РўР°Р±РµР»СЊ_Электрооборудование': " . ((strpos($category, 'РўР°Р±РµР»СЊ_Электрооборудование') !== false) ? 'YES' : 'NO') . "\n";
    
    echo "\n=== Step 4: Testing User Access Level ===\n";
    $userId = 230379668;
    $userAccessLevel = $adminService->getUserAccessLevel($userId);
    echo "User access level: {$userAccessLevel}\n";
    echo "User access level name: " . $adminService->getAccessLevelName($userAccessLevel) . "\n";
    
    echo "\n=== Step 5: Testing FIO and Functional Direction Requirements ===\n";
    
    // Use reflection to test our new method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('isElectricalEquipmentMenu');
    $method->setAccessible(true);
    $isElectricalEquipmentMenu = $method->invokeArgs($tabelHandler, [$category]);
    
    echo "isElectricalEquipmentMenu result: " . ($isElectricalEquipmentMenu ? 'YES' : 'NO') . "\n";
    
    // Check if FIO/functional direction would be required
    $containsTabel = str_contains($category, 'Табель');
    $isMainTabelMenuMethod = $reflection->getMethod('isMainTabelMenu');
    $isMainTabelMenuMethod->setAccessible(true);
    $isMainMenu = $isMainTabelMenuMethod->invokeArgs($tabelHandler, [$category]);
    $shouldRequireFio = $containsTabel && !$isMainMenu && !$isElectricalEquipmentMenu;
    
    echo "Contains 'Табель': " . ($containsTabel ? 'YES' : 'NO') . "\n";
    echo "Is main menu: " . ($isMainMenu ? 'YES' : 'NO') . "\n";
    echo "Should require FIO/functional direction: " . ($shouldRequireFio ? 'YES' : 'NO') . "\n";
    
    echo "\n=== Step 6: Testing MessageService Keyboard Creation ===\n";
    try {
        $keyboard = $messageService->createElectricalEquipmentKeyboard();
        echo "MessageService created electrical equipment keyboard with " . count($keyboard) . " rows\n";
        echo "First row buttons: " . count($keyboard[0]) . "\n";
        echo "First button text: " . $keyboard[0][0]['text'] . "\n";
        echo "First button callback_data: " . $keyboard[0][0]['callback_data'] . "\n";
    } catch (Exception $e) {
        echo "Error creating electrical equipment keyboard: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Step 7: Testing Database Connection ===\n";
    try {
        $userData = $databaseService->getUserData($userId);
        echo "Database connection successful\n";
        echo "User data retrieved: " . (isset($userData['fio']) ? 'YES' : 'NO') . "\n";
        if (isset($userData['fio'])) {
            echo "User FIO: " . $userData['fio'] . "\n";
        }
    } catch (Exception $e) {
        echo "Database connection error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Final Summary ===\n";
    
    $allConditionsMet = $canHandle && $userAccessLevel > 0 && !$shouldRequireFio;
    
    if ($allConditionsMet) {
        echo "✅ All conditions met - button should work\n";
        echo "If it's still not working, the issue might be:\n";
        echo "1. Telegram client cache - try restarting Telegram\n";
        echo "2. Webhook issues - check server logs\n";
        echo "3. Button definition in keyboard - check callback_data\n";
        echo "4. Recent code changes might have introduced a regression\n";
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

echo "\n=== Comprehensive Debug Test Complete ===\n";