<?php
/**
 * Test Manual Input Confirmation Issue
 * 
 * This test simulates the manual input flow to check if confirmation messages 
 * are working correctly for Group ID and Chat ID input.
 */

require_once 'CustomCommands/Services/ManualInputStateManager.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager;

echo "=== Testing Manual Input Confirmation Issue ===\n\n";

// Test parameters
$testUserId = 12345;
$testProjectCode = "TEST_PROJECT";

// Initialize state manager
$stateManager = new ManualInputStateManager();

echo "1. Testing ManualInputStateManager functionality:\n";

// Test setting group input state
echo "   - Setting group input state... ";
$result = $stateManager->setState($testUserId, 'group_id', $testProjectCode);
echo $result ? "✅ SUCCESS" : "❌ FAILED";
echo "\n";

// Test checking group input state
echo "   - Checking group input state... ";
$isAwaitingGroup = $stateManager->isAwaitingGroupInput($testUserId);
echo $isAwaitingGroup ? "✅ SUCCESS" : "❌ FAILED";
echo "\n";

// Test getting project code
echo "   - Getting project code... ";
$projectCode = $stateManager->getProjectCode($testUserId);
echo ($projectCode === $testProjectCode) ? "✅ SUCCESS ($projectCode)" : "❌ FAILED (got: $projectCode)";
echo "\n";

// Clear state
$stateManager->clearState($testUserId);

// Test chat input state
echo "   - Setting chat input state... ";
$result = $stateManager->setState($testUserId, 'chat_id', $testProjectCode);
echo $result ? "✅ SUCCESS" : "❌ FAILED";
echo "\n";

echo "   - Checking chat input state... ";
$isAwaitingChat = $stateManager->isAwaitingChatInput($testUserId);
echo $isAwaitingChat ? "✅ SUCCESS" : "❌ FAILED";
echo "\n";

$stateManager->clearState($testUserId);

echo "\n2. Testing GenericmessageCommand integration:\n";

// Check if GenericmessageCommand file exists and has required methods
$commandFile = 'CustomCommands/GenericmessageCommand.php';
if (file_exists($commandFile)) {
    echo "   - GenericmessageCommand file exists: ✅\n";
    
    $content = file_get_contents($commandFile);
    
    // Check for required methods
    $requiredMethods = [
        'handleManualGroupInput' => 'Group input handler',
        'handleManualChatInput' => 'Chat input handler',
        'isAwaitingGroupInput' => 'Group input checker',
        'isAwaitingChatInput' => 'Chat input checker'
    ];
    
    foreach ($requiredMethods as $method => $description) {
        if (strpos($content, $method) !== false) {
            echo "   - $description method found: ✅\n";
        } else {
            echo "   - $description method found: ❌ MISSING\n";
        }
    }
    
    // Check for confirmation messages
    echo "\n3. Testing confirmation message patterns:\n";
    
    $confirmationPatterns = [
        '✅ <b>ID группы сохранён</b>' => 'Group ID confirmation',
        '✅ <b>ID чата сохранён</b>' => 'Chat ID confirmation',
        'К настройкам' => 'Navigation button',
        'Вернуться к настройкам проекта' => 'Project settings navigation'
    ];
    
    foreach ($confirmationPatterns as $pattern => $description) {
        if (strpos($content, $pattern) !== false) {
            echo "   - $description found: ✅\n";
        } else {
            echo "   - $description found: ❌ MISSING\n";
        }
    }
    
    // Check state manager usage in GenericmessageCommand
    echo "\n4. Testing state management integration:\n";
    
    if (strpos($content, '$this->stateManager->isAwaitingGroupInput') !== false) {
        echo "   - Group input state check: ✅\n";
    } else {
        echo "   - Group input state check: ❌ MISSING\n";
    }
    
    if (strpos($content, '$this->stateManager->isAwaitingChatInput') !== false) {
        echo "   - Chat input state check: ✅\n";
    } else {
        echo "   - Chat input state check: ❌ MISSING\n";
    }
    
    if (strpos($content, '$this->stateManager->getProjectCode') !== false) {
        echo "   - Project code retrieval: ✅\n";
    } else {
        echo "   - Project code retrieval: ❌ MISSING\n";
    }
    
    if (strpos($content, '$this->stateManager->clearState') !== false) {
        echo "   - State cleanup: ✅\n";
    } else {
        echo "   - State cleanup: ❌ MISSING\n";
    }
    
} else {
    echo "   - GenericmessageCommand file: ❌ NOT FOUND\n";
}

echo "\n=== Test Complete ===\n";