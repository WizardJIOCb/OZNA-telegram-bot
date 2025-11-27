<?php
/**
 * Deep diagnostic for manual input feedback issue
 * Following safety protocol: Runtime verification and testing
 */

echo "=== DEEP DIAGNOSTIC: Manual Input Feedback Issue ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$userId = 4225504;
$projectCode = 'ОИ789';

echo "Testing with user: {$userId}, project: {$projectCode}\n\n";

echo "STEP 1: Checking current state management files...\n";

// Check if state directory exists
$stateDir = __DIR__ . '/data/manual_input_states';
if (is_dir($stateDir)) {
    echo "   ✓ State directory exists: {$stateDir}\n";
    $files = glob($stateDir . '/*.json');
    echo "   Current state files: " . count($files) . "\n";
    foreach ($files as $file) {
        echo "     - " . basename($file) . "\n";
    }
} else {
    echo "   ✗ State directory missing: {$stateDir}\n";
}

echo "\nSTEP 2: Testing ManualInputStateManager directly...\n";

try {
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    // Test setting state for chat input
    echo "   Setting chat input state...\n";
    $result = $stateManager->setState($userId, 'chat_id', $projectCode);
    echo "   State set result: " . ($result ? "✓ Success" : "✗ Failed") . "\n";
    
    // Check if file was created
    $stateFile = $stateDir . "/user_{$userId}.json";
    if (file_exists($stateFile)) {
        echo "   ✓ State file created: {$stateFile}\n";
        $content = file_get_contents($stateFile);
        echo "   File content: {$content}\n";
        
        $state = $stateManager->getState($userId);
        echo "   Retrieved state: " . json_encode($state, JSON_UNESCAPED_UNICODE) . "\n";
        
        $isAwaitingChat = $stateManager->isAwaitingChatInput($userId);
        echo "   Is awaiting chat input: " . ($isAwaitingChat ? "✓ Yes" : "✗ No") . "\n";
        
        $retrievedProject = $stateManager->getProjectCode($userId);
        echo "   Retrieved project: {$retrievedProject}\n";
    } else {
        echo "   ✗ State file not created\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ StateManager error: " . $e->getMessage() . "\n";
}

echo "\nSTEP 3: Checking GenericmessageCommand detection logic...\n";

$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
$content = file_get_contents($genericFile);

// Check the exact order of checks in execute() method
echo "   Checking detection order in execute() method...\n";

$patterns = [
    'isAddingProject' => 'Project adding detection',
    'isAddingAdmin' => 'Admin adding detection', 
    'isAwaitingFioInput' => 'FIO input detection',
    'isAwaitingCustomInput' => 'Custom input detection',
    'isAwaitingDateInput' => 'Date input detection',
    'isAwaitingGroupInput' => 'Group input detection',
    'isAwaitingChatInput' => 'Chat input detection'
];

$executeMethodStart = strpos($content, 'public function execute()');
$executeMethodEnd = strpos($content, 'public function', $executeMethodStart + 1);
if ($executeMethodEnd === false) {
    $executeMethodEnd = strpos($content, 'private function', $executeMethodStart + 1);
}

$executeMethod = substr($content, $executeMethodStart, $executeMethodEnd - $executeMethodStart);

foreach ($patterns as $method => $description) {
    $pos = strpos($executeMethod, $method);
    if ($pos !== false) {
        echo "   ✓ {$description} at position {$pos}\n";
    } else {
        echo "   ✗ {$description} not found\n";
    }
}

echo "\nSTEP 4: Simulating real user input flow...\n";

try {
    // Clear any existing state
    $stateManager->clearState($userId);
    
    echo "   4.1. User clicks 'Ввести ID чата вручную' button\n";
    
    // Simulate ProjectHandler setting the state
    $result = $stateManager->setState($userId, 'chat_id', $projectCode);
    echo "   ProjectHandler sets state: " . ($result ? "✓" : "✗") . "\n";
    
    echo "   4.2. User sends text message with chat ID\n";
    
    // Simulate GenericmessageCommand checking state
    $isAwaitingChat = $stateManager->isAwaitingChatInput($userId);
    echo "   GenericmessageCommand detects awaiting chat: " . ($isAwaitingChat ? "✓" : "✗") . "\n";
    
    if ($isAwaitingChat) {
        $retrievedProject = $stateManager->getProjectCode($userId);
        echo "   Retrieved project for processing: {$retrievedProject}\n";
        
        if ($retrievedProject === $projectCode) {
            echo "   ✓ Project code matches - should process input\n";
        } else {
            echo "   ✗ Project code mismatch: expected '{$projectCode}', got '{$retrievedProject}'\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ✗ Simulation error: " . $e->getMessage() . "\n";
}

echo "\nSTEP 5: Checking conversation conflicts...\n";

// Check if there are other conversation states that might interfere
try {
    $conversation = new \Longman\TelegramBot\Conversation($userId, $userId, '/callback');
    if (!empty($conversation->notes)) {
        echo "   Current conversation notes:\n";
        foreach ($conversation->notes as $key => $value) {
            echo "     {$key}: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    } else {
        echo "   ✓ No conflicting conversation notes\n";
    }
} catch (Exception $e) {
    echo "   Warning: Could not check conversation: " . $e->getMessage() . "\n";
}

echo "\nSTEP 6: Testing actual method execution...\n";

// Test if the handleManualChatInput method would work
if ($stateManager->isAwaitingChatInput($userId)) {
    echo "   Testing handleManualChatInput logic...\n";
    
    $testChatId = '-1001234567890';
    echo "   Test input: {$testChatId}\n";
    
    // Validate format
    if (preg_match('/^-?\d+$/', $testChatId)) {
        echo "   ✓ Format validation passed\n";
    } else {
        echo "   ✗ Format validation failed\n";
    }
    
    // Test project retrieval
    $retrievedProject = $stateManager->getProjectCode($userId);
    if (!empty($retrievedProject)) {
        echo "   ✓ Project retrieved: {$retrievedProject}\n";
        
        // Test database update simulation
        try {
            $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
            $userData = $databaseService->getUserData($userId);
            echo "   ✓ Database service accessible\n";
            echo "   Current user data keys: " . implode(', ', array_keys($userData)) . "\n";
            
            if (isset($userData['control_projects'])) {
                echo "   ✓ User has control_projects data\n";
                $controlProjects = $userData['control_projects'];
                if (isset($controlProjects[$retrievedProject])) {
                    echo "   ✓ Project exists in control list\n";
                } else {
                    echo "   ✗ Project not found in control list\n";
                    echo "   Available projects: " . implode(', ', array_keys($controlProjects)) . "\n";
                }
            } else {
                echo "   ✗ User has no control_projects data\n";
            }
            
        } catch (Exception $e) {
            echo "   ✗ Database error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ✗ No project retrieved from state\n";
    }
}

echo "\nSTEP 7: Checking webhook and command routing...\n";

// Check if GenericmessageCommand is properly registered
$callbackFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
if (file_exists($callbackFile)) {
    echo "   ✓ CallbackqueryCommand.php exists\n";
} else {
    echo "   ✗ CallbackqueryCommand.php missing\n";
}

$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
if (file_exists($genericFile)) {
    echo "   ✓ GenericmessageCommand.php exists\n";
} else {
    echo "   ✗ GenericmessageCommand.php missing\n";
}

// Clean up test state
$stateManager->clearState($userId);

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "Run this script and check the output for any issues found.\n";
echo "Pay special attention to any ✗ marks indicating problems.\n";
?>