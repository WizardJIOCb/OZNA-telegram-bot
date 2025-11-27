<?php
/**
 * Debug script to test warranty equipment confirmation flow step by step
 */

echo "=== DEBUGGING WARRANTY EQUIPMENT CONFIRMATION FLOW ===\n\n";

// Simulate the exact warranty equipment confirmation callback that should be processed
$testCallback = 'cat_Гарантии_ЭлектрОб_yes';
echo "Testing warranty equipment confirmation: {$testCallback}\n\n";

// Step 1: Test if callback is properly mapped in extractCategoryFromCallback
echo "1. TESTING extractCategoryFromCallback MAPPING:\n";
$shortFormats = [
    'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes' => 'РабГарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes' => 'РабГарантии_НасОб_yes'
];

if (isset($shortFormats[$testCallback])) {
    $mapped = $shortFormats[$testCallback];
    echo "✅ Callback mapped: {$testCallback} -> {$mapped}\n";
} else {
    echo "❌ Callback NOT mapped: {$testCallback}\n";
}

// Step 2: Test if callback matches isActivityExecution pattern
echo "\n2. TESTING isActivityExecution PATTERN MATCHING:\n";
if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $testCallback, $matches)) {
    $equipmentType = $matches[1];
    echo "✅ Pattern matches: Equipment type = {$equipmentType}\n";
    
    $warrantyEquipmentMap = [
        'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
        'СрИзм' => 'РабГарантии_СрИзм',
        'НасОб' => 'РабГарантии_НасОб'
    ];
    
    if (isset($warrantyEquipmentMap[$equipmentType])) {
        echo "✅ Equipment type mapped: {$equipmentType} -> {$warrantyEquipmentMap[$equipmentType]}\n";
        echo "✅ isActivityExecution should return TRUE\n";
    } else {
        echo "❌ Equipment type NOT mapped: {$equipmentType}\n";
    }
} else {
    echo "❌ Pattern does NOT match: {$testCallback}\n";
}

// Step 3: Test if callback matches handleActivityExecution pattern
echo "\n3. TESTING handleActivityExecution PATTERN MATCHING:\n";
if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $testCallback, $matches)) {
    $equipmentType = $matches[1];
    $activityType = 'РабГарантии_' . $equipmentType;
    echo "✅ handleActivityExecution pattern matches\n";
    echo "✅ Will create activityType: {$activityType}\n";
} else {
    echo "❌ handleActivityExecution pattern does NOT match\n";
}

echo "\n4. TESTING CALLBACK ROUTING FLOW:\n";
echo "Expected flow:\n";
echo "1. User clicks warranty equipment button -> Creates callback: cat_Гарантии_ЭлектрОб_yes\n";
echo "2. extractCategoryFromCallback maps it to: РабГарантии_ЭлектрОб_yes\n";
echo "3. isActivityExecution detects it as activity execution: TRUE\n";
echo "4. Routes to handleActivityExecution\n";
echo "5. handleActivityExecution creates activityType: РабГарантии_ЭлектрОб\n";
echo "6. addEntry is called to create report\n";
echo "7. Report message is sent to user\n";

echo "\n=== POTENTIAL ISSUES TO CHECK ===\n";
echo "❓ Check if activityType 'РабГарантии_ЭлектрОб' exists in activityTypes array\n";
echo "❓ Check if user has required data (fio, functional_direction)\n";
echo "❓ Check if addEntry method is actually being called\n";
echo "❓ Check if there are any validation errors preventing report creation\n";
echo "❓ Check logs for any error messages during warranty equipment processing\n";
?>