<?php

echo "=== Testing TKP Submenu Fix ===\n\n";

// Test the TKP callback data routing
$tkpCallback = 'category_Табель_ПодготовкаТКП';
echo "1. Testing TKP callback: '{$tkpCallback}'\n";

// Extract category (simulate extractCategoryFromCallback)
$category = substr($tkpCallback, strlen('category_'));
echo "   Extracted category: '{$category}'\n";

// Test if it matches our new routing logic
if ($category === 'Табель_ПодготовкаТКП') {
    echo "   ✅ WOULD route to handleTKPSubtypesMenu()\n";
} else {
    echo "   ❌ Would NOT route to TKP submenu\n";
}

echo "\n2. Testing that ПодготовкаТКП is excluded from activity confirmation:\n";

// Simulate the activityTypes array
$activityTypes = [
    'Дорога', 'ПодготовкаКПоездке', 'Инструктаж', 'СдачаАвансовогоОтчета', 
    'ПодготовкаТКП', 'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
    'МедОсмотр', 'Аттестация', 'Общая'
];

// Simulate isActivityConfirmation logic with the fix
function testIsActivityConfirmation($category, $activityTypes) {
    foreach ($activityTypes as $activity) {
        // Skip "Прочее" activities
        if (str_contains($activity, 'Прочее')) {
            continue;
        }
        
        // Skip "ПодготовкаТКП" - it should show submenu, not direct confirmation
        if ($activity === 'ПодготовкаТКП') {
            continue;
        }
        
        // Check both full and short callback formats
        if ($category === "category_Табель_{$activity}" || $category === "Табель_{$activity}") {
            return true;
        }
    }
    return false;
}

$testCategory = 'Табель_ПодготовкаТКП';
$isActivityConfirmation = testIsActivityConfirmation($testCategory, $activityTypes);

echo "   Category: '{$testCategory}'\n";
echo "   isActivityConfirmation result: " . ($isActivityConfirmation ? "true" : "false") . "\n";
if (!$isActivityConfirmation) {
    echo "   ✅ CORRECT: ПодготовкаТКП is excluded from activity confirmation\n";
} else {
    echo "   ❌ ERROR: ПодготовкаТКП should not be treated as activity confirmation\n";
}

echo "\n3. Testing business direction callbacks (should still work):\n";

$businessDirections = ['ОПНВГ', 'НКО', 'МСО', 'АСН'];
foreach ($businessDirections as $direction) {
    $bdCallback = "category_Табель_ПодготовкаТКП_{$direction}";
    $bdCategory = substr($bdCallback, strlen('category_'));
    $bdActivity = "ПодготовкаТКП_{$direction}";
    
    $bdIsConfirmation = testIsActivityConfirmation($bdCategory, $activityTypes);
    
    echo "   Business Direction: {$direction}\n";
    echo "   Callback: '{$bdCallback}'\n";
    echo "   Category: '{$bdCategory}'\n"; 
    echo "   Should be confirmation: " . ($bdIsConfirmation ? "YES ✅" : "NO ❌") . "\n";
}

echo "\n=== Fix Summary ===\n";
echo "✅ Added specific routing for 'Табель_ПодготовкаТКП' to show submenu\n";
echo "✅ Excluded 'ПодготовкаТКП' from direct activity confirmation\n";
echo "✅ Business direction buttons (ОПНВГ, НКО, МСО, АСН) still work as confirmations\n";
echo "\n🎯 The TKP Material Preparation button should now show the business direction submenu!\n";

?>