<?php

// Test script to verify admin button handling
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/TextmenuCommand.php';

use Longman\TelegramBot\Commands\UserCommands\TextmenuCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Entities\Chat;

echo "Testing admin button handling...\n";

// Create mock objects
class MockTelegram {
    public function __call($name, $arguments) {
        // Mock any Telegram methods
        return null;
    }
}

class MockUpdate extends Update {
    private $message;
    
    public function __construct($message) {
        $this->message = $message;
    }
    
    public function getMessage() {
        return $this->message;
    }
}

class MockMessage extends Message {
    private $text;
    private $from;
    private $chat;
    
    public function __construct($text, $userId, $chatId) {
        $this->text = $text;
        $this->from = new User(['id' => $userId, 'first_name' => 'Test', 'last_name' => 'User']);
        $this->chat = new Chat(['id' => $chatId, 'type' => 'private']);
    }
    
    public function getText($withoutCmd = false) {
        return $this->text;
    }
    
    public function getFrom() {
        return $this->from;
    }
    
    public function getChat() {
        return $this->chat;
    }
}

// Test the "Админка" button
echo "\nTesting 'Админка' button...\n";
$message = new MockMessage('Админка', 230379668, 230379668); // Using a super admin ID
$update = new MockUpdate($message);

$telegram = new MockTelegram();
$textmenuCommand = new TextmenuCommand($telegram, $update);

echo "TextmenuCommand created successfully\n";
echo "Command name: " . $textmenuCommand->getName() . "\n";

echo "\nTest completed.\n";