<?php

// Test AdminService functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing AdminService functionality...\n";

// Create a mock MessageService
class MockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
        echo "Would send message to chat {$chatId}\n";
        echo "Text length: " . strlen($text) . " characters\n";
        if ($keyboard) {
            echo "Keyboard has " . count($keyboard) . " rows\n";
        }
        echo "---\n";
        // Return a mock response
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true, 'result' => true]);
    }
}

// Initialize services
$messageService = new MockMessageService();
$adminService = new AdminService($messageService);

// Check current access requests
echo "Current access requests:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
if (!empty($requests)) {
    foreach ($requests as $index => $request) {
        echo "Request #{$index}: User ID {$request['user_id']}, Name: {$request['user_name']}\n";
    }
}

// Test clearing all requests
echo "\nClearing all access requests...\n";
$adminService->clearAllAccessRequests();

// Check requests after clearing
echo "Requests after clearing:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";

// Add a few test requests
echo "\nAdding test requests...\n";
$adminService->sendAccessRequest(123456789, "Test User 1", "123456789");
$adminService->sendAccessRequest(987654321, "Test User 2", "987654321");

// Check requests after adding
echo "Requests after adding test requests:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
foreach ($requests as $index => $request) {
    echo "Request #{$index}: User ID {$request['user_id']}, Name: {$request['user_name']}\n";
}

// Test approving a request
echo "\nApproving test request...\n";
$adminService->approveAccessRequest(123456789, "Test User 1", "123456789");

// Check requests after approving
echo "Requests after approving test request:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
foreach ($requests as $index => $request) {
    echo "Request #{$index}: User ID {$request['user_id']}, Name: {$request['user_name']}\n";
}

echo "\nTest completed.\n";