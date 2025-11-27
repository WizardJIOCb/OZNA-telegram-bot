<?php

echo "=== SUPERVISION DATE FLOW DEBUGGING ===\n\n";

echo "1. TESTING SUPERVISION DATE CALLBACK DETECTION\n";
echo "===============================================\n";

// Test the exact callback that would come from supervision date selection
$supervisionDateCallbacks = [
    'Табель_ШМ_ДатаСегодня',
    'Табель_ШМ_ДатаВчера', 
    'Табель_Шеф_монтаж_ДатаСегодня', // This might not exist
    'Табель_Шеф-монтаж_ДатаСегодня' // This might not exist
];

foreach ($supervisionDateCallbacks as $category) {
    echo "\nTesting category: '$category'\n";
    
    // Test current detection logic in handleDateSelection
    $containsShefMontazh = str_contains($category, 'Шеф-монтаж');
    $containsShM = str_contains($category, 'ШМ');
    
    echo "  Contains 'Шеф-монтаж': " . ($containsShefMontazh ? 'YES' : 'NO') . "\n";
    echo "  Contains 'ШМ': " . ($containsShM ? 'YES' : 'NO') . "\n";
    echo "  Current logic result: " . (($containsShefMontazh || $containsShM) ? 'DETECTED AS SUPERVISION' : 'NOT DETECTED') . "\n";
}

echo "\n\n2. TESTING COMPLETE SUPERVISION FLOW\n";
echo "====================================\n";

echo "Step 1: User clicks 'Шеф-монтаж' button\n";
echo "  → Calls handleSupervisionDateSelection()\n";
echo "  → Creates date keyboard with prefix 'category_Табель_ШМ'\n";

echo "\nStep 2: User clicks 'Сегодня' date button\n";
echo "  → Generates callback: 'category_Табель_ШМ_ДатаСегодня'\n";
echo "  → Extracted category: 'Табель_ШМ_ДатаСегодня'\n";

echo "\nStep 3: handleDateSelection processes category\n";
$testCategory = 'Табель_ШМ_ДатаСегодня';
echo "  → Category: '$testCategory'\n";
echo "  → Contains 'Шеф-монтаж': " . (str_contains($testCategory, 'Шеф-монтаж') ? 'YES' : 'NO') . "\n";
echo "  → Contains 'ШМ': " . (str_contains($testCategory, 'ШМ') ? 'YES' : 'NO') . "\n";

$isSupervisionDetected = str_contains($testCategory, 'Шеф-монтаж') || str_contains($testCategory, 'ШМ');
echo "  → Detected as supervision: " . ($isSupervisionDetected ? 'YES' : 'NO') . "\n";

if ($isSupervisionDetected) {
    echo "  → Should set: is_supervision = true\n";
    echo "  → Should proceed to project selection\n";
} else {
    echo "  → ERROR: Will not be detected as supervision!\n";
}

echo "\nStep 4: User selects project\n";
echo "  → handleProjectSelection checks is_supervision flag\n";
echo "  → If flag is true: shows createSupervisionKeyboard()\n";
echo "  → If flag is false: shows mobile brigade keyboard (WRONG!)\n";

echo "\n🎯 CONCLUSION:\n";
if ($isSupervisionDetected) {
    echo "✅ The supervision flow should work correctly!\n";
    echo "   The 'ШМ' detection in handleDateSelection should catch this.\n";
} else {
    echo "❌ The supervision flow is broken!\n";
    echo "   The detection logic needs to be fixed.\n";
}

echo "\n📋 VERIFICATION:\n";
echo "If the user is still not seeing the supervision menu after project selection,\n";
echo "check the error logs for:\n";
echo "- 'TabelHandler: flow flags - isSupervision: true'\n";
echo "- 'TabelHandler: showing supervision keyboard'\n";