<?php
// Test if RK callback matches any short format patterns in isActivityConfirmation

echo "=== RK SHORT FORMAT PATTERN TEST ===\n\n";

$originalCallback = 'cat_ТабУстрЗамРК';
$mappedCategory = 'ТабУстрЗамРК';

echo "Original callback: '{$originalCallback}'\n";
echo "Mapped category: '{$mappedCategory}'\n\n";

// Test short format mappings from isActivityConfirmation
echo "1. TESTING SHORT FORMAT MAPPINGS:\n";

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

// Test electrical equipment pattern
foreach ($shortToFullMap as $shortKey => $fullActivity) {
    $electricalPattern = "cat_ТабЭлОб_{$shortKey}";
    if ($originalCallback === $electricalPattern) {
        echo "   ❌ ELECTRICAL MATCH: '{$originalCallback}' matches '{$electricalPattern}'\n";
        echo "   This would return TRUE from isActivityConfirmation!\n";
        $foundElectricalMatch = true;
        break;
    }
}

if (!isset($foundElectricalMatch)) {
    echo "   ✅ No electrical equipment pattern matches\n";
}

// Test commissioning pattern
foreach ($shortToFullMap as $shortKey => $fullActivity) {
    $commissioningPattern = "cat_{$shortKey}";
    if ($originalCallback === $commissioningPattern) {
        echo "   ❌ COMMISSIONING MATCH: '{$originalCallback}' matches '{$commissioningPattern}'\n";
        echo "   This would return TRUE from isActivityConfirmation!\n";
        $foundCommissioningMatch = true;
        break;
    }
}

if (!isset($foundCommissioningMatch)) {
    echo "   ✅ No commissioning pattern matches\n";
}

echo "\n2. TESTING MAPPED CATEGORY:\n";
// Test if mapped category matches any pattern
foreach ($shortToFullMap as $shortKey => $fullActivity) {
    $electricalPattern = "cat_ТабЭлОб_{$shortKey}";
    $commissioningPattern = "cat_{$shortKey}";
    
    if ($mappedCategory === $electricalPattern) {
        echo "   ❌ MAPPED ELECTRICAL MATCH: '{$mappedCategory}' matches '{$electricalPattern}'\n";
        $foundMappedElectricalMatch = true;
        break;
    }
    
    if ($mappedCategory === $commissioningPattern) {
        echo "   ❌ MAPPED COMMISSIONING MATCH: '{$mappedCategory}' matches '{$commissioningPattern}'\n";
        $foundMappedCommissioningMatch = true;
        break;
    }
}

if (!isset($foundMappedElectricalMatch) && !isset($foundMappedCommissioningMatch)) {
    echo "   ✅ Mapped category doesn't match any short format patterns\n";
}

echo "\n3. SPECIFIC RK PATTERN CHECK:\n";
// Check if any of the short keys could be confused with RK
echo "Checking if any short keys could match RK patterns...\n";
$rkRelatedKeys = ['УстрЗамРК', 'УЗ_РК', 'РК', 'Устр'];
foreach ($shortToFullMap as $shortKey => $fullActivity) {
    foreach ($rkRelatedKeys as $rkKey) {
        if (strpos($shortKey, $rkKey) !== false || strpos($rkKey, $shortKey) !== false) {
            echo "   ⚠️  POTENTIAL CONFLICT: Short key '{$shortKey}' might conflict with RK key '{$rkKey}'\n";
        }
    }
}

echo "\n4. CONCLUSION:\n";
if (isset($foundElectricalMatch) || isset($foundCommissioningMatch) || 
    isset($foundMappedElectricalMatch) || isset($foundMappedCommissioningMatch)) {
    echo "   ❌ PATTERN CONFLICT FOUND in short format mappings\n";
    echo "   This explains why RK button creates confirmation instead of showing submenu\n";
} else {
    echo "   ✅ NO PATTERN CONFLICTS in isActivityConfirmation\n";
    echo "   The issue must be in a different method or routing logic\n";
}

echo "\n5. NEXT INVESTIGATION:\n";
echo "   Since isActivityConfirmation doesn't seem to be the issue,\n";
echo "   check these other possible causes:\n";
echo "   a) isActivityExecution method might be returning TRUE\n";
echo "   b) Different routing logic in routeTabelCallback\n";
echo "   c) Another handler intercepting the callback\n";
echo "   d) The RK handler in handleOtherWorkTypes has a bug\n";