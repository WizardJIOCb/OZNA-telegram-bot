<?php

echo "=== Testing Department Buttons Fix ===\n\n";

// Test the callback mapping
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;

// Mock classes
class MockMessage {
    private $chat;
    
    public function __construct() {
        $this->chat = new MockChat();
    }
    
    public function getChat() {
        return $this->chat;
    }
}

class MockChat {
    public function getId() {
        return 'test_chat_id';
    }
}

class MockCallbackQuery {
    private $message;
    private $data;
    
    public function __construct($data) {
        $this->message = new MockMessage();
        $this->data = $data;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getFrom() {
        return new MockUser();
    }
}

class MockUser {
    public function getId() {
        return 12345;
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        return true;
    }
}

// Test department button callbacks
$departmentCallbacks = [
    'category_Табель_Пусконаладка' => 'ПНР Department (Отдел ПНР)',
    'category_Табель_Шеф_монтаж' => 'ШМР Department (Отдел ШМР)'
];

echo "Testing callback mappings:\n";
foreach ($departmentCallbacks as $callback => $description) {
    echo "- {$description}: '{$callback}'\n";
    
    // Test if TabelHandler can handle these callbacks
    $tabelHandler = new \CustomCommands\Handlers\TabelHandler();
    
    // Use reflection to test the extractCategoryFromCallback method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    
    $extractedCategory = $method->invoke($tabelHandler, $callback);
    echo "  Extracted category: '{$extractedCategory}'\n";
    
    // Test canHandle method
    $canHandleMethod = $reflection->getMethod('canHandle');
    $canHandleMethod->setAccessible(true);
    $canHandle = $canHandleMethod->invoke($tabelHandler, $callback);
    echo "  Can handle: " . ($canHandle ? 'YES' : 'NO') . "\n\n";
}

echo "=== Summary ===\n";
echo "The department buttons should now work correctly with these fixes:\n";
echo "1. Added category mappings in extractCategoryFromCallback\n"; 
echo "2. Added handlers in handleOtherWorkTypes for both department buttons\n";
echo "3. Fixed syntax errors in the file\n\n";

echo "NEXT: Test in the actual bot by clicking:\n";
echo "- Табель → Отдел ПНР\n";
echo "- Табель → Отдел ШМР\n";

?>