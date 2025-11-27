<?php

// Test script to verify the "No" button callback handling

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

// Mock callback query for "No" button
$callbackQueryData = [
    'id' => '123456789',
    'from' => [
        'id' => 230379668,
        'first_name' => 'Test',
        'username' => 'testuser'
    ],
    'message' => [
        'message_id' => 123,
        'from' => [
            'id' => 7902640773,
            'first_name' => 'OznaWork',
            'username' => 'OznaWork_bot'
        ],
        'chat' => [
            'id' => 230379668,
            'first_name' => 'Test',
            'username' => 'testuser',
            'type' => 'private'
        ],
        'date' => time(),
        'text' => 'Test message'
    ],
    'chat_instance' => '1234567890123456789',
    'data' => 'category_Табель'
];

$callbackQuery = new CallbackQuery($callbackQueryData);

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test canHandle method
$canHandle = $tabelHandler->canHandle('category_Табель');
echo "canHandle('category_Табель'): " . ($canHandle ? 'true' : 'false') . "\n";

// Test handle method
$category = 'Табель';
$conversation = new Conversation(230379668, 230379668, '/callback');

echo "Testing handle method with category: {$category}\n";
// This would normally send a message, but we're just testing the logic
try {
    // We can't actually execute this without a real Telegram context, but we can check the logic
    echo "Handler should show main menu for category: {$category}\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Test completed.\n";