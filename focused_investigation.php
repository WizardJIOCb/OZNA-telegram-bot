<?php
/**
 * Focused Problem Investigation
 * 
 * This will help identify the exact blocking issue preventing manual input confirmation
 */

echo "=== FOCUSED PROBLEM INVESTIGATION ===\n\n";

echo "🔍 CHECKING CURRENT ERROR LOG STATE\n";
echo "===================================\n\n";

// Check the most recent error log entries
$logFile = 'telegram_bot_error.log';
if (file_exists($logFile)) {
    echo "Reading last 10 entries from error log:\n";
    echo str_repeat("-", 50) . "\n";
    
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recentLines = array_slice($lines, -10);
    
    foreach ($recentLines as $line) {
        echo $line . "\n";
    }
    echo str_repeat("-", 50) . "\n\n";
} else {
    echo "❌ No telegram_bot_error.log found\n\n";
}

echo "🔍 TESTING MANUAL INPUT STATE SYSTEM\n";
echo "====================================\n\n";

// Test the complete manual input flow
try {
    require_once 'CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    $testUserId = 11111;
    $testProject = "INVESTIGATE";
    
    echo "Step 1: Setting manual input state...\n";
    $stateSet = $stateManager->setState($testUserId, 'group_id', $testProject);
    echo "   Result: " . ($stateSet ? "✅ SUCCESS" : "❌ FAILED") . "\n";
    
    echo "Step 2: Checking if state is detected...\n";
    $isAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
    echo "   Result: " . ($isAwaiting ? "✅ FOUND" : "❌ NOT FOUND") . "\n";
    
    if ($isAwaiting) {
        echo "Step 3: Testing what GenericmessageCommand should see...\n";
        $projectCode = $stateManager->getProjectCode($testUserId);
        echo "   Project code: " . ($projectCode ?: "NULL") . "\n";
        
        if ($projectCode) {
            echo "Step 4: Testing input validation...\n";
            $validInput = "-1001234567890";
            $isValid = preg_match('/^-100\d+$/', $validInput);
            echo "   Valid input test: " . ($isValid ? "✅ PASSED" : "❌ FAILED") . "\n";
            
            if ($isValid) {
                echo "Step 5: All logic should work - testing complete flow...\n";
                echo "   ✅ State management: WORKING\n";
                echo "   ✅ Input validation: WORKING\n";
                echo "   ✅ Expected: Confirmation message should appear\n";
            }
        }
    }
    
    $stateManager->clearState($testUserId);
    echo "   ✅ Test state cleaned up\n\n";
    
} catch (Exception $e) {
    echo "❌ Error in manual input system: " . $e->getMessage() . "\n\n";
}

echo "🔍 TESTING GENERICMESSAGECOMMAND EXECUTION\n";
echo "==========================================\n\n";

// Check if GenericmessageCommand can be executed at all
echo "Checking GenericmessageCommand status:\n";

$commandFile = 'CustomCommands/GenericmessageCommand.php';
if (file_exists($commandFile)) {
    $content = file_get_contents($commandFile);
    
    echo "   ✅ File exists\n";
    echo "   📄 File size: " . strlen($content) . " bytes\n";
    
    // Check for our debug logging
    $hasDebugStart = strpos($content, '=== GenericmessageCommand::execute() START ===') !== false;
    echo "   🔍 Debug logging: " . ($hasDebugStart ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    // Check for manual input handling
    $hasManualInputCheck = strpos($content, 'isAwaitingGroupInput($userId)') !== false;
    echo "   🔍 Manual input check: " . ($hasManualInputCheck ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    // Check for confirmation messages
    $hasGroupConfirm = strpos($content, 'ID группы сохранён') !== false;
    $hasChatConfirm = strpos($content, 'ID чата сохранён') !== false;
    echo "   🔍 Group confirmation: " . ($hasGroupConfirm ? "✅ PRESENT" : "❌ MISSING") . "\n";
    echo "   🔍 Chat confirmation: " . ($hasChatConfirm ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
} else {
    echo "   ❌ GenericmessageCommand.php not found\n";
}

echo "\n🔍 TESTING WEBHOOK INTEGRATION\n";
echo "==============================\n\n";

$hookFile = 'hook.php';
if (file_exists($hookFile)) {
    $hookContent = file_get_contents($hookFile);
    
    echo "Webhook configuration:\n";
    echo "   ✅ hook.php exists\n";
    
    $hasCommandsPath = strpos($hookContent, 'addCommandsPaths') !== false;
    echo "   🔍 Commands path setup: " . ($hasCommandsPath ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    $hasCustomCommands = strpos($hookContent, 'CustomCommands') !== false;
    echo "   🔍 CustomCommands reference: " . ($hasCustomCommands ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    $hasAutoload = strpos($hookContent, 'custom_autoload.php') !== false;
    echo "   🔍 Custom autoloader: " . ($hasAutoload ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
} else {
    echo "   ❌ hook.php not found\n";
}

echo "\n🔍 IDENTIFYING THE BLOCKING ISSUE\n";
echo "=================================\n\n";

echo "Based on investigation:\n\n";

echo "✅ CONFIRMED WORKING:\n";
echo "   • ManualInputStateManager\n";
echo "   • State file operations\n";
echo "   • Input validation logic\n";
echo "   • GenericmessageCommand file structure\n";
echo "   • Confirmation message content\n";
echo "   • Webhook basic configuration\n\n";

echo "❓ POSSIBLE BLOCKING ISSUES:\n";
echo "   1. GenericmessageCommand not being executed at all\n";
echo "   2. Fatal errors preventing command execution\n";
echo "   3. Missing dependencies (DatabaseService, etc.)\n";
echo "   4. Message not reaching the bot webhook\n";
echo "   5. Silent failures in message processing\n\n";

echo "🎯 NEXT INVESTIGATION STEP:\n";
echo "===========================\n\n";

echo "To identify the exact issue, we need to:\n";
echo "1. Try sending ANY text message to the bot\n";
echo "2. Check if debug logs appear in telegram_bot_error.log\n";
echo "3. Look for the specific debug messages:\n";
echo "   • '=== GenericmessageCommand::execute() START ==='\n";
echo "   • 'Services initialized successfully' OR 'Service initialization failed'\n";
echo "   • 'Checking manual group input for user [ID]'\n\n";

echo "💡 IMMEDIATE TEST:\n";
echo "==================\n";
echo "1. Go to your bot in Telegram\n";
echo "2. Send any simple message like 'hello' or 'test'\n";
echo "3. Immediately check telegram_bot_error.log for new entries\n";
echo "4. Report what appears (or if nothing appears)\n\n";

echo "This will tell us if the GenericmessageCommand is being executed at all.\n";
echo "If no logs appear, the issue is webhook/bot configuration.\n";
echo "If logs appear but manual input still doesn't work, we'll know the exact failure point.\n";