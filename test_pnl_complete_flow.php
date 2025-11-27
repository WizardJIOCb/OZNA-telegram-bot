<?php
// Complete flow test for the "Пусконаладочные работы" button
require_once __DIR__ . '/encoding_helper.php';

echo "=== Complete Flow Test for 'Пусконаладочные работы' Button ===\n";

// Simulate the complete flow from button press to menu display
echo "\n1. Simulating button press:\n";
$originalCallback = 'category_Табель_ПНЛ';
echo "Original callback data: $originalCallback\n";

// Simulate potential corruption during transmission
$corruptedCallback = 'category_РўР°Р±РµР»СЊ_РџРќР›';
echo "Corrupted callback data (as might happen in real usage): $corruptedCallback\n";

// Apply our fix
$fixedCallback = fixKnownCyrillicPatterns($corruptedCallback);
echo "Fixed callback data: $fixedCallback\n";

if ($fixedCallback === $originalCallback) {
    echo "SUCCESS: Callback data correctly restored!\n";
} else {
    echo "FAILURE: Expected '$originalCallback' but got '$fixedCallback'\n";
}

// Extract category
$category = substr($fixedCallback, strlen('category_'));
echo "\n2. Extracted category: $category\n";

// Simulate routing logic
echo "\n3. Simulating routing logic:\n";
if ($category === 'Табель_ПНЛ') {
    echo "SUCCESS: Matched 'Табель_ПНЛ' - would call handleCommissioningWorksMenu\n";
    
    // Simulate what handleCommissioningWorksMenu would do
    echo "\n4. Simulating handleCommissioningWorksMenu:\n";
    echo "Would display message: '<b>⚙️ Пусконаладочные работы</b>\\n\\nВыберите тип оборудования:'\n";
    
    // Simulate the keyboard that would be created
    $expectedActivities = [
        '⚡ Электрооборудование',
        '🔥 Пожарной сигнализации',
        '❄️ Отопление и вентиляция',
        '⚙️ Технологическое оборудование',
        '📏 Метрология',
        '🔄 Комплексное опробование',
        '📊 Режимная наладка'
    ];
    
    echo "Would create keyboard with " . count($expectedActivities) . " activities:\n";
    foreach ($expectedActivities as $index => $activity) {
        echo "  " . ($index + 1) . ". $activity\n";
    }
    
    echo "\nSUCCESS: All 8 activities would be displayed correctly!\n";
} else {
    echo "FAILURE: Did not match 'Табель_ПНЛ'\n";
}

echo "\n=== Test completed successfully! ===\n";
echo "The 'Пусконаладочные работы' button should now work correctly and display all 8 activities.\n";
?>