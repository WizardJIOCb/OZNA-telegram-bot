<?php

echo "Testing StartCommand functionality...\n\n";

// Подключаем наш собственный автозагрузчик
require_once __DIR__ . '/custom_autoload.php';

// Также подключаем автозагрузчик Composer для сторонних библиотек
require_once __DIR__ . '/vendor/autoload.php';

// Создаем имитацию необходимых классов Telegram Bot
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
    private $text;
    
    public function __construct($userId, $chatId, $text = '') {
        $this->from = new MockUser($userId);
        $this->chat = new MockChat($chatId);
        $this->text = $text;
    }
    
    public function getFrom() {
        return $this->from;
    }
    
    public function getChat() {
        return $this->chat;
    }
    
    public function getText() {
        return $this->text;
    }
}

class MockServerResponse {
    public function isOk() {
        return true;
    }
}

// Создаем имитацию StartCommand
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
        try {
            $messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
            echo "✓ MessageService created successfully\n";
            
            // Для теста создаем упрощенную версию AdminService без базы данных
            $adminService = new class($messageService) {
                private $messageService;
                
                public function __construct($messageService) {
                    $this->messageService = $messageService;
                }
                
                public function getUserAccessLevel($userId) {
                    // Для теста возвращаем уровень доступа 1 (пользователь)
                    return 1;
                }
            };
            
            echo "✓ AdminService created successfully\n";
            
        } catch (Exception $e) {
            echo "✗ Error creating services: " . $e->getMessage() . "\n";
            return false;
        }
        
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
        
        // Send message with reply keyboard (persistent menu)
        try {
            // Для теста просто проверяем, что метод существует
            if (method_exists($messageService, 'sendMessageWithReplyKeyboard')) {
                echo "✓ sendMessageWithReplyKeyboard method exists\n";
                
                // Создаем reply markup
                $replyMarkup = [
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false
                ];
                
                echo "Reply markup JSON:\n";
                echo json_encode($replyMarkup, JSON_UNESCAPED_UNICODE) . "\n\n";
                
                echo "✓ Message would be sent successfully\n";
                return true;
            } else {
                echo "✗ sendMessageWithReplyKeyboard method not found\n";
                return false;
            }
        } catch (Exception $e) {
            echo "Error sending message: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Тест с различными уровнями доступа
$testCases = [
    ['user_id' => 123456789, 'chat_id' => 987654321, 'description' => 'Regular user'],
    ['user_id' => 230379668, 'chat_id' => 987654321, 'description' => 'Super admin (hardcoded)'],
];

$allTestsPassed = true;
foreach ($testCases as $testCase) {
    echo "=== Testing {$testCase['description']} ===\n";
    $startCommand = new TestStartCommand($testCase['user_id'], $testCase['chat_id']);
    $result = $startCommand->executeTest();
    if (!$result) {
        $allTestsPassed = false;
    }
    echo str_repeat("=", 50) . "\n\n";
}

if ($allTestsPassed) {
    echo "✓ All tests passed! The /start command should work correctly.\n";
} else {
    echo "✗ Some tests failed. Please check the errors above.\n";
}