<?php
// Test the RK fix by simulating isActivityConfirmation with УЗ_РК exclusion

echo "=== RK FIX VERIFICATION ===\n\n";

$category = 'ТабУстрЗамРК';
echo "Testing category: '{$category}'\n\n";

// Simulate activityTypes array
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет'
];

echo "1. TESTING ORIGINAL LOGIC (before fix):\n";
$foundMatch = false;
foreach ($activityTypes as $activity) {
    // Skip "Прочее" activities
    if (strpos($activity, 'Прочее') !== false) {
        continue;
    }
    
    // Skip "ПодготовкаТКП" 
    if ($activity === 'ПодготовкаТКП') {
        continue;
    }
    
    // Check both full and short callback formats
    $pattern1 = "category_Табель_{$activity}";
    $pattern2 = "Табель_{$activity}";
    
    if ($category === $pattern1 || $category === $pattern2) {
        echo "   ❌ MATCH: '{$category}' matches activity '{$activity}' (pattern: '{$pattern2}')\n";
        echo "   This would return TRUE from isActivityConfirmation!\n";
        $foundMatch = true;
        break;
    }
}

if (!$foundMatch) {
    echo "   ✅ No matches found in original logic\n";
}

echo "\n2. TESTING FIXED LOGIC (with УЗ_РК exclusion):\n";
$foundMatchFixed = false;
foreach ($activityTypes as $activity) {
    // Skip "Прочее" activities
    if (strpos($activity, 'Прочее') !== false) {
        continue;
    }
    
    // Skip "ПодготовкаТКП" 
    if ($activity === 'ПодготовкаТКП') {
        continue;
    }
    
    // Skip "УЗ_РК" - NEW FIX
    if ($activity === 'УЗ_РК') {
        echo "   ⚠️  SKIPPING УЗ_РК due to new exclusion rule\n";
        continue;
    }
    
    // Check both full and short callback formats
    $pattern1 = "category_Табель_{$activity}";
    $pattern2 = "Табель_{$activity}";
    
    if ($category === $pattern1 || $category === $pattern2) {
        echo "   ❌ MATCH: '{$category}' matches activity '{$activity}' (pattern: '{$pattern2}')\n";
        echo "   This would return TRUE from isActivityConfirmation!\n";
        $foundMatchFixed = true;
        break;
    }
}

if (!$foundMatchFixed) {
    echo "   ✅ No matches found with УЗ_РК exclusion\n";
}

echo "\n3. CONCLUSION:\n";
if ($foundMatch && !$foundMatchFixed) {
    echo "   ✅ FIX SUCCESSFUL: УЗ_РК exclusion prevents activity confirmation\n";
    echo "   RK callback should now route to handleOtherWorkTypes\n";
    echo "   handleOtherWorkTypes should show RK submenu instead of confirmation\n";
} elseif (!$foundMatch && !$foundMatchFixed) {
    echo "   ℹ️  NO CHANGE: Neither original nor fixed logic matches\n";
    echo "   The problem might be elsewhere in the routing\n";
} else {
    echo "   ❌ UNEXPECTED RESULT: Please verify the logic\n";
}

echo "\n4. EXPECTED FLOW AFTER FIX:\n";
echo "   1. User clicks '🔧 Устранения замечаний РК' → callback: 'cat_ТабУстрЗамРК'\n";
echo "   2. extractCategoryFromCallback maps to: 'ТабУстрЗамРК'\n";
echo "   3. isActivityConfirmation returns FALSE (due to УЗ_РК exclusion)\n";
echo "   4. Routes to handleOtherWorkTypes\n";
echo "   5. handleOtherWorkTypes matches RK condition\n";
echo "   6. Calls handleRKCommentsEliminationMenu\n";
echo "   7. Shows submenu with 6 RK sections\n";