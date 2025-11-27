<?php
// Complete callback process test

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Conversation;

echo "Testing complete callback process...\n";

try {
    // Initialize all services
    echo "\n1. Initializing services:\n";
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Create handlers
    echo "Creating handlers...\n";
    $adminHandler = new AdminHandler($adminService, $messageService);
    $accessLevelHandler = new AccessLevelHandler($adminService, $messageService);
    
    // Create factory and register handlers
    echo "Creating CallbackHandlerFactory...\n";
    $factory = new CallbackHandlerFactory();
    $factory->registerHandler($adminHandler);
    $factory->registerHandler($accessLevelHandler);
    
    // Test the complete process
    echo "\n2. Testing complete callback process:\n";
    $callbackData = 'category_Админка_Заявки';
    $chatId = '5261465966'; // Your user ID
    $userId = '5261465966'; // Your user ID
    
    echo "Callback data: {$callbackData}\n";
    echo "Chat ID: {$chatId}\n";
    echo "User ID: {$userId}\n";
    
    // Create a mock callback query (simplified)
    // Since we can't easily create a real CallbackQuery object, we'll test the factory directly
    
    // Create conversation
    echo "Creating conversation...\n";
    $conversation = new Conversation($chatId, $userId, "/callback");
    
    // Test factory handling
    echo "Testing factory handleCallback...\n";
    
    // We'll create a mock callback query object
    // This is a simplified approach since creating a real CallbackQuery is complex
    class MockCallbackQuery {
        private $message;
        private $from;
        
        public function __construct($chatId, $userId) {
            $this->message = new class($chatId) {
                private $chat;
                
                public function __construct($chatId) {
                    $this->chat = new class($chatId) {
                        private $id;
                        
                        public function __construct($chatId) {
                            $this->id = $chatId;
                        }
                        
                        public function getId() {
                            return $this->id;
                        }
                    };
                }
                
                public function getChat() {
                    return $this->chat;
                }
            };
            
            $this->from = new class($userId) {
                private $id;
                
                public function __construct($userId) {
                    $this->id = $userId;
                }
                
                public function getId() {
                    return $this->id;
                }
            };
        }
        
        public function getMessage() {
            return $this->message;
        }
        
        public function getFrom() {
            return $this->from;
        }
    }
    
    $mockCallbackQuery = new MockCallbackQuery($chatId, $userId);
    
    echo "Calling factory->handleCallback...\n";
    $result = $factory->handleCallback($mockCallbackQuery, $callbackData, $conversation);
    
    echo "Factory handleCallback result:\n";
    print_r($result);
    
    if (is_object($result) && method_exists($result, 'isOk')) {
        echo "isOk(): " . ($result->isOk() ? 'true' : 'false') . "\n";
        if (!$result->isOk()) {
            echo "Error description: " . $result->getDescription() . "\n";
            echo "Error code: " . $result->getErrorCode() . "\n";
        }
    }
    
    echo "\nComplete callback process test finished!\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>