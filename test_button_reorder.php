<?php

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Testing Button Order Change ===\n\n";

$messageService = new MessageService();
$keyboard = $messageService->createCommissioningWorksKeyboard();

echo "Current button order in commissioning works keyboard:\n";
echo "====================================================\n";

$targetButtons = ['Метрология', 'Режимная наладка', 'Комплексное опробование'];
$foundButtons = [];

foreach ($keyboard as $index => $row) {
    foreach ($row as $button) {
        $buttonText = $button['text'];
        
        // Check if this is one of our target buttons
        foreach ($targetButtons as $target) {
            if (strpos($buttonText, $target) !== false) {
                $foundButtons[] = [
                    'position' => $index + 1,
                    'text' => $buttonText,
                    'callback' => $button['callback_data']
                ];
                break;
            }
        }
        
        echo "Row " . ($index + 1) . ": " . $buttonText . "\n";
        echo "  Callback: " . $button['callback_data'] . "\n";
    }
}

echo "\n=== Target Button Analysis ===\n";
echo "Found buttons in order:\n";

foreach ($foundButtons as $i => $buttonInfo) {
    echo ($i + 1) . ". Position " . $buttonInfo['position'] . ": " . $buttonInfo['text'] . "\n";
    echo "   Callback: " . $buttonInfo['callback'] . "\n";
}

// Check if the order is correct
$expectedOrder = ['Метрология', 'Режимная наладка', 'Комплексное опробование'];
$actualOrder = [];

foreach ($foundButtons as $buttonInfo) {
    foreach ($expectedOrder as $expected) {
        if (strpos($buttonInfo['text'], $expected) !== false) {
            $actualOrder[] = $expected;
            break;
        }
    }
}

echo "\nExpected order: " . implode(' → ', $expectedOrder) . "\n";
echo "Actual order:   " . implode(' → ', $actualOrder) . "\n";

if ($actualOrder === $expectedOrder) {
    echo "\n✅ SUCCESS: Button order is correct!\n";
    echo "After 'Метрология' comes 'Режимная наладка', then 'Комплексное опробование'\n";
} else {
    echo "\n❌ FAILURE: Button order is incorrect\n";
}

echo "\n=== Test completed! ===\n";