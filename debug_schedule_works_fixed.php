<?php
// Debug script for electrical equipment schedule works (FIXED)

echo "DEBUG: Testing Electrical Equipment Schedule Works (FIXED)\n";
echo "======================================================\n\n";

// Test if the activity type is in the activity types array
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
    'АвансовыйОтчет',
    // New electrical equipment activity types
    'РаботыПоГрафику_Электрооборудование',
    'ФормированиеИД_Электрооборудование',
    'ПодтвДостВехи_ЭлектрОб',
    'ДопРаб_ЭлектрОб',
    'ИзмСхемПодкл_ЭлектрОб',
    'МонтажКабЛиний_ЭлектрОб',
    'РасклКабЛиний_ЭлектрОб',
    'Прочее_Электрооборудование',
    // Electrical equipment main category
    'Электрооборудование'
];

echo "1. Checking if 'РаботыПоГрафику_Электрооборудование' is in activity types:\n";
if (in_array('РаботыПоГрафику_Электрооборудование', $activityTypes)) {
    echo "   ✅ FOUND: 'РаботыПоГрафику_Электрооборудование' is in activity types\n";
} else {
    echo "   ❌ NOT FOUND: 'РаботыПоГрафику_Электрооборудование' is NOT in activity types\n";
}

// Test the regex pattern that would be used in isActivityExecution
$category = "Табель_РаботыПоГрафику_Электрооборудование_yes"; // This is what gets passed to isActivityExecution
echo "\n2. Testing regex pattern for activity execution:\n";
echo "   Category: {$category}\n";

$pattern = '/^Табель_(.+)_yes$/';
if (preg_match($pattern, $category, $matches)) {
    $activityType = $matches[1];
    echo "   ✅ REGEX MATCH: Extracted activity type: '{$activityType}'\n";
    
    // Check if this activity type is in our list
    if (in_array($activityType, $activityTypes)) {
        echo "   ✅ ACTIVITY TYPE FOUND: '{$activityType}' is in activity types\n";
    } else {
        echo "   ❌ ACTIVITY TYPE NOT FOUND: '{$activityType}' is NOT in activity types\n";
    }
} else {
    echo "   ❌ REGEX NO MATCH: Pattern did not match\n";
}

// Test the alternative method (fallback)
echo "\n3. Testing fallback method:\n";
$fallbackActivityType = str_replace(['Табель_', '_yes'], '', $category);
echo "   Fallback extracted activity type: '{$fallbackActivityType}'\n";

if (in_array($fallbackActivityType, $activityTypes)) {
    echo "   ✅ FALLBACK MATCH: '{$fallbackActivityType}' is in activity types\n";
} else {
    echo "   ❌ FALLBACK NO MATCH: '{$fallbackActivityType}' is NOT in activity types\n";
}

echo "\n4. Testing isActivityExecution logic:\n";
// Simulate the isActivityExecution method
function isActivityExecution($category, $activityTypes) {
    // Check standard activity execution
    if (strpos($category, '_yes') !== false) {
        // Extract the activity type from the category
        $pattern = '/^Табель_(.+)_yes$/';
        if (preg_match($pattern, $category, $matches)) {
            $activityType = $matches[1];
            // Check if this activity type is in our list
            $result = in_array($activityType, $activityTypes);
            echo "   isActivityExecution: category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false') . "\n";
            return $result;
        }
    }
    
    // Check idle menu execution (when user confirms idle activity with detailed reason)
    if (strpos($category, '_yes') !== false && 
        (strpos($category, 'Табель_Простой_yes') !== false || strpos($category, 'Табель_ШМ_Простой_yes') !== false)) {
        return true;
    }
    
    return false;
}

$result = isActivityExecution($category, $activityTypes);
echo "   Final result: " . ($result ? "✅ TRUE - Activity execution should work" : "❌ FALSE - Activity execution will fail") . "\n";

echo "\nCONCLUSION:\n";
if ($result) {
    echo "The activity execution logic is correct. If it's not working, the issue might be:\n";
    echo "1. Database connection problems\n";
    echo "2. User data issues (missing FIO or functional direction)\n";
    echo "3. Conversation state issues\n";
    echo "4. The callback data might not be what we expect\n";
    echo "5. Logging might help identify the exact problem\n";
} else {
    echo "There's an issue with the activity execution logic that needs to be fixed.\n";
}