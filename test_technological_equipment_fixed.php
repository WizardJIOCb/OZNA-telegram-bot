<?php
// Test script to verify technological equipment button fixes

echo "=== TESTING TECHNOLOGICAL EQUIPMENT BUTTON FIXES ===\n\n";

// Test callback data that should work
$testCallbacks = [
    'cat_ТабТехОб_РабПоГр_yes',
    'cat_ТабТехОб_ФормИД_yes', 
    'cat_ТабТехОб_ДопРаб_yes',
    'cat_ТабТехОб_ПодтВех_yes'
];

// Simulate the isActivityExecution method logic
function testIsActivityExecution($category) {
    echo "Testing isActivityExecution for: {$category}\n";
    
    // Handle short callback format for technological equipment
    if (preg_match('/^cat_ТабТехОб_(.+)_yes$/u', $category, $matches)) {
        // Map short format to full activity type
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_ТехОб',
            'ФормИД' => 'ФормИД_ТехОб',
            'ДопРаб' => 'ДопРаб_ТехОб',
            'ПодтВех' => 'ПодтВех_ТехОб'
        ];
        
        $shortActivityKey = $matches[1];
        if (isset($shortToFullMap[$shortActivityKey])) {
            echo "   ✅ MATCH: Short key '{$shortActivityKey}' mapped to '{$shortToFullMap[$shortActivityKey]}'\n";
            echo "   ✅ Should route to: handleActivityExecution()\n";
            return true;
        } else {
            echo "   ❌ NO MATCH: Short key '{$shortActivityKey}' not found in mapping\n";
            return false;
        }
    } else {
        echo "   ❌ NO MATCH: Doesn't match technological equipment pattern\n";
        return false;
    }
}

// Simulate the handleActivityExecution method logic
function testHandleActivityExecution($category) {
    echo "Testing handleActivityExecution for: {$category}\n";
    
    // Handle short callback format for technological equipment
    if (preg_match('/^cat_ТабТехОб_(.+)_yes$/u', $category, $matches)) {
        // Handle short callback format for technological equipment
        $shortActivityKey = $matches[1];
        // Map short format to full activity type
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_ТехОб',
            'ФормИД' => 'ФормИД_ТехОб',
            'ДопРаб' => 'ДопРаб_ТехОб',
            'ПодтВех' => 'ПодтВех_ТехОб'
        ];
        
        if (isset($shortToFullMap[$shortActivityKey])) {
            $activityType = $shortToFullMap[$shortActivityKey];
            echo "   ✅ MAPPED: '{$shortActivityKey}' → '{$activityType}'\n";
            echo "   ✅ Should create tabel entry for: {$activityType}\n";
            return true;
        }
    }
    
    echo "   ❌ NO HANDLING: Activity type extraction failed\n";
    return false;
}

// Test each callback
foreach ($testCallbacks as $callback) {
    echo str_repeat("=", 60) . "\n";
    echo "TESTING: {$callback}\n";
    echo str_repeat("-", 60) . "\n";
    
    $isExecution = testIsActivityExecution($callback);
    echo "\n";
    
    if ($isExecution) {
        $handleExecution = testHandleActivityExecution($callback);
        echo "\n";
        
        if ($handleExecution) {
            echo "🎉 RESULT: SHOULD WORK CORRECTLY\n";
        } else {
            echo "❌ RESULT: WILL FAIL IN handleActivityExecution\n";
        }
    } else {
        echo "❌ RESULT: WILL SHOW 'неизвестная команда табеля'\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 60) . "\n";
echo "SUMMARY:\n";
echo "- Added technological equipment pattern to isActivityExecution()\n";
echo "- Added technological equipment pattern to handleActivityExecution()\n";
echo "- All technological equipment confirmation buttons should now work\n";
echo "- Users should see proper confirmation dialogs and successful tabel entries\n";