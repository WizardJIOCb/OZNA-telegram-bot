<?php
// Test script to debug the commissioning works button issue

echo "=== Testing Commissioning Works Button Issue ===\n\n";

// The button that's not working
$buttonText = "⚙️ Пусконаладочные работы";
$callbackData = "cat_ТабПНРаботы";

echo "1. Button Analysis:\n";
echo "   Text: {$buttonText}\n";
echo "   Callback data: {$callbackData}\n";
echo "   Text length: " . strlen($buttonText) . " bytes\n";
echo "   Callback length: " . strlen($callbackData) . " bytes\n";
echo "   Text UTF-8 length: " . mb_strlen($buttonText, 'UTF-8') . " characters\n";

echo "\n2. Callback Data Analysis:\n";
echo "   Starts with 'cat_Таб': " . (strpos($callbackData, 'cat_Таб') === 0 ? 'YES' : 'NO') . "\n";
echo "   Starts with 'category_': " . (strpos($callbackData, 'category_') === 0 ? 'YES' : 'NO') . "\n";

// Test how TabelHandler.canHandle would process this
echo "\n3. TabelHandler canHandle simulation:\n";
$canHandle = str_starts_with($callbackData, 'category_Табель') || str_starts_with($callbackData, 'cat_ТабПНРаботы');
echo "   TabelHandler can handle: " . ($canHandle ? 'YES' : 'NO') . "\n";

// Test how the callback would be extracted
echo "\n4. Category extraction simulation:\n";
if (strpos($callbackData, 'category_') === 0) {
    $extractedCategory = substr($callbackData, strlen('category_'));
    echo "   Using category_ format: {$extractedCategory}\n";
} elseif (strpos($callbackData, 'cat_Таб') === 0) {
    echo "   Using short format (cat_Таб): detected\n";
    // This would need a mapping in extractCategoryFromCallback
    $extractedCategory = substr($callbackData, strlen('cat_'));
    echo "   After stripping 'cat_': {$extractedCategory}\n";
} else {
    echo "   No matching pattern found\n";
}

// Test what handleOtherWorkTypes would receive
echo "\n5. handleOtherWorkTypes simulation:\n";
$category = 'ТабПНРаботы'; // What would be passed after stripping 'cat_'
echo "   Category passed to handleOtherWorkTypes: {$category}\n";
echo "   Matches 'ТабПНРаботы': " . ($category === 'ТабПНРаботы' ? 'YES' : 'NO') . "\n";

echo "\n6. Issue Analysis:\n";
echo "   The problem is likely that:\n";
echo "   - The button uses callback 'cat_ТабПНРаботы'\n";
echo "   - TabelHandler.canHandle needs to recognize this callback\n";
echo "   - The category 'ТабПНРаботы' needs proper handling in handleOtherWorkTypes\n";

echo "\n7. Recommended fixes:\n";
echo "   1. Update TabelHandler.canHandle to include 'cat_ТабПНРаботы'\n";
echo "   2. Ensure handleOtherWorkTypes handles 'ТабПНРаботы' category\n";
echo "   3. Consider shortening the button text if callback data is too long\n";

echo "\n=== Test Complete ===\n";
?>