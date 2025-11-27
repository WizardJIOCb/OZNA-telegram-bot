<?php

// Detailed test for ProjectManagementHandler
echo "Detailed ProjectManagementHandler Test\n";
echo "=====================================\n";

// Simulate the canHandle logic from ProjectManagementHandler
function canHandle($callbackData) {
    $result = str_starts_with($callbackData, 'category_УправлениеПроектом');
    echo "Testing callback: '{$callbackData}'\n";
    echo "Starts with 'category_УправлениеПроектом': " . ($result ? 'YES' : 'NO') . "\n";
    echo "String comparison details:\n";
    echo "  Callback length: " . strlen($callbackData) . "\n";
    echo "  Expected prefix: 'category_УправлениеПроектом'\n";
    echo "  Expected prefix length: " . strlen('category_УправлениеПроектом') . "\n";
    echo "  Substring: '" . substr($callbackData, 0, strlen('category_УправлениеПроектом')) . "'\n";
    return $result;
}

// Test cases
$testCases = [
    'category_УправлениеПроектом',
    'category_УправлениеПроектом_Персонал',
    'category_Админка',
    'personnel_main'
];

foreach ($testCases as $testCase) {
    echo "\n--- Test Case ---\n";
    $result = canHandle($testCase);
    echo "Result: " . ($result ? 'HANDLED' : 'NOT HANDLED') . "\n";
}

echo "\nTest completed!\n";