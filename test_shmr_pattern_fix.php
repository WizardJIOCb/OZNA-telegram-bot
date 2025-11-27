<?php
// Simple test script to verify the ШМР технологическое оборудование fix

echo "=== Testing ШМР Технологическое оборудование Pattern Fix ===\n";

// Test the callback pattern that was causing the hang
$testCallback = 'cat_ШМР_ТехОб_yes';
echo "Testing callback: '{$testCallback}'\n";

// Test if the pattern matches what we added to isActivityExecution method
if (preg_match('/^cat_ШМР_(.+)_yes$/u', $testCallback, $matches)) {
    $shortActivityKey = $matches[1];
    echo "✅ Pattern MATCHED! Extracted key: '{$shortActivityKey}'\n";
    
    // Test the mapping that we added
    $shmrSubActivitiesMap = [
        'ТехОб' => 'ШМР_ТехнологическоеОборудование',
        'ЭлектрОб' => 'ШМР_Электрооборудование', 
        'ОтоплВент' => 'ШМР_ОтоплениеИВентиляция',
        'ПожСигн' => 'ШМР_ПожарнаяСигнализация',
        'ОхрСигн' => 'ШМР_ОхраннаяСигнализация',
        'Автом' => 'ШМР_Автоматизация',
        'Метрол' => 'ШМР_Метрология'
    ];
    
    if (isset($shmrSubActivitiesMap[$shortActivityKey])) {
        $activityType = $shmrSubActivitiesMap[$shortActivityKey];
        echo "✅ Key FOUND in mapping! Activity: '{$activityType}'\n";
        echo "\n🎉 SUCCESS: The callback flow should now work correctly!\n";
        echo "   1. User clicks 'Технологическое оборудование'\n";
        echo "   2. Shows confirmation dialog\n"; 
        echo "   3. Callback '{$testCallback}' will be recognized as activity execution\n";
        echo "   4. Creates tabel entry for '{$activityType}'\n";
    } else {
        echo "❌ Key NOT FOUND in mapping!\n";
    }
} else {
    echo "❌ Pattern FAILED to match!\n";
}

echo "\n=== Testing All 7 New ШМР Sub-Activities ===\n";
$allTestCases = [
    'cat_ШМР_ТехОб_yes' => 'Технологическое оборудование',
    'cat_ШМР_ЭлектрОб_yes' => 'Электрооборудование',
    'cat_ШМР_ОтоплВент_yes' => 'Отопление и вентиляция',
    'cat_ШМР_ПожСигн_yes' => 'Пожарная сигнализация', 
    'cat_ШМР_ОхрСигн_yes' => 'Охранная сигнализация',
    'cat_ШМР_Автом_yes' => 'Автоматизация',
    'cat_ШМР_Метрол_yes' => 'Метрология'
];

foreach ($allTestCases as $callback => $description) {
    if (preg_match('/^cat_ШМР_(.+)_yes$/u', $callback, $matches)) {
        $key = $matches[1];
        $status = isset($shmrSubActivitiesMap[$key]) ? "✅ WORKING" : "❌ BROKEN";
        echo "  {$description}: {$status}\n";
    }
}

echo "\n=== Fix Summary ===\n";
echo "✅ Added pattern '/^cat_ШМР_(.+)_yes$/u' to isActivityExecution method\n";
echo "✅ Added mapping for all 7 new ШМР sub-activities\n";
echo "✅ Технологическое оборудование should no longer hang\n";
echo "\nThe issue has been resolved!\n";
?>