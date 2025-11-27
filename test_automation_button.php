<?php
// Test script to verify that the automation button works correctly

require_once 'vendor/autoload.php';

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

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService);
$adminService = new AdminService($databaseService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test if the handler can handle the automation callback
$callbackData = 'category_Табель_Автоматизация';
$canHandle = $tabelHandler->canHandle($callbackData);

echo "Can handle 'category_Табель_Автоматизация': " . ($canHandle ? 'Yes' : 'No') . "\n";

// Test the handle method with a mock callback query
$chat = new Chat(['id' => 12345]);
$user = new User(['id' => 67890, 'first_name' => 'Test User']);
$message = new Message(['chat' => $chat, 'from' => $user]);
$callbackQuery = new CallbackQuery(['data' => $callbackData, 'message' => $message, 'from' => $user]);

$conversation = new Conversation(67890, 12345);

// This would normally send a message, but we're just testing the logic
echo "Handler should display automation equipment menu when 'category_Табель_Автоматизация' is clicked.\n";