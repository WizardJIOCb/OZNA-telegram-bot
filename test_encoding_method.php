<?php

// Test ProjectManagementHandler ensureUtf8Encoding method
echo "Testing ProjectManagementHandler ensureUtf8Encoding Method\n";
echo "================================================\n";

// Simulate the ensureUtf8Encoding method
function ensureUtf8Encoding($text) {
    echo "Original text: '{$text}'\n";
    echo "Original text length: " . strlen($text) . " bytes\n";
    echo "Original text hex: " . bin2hex($text) . "\n";
    
    // If mbstring is available, use it for encoding conversion
    if (function_exists('mb_convert_encoding')) {
        $result = mb_convert_encoding($text, 'UTF-8', 'auto');
        echo "Converted text: '{$result}'\n";
        echo "Converted text length: " . strlen($result) . " bytes\n";
        echo "Converted text hex: " . bin2hex($result) . "\n";
        return $result;
    }
    
    // If mbstring is not available, return as is
    echo "mbstring not available, returning as is\n";
    return $text;
}

// Test the method with different texts
$testTexts = [
    "🛠️ <b>Управление проектом</b>",
    "Добро пожаловать в раздел управления проектами!",
    "❌ <b>Доступ запрещен</b>\n\nТолько супер-администраторы могут управлять проектами."
];

foreach ($testTexts as $index => $text) {
    echo "\n--- Test Case " . ($index + 1) . " ---\n";
    $result = ensureUtf8Encoding($text);
    echo "Result: '{$result}'\n";
}

echo "\nTest completed!\n";