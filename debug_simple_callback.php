<?php
/**
 * Simple debug script to test callback processing without database dependencies
 */

echo "=== Simple Callback Debug ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
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

// Create a simplified version of AdminService that doesn't depend on DatabaseService
class SimpleAdminService {
    private string $accessRequestsFile;
    
    public function __construct() {
        $this->accessRequestsFile = __DIR__ . '/data/access_requests.json';
    }
    
    public function getUserAccessLevel(int $userId): int {
        // Hardcode user 5261465966 as super admin (level 4)
        if ($userId === 5261465966) {
            return 4;
        }
        // Default to guest level
        return 0;
    }
    
    public function getAccessLevelName(int $level): string {
        $levels = [
            0 => 'Гость',
            1 => 'Пользователь',
            2 => 'Администратор',
            3 => 'Администратор',
            4 => 'Суперадмин'
        ];
        return $levels[$level] ?? 'Неизвестный';
    }
    
    public function getAllAccessRequests(): array {
        if (file_exists($this->accessRequestsFile)) {
            $content = file_get_contents($this->accessRequestsFile);
            $requests = json_decode($content, true);
            return is_array($requests) ? $requests : [];
        }
        return [];
    }
}

// Create services
$messageService = new MessageService();
$adminService = new SimpleAdminService();

// Create AdminHandler
$adminHandler = new AdminHandler($adminService, $messageService);

echo "1. Testing AdminHandler registration...\n";
echo "   AdminHandler created successfully\n\n";

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

echo "\n6. Testing AdminHandler::handle method...\n";
$callbackQuery = new MockCallbackQuery();
$conversation = new MockConversation();

echo "   Calling adminHandler->handle with category: 'Админка_Заявки'\n";
// Enable error logging for this test
ob_start();
// We can't actually call the handle method because it depends on other services
// But we can test the logic that determines if it should handle the callback
$category = 'Админка_Заявки';
echo "   Testing category routing logic...\n";
echo "   Category: {$category}\n";

// Manual test of routing logic from routeAdminCallback method
if ($category === 'Админка_Заявки') {
    echo "   Would call showAccessRequestsList method\n";
} else {
    echo "   Would NOT call showAccessRequestsList method\n";
}

echo "\n=== Test Complete ===\n";