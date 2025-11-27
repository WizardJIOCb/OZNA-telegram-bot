<?php
// Test exact RK routing with real TabelHandler patterns

echo "=== REAL RK ROUTING DEBUG ===\n\n";

// Test the EXACT patterns used in isActivityConfirmation
$category = 'ТабУстрЗамРК';

echo "Testing category: '{$category}'\n\n";

// Pattern 1: Check against shortToFullMap in isActivityConfirmation
echo "1. SHORT FORMAT MAP IN ISACTIVITYCONFIRMATION:\n";
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

echo "Checking for 'cat_{shortKey}' patterns:\n";
foreach ($shortToFullMap as $shortKey => $fullActivity) {
    $pattern = "cat_{$shortKey}";
    if ($category === $pattern) {
        echo "   ❌ MATCH FOUND: '{$category}' matches '{$pattern}'\n";
        echo "   This would return TRUE from isActivityConfirmation!\n";
        $foundMatch = true;
        break;
    }
}

if (!isset($foundMatch)) {
    echo "   ✅ No matches in commissioning short format patterns\n";
}

// Pattern 2: Check if there's something different about the callback
echo "\n2. CHECKING ORIGINAL CALLBACK:\n";
$originalCallback = 'cat_ТабУстрЗамРК';
echo "Original callback: '{$originalCallback}'\n";

// Check if it might match any of the short commissioning patterns
foreach ($shortToFullMap as $shortKey => $fullActivity) {
    if ($originalCallback === "cat_{$shortKey}") {
        echo "   ❌ ORIGINAL CALLBACK MATCHES: 'cat_{$shortKey}'\n";
        $foundOriginalMatch = true;
        break;
    }
}

if (!isset($foundOriginalMatch)) {
    echo "   ✅ Original callback doesn't match commissioning patterns\n";
}

// Pattern 3: Check the most likely culprit - УЗ_РК in activityTypes
echo "\n3. ACTIVITY TYPES PATTERN CHECK:\n";
$activityTypes = ['УЗ_РК', 'ПНР', 'ШМР', 'МедОсмотр', 'Аттестация'];

foreach ($activityTypes as $activity) {
    $pattern1 = "category_Табель_{$activity}";
    $pattern2 = "Табель_{$activity}";
    
    echo "Testing patterns for activity '{$activity}':\n";
    echo "   Pattern 1: '{$pattern1}' vs '{$category}' -> " . ($category === $pattern1 ? 'MATCH ❌' : 'no match') . "\n";
    echo "   Pattern 2: '{$pattern2}' vs '{$category}' -> " . ($category === $pattern2 ? 'MATCH ❌' : 'no match') . "\n";
    
    if ($category === $pattern1 || $category === $pattern2) {
        echo "   ❌ ACTIVITY CONFIRMATION MATCH FOUND!\n";
        echo "   This means isActivityConfirmation would return TRUE\n";
        echo "   And the callback would route to handleActivityConfirmation (creates report)\n";
        $foundActivityMatch = true;
        break;
    }
}

if (!isset($foundActivityMatch)) {
    echo "   ✅ No activity type matches found\n";
}

echo "\n4. CONCLUSION:\n";
if (isset($foundMatch) || isset($foundOriginalMatch) || isset($foundActivityMatch)) {
    echo "   ❌ PROBLEM IDENTIFIED: RK callback is being treated as activity confirmation\n";
    echo "   ROOT CAUSE: There's a pattern match causing it to route to confirmation instead of submenu\n";
} else {
    echo "   ✅ NO CONFLICTS FOUND: RK should route to handleOtherWorkTypes correctly\n";
    echo "   The problem must be in the routing logic or handler priority\n";
}

echo "\n5. NEXT INVESTIGATION:\n";
echo "   Since no obvious conflicts found, the issue might be:\n";
echo "   a) The routing order in routeTabelCallback or handle method\n";
echo "   b) A different activity type or pattern not considered\n";
echo "   c) The RK handler in handleOtherWorkTypes has a bug\n";
echo "   d) Something in the immediate equipment handlers is intercepting\n";