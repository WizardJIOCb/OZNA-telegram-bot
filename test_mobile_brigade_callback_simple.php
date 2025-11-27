<?php

echo "=== SIMPLE TEST: Mobile Brigade Callback Flow ===\n\n";

// Simulate the exact callback that should be sent
$buttonCallback = 'category_Табель_Электромонтажные';
echo "1. Button sends: '{$buttonCallback}'\n\n";

// Step 1: extractCategoryFromCallback
echo "2. extractCategoryFromCallback:\n";
if (strpos($buttonCallback, 'category_') === 0) {
    $category = substr($buttonCallback, strlen('category_'));
    echo "   Strips 'category_' prefix\n";
    echo "   Result: '{$category}'\n\n";
} else {
    echo "   ERROR: callback doesn't start with 'category_'\n";
    exit;
}

// Step 2: isActivityExecution check
echo "3. isActivityExecution check:\n";
$hasYesSuffix = str_contains($category, '_yes');
echo "   Contains '_yes': " . ($hasYesSuffix ? 'YES' : 'NO') . "\n";
echo "   Result: " . ($hasYesSuffix ? 'TRUE (route to execution)' : 'FALSE (continue routing)') . "\n\n";

// Step 3: isActivityConfirmation check  
echo "4. isActivityConfirmation check:\n";
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Малярные'
];

$mobileSquadSkipList = ['Электромонтажные', 'Слесарные', 'Сварочные', 'Теплоизоляция', 'Малярные'];

$foundMatch = false;
foreach ($activityTypes as $activity) {
    // Check skip list
    if (in_array($activity, $mobileSquadSkipList)) {
        if ($activity === 'Электромонтажные') {
            echo "   Found 'Электромонтажные' in activityTypes\n";
            echo "   ✓ In skip list - continuing to next activity\n";
        }
        continue;
    }
    
    // Check patterns
    $pattern1 = "category_Табель_{$activity}";
    $pattern2 = "Табель_{$activity}";
    
    if ($category === $pattern1 || $category === $pattern2) {
        $foundMatch = true;
        echo "   MATCH: '{$activity}'\n";
        break;
    }
}

if (!$foundMatch) {
    echo "   ✓ No match found (skip list worked!)\n";
    echo "   Result: FALSE (continue routing)\n\n";
} else {
    echo "   ERROR: Should have been skipped!\n";
    exit;
}

// Step 4: Route to handleOtherWorkTypes
echo "5. Route to handleOtherWorkTypes:\n";
echo "   Category: '{$category}'\n";

// Check the handler condition
$handlerCondition = ($category === 'Табель_Электромонтажные' || 
                     $category === 'category_Табель_Электромонтажные' || 
                     $category === 'Электромонтажные');

echo "   Handler condition check:\n";
echo "     - category === 'Табель_Электромонтажные': " . ($category === 'Табель_Электромонтажные' ? 'TRUE ✓' : 'FALSE') . "\n";
echo "     - category === 'category_Табель_Электромонтажные': " . ($category === 'category_Табель_Электромонтажные' ? 'TRUE' : 'FALSE') . "\n";
echo "     - category === 'Электромонтажные': " . ($category === 'Электромонтажные' ? 'TRUE' : 'FALSE') . "\n";
echo "   Combined result: " . ($handlerCondition ? 'TRUE ✓✓✓' : 'FALSE ❌') . "\n\n";

if ($handlerCondition) {
    echo "6. ✓✓✓ SUCCESS! Should show submenu:\n";
    echo "   - Display message: '⚡ Электромонтажные работы для проекта {project_code}'\n";
    echo "   - Show keyboard with 4 options:\n";
    echo "     🔧 Досборка\n";
    echo "     ⚙️ Реализация ТР\n";
    echo "     🔍 Устранение замечаний РК\n";
    echo "     🛡️ Работы по гарантии\n";
} else {
    echo "6. ❌ FAILURE! Handler condition not met\n";
}

echo "\n=== Test Complete ===\n";
