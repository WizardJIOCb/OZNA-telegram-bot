<?php
/**
 * Manual Input Confirmation Issue - Runtime Verification
 * 
 * This will verify the current state of the manual input system that was previously fixed
 */

echo "=== Manual Input Confirmation - Runtime Verification ===\n\n";

echo "🔍 Step 1: Checking Previous Fix Implementation\n";
echo "===============================================\n\n";

// Check if the GenericmessageCommand still has the file-based state checking
$commandFile = 'CustomCommands/GenericmessageCommand.php';
if (file_exists($commandFile)) {
    $content = file_get_contents($commandFile);
    
    echo "Verifying previously implemented fixes:\n\n";
    
    // Check for file-based state manager usage
    $hasStateManager = strpos($content, 'ManualInputStateManager') !== false;
    echo "1. ManualInputStateManager integration: " . ($hasStateManager ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    // Check for file-based state checks in execute method
    $hasGroupCheck = strpos($content, '$this->stateManager->isAwaitingGroupInput') !== false;
    $hasChatCheck = strpos($content, '$this->stateManager->isAwaitingChatInput') !== false;
    echo "2. File-based state checks: " . (($hasGroupCheck && $hasChatCheck) ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    // Check for handler methods
    $hasGroupHandler = strpos($content, 'handleManualGroupInput') !== false;
    $hasChatHandler = strpos($content, 'handleManualChatInput') !== false;
    echo "3. Handler methods: " . (($hasGroupHandler && $hasChatHandler) ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    // Check for confirmation messages
    $hasGroupConfirm = strpos($content, 'ID группы сохранён') !== false;
    $hasChatConfirm = strpos($content, 'ID чата сохранён') !== false;
    echo "4. Confirmation messages: " . (($hasGroupConfirm && $hasChatConfirm) ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    // Check for navigation buttons
    $hasNavigation = strpos($content, 'К настройкам') !== false || strpos($content, 'Вернуться к настройкам') !== false;
    echo "5. Navigation buttons: " . ($hasNavigation ? "✅ PRESENT" : "❌ MISSING") . "\n";
    
    echo "\n";
} else {
    echo "❌ GenericmessageCommand.php not found\n\n";
}

echo "🔍 Step 2: Testing Manual Input State System\n";
echo "=============================================\n\n";

// Test the ManualInputStateManager
try {
    require_once 'CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    $testUserId = 99999;
    $testProject = "VERIFICATION_TEST";
    
    echo "Testing state management:\n";
    
    // Test group input state
    echo "  Group input state test: ";
    $groupStateSet = $stateManager->setState($testUserId, 'group_id', $testProject);
    $groupStateCheck = $stateManager->isAwaitingGroupInput($testUserId);
    $groupProjectCode = $stateManager->getProjectCode($testUserId);
    echo ($groupStateSet && $groupStateCheck && $groupProjectCode === $testProject) ? "✅ WORKING" : "❌ FAILED";
    echo "\n";
    
    $stateManager->clearState($testUserId);
    
    // Test chat input state
    echo "  Chat input state test: ";
    $chatStateSet = $stateManager->setState($testUserId, 'chat_id', $testProject);
    $chatStateCheck = $stateManager->isAwaitingChatInput($testUserId);
    $chatProjectCode = $stateManager->getProjectCode($testUserId);
    echo ($chatStateSet && $chatStateCheck && $chatProjectCode === $testProject) ? "✅ WORKING" : "❌ FAILED";
    echo "\n";
    
    $stateManager->clearState($testUserId);
    
} catch (Exception $e) {
    echo "❌ Error testing state manager: " . $e->getMessage() . "\n";
}

echo "\n🔍 Step 3: Checking Error Logs for Recent Issues\n";
echo "===============================================\n\n";

// Check recent error log entries
$logFile = 'telegram_bot_error.log';
if (file_exists($logFile)) {
    echo "Checking recent error log entries:\n";
    
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recentLines = array_slice($lines, -15); // Last 15 lines
    
    $hasRecentErrors = false;
    foreach ($recentLines as $line) {
        if (strpos($line, 'Error') !== false || strpos($line, 'Fatal') !== false) {
            echo "⚠️ " . $line . "\n";
            $hasRecentErrors = true;
        }
    }
    
    if (!$hasRecentErrors) {
        echo "✅ No recent error entries found\n";
    }
    
} else {
    echo "❌ Error log file not found\n";
}

echo "\n🔍 Step 4: Testing ProjectHandler Integration\n";
echo "=============================================\n\n";

// Check if ProjectHandler still uses ManualInputStateManager
$projectHandlerFile = 'CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($projectHandlerFile)) {
    $content = file_get_contents($projectHandlerFile);
    
    echo "ProjectHandler manual input setup:\n";
    
    $hasStateManagerUsage = strpos($content, 'ManualInputStateManager') !== false;
    echo "  Uses ManualInputStateManager: " . ($hasStateManagerUsage ? "✅ YES" : "❌ NO") . "\n";
    
    $hasGroupSetup = strpos($content, "setState(\$userId, 'group_id'") !== false;
    echo "  Sets up group input state: " . ($hasGroupSetup ? "✅ YES" : "❌ NO") . "\n";
    
    $hasChatSetup = strpos($content, "setState(\$userId, 'chat_id'") !== false;
    echo "  Sets up chat input state: " . ($hasChatSetup ? "✅ YES" : "❌ NO") . "\n";
    
} else {
    echo "❌ ProjectHandler.php not found\n";
}

echo "\n🔍 Step 5: Runtime Flow Simulation\n";
echo "==================================\n\n";

echo "Simulating complete manual input flow:\n\n";

try {
    $testUserId = 88888;
    $testProject = "FLOW_TEST";
    $testGroupId = "-1001234567890";
    
    echo "1. User clicks manual group input button (ProjectHandler action)\n";
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    $stateSet = $stateManager->setState($testUserId, 'group_id', $testProject);
    echo "   State set: " . ($stateSet ? "✅" : "❌") . "\n";
    
    echo "2. User sends group ID: $testGroupId\n";
    echo "   GenericmessageCommand should detect: ";
    $isAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
    echo ($isAwaiting ? "✅ DETECTED" : "❌ NOT DETECTED") . "\n";
    
    if ($isAwaiting) {
        echo "3. Input validation: ";
        $isValid = preg_match('/^-100\d+$/', $testGroupId);
        echo ($isValid ? "✅ VALID" : "❌ INVALID") . "\n";
        
        echo "4. Expected result: Confirmation message and navigation button\n";
        echo "   Message should be: '✅ ID группы сохранён'\n";
        echo "   Button should be: '⬅️ К настройкам'\n";
    }
    
    $stateManager->clearState($testUserId);
    
} catch (Exception $e) {
    echo "❌ Error in flow simulation: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 DIAGNOSIS SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "If all components show ✅ but manual input still doesn't work:\n";
echo "• The issue is likely in command execution or message delivery\n";
echo "• GenericmessageCommand may not be executing when messages are sent\n";
echo "• There may be silent failures in the Telegram API communication\n\n";

echo "If any components show ❌:\n";
echo "• Those specific components need to be fixed\n";
echo "• The previous fix may have been overwritten or corrupted\n\n";

echo "NEXT STEP REQUIRED:\n";
echo "===================\n";
echo "Please test the manual input feature and report:\n";
echo "1. Whether you receive ANY response when entering an ID\n";
echo "2. Any error messages that appear\n";
echo "3. What shows in telegram_bot_error.log after attempting manual input\n\n";

echo "This will help identify if the issue is:\n";
echo "• Code logic (can be fixed)\n";
echo "• System execution (needs server-side investigation)\n";
echo "• Configuration (needs webhook/bot setup check)\n";