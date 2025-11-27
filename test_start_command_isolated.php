<?php

echo "Testing StartCommand functionality in isolation...\n\n";

// Имитируем необходимые классы Telegram Bot
class MockUser {
    private $id;
    
    public function __construct($id) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
}

class MockChat {
    private $id;
    
    public function __construct($id) {
        $this->id = $id;
    }
    
    public function getId() {
        return $this->id;
    }
}

class MockMessage {
    private $from;
    private $chat;
    
    public function __construct($userId, $chatId) {
        $this->from = new MockUser($userId);
        $this->chat = new MockChat($chatId);
    }
    
    public function getFrom() {
        return $this->from;
    }
    
    public function getChat() {
        return $this->chat;
    }
}

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

// Создаем имитацию команды StartCommand
class TestStartCommand {
    private $message;
    
    public function __construct($userId, $chatId) {
        $this->message = new MockMessage($userId, $chatId);
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function executeTest() {
        // Get user info
        $message = $this->getMessage();
        $user_id = $message->getFrom()->getId();
        $chat_id = $message->getChat()->getId();
        
        echo "User ID: {$user_id}\n";
        echo "Chat ID: {$chat_id}\n\n";
        
        // Initialize services
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);
        
        // Check user access level with fallback
        $userAccessLevel = 0; // Default to guest level
        try {
            $userAccessLevel = $adminService->getUserAccessLevel($user_id);
            echo "User access level: {$userAccessLevel}\n";
        } catch (Exception $e) {
            echo "Error getting user access level: " . $e->getMessage() . "\n";
            // Fallback to guest level
            $userAccessLevel = 0;
        }
        
        // Create main menu reply keyboard based on user access level
        $keyboard = [];
        if ($userAccessLevel == 0) { // Guest level
            // Limited menu for guests
            $keyboard = [
                [
                    ['text' => '👤 Профиль'], 
                    ['text' => '❓ Помощь']
                ]
            ];
            echo "Creating guest menu\n";
        } else {
            // Full menu for other users
            try {
                $keyboard = $messageService->createMainMenuReplyKeyboard();
                echo "Creating full menu\n";
                echo "Keyboard data: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n";
            } catch (Exception $e) {
                echo "Error creating main menu keyboard: " . $e->getMessage() . "\n";
                // Fallback to guest menu
                $keyboard = [
                    [
                        ['text' => '👤 Профиль'], 
                        ['text' => '❓ Помощь']
                    ]
                ];
            }
        }
        
        // Ensure keyboard is properly formatted
        if (!is_array($keyboard) || empty($keyboard)) {
            echo "Keyboard is empty or invalid, using default\n";
            $keyboard = [
                [
                    ['text' => '📊 Табель'], 
                    ['text' => '❓ Помощь']
                ],
                [
                    ['text' => '👤 Профиль'], 
                    ['text' => '⚙️ Админка']
                ]
            ];
        }
        
        echo "\nFinal keyboard to be sent:\n";
        echo json_encode($keyboard, JSON_UNESCAPED_UNICODE) . "\n\n";
        
        // Create reply markup
        $replyMarkup = [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ];
        
        echo "Reply markup JSON:\n";
        echo json_encode($replyMarkup, JSON_UNESCAPED_UNICODE) . "\n\n";
        
        return [
            'chat_id' => $chat_id,
            'text' => "<b>🏠 Главное меню</b>\r\n\r\n🔄 Для вызова этого меню отправьте /start",
            'reply_markup' => $replyMarkup
        ];
    }
}

// Тест с различными уровнями доступа
$testCases = [
    ['user_id' => 123456789, 'chat_id' => 987654321, 'description' => 'Regular user'],
    ['user_id' => 230379668, 'chat_id' => 987654321, 'description' => 'Super admin (hardcoded)'],
];

foreach ($testCases as $testCase) {
    echo "=== Testing {$testCase['description']} ===\n";
    $startCommand = new TestStartCommand($testCase['user_id'], $testCase['chat_id']);
    $result = $startCommand->executeTest();
    echo str_repeat("=", 50) . "\n\n";
}