<?php

// Debug script to test callback processing in detail

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Detailed Callback Debug Test ===\n\n";

// Include required files
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

// UTF-8 encoding helper function (fallback if mbstring is not available)
function ensureUtf8Encoding($text) {
    // If mbstring is available, use it for encoding conversion
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($text, 'UTF-8', 'auto');
    }
    
    // If mbstring is not available, return as is
    return $text;
}

// Test data
$testCallbacks = [
    'adm_req_clear',
    'adm_req_view_111111111',
    'adm_req_approve_111111111',
    'adm_req_reject_111111111',
    'category_Админка_Заявки'
];

echo "Testing AdminHandler canHandle method:\n";
$adminService = new AdminService(new MessageService());
$adminHandler = new AdminHandler($adminService, new MessageService());

foreach ($testCallbacks as $callback) {
    echo "Callback: '{$callback}'\n";
    $canHandle = $adminHandler->canHandle($callback);
    echo "  canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    // Also test with UTF-8 encoding
    $utf8Callback = ensureUtf8Encoding($callback);
    if ($utf8Callback !== $callback) {
        echo "  UTF-8 version: '{$utf8Callback}'\n";
        $canHandleUtf8 = $adminHandler->canHandle($utf8Callback);
        echo "  canHandle UTF-8: " . ($canHandleUtf8 ? 'YES' : 'NO') . "\n";
    }
    echo "\n";
}

echo "Testing all handlers registration and priority:\n";

// Create services
$messageService = new MessageService();
$databaseService = new DatabaseService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create handler factory
$handlerFactory = new CallbackHandlerFactory();

// Register handlers
$handlerFactory->registerHandler(new AdminHandler($adminService, $messageService));
$handlerFactory->registerHandler(new AccessLevelHandler($adminService, $messageService));
$handlerFactory->registerHandler(new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService));
$handlerFactory->registerHandler(new ProfileHandler($messageService));
$handlerFactory->registerHandler(new FunctionalDirectionHandler($messageService));
$handlerFactory->registerHandler(new HelpHandler($messageService));

echo "\nAll tests completed.\n";
