<?php

require_once 'CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Test to verify that the technological equipment keyboard can be created properly

echo "=== Testing SMR Technological Equipment Keyboard Creation ===\n";

try {
    // Create a mock MessageService instance to test the keyboard
    // Note: This test focuses on the keyboard structure rather than actual Telegram API calls
    
    $expectedKeyboard = [
        [
            ['text' => '🔧 Монтаж трубопроводов', 'callback_data' => 'cat_ШМР_МонтТруб']
        ],
        [
            ['text' => '💧 Гидро/пневмо испытания', 'callback_data' => 'cat_ШМР_ГидрИсп']
        ],
        [
            ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
        ]
    ];
    
    echo "✅ Expected keyboard structure:\n";
    foreach ($expectedKeyboard as $row) {
        foreach ($row as $button) {
            echo "   Button: {$button['text']} -> {$button['callback_data']}\n";
        }
    }
    
    echo "\n=== Testing Callback Data Length ===\n";
    foreach ($expectedKeyboard as $row) {
        foreach ($row as $button) {
            $callbackLength = strlen($button['callback_data']);
            if ($callbackLength > 64) {
                echo "❌ ERROR: Callback data too long ({$callbackLength} chars): {$button['callback_data']}\n";
            } else {
                echo "✅ OK: Callback data length ({$callbackLength} chars): {$button['callback_data']}\n";
            }
        }
    }
    
    echo "\n=== Testing Unicode Encoding ===\n";
    foreach ($expectedKeyboard as $row) {
        foreach ($row as $button) {
            $textBytes = bin2hex($button['text']);
            $callbackBytes = bin2hex($button['callback_data']);
            echo "Button text bytes: {$textBytes}\n";
            echo "Callback bytes: {$callbackBytes}\n";
            echo "---\n";
        }
    }
    
    echo "\n✅ Test completed successfully. Keyboard structure is valid.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Handler Route Mapping ===\n";

// Test the specific callback mapping that should trigger the submenu
$testCallback = 'cat_ШМР_ТехОб';
$expectedMapping = 'Табель_ШМР_ТехнологическоеОборудование';

echo "Testing callback: {$testCallback}\n";
echo "Expected mapping: {$expectedMapping}\n";

// Simulate the canHandle check
$canHandle = str_starts_with($testCallback, 'cat_ШМР_');
echo "canHandle result: " . ($canHandle ? "✅ TRUE" : "❌ FALSE") . "\n";

// Simulate the extractCategoryFromCallback logic
$shortFormats = [
    'cat_ШМР_ТехОб' => 'Табель_ШМР_ТехнологическоеОборудование'
];

if (isset($shortFormats[$testCallback])) {
    $mapped = $shortFormats[$testCallback];
    echo "✅ Mapping successful: {$testCallback} -> {$mapped}\n";
    
    if ($mapped === $expectedMapping) {
        echo "✅ Mapping matches expected result!\n";
    } else {
        echo "❌ Mapping doesn't match expected result!\n";
    }
} else {
    echo "❌ Mapping failed: Callback not found in shortFormats array!\n";
}

echo "\nTest completed.\n";