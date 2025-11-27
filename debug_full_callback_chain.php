<?php
/**
 * Debug script to test the full callback processing chain
 */

echo "=== Full Callback Chain Debug ===\n\n";

// Include required files in correct order
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

// Create mock classes for testing
class MockCallbackQuery {
    public function getMessage() {
        return new class {
            public function getChat() {
                return new class {
                    public function getId() {
                        return '123456789'; // Test chat ID
                    }
                };
            }
        };
    }
    
    public function getFrom() {
        return new class {
            public function getId() {
                return '5261465966'; // Test user ID (should be admin)
            }
            
            public function getFirstName() {
                return 'Test';
            }
            
            public function getLastName() {
                return 'User';
            }
        };
    }
    
    public function getData() {
        return 'category_Админка_Заявки';
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock implementation
    }
}

// Create services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Create CallbackHandlerFactory
$handlerFactory = new CallbackHandlerFactory();

// Register AdminHandler
$adminHandler = new AdminHandler($adminService, $messageService);
$handlerFactory->registerHandler($adminHandler);

echo "1. Testing AdminHandler registration...\n";
echo "   AdminHandler registered successfully\n\n";

// Test canHandle method
$callbackData = 'category_Админка_Заявки';
echo "2. Testing AdminHandler::canHandle with callbackData: '{$callbackData}'\n";
$canHandle = $adminHandler->canHandle($callbackData);
echo "   Result: " . ($canHandle ? 'YES' : 'NO') . "\n\n";

// Test with a test user ID that should be an admin
$testAdminUserId = 5261465966; // This should be an admin user ID
echo "3. Testing access level for user ID: {$testAdminUserId}\n";
$userAccessLevel = $adminService->getUserAccessLevel($testAdminUserId);
echo "   User access level: {$userAccessLevel}\n";
$levelName = $adminService->getAccessLevelName($userAccessLevel);
echo "   Level name: {$levelName}\n\n";

// Check if user has admin access
$hasAdminAccess = $userAccessLevel >= 3;
echo "4. Has admin access (level 3 or higher): " . ($hasAdminAccess ? 'YES' : 'NO') . "\n\n";

// Test getting all access requests
echo "5. Getting all access requests...\n";
$requests = $adminService->getAllAccessRequests();
echo "   Total requests found: " . count($requests) . "\n";

if (!empty($requests)) {
    echo "   Displaying first request:\n";
    $firstRequest = $requests[0];
    print_r($firstRequest);
}

echo "\n6. Testing CallbackHandlerFactory...\n";
$callbackQuery = new MockCallbackQuery();
$conversation = new MockConversation();

echo "   Calling handlerFactory->handleCallback with callbackData: '{$callbackData}'\n";
// Enable error logging for this test
ob_start();
$result = $handlerFactory->handleCallback($callbackQuery, $callbackData, $conversation);
$handlerOutput = ob_get_clean();

echo "   Handler output:\n";
echo $handlerOutput . "\n";

echo "   Handler result: " . (is_object($result) ? 'ServerResponse object' : 'Not an object') . "\n\n";

echo "7. Testing AdminHandler directly...\n";
echo "   Calling adminHandler->handle with category: 'Админка_Заявки'\n";
ob_start();
$directResult = $adminHandler->handle($callbackQuery, 'Админка_Заявки', $conversation);
$directOutput = ob_get_clean();

echo "   Direct handler output:\n";
echo $directOutput . "\n";

echo "   Direct handler result: " . (is_object($directResult) ? 'ServerResponse object' : 'Not an object') . "\n\n";

echo "=== Test Complete ===\n";