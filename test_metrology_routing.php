<?php
// Test metrology routing to diagnose the report generation issue

echo "=== METROLOGY ROUTING DEBUG TEST ===\n\n";

// Test different metrology callbacks
$metrologyCallbacks = [
    'cat_ТабМетрол_РабПоГр',      // Works by schedule button
    'cat_ТабМетрол_РабПоГр_yes',  // Confirmation callback
    'cat_ТабМетрол_ФормИД',       // ID formation button
    'cat_ТабМетрол_ФормИД_yes',   // ID formation confirmation
    'cat_ТабМетрол_ИзмКонф',      // Additional work
    'cat_ТабМетрол_ИзмКонф_yes',  // Additional work confirmation
    'cat_ТабМетрол_ПодтВех',      // Milestone button
    'cat_ТабМетрол_ПодтВех_yes'   // Milestone confirmation
];

// Expected behavior for each callback
$expectedBehavior = [
    'cat_ТабМетрол_РабПоГр'     => 'Show confirmation dialog',
    'cat_ТабМетрол_РабПоГр_yes' => 'Generate report in tabel',
    'cat_ТабМетрол_ФормИД'      => 'Show confirmation dialog',
    'cat_ТабМетрол_ФормИД_yes'  => 'Generate report in tabel',
    'cat_ТабМетрол_ИзмКонф'     => 'Show confirmation dialog',
    'cat_ТабМетрол_ИзмКонф_yes' => 'Generate report in tabel',
    'cat_ТабМетрол_ПодтВех'     => 'Show confirmation dialog',
    'cat_ТабМетрол_ПодтВех_yes' => 'Generate report in tabel'
];

echo "Testing each metrology callback:\n";
echo str_repeat("-", 50) . "\n";

foreach ($metrologyCallbacks as $callback) {
    echo "\nCallback: {$callback}\n";
    echo "Expected: " . ($expectedBehavior[$callback] ?? 'Unknown') . "\n";
    
    // Check which handler method would process this callback
    if (str_ends_with($callback, '_yes')) {
        echo "Handler: Should route to handleActivityExecution()\n";
        
        // Check if it matches the metrology execution pattern
        if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $callback, $matches)) {
            $shortActivityKey = $matches[1];
            echo "Activity key: {$shortActivityKey}\n";
            
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_Метрол',
                'ФормИД' => 'ФормИД_Метрол',
                'ПодтВех' => 'ПодтВех_Метрол',
                'ИзмКонф' => 'ИзмКонф_Метрол',
                'КалПов' => 'КалПов_Метрол',
                'КМХ' => 'КМХ_Метрол',
                'КоррХар' => 'КоррХар_Метрол',
                'Проч' => 'Проч_Метрол'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
                echo "Maps to activity type: {$activityType}\n";
                echo "Status: ✅ SHOULD WORK - Pattern matches\n";
            } else {
                echo "Status: ❌ MISSING MAPPING for key: {$shortActivityKey}\n";
            }
        } else {
            echo "Status: ❌ PATTERN MISMATCH\n";
        }
    } else {
        echo "Handler: Should route to handleOtherWorkTypes()\n";
        echo "Status: ✅ SHOULD WORK - Show confirmation dialog\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "DIAGNOSIS SUMMARY:\n";
echo str_repeat("=", 50) . "\n";

echo "\n1. CONFIRMATION DIALOGS (non-_yes callbacks):\n";
echo "   - Should work fine via handleOtherWorkTypes()\n";
echo "   - These show the confirmation dialog\n";

echo "\n2. REPORT GENERATION (_yes callbacks):\n";
echo "   - Should work via isActivityExecution() -> handleActivityExecution()\n";
echo "   - Pattern: /^cat_ТабМетрол_(.+)_yes$/u\n";
echo "   - Mapping exists for all metrology activity keys\n";

echo "\n3. POTENTIAL ISSUES:\n";
echo "   - Check if user has proper project_code set\n";
echo "   - Check if user has FIO and functional_direction set\n";
echo "   - Check if TabelEntry->addEntry() is being called\n";
echo "   - Check server logs for any errors during execution\n";

echo "\n4. DEBUGGING STEPS:\n";
echo "   - Add error_log statements in handleActivityExecution()\n";
echo "   - Check conversation notes for project_code\n";
echo "   - Verify TabelEntry->createTabelMessage() is called\n";
echo "   - Check if DatabaseService->addUserTabel() succeeds\n";

echo "\nTest completed.\n";
?>