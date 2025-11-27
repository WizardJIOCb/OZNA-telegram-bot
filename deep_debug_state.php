<?php
/**
 * Deep Debug: State File Creation
 * 
 * This will help us understand exactly why state files aren't being created
 */

require_once 'CustomCommands/Services/ManualInputStateManager.php';

echo "=== Deep Debug: State File Creation ===\n\n";

$testUserId = 99999;
$testProject = "FILE_TEST";

echo "1. Testing file creation step by step:\n";

$stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();

// Check directory path
$reflection = new ReflectionClass($stateManager);
$stateDir = $reflection->getProperty('stateDir');
$stateDir->setAccessible(true);
$stateDirPath = $stateDir->getValue($stateManager);

echo "   State directory path: $stateDirPath\n";
echo "   Directory exists: " . (is_dir($stateDirPath) ? "✅" : "❌") . "\n";
echo "   Directory writable: " . (is_writable($stateDirPath) ? "✅" : "❌") . "\n";

// Test manual file creation
$testFile = $stateDirPath . "/test_file.json";
echo "   Creating test file: $testFile\n";

$testData = ['test' => 'data', 'timestamp' => time()];
$jsonData = json_encode($testData);
$writeResult = file_put_contents($testFile, $jsonData);

echo "   Test file write result: " . ($writeResult !== false ? "✅ ($writeResult bytes)" : "❌") . "\n";
echo "   Test file exists: " . (file_exists($testFile) ? "✅" : "❌") . "\n";

if (file_exists($testFile)) {
    $readContent = file_get_contents($testFile);
    echo "   Test file content: $readContent\n";
    unlink($testFile);
    echo "   Test file deleted: ✅\n";
}

echo "\n2. Testing setState method:\n";

echo "   Calling setState...\n";
$setResult = $stateManager->setState($testUserId, 'group_id', $testProject);
echo "   setState returned: " . ($setResult ? "✅" : "❌") . "\n";

// Check if file was created
$userFile = $stateDirPath . "/user_{$testUserId}.json";
echo "   Expected file: $userFile\n";
echo "   File exists after setState: " . (file_exists($userFile) ? "✅" : "❌") . "\n";

if (file_exists($userFile)) {
    $content = file_get_contents($userFile);
    echo "   File content: $content\n";
    
    $data = json_decode($content, true);
    if ($data) {
        echo "   JSON is valid: ✅\n";
        echo "   Type: " . ($data['type'] ?? 'MISSING') . "\n";
        echo "   Project: " . ($data['project'] ?? 'MISSING') . "\n";
        echo "   Expires at: " . ($data['expires_at'] ?? 'MISSING') . "\n";
    } else {
        echo "   JSON is valid: ❌\n";
    }
} else {
    echo "   File creation failed!\n";
    
    // Check parent directory permissions
    $parentDir = dirname($userFile);
    echo "   Parent dir writable: " . (is_writable($parentDir) ? "✅" : "❌") . "\n";
    echo "   Parent dir readable: " . (is_readable($parentDir) ? "✅" : "❌") . "\n";
    
    // Try to manually create the file
    echo "   Trying manual creation...\n";
    $manualResult = file_put_contents($userFile, '{"manual":"test"}');
    echo "   Manual creation result: " . ($manualResult !== false ? "✅" : "❌") . "\n";
}

echo "\n3. Testing getState method:\n";

$getResult = $stateManager->getState($testUserId);
echo "   getState returned: " . ($getResult ? "✅ (data found)" : "❌ (no data)") . "\n";

if ($getResult) {
    echo "   State type: " . ($getResult['type'] ?? 'MISSING') . "\n";
    echo "   State project: " . ($getResult['project'] ?? 'MISSING') . "\n";
}

echo "\n4. Testing isAwaitingGroupInput:\n";

$isAwaiting = $stateManager->isAwaitingGroupInput($testUserId);
echo "   isAwaitingGroupInput: " . ($isAwaiting ? "✅" : "❌") . "\n";

// Clean up
echo "\n5. Cleanup:\n";
$cleanResult = $stateManager->clearState($testUserId);
echo "   clearState result: " . ($cleanResult ? "✅" : "❌") . "\n";

echo "\n=== Deep Debug Complete ===\n";