<?php
/**
 * Diagnose manual ID input callback issue
 * Following safety protocols for non-programmer user
 */

echo "=== DIAGNOSING MANUAL ID INPUT CALLBACKS ===\n\n";

// Check what callbacks should be generated for manual input
echo "1. Expected callback patterns for manual input:\n";
$expectedCallbacks = [
    'proj_ctrl_group_manual_ОИ789' => 'Manual group ID input for project ОИ789',
    'proj_ctrl_chat_manual_ОИ789' => 'Manual chat ID input for project ОИ789'
];

foreach ($expectedCallbacks as $callback => $description) {
    echo "   $callback → $description\n";
}

// Check ProjectHandler for these patterns
echo "\n2. Checking ProjectHandler for manual input handlers...\n";
$handlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$content = file_get_contents($handlerFile);

$manualPatterns = [
    'proj_ctrl_group_manual_' => 'Group manual input handler',
    'proj_ctrl_chat_manual_' => 'Chat manual input handler'
];

$missingPatterns = [];
foreach ($manualPatterns as $pattern => $description) {
    if (strpos($content, $pattern) !== false) {
        echo "   ✓ Found: $pattern\n";
    } else {
        echo "   ✗ Missing: $pattern ($description)\n";
        $missingPatterns[] = $pattern;
    }
}

// Check the showGroupConfiguration and showChatConfiguration methods
echo "\n3. Checking manual input button generation...\n";
if (strpos($content, 'proj_ctrl_group_manual_') !== false) {
    echo "   ✓ Group manual button callback is generated\n";
} else {
    echo "   ? Group manual button callback may not be generated\n";
}

if (strpos($content, 'proj_ctrl_chat_manual_') !== false) {
    echo "   ✓ Chat manual button callback is generated\n";
} else {
    echo "   ? Chat manual button callback may not be generated\n";
}

// Simulate the missing patterns
echo "\n4. Simulating manual input callback processing...\n";
$testCallbacks = [
    'proj_ctrl_group_manual_ОИ789',
    'proj_ctrl_chat_manual_ОИ789'
];

foreach ($testCallbacks as $callback) {
    echo "   Testing: $callback\n";
    
    if (str_starts_with($callback, 'proj_ctrl_group_manual_')) {
        $projectCode = str_replace('proj_ctrl_group_manual_', '', $callback);
        echo "     ✓ Should extract project: $projectCode\n";
        echo "     ✓ Should show manual group ID input interface\n";
    }
    
    if (str_starts_with($callback, 'proj_ctrl_chat_manual_')) {
        $projectCode = str_replace('proj_ctrl_chat_manual_', '', $callback);
        echo "     ✓ Should extract project: $projectCode\n";
        echo "     ✓ Should show manual chat ID input interface\n";
    }
}

echo "\n=== DIAGNOSIS SUMMARY ===\n";
if (!empty($missingPatterns)) {
    echo "🔧 ISSUE IDENTIFIED: Missing manual input handlers\n";
    echo "   The following callback patterns need handlers:\n";
    foreach ($missingPatterns as $pattern) {
        echo "   • $pattern\n";
    }
    echo "\n📝 SOLUTION NEEDED:\n";
    echo "   Add handlers for manual ID input callbacks in handleControlProjectsCallback()\n";
} else {
    echo "✅ All manual input handlers are present\n";
    echo "   The issue might be elsewhere in the callback processing chain\n";
}

echo "\n🎯 NEXT STEPS:\n";
echo "1. Add missing manual input handlers\n";
echo "2. Implement manual input interface methods\n";
echo "3. Test the manual input functionality\n";

?>