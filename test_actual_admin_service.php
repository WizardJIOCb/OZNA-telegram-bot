<?php

// Test actual AdminService functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing actual AdminService functionality...\n";

// Create a simple mock MessageService that doesn't require Telegram Bot dependencies
class SimpleMockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
        echo "Mock sendMessage called for chatId: {$chatId}\n";
        echo "Text: " . substr($text, 0, 50) . "...\n";
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

// Check current access requests
echo "Current access requests:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";

// Test clearing all requests
echo "\nClearing all access requests...\n";
$adminService->clearAllAccessRequests();

// Check what was written to the file
echo "\nChecking file content after clear:\n";
$fileContent = file_get_contents(__DIR__ . '/data/access_requests.json');
echo "File content: " . $fileContent . "\n";
echo "File hex: " . bin2hex($fileContent) . "\n";

// Check requests after clearing
echo "\nRequests after clearing:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";

echo "\nTest completed.\n";