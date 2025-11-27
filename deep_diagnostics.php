<?php
/**
 * Deep Runtime Diagnostics for Project Control Callbacks
 * Following safety protocols for non-programmer user
 * Will trace exact callback execution path
 */

echo "=== DEEP RUNTIME DIAGNOSTICS ===\n\n";

// Step 1: Enable comprehensive callback monitoring
echo "1. Setting up real-time callback monitoring...\n";

$monitorCode = '<?php
// DEEP CALLBACK MONITOR - Add to CallbackqueryCommand execute() method

$deepLogFile = __DIR__ . "/../../deep_callback_monitor.log";
$timestamp = date("Y-m-d H:i:s");
$userId = $callback_query->getFrom()->getId();
$data = $callback_query->getData();
$firstName = $callback_query->getFrom()->getFirstName();

// Log everything about this callback
$logEntry = "=== CALLBACK RECEIVED AT $timestamp ===\n";
$logEntry .= "User ID: $userId\n";
$logEntry .= "User Name: $firstName\n";
$logEntry .= "Callback Data: \'$data\'\n";
$logEntry .= "Callback Length: " . strlen($data) . " bytes\n";
$logEntry .= "Callback Hex: " . bin2hex($data) . "\n";

// Check which patterns match
$patterns = [
    "category_" => strpos($data, "category_") === 0 ? "MATCH" : "NO MATCH",
    "adm_req_" => strpos($data, "adm_req_") === 0 ? "MATCH" : "NO MATCH", 
    "acc_lvl_" => strpos($data, "acc_lvl_") === 0 ? "MATCH" : "NO MATCH",
    "cat_Таб" => strpos($data, "cat_Таб") === 0 ? "MATCH" : "NO MATCH",
    "proj_ctrl_" => strpos($data, "proj_ctrl_") === 0 ? "MATCH" : "NO MATCH"
];

$logEntry .= "Pattern Matching:\n";
foreach ($patterns as $pattern => $result) {
    $logEntry .= "  $pattern -> $result\n";
}

$logEntry .= "Will pass through early exit filter: " . 
    (strpos($data, "category_") === 0 || 
     strpos($data, "adm_req_") === 0 || 
     strpos($data, "acc_lvl_") === 0 || 
     strpos($data, "cat_Таб") === 0 || 
     strpos($data, "proj_ctrl_") === 0 ? "YES" : "NO") . "\n";

$logEntry .= "===================================\n\n";

file_put_contents($deepLogFile, $logEntry, FILE_APPEND);
error_log("DEEP CALLBACK: $data from user $userId");
';

file_put_contents(__DIR__ . '/deep_callback_monitor_code.txt', $monitorCode);
echo "   ✓ Created monitoring code in deep_callback_monitor_code.txt\n";

// Step 2: Check current ProjectHandler structure
echo "\n2. Analyzing ProjectHandler callback handling...\n";
$handlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$content = file_get_contents($handlerFile);

// Find handleControlProjectsCallback method
$methodStart = strpos($content, 'function handleControlProjectsCallback');
if ($methodStart !== false) {
    // Extract the method
    $methodEnd = strpos($content, 'function ', $methodStart + 1);
    if ($methodEnd === false) {
        $methodEnd = strlen($content);
    }
    
    $methodContent = substr($content, $methodStart, $methodEnd - $methodStart);
    $lines = explode("\n", $methodContent);
    
    echo "   Found handleControlProjectsCallback method:\n";
    echo "   Method has " . count($lines) . " lines\n";
    
    // Check specific callback handling
    $handledCallbacks = [];
    foreach ($lines as $line) {
        if (strpos($line, 'proj_ctrl_') !== false && strpos($line, 'str_starts_with') !== false) {
            $handledCallbacks[] = trim($line);
        }
    }
    
    echo "   Handled callback patterns found:\n";
    foreach ($handledCallbacks as $callback) {
        echo "     • $callback\n";
    }
    
    // Check for fallback error message location
    if (strpos($methodContent, 'Неизвестная команда управления проектами на контроле') !== false) {
        echo "   ✓ Error message found in method\n";
        
        // Find what leads to this error
        $errorLines = explode("\n", $methodContent);
        $errorLineNum = 0;
        foreach ($errorLines as $i => $line) {
            if (strpos($line, 'Неизвестная команда управления проектами на контроле') !== false) {
                $errorLineNum = $i;
                break;
            }
        }
        
        echo "   Error message context:\n";
        for ($i = max(0, $errorLineNum - 5); $i <= min(count($errorLines) - 1, $errorLineNum + 2); $i++) {
            $marker = ($i === $errorLineNum) ? " >>> " : "     ";
            echo "$marker" . ($i + 1) . ": " . trim($errorLines[$i]) . "\n";
        }
    }
} else {
    echo "   ✗ handleControlProjectsCallback method not found!\n";
}

// Step 3: Test specific callback patterns
echo "\n3. Testing specific callback patterns that should be working...\n";

// Test the exact callbacks that should be generated
$testCallbacks = [
    'proj_ctrl_set_ОИ789',  // Click on project in settings
    'proj_ctrl_remove_ОИ789', // Remove project 
    'proj_ctrl_add_TestProject', // Add project
];

foreach ($testCallbacks as $callback) {
    echo "   Testing: '$callback'\n";
    
    // Check if it would match any pattern
    if (str_starts_with($callback, 'proj_ctrl_set_')) {
        echo "     ✓ Should match proj_ctrl_set_ pattern\n";
        $projectCode = str_replace('proj_ctrl_set_', '', $callback);
        echo "     ✓ Would extract project: '$projectCode'\n";
    }
    
    if (str_starts_with($callback, 'proj_ctrl_remove_')) {
        echo "     ✓ Should match proj_ctrl_remove_ pattern\n";
        $projectCode = str_replace('proj_ctrl_remove_', '', $callback);
        echo "     ✓ Would extract project: '$projectCode'\n";
    }
    
    if (str_starts_with($callback, 'proj_ctrl_add_')) {
        echo "     ✓ Should match proj_ctrl_add_ pattern\n";
        $projectCode = str_replace('proj_ctrl_add_', '', $callback);
        echo "     ✓ Would extract project: '$projectCode'\n";
    }
    
    echo "\n";
}

// Step 4: Check if methods exist
echo "4. Verifying required methods exist...\n";
$requiredMethods = [
    'showProjectControlSettings',
    'removeProjectFromControlList', 
    'addProjectToControlList'
];

foreach ($requiredMethods as $method) {
    if (strpos($content, "function $method") !== false) {
        echo "   ✓ Method exists: $method\n";
    } else {
        echo "   ✗ Method missing: $method\n";
    }
}

// Step 5: Create test to simulate actual callback
echo "\n5. Creating callback simulation test...\n";

$testCode = '<?php
// Simulate the exact callback handling logic
// This tests what would happen when callbacks are received

function simulateCallback($callbackData) {
    echo "Simulating callback: $callbackData\n";
    
    // Test the main patterns from handleControlProjectsCallback
    if ($callbackData === "proj_ctrl_add") {
        echo "  ✓ Would call showAddControlProjectMenu\n";
        return "showAddControlProjectMenu";
    }
    
    if ($callbackData === "proj_ctrl_remove") {
        echo "  ✓ Would call showRemoveControlProjectMenu\n";
        return "showRemoveControlProjectMenu";
    }
    
    if ($callbackData === "proj_ctrl_list") {
        echo "  ✓ Would call showControlProjectsList\n";
        return "showControlProjectsList";
    }
    
    if ($callbackData === "proj_ctrl_settings") {
        echo "  ✓ Would call showControlProjectSettings\n";
        return "showControlProjectSettings";
    }
    
    // Test specific project actions
    if (str_starts_with($callbackData, "proj_ctrl_add_")) {
        $projectCode = str_replace("proj_ctrl_add_", "", $callbackData);
        echo "  ✓ Would call addProjectToControlList with project: $projectCode\n";
        return "addProjectToControlList";
    }
    
    if (str_starts_with($callbackData, "proj_ctrl_remove_")) {
        $projectCode = str_replace("proj_ctrl_remove_", "", $callbackData);
        echo "  ✓ Would call removeProjectFromControlList with project: $projectCode\n";
        return "removeProjectFromControlList";
    }
    
    if (str_starts_with($callbackData, "proj_ctrl_set_")) {
        $projectCode = str_replace("proj_ctrl_set_", "", $callbackData);
        echo "  ✓ Would call showProjectControlSettings with project: $projectCode\n";
        return "showProjectControlSettings";
    }
    
    echo "  ✗ Would reach fallback error message\n";
    return "ERROR: Неизвестная команда управления проектами на контроле";
}

// Test the problematic callbacks
$testCallbacks = [
    "proj_ctrl_set_ОИ789",
    "proj_ctrl_remove_ОИ789", 
    "proj_ctrl_add_TestProject"
];

foreach ($testCallbacks as $callback) {
    $result = simulateCallback($callback);
    echo "Result: $result\n\n";
}
?>';

file_put_contents(__DIR__ . '/simulate_callbacks.php', $testCode);
echo "   ✓ Created callback simulation test\n";

echo "\n=== DIAGNOSTIC SUMMARY ===\n";
echo "📋 NEXT STEPS FOR DEEPER DIAGNOSIS:\n\n";

echo "1. 🔍 ADD MONITORING CODE:\n";
echo "   Add the code from 'deep_callback_monitor_code.txt' to CallbackqueryCommand.php\n";
echo "   This will log exactly what callbacks are being received\n\n";

echo "2. 🧪 RUN SIMULATION:\n";
echo "   Execute: php simulate_callbacks.php\n";
echo "   This will test the callback logic without real bot interaction\n\n";

echo "3. 📊 TEST IN REAL BOT:\n";
echo "   Try clicking the problematic buttons\n";
echo "   Check the new log file: deep_callback_monitor.log\n\n";

echo "4. 🔍 ANALYZE RESULTS:\n";
echo "   Compare what callbacks are actually sent vs what the code expects\n\n";

echo "⚠️  POSSIBLE ISSUES TO CHECK:\n";
echo "   • Callback data encoding (Cyrillic characters)\n";
echo "   • Callback data length limits\n";
echo "   • Method routing in handleControlProjectsCallback\n";
echo "   • Early exit conditions in CallbackqueryCommand\n\n";

echo "🎯 This deep diagnosis will reveal the exact cause of the issue.\n";

?>