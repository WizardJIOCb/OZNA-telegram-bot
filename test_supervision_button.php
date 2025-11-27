<?php
// Test script to simulate the "Отдел ШМР" button click

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
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

// Create mock objects
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Create mock callback query for "Отдел ШМР" button
$chat = new Chat(['id' => 123456789, 'type' => 'private']);
$user = new User(['id' => 987654321, 'is_bot' => false, 'first_name' => 'Test']);
$message = new Message([
    'message_id' => 1,
    'from' => $user,
    'chat' => $chat,
    'date' => time(),
    'text' => 'Test message'
]);

$callbackQuery = new CallbackQuery([
    'id' => '123456789',
    'from' => $user,
    'message' => $message,
    'chat_instance' => 'test',
    'data' => 'category_Табель_Шеф_монтаж'
]);

// Create mock conversation
$conversation = new Conversation($user->getId(), $chat->getId(), '/callback');

// Test if TabelHandler can handle the callback
$canHandle = $tabelHandler->canHandle('category_Табель_Шеф_монтаж');
echo "Can handle: " . ($canHandle ? 'true' : 'false') . "\n";

// Test handling the callback
if ($canHandle) {
    try {
        $result = $tabelHandler->handle($callbackQuery, 'Табель_Шеф_монтаж', $conversation);
        echo "Handle result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'success' : 'failed') : 'unknown') . "\n";
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "TabelHandler cannot handle the callback\n";
}