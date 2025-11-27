<?php

require_once __DIR__ . '/custom_autoload.php';

// Test the updated MessageService date selection keyboard
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();

echo "Testing updated date selection keyboard:\n\n";

// Test default date selection keyboard
$keyboard = $messageService->createDateSelectionKeyboard();
echo "Default date selection keyboard:\n";
foreach ($keyboard as $rowIndex => $row) {
    foreach ($row as $buttonIndex => $button) {
        echo "  Button: {$button['text']} -> {$button['callback_data']}\n";
    }
}
echo "\n";

// Test with custom prefix
$keyboardWithPrefix = $messageService->createDateSelectionKeyboard('category_Табель_ПНЛ');
echo "Date selection keyboard with custom prefix:\n";
foreach ($keyboardWithPrefix as $rowIndex => $row) {
    foreach ($row as $buttonIndex => $button) {
        echo "  Button: {$button['text']} -> {$button['callback_data']}\n";
    }
}
echo "\n";

// Verify that "Другая дата" button is no longer present
$hasOtherDate = false;
foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Другая') !== false) {
            $hasOtherDate = true;
            break 2;
        }
    }
}

echo "Contains 'Другая дата' button: " . ($hasOtherDate ? 'YES' : 'NO') . "\n";
echo "Expected: NO\n";