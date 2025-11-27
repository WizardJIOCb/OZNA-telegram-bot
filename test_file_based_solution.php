<?php
/**
 * Test the file-based manual input solution
 */

echo "=== TESTING FILE-BASED MANUAL INPUT SOLUTION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';

$userId = 4225504;
$projectCode = 'ОИ789';

echo "Testing file-based state management:\n";
echo "User ID: {$userId}\n";
echo "Project: {$projectCode}\n\n";

echo "STEP 1: User clicks 'Ввести ID группы вручную'\n";
echo "ProjectHandler calls setState()\n\n";

try {
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    // Set state for group input
    $result = $stateManager->setState($userId, 'group_id', $projectCode);
    echo "Set state result: " . ($result ? "✓ Success" : "❌ Failed") . "\n";
    
    // Check if state was saved
    $state = $stateManager->getState($userId);
    echo "Retrieved state: " . json_encode($state, JSON_UNESCAPED_UNICODE) . "\n";
    
    // Test detection
    $isWaitingGroup = $stateManager->isAwaitingGroupInput($userId);
    echo "Is awaiting group input: " . ($isWaitingGroup ? "✓ Yes" : "❌ No") . "\n";
    
    $projectFromState = $stateManager->getProjectCode($userId);
    echo "Project from state: " . ($projectFromState ?? 'NULL') . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error in step 1: " . $e->getMessage() . "\n";
    exit(1);
}

echo "STEP 2: User types group ID: -1001234567890\n";
echo "GenericmessageCommand detects state and processes\n\n";

try {
    // Simulate GenericmessageCommand detection
    $isDetected = $stateManager->isAwaitingGroupInput($userId);
    echo "GenericmessageCommand detection: " . ($isDetected ? "✓ DETECTED" : "❌ NOT DETECTED") . "\n";
    
    if ($isDetected) {
        echo "✅ SUCCESS: User input would be processed!\n";
        
        $inputText = '-1001234567890';
        echo "  Input: {$inputText}\n";
        
        // Validate format
        $isValid = preg_match('/^-100\d+$/', trim($inputText));
        echo "  Validation: " . ($isValid ? "✓ Valid" : "❌ Invalid") . "\n";
        
        if ($isValid) {
            // Simulate database save
            echo "  ✓ Would save to database\n";
            
            // Clear state
            $stateManager->clearState($userId);
            echo "  ✓ State cleared\n";
            
            // Verify state is cleared
            $clearedState = $stateManager->getState($userId);
            echo "  State after clear: " . ($clearedState === null ? "✓ NULL" : "❌ Still exists") . "\n";
            
            echo "  ✅ Would send success message with navigation\n";
        }
    } else {
        echo "❌ FAILURE: No feedback would be provided\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error in step 2: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING CHAT ID FLOW ===\n\n";

echo "STEP 1: Set chat input state\n";
try {
    $result = $stateManager->setState($userId, 'chat_id', $projectCode);
    echo "Set chat state: " . ($result ? "✓ Success" : "❌ Failed") . "\n";
    
    $isWaitingChat = $stateManager->isAwaitingChatInput($userId);
    echo "Is awaiting chat input: " . ($isWaitingChat ? "✓ Yes" : "❌ No") . "\n";
    
    // Clear for cleanup
    $stateManager->clearState($userId);
    
} catch (Exception $e) {
    echo "❌ Error testing chat flow: " . $e->getMessage() . "\n";
}

echo "\n=== FILE-BASED SOLUTION TEST COMPLETE ===\n";
echo "This solution bypasses database conversation limitations!\n";
echo "Users should now get proper feedback when entering IDs.\n";