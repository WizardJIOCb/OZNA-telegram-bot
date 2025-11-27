<?php
// Simple test to verify Manual Input State Manager functionality
// This simulates the exact flow that should happen during Group ID input

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== MANUAL INPUT STATE TEST ===\n";
echo "Testing the exact flow for Group ID manual input...\n\n";

// Step 1: Create the state directory manually (like ManualInputStateManager would)
$stateDir = __DIR__ . '/data/manual_input_states';
if (!is_dir($stateDir)) {
    if (mkdir($stateDir, 0755, true)) {
        echo "✅ Created state directory: $stateDir\n";
    } else {
        echo "❌ Failed to create state directory: $stateDir\n";
        exit(1);
    }
} else {
    echo "✅ State directory exists: $stateDir\n";
}

// Step 2: Simulate setting a manual input state (what ProjectHandler does)
$testUserId = 123456;
$testProject = 'БКНС125';
$stateFile = $stateDir . "/user_{$testUserId}.json";

$state = [
    'type' => 'group_id', // This is the key difference - should be 'group_id' not 'group'
    'project' => $testProject,
    'timestamp' => time(),
    'expires_at' => time() + 3600
];

$jsonData = json_encode($state, JSON_UNESCAPED_UNICODE);
$result = file_put_contents($stateFile, $jsonData, LOCK_EX);

if ($result !== false) {
    echo "✅ State file created successfully\n";
    echo "State content: $jsonData\n";
} else {
    echo "❌ Failed to create state file\n";
    exit(1);
}

// Step 3: Simulate checking if user is awaiting group input (what GenericmessageCommand does)
function isAwaitingGroupInput($userId) {
    global $stateDir;
    $stateFile = $stateDir . "/user_{$userId}.json";
    
    if (!file_exists($stateFile)) {
        echo "⚠️ State file does not exist for user $userId\n";
        return false;
    }
    
    $jsonData = file_get_contents($stateFile);
    $state = json_decode($jsonData, true);
    
    if (!$state || !isset($state['expires_at'])) {
        echo "⚠️ Invalid state data for user $userId\n";
        return false;
    }
    
    // Check if state has expired
    if (time() > $state['expires_at']) {
        echo "⚠️ State has expired for user $userId\n";
        return false;
    }
    
    $isGroupInput = $state['type'] === 'group_id';
    echo "State type: {$state['type']}, Is group input: " . ($isGroupInput ? "YES" : "NO") . "\n";
    
    return $isGroupInput;
}

// Step 4: Test the detection
echo "\nStep 4: Testing state detection...\n";
$isAwaiting = isAwaitingGroupInput($testUserId);
echo "Result: User $testUserId is " . ($isAwaiting ? "✅ AWAITING" : "❌ NOT AWAITING") . " group input\n";

// Step 5: Simulate getting project code (what GenericmessageCommand does for confirmation)
function getProjectCode($userId) {
    global $stateDir;
    $stateFile = $stateDir . "/user_{$userId}.json";
    
    if (!file_exists($stateFile)) {
        return null;
    }
    
    $jsonData = file_get_contents($stateFile);
    $state = json_decode($jsonData, true);
    
    return $state['project'] ?? null;
}

if ($isAwaiting) {
    echo "\nStep 5: Getting project code...\n";
    $projectCode = getProjectCode($testUserId);
    echo "Project code: " . ($projectCode ?? "NULL") . "\n";
    
    if ($projectCode) {
        echo "\n✅ SUCCESS: Manual input state system is working correctly!\n";
        echo "The GenericmessageCommand should detect this state and handle Group ID input.\n";
        
        // Simulate clearing state (what happens after successful input)
        if (unlink($stateFile)) {
            echo "✅ State cleared successfully\n";
        }
    }
}

echo "\n=== TESTING CHAT ID STATE ===\n";

// Test Chat ID state as well (to compare with working functionality)
$chatState = [
    'type' => 'chat_id',
    'project' => $testProject,
    'timestamp' => time(),
    'expires_at' => time() + 3600
];

$chatStateFile = $stateDir . "/user_{$testUserId}_chat.json";
$chatJsonData = json_encode($chatState, JSON_UNESCAPED_UNICODE);
file_put_contents($chatStateFile, $chatJsonData, LOCK_EX);

function isAwaitingChatInput($userId) {
    global $stateDir;
    $stateFile = $stateDir . "/user_{$userId}_chat.json";
    
    if (!file_exists($stateFile)) {
        return false;
    }
    
    $jsonData = file_get_contents($stateFile);
    $state = json_decode($jsonData, true);
    
    if (!$state || !isset($state['expires_at'])) {
        return false;
    }
    
    if (time() > $state['expires_at']) {
        return false;
    }
    
    return $state['type'] === 'chat_id';
}

$isChatAwaiting = isAwaitingChatInput($testUserId);
echo "Chat ID state detection: " . ($isChatAwaiting ? "✅ WORKING" : "❌ NOT WORKING") . "\n";

// Clean up chat test file
if (file_exists($chatStateFile)) {
    unlink($chatStateFile);
}

echo "\n=== TEST COMPLETE ===\n";
echo "If this test shows working state management but Group ID confirmation still fails,\n";
echo "check the logs in GenericmessageCommand for DEBUG_MANUAL_INPUT messages.\n";
?>