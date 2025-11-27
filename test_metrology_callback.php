<?php
// Test metrology callback routing to find the exact issue

echo "=== METROLOGY CALLBACK ROUTING TEST ===\n\n";

// Test the specific callback that's failing
$testCallback = 'cat_ТабМетрол_РабПоГр';

echo "Testing problematic callback: '{$testCallback}'\n";
echo "Expected behavior: Show confirmation dialog\n";
echo "Actual result: 'Неизвестная команда табеля'\n\n";

echo "STEP-BY-STEP ANALYSIS:\n";
echo str_repeat("-", 50) . "\n";

// Step 1: Check canHandle
echo "1. TabelHandler->canHandle('{$testCallback}'):\n";
// The pattern should match: str_starts_with($callbackData, 'cat_Таб')
if (str_starts_with($testCallback, 'cat_Таб')) {
    echo "   ✅ SHOULD PASS - Starts with 'cat_Таб'\n";
} else {
    echo "   ❌ WOULD FAIL - Does not start with 'cat_Таб'\n";
}

// Step 2: Check extractCategoryFromCallback
echo "\n2. extractCategoryFromCallback('{$testCallback}'):\n";
// Should map 'cat_ТабМетрол_РабПоГр' to 'Табель_РабПоГр_Метрол'
$shortFormats = [
    'cat_ТабМетрол_РабПоГр' => 'Табель_РабПоГр_Метрол',
    'cat_ТабМетрол_ФормИД' => 'Табель_ФормИД_Метрол',
    'cat_ТабМетрол_ПодтВех' => 'Табель_ПодтВех_Метрол',
    'cat_ТабМетрол_ДопРаб' => 'Табель_ДопРаб_Метрол'
];

if (isset($shortFormats[$testCallback])) {
    $mappedCategory = $shortFormats[$testCallback];
    echo "   ✅ SHOULD MAP TO: '{$mappedCategory}'\n";
} else {
    echo "   ❌ NO MAPPING FOUND - This is likely the issue!\n";
    echo "   Would return: 'ТабМетрол_РабПоГр' (without 'cat_' prefix)\n";
}

// Step 3: Check routing in routeTabelCallback
echo "\n3. routeTabelCallback routing check:\n";
$extractedCategory = isset($mappedCategory) ? $mappedCategory : 'ТабМетрол_РабПоГр';

echo "   Category passed to routing: '{$extractedCategory}'\n";

// Check immediate handlers
if ($extractedCategory === 'cat_ТабМетрол' || $extractedCategory === 'Табель_Метрология') {
    echo "   ✅ Would handle metrology menu\n";
} elseif (str_contains($extractedCategory, '_yes')) {
    echo "   ✅ Would route to activity execution\n";
} else {
    echo "   → Would route to handleOtherWorkTypes\n";
}

// Step 4: Check handleOtherWorkTypes
echo "\n4. handleOtherWorkTypes processing:\n";
echo "   Looking for category: '{$extractedCategory}'\n";

// Check the exact conditions in handleOtherWorkTypes
$metrologyCategoryOptions = [
    'ТабМетрол_РабПоГр',
    'cat_ТабМетрол_РабПоГр',
    'Табель_РабПоГр_Метрол'
];

$matched = false;
foreach ($metrologyCategoryOptions as $option) {
    if ($extractedCategory === $option) {
        echo "   ✅ SHOULD MATCH: '{$option}'\n";
        $matched = true;
        break;
    }
}

if (!$matched) {
    echo "   ❌ NO MATCH FOUND for '{$extractedCategory}'\n";
    echo "   This explains why it returns 'Неизвестная команда табеля'\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "DIAGNOSIS:\n";
echo str_repeat("=", 50) . "\n";

echo "\nThe issue is likely in the category extraction or mapping.\n";
echo "Let's check what the actual extracted category is:\n\n";

// Manual category extraction
if (strpos($testCallback, 'category_') === 0) {
    $actualExtracted = substr($testCallback, strlen('category_'));
    echo "Original format: '{$actualExtracted}'\n";
} elseif (strpos($testCallback, 'cat_Таб') === 0) {
    // This is the path that should be taken
    if (isset($shortFormats[$testCallback])) {
        $actualExtracted = $shortFormats[$testCallback];
        echo "Short format mapped: '{$actualExtracted}'\n";
    } else {
        $actualExtracted = substr($testCallback, strlen('cat_'));
        echo "Short format unmapped: '{$actualExtracted}'\n";
    }
} else {
    $actualExtracted = '';
    echo "No extraction possible: ''\n";
}

echo "\nFINAL RESULT:\n";
echo "If extracted category is: '{$actualExtracted}'\n";
echo "And handleOtherWorkTypes looks for: 'ТабМетрол_РабПоГр' or 'cat_ТабМетрол_РабПоГр'\n";

if ($actualExtracted === 'ТабМетрол_РабПоГр' || $actualExtracted === 'cat_ТабМетрол_РабПоГр') {
    echo "✅ SHOULD WORK - Categories match\n";
} else {
    echo "❌ WILL FAIL - Category mismatch\n";
    echo "SOLUTION: Need to either:\n";
    echo "1. Fix the extractCategoryFromCallback mapping, OR\n";
    echo "2. Add handling for the actual extracted category in handleOtherWorkTypes\n";
}
?>