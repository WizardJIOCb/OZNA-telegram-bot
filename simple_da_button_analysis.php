<?php

// Simple pattern analysis for "Да" button issues

echo "=== SIMPLE 'ДА' BUTTON PATTERN ANALYSIS ===\n\n";

// Read the TabelHandler source code
$handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$sourceCode = file_get_contents($handlerFile);

echo "1. CHECKING CONFIRMATION PATTERNS IN isActivityExecution\n";
echo "========================================================\n";

// Extract the isActivityExecution method
$start = strpos($sourceCode, 'private function isActivityExecution');
$end = strpos($sourceCode, 'private function handleActivityExecution', $start);
$isActivityExecutionMethod = substr($sourceCode, $start, $end - $start);

echo "Found isActivityExecution method: " . (strlen($isActivityExecutionMethod) > 0 ? "YES" : "NO") . "\n\n";

// Check for specific patterns
$patterns = [
    'Supervision Project Activities' => '/ШМ_УчастиеВЗПИ\|ШМ_РазрПрограммы\|ШМ_РазрГрафика\|ШМ_ОргДопоставки\|ШМ_УчастиеВОПИ\|ШМ_ИспобязМастера/',
    'Electrical Equipment Short' => '/cat_ТабЭлОб_\(\.\+\)_yes/',
    'Automation Equipment Short' => '/cat_ТабАвтом_\(\.\+\)_yes/',
    'Fire Alarm Equipment Short' => '/cat_ТабПожСигн_\(\.\+\)_yes/',
    'Warranty Equipment Short' => '/cat_Гарантии_\(\.\+\)_yes/',
    'Commissioning Basic Activities' => '/basicCommissioningActivities/',
    'Metrology Equipment' => '/cat_ТабМетрол_\(\.\+\)_yes/',
    'Security Alarm Equipment' => '/cat_ТабОхрСиг_\(\.\+\)_yes/',
    'Technological Equipment' => '/cat_ТабТехОб_\(\.\+\)_yes/',
    'Heating Ventilation Equipment' => '/cat_ТабВентОтопл_\(\.\+\)_yes/'
];

foreach ($patterns as $name => $pattern) {
    if (preg_match($pattern, $isActivityExecutionMethod)) {
        echo "  ✅ $name pattern FOUND\n";
    } else {
        echo "  ❌ $name pattern MISSING\n";
    }
}

echo "\n2. CHECKING SPECIFIC PATTERN IMPLEMENTATIONS\n";
echo "============================================\n";

// Check if supervision project pattern exists with exact format
if (strpos($isActivityExecutionMethod, 'ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера') !== false) {
    echo "  ✅ EXACT supervision project pattern found\n";
} else {
    echo "  ❌ EXACT supervision project pattern missing\n";
}

// Check if electrical equipment pattern exists
if (strpos($isActivityExecutionMethod, 'cat_ТабЭлОб_(.+)_yes') !== false) {
    echo "  ✅ EXACT electrical equipment pattern found\n";
} else {
    echo "  ❌ EXACT electrical equipment pattern missing\n";
}

// Check if warranty equipment pattern exists
if (strpos($isActivityExecutionMethod, 'cat_Гарантии_(.+)_yes') !== false) {
    echo "  ✅ EXACT warranty equipment pattern found\n";
} else {
    echo "  ❌ EXACT warranty equipment pattern missing\n";
}

echo "\n3. ANALYZING PATTERN ORDER AND COVERAGE\n";
echo "=======================================\n";

// Count how many patterns are implemented
$implementedCount = 0;
foreach ($patterns as $name => $pattern) {
    if (preg_match($pattern, $isActivityExecutionMethod)) {
        $implementedCount++;
    }
}

echo "Implemented patterns: $implementedCount/" . count($patterns) . "\n";

if ($implementedCount < count($patterns)) {
    echo "⚠️  WARNING: Some confirmation patterns are missing!\n";
    echo "This could cause 'Да' buttons to not work for certain equipment types.\n";
} else {
    echo "✅ All expected patterns are implemented.\n";
}

echo "\n4. EXTRACTING METHOD SIGNATURES\n";
echo "==============================\n";

// Extract method signatures to understand the flow
$methods = [
    'canHandle',
    'isActivityExecution', 
    'isConfirmationCallback',
    'extractCategoryFromCallback',
    'handleActivityExecution'
];

foreach ($methods as $method) {
    if (strpos($sourceCode, "function $method") !== false) {
        echo "  ✅ Method $method exists\n";
    } else {
        echo "  ❌ Method $method missing\n";
    }
}

echo "\n5. CHECKING SPECIFIC CALLBACK EXAMPLES\n";
echo "======================================\n";

// Test some specific callback patterns
$testCallbacks = [
    'category_Табель_ШМ_УчастиеВЗПИ_yes' => 'Supervision project activity',
    'cat_ТабЭлОб_РабПоГр_yes' => 'Electrical equipment short',
    'cat_Гарантии_ЭлектрОб_yes' => 'Warranty equipment short',
    'cat_ТабИнстр_yes' => 'Commissioning works short',
    'cat_ТабАвтом_ИзмСх_yes' => 'Automation equipment short',
    'cat_ТабПожСигн_РабПоГр_yes' => 'Fire alarm equipment short'
];

foreach ($testCallbacks as $callback => $type) {
    echo "Testing: $callback ($type)\n";
    
    // Check if this specific callback would be matched by any pattern in the method
    $matched = false;
    
    // Check supervision project pattern
    if (preg_match('/^(category_)?Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/', $callback)) {
        echo "  ✅ Matches supervision project pattern\n";
        $matched = true;
    }
    
    // Check electrical equipment pattern
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callback)) {
        echo "  ✅ Matches electrical equipment pattern\n";
        $matched = true;
    }
    
    // Check warranty equipment pattern
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback)) {
        echo "  ✅ Matches warranty equipment pattern\n";
        $matched = true;
    }
    
    // Check automation equipment pattern
    if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $callback)) {
        echo "  ✅ Matches automation equipment pattern\n";
        $matched = true;
    }
    
    // Check fire alarm equipment pattern
    if (preg_match('/^cat_ТабПожСигн_(.+)_yes$/u', $callback)) {
        echo "  ✅ Matches fire alarm equipment pattern\n";
        $matched = true;
    }
    
    // Check commissioning works pattern
    if (preg_match('/^cat_(.+)_yes$/', $callback, $matches)) {
        $shortKey = $matches[1];
        $basicCommissioningActivities = [
            'ТабИнстр', 'ТабДорога', 'ТабПодгПоезд', 'ТабСдачаОтч',
            'ТабОхрСигн', 'ТабОтоплВент', 'ТабТехОб', 'ТабМетрол',
            'ТабКомплОпр', 'ТабРежНал'
        ];
        if (in_array($shortKey, $basicCommissioningActivities)) {
            echo "  ✅ Matches commissioning works pattern\n";
            $matched = true;
        }
    }
    
    if (!$matched) {
        echo "  ❌ NO PATTERN MATCH - This button will NOT work!\n";
    }
    
    echo "\n";
}

echo "6. SUMMARY AND RECOMMENDATIONS\n";
echo "==============================\n";

echo "If 'Да' buttons are not working, check these common issues:\n\n";

echo "A. Missing Pattern Implementation:\n";
echo "   - Ensure all equipment type patterns are in isActivityExecution()\n";
echo "   - Check line numbers around 1200-1500 in TabelHandler.php\n\n";

echo "B. Callback Format Mismatch:\n";
echo "   - Confirm button creation uses correct callback format\n";
echo "   - Check createConfirmationKeyboard() method calls\n\n";

echo "C. Method Call Order:\n";
echo "   - Verify isActivityExecution() is called before handleOtherWorkTypes()\n";
echo "   - Check routing logic in handle() method\n\n";

echo "D. User Validation:\n";
echo "   - Ensure user has FIO and functional_direction set\n";
echo "   - Check access level permissions (not guest level)\n\n";

echo "The most likely issue is missing pattern implementation in isActivityExecution().\n";
echo "Run this diagnosis and let me know which specific patterns are missing!\n";

?>