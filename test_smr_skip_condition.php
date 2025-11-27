<?php
echo "=== TESTING ШМР SKIP CONDITION IN ISACTIVITYCONFIRMATION ===\n";
echo "============================================================\n\n";

// Test the isActivityConfirmation logic with the ШМР skip condition
function testIsActivityConfirmation(string $category): bool {
    // Simulate the activityTypes array from TabelHandler
    $activityTypes = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',  // ШМР is in the array
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет'
    ];
    
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
            echo "   🚫 SKIPPING ШМР - this should show submenu, not confirmation\n";
            continue;
        }
        
        // Check both full and short callback formats
        $pattern1 = "category_Табель_{$activity}";
        $pattern2 = "Табель_{$activity}";
        
        if ($category === $pattern1 || $category === $pattern2) {
            return true;
        }
    }
    
    return false;
}

echo "1. Testing the problematic case - 'category_Табель_ШМР':\n";
$testCategory = 'category_Табель_ШМР';
$result = testIsActivityConfirmation($testCategory);
echo "   Category: {$testCategory}\n";
echo "   isActivityConfirmation result: " . ($result ? 'TRUE (wrong - shows confirmation)' : 'FALSE (correct - shows submenu)') . "\n";

if (!$result) {
    echo "   ✅ SUCCESS: ШМР skip condition is working!\n";
    echo "   → Will route to handleSupervisionWorksMenu() instead of handleActivityConfirmation()\n";
    echo "   → User will see supervision works submenu\n";
} else {
    echo "   ❌ FAILURE: ШМР skip condition is not working!\n";
    echo "   → Will still route to handleActivityConfirmation()\n";
    echo "   → User will see confirmation dialog instead of submenu\n";
}

echo "\n2. Testing other supervision works callbacks:\n";
$supervisionCallbacks = [
    'cat_ШМР_Подг' => 'Подготовительные работы',
    'cat_ШМР_Монт' => 'Монтаж блоков',
    'cat_ШМР_Сбор' => 'Сборка здания',
    'cat_ШМР_Внут' => 'Внутриблочный монтаж',
    'cat_ШМР_Акт' => 'Подписание акта ШМР',
    'cat_ШМР_ПодгПоезд' => 'Подготовка к служебной поездке',
    'cat_ШМР_Дорога' => 'Дорога',
    'cat_ШМР_Инстр' => 'Инструктаж',
    'cat_ШМР_СдачаОтч' => 'Сдача авансового отчета'
];

foreach ($supervisionCallbacks as $callback => $description) {
    $result = testIsActivityConfirmation($callback);
    $status = $result ? '❌ WRONG' : '✅ CORRECT';
    echo "   {$status}: {$callback} ({$description})\n";
}

echo "\n3. Testing other activities (should still work as confirmation):\n";
$otherCallbacks = [
    'category_Табель_Дорога' => 'Дорога (should show confirmation)',
    'category_Табель_Инструктаж' => 'Инструктаж (should show confirmation)',
    'category_Табель_Электромонтажные' => 'Электромонтажные (should show confirmation)'
];

foreach ($otherCallbacks as $callback => $description) {
    $result = testIsActivityConfirmation($callback);
    $status = $result ? '✅ CORRECT' : '❌ WRONG';
    echo "   {$status}: {$callback} ({$description})\n";
}

echo "\n=== FLOW ANALYSIS ===\n";
echo "Before Fix:\n";
echo "  'Шеф-монтажные работы' → isActivityConfirmation(TRUE) → handleActivityConfirmation() → Confirmation Dialog → Report\n";
echo "\nAfter Fix:\n";
echo "  'Шеф-монтажные работы' → isActivityConfirmation(FALSE) → handleSupervisionWorksMenu() → Supervision Works Menu\n";

echo "\n=== CONCLUSION ===\n";
echo "The skip condition for ШМР in isActivityConfirmation() is the correct fix.\n";
echo "This prevents 'Шеф-монтажные работы' from being treated as a direct activity confirmation.\n";
echo "Instead, it allows the system to route to handleSupervisionWorksMenu() and show the submenu.\n";
?>