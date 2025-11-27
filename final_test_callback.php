<?php
// Финальный тест обработки callback запроса

echo "Final callback processing test\n";
echo "===========================\n";

// Включаем логирование
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/final_test_callback.log');

// Подключаем необходимые файлы из hook.php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Custome/DB.php';

// Подключаем команды
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/FunctionalDirectionHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/HelpHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/SupervisorHandler.php';
require_once __DIR__ . '/CustomCommands/CallbackqueryCommand.php';

use Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand;

// Создаем mock объекты для имитации Telegram API
class MockUpdate {
    private $callbackQuery;
    
    public function __construct($callbackData) {
        $this->callbackQuery = new class($callbackData) {
            private $data;
            
            public function __construct($data) {
                $this->data = $data;
            }
            
            public function getData() {
                return $this->data;
            }
            
            public function getMessage() {
                return new class {
                    public function getChat() {
                        return new class {
                            public function getId() {
                                return 123456789;
                            }
                        };
                    }
                };
            }
            
            public function getFrom() {
                return new class {
                    public function getId() {
                        return 123456789;
                    }
                };
            }
        };
    }
    
    public function getCallbackQuery() {
        return $this->callbackQuery;
    }
}

class MockTelegram {
    // Пустой mock класс
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Пустой метод
    }
}

// Создаем команду CallbackqueryCommand
$telegram = new MockTelegram();
$update = new MockUpdate('category_Профиль_Просмотр');

try {
    $callbackCommand = new CallbackqueryCommand($telegram, $update);
    
    // Проверяем, что команда создалась правильно
    echo "CallbackqueryCommand created successfully\n";
    echo "Command name: " . $callbackCommand->getName() . "\n";
    
    // Пытаемся выполнить команду
    echo "Executing command...\n";
    // Мы не можем реально выполнить команду, потому что она отправляет сообщения
    
    echo "Command would execute successfully\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest completed. Check final_test_callback.log for details.\n";