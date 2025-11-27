<?php
/**
 * Targeted test for Group ID vs Chat ID issue
 * Following safety protocols for non-programmer user
 */

echo "=== TARGETED GROUP ID vs CHAT ID TEST ===\n\n";

echo "ISSUE: Chat ID shows confirmation, Group ID doesn't\n";
echo "TESTING: Both input flows side by side\n\n";

try {
    require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
    
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    $userId = 230379668;
    $projectCode = 'ОИ834';
    
    echo "1. TESTING GROUP ID PROCESSING...\n";
    
    // Set Group ID state (what ProjectHandler does)
    $stateManager->setState($userId, 'group_id', $projectCode);
    
    // Check if state is detected (what GenericmessageCommand checks)
    $groupWaiting = $stateManager->isAwaitingGroupInput($userId);
    echo "   Group state detected: " . ($groupWaiting ? "✓ YES" : "✗ NO") . "\n";
    
    if ($groupWaiting) {
        $groupProject = $stateManager->getProjectCode($userId);
        echo "   Group project code: {$groupProject}\n";
        
        // Test Group ID validation
        $testGroupId = '-1001234567890';
        $groupValid = preg_match('/^-100\\d+$/', trim($testGroupId));
        echo "   Group ID '{$testGroupId}' valid: " . ($groupValid ? "✓ YES" : "✗ NO") . "\n";
        
        // Test invalid Group ID
        $invalidGroupId = '123456789';
        $invalidValid = preg_match('/^-100\\d+$/', trim($invalidGroupId));
        echo "   Invalid Group ID '{$invalidGroupId}' rejected: " . (!$invalidValid ? "✓ YES" : "✗ NO") . "\n";
    }
    
    $stateManager->clearState($userId);
    
    echo "\n2. TESTING CHAT ID PROCESSING...\n";
    
    // Set Chat ID state
    $stateManager->setState($userId, 'chat_id', $projectCode);
    
    // Check if state is detected
    $chatWaiting = $stateManager->isAwaitingChatInput($userId);
    echo "   Chat state detected: " . ($chatWaiting ? "✓ YES" : "✗ NO") . "\n";
    
    if ($chatWaiting) {
        $chatProject = $stateManager->getProjectCode($userId);
        echo "   Chat project code: {$chatProject}\n";
        
        // Test Chat ID validation
        $testChatId = '123456789';
        $chatValid = preg_match('/^-?\\d+$/', trim($testChatId));
        echo "   Chat ID '{$testChatId}' valid: " . ($chatValid ? "✓ YES" : "✗ NO") . "\n";
        
        // Test Group-style Chat ID
        $groupStyleChatId = '-1001234567890';
        $groupStyleValid = preg_match('/^-?\\d+$/', trim($groupStyleChatId));
        echo "   Group-style Chat ID '{$groupStyleChatId}' valid: " . ($groupStyleValid ? "✓ YES" : "✗ NO") . "\n";
    }
    
    $stateManager->clearState($userId);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. CHECKING IF THERE'S A STATE CONFLICT...\n";

// Test if there could be a state conflict
try {
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    // Set Group ID state
    $stateManager->setState($userId, 'group_id', $projectCode);
    
    // Check what state is actually stored
    $state = $stateManager->getState($userId);
    if ($state) {
        echo "   Stored state type: {$state['type']}\n";
        echo "   Stored project: {$state['project']}\n";
        echo "   State timestamp: " . date('Y-m-d H:i:s', $state['timestamp']) . "\n";
        echo "   Expires at: " . date('Y-m-d H:i:s', $state['expires_at']) . "\n";
        
        // Check both detection methods
        $detectGroup = $stateManager->isAwaitingGroupInput($userId);
        $detectChat = $stateManager->isAwaitingChatInput($userId);
        
        echo "   Group detection: " . ($detectGroup ? "✓ DETECTED" : "✗ NOT DETECTED") . "\n";
        echo "   Chat detection: " . ($detectChat ? "✓ DETECTED" : "✗ NOT DETECTED") . "\n";
        
        if ($detectGroup && $detectChat) {
            echo "   ⚠️ WARNING: Both states detected simultaneously!\n";
        }
    } else {
        echo "   ✗ No state found\n";
    }
    
    $stateManager->clearState($userId);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. TESTING ACTUAL INPUT VALIDATION...\n";

// Test the exact validation that would happen
$testInputs = [
    ['type' => 'Group ID', 'value' => '-1001234567890', 'regex' => '/^-100\\d+$/'],
    ['type' => 'Group ID', 'value' => '123456789', 'regex' => '/^-100\\d+$/'],
    ['type' => 'Chat ID', 'value' => '123456789', 'regex' => '/^-?\\d+$/'],
    ['type' => 'Chat ID', 'value' => '-1001234567890', 'regex' => '/^-?\\d+$/'],
];

foreach ($testInputs as $test) {
    $isValid = preg_match($test['regex'], trim($test['value']));
    echo "   {$test['type']} '{$test['value']}': " . ($isValid ? "✓ VALID" : "✗ INVALID") . "\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n\n";

echo "POSSIBLE ISSUES:\n";
echo "1. Group ID validation is too strict (requires -100 prefix)\n";
echo "2. User might be entering Chat ID format when Group ID is expected\n";
echo "3. State detection logic might have an edge case\n";
echo "4. Group ID handler might have a silent error\n";
echo "5. Database save might be failing for Group ID specifically\n\n";

echo "NEXT STEPS:\n";
echo "1. Check what exact Group ID format you're entering\n";
echo "2. Try entering a Group ID that starts with -100\n";
echo "3. Check if there are any PHP errors in the logs\n";
echo "4. Compare the exact database operations for both cases\n\n";

echo "RECOMMENDATION:\n";
echo "Try entering a Group ID in this exact format: -1001234567890\n";
echo "Make sure it starts with -100 followed by numbers only.\n";

?>