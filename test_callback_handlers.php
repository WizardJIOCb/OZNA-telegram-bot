<?php

// Тестовый скрипт для проверки обработчиков callback-ов

echo "=== Тест обработчиков callback-ов ===\n\n";

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

// Создаем сервисы
$messageService = new MessageService();
$databaseService = new DatabaseService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Создаем обработчики
$adminHandler = new AdminHandler($adminService, $messageService);
$accessLevelHandler = new AccessLevelHandler($adminService, $messageService);
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
$profileHandler = new ProfileHandler($messageService);
$functionalDirectionHandler = new FunctionalDirectionHandler($messageService);
$helpHandler = new HelpHandler($messageService);

// Тестовые callback-ы
$testCallbacks = [
    'adm_req_clear',
    'adm_req_view_123456789',
    'adm_req_approve_123456789',
    'adm_req_reject_123456789',
    'category_Админка_Заявки',
    'category_Админка',
    'category_Главное'
];

echo "Тест метода canHandle для AdminHandler:\n";
foreach ($testCallbacks as $callback) {
    $canHandle = $adminHandler->canHandle($callback);
    echo "  '{$callback}': " . ($canHandle ? "✓ обрабатывается" : "✗ не обрабатывается") . "\n";
}
echo "\n";

echo "Тест метода canHandle для других обработчиков:\n";
$otherHandlers = [
    'AccessLevelHandler' => $accessLevelHandler,
    'TabelHandler' => $tabelHandler,
    'ProfileHandler' => $profileHandler,
    'FunctionalDirectionHandler' => $functionalDirectionHandler,
    'HelpHandler' => $helpHandler
];

foreach ($testCallbacks as $callback) {
    echo "  '{$callback}':\n";
    foreach ($otherHandlers as $handlerName => $handler) {
        $canHandle = $handler->canHandle($callback);
        if ($canHandle) {
            echo "    {$handlerName}: ✓ обрабатывается\n";
        }
    }
    echo "\n";
}

echo "=== Тест завершен ===\n";