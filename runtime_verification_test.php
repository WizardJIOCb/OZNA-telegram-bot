<?php
/**
 * Runtime Verification: Manual Input Flow
 * 
 * This script will help verify what's happening in real-time
 * when manual input is attempted
 */

echo "=== RUNTIME VERIFICATION: Manual Input Issue ===\n\n";

echo "🔍 STEP 1: Checking Current System State\n";
echo "=========================================\n\n";

// Check if error logging is enabled
echo "1.1 Error Logging Check:\n";
$errorLogEnabled = ini_get('log_errors');
$errorLogFile = ini_get('error_log');
echo "   Error logging enabled: " . ($errorLogEnabled ? "✅ YES" : "❌ NO") . "\n";
echo "   Error log file: " . ($errorLogFile ?: "default") . "\n";

// Check for existing log files
$possibleLogFiles = [
    'php_errors.log',
    'error.log',
    'errors.log',
    '/var/log/php_errors.log',
    'data/logs/error.log'
];

echo "\n1.2 Looking for existing log files:\n";
$foundLogs = [];
foreach ($possibleLogFiles as $logFile) {
    if (file_exists($logFile)) {
        $foundLogs[] = $logFile;
        $size = filesize($logFile);
        echo "   ✅ Found: $logFile (Size: {$size} bytes)\n";
    }
}

if (empty($foundLogs)) {
    echo "   ❌ No log files found\n";
}

echo "\n🔍 STEP 2: Testing Command Execution Path\n";
echo "==========================================\n\n";

// Test if we can trace the execution path
echo "2.1 Creating test state to simulate manual input:\n";

try {
    require_once 'CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    $testUserId = 12345;
    $testProject = "RUNTIME_VERIFY";
    
    // Set state as if user clicked manual input button
    $stateSet = $stateManager->setState($testUserId, 'group_id', $testProject);
    echo "   ✅ Test state created for user $testUserId\n";
    
    // Verify state exists
    $isAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
    echo "   ✅ State verification: " . ($isAwaiting ? "FOUND" : "NOT FOUND") . "\n";
    
    if ($isAwaiting) {
        $projectCode = $stateManager->getProjectCode($testUserId);
        echo "   ✅ Project code: $projectCode\n";
        
        echo "\n2.2 This is what GenericmessageCommand should see:\n";
        echo "   - User ID: $testUserId\n";
        echo "   - Awaiting group input: YES\n";
        echo "   - Project: $projectCode\n";
        echo "   - Expected behavior: Process input and show confirmation\n";
    }
    
    // Clean up test state
    $stateManager->clearState($testUserId);
    echo "   ✅ Test state cleaned up\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🔍 STEP 3: Webhook Delivery Test\n";
echo "=================================\n\n";

echo "3.1 Checking for webhook entry points:\n";
$webhookFiles = ['webhook.php', 'bot.php', 'index.php', 'telegram.php', 'hook.php'];
$foundWebhook = false;

foreach ($webhookFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ Found webhook file: $file\n";
        $foundWebhook = true;
        
        // Check if file contains GenericmessageCommand
        $content = file_get_contents($file);
        $hasGeneric = strpos($content, 'GenericmessageCommand') !== false;
        $hasSystemCommands = strpos($content, 'SystemCommands') !== false;
        
        echo "     - References GenericmessageCommand: " . ($hasGeneric ? "✅" : "❌") . "\n";
        echo "     - Has SystemCommands path: " . ($hasSystemCommands ? "✅" : "❌") . "\n";
    }
}

if (!$foundWebhook) {
    echo "   ❌ No webhook files found\n";
    echo "   This might explain why messages aren't being processed\n";
}

echo "\n🔍 STEP 4: Real Execution Test\n";
echo "==============================\n\n";

echo "4.1 Testing if GenericmessageCommand can execute at all:\n";

// Try to create a minimal execution test
try {
    echo "   Attempting to load GenericmessageCommand...\n";
    
    // Check if we can at least load the file
    if (file_exists('CustomCommands/GenericmessageCommand.php')) {
        echo "   ✅ GenericmessageCommand.php file exists\n";
        
        // Try to read and analyze the file without executing it
        $content = file_get_contents('CustomCommands/GenericmessageCommand.php');
        
        // Check for our debug logging
        $hasDebugLogging = strpos($content, '=== GenericmessageCommand::execute() START ===') !== false;
        echo "   ✅ Debug logging added: " . ($hasDebugLogging ? "YES" : "NO") . "\n";
        
        // Check for manual input handling
        $hasManualInputChecks = strpos($content, 'isAwaitingGroupInput') !== false;
        echo "   ✅ Manual input checks: " . ($hasManualInputChecks ? "YES" : "NO") . "\n";
        
        // Check for handler methods
        $hasHandlers = strpos($content, 'handleManualGroupInput') !== false;
        echo "   ✅ Handler methods: " . ($hasHandlers ? "YES" : "NO") . "\n";
        
    } else {
        echo "   ❌ GenericmessageCommand.php not found\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error loading command: " . $e->getMessage() . "\n";
}

echo "\n🔍 STEP 5: Log Analysis Instructions\n";
echo "====================================\n\n";

echo "5.1 How to check if the command is being executed:\n";
echo "\n   IMMEDIATE TEST TO PERFORM:\n";
echo "   -------------------------\n";
echo "   1. Go to your bot in Telegram\n";
echo "   2. Navigate to: Projects → My Projects → Projects Under Control\n";
echo "   3. Add a project or go to project settings\n";
echo "   4. Click 'Configure Group' → 'Manual Group ID Input'\n";
echo "   5. Send any text message (like: -1001234567890)\n";
echo "   6. Immediately check for new log entries\n\n";

if (!empty($foundLogs)) {
    echo "   CHECK THESE LOG FILES FOR NEW ENTRIES:\n";
    foreach ($foundLogs as $logFile) {
        echo "   📄 $logFile\n";
    }
} else {
    echo "   📄 Check server error logs or PHP error logs\n";
}

echo "\n5.2 What to look for in logs:\n";
echo "   🔍 SUCCESS INDICATORS:\n";
echo "   • '=== GenericmessageCommand::execute() START ==='\n";
echo "   • 'Services initialized successfully'\n";
echo "   • 'Checking manual group input for user [ID]'\n";
echo "   • 'User [ID] is awaiting group input - calling handler'\n";
echo "   • '=== handleManualGroupInput START ==='\n\n";

echo "   ❌ FAILURE INDICATORS:\n";
echo "   • 'Service initialization failed'\n";
echo "   • 'Class not found' errors\n";
echo "   • Fatal errors\n";
echo "   • No logs at all (webhook issue)\n\n";

echo "🔍 STEP 6: Emergency Fallback Test\n";
echo "===================================\n\n";

echo "6.1 Testing basic message handling:\n";
echo "   Try sending any simple text message to your bot\n";
echo "   (like 'hello' or 'test') to see if ANY messages\n";
echo "   are being processed at all.\n\n";

echo "   If NO messages get any response:\n";
echo "   ❌ Problem is with webhook/bot configuration\n";
echo "   ❌ Messages are not reaching the bot at all\n\n";

echo "   If simple messages work but manual input doesn't:\n";
echo "   ❌ Problem is with manual input specific logic\n";
echo "   ❌ Check the debug logs for specific errors\n\n";

echo str_repeat("=", 50) . "\n";
echo "🎯 NEXT ACTION REQUIRED\n";
echo str_repeat("=", 50) . "\n\n";

echo "Please perform the test described in STEP 5.1 above:\n";
echo "1. Try the manual input feature again\n";
echo "2. Check the log files immediately after\n";
echo "3. Report what log entries (if any) appear\n\n";

echo "This will tell us exactly where the execution is failing\n";
echo "and allow us to provide a targeted solution.\n";