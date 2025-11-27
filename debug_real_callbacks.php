<?php
/**
 * Real-time callback debugging
 * Safety protocol: Monitor actual callback data being received
 */

echo "=== REAL-TIME CALLBACK DEBUGGING ===\n\n";

// Enable callback monitoring if not already enabled
$callbackFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
$content = file_get_contents($callbackFile);

echo "1. Checking if callback monitoring is active...\n";
if (strpos($content, 'callback_monitor.log') !== false) {
    echo "   ✓ Callback monitoring is enabled\n";
} else {
    echo "   ⚠️ Callback monitoring not found - adding it now\n";
    
    // Add monitoring code to the beginning of execute() method
    $monitoringCode = '
        // LIVE CALLBACK MONITOR - Log all received callbacks
        $logFile = __DIR__ . "/../../callback_monitor.log";
        $timestamp = date("Y-m-d H:i:s");
        $userId = $callback_query->getFrom()->getId();
        $messageText = "No message";
        
        if ($callback_query->getMessage()) {
            $messageText = "Message exists";
        }
        
        $logEntry = "[$timestamp] USER: $userId | DATA: $callback_data | MSG: $messageText\\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        // Also log to PHP error log
        error_log("CALLBACK RECEIVED: User $userId clicked \'$callback_data\'");
';
    
    // Find the line where callback_data is defined and add monitoring after it
    $lines = explode("\n", $content);
    $newContent = '';
    $monitoringAdded = false;
    
    foreach ($lines as $line) {
        $newContent .= $line . "\n";
        
        // Add monitoring after the callback_data line
        if (strpos($line, '$callback_data = $callback_query->getData()') !== false && !$monitoringAdded) {
            $newContent .= $monitoringCode;
            $monitoringAdded = true;
        }
    }
    
    if ($monitoringAdded) {
        file_put_contents($callbackFile, $newContent);
        echo "   ✓ Callback monitoring added\n";
    } else {
        echo "   ✗ Could not add callback monitoring automatically\n";
    }
}

echo "\n2. Checking current callback log...\n";
$logFile = __DIR__ . '/callback_monitor.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", trim($logContent));
    $recentLines = array_slice($lines, -5); // Last 5 entries
    
    echo "   Recent callback activity:\n";
    foreach ($recentLines as $line) {
        if (!empty($line)) {
            echo "   > $line\n";
        }
    }
} else {
    echo "   ℹ️ No callback log file found yet\n";
    echo "   📝 Will be created when you click buttons\n";
}

echo "\n3. Checking specific callback handling in ProjectHandler...\n";
$handlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$handlerContent = file_get_contents($handlerFile);

// Find the handleControlProjectsCallback method
$startPos = strpos($handlerContent, 'function handleControlProjectsCallback');
if ($startPos !== false) {
    // Find the end of the method
    $lines = explode("\n", $handlerContent);
    $inMethod = false;
    $braceCount = 0;
    $methodLines = [];
    
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'function handleControlProjectsCallback') !== false) {
            $inMethod = true;
        }
        
        if ($inMethod) {
            $methodLines[] = ($lineNum + 1) . ": " . $line;
            
            // Count braces to find method end
            $braceCount += substr_count($line, '{') - substr_count($line, '}');
            
            if ($braceCount === 0 && strpos($line, '}') !== false && count($methodLines) > 1) {
                break;
            }
        }
    }
    
    echo "   Found handleControlProjectsCallback method:\n";
    echo "   Lines " . count($methodLines) . " total\n";
    
    // Show the key parts
    $keyPatterns = [
        'proj_ctrl_set_',
        'proj_ctrl_cfg_group_',
        'proj_ctrl_cfg_chat_',
        'proj_ctrl_cfg_types_',
        'proj_ctrl_cfg_mode_',
        'proj_ctrl_test_',
        'Неизвестная команда'
    ];
    
    foreach ($keyPatterns as $pattern) {
        $found = false;
        foreach ($methodLines as $line) {
            if (strpos($line, $pattern) !== false) {
                echo "   ✓ Found pattern '$pattern' in: " . trim($line) . "\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "   ✗ Pattern '$pattern' not found\n";
        }
    }
} else {
    echo "   ✗ handleControlProjectsCallback method not found!\n";
}

echo "\n4. Testing the exact callback patterns your buttons generate...\n";
echo "   When you click '⚙️ ОИ789' button, it should generate:\n";
echo "   → proj_ctrl_set_ОИ789\n";
echo "   \n";
echo "   Testing this pattern:\n";
$testCallback = 'proj_ctrl_set_ОИ789';
if (str_starts_with($testCallback, 'proj_ctrl_set_')) {
    $projectCode = str_replace('proj_ctrl_set_', '', $testCallback);
    echo "   ✓ Pattern matches: Project = '$projectCode'\n";
} else {
    echo "   ✗ Pattern does not match\n";
}

echo "\n=== DEBUGGING INSTRUCTIONS ===\n";
echo "📝 To find the exact issue:\n";
echo "1. Try clicking on your project (⚙️ ОИ789) in the bot\n";
echo "2. Check the callback_monitor.log file that will be created\n";
echo "3. Run this script again to see what callback data was actually received\n";
echo "4. Compare the actual callback with what we expect\n\n";

echo "🔍 Expected vs Actual:\n";
echo "Expected: proj_ctrl_set_ОИ789\n";
echo "Actual:   (will show in log after you click)\n\n";

echo "💡 If the callback data is different from expected, we'll know\n";
echo "   the issue is in button generation, not parsing.\n";

?>