<?php

require_once __DIR__ . '/custom_autoload.php';

// Test that the "Другая дата" button has been restored
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();

echo "Testing that 'Другая дата' button has been restored:\n\n";

// Test default date selection keyboard
$keyboard = $messageService->createDateSelectionKeyboard();
echo "Default date selection keyboard:\n";
foreach ($keyboard as $rowIndex => $row) {
    foreach ($row as $buttonIndex => $button) {
        echo "  Button: {$button['text']} -> {$button['callback_data']}\n";
    }
}
echo "\n";

// Verify that "Другая дата" button is present
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
echo "Expected: YES\n";

// Test ProjectSelector functionality
$projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();

// Test "Other date" callback
$otherDateCallback = 'category_Табель_ДругаяДата';

echo "\nTesting ProjectSelector with 'Другая дата' callback:\n";
$isDateSelection = $projectSelector->isDateSelection($otherDateCallback);
echo "isDateSelection: " . ($isDateSelection ? 'YES' : 'NO') . "\n";

$extractedDate = $projectSelector->extractDate($otherDateCallback);
echo "extractDate: " . ($extractedDate ?? 'NULL') . "\n";
echo "Expected: NULL (to trigger custom date input)\n";