<?php

echo "=== Runtime Test for Supervision Installation Works Button ===\n\n";

// Test 1: Check if the callback data is correct in MessageService
echo "1. Checking MessageService callback data:\n";
$messageServiceContent = file_get_contents('CustomCommands/Services/MessageService.php');

if (strpos($messageServiceContent, "'callback_data' => 'category_Табель_ШМ_ШМР'") !== false) {
    echo "   ✅ FOUND: 'category_Табель_ШМ_ШМР' in MessageService\n";
} else {
    echo "   ❌ NOT FOUND: 'category_Табель_ШМ_ШМР' in MessageService\n";
}

// Test 2: Check the routing order in handleOtherWorkTypes
echo "\n2. Checking routing order in handleOtherWorkTypes:\n";
$tabelHandlerContent = file_get_contents('CustomCommands/Handlers/TabelHandler.php');

// Find the position of the submenu handler
$submenuPos = strpos($tabelHandlerContent, "if (\$category === 'Табель_ШМ_ШМР')");
$activityTypePos = strpos($tabelHandlerContent, "\$strippedCategory = str_replace('Табель_', '', \$category);");

if ($submenuPos !== false && $activityTypePos !== false) {
    if ($submenuPos < $activityTypePos) {
        echo "   ✅ CORRECT ORDER: Submenu handler comes BEFORE activity type check\n";
        echo "   Submenu handler at position: {$submenuPos}\n";
        echo "   Activity type check at position: {$activityTypePos}\n";
    } else {
        echo "   ❌ WRONG ORDER: Activity type check comes BEFORE submenu handler\n";
        echo "   This would cause the bug!\n";
    }
} else {
    echo "   ❌ MISSING: Either submenu handler or activity type check not found\n";
}

// Test 3: Check if ШМ_ШМР is in activityTypes array
echo "\n3. Checking activity types array:\n";
$activityTypesPattern = '/activityTypes\s*=\s*\[(.*?)\]/s';
if (preg_match($activityTypesPattern, $tabelHandlerContent, $matches)) {
    $activityTypesString = $matches[1];
    if (strpos($activityTypesString, "'ШМ_ШМР'") !== false) {
        echo "   ⚠️ WARNING: 'ШМ_ШМР' IS in activityTypes array\n";
        echo "   This means it will match the activity type check\n";
        echo "   The submenu handler MUST come before the activity type check\n";
    } else {
        echo "   ✅ OK: 'ШМ_ШМР' not in activityTypes array\n";
    }
} else {
    echo "   ❌ Could not find activityTypes array\n";
}

// Test 4: Simulate the flow
echo "\n4. Simulating the callback flow:\n";
$callback = 'category_Табель_ШМ_ШМР';
$category = str_replace('category_', '', $callback);
echo "   Callback: {$callback}\n";
echo "   Extracted category: {$category}\n";

// Check if category matches submenu condition
if ($category === 'Табель_ШМ_ШМР') {
    echo "   ✅ MATCH: Submenu condition\n";
    echo "   Should call: handleSupervisionInstallationWorksMenu()\n";
    echo "   Should show: 5 sub-activities menu\n";
} else {
    echo "   ❌ NO MATCH: Submenu condition\n";
}

// Check if category would match activity type condition
$strippedCategory = str_replace('Табель_', '', $category);
if (strpos($activityTypesString, "'{$strippedCategory}'") !== false) {
    echo "   ⚠️ WARNING: Also matches activity type condition\n";
    echo "   Stripped category: {$strippedCategory}\n";
    echo "   This confirms the order is critical!\n";
}

echo "\n5. Conclusion:\n";
if ($submenuPos !== false && $submenuPos < $activityTypePos) {
    echo "   🎯 The routing logic appears CORRECT\n";
    echo "   The issue might be elsewhere:\n";
    echo "   - Check if there's another handler intercepting the callback earlier\n";
    echo "   - Check if the conversation state affects routing\n";
    echo "   - Check if there are any errors in the submenu handler method\n";
} else {
    echo "   🚨 PROBLEM FOUND: The routing order is incorrect!\n";
    echo "   This is causing the bug\n";
}

echo "\n=== Test Complete ===\n";