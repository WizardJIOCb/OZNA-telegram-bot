<?php

// Simple test script to verify access requests functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\ServerResponse;

echo "Testing access requests functionality...\n";

// Create a mock MessageService that doesn't send actual messages
class MockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): ServerResponse {
        echo "Would send message to chat {$chatId}:\n";
        echo "Text: {$text}\n";
        if ($keyboard) {
            echo "Keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "---\n";
        // Return a mock response
        return new ServerResponse(['ok' => true, 'result' => true]);
    }
}

// Initialize services with mock
$messageService = new MockMessageService();
$adminService = new AdminService($messageService);

// Check if database is available
$databaseAvailable = extension_loaded('mysqli');
echo "Database available: " . ($databaseAvailable ? 'Yes' : 'No') . "\n";

// Check current access requests
echo "Current access requests:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
if (!empty($requests)) {
    echo "Data: " . json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

// Test clearing all requests
echo "\nClearing all access requests...\n";
$adminService->clearAllAccessRequests();

// Check requests after clearing
echo "Requests after clearing:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";

// Add a test request
echo "\nAdding a test request...\n";
$adminService->sendAccessRequest(987654321, "Test User", "987654321");

// Check requests after adding
echo "Requests after adding test request:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
if (!empty($requests)) {
    echo "Data: " . json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

// Test approving a request (without database)
echo "\nApproving test request (without database)...\n";
$adminService->approveAccessRequest(987654321, "Test User", "987654321");

// Check requests after approving
echo "Requests after approving test request:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
if (!empty($requests)) {
    echo "Data: " . json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\nTest completed.\n";