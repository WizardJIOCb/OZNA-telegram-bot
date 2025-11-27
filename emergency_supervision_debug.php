<?php
/**
 * EMERGENCY SUPERVISION DEBUG - Find the exact issue NOW
 */

echo "🚨 EMERGENCY SUPERVISION DEBUG\n";
echo "==============================\n\n";

// Test the exact callback flow
$testCallbacks = [
    'category_Табель_ШМ_РабПоГрафику_yes',
    'category_Табель_ШМ_Дорога_yes',
    'category_Табель_ШМ_Инструктаж_yes'
];

echo "📋 TESTING CALLBACK PATTERNS\n";
echo "============================\n\n";

foreach ($testCallbacks as $callback) {
    echo "Testing callback: '{$callback}'\n";
    
    // Test pattern 1: Full format
    if (preg_match('/^category_Табель_(ШМ_РабПоГрафику|ШМ_ПодготовкаКПоездке|ШМ_Дорога|ШМ_Инструктаж|ШМ_СдачаАвансовогоОтчета)_yes$/', $callback, $matches)) {
        echo "  ✅ MATCHES full format pattern: {$matches[1]}\n";
    } else {
        echo "  ❌ DOES NOT match full format pattern\n";
    }
    
    // Test pattern 2: Project activities
    if (preg_match('/^(category_)?Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/', $callback, $matches)) {
        echo "  ✅ MATCHES project pattern\n";
    } else {
        echo "  ❌ DOES NOT match project pattern\n";
    }
    
    echo "\n";
}

echo "🔍 DIAGNOSIS\n";
echo "============\n\n";

echo "If any callback shows '❌ DOES NOT match' - that's the problem!\n";
echo "The patterns in isActivityExecution() are wrong.\n\n";

echo "🔧 QUICK FIXES TO TRY\n";
echo "=====================\n\n";

echo "Issue 1: Pattern not matching\n";
echo "→ Need to fix regex in isActivityExecution()\n\n";

echo "Issue 2: Activity types not in array\n";
echo "→ Need to add to activityTypes array\n\n";

echo "Issue 3: Wrong callback format\n";
echo "→ Need to check what callbacks are actually generated\n\n";

echo "🚨 IMMEDIATE ACTION NEEDED\n";
echo "==========================\n";
echo "I will check the EXACT callback generation and fix the pattern matching NOW!\n";
?>