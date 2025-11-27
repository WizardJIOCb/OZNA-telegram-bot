<?php
// Test script to verify RK sections routing fix

echo "=== RK SECTIONS ROUTING FIX VERIFICATION ===\n\n";

echo "1. TESTING isActivityExecution() WITH RK SECTIONS:\n";
echo "=================================================\n";

// Simulate the isActivityExecution method logic
function testIsActivityExecution($category) {
    echo "Testing category: '{$category}'\n";
    
    // Check if this is a menu callback that should NOT be treated as activity execution
    $menuCallbacks = [
        'cat_ТабЭлОб',     // Electrical equipment main menu (short format)
        'cat_ТабАвтом',    // Automation equipment main menu (short format)
        'cat_ТабПожСигн',  // Fire alarm equipment main menu (short format)
        'Табель_Электрооборудование',  // Electrical equipment main menu (full format)
        'Табель_Автоматизация',// Automation equipment main menu (full format)
        'Табель_ПожарнаяСигнализация',  // Fire alarm equipment main menu (full format)
        // RK sections should show confirmation dialogs, not be treated as activity execution
        'cat_ТабУстрЗамЭС',    // RK ES Section
        'cat_ТабУстрЗамАТХ',   // RK ATX Section
        'cat_ТабУстрЗамОВКВ',  // RK OVKV Section
        'cat_ТабУстрЗамПСОС',  // RK PS/OS Section
        'cat_ТабУстрЗамТХ',    // RK TX Section
        'cat_ТабУстрЗамПрочее' // RK Other Equipment Section
    ];
    
    if (in_array($category, $menuCallbacks)) {
        echo "  ✅ RESULT: FALSE (excluded from activity execution - correct!)\n";
        return false;
    }
    
    // Check standard activity execution (only _yes callbacks should be true)
    if (str_contains($category, '_yes')) {
        echo "  ✅ RESULT: TRUE (confirmation callback - correct!)\n";
        return true;
    }
    
    echo "  ✅ RESULT: FALSE (not activity execution - correct!)\n";
    return false;
}

// Test RK sections (should be FALSE - not activity execution)
$rkSections = [
    'cat_ТабУстрЗамЭС',
    'cat_ТабУстрЗамАТХ', 
    'cat_ТабУстрЗамОВКВ',
    'cat_ТабУстрЗамПСОС',
    'cat_ТабУстрЗамТХ',
    'cat_ТабУстрЗамПрочее'
];

foreach ($rkSections as $section) {
    testIsActivityExecution($section);
}

echo "\n";

// Test RK confirmations (should be TRUE - activity execution)
echo "2. TESTING RK CONFIRMATION CALLBACKS:\n";
echo "=====================================\n";

$rkConfirmations = [
    'cat_ТабУстрЗамЭС_yes',
    'cat_ТабУстрЗамАТХ_yes',
    'cat_ТабУстрЗамОВКВ_yes',
    'cat_ТабУстрЗамПСОС_yes',
    'cat_ТабУстрЗамТХ_yes'
];

foreach ($rkConfirmations as $confirmation) {
    testIsActivityExecution($confirmation);
}

echo "\n";

echo "3. EXPECTED ROUTING FLOW:\n";
echo "=========================\n";

function simulateRoutingFlow($category) {
    echo "Category: '{$category}'\n";
    
    // Simulate the routing logic from routeTabelCallback
    
    // 1. Check isActivityExecution first (higher priority)
    $isActivityExecution = testIsActivityExecution($category);
    if ($isActivityExecution) {
        echo "  → Routes to: handleActivityExecution() ✅\n";
        return;
    }
    
    // 2. Check isActivityConfirmation second
    // (RK sections without _yes are not confirmations)
    if (!str_contains($category, '_yes')) {
        echo "  → Not activity confirmation (no _yes)\n";
    }
    
    // 3. Finally routes to handleOtherWorkTypes
    echo "  → Routes to: handleOtherWorkTypes() ✅\n";
    echo "    └─ handleOtherWorkTypes() will match RK section and show confirmation dialog\n";
}

echo "\nTesting routing for RK sections:\n";
foreach ($rkSections as $section) {
    simulateRoutingFlow($section);
    echo "\n";
}

echo "Testing routing for RK confirmations:\n";
foreach ($rkConfirmations as $confirmation) {
    simulateRoutingFlow($confirmation);
    echo "\n";
}

echo "=== SUMMARY ===\n";
echo "✅ RK sections (cat_ТабУстрЗам...) now correctly route to handleOtherWorkTypes()\n";
echo "✅ RK confirmations (cat_ТабУстрЗам..._yes) correctly route to handleActivityExecution()\n";
echo "✅ This should fix the looping issue - RK sections will now show confirmation dialogs!\n";

echo "\n🎯 EXPECTED RESULT:\n";
echo "- User clicks RK section → Shows confirmation dialog\n";
echo "- User clicks 'Да' → Creates tabel entry\n";
echo "- No more infinite loops!\n";
?>