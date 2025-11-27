<?php
/**
 * Debug script to test milestone confirmation callback flow
 */

echo "🔍 MILESTONE CONFIRMATION DEBUG\n";
echo "==============================\n\n";

// Test the exact callback pattern that should be received
$testCallback = 'ШМР_ПодтВех_yes';

echo "Testing callback: {$testCallback}\n\n";

// Test simple supervision pattern
$simplePattern = '/^ШМР_(.+)_yes$/u';
$matches = [];
$isSimpleMatch = preg_match($simplePattern, $testCallback, $matches);

echo "1. SIMPLE SUPERVISION PATTERN TEST\n";
echo "Pattern: {$simplePattern}\n";
echo "Match: " . ($isSimpleMatch ? "✅ YES" : "❌ NO") . "\n";

if ($isSimpleMatch) {
    echo "Extracted key: '{$matches[1]}'\n";
    
    // Test mapping
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    if (isset($simpleSupervisionMap[$matches[1]])) {
        echo "✅ Key found in map: {$simpleSupervisionMap[$matches[1]]}\n";
    } else {
        echo "❌ Key NOT found in map!\n";
    }
}

echo "\n2. ACTIVITY TYPE VALIDATION\n";
echo "===========================\n";

// Check if the activity type exists in the mappings
$activityTypeMappings = [
    'ПодтверждениеВехи_ШМР' => '✅ Этап шеф-монтажа завершен',
];

$activityType = 'ПодтверждениеВехи_ШМР';
if (isset($activityTypeMappings[$activityType])) {
    echo "✅ Activity type exists: {$activityType}\n";
    echo "Display text: {$activityTypeMappings[$activityType]}\n";
} else {
    echo "❌ Activity type NOT found: {$activityType}\n";
}

echo "\n3. COMMISSIONING ACTIVITIES CHECK\n";
echo "=================================\n";

// Check if it's in commissioning activities list
$commissioningActivities = [
    'ПодтверждениеВехи_ШМР', 'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
];

if (in_array($activityType, $commissioningActivities)) {
    echo "✅ Activity type is in commissioning activities list\n";
} else {
    echo "❌ Activity type NOT in commissioning activities list\n";
}

echo "\n🎯 SUMMARY\n";
echo "=========\n";
echo "If all checks pass, milestone confirmation should work.\n";
echo "If any check fails, that's the source of the problem.\n";