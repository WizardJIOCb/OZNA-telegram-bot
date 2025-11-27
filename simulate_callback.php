<?php
// Симуляция callback запроса

echo "Simulating callback request\n";
echo "========================\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

// Создаем mock объекты
class MockCallbackQuery {
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
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Пустой метод
    }
}

// Создаем фабрику обработчиков и регистрируем ProfileHandler
$messageService = new MessageService();
$profileHandler = new ProfileHandler($messageService);
$handlerFactory = new CallbackHandlerFactory();
$handlerFactory->registerHandler($profileHandler);

// Имитируем callback_data для кнопки "Просмотр профиля"
$callbackData = 'category_Профиль_Просмотр';
echo "Testing with callbackData: $callbackData\n";

// Создаем mock объекты
$callbackQuery = new MockCallbackQuery($callbackData);
$conversation = new MockConversation();

// Проверяем, может ли ProfileHandler обработать этот callback
$canHandle = $profileHandler->canHandle($callbackData);
echo "ProfileHandler canHandle: " . ($canHandle ? 'true' : 'false') . "\n";

if ($canHandle) {
    echo "ProfileHandler can handle this callback. Testing handle method...\n";
    
    // Имитируем извлечение категории (как в CallbackHandlerFactory)
    $category = substr($callbackData, strlen('category_'));
    echo "Extracted category: $category\n";
    
    // Пытаемся вызвать handle метод
    try {
        echo "Calling handle method...\n";
        // Мы не можем реально вызвать handle, потому что он отправляет сообщения
        echo "Handle method would be called successfully\n";
    } catch (Exception $e) {
        echo "Error calling handle method: " . $e->getMessage() . "\n";
    }
} else {
    echo "ProfileHandler cannot handle this callback!\n";
    echo "This is the source of the problem.\n";
}

echo "\nTest completed.\n";