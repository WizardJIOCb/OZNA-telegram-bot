<?php
// Debug isActivityConfirmation for RK callback

echo "=== ISACTIVITYCONFIRMATION DETAILED DEBUG ===\n\n";

$category = 'ТабУстрЗамРК'; // This is what extractCategoryFromCallback returns

echo "Testing category: '{$category}'\n\n";

// Test 1: Check against activityTypes array
echo "1. ACTIVITY TYPES CHECK:\n";
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет'
];

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
        echo "   ❌ MATCH FOUND: '{$category}' matches activity '{$activity}'\n";
        echo "   Pattern: '{$pattern2}'\n";
        echo "   This would return TRUE from isActivityConfirmation!\n";
        $foundMatch = true;
        break;
    }
}

if (!isset($foundMatch)) {
    echo "   ✅ No matches in activity types\n";
}

// Test 2: Check shortToFullMap
echo "\n2. SHORT FORMAT MAP CHECK:\n";
$shortToFullMap = [
    'РабПоГр' => 'РабПоГр_ЭлектрОб',
    'ФормИД' => 'ФормИД_ЭлектрОб',
    'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
    'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
    'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
    'ДопРаб' => 'ДопРаб_ЭлектрОб',
    'ПодтВех' => 'ПодтВех_ЭлектрОб',
    'ТабПНРаботы' => 'ПусконаладочныеРаботы',
    'ТабИнстр' => 'Инструктаж',
    'ТабДорога' => 'Дорога',
    'ТабПодгПоезд' => 'ПодготовкаКПоездке',
    'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
    'ТабОхрСигн' => 'ОхраннаяСигнализация',
    'ТабОтоплВент' => 'ОтоплениеИВентиляция',
    'ТабТехОб' => 'ТехнологическоеОборудование',
    'ТабМетрол' => 'Метрология',
    'ТабКомплОпр' => 'КомплексноеОпробование',
    'ТабРежНал' => 'РежимнаяНаладка'
];

foreach ($shortToFullMap as $shortKey => $fullActivity) {
    $pattern1 = "cat_ТабЭлОб_{$shortKey}";
    $pattern2 = "cat_{$shortKey}";
    
    if ($category === $pattern1) {
        echo "   ❌ ELECTRICAL MATCH: '{$category}' matches '{$pattern1}'\n";
        $foundShortMatch = true;
        break;
    }
    
    if ($category === $pattern2) {
        echo "   ❌ COMMISSIONING MATCH: '{$category}' matches '{$pattern2}'\n";
        $foundShortMatch = true;
        break;
    }
}

if (!isset($foundShortMatch)) {
    echo "   ✅ No matches in short format map\n";
}

echo "\n3. CONCLUSION:\n";
if (isset($foundMatch) || isset($foundShortMatch)) {
    echo "   ❌ PROBLEM: isActivityConfirmation would return TRUE\n";
    echo "   This explains why it creates a report instead of showing submenu!\n";
} else {
    echo "   ✅ isActivityConfirmation should return FALSE\n";
    echo "   The problem must be elsewhere in the routing logic\n";
}

echo "\n4. CHECKING FOR SPECIFIC RK PATTERN:\n";
// Let's check if there's a specific pattern that might match RK
$possibleMatches = [
    'ТабУстрЗамРК',
    'УстрЗамРК', 
    'УЗ_РК',
    'cat_ТабУстрЗамРК'
];

foreach ($possibleMatches as $test) {
    if ($test === $category) {
        echo "   Testing: '{$test}' vs '{$category}' -> MATCH\n";
    } else {
        echo "   Testing: '{$test}' vs '{$category}' -> no match\n";
    }
}