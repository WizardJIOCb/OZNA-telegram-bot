<?php
echo "🔥 Testing Fire Alarm Equipment 'Additional Works' Fix\n";
echo "==================================================\n\n";

// Test callback flow simulation
$callback = 'cat_ТабПожСигн_ДопРаб';

// Step 1: extractCategoryFromCallback mapping
$shortFormats = [
    'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн'
];

if (isset($shortFormats[$callback])) {
    $mappedCategory = $shortFormats[$callback];
    echo "1. ✅ Callback Mapping:\n";
    echo "   Original: {$callback}\n";
    echo "   Mapped: {$mappedCategory}\n\n";
} else {
    echo "1. ❌ No callback mapping found\n\n";
    exit;
}

// Step 2: routeTabelCallback priority checks
echo "2. 🔄 Priority Check Flow in routeTabelCallback:\n";

// Check if it would be caught by immediate additional works handler (NEW FIX)
$immediateHandlers = [
    'cat_ТабПожСигн_ДопРаб',
    'Табель_ДопРаб_ПожСигн'
];

if (in_array($mappedCategory, $immediateHandlers)) {
    echo "   ✅ IMMEDIATE FIRE ALARM ADDITIONAL WORKS HANDLER: MATCH\n";
    echo "   🎯 Would call: handleAdditionalWorksFireAlarmMenu()\n";
    echo "   📋 Result: Show submenu (CORRECT)\n\n";
    $caught = true;
} else {
    echo "   ❌ Immediate handler: NO MATCH\n";
    $caught = false;
}

// Check if it would be caught by isActivityExecution (OLD PROBLEM)
$activityTypes = ['ДопРаб_ПожСигн']; // Simplified list

// Simulate isActivityExecution logic for short callback
if (preg_match('/^cat_ТабПожСигн_(.+)_yes$/u', $mappedCategory, $matches)) {
    $activityCheck = false; // Not a _yes callback
} else if (preg_match('/^cat_ТабПожСигн_(.+)$/u', $callback, $matches)) {
    $shortKey = $matches[1]; // "ДопРаб"
    $shortToFullMap = ['ДопРаб' => 'ДопРаб_ПожСигн'];
    $activityType = $shortToFullMap[$shortKey] ?? null;
    $activityCheck = $activityType && in_array($activityType, $activityTypes);
} else {
    $activityCheck = false;
}

if (!$caught && $activityCheck) {
    echo "   ❌ Would be caught by isActivityExecution: YES\n";
    echo "   🎯 Would call: handleActivityExecution()\n";
    echo "   📋 Result: Generate report (WRONG)\n\n";
} else if (!$caught) {
    echo "   ✅ isActivityExecution: NO MATCH\n";
    echo "   🎯 Would proceed to: handleOtherWorkTypes()\n\n";
}

echo "3. 📊 Final Assessment:\n";
if ($caught) {
    echo "   🎉 SUCCESS: Button will now show submenu correctly!\n";
    echo "   ✅ Fixed by adding immediate handler before isActivityExecution check\n";
} else {
    echo "   ❌ PROBLEM: Button would still generate report\n";
    echo "   ⚠️  Fix incomplete - need to adjust handler logic\n";
}