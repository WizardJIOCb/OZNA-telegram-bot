<?php
/**
 * Diagnose callback handling issue for project control
 * Safety protocol: Runtime verification before making changes
 */

echo "=== DIAGNOSING PROJECT CONTROL CALLBACK ISSUE ===\n\n";

// Check the current callback monitor log to see what's being received
echo "1. Checking recent callback activity...\n";
$callbackLogFile = __DIR__ . '/callback_monitor.log';
if (file_exists($callbackLogFile)) {
    $logContent = file_get_contents($callbackLogFile);
    $lines = explode("\n", trim($logContent));
    $recentLines = array_slice($lines, -10); // Last 10 entries
    
    echo "   Recent callback activity:\n";
    foreach ($recentLines as $line) {
        if (!empty($line)) {
            echo "   > $line\n";
        }
    }
} else {
    echo "   ✗ No callback monitor log found\n";
}

// Check ProjectHandler method structure
echo "\n2. Checking ProjectHandler callback handling...\n";
$handlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$content = file_get_contents($handlerFile);

// Look for the handleControlProjectsCallback method
if (strpos($content, 'function handleControlProjectsCallback') !== false) {
    echo "   ✓ handleControlProjectsCallback method exists\n";
    
    // Check if it handles the new callback patterns
    $newPatterns = [
        'proj_ctrl_cfg_group_',
        'proj_ctrl_cfg_chat_', 
        'proj_ctrl_cfg_types_',
        'proj_ctrl_cfg_mode_',
        'proj_ctrl_test_',
        'proj_ctrl_group_select_',
        'proj_ctrl_chat_select_',
        'proj_ctrl_toggle_type_',
        'proj_ctrl_mode_select_'
    ];
    
    $missingPatterns = [];
    foreach ($newPatterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            echo "   ✓ Handles pattern: $pattern\n";
        } else {
            echo "   ✗ Missing pattern: $pattern\n";
            $missingPatterns[] = $pattern;
        }
    }
    
    if (!empty($missingPatterns)) {
        echo "\n   📝 Missing patterns need to be added to handleControlProjectsCallback\n";
    }
    
} else {
    echo "   ✗ handleControlProjectsCallback method not found\n";
}

// Check if the missing methods are actually defined
echo "\n3. Checking required helper methods...\n";
$requiredMethods = [
    'saveGroupConfiguration',
    'saveChatConfiguration', 
    'toggleNotificationType',
    'saveNotificationMode'
];

$missingMethods = [];
foreach ($requiredMethods as $method) {
    if (strpos($content, "function {$method}") !== false) {
        echo "   ✓ Method exists: $method\n";
    } else {
        echo "   ✗ Method missing: $method\n";
        $missingMethods[] = $method;
    }
}

// Check the exact error location
echo "\n4. Locating error message source...\n";
if (strpos($content, 'Неизвестная команда управления проектами на контроле') !== false) {
    echo "   ✓ Found error message in ProjectHandler\n";
    
    // Find the context around this message
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'Неизвестная команда управления проектами на контроле') !== false) {
            echo "   📍 Error message at line: " . ($lineNum + 1) . "\n";
            echo "   Context:\n";
            for ($i = max(0, $lineNum - 3); $i <= min(count($lines) - 1, $lineNum + 3); $i++) {
                $marker = ($i === $lineNum) ? " >>> " : "     ";
                echo "$marker" . ($i + 1) . ": " . trim($lines[$i]) . "\n";
            }
            break;
        }
    }
} else {
    echo "   ✗ Error message not found in ProjectHandler\n";
}

echo "\n=== DIAGNOSIS SUMMARY ===\n";
if (!empty($missingPatterns)) {
    echo "🔧 ISSUE IDENTIFIED: Missing callback patterns in handleControlProjectsCallback\n";
    echo "   The method exists but doesn't handle the new notification configuration callbacks.\n\n";
    echo "📝 PATTERNS TO ADD:\n";
    foreach ($missingPatterns as $pattern) {
        echo "   - $pattern\n";
    }
}

if (!empty($missingMethods)) {
    echo "\n🔧 ISSUE IDENTIFIED: Missing helper methods\n";
    echo "📝 METHODS TO ADD:\n";
    foreach ($missingMethods as $method) {
        echo "   - $method\n";
    }
}

echo "\n🔍 NEXT STEPS:\n";
echo "1. Fix the missing callback patterns in handleControlProjectsCallback\n";
echo "2. Add any missing helper methods\n";
echo "3. Test the functionality again\n";

?>