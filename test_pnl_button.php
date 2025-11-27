<?php
// Test script to verify the "Пусконаладочные работы" button functionality

require_once __DIR__ . '/encoding_helper.php';

echo "Testing 'Пусконаладочные работы' button functionality...\n";

// Simulate the callback data that would be sent when the button is pressed
$callbackData = 'category_Табель_ПНЛ';

echo "Original callback data: $callbackData\n";

// Apply encoding fixes
$fixedCallbackData = fixEncoding($callbackData);
echo "After fixEncoding: $fixedCallbackData\n";

// Extract category (remove 'category_' prefix)
$category = substr($fixedCallbackData, strlen('category_'));
echo "Extracted category: $category\n";

// Check if this matches the expected category for ПНЛ
if ($category === 'Табель_ПНЛ') {
    echo "SUCCESS: Category correctly identified as 'Табель_ПНЛ'\n";
    echo "This should trigger handleCommissioningWorksMenu() in TabelHandler\n";
} else {
    echo "FAILURE: Category mismatch. Expected 'Табель_ПНЛ', got '$category'\n";
}

// Test with potentially corrupted data
$corruptedCallbackData = 'category_РўР°Р±РµР»СЊ_РџРќР›';
echo "\nTesting with potentially corrupted data: $corruptedCallbackData\n";

$fixedCorruptedCallbackData = fixEncoding($corruptedCallbackData);
echo "After fixEncoding: $fixedCorruptedCallbackData\n";

$categoryFromCorrupted = substr($fixedCorruptedCallbackData, strlen('category_'));
echo "Extracted category from corrupted data: $categoryFromCorrupted\n";

if ($categoryFromCorrupted === 'Табель_ПНЛ') {
    echo "SUCCESS: Corrupted data correctly fixed to 'Табель_ПНЛ'\n";
} else {
    echo "FAILURE: Corrupted data not correctly fixed. Expected 'Табель_ПНЛ', got '$categoryFromCorrupted'\n";
}

echo "\nTest completed.\n";
?>