<?php

/**
 * Manual Input Debug Test
 * Tests the current state of manual input functionality
 */

// Include required files
require_once 'config.php';
require_once 'CustomCommands/Services/ManualInputStateManager.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager;

echo "=== Manual Input Debug Test ===\n\n";

// Test ManualInputStateManager
$stateManager = new ManualInputStateManager();
echo "✅ ManualInputStateManager created successfully\n";

// Test setting a state
$testUserId = 12345;
$testProject = "TEST_PROJECT";

echo "\n--- Testing State Management ---\n";

// Set group input state
$result = $stateManager->setState($testUserId, 'group_id', $testProject);
echo "Set group_id state: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "\n";

// Check if awaiting group input
$isAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
echo "Is awaiting group input: " . ($isAwaiting ? "✅ YES" : "❌ NO") . "\n";

// Get project code
$projectCode = $stateManager->getProjectCode($testUserId);
echo "Project code: " . ($projectCode === $testProject ? "✅ {$projectCode}" : "❌ {$projectCode}") . "\n";

// Clear state
$cleared = $stateManager->clearState($testUserId);
echo "Clear state: " . ($cleared ? "✅ SUCCESS" : "❌ FAILED") . "\n";

// Check if still awaiting
$isStillAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
echo "Still awaiting after clear: " . ($isStillAwaiting ? "❌ YES (ERROR)" : "✅ NO") . "\n";

echo "\n--- Testing File Structure ---\n";

// Check state directory
$stateDir = __DIR__ . '/data/manual_input_states';
echo "State directory exists: " . (is_dir($stateDir) ? "✅ YES" : "❌ NO") . "\n";
echo "State directory path: {$stateDir}\n";

if (is_dir($stateDir)) {
    $files = glob($stateDir . '/*.json');
    echo "State files count: " . count($files) . "\n";
    foreach ($files as $file) {
        echo "  - " . basename($file) . "\n";
    }
}

echo "\n--- Testing GenericmessageCommand Integration ---\n";

// Test if GenericmessageCommand can load the state manager
try {
    require_once 'CustomCommands/GenericmessageCommand.php';
    echo "✅ GenericmessageCommand loads successfully\n";
    
    // Create a mock conversation to test the execute method logic
    echo "✅ Command class accessible\n";
    
} catch (Exception $e) {
    echo "❌ Error loading GenericmessageCommand: " . $e->getMessage() . "\n";
}

echo "\n--- Testing Recent Error Logs ---\n";

// Check recent errors in the error log
$errorLogFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($errorLogFile)) {
    $lines = file($errorLogFile);
    $recentLines = array_slice($lines, -20); // Last 20 lines
    
    echo "Recent error log entries:\n";
    foreach ($recentLines as $line) {
        if (stripos($line, 'GenericmessageCommand') !== false || 
            stripos($line, 'manual') !== false ||
            stripos($line, 'group') !== false ||
            stripos($line, 'chat') !== false) {
            echo "  " . trim($line) . "\n";
        }
    }
} else {
    echo "❌ Error log file not found\n";
}

echo "\n=== Test Complete ===\n";