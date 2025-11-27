<?php

require_once 'config.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Test the new supervision keyboard
$messageService = new MessageService();

echo "Testing supervision keyboard...\n";
$supervisionKeyboard = $messageService->createSupervisionKeyboard();

echo "Supervision keyboard created successfully!\n";
echo "Number of rows: " . count($supervisionKeyboard) . "\n";

foreach ($supervisionKeyboard as $index => $row) {
    echo "Row " . ($index + 1) . ": " . count($row) . " buttons\n";
    foreach ($row as $button) {
        echo "  - " . $button['text'] . " (" . $button['callback_data'] . ")\n";
    }
}

echo "\nTesting mobile brigade keyboard for comparison...\n";
$mobileBrigadeKeyboard = $messageService->createMobileBrigadeKeyboard();

echo "Mobile brigade keyboard created successfully!\n";
echo "Number of rows: " . count($mobileBrigadeKeyboard) . "\n";

foreach ($mobileBrigadeKeyboard as $index => $row) {
    echo "Row " . ($index + 1) . ": " . count($row) . " buttons\n";
    foreach ($row as $button) {
        echo "  - " . $button['text'] . " (" . $button['callback_data'] . ")\n";
    }
}

echo "\nTest completed!\n";