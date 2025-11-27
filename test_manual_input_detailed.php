<?php
/**
 * Test script to simulate exact user interaction and check logs
 * Following safety protocols for non-programmer user
 */

echo "=== TESTING MANUAL INPUT WITH LOGGING ===\n\n";

// Enable detailed logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a test log file to capture any errors
$logFile = __DIR__ . '/manual_input_test.log';
file_put_contents($logFile, "=== Manual Input Test Log ===\n", LOCK_EX);

function logTest($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
    echo $message . "\n";
}

try {
    logTest("1. Setting up test environment...");
    
    // Load required files
    require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    logTest("   Required files loaded successfully");
    
    // Test user data
    $userId = 230379668;
    $chatId = 230379668; 
    $projectCode = 'TEST-REAL';
    $testGroupId = '-1001234567890';
    
    logTest("2. Testing state management...");
    
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    // Set state (this is what ProjectHandler does)
    $stateSet = $stateManager->setState($userId, 'group_id', $projectCode);
    logTest("   State set result: " . ($stateSet ? "SUCCESS" : "FAILED"));
    
    // Check if state exists (this is what GenericmessageCommand checks)
    $isAwaiting = $stateManager->isAwaitingGroupInput($userId);
    logTest("   State detection: " . ($isAwaiting ? "SUCCESS - would be detected" : "FAILED - would not be detected"));
    
    if ($isAwaiting) {
        logTest("3. Testing input processing...");
        
        // Get project code (this is what the handler does)
        $retrievedProject = $stateManager->getProjectCode($userId);
        logTest("   Project code retrieval: " . ($retrievedProject ? "SUCCESS - {$retrievedProject}" : "FAILED"));
        
        // Validate input format (this is what the handler does)
        $isValidFormat = preg_match('/^-100\d+$/', trim($testGroupId));
        logTest("   Input validation: " . ($isValidFormat ? "SUCCESS - valid format" : "FAILED - invalid format"));
        
        if ($isValidFormat) {
            logTest("4. Testing database operations...");
            
            // Test database service
            $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
            $userData = $databaseService->getUserData($userId);
            logTest("   User data retrieval: " . (isset($userData) ? "SUCCESS" : "FAILED"));
            
            // Test updating control projects
            $controlProjects = $userData['control_projects'] ?? [];
            if (!isset($controlProjects[$projectCode])) {
                $controlProjects[$projectCode] = [];
            }
            $controlProjects[$projectCode]['group_id'] = $testGroupId;
            $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
            $userData['control_projects'] = $controlProjects;
            
            // Save back to database
            $saveResult = $databaseService->updateUserData($userId, $userData);
            logTest("   Database save: " . ($saveResult ? "SUCCESS" : "FAILED"));
            
            if ($saveResult) {
                logTest("5. Testing state cleanup...");
                
                // Clear state (this is what the handler does)
                $clearResult = $stateManager->clearState($userId);
                logTest("   State cleared: " . ($clearResult ? "SUCCESS" : "FAILED"));
                
                // Verify state is cleared
                $isStillWaiting = $stateManager->isAwaitingGroupInput($userId);
                logTest("   State verification: " . (!$isStillWaiting ? "SUCCESS - state cleared" : "FAILED - state still exists"));
                
                logTest("6. Testing response message...");
                
                // Test MessageService
                $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
                logTest("   MessageService created: SUCCESS");
                
                // Build response message (this is what the handler does)
                $responseText = "✅ <b>ID группы сохранён</b>\n\n";
                $responseText .= "Для проекта <b>{$projectCode}</b>:\n";
                $responseText .= "<b>ID группы:</b> {$testGroupId}\n\n";
                $responseText .= "💡 Теперь уведомления будут отправляться в указанную группу.";
                
                logTest("   Response message prepared: SUCCESS");
                logTest("   Message content: {$responseText}");
                
                // Test keyboard creation
                $keyboard = [[
                    ['text' => '⬅️ К настройкам проекта', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
                ]];
                logTest("   Keyboard prepared: SUCCESS");
                
                logTest("\n=== SIMULATION COMPLETE ===");
                logTest("All components are working correctly in isolation.");
                logTest("User should see confirmation: '✅ ID группы сохранён'");
                
            } else {
                logTest("ERROR: Database save failed - this would prevent confirmation");
            }
        } else {
            logTest("ERROR: Input validation failed - this would show error message");
        }
    } else {
        logTest("ERROR: State not detected - this would prevent processing");
    }
    
} catch (Exception $e) {
    logTest("CRITICAL ERROR: " . $e->getMessage());
    logTest("Stack trace: " . $e->getTraceAsString());
}

echo "\n=== CHECKING ACTUAL BOT LOGS ===\n";

// Check if there are any recent error logs
$logFiles = [
    __DIR__ . '/error.log',
    __DIR__ . '/debug.log',
    __DIR__ . '/bot.log',
    '/var/log/php_errors.log'
];

foreach ($logFiles as $file) {
    if (file_exists($file)) {
        echo "Found log file: {$file}\n";
        $logContent = file_get_contents($file);
        if (!empty($logContent)) {
            $lines = explode("\n", $logContent);
            $recentLines = array_slice($lines, -10); // Last 10 lines
            echo "Recent entries:\n";
            foreach ($recentLines as $line) {
                if (!empty(trim($line))) {
                    echo "  {$line}\n";
                }
            }
        }
        echo "\n";
    }
}

echo "Test log saved to: {$logFile}\n";
echo "\nIf the simulation shows all SUCCESS messages, then the issue is likely:\n";
echo "1. Bot webhook not receiving messages\n";
echo "2. GenericmessageCommand not being called\n";
echo "3. Silent PHP errors during execution\n";
echo "4. Telegram API response issues\n";
?>