<?php
// Test to verify the exact callback data and string matching

echo "=== Testing Exact Mobile Brigade Callback Data ===\n\n";

// The callback data that should be sent by the mobile brigade button
$expectedCallbackData = 'category_Табель_Мобильная_Бригада';

// Let's check the exact characters
echo "Expected callback data: " . $expectedCallbackData . "\n";
echo "Length: " . strlen($expectedCallbackData) . " bytes\n";
echo "UTF-8 length: " . mb_strlen($expectedCallbackData, 'UTF-8') . " characters\n";

// Let's check each character
echo "Character by character:\n";
for ($i = 0; $i < strlen($expectedCallbackData); $i++) {
    $char = $expectedCallbackData[$i];
    echo "  Position $i: '" . $char . "' (ASCII: " . ord($char) . ")\n";
}

// Let's also check for any hidden characters
echo "\nHex representation:\n";
echo bin2hex($expectedCallbackData) . "\n";

// Now let's test string matching
$category = substr($expectedCallbackData, strlen('category_'));
echo "\nExtracted category: " . $category . "\n";

// Test the conditions in handleOtherWorkTypes
$condition1 = ($category === 'Табель_Мобильная_Бригада');
$condition2 = ($category === 'Табель_МобБриг');

echo "\nCondition tests:\n";
echo "Category === 'Табель_Мобильная_Бригада': " . ($condition1 ? 'TRUE' : 'FALSE') . "\n";
echo "Category === 'Табель_МобБриг': " . ($condition2 ? 'TRUE' : 'FALSE') . "\n";

// Test the combined condition
$mobileBrigadeCondition = ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг');
echo "\nCombined mobile brigade condition: " . ($mobileBrigadeCondition ? 'TRUE' : 'FALSE') . "\n";

// Test TabelHandler canHandle
$tabelCanHandle = str_starts_with($expectedCallbackData, 'category_Табель');
echo "TabelHandler canHandle test: " . ($tabelCanHandle ? 'TRUE' : 'FALSE') . "\n";

echo "\n=== Conclusion ===\n";
if ($mobileBrigadeCondition && $tabelCanHandle) {
    echo "✓ All conditions pass - mobile brigade button should work correctly\n";
} else {
    echo "✗ Some conditions fail - there may be an issue with string matching\n";
}