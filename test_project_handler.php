<?php

// Test ProjectManagementHandler canHandle method
echo "Testing ProjectManagementHandler canHandle method...\n";

// Simulate the canHandle logic from ProjectManagementHandler
function canHandle($callbackData) {
    return str_starts_with($callbackData, 'category_УправлениеПроектом');
}

// Test cases
$testCases = [
    'category_УправлениеПроектом' => true,
    'category_УправлениеПроектом_Персонал' => true,
    'category_Админка' => false,
    'personnel_main' => false
];

echo "\nTesting canHandle method:\n";
foreach ($testCases as $input => $expected) {
    $result = canHandle($input);
    if ($result === $expected) {
        echo "✅ '{$input}' -> " . ($result ? 'true' : 'false') . "\n";
    } else {
        echo "❌ '{$input}' -> " . ($result ? 'true' : 'false') . " (expected: " . ($expected ? 'true' : 'false') . ")\n";
    }
}

echo "\nTest completed!\n";