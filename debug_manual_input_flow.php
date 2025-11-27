<?php
/**
 * Debug Manual Input Flow
 * 
 * This script will help us understand why manual input isn't working
 * by checking the current state and flow execution.
 */

require_once 'CustomCommands/Services/ManualInputStateManager.php';

echo "=== Debugging Manual Input Flow ===\n\n";

// Test user
$testUserId = 12345;
$testProject = "DEBUG_TEST";

echo "1. Testing ManualInputStateManager:\n";

$stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();

// Test setting state
echo "   Setting group input state... ";
$result = $stateManager->setState($testUserId, 'group_id', $testProject);
echo $result ? "✅" : "❌";
echo "\n";

// Check state file exists
$stateDir = __DIR__ . '/data/manual_input_states';
$stateFile = $stateDir . "/user_{$testUserId}.json";
echo "   State file exists: ";
echo file_exists($stateFile) ? "✅" : "❌";
echo "\n";

if (file_exists($stateFile)) {
    $content = file_get_contents($stateFile);
    echo "   State file content: " . $content . "\n";
}

// Test checking state
echo "   Checking isAwaitingGroupInput: ";
$isAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
echo $isAwaiting ? "✅" : "❌";
echo "\n";

$stateManager->clearState($testUserId);

echo "\n2. Testing GenericmessageCommand Integration:\n";

// Check if GenericmessageCommand properly initializes StateManager
$commandFile = 'CustomCommands/GenericmessageCommand.php';
$content = file_get_contents($commandFile);

echo "   StateManager initialization: ";
echo strpos($content, 'new ManualInputStateManager()') !== false ? "✅" : "❌";
echo "\n";

echo "   StateManager usage in execute(): ";
echo strpos($content, '$this->stateManager->isAwaitingGroupInput') !== false ? "✅" : "❌";
echo "\n";

echo "   StateManager usage in execute() for chat: ";
echo strpos($content, '$this->stateManager->isAwaitingChatInput') !== false ? "✅" : "❌";
echo "\n";

echo "\n3. Checking ProjectHandler Integration:\n";

$projectHandlerFile = 'CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($projectHandlerFile)) {
    $projectContent = file_get_contents($projectHandlerFile);
    
    echo "   ProjectHandler exists: ✅\n";
    echo "   Uses ManualInputStateManager: ";
    echo strpos($projectContent, 'ManualInputStateManager') !== false ? "✅" : "❌";
    echo "\n";
    
    echo "   Sets group input state: ";
    echo strpos($projectContent, "setState(\$userId, 'group_id'") !== false ? "✅" : "❌";
    echo "\n";
    
    echo "   Sets chat input state: ";
    echo strpos($projectContent, "setState(\$userId, 'chat_id'") !== false ? "✅" : "❌";
    echo "\n";
} else {
    echo "   ProjectHandler exists: ❌\n";
}

echo "\n4. Testing Error Scenarios:\n";

// Check if there might be conflicts with conversation-based state
echo "   Looking for conversation-based manual input: ";
$hasConversationManual = strpos($content, "notes['manual_input_type']") !== false;
echo $hasConversationManual ? "⚠️ FOUND (potential conflict)" : "✅";
echo "\n";

echo "   Looking for mixed state approaches: ";
$hasFileState = strpos($content, '$this->stateManager->') !== false;
$hasConvState = strpos($content, "notes['manual_input") !== false;
if ($hasFileState && $hasConvState) {
    echo "⚠️ MIXED APPROACHES DETECTED\n";
} else if ($hasFileState && !$hasConvState) {
    echo "✅ CONSISTENT FILE-BASED\n";
} else {
    echo "❌ NO STATE MANAGEMENT\n";
}

echo "\n5. File Permissions Check:\n";

// Check if we can write to data directory
$dataDir = __DIR__ . '/data';
$stateDir = $dataDir . '/manual_input_states';

echo "   Data directory exists: ";
echo is_dir($dataDir) ? "✅" : "❌";
echo "\n";

echo "   State directory exists: ";
echo is_dir($stateDir) ? "✅" : "❌";
echo "\n";

echo "   State directory writable: ";
echo is_writable($stateDir) ? "✅" : "❌";
echo "\n";

if (!is_dir($stateDir)) {
    echo "   Attempting to create state directory... ";
    $created = mkdir($stateDir, 0755, true);
    echo $created ? "✅" : "❌";
    echo "\n";
}

echo "\n=== Debug Complete ===\n";
echo "If any items show ❌ or ⚠️, these may be the source of the issue.\n";