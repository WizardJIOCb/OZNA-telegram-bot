<?php
/**
 * Test script to simulate the "Заявки на доступ" button click
 */

echo "=== Testing 'Заявки на доступ' Button ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
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

// Create AdminHandler
$adminHandler = new AdminHandler($adminService, $messageService);

// Test canHandle method
$callbackData = 'category_Админка_Заявки';
$canHandle = $adminHandler->canHandle($callbackData);
echo "Can handle '{$callbackData}': " . ($canHandle ? 'YES' : 'NO') . "\n";

// Test with a test user ID that should be an admin
$testAdminUserId = 5261465966; // This should be an admin user ID
echo "Testing access level for user ID: {$testAdminUserId}\n";
$userAccessLevel = $adminService->getUserAccessLevel($testAdminUserId);
echo "User access level: {$userAccessLevel}\n";
$levelName = $adminService->getAccessLevelName($userAccessLevel);
echo "Level name: {$levelName}\n\n";

// Check if user has admin access
$hasAdminAccess = $userAccessLevel >= 3;
echo "Has admin access (level 3 or higher): " . ($hasAdminAccess ? 'YES' : 'NO') . "\n\n";

// Test getting all access requests
echo "Getting all access requests...\n";
$requests = $adminService->getAllAccessRequests();
echo "Total requests found: " . count($requests) . "\n";

if (!empty($requests)) {
    echo "Displaying first request:\n";
    $firstRequest = $requests[0];
    print_r($firstRequest);
}

echo "\n=== Test Complete ===\n";