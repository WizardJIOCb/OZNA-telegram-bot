<?php
/**
 * Debug script to trace milestone callback routing
 */

echo "🔍 MILESTONE CALLBACK ROUTING DEBUG\n";
echo "==================================\n\n";

// Test the milestone callback routing step by step
$milestoneCallback = 'ШМР_ПодтВех_yes';

echo "1. MILESTONE CALLBACK RECEIVED\n";
echo "=============================\n";
echo "Expected callback: {$milestoneCallback}\n\n";

// Test isActivityExecution pattern matching
$simplePattern = '/^ШМР_(.+)_yes$/u';
$matches = [];
$isMatch = preg_match($simplePattern, $milestoneCallback, $matches);

echo "2. PATTERN MATCHING TEST\n";
echo "========================\n";
echo "Pattern: {$simplePattern}\n";
echo "Match: " . ($isMatch ? "✅ YES" : "❌ NO") . "\n";

if ($isMatch) {
    echo "Extracted key: '{$matches[1]}'\n";
    
    // Test simple supervision mapping
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    if (isset($simpleSupervisionMap[$matches[1]])) {
        $activityType = $simpleSupervisionMap[$matches[1]];
        echo "✅ Key mapped to: {$activityType}\n";
    } else {
        echo "❌ Key NOT found in simple supervision map!\n";
    }
}

echo "\n3. ACTIVITY TYPE VALIDATION\n";
echo "===========================\n";

// Check if the activity type is valid
$activityTypeMappings = [
    'ПодтверждениеВехи_ШМР' => '✅ Этап шеф-монтажа завершен',
];

$expectedActivityType = 'ПодтверждениеВехи_ШМР';
if (isset($activityTypeMappings[$expectedActivityType])) {
    echo "✅ Activity type valid: {$expectedActivityType}\n";
    echo "Display text: {$activityTypeMappings[$expectedActivityType]}\n";
} else {
    echo "❌ Activity type INVALID: {$expectedActivityType}\n";
}

echo "\n4. ROUTING ANALYSIS\n";
echo "===================\n";
echo "Expected flow:\n";
echo "1. Callback: {$milestoneCallback}\n";
echo "2. isActivityExecution() → TRUE (simple supervision pattern)\n";
echo "3. handleActivityExecution() → Process milestone\n";
echo "4. Activity type: ПодтверждениеВехи_ШМР\n";
echo "5. Success message displayed\n";

echo "\n🔍 POSSIBLE ISSUES:\n";
echo "==================\n";
echo "1. Callback format might be different than expected\n";
echo "2. Pattern matching might be failing\n";
echo "3. Activity type might not be properly mapped\n";
echo "4. Error in handleActivityExecution routing\n";

echo "\nNext step: Check actual callback data being received\n";