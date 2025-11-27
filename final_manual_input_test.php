<?php
// Final test to verify manual input state management with fixed path

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== FINAL MANUAL INPUT TEST ===\n";
echo "Testing ManualInputStateManager with corrected path...\n\n";

// Include the actual ManualInputStateManager
require_once 'CustomCommands/Services/ManualInputStateManager.php';
use Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager;

try {
    // Create an instance
    $stateManager = new ManualInputStateManager();
    echo "✅ ManualInputStateManager created successfully\n";
    
    // Test setting Group ID state
    $testUserId = 789123;
    $testProject = 'БКНС125';
    
    echo "\nStep 1: Setting Group ID state...\n";
    $result = $stateManager->setState($testUserId, 'group_id', $testProject);
    echo "setState result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "\n";
    
    // Test checking if user is awaiting group input
    echo "\nStep 2: Checking if user is awaiting group input...\n";
    $isAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
    echo "isAwaitingGroupInput: " . ($isAwaiting ? "✅ TRUE" : "❌ FALSE") . "\n";
    
    // Test getting project code
    echo "\nStep 3: Getting project code...\n";
    $projectCode = $stateManager->getProjectCode($testUserId);
    echo "getProjectCode: " . ($projectCode ?? "NULL") . "\n";
    
    if ($isAwaiting && $projectCode) {
        echo "\n🎉 SUCCESS: ManualInputStateManager is working correctly!\n";
        echo "This means GenericmessageCommand should be able to detect Group ID input states.\n";
        
        // Test Chat ID state for comparison
        echo "\nStep 4: Testing Chat ID state...\n";
        $chatResult = $stateManager->setState($testUserId + 1, 'chat_id', $testProject);
        $isChatAwaiting = $stateManager->isAwaitingChatInput($testUserId + 1);
        echo "Chat ID state test: " . ($isChatAwaiting ? "✅ WORKING" : "❌ NOT WORKING") . "\n";
        
        // Clear states
        $stateManager->clearState($testUserId);
        $stateManager->clearState($testUserId + 1);
        echo "✅ Test states cleared\n";
        
    } else {
        echo "\n❌ PROBLEM: State management is not working as expected\n";
        if (!$isAwaiting) echo "- isAwaitingGroupInput returned false\n";
        if (!$projectCode) echo "- getProjectCode returned null\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "If this test passes, the issue must be in the webhook execution context.\n";
echo "Check DEBUG_MANUAL_INPUT logs when actually testing Group ID input via Telegram.\n";
?>