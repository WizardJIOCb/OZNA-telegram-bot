<?php

// Test script to verify secondary button functionality

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
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
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;
use Longman\TelegramBot\Conversation;

// Mock classes for testing
class MockCallbackQuery {
    public function getMessage() {
        return new MockMessage();
    }
    
    public function getFrom() {
        return new MockUser();
    }
    
    public function getData() {
        return 'category_Табель_Дорога';
    }
}

class MockMessage {
    public function getChat() {
        return new MockChat();
    }
}

class MockChat {
    public function getId() {
        return 123456789;
    }
}

class MockUser {
    public function getId() {
        return 123456789;
    }
    
    public function getFirstName() {
        return 'Test';
    }
}

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Initialize handler factory
$handlerFactory = new CallbackHandlerFactory();

// Register handlers (order matters for priority)
$handlerFactory->registerHandler(new AdminHandler($adminService, $messageService));
$handlerFactory->registerHandler(new AccessLevelHandler($adminService, $messageService));
$handlerFactory->registerHandler(new SupervisorHandler($messageService, $adminService));
$handlerFactory->registerHandler(new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService));
$handlerFactory->registerHandler(new ProfileHandler($messageService));
$handlerFactory->registerHandler(new FunctionalDirectionHandler($messageService));
$handlerFactory->registerHandler(new HelpHandler($messageService));

// Test callback data
$callbackData = 'category_Табель_Дорога';
$mockCallbackQuery = new MockCallbackQuery();
$conversation = new Conversation(123456789, 123456789, "/callback");

echo "Testing callback data: {$callbackData}\n";

// Handle the callback
try {
    $result = $handlerFactory->handleCallback($mockCallbackQuery, $callbackData, $conversation);
    echo "Callback handled successfully\n";
    echo "Result: " . print_r($result, true) . "\n";
} catch (Exception $e) {
    echo "Error handling callback: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}