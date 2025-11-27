<?php

echo "Debugging Callback Category Extraction\n";
echo "=====================================\n\n";

// Test the category extraction logic
function simulateExtractCategoryFromCallback($callbackData) {
    echo "Testing callbackData: '{$callbackData}'\n";
    
    // This simulates the logic that should be in TabelHandler
    // Check if it starts with 'cat_' (short format)
    if (strpos($callbackData, 'cat_') === 0) {
        // Remove 'cat_' prefix to get the category
        $category = substr($callbackData, 4); // Remove 'cat_' (4 characters)
        echo "  Short format detected: category = '{$category}'\n";
        return $category;
    }
    
    // Check if it starts with 'category_Табель_' (long format)
    if (strpos($callbackData, 'category_Табель_') === 0) {
        // Remove 'category_Табель_' prefix
        $category = substr($callbackData, 16); // Remove 'category_Табель_' 
        echo "  Long format detected: category = 'Табель_{$category}'\n";
        return 'Табель_' . $category;
    }
    
    echo "  No matching format found\n";
    return $callbackData;
}

echo "Test 1: Electrical Equipment Callback\n";
echo "-------------------------------------\n";
$result1 = simulateExtractCategoryFromCallback('cat_ТабЭлОб');
echo "Expected: 'ТабЭлОб'\n";
echo "Result: '{$result1}'\n";
echo "Match: " . ($result1 === 'ТабЭлОб' ? 'YES' : 'NO') . "\n\n";

echo "Test 2: Automation Equipment Callback\n";
echo "-------------------------------------\n";
$result2 = simulateExtractCategoryFromCallback('cat_ТабАвтом');
echo "Expected: 'ТабАвтом'\n";
echo "Result: '{$result2}'\n";
echo "Match: " . ($result2 === 'ТабАвтом' ? 'YES' : 'NO') . "\n\n";

echo "Test 3: Long Format Callback\n";
echo "----------------------------\n";
$result3 = simulateExtractCategoryFromCallback('category_Табель_Электрооборудование');
echo "Expected: 'Табель_Электрооборудование'\n";
echo "Result: '{$result3}'\n";
echo "Match: " . ($result3 === 'Табель_Электрооборудование' ? 'YES' : 'NO') . "\n\n";

echo "DIAGNOSIS:\n";
echo "==========\n";

if ($result1 === 'ТабЭлОб') {
    echo "✅ Callback extraction should work correctly for 'cat_ТабЭлОб'\n";
    echo "\nThe issue is likely in one of these areas:\n";
    echo "1. The actual extractCategoryFromCallback method in TabelHandler\n";
    echo "2. The routing logic in handleOtherWorkTypes method\n";
    echo "3. A conflict with another handler or routing condition\n\n";
    
    echo "RECOMMENDATION:\n";
    echo "Check if the TabelHandler's extractCategoryFromCallback method\n";
    echo "properly handles the 'cat_' prefix and extracts 'ТабЭлОб' correctly.\n";
} else {
    echo "❌ Issue found in callback extraction logic\n";
}

echo "\nNOTE: The user is getting 'Не выбран пункт меню' which usually means:\n";
echo "- The callback is being handled by TabelHandler (canHandle returns true)\n";
echo "- But the routing logic doesn't recognize the extracted category\n";
echo "- This suggests the category extraction is working, but the routing is missing\n";

?>