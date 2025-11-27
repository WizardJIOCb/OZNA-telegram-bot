<?php
/**
 * CRITICAL DEBUG: Trace the exact execution path for supervision installation works
 */

echo "🚨 CRITICAL DEBUG: SUPERVISION EXECUTION PATH\n";
echo "============================================\n\n";

echo "🎯 PROBLEM CONFIRMED:\n";
echo "====================\n";
echo "• ✅ 'ШМ_СдачаАвансовогоОтчета' IS in supervisionInstallationWorks array\n";
echo "• ✅ Activity mapping exists: 'ШМ_СдачаАвансовогоОтчета' → '💰 Сдача авансового отчета'\n";
echo "• ✅ Format condition exists: in_array(\$activityType, \$supervisionInstallationWorks)\n";
echo "• ❌ But old format is still being used!\n\n";

echo "🔍 HYPOTHESES TO TEST:\n";
echo "======================\n\n";

echo "HYPOTHESIS 1: Activity type is not being set correctly\n";
echo "• Maybe handleActivityExecution() is not setting \$activityType\n";
echo "• Maybe the pattern is not matching\n";
echo "• Maybe the mapping is not working\n\n";

echo "HYPOTHESIS 2: Activity type is being overwritten\n";
echo "• Maybe later code is changing \$activityType\n";
echo "• Maybe fallback logic is kicking in\n\n";

echo "HYPOTHESIS 3: Wrong format branch is being taken\n";
echo "• Maybe supervision installation works format condition comes AFTER another condition\n";
echo "• Maybe the format logic order is wrong\n\n";

echo "🔧 DEBUG STRATEGY:\n";
echo "==================\n\n";

echo "Let's trace the exact execution path:\n\n";

// Simulate the handleActivityExecution logic
$testCallback = 'cat_ШМР_СдачаОтч_yes';
echo "TEST CALLBACK: '{$testCallback}'\n\n";

echo "Step 1: Pattern matching in handleActivityExecution\n";
echo "Pattern: /^cat_ШМР_(.+)_yes$/u\n";
if (preg_match('/^cat_ШМР_(.+)_yes$/u', $testCallback, $matches)) {
    echo "✅ Pattern matches!\n";
    echo "Captured group: '{$matches[1]}'\n";
    
    $shortActivityKey = $matches[1];
    echo "Short activity key: '{$shortActivityKey}'\n";
    
    // Test mapping
    $shortToFullMap = [
        'РабПоГр' => 'ШМ_РабПоГрафику',
        'ПодгПоезд' => 'ШМ_ПодготовкаКПоездке',
        'Дорога' => 'ШМ_Дорога',
        'Инстр' => 'ШМ_Инструктаж',
        'СдачаОтч' => 'ШМ_СдачаАвансовогоОтчета'
    ];
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $activityType = $shortToFullMap[$shortActivityKey];
        echo "✅ Mapping found: '{$shortActivityKey}' → '{$activityType}'\n";
    } else {
        echo "❌ No mapping found for: '{$shortActivityKey}'\n";
        $activityType = '';
    }
} else {
    echo "❌ Pattern does not match!\n";
    $activityType = '';
}

echo "\nStep 2: Check if activity type is in supervision installation works array\n";
$supervisionInstallationWorks = [
    'ШМ_РабПоГрафику', 'ШМ_ПодготовкаКПоездке', 'ШМ_Дорога', 'ШМ_Инструктаж', 'ШМ_СдачаАвансовогоОтчета'
];

if (!empty($activityType) && in_array($activityType, $supervisionInstallationWorks)) {
    echo "✅ Activity type '{$activityType}' IS in supervision installation works array\n";
    echo "✅ Should use supervision format\n";
} else {
    echo "❌ Activity type '{$activityType}' is NOT in supervision installation works array\n";
    echo "❌ Will use default format\n";
}

echo "\nStep 3: Check activity mapping\n";
$activityTypeMappings = [
    'ШМ_СдачаАвансовогоОтчета' => '💰 Сдача авансового отчета'
];

if (!empty($activityType) && isset($activityTypeMappings[$activityType])) {
    $activityText = $activityTypeMappings[$activityType];
    echo "✅ Activity text found: '{$activityType}' → '{$activityText}'\n";
} else {
    echo "❌ No activity text mapping for: '{$activityType}'\n";
    $activityText = $activityType;
}

echo "\n🎯 MOST LIKELY ISSUE:\n";
echo "====================\n\n";

echo "Based on this analysis, the issue is probably:\n";
echo "1. ❌ Activity type is being set correctly in handleActivityExecution\n";
echo "2. ❌ Activity type is in the supervision installation works array\n";
echo "3. ❌ Activity mapping exists\n";
echo "4. 🚨 BUT something is preventing the supervision format from being used!\n\n";

echo "CRITICAL QUESTIONS:\n";
echo "• Is the supervision format condition coming AFTER another format condition?\n";
echo "• Is there a format priority issue in TabelEntry.createTabelMessage()?\n";
echo "• Is the activity type being changed between handleActivityExecution and createTabelMessage?\n\n";

echo "🔍 NEXT INVESTIGATION:\n";
echo "======================\n";
echo "Check the exact order of format conditions in TabelEntry.createTabelMessage()\n";
echo "and see if supervision installation works is being caught by an earlier condition.\n";

?>