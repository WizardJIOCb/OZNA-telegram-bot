<?php
// Simple script to create a test to verify the commissioning works button behavior

echo "=== Analyzing Commissioning Works Button Flow ===\n\n";

// Step 1: Check button creation
echo "1. Button Creation in MessageService::createCommissioningKeyboard():\n";
echo "   Button text: '⚙️ Пусконаладочные работы'\n";
echo "   Callback data: 'cat_ТабПНРаботы'\n";
echo "   Length: " . strlen('cat_ТабПНРаботы') . " bytes (OK - under 64 byte limit)\n\n";

// Step 2: Check TabelHandler.canHandle
echo "2. TabelHandler.canHandle() Check:\n";
$callbackData = 'cat_ТабПНРаботы';
$canHandle = str_starts_with($callbackData, 'category_Табель') || str_starts_with($callbackData, 'cat_ТабПНРаботы');
echo "   Callback: {$callbackData}\n";
echo "   Can handle: " . ($canHandle ? 'YES' : 'NO') . "\n\n";

// Step 3: Check category extraction
echo "3. Category Extraction:\n";
if (strpos($callbackData, 'category_') === 0) {
    $category = substr($callbackData, strlen('category_'));
    echo "   Method: category_ format\n";
    echo "   Extracted: {$category}\n";
} elseif (strpos($callbackData, 'cat_Таб') === 0) {
    $category = substr($callbackData, strlen('cat_'));
    echo "   Method: short format (cat_Таб)\n";
    echo "   Extracted: {$category}\n";
} else {
    echo "   Method: unknown\n";
    $category = 'UNKNOWN';
}

// Step 4: Check handleOtherWorkTypes logic
echo "\n4. handleOtherWorkTypes() Processing:\n";
echo "   Category passed: '{$category}'\n";
echo "   Matches 'ТабПНРаботы': " . ($category === 'ТабПНРаботы' ? 'YES' : 'NO') . "\n";

// Step 5: Identify the likely issue
echo "\n5. ISSUE IDENTIFICATION:\n";
if ($category === 'ТабПНРаботы') {
    echo "   ✅ Category extraction works correctly\n";
    echo "   ✅ Should match condition in handleOtherWorkTypes\n";
    echo "   \n";
    echo "   LIKELY ISSUES:\n";
    echo "   1. Missing mapping in extractCategoryFromCallback method\n";
    echo "   2. handleOtherWorkTypes condition ordering problem\n";
    echo "   3. Conversation state interfering with flow\n";
    echo "   4. Method routing in handle() method\n";
} else {
    echo "   ❌ Category extraction failed!\n";
}

echo "\n6. RECOMMENDED SOLUTION:\n";
echo "   Since we already have the handler for 'ТабПНРаботы' in handleOtherWorkTypes,\n";
echo "   but the button doesn't work, the issue is likely in the callback routing.\n";
echo "   The fix should ensure the callback 'cat_ТабПНРаботы' properly maps to\n";
echo "   category 'ТабПНРаботы' and gets routed to handleCommissioningWorksMenu().\n";

echo "\n=== Analysis Complete ===\n";
?>