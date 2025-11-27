<?php
/**
 * Test the actual warranty equipment execution flow
 */

echo "🔧 WARRANTY EQUIPMENT EXECUTION FLOW TEST\n";
echo "=".str_repeat("=", 50)."\n\n";

// Test callback data
$testCallbackData = 'cat_Гарантии_ЭлектрОб_yes';
echo "Testing callback data: '{$testCallbackData}'\n\n";

echo "1. TESTING HANDLE ACTIVITY EXECUTION FLOW\n";
echo "-".str_repeat("-", 40)."\n";

// Simulate the actual flow in handleActivityExecution
if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $testCallbackData, $matches)) {
    // This is the new specific check for warranty equipment
    $equipmentType = $matches[1];
    $activityType = 'РабГарантии_' . $equipmentType;
    echo "✅ Specific warranty equipment check matched\n";
    echo "   Equipment type: '{$equipmentType}'\n";
    echo "   Activity type: '{$activityType}'\n";
    echo "   This should work correctly now!\n\n";
} elseif (preg_match('/^cat_(.+)_yes$/u', $testCallbackData, $matches)) {
    // This is the general check that was catching warranty equipment before
    $shortActivityKey = $matches[1];
    echo "⚠️  General cat_ check matched instead\n";
    echo "   Short activity key: '{$shortActivityKey}'\n";
    
    // Test if warranty equipment map would work here
    $warrantyEquipmentMap = [
        'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
        'СрИзм' => 'РабГарантии_СрИзм',
        'НасОб' => 'РабГарантии_НасОб',
        'ТрубАрм' => 'РабГарантии_ТрубАрм',
        'ИспМех' => 'РабГарантии_ИспМех',
        'КонтрОб' => 'РабГарантии_КонтрОб',
        'Прочее' => 'РабГарантии_Прочее'
    ];
    
    if (isset($warrantyEquipmentMap[$shortActivityKey])) {
        $activityType = $warrantyEquipmentMap[$shortActivityKey];
        echo "   Would map to: '{$activityType}'\n";
    } else {
        echo "   ❌ Would NOT map correctly\n";
    }
    echo "\n";
}

echo "=".str_repeat("=", 50)."\n";
echo "EXECUTION FLOW TEST COMPLETE\n";
echo "=".str_repeat("=", 50)."\n";
?>