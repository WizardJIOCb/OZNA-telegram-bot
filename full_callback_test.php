<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/hook.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Conversation;

// Create mock objects for the callback query
$user = new User([
    'id' => 230379668,
    'first_name' => 'Test',
    'username' => 'testuser'
]);

$chat = new Chat([
    'id' => 230379668,
    'type' => 'private'
]);

$message = new Message([
    'message_id' => 1,
    'from' => $user,
    'chat' => $chat,
    'date' => time(),
    'text' => 'Test message'
]);

$callbackQuery = new CallbackQuery([
    'id' => 'test_callback_id',
    'from' => $user,
    'message' => $message,
    'chat_instance' => 'test_chat_instance',
    'data' => 'category_Табель_Мобильная_Бригада'
]);

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create handlers
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
$adminHandler = new AdminHandler($adminService, $messageService);
$accessLevelHandler = new AccessLevelHandler($adminService, $messageService);
$supervisorHandler = new SupervisorHandler($messageService, $adminService);
$profileHandler = new ProfileHandler($messageService);
$functionalDirectionHandler = new FunctionalDirectionHandler($messageService);
$helpHandler = new HelpHandler($messageService);
$mainMenuHandler = new MainMenuHandler($messageService);

// Create CallbackHandlerFactory
$factory = new CallbackHandlerFactory();

// Register handlers in the same order as CallbackqueryCommand
$factory->registerHandler($adminHandler);
$factory->registerHandler($accessLevelHandler);
$factory->registerHandler($supervisorHandler);
$factory->registerHandler($tabelHandler);
$factory->registerHandler($profileHandler);
$factory->registerHandler($functionalDirectionHandler);
$factory->registerHandler($helpHandler);
$factory->registerHandler($mainMenuHandler);

// Create conversation
$conversation = new Conversation(
    $chat->getId(),
    $chat->getId(),
    "/callback"
);

// Test the mobile brigade button callback
$callback_data = 'category_Табель_Мобильная_Бригада';
echo "Testing mobile brigade button callback: {$callback_data}\n";

// Extract category manually to see what should be passed to handler
$category = '';
if (strpos($callback_data, 'category_') === 0) {
    $category = substr($callback_data, strlen('category_'));
}
echo "Extracted category: {$category}\n";

// Test each handler's canHandle method
echo "\nTesting individual handlers:\n";
echo "AdminHandler canHandle: " . ($adminHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";
echo "AccessLevelHandler canHandle: " . ($accessLevelHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";
echo "SupervisorHandler canHandle: " . ($supervisorHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";
echo "TabelHandler canHandle: " . ($tabelHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";
echo "ProfileHandler canHandle: " . ($profileHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";
echo "FunctionalDirectionHandler canHandle: " . ($functionalDirectionHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";
echo "HelpHandler canHandle: " . ($helpHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";
echo "MainMenuHandler canHandle: " . ($mainMenuHandler->canHandle($callback_data) ? 'true' : 'false') . "\n";

// Test the factory handling
echo "\nTesting factory handling:\n";
$result = $factory->handleCallback($callbackQuery, $callback_data, $conversation);
echo "Factory handling result: " . (is_object($result) ? 'ServerResponse object' : 'null') . "\n";

?>