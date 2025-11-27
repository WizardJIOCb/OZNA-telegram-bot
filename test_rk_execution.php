<?php
// Test if RK callback matches isActivityExecution patterns

echo "=== RK ISACTIVITYEXECUTION TEST ===\n\n";

$originalCallback = 'cat_ТабУстрЗамРК';
$mappedCategory = 'ТабУстрЗамРК';

echo "Original callback: '{$originalCallback}'\n";
echo "Mapped category: '{$mappedCategory}'\n\n";

echo "1. TESTING ISACTIVITYEXECUTION PATTERNS:\n";

// Check if it contains _yes (main activity execution pattern)
if (strpos($mappedCategory, '_yes') !== false) {
    echo "   ❌ CONTAINS '_yes': Would be treated as activity execution\n";
    $hasYes = true;
} else {
    echo "   ✅ No '_yes' suffix\n";
    $hasYes = false;
}

// Check standard activity execution pattern
if (preg_match('/^(category_)?Табель_(.+)_yes$/', $mappedCategory, $matches)) {
    echo "   ❌ MATCHES STANDARD PATTERN: '{$mappedCategory}'\n";
    echo "   Extracted activity: '{$matches[2]}'\n";
    $matchesStandard = true;
} else {
    echo "   ✅ Doesn't match standard activity execution pattern\n";
    $matchesStandard = false;
}

// Check short callback format for electrical equipment
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $mappedCategory, $matches)) {
    echo "   ❌ MATCHES ELECTRICAL SHORT FORMAT: '{$mappedCategory}'\n";
    echo "   Extracted key: '{$matches[1]}'\n";
    $matchesElectrical = true;
} else {
    echo "   ✅ Doesn't match electrical short format\n";
    $matchesElectrical = false;
}

// Check short callback format for commissioning works
if (preg_match('/^cat_(.+)_yes$/u', $mappedCategory, $matches)) {
    echo "   ❌ MATCHES COMMISSIONING SHORT FORMAT: '{$mappedCategory}'\n";
    echo "   Extracted key: '{$matches[1]}'\n";
    
    // Check if it's in the basic commissioning activities
    $basicCommissioningActivities = [
        'ТабИнстр' => 'Инструктаж',
        'ТабДорога' => 'Дорога',
        'ТабПодгПоезд' => 'ПодготовкаКПоездке',
        'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
        'ТабОхрСигн' => 'ОхраннаяСигнализация'
    ];
    
    $shortActivityKey = $matches[1];
    if (isset($basicCommissioningActivities[$shortActivityKey])) {
        echo "   ❌ FOUND IN BASIC COMMISSIONING: '{$shortActivityKey}'\n";
        $matchesCommissioning = true;
    } else {
        echo "   ⚠️  Not in basic commissioning activities\n";
        $matchesCommissioning = false;
    }
} else {
    echo "   ✅ Doesn't match commissioning short format\n";
    $matchesCommissioning = false;
}

echo "\n2. SPECIFIC RK EXECUTION CHECKS:\n";

// Check RK comments elimination pattern from isActivityExecution
if (preg_match('/^cat_ТабУстрЗам(.+)_yes$/u', $mappedCategory, $matches)) {
    echo "   ❌ MATCHES RK EXECUTION PATTERN: '{$mappedCategory}'\n";
    echo "   RK Section: '{$matches[1]}'\n";
    
    $rkToFullMap = [
        'ЭС' => 'УстрЗам_ЭС',
        'АТХ' => 'УстрЗам_АТХ',
        'ОВКВ' => 'УстрЗам_ОВКВ',
        'ПСОС' => 'УстрЗам_ПСОС',
        'ТХ' => 'УстрЗам_ТХ',
        'Прочее' => 'УстрЗам_Прочее'
    ];
    
    $rkActivityKey = $matches[1];
    if (isset($rkToFullMap[$rkActivityKey])) {
        echo "   ❌ RK SECTION FOUND: '{$rkActivityKey}' maps to '{$rkToFullMap[$rkActivityKey]}'\n";
        $matchesRKExecution = true;
    } else {
        echo "   ⚠️  RK section not in map\n";
        $matchesRKExecution = false;
    }
} else {
    echo "   ✅ Doesn't match RK execution pattern\n";
    $matchesRKExecution = false;
}

echo "\n3. CONCLUSION:\n";
if ($hasYes || $matchesStandard || $matchesElectrical || $matchesCommissioning || $matchesRKExecution) {
    echo "   ❌ ISACTIVITYEXECUTION WOULD RETURN TRUE\n";
    echo "   This means the callback would route to handleActivityExecution\n";
    echo "   Which creates a report instead of showing the submenu\n";
    echo "   THIS IS THE PROBLEM!\n";
} else {
    echo "   ✅ isActivityExecution should return FALSE\n";
    echo "   The callback should route to handleOtherWorkTypes\n";
    echo "   Which should show the RK submenu\n";
}

echo "\n4. EXPECTED vs ACTUAL:\n";
echo "   Expected: '{$originalCallback}' → isActivityExecution=FALSE → handleOtherWorkTypes → RK submenu\n";
echo "   Actual: '{$originalCallback}' → isActivityExecution=TRUE → handleActivityExecution → Report created\n";

if ($hasYes || $matchesStandard || $matchesElectrical || $matchesCommissioning || $matchesRKExecution) {
    echo "\n5. FIX NEEDED:\n";
    echo "   Add exclusion for RK main button in isActivityExecution method\n";
    echo "   The pattern '{$mappedCategory}' should NOT be treated as activity execution\n";
    echo "   Only RK SECTION confirmations (like 'cat_ТабУстрЗамЭС_yes') should be executions\n";
}