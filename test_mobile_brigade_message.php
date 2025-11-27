<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/hook.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create message service
$messageService = new MessageService();

// Test chat ID (your ID)
$chatId = 230379668;

// Create mobile brigade keyboard
echo "Creating mobile brigade keyboard...\n";
$keyboard = $messageService->createMobileBrigadeKeyboard();

echo "Keyboard created with " . count($keyboard) . " rows:\n";
foreach ($keyboard as $index => $row) {
    echo "Row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}

// Test sending a message with the keyboard
echo "\nTesting message sending...\n";
$text = '💼 <b>Выберите дату для Мобильной бригады</b>';

try {
    // This would actually send a message, but we'll just test the creation
    echo "Message text: {$text}\n";
    echo "Message would be sent to chat ID: {$chatId}\n";
    echo "Keyboard would be attached to message\n";
    echo "Test completed successfully!\n";
} catch (Exception $e) {
    echo "Error sending message: " . $e->getMessage() . "\n";
}

?>