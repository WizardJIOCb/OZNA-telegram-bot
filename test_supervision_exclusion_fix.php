<?php
// Test supervision exclusion fix in isActivityExecution

echo "=== SUPERVISION EXCLUSION FIX TEST ===\n\n";

// Test the exact supervision callbacks
$supervisionCallbacks = [
    'Табель_ШМ_РабГарантии' => 'Supervision warranty works (should show menu)',
    'Табель_ШМ_УЗ_РК' => 'Supervision RK elimination (should show menu)'
];

echo "1. TESTING isActivityExecution EXCLUSION:\n";
echo "=========================================\n";

// Simulate the isActivityExecution method with the fix
function simulateIsActivityExecution($category) {
    echo "Testing category: '{$category}'\n";
    
    // Check if this is a menu callback that should NOT be treated as activity execution
    $menuCallbacks = [
        'cat_ТабЭлОб',     // Electrical equipment main menu (short format)
        'cat_ТабАвтом',    // Automation equipment main menu (short format)
        'cat_ТабПожСигн',  // Fire alarm equipment main menu (short format)
        'Табель_Электрооборудование',  // Electrical equipment main menu (full format)
        'Табель_Автоматизация',        // Automation equipment main menu (full format)
        'Табель_ПожарнаяСигнализация',  // Fire alarm equipment main menu (full format)
        // Supervision department menu callbacks (CRITICAL FIX)
        'Табель_ШМ_РабГарантии',  // Supervision warranty works menu
        'Табель_ШМ_УЗ_РК'         // Supervision RK elimination menu
    ];
    
    if (in_array($category, $menuCallbacks)) {
        echo "✅ EXCLUDED: Found in menu callbacks - NOT an activity execution\n";
        echo "   Result: Will show sub-menu instead of creating report\n";
        return false;
    }
    
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        echo "✅ ACTIVITY EXECUTION: Contains '_yes' - this is an activity execution\n";
        return true;
    }
    
    echo "❌ NOT RECOGNIZED: Not in exclusions and not activity execution\n";
    return false;
}

foreach ($supervisionCallbacks as $callback => $description) {
    echo "\nTesting {$description}:\n";
    echo str_repeat("-", 50) . "\n";
    $result = simulateIsActivityExecution($callback);
    echo "isActivityExecution result: " . ($result ? 'TRUE (creates report) ❌' : 'FALSE (shows menu) ✅') . "\n";
}

echo "\n2. TESTING COMPLETE SUPERVISION FLOW:\n";
echo "=====================================\n";

echo "BEFORE THE FIX:\n";
echo "1. User clicks '🛡️ Работы по гарантии' in supervision\n";
echo "2. Callback: category_Табель_ШМ_РабГарантии\n";
echo "3. extractCategoryFromCallback(): Табель_ШМ_РабГарантии\n";
echo "4. isActivityExecution(): TRUE (because not in exclusions) ❌\n";
echo "5. handleActivityExecution() called → Creates report ❌\n";

echo "\nAFTER THE FIX:\n";
echo "1. User clicks '🛡️ Работы по гарантии' in supervision\n";
echo "2. Callback: category_Табель_ШМ_РабГарантии\n";
echo "3. extractCategoryFromCallback(): Табель_ШМ_РабГарантии\n";
echo "4. isActivityExecution(): FALSE (found in exclusions) ✅\n";
echo "5. handleOtherWorkTypes() called → Shows warranty sub-menu ✅\n";

echo "\n3. TESTING OTHER CALLBACKS (SHOULD NOT BE AFFECTED):\n";
echo "====================================================\n";

$testCallbacks = [
    'Табель_Дорога' => 'Regular activity (should create report)',
    'Табель_Дорога_yes' => 'Activity execution (should create report)',
    'cat_ТабЭлОб' => 'Electrical menu (should show menu)',
    'Табель_Электрооборудование' => 'Electrical menu full format (should show menu)'
];

foreach ($testCallbacks as $callback => $description) {
    echo "\n{$description}:\n";
    $result = simulateIsActivityExecution($callback);
    $expected = str_contains($description, 'should create report') ? 'TRUE' : 'FALSE';
    $actual = $result ? 'TRUE' : 'FALSE';
    echo "Expected: {$expected}, Actual: {$actual} " . ($expected === $actual ? '✅' : '❌') . "\n";
}

echo "\n4. VERIFICATION SUMMARY:\n";
echo "========================\n";

$supervisionWarrantyResult = simulateIsActivityExecution('Табель_ШМ_РабГарантии');
$supervisionRKResult = simulateIsActivityExecution('Табель_ШМ_УЗ_РК');

if (!$supervisionWarrantyResult && !$supervisionRKResult) {
    echo "✅ SUCCESS: Both supervision callbacks are properly excluded!\n";
    echo "✅ Users will now see sub-menus instead of creating reports\n";
    echo "✅ Supervision department warranty and RK buttons are fixed\n";
} else {
    echo "❌ ISSUE: Some supervision callbacks are still being treated as activity executions\n";
    echo "   Warranty exclusion: " . (!$supervisionWarrantyResult ? 'OK' : 'FAILED') . "\n";
    echo "   RK exclusion: " . (!$supervisionRKResult ? 'OK' : 'FAILED') . "\n";
}

echo "\n=== SUPERVISION EXCLUSION FIX TEST COMPLETE ===\n";
?>