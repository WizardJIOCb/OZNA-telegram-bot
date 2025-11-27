<?php
/**
 * Diagnostic script to check manual input confirmation issue
 * Following safety protocols for non-programmer user
 */

echo "=== DIAGNOSING MANUAL INPUT CONFIRMATION ISSUE ===\n\n";

// Check 1: Verify ManualInputStateManager is working
echo "1. Testing ManualInputStateManager functionality...\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    $testUserId = 230379668;
    $testProject = 'TEST-DIAGNOSTIC';
    
    // Test setting state
    $result = $stateManager->setState($testUserId, 'group_id', $testProject);
    echo "   State setting result: " . ($result ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Test retrieving state
    $state = $stateManager->getState($testUserId);
    echo "   State retrieval: " . (isset($state['type']) ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Test detection methods
    $isWaiting = $stateManager->isAwaitingGroupInput($testUserId);
    echo "   Group input detection: " . ($isWaiting ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Clean up
    $stateManager->clearState($testUserId);
    echo "   State cleanup: ✓ DONE\n";
    
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

echo "\n2. Checking GenericmessageCommand integration...\n";

// Check that GenericmessageCommand has the required methods
$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
if (file_exists($genericFile)) {
    $content = file_get_contents($genericFile);
    
    $requiredMethods = [
        'handleManualGroupInputFromFile',
        'handleManualChatInputFromFile',
        'isAwaitingGroupInput',
        'isAwaitingChatInput'
    ];
    
    foreach ($requiredMethods as $method) {
        $found = strpos($content, $method) !== false;
        echo "   Method {$method}: " . ($found ? "✓ FOUND" : "✗ MISSING") . "\n";
    }
    
    // Check if file-based state checking is implemented
    $hasFileStateCheck = strpos($content, '$this->stateManager->isAwaitingGroupInput($userId)') !== false;
    echo "   File-based state checking: " . ($hasFileStateCheck ? "✓ IMPLEMENTED" : "✗ MISSING") . "\n";
    
} else {
    echo "   ✗ GenericmessageCommand.php not found!\n";
}

echo "\n3. Checking ProjectHandler integration...\n";

$projectFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($projectFile)) {
    $content = file_get_contents($projectFile);
    
    // Check if ProjectHandler is using ManualInputStateManager
    $usesStateManager = strpos($content, 'ManualInputStateManager') !== false;
    echo "   Uses ManualInputStateManager: " . ($usesStateManager ? "✓ YES" : "✗ NO") . "\n";
    
    // Check if setState is called for group_id
    $setsGroupState = strpos($content, "setState(\$userId, 'group_id'") !== false;
    echo "   Sets group ID state: " . ($setsGroupState ? "✓ YES" : "✗ NO") . "\n";
    
    // Check if setState is called for chat_id
    $setsChatState = strpos($content, "setState(\$userId, 'chat_id'") !== false;
    echo "   Sets chat ID state: " . ($setsChatState ? "✓ YES" : "✗ NO") . "\n";
    
} else {
    echo "   ✗ ProjectHandler.php not found!\n";
}

echo "\n4. Checking data directory permissions...\n";

$dataDir = __DIR__ . '/data/manual_input_states';
if (is_dir($dataDir)) {
    echo "   Manual input states directory: ✓ EXISTS\n";
    echo "   Directory writable: " . (is_writable($dataDir) ? "✓ YES" : "✗ NO") . "\n";
} else {
    echo "   Manual input states directory: ✗ MISSING\n";
    echo "   Attempting to create directory...\n";
    if (mkdir($dataDir, 0755, true)) {
        echo "   Directory created: ✓ SUCCESS\n";
    } else {
        echo "   Directory creation: ✗ FAILED\n";
    }
}

echo "\n5. Testing actual manual input flow...\n";

try {
    // Simulate the complete manual input flow
    $userId = 230379668;
    $projectCode = 'TEST-FLOW';
    
    echo "   Step 1: Setting up manual group input state...\n";
    require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    $stateManager->setState($userId, 'group_id', $projectCode);
    
    echo "   Step 2: Checking if GenericmessageCommand would detect the state...\n";
    $isDetected = $stateManager->isAwaitingGroupInput($userId);
    echo "   State detection: " . ($isDetected ? "✓ WOULD BE DETECTED" : "✗ WOULD NOT BE DETECTED") . "\n";
    
    if ($isDetected) {
        echo "   Step 3: Testing input validation...\n";
        $testGroupId = '-1001234567890';
        $isValid = preg_match('/^-100\d+$/', $testGroupId);
        echo "   Group ID validation: " . ($isValid ? "✓ VALID FORMAT" : "✗ INVALID FORMAT") . "\n";
    }
    
    // Clean up
    $stateManager->clearState($userId);
    echo "   Cleanup: ✓ DONE\n";
    
} catch (Exception $e) {
    echo "   ✗ ERROR in flow test: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n\n";

echo "If all checks show ✓ SUCCESS/YES/FOUND, then the issue might be:\n";
echo "1. Webhook not properly configured\n";
echo "2. Bot not receiving messages\n";
echo "3. PHP execution context differences\n";
echo "4. Telegram API connectivity issues\n\n";

echo "If any checks show ✗, those components need to be fixed first.\n";
?>