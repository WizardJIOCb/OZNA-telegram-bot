<?php
/*
 * Quick test to verify ШМР "Работы по графику" execution is working
 * This will help us confirm the fix
 */

echo "🔧 Testing ШМР Schedule Execution Fix\n";
echo "=====================================\n\n";

// Test 1: Verify the callback pattern works
$testCallback = 'cat_ШМР_ВхКонтр_РабПоГр_yes';
echo "✅ Test callback: '{$testCallback}'\n";

// Test 2: Check pattern matching
if (preg_match('/^cat_ШМР_(.+)_РабПоГр_yes$/u', $testCallback, $matches)) {
    echo "✅ Pattern matches correctly\n";
    echo "✅ Extracted key: '{$matches[1]}'\n";
    
    // Test 3: Check mapping
    $shortToFullMap = [
        'ВхКонтр' => 'ШМР_ВходнойКонтроль_РабПоГр',
        'ЗавОбор' => 'ШМР_ЗавозОборудования_РабПоГр',
        // ... other mappings
    ];
    
    $shortActivityKey = $matches[1];
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        echo "✅ Activity type mapped: '{$activityType}'\n\n";
        
        echo "🎯 EXPECTED BEHAVIOR:\n";
        echo "1. User clicks 'Работы по графику' → confirmation dialog appears\n";
        echo "2. User clicks 'Да' → callback '{$testCallback}' is sent\n";
        echo "3. System maps to activity type: '{$activityType}'\n";
        echo "4. Tabel entry is created and report is sent\n\n";
        
        echo "🔍 IF REPORTS ARE NOT SENDING:\n";
        echo "- Check that user has FIO and functional_direction set\n";
        echo "- Verify the handleActivityExecution method reaches addEntry call\n";
        echo "- Check for any errors in the addEntry method execution\n";
        
    } else {
        echo "❌ Failed to map activity type\n";
    }
} else {
    echo "❌ Pattern does not match\n";
}

echo "\n🚀 Next step: Test with a real user to confirm functionality\n";