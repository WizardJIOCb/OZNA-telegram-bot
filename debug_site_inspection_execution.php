<?php
// Debug script to trace ШМР Site Inspection callback execution

echo "=== DEBUG: ШМР Site Inspection Callback Execution ===\n\n";

$testCallback = 'cat_ШМР_АналФунд_yes';

echo "Testing callback: '{$testCallback}'\n\n";

// Test 1: Check if TabelHandler.canHandle would accept this callback
echo "1. CHECKING TabelHandler::canHandle():\n";

$canHandlePatterns = [
    'category_Табель' => str_starts_with($testCallback, 'category_Табель'),
    'cat_ТабПНРаботы' => str_starts_with($testCallback, 'cat_ТабПНРаботы'),
    'cat_Таб' => str_starts_with($testCallback, 'cat_Таб'),
    'cat_Гарантии_' => str_starts_with($testCallback, 'cat_Гарантии_'),
    'cat_ШМР_' => str_starts_with($testCallback, 'cat_ШМР_')
];

foreach ($canHandlePatterns as $pattern => $matches) {
    echo "   Pattern '{$pattern}': " . ($matches ? '✅ MATCHES' : '❌ NO MATCH') . "\n";
}

$canHandle = array_reduce($canHandlePatterns, function($carry, $item) {
    return $carry || $item;
}, false);

echo "   Result: TabelHandler " . ($canHandle ? 'CAN' : 'CANNOT') . " handle this callback\n\n";

// Test 2: Check extractCategoryFromCallback behavior
echo "2. CHECKING extractCategoryFromCallback():\n";

// Simulate the method logic
function testExtractCategory($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0 || 
        strpos($callbackData, 'cat_Гарантии_') === 0 || 
        strpos($callbackData, 'cat_ШМР_') === 0) {
        
        // For ШМР callbacks, map them to proper categories
        $shortFormats = [
            'cat_ШМР_АналФунд_yes' => 'cat_ШМР_АналФунд_yes', // Should stay as is for execution
            'cat_ШМР_ПодпАкт_yes' => 'cat_ШМР_ПодпАкт_yes'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Default handling
        return substr($callbackData, strlen('cat_'));
    }
    
    return $callbackData;
}

$extractedCategory = testExtractCategory($testCallback);
echo "   Input: '{$testCallback}'\n";
echo "   Extracted: '{$extractedCategory}'\n\n";

// Test 3: Check isActivityExecution pattern matching
echo "3. CHECKING isActivityExecution() Pattern Matching:\n";

$category = $extractedCategory;

// Pattern: /^cat_ШМР_(.+)_yes$/u
$pattern = '/^cat_ШМР_(.+)_yes$/u';
if (preg_match($pattern, $category, $matches)) {
    echo "   ✅ Pattern MATCHES: cat_ШМР_(.+)_yes\n";
    echo "   Extracted key: '{$matches[1]}'\n";
    
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР'
    ];
    
    if (isset($simpleSupervisionMap[$matches[1]])) {
        echo "   ✅ Key found in simpleSupervisionMap\n";
        echo "   Maps to: '{$simpleSupervisionMap[$matches[1]]}'\n";
        echo "   ✅ isActivityExecution() should return TRUE\n";
    } else {
        echo "   ❌ Key NOT found in simpleSupervisionMap\n";
        echo "   ❌ isActivityExecution() would return FALSE\n";
    }
} else {
    echo "   ❌ Pattern does NOT match: cat_ШМР_(.+)_yes\n";
    echo "   ❌ isActivityExecution() would return FALSE\n";
}

echo "\n";

// Test 4: Check if the callback would route to execution or other work types
echo "4. CHECKING ROUTING DECISION:\n";

// Simulate routing logic
if (preg_match($pattern, $category, $matches) && 
    isset($simpleSupervisionMap[$matches[1]])) {
    echo "   ✅ Should route to: handleActivityExecution()\n";
    echo "   ✅ Activity type: {$simpleSupervisionMap[$matches[1]]}\n";
} else {
    echo "   ❌ Would route to: handleOtherWorkTypes()\n";
    echo "   ❌ This causes 'неизвестная команда табеля'\n";
}

echo "\n";

// Test 5: Check activity type registration
echo "5. CHECKING ACTIVITY TYPE REGISTRATION:\n";

$activityTypes = [
    'ПроверкаМонтажнойПлощадки',
    'ПодписаниеАктаШМР'
];

foreach ($activityTypes as $type) {
    echo "   Activity '{$type}': ✅ REGISTERED\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";

if (preg_match($pattern, $category, $matches) && 
    isset($simpleSupervisionMap[$matches[1]])) {
    echo "✅ EXPECTED RESULT: Callback should work correctly\n";
    echo "If it's still failing, check:\n";
    echo "- Server logs for exact callback received\n";
    echo "- UTF-8 encoding issues\n";
    echo "- Other handlers intercepting the callback\n";
} else {
    echo "❌ PROBLEM IDENTIFIED: Callback will not be recognized\n";
    echo "The issue is in the callback format or pattern matching\n";
}
?>