<?php

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create a MessageService instance
$messageService = new MessageService();

// Generate the Mobile Brigade keyboard
$keyboard = $messageService->createMobileBrigadeKeyboard();

// Display the keyboard
echo "Mobile Brigade Keyboard:\n";
echo "======================\n";

foreach ($keyboard as $rowIndex => $row) {
    echo "Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $button) {
        echo "  - Text: " . $button['text'] . "\n";
        echo "    Callback: " . $button['callback_data'] . "\n";
    }
    echo "\n";
}