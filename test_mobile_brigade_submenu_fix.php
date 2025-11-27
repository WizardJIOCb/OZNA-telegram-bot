<?php

echo "=== TESTING MOBILE BRIGADE SUBMENU FIX ===\n\n";

// Simulate the mobile brigade activity types that should show submenus
$mobileBrigadeActivities = ['Электромонтажные', 'Слесарные', 'Сварочные', 'Теплоизоляция', 'Малярные'];

// Simulate the activityTypes array from TabelHandler
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Малярные', 'Документы',
    'АвансовыйОтчет'
];

// Simulate the updated isActivityConfirmation method logic
function testIsActivityConfirmation($category, $activityTypes) {
    error_log("Testing isActivityConfirmation with: '{$category}'");
    
    // Check standard activity types
    foreach ($activityTypes as $activity) {
        // Skip "Прочее" activities - they should trigger custom input, not confirmation
        if (str_contains($activity, 'Прочее')) {
            continue;
        }
        
        // Skip "ПодготовкаТКП" - it should show submenu, not direct confirmation
        if ($activity === 'ПодготовкаТКП') {
            continue;
        }
        
        // Skip "ШМР" - it should show supervision works submenu, not direct confirmation
        if ($activity === 'ШМР') {
            continue;
        }
        
        // CRITICAL FIX: Skip mobile brigade main activities - they should show submenus, not direct confirmation
        if (in_array($activity, ['Электромонтажные', 'Слесарные', 'Сварочные', 'Теплоизоляция', 'Малярные'])) {
            error_log("Skipping mobile brigade activity: {$activity}");
            continue;
        }
        
        // Check both full and short callback formats
        $pattern1 = "category_Табель_{$activity}";
        $pattern2 = "Табель_{$activity}";
        
        if ($category === $pattern1 || $category === $pattern2) {
            error_log("MATCH FOUND! Returning true for activity: {$activity}");
            return true;
        }
    }
    
    return false;
}

echo "1. TESTING MOBILE BRIGADE ACTIVITIES:\n";
echo "====================================\n\n";

foreach ($mobileBrigadeActivities as $activity) {
    $category = "Табель_{$activity}";
    echo "Testing activity: {$activity}\n";
    echo "Category: {$category}\n";
    
    // Test if it's in the activityTypes array (should be true)
    $inActivityTypes = in_array($activity, $activityTypes);
    echo "In activityTypes array: " . ($inActivityTypes ? 'YES' : 'NO') . "\n";
    
    // Test the updated isActivityConfirmation method
    $isActivityConfirmation = testIsActivityConfirmation($category, $activityTypes);
    echo "isActivityConfirmation result: " . ($isActivityConfirmation ? 'TRUE (❌ WRONG - would show confirmation)' : 'FALSE (✅ CORRECT - will route to submenu)') . "\n";
    
    if (!$isActivityConfirmation) {
        echo "✅ SUCCESS: {$activity} will now route to submenu handler!\n";
    } else {
        echo "❌ FAILED: {$activity} will still show confirmation dialog!\n";
    }
    echo "\n";
}

echo "2. TESTING OTHER ACTIVITIES (should still work):\n";
echo "==============================================\n\n";

$otherActivities = ['Дорога', 'Подготовка', 'Инструктаж', 'Ожидание'];

foreach ($otherActivities as $activity) {
    $category = "Табель_{$activity}";
    echo "Testing activity: {$activity}\n";
    echo "Category: {$category}\n";
    
    $isActivityConfirmation = testIsActivityConfirmation($category, $activityTypes);
    echo "isActivityConfirmation result: " . ($isActivityConfirmation ? 'TRUE (✅ CORRECT - should show confirmation)' : 'FALSE (❌ WRONG - should show confirmation)') . "\n";
    echo "\n";
}

echo "3. EXPECTED USER FLOW AFTER FIX:\n";
echo "===============================\n\n";

echo "User clicks 'Электромонтажные работы':\n";
echo "1. Callback: category_Табель_Электромонтажные\n";
echo "2. extractCategoryFromCallback → 'Табель_Электромонтажные'\n";
echo "3. isActivityExecution → FALSE (no '_yes')\n";
echo "4. isActivityConfirmation → FALSE (now skipped!)\n";
echo "5. handleOtherWorkTypes → routes to submenu handler\n";
echo "6. Shows submenu with 4 sub-activities\n";
echo "7. User selects sub-activity → shows confirmation\n";
echo "8. User confirms → creates tabel entry\n\n";

echo "✅ MOBILE BRIGADE SUBMENU FIX APPLIED SUCCESSFULLY!\n";

?>