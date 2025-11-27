<?php

require_once 'config.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create a mock MessageService for testing
class MockMessageService extends MessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "Would send message to chat ID $chatId:\n";
        echo "Text: $text\n";
        if ($keyboard) {
            echo "Keyboard: " . json_encode($keyboard, JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
        // Return a mock ServerResponse
        return new class {
            public function isOk() { return true; }
        };
    }
    
    public function sendToHistoryChat($message, $history_thread_id = 2) {
        echo "Would send to history chat: $message\n";
        return new class {
            public function isOk() { return true; }
        };
    }
}

// Test the AdminService
$messageService = new MockMessageService();
$adminService = new AdminService($messageService);

echo "Testing AdminService access requests functionality...\n\n";

// Test getting all access requests
$requests = $adminService->getAllAccessRequests();
echo "Found " . count($requests) . " access requests\n";

// Display the requests
if (!empty($requests)) {
    echo "Access requests data:\n";
    foreach ($requests as $request) {
        echo "  User ID: " . $request['user_id'] . "\n";
        echo "  User Name: " . $request['user_name'] . "\n";
        echo "  FIO: " . ($request['user_fio'] ?? 'N/A') . "\n";
        echo "  Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n\n";
    }
} else {
    echo "No access requests found\n";
}

echo "\nTest completed.\n";