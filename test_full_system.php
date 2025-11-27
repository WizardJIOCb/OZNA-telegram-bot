<?php

// Подключаем необходимые файлы
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Handlers/AdminHandler.php';
require_once 'CustomCommands/Services/CallbackHandlerFactory.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

// Создаем mock-объект MessageService
class MockMessageService extends MessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        echo "Отправка сообщения в чат $chatId:\n";
        echo "Текст: $text\n";
        if ($keyboard) {
            echo "Клавиатура: " . json_encode($keyboard, JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
        
        // Возвращаем mock-объект ServerResponse
        return new class {
            public function isOk() { return true; }
        };
    }
}

// Создаем mock-объект CallbackQuery
class MockCallbackQuery {
    private $message;
    private $from;
    
    public function __construct() {
        $this->message = new class {
            public function getChat() {
                return new class {
                    public function getId() {
                        return 123456789;
                    }
                };
            }
        };
        
        $this->from = new class {
            public function getId() {
                return 123456789;
            }
            
            public function getFirstName() {
                return "Иван";
            }
            
            public function getLastName() {
                return "Иванов";
            }
        };
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getFrom() {
        return $this->from;
    }
    
    public function getData() {
        return "category_Админка_Заявки";
    }
}

// Создаем mock-объект Conversation
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Пустая реализация
    }
}

// Тестируем систему
echo "Тестирование полной системы...\n\n";

// Создаем экземпляр MessageService
$messageService = new MockMessageService();

// Создаем экземпляр AdminService
$adminService = new AdminService($messageService);

// Получаем все заявки на доступ
$requests = $adminService->getAllAccessRequests();

echo "Найдено заявок: " . count($requests) . "\n";

if (!empty($requests)) {
    echo "Детали заявок:\n";
    foreach ($requests as $index => $request) {
        echo "Заявка #" . ($index + 1) . ":\n";
        echo "  User ID: " . ($request['user_id'] ?? 'N/A') . "\n";
        echo "  User Name: " . ($request['user_name'] ?? 'N/A') . "\n";
        echo "  FIO: " . ($request['user_fio'] ?? 'N/A') . "\n";
        echo "  Telegram Name: " . ($request['user_telegram_name'] ?? 'N/A') . "\n";
        echo "  Chat ID: " . ($request['chat_id'] ?? 'N/A') . "\n";
        echo "  Timestamp: " . ($request['timestamp'] ?? 'N/A') . "\n\n";
    }
} else {
    echo "Заявок на доступ не найдено.\n";
}

// Создаем экземпляр AdminHandler
$adminHandler = new AdminHandler($adminService, $messageService);

// Создаем mock-объекты
$callbackQuery = new MockCallbackQuery();
$conversation = new MockConversation();

// Проверяем, может ли AdminHandler обработать запрос
$canHandle = $adminHandler->canHandle("category_Админка_Заявки");
echo "AdminHandler может обработать запрос: " . ($canHandle ? "Да" : "Нет") . "\n";

// Проверяем, как AdminHandler обрабатывает запрос
if ($canHandle) {
    echo "\nОбработка запроса...\n";
    $response = $adminHandler->handle($callbackQuery, "Админка_Заявки", $conversation);
    echo "Ответ получен: " . ($response->isOk() ? "Успешно" : "Ошибка") . "\n";
}

echo "\nТест завершен.\n";