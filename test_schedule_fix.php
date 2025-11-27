<?php

require_once 'config.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Create CallbackHandlerFactory instance
$callbackFactory = new CallbackHandlerFactory();
$callbackFactory->registerHandler($tabelHandler);

echo "Testing the fix for 'Работы по графику' button...\n";

// Test the specific callback that was causing issues
$testCallback = 'cat_ТабЭлОб_РабПоГр';  // "Работы по графику" button

echo "\n--- Testing: {$testCallback} ---\n";

// Test canHandle directly on TabelHandler
$canHandle = $tabelHandler->canHandle($testCallback);
echo "TabelHandler->canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";

// Test through CallbackHandlerFactory
$mockCallbackQuery = new CallbackQuery([
    'id' => 'test',
    'from' => [
        'id' => 123456,
        'first_name' => 'Test',
        'username' => 'testuser'
    ],
    'message' => [
        'message_id' => 1,
        'from' => [
            'id' => 123456,
            'first_name' => 'Test',
            'username' => 'testuser'
        ],
        'chat' => [
            'id' => 123456,
            'type' => 'private'
        ],
        'date' => time(),
        'text' => 'Test message'
    ],
    'chat_instance' => 'test',
    'data' => $testCallback
]);

$mockConversation = new Conversation(123456, 123456);

// Capture the output to see what happens
ob_start();
$result = $callbackFactory->handleCallback($mockCallbackQuery, $testCallback, $mockConversation);
$output = ob_get_clean();

echo "CallbackHandlerFactory result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'SUCCESS' : 'FAILED') : 'UNKNOWN') . "\n";
echo "Factory output log:\n{$output}\n";

echo "\nTest completed. The fix should now properly handle the 'Работы по графику' button.\n";