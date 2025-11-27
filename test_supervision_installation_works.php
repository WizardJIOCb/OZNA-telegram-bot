<?php
// Test script to verify that the supervision installation works back button is correctly configured

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create an instance of MessageService
$messageService = new MessageService();

echo "Testing supervision installation works back button...\n\n";

// Call the method for supervision installation works keyboard
$keyboard = $messageService->createSupervisionInstallationWorksKeyboard();

echo "Supervision Installation Works Keyboard:\n";
foreach ($keyboard as $index => $row) {
    $button = $row[0]; // First button in each row
    echo "Row " . ($index + 1) . ": {$button['text']} -> {$button['callback_data']}\n";
}

// Check the last row which should contain the back button
$lastRow = end($keyboard);
$backButton = $lastRow[0]; // First button in the last row

echo "\nBack button details:\n";
echo "Text: {$backButton['text']}\n";
echo "Callback: {$backButton['callback_data']}\n";

// Verify that the callback data is correct
if ($backButton['callback_data'] === 'Табель_ШМ_Back') {
    echo "\n✅ PASS: Back button correctly configured for Supervision Installation Works\n";
    echo "The 'Назад' button will correctly return to the supervision department menu.\n";
} else {
    echo "\n❌ FAIL: Back button incorrectly configured.\n";
    echo "Expected 'Табель_ШМ_Back', got '{$backButton['callback_data']}'\n";
}

echo "\nTest completed.\n";