<?php

// Test encoding issues
echo "Testing Encoding Issues\n";
echo "=====================\n";

// Test different ways of creating the callback data
$callback1 = 'category_УправлениеПроектом';
$callback2 = 'category_' . 'УправлениеПроектом';
$callback3 = 'category_' . iconv('UTF-8', 'UTF-8', 'УправлениеПроектом');

echo "Callback 1: '{$callback1}'\n";
echo "Callback 1 length: " . strlen($callback1) . "\n";
echo "Callback 1 hex: " . bin2hex($callback1) . "\n\n";

echo "Callback 2: '{$callback2}'\n";
echo "Callback 2 length: " . strlen($callback2) . "\n";
echo "Callback 2 hex: " . bin2hex($callback2) . "\n\n";

echo "Callback 3: '{$callback3}'\n";
echo "Callback 3 length: " . strlen($callback3) . "\n";
echo "Callback 3 hex: " . bin2hex($callback3) . "\n\n";

// Test str_starts_with function
function test_canHandle($callbackData) {
    $prefix = 'category_УправлениеПроектом';
    $result = str_starts_with($callbackData, $prefix);
    echo "Testing: '{$callbackData}'\n";
    echo "Starts with '{$prefix}': " . ($result ? 'YES' : 'NO') . "\n";
    echo "String comparison:\n";
    echo "  Callback length: " . strlen($callbackData) . "\n";
    echo "  Prefix length: " . strlen($prefix) . "\n";
    echo "  Callback substring: '" . substr($callbackData, 0, strlen($prefix)) . "'\n";
    echo "  Prefix: '{$prefix}'\n";
    echo "  Equal: " . ($callbackData === $prefix ? 'YES' : 'NO') . "\n";
    return $result;
}

echo "\n--- Test Results ---\n";
test_canHandle($callback1);
echo "\n";
test_canHandle($callback2);
echo "\n";
test_canHandle($callback3);

echo "\nTest completed!\n";