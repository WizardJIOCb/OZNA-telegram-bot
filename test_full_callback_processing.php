<?php

// Тестовый скрипт для проверки полного цикла обработки callback-ов

echo "=== Тест полного цикла обработки callback-ов ===\n\n";

// Подключаем необходимые файлы
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

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

// Создаем mock объекты для симуляции callback query
class MockCallbackQuery extends CallbackQuery {
    private $data;
    private $message;
    private $from;
    
    public function __construct($data) {
        $this->data = $data;
        
        // Создаем mock message
        $mockChat = new Chat(['id' => 123456789]);
        $this->message = new Message([
            'message_id' => 1,
            'from' => ['id' => 123456789, 'first_name' => 'Test', 'last_name' => 'User'],
            'chat' => ['id' => 123456789],
            'date' => time(),
            'text' => 'test'
        ]);
        
        // Создаем mock user
        $this->from = new User([
            'id' => 123456789,
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function getFrom() {
        return $this->from;
    }
}

// Создаем сервисы
$messageService = new MessageService();
$databaseService = new DatabaseService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Создаем фабрику обработчиков
$handlerFactory = new CallbackHandlerFactory();

// Регистрируем обработчики в правильном порядке
$handlerFactory->registerHandler(new AdminHandler($adminService, $messageService));
$handlerFactory->registerHandler(new AccessLevelHandler($adminService, $messageService));
$handlerFactory->registerHandler(new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService));
$handlerFactory->registerHandler(new ProfileHandler($messageService));
$handlerFactory->registerHandler(new FunctionalDirectionHandler($messageService));
$handlerFactory->registerHandler(new HelpHandler($messageService));

// Тестовые callback-ы
$testCallbacks = [
    'adm_req_clear',
    'adm_req_view_123456789',
    'adm_req_approve_123456789',
    'adm_req_reject_123456789'
];

echo "Тест полного цикла обработки callback-ов:\n";
foreach ($testCallbacks as $callback) {
    echo "\nТест callback: '{$callback}'\n";
    
    // Создаем mock callback query
    $callbackQuery = new MockCallbackQuery($callback);
    $conversation = new Conversation(123456789, 123456789, "/callback");
    
    try {
        // Пытаемся обработать callback
        $result = $handlerFactory->handleCallback($callbackQuery, $callback, $conversation);
        echo "  Результат: обработан успешно\n";
    } catch (Exception $e) {
        echo "  Ошибка: " . $e->getMessage() . "\n";
        echo "  Трассировка: " . $e->getTraceAsString() . "\n";
    }
}

echo "\n=== Тест завершен ===\n";