<?php

// Test callback extraction for project management
echo "Testing callback extraction...\n";

// Simulate the CallbackHandlerFactory extraction logic
function extractCategory($callbackData) {
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    return '';
}

// Test cases
$testCases = [
    'category_УправлениеПроектом' => 'УправлениеПроектом',
    'category_УправлениеПроектом_Персонал' => 'УправлениеПроектом_Персонал',
    'category_Админка' => 'Админка'
];

echo "\nTesting category extraction:\n";
foreach ($testCases as $input => $expected) {
    $result = extractCategory($input);
    if ($result === $expected) {
        echo "✅ '{$input}' -> '{$result}'\n";
    } else {
        echo "❌ '{$input}' -> '{$result}' (expected: '{$expected}')\n";
    }
}

echo "\nTest completed!\n";
