<?php

// Simple test to check the exact callback format
echo "=== TESTING CALLBACK FORMAT ===\n\n";

// Check what the createConfirmationKeyboard actually outputs
function createConfirmationKeyboard(string $confirmCallbackData): array
{
    return [
        [
            ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
            ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
        ]
    ];
}

// Test with the current implementation
$keyboard = createConfirmationKeyboard('новое_прочее_yes');
$yesButtonCallback = $keyboard[0][0]['callback_data'];

echo "Expected: 'новое_прочее_yes'\n";
echo "Actual: '{$yesButtonCallback}'\n";

if ($yesButtonCallback === 'новое_прочее_yes') {
    echo "✅ MATCH - Callback format is correct\n";
} else {
    echo "❌ MISMATCH - Callback format is wrong\n";
}

echo "\nHex comparison:\n";
echo "Expected: " . bin2hex('новое_прочее_yes') . "\n";
echo "Actual: " . bin2hex($yesButtonCallback) . "\n";

echo "\n=== Test Alternative Format ===\n";
$altKeyboard = createConfirmationKeyboard('cat_ТабЭлОб_Проч_yes');
$altCallback = $altKeyboard[0][0]['callback_data'];

echo "Alternative: '{$altCallback}'\n";
echo "Alternative hex: " . bin2hex($altCallback) . "\n";

echo "\n✅ TEST COMPLETED\n";
?>