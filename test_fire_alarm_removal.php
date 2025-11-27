<?php

// Test script to verify that the "Пожарной сигнализации" button has been removed

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Testing removal of 'Пожарной сигнализации' button...\n";

// Create MessageService instance
$messageService = new MessageService();

// Get the commissioning works keyboard with mobile activities
$keyboard = $messageService->createCommissioningWorksKeyboardWithMobileActivities();

echo "Commissioning works keyboard contains " . count($keyboard) . " rows:\n";

$fireAlarmFound = false;
foreach ($keyboard as $rowIndex => $row) {
    foreach ($row as $buttonIndex => $button) {
        $text = $button['text'];
        $callback = $button['callback_data'];
        
        echo "  Row " . ($rowIndex + 1) . ", Button " . ($buttonIndex + 1) . ": '$text' -> '$callback'\n";
        
        if (strpos($text, 'Пожарной сигнализации') !== false) {
            $fireAlarmFound = true;
            echo "  ❌ FOUND 'Пожарной сигнализации' button!\n";
        }
    }
}

if (!$fireAlarmFound) {
    echo "\n✅ SUCCESS: 'Пожарной сигнализации' button has been successfully removed!\n";
} else {
    echo "\n❌ FAILURE: 'Пожарной сигнализации' button is still present!\n";
}

echo "\nTest completed.\n";