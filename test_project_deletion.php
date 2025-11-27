<?php
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create mock services
class MockMessageService extends MessageService {
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML') {
        echo "=== Mock Message Service ===\n";
        echo "Sending message to chat $chatId:\n";
        echo "$text\n";
        if ($keyboard) {
            echo "With keyboard:\n";
            foreach ($keyboard as $row) {
                foreach ($row as $button) {
                    echo "  [{$button['text']}] -> {$button['callback_data']}\n";
                }
            }
        }
        echo "========================\n\n";
        // Return a mock response
        return new \Longman\TelegramBot\Entities\ServerResponse(['ok' => true, 'result' => null]);
    }
}

// Test the project deletion functionality
$messageService = new MockMessageService();
$adminService = new AdminService($messageService);
$handler = new AdminHandler($adminService, $messageService);

echo "Testing project deletion functionality...\n\n";

// Test handleDeleteSpecificProject method
echo "1. Testing handleDeleteSpecificProject with new format:\n";
$callbackQuery = new \Longman\TelegramBot\Entities\CallbackQuery([
    'id' => '123',
    'from' => ['id' => 5261465966],
    'message' => [
        'message_id' => '1',
        'from' => ['id' => 5261465966],
        'chat' => ['id' => 5261465966],
        'date' => time(),
        'text' => 'Test'
    ],
    'chat_instance' => '123',
    'data' => 'category_Адм_УдПр_ОИ834'
]);

// Create a mock conversation
class MockConversation {
    public $notes = [];
    public function update() {
        // Mock implementation
    }
}

$conversation = new MockConversation();

// Test the method
try {
    $result = $handler->handle($callbackQuery, 'Адм_УдПр_ОИ834', $conversation);
    echo "Method executed successfully\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "2. Testing handleConfirmDeleteProject method:\n";
$callbackQuery2 = new \Longman\TelegramBot\Entities\CallbackQuery([
    'id' => '124',
    'from' => ['id' => 5261465966],
    'message' => [
        'message_id' => '2',
        'from' => ['id' => 5261465966],
        'chat' => ['id' => 5261465966],
        'date' => time(),
        'text' => 'Test'
    ],
    'chat_instance' => '124',
    'data' => 'category_Админка_ПодтвУдал_ОИ834'
]);

try {
    $result = $handler->handle($callbackQuery2, 'Админка_ПодтвУдал_ОИ834', $conversation);
    echo "Method executed successfully\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "Test completed.\n";