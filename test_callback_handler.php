<?php

// Test callback handler functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

echo "Testing callback handler functionality...\n";

// Create a mock MessageService
class MockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): \Longman\TelegramBot\Entities\ServerResponse {
        echo "Would send message to chat {$chatId}\n";
        echo "Text: {$text}\n";
        if ($keyboard) {
            echo "Keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "---\n";
        // Return a mock response
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true, 'result' => true]);
    }
}

// Mock classes for testing
class MockCallbackQuery {
    private $data;
    private $from;
    private $message;
    
    public function __construct($data) {
        $this->data = $data;
        $this->from = new class {
            public function getId() { return 230379668; }
        };
        $this->message = new class {
            public function getChat() {
                return new class {
                    public function getId() { return 'test_chat_id'; }
                };
            }
        };
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getFrom() {
        return $this->from;
    }
    
    public function getMessage() {
        return $this->message;
    }
}

class MockConversation {
    public function __construct() {}
    public function notes($key = null) { return []; }
    public function update() {}
}

// Initialize services
$messageService = new MockMessageService();
$adminService = new AdminService($messageService);
$adminHandler = new AdminHandler($adminService, $messageService);

// Test if handler can handle adm_req_clear callback
$callbackData = 'adm_req_clear';
$canHandle = $adminHandler->canHandle($callbackData);
echo "Can handle '{$callbackData}': " . ($canHandle ? 'Yes' : 'No') . "\n";

// Test if handler can handle adm_req_approve callback
$callbackData = 'adm_req_approve_123456789';
$canHandle = $adminHandler->canHandle($callbackData);
echo "Can handle '{$callbackData}': " . ($canHandle ? 'Yes' : 'No') . "\n";

// Test if handler can handle adm_req_view callback
$callbackData = 'adm_req_view_123456789';
$canHandle = $adminHandler->canHandle($callbackData);
echo "Can handle '{$callbackData}': " . ($canHandle ? 'Yes' : 'No') . "\n";

echo "Test completed.\n";