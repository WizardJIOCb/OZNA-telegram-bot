<?php
/**
 * Simple direct test of AdminService functionality
 */

echo "=== Simple AdminService Test ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Create a simple mock MessageService
class SimpleMockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
        echo "Mock sendMessage called:\n";
        echo "  Chat ID: $chatId\n";
        echo "  Text: " . substr($text, 0, 50) . "...\n";
        if ($keyboard) {
            echo "  Keyboard rows: " . count($keyboard) . "\n";
        }
        echo "\n";
        
        // Return a mock ServerResponse
        return new \Longman\TelegramBot\Entities\ServerResponse([
            'ok' => true,
            'result' => true
        ]);
    }
}

// Test with a test user ID that should be an admin
$testAdminUserId = 5261465966; // This should be an admin user ID

echo "Testing access level for user ID: {$testAdminUserId}\n";
$messageService = new SimpleMockMessageService();
$adminService = new AdminService($messageService);

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
    echo "Displaying first few requests:\n";
    $count = 0;
    foreach ($requests as $request) {
        if ($count >= 3) break;
        echo "  - {$request['user_name']} (ID: {$request['user_id']}) - " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
        $count++;
    }
    if (count($requests) > 3) {
        echo "  ... and " . (count($requests) - 3) . " more requests\n";
    }
} else {
    echo "No access requests found.\n";
}

// Test the showAccessRequestsList method directly
echo "\nTesting showAccessRequestsList method...\n";
try {
    // Simulate what the AdminHandler does
    $testChatId = '123456789';
    
    // Get requests again
    $requests = $adminService->getAllAccessRequests();
    echo "Requests count: " . count($requests) . "\n";
    
    // Create text message
    $text = "👥 <b>Заявки на доступ к боту</b>\n\n";
    $text .= "Всего заявок: " . count($requests) . "\n\n";
    
    if (empty($requests)) {
        $text .= "Нет новых заявок на доступ.";
        echo "No requests message: $text\n";
    } else {
        $text .= "Выберите заявку для просмотра:\n\n";
        echo "Has requests message: $text\n";
        echo "First few requests would be shown in keyboard...\n";
    }
    
} catch (Exception $e) {
    echo "Error in showAccessRequestsList: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";