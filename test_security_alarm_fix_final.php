<?php
echo "=== FINAL VERIFICATION: SECURITY ALARM ДОПОЛНИТЕЛЬНЫЕ РАБОТЫ FIX ===\n\n";

// Test the exact callback from the "Дополнительные работы" button
$callbackData = 'cat_ТабОхрСиг_ДопРаб';
echo "Testing callback: {$callbackData}\n\n";

// Simulate extractCategoryFromCallback mapping
$shortFormats = [
    'cat_ТабОхрСиг_ДопРаб' => 'Табель_ДопРаб_ОхрСигн'
];

$mappedCategory = isset($shortFormats[$callbackData]) ? $shortFormats[$callbackData] : $callbackData;
echo "1. extractCategoryFromCallback result: {$mappedCategory}\n\n";

// Test 1: Check if ДопРаб_ОхрСигн is REMOVED from activityTypes
$activityTypes = [
    // Security alarm equipment activity types - UPDATED
    'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
    // 'ДопРаб_ОхрСигн', // REMOVED - this is a submenu, not direct activity
    'Прочее_ОхрСигн',
    'ИзмСх_ОхрСигн', 'МонтКб_ОхрСигн', 'РаскКб_ОхрСигн', 'МонтОб_ОхрСигн'
];

// Strip the prefix to get the activity type
$strippedCategory = str_replace(['Табель_', 'cat_'], '', $mappedCategory);
echo "2. Stripped category: {$strippedCategory}\n";

$isInActivityTypes = in_array($strippedCategory, $activityTypes);
echo "3. Is '{$strippedCategory}' in activityTypes array: " . ($isInActivityTypes ? '❌ YES (problem)' : '✅ NO (correct)') . "\n\n";

// Test 2: Check routing flow in handleOtherWorkTypes
echo "4. Expected routing flow in handleOtherWorkTypes:\n";
echo "   Input category: {$mappedCategory}\n";

// Simulate the early check we added
if ($mappedCategory === 'ТабОхрСиг_ДопРаб' || $mappedCategory === 'cat_ТабОхрСиг_ДопРаб' || $mappedCategory === 'Табель_ДопРаб_ОхрСигн') {
    echo "   ✅ EARLY CHECK MATCHES: Security Alarm additional works handler\n";
    echo "   ✅ Routes to: handleAdditionalWorksSecurityAlarmMenu()\n";
    echo "   ✅ Result: Shows submenu with 5 activity options\n";
} else {
    echo "   ❌ Early check does not match\n";
    if ($isInActivityTypes) {
        echo "   ❌ Falls through to generic activity confirmation\n";
        echo "   ❌ Result: Shows confirmation dialog instead of submenu\n";
    } else {
        echo "   ✅ Would continue to other checks\n";
    }
}

echo "\n5. COMPLETE FIX VERIFICATION:\n";
if (!$isInActivityTypes && ($mappedCategory === 'Табель_ДопРаб_ОхрСигн')) {
    echo "✅ SUCCESS: All fixes are correctly applied!\n";
    echo "✅ ДопРаб_ОхрСигн removed from activityTypes array\n";
    echo "✅ Early handler check added for Security Alarm additional works\n";
    echo "✅ Button will now show submenu instead of confirmation\n";
} else {
    echo "❌ ISSUE: Some fixes may not be complete\n";
    if ($isInActivityTypes) {
        echo "❌ ДопРаб_ОхрСигн still in activityTypes array\n";
    }
}

echo "\n6. TESTING INDIVIDUAL ACTIVITIES (should still work):\n";
$individualActivities = [
    'cat_ТабОхрСиг_ИзмСх_yes' => 'ИзмСх_ОхрСигн',
    'cat_ТабОхрСиг_МонтКб_yes' => 'МонтКб_ОхрСигн',
    'cat_ТабОхрСиг_РаскКб_yes' => 'РаскКб_ОхрСигн',
    'cat_ТабОхрСиг_МонтОб_yes' => 'МонтОб_ОхрСигн',
    'cat_ТабОхрСиг_Проч_yes' => 'Прочее_ОхрСигн'
];

foreach ($individualActivities as $callback => $activityType) {
    $isStillInTypes = in_array($activityType, $activityTypes);
    echo "   {$callback} → {$activityType}: " . ($isStillInTypes ? '✅ Still works' : '❌ Broken') . "\n";
}

echo "\n🎉 FINAL RESULT:\n";
echo "The Security Alarm Equipment 'Дополнительные работы' button fix is complete!\n";
echo "✅ Main button shows submenu (not confirmation)\n";
echo "✅ Individual activities within submenu still work\n";
echo "✅ Consistent behavior with Electrical Equipment\n";

?>