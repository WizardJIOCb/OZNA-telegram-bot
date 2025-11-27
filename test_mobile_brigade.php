<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Создаем мock объекты для тестирования
class MockCallbackQuery {
    public function getMessage() {
        return new class {
            public function getChat() {
                return new class {
                    public function getId() {
                        return '123456789';
                    }
                };
            }
        };
    }
    
    public function getFrom() {
        return new class {
            public function getId() {
                return '987654321';
            }
        };
    }
    
    public function getData() {
        return 'category_Табель_Мобильная_Бригада_ДатаСегодня';
    }
}

class MockConversation {
    public $notes = [];
    
    public function update() {
        // Пустая реализация для теста
    }
}

// Инициализация сервисов
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Создание обработчика
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Создание mock объектов
$callbackQuery = new MockCallbackQuery();
$conversation = new MockConversation();

// Извлечение категории из callback данных
$category = str_replace('category_', '', $callbackQuery->getData());

echo "Testing with category: $category\n";

// Проверка, может ли обработчик справиться с этим callback
$canHandle = $tabelHandler->canHandle($callbackQuery->getData());
echo "Can handle: " . ($canHandle ? 'Yes' : 'No') . "\n";

// Проверка маршрутизации callback
echo "Routing callback...\n";
// В реальной ситуации здесь был бы вызов routeTabelCallback

echo "Test completed.\n";