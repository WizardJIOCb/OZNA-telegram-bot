<?php

// Test approval functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing approval functionality...\n";

// Create a simple mock MessageService
class SimpleMockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
        echo "Mock sendMessage called for chatId: {$chatId}\n";
        // Create a simple mock ServerResponse
        $response = new \Longman\TelegramBot\Entities\ServerResponse([
            'ok' => true,
            'result' => true
        ]);
        return $response;
    }
}

// Initialize services
$messageService = new SimpleMockMessageService();
$adminService = new AdminService($messageService);

// Add a test request
echo "Adding a test request...\n";
$adminService->sendAccessRequest(111222333, "Approval Test User", "111222333");

// Check requests after adding
echo "Requests after adding test request:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
foreach ($requests as $index => $request) {
    echo "Request #{$index}: User ID {$request['user_id']}, Name: {$request['user_name']}\n";
}

// Test approving a request
echo "\nApproving test request...\n";
$adminService->approveAccessRequest(111222333, "Approval Test User", "111222333");

// Check requests after approving
echo "Requests after approving test request:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
foreach ($requests as $index => $request) {
    echo "Request #{$index}: User ID {$request['user_id']}, Name: {$request['user_name']}\n";
}

echo "\nTest completed.\n";