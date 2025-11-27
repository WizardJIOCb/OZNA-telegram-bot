<?php
/**
 * Runtime verification for manual input feedback issue
 * Testing actual behavior to ensure safe implementation
 */

echo "=== RUNTIME VERIFICATION: Manual Input Issue ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$userId = 230379668; // Your user ID
$projectCode = 'ОИ834';

echo "Testing for user: {$userId}\n";
echo "Project: {$projectCode}\n\n";

// Test 1: Check if GenericmessageCommand is actually active
echo "1. CHECKING GENERICMESSAGECOMMAND STATUS:\n";
$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
$textmenuFile = __DIR__ . '/CustomCommands/TextmenuCommand.php';

if (file_exists($genericFile)) {
    echo "   ✓ GenericmessageCommand.php exists\n";
    
    $content = file_get_contents($genericFile);
    if (strpos($content, "protected \$name = 'genericmessage'") !== false) {
        echo "   ✓ Has correct command name\n";
    } else {
        echo "   ❌ Missing or incorrect command name\n";
    }
    
    // Check for manual input handlers
    if (strpos($content, 'isAwaitingGroupInput') !== false) {
        echo "   ✓ Has group input handler\n";
    } else {
        echo "   ❌ Missing group input handler\n";
    }
    
    if (strpos($content, 'isAwaitingChatInput') !== false) {
        echo "   ✓ Has chat input handler\n";
    } else {
        echo "   ❌ Missing chat input handler\n";
    }
} else {
    echo "   ❌ GenericmessageCommand.php not found\n";
}

// Test 2: Check TextmenuCommand name to avoid conflicts
if (file_exists($textmenuFile)) {
    $textContent = file_get_contents($textmenuFile);
    if (strpos($textContent, "protected \$name = 'textmenu'") !== false) {
        echo "   ✓ TextmenuCommand has 'textmenu' name (no conflict)\n";
    } else if (strpos($textContent, "protected \$name = 'genericmessage'") !== false) {
        echo "   ❌ CONFLICT: TextmenuCommand also uses 'genericmessage' name\n";
    } else {
        echo "   ⚠️ TextmenuCommand name unclear\n";
    }
}

echo "\n2. TESTING STATE MANAGER:\n";
try {
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    // Test state setting
    $result = $stateManager->setState($userId, 'group_id', $projectCode);
    echo "   State setting: " . ($result ? "✓ Works" : "❌ Failed") . "\n";
    
    // Test state detection
    $isWaiting = $stateManager->isAwaitingGroupInput($userId);
    echo "   State detection: " . ($isWaiting ? "✓ Works" : "❌ Failed") . "\n";
    
    // Test project retrieval
    $retrievedProject = $stateManager->getProjectCode($userId);
    echo "   Project retrieval: " . ($retrievedProject === $projectCode ? "✓ Works" : "❌ Failed") . "\n";
    
    // Clean up
    $stateManager->clearState($userId);
    echo "   State cleanup: ✓ Done\n";
    
} catch (Exception $e) {
    echo "   ❌ StateManager error: " . $e->getMessage() . "\n";
}

echo "\n3. TESTING DATABASE CONNECTIVITY:\n";
try {
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $userData = $databaseService->getUserData($userId);
    
    if ($userData) {
        echo "   ✓ Database connection works\n";
        echo "   ✓ User data exists\n";
        
        $controlProjects = $userData['control_projects'] ?? [];
        echo "   Control projects: " . count($controlProjects) . " found\n";
        
        if (isset($controlProjects[$projectCode])) {
            echo "   ✓ Project {$projectCode} exists in user data\n";
        } else {
            echo "   ⚠️ Project {$projectCode} not in user control projects\n";
        }
    } else {
        echo "   ❌ No user data found\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n4. CHECKING FILE SYSTEM:\n";
$stateDir = __DIR__ . '/data/manual_input_states';
echo "   State directory: " . (is_dir($stateDir) ? "✓ Exists" : "❌ Missing") . "\n";
echo "   Writable: " . (is_writable($stateDir) ? "✓ Yes" : "❌ No") . "\n";

echo "\n5. SIMULATING REAL WORKFLOW:\n";
try {
    echo "   Step 1: User clicks manual input button\n";
    
    // Simulate ProjectHandler setting state
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    $stateManager->setState($userId, 'group_id', $projectCode);
    echo "   ✓ State set by ProjectHandler\n";
    
    echo "   Step 2: User types group ID: -1002722176147\n";
    
    // Check if GenericmessageCommand would detect this
    $isDetected = $stateManager->isAwaitingGroupInput($userId);
    echo "   GenericmessageCommand detection: " . ($isDetected ? "✓ DETECTED" : "❌ NOT DETECTED") . "\n";
    
    if ($isDetected) {
        echo "   ✓ Input would be processed\n";
        echo "   ✓ User should see confirmation message\n";
    } else {
        echo "   ❌ Input would be IGNORED\n";
        echo "   ❌ User sees NO FEEDBACK\n";
    }
    
    $stateManager->clearState($userId);
    
} catch (Exception $e) {
    echo "   ❌ Simulation error: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION RESULTS ===\n";
echo "If all tests show ✓, the system should work.\n";
echo "If any tests show ❌, that's the cause of the problem.\n\n";

echo "NEXT STEPS:\n";
echo "1. If system tests pass but bot doesn't work - webhook/server issue\n";
echo "2. If state detection fails - GenericmessageCommand not receiving messages\n";
echo "3. If conflicts found - command naming conflicts\n";
echo "4. If file system fails - permission issues\n";

?>