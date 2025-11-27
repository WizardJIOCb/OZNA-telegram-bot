<?php
/**
 * Debug script to trace why ШМР electrical equipment "Прочее" is bypassing custom input
 */

echo "=== DEBUG: ШМР Electrical Equipment 'Прочее' Routing Issue ===\n\n";

// Simulate the activity types array
$activityTypes = [
    'ШМР_Прочее_Электрооборудование',
    'ШМР_Прочее_ОтоплениеИВентиляция',
    'ШМР_Прочее_ПожарнаяСигнализация',
    'ШМР_Прочее_ОхраннаяСигнализация',
    'ШМР_Прочее_Автоматизация',
    'ШМР_Прочее_Метрология',
    'Прочее_Электрооборудование',
    'Прочее_Автоматизация',
];

// Incoming category after mapping
$category = 'Табель_ШМР_Прочее_Электрооборудование';

echo "Incoming category: '{$category}'\n\n";
echo "Testing isActivityConfirmation logic:\n";
echo str_repeat("-", 70) . "\n";

$matched = false;

foreach ($activityTypes as $activity) {
    echo "\nChecking activity: '{$activity}'\n";
    
    // Skip "Прочее" activities - they should trigger custom input, not confirmation
    if (str_contains($activity, 'Прочее')) {
        echo "  ✅ SKIPPED: Activity contains 'Прочее'\n";
        continue;
    }
    
    // Check both full and short callback formats
    $pattern1 = "category_Табель_{$activity}";
    $pattern2 = "Табель_{$activity}";
    
    echo "  Testing patterns:\n";
    echo "    Pattern 1: '{$pattern1}'\n";
    echo "    Pattern 2: '{$pattern2}'\n";
    
    if ($category === $pattern1 || $category === $pattern2) {
        echo "  🚨 MATCH FOUND! Would return true for activity: {$activity}\n";
        $matched = true;
        break;
    } else {
        echo "  ❌ No match\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";

if ($matched) {
    echo "❌ PROBLEM: isActivityConfirmation would return TRUE\n";
    echo "   This means the category is treated as a confirmation request\n";
    echo "   instead of routing to the custom input handler!\n";
} else {
    echo "✅ OK: isActivityConfirmation would return FALSE\n";
    echo "   This means the category would route to handleOtherWorkTypes\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "\nROOT CAUSE ANALYSIS:\n";
echo "The skip logic on line 1523 IS working correctly.\n";
echo "All 'Прочее' activities are being skipped.\n";
echo "So isActivityConfirmation should return FALSE.\n";
echo "\nBUT - there might be ANOTHER check that's matching earlier!\n";
echo "Let me check for other patterns that might match...\n\n";

// Check ШМР technological equipment pattern from line 1582
if (preg_match('/^cat_ШМР_(.+)_(РабГр|ПодтВех)$/u', 'cat_ШМР_ЭлектрОб_Проч', $matches)) {
    echo "🚨 FOUND IT! The callback 'cat_ШМР_ЭлектрОб_Проч' matches the ШМР tech equipment pattern!\n";
    echo "   Match 1: {$matches[1]}\n";
    echo "   Match 2: {$matches[2]}\n";
} else {
    echo "✅ OK: The callback 'cat_ШМР_ЭлектрОб_Проч' does NOT match the ШМР tech equipment pattern.\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "\nCONCLUSION:\n";
echo "The issue is NOT in isActivityConfirmation.\n";
echo "The problem must be in the routing BEFORE isActivityConfirmation is called.\n";
echo "Let's check the extractCategoryFromCallback mapping...\n\n";

// Simulate extractCategoryFromCallback
$callbackData = 'cat_ШМР_ЭлектрОб_Проч';
$shortFormats = [
    'cat_ШМР_ЭлектрОб_Проч' => 'Табель_ШМР_Прочее_Электрооборудование',
];

if (isset($shortFormats[$callbackData])) {
    $extractedCategory = $shortFormats[$callbackData];
    echo "✅ Mapping found: '{$callbackData}' => '{$extractedCategory}'\n";
} else {
    echo "❌ No mapping found for '{$callbackData}'\n";
    $extractedCategory = $callbackData;
}

echo "\nFINAL TRACE:\n";
echo "1. User clicks: {$callbackData}\n";
echo "2. Extracted category: {$extractedCategory}\n";
echo "3. Activity types array contains: ШМР_Прочее_Электрооборудование\n";
echo "4. isActivityConfirmation skips all 'Прочее' activities\n";
echo "5. Should route to handleOtherWorkTypes\n";
echo "6. handleOtherWorkTypes should have handler at line 3075\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "\nNEXT STEP: Check if there's EARLY routing that intercepts before isActivityConfirmation!\n";
echo "Look at lines 591-594 in TabelHandler.php - the confirmation callback handler!\n";
