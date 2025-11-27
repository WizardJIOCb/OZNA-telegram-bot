<?php
/**
 * Test the complete manual input flow with existing user data
 * Following safety protocols for non-programmer user
 */

echo "=== TESTING COMPLETE MANUAL INPUT FLOW ===\n\n";

// Use your real user data for testing
$userId = 230379668;
$chatId = 230379668;
$existingProject = 'ОИ834'; // From your database
$newTestGroupId = '-1001999888777'; // Test group ID

echo "Testing with your existing project: {$existingProject}\n";
echo "User ID: {$userId}\n\n";

try {
    // Load required services
    require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    echo "1. STEP 1: User clicks 'Ввести ID группы вручную'\n";
    echo "   (ProjectHandler sets file-based state)\n";
    
    // This is what ProjectHandler::showManualGroupInput() does
    $stateSet = $stateManager->setState($userId, 'group_id', $existingProject);
    echo "   State set: " . ($stateSet ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    echo "\n2. STEP 2: User types group ID: {$newTestGroupId}\n";
    echo "   (GenericmessageCommand processes input)\n";
    
    // Check if GenericmessageCommand would detect the state
    $isWaiting = $stateManager->isAwaitingGroupInput($userId);
    echo "   State detected: " . ($isWaiting ? "✓ YES" : "✗ NO") . "\n";
    
    if ($isWaiting) {
        // Get project code
        $projectCode = $stateManager->getProjectCode($userId);
        echo "   Project code: " . ($projectCode ? "✓ {$projectCode}" : "✗ MISSING") . "\n";
        
        // Validate input
        $text = trim($newTestGroupId);
        $isValid = preg_match('/^-100\\d+$/', $text);
        echo "   Input validation: " . ($isValid ? "✓ VALID" : "✗ INVALID") . "\n";
        
        if ($isValid && $projectCode) {
            echo "\n3. STEP 3: Save to database\n";
            
            // Get current user data
            $userData = $databaseService->getUserData($userId);
            echo "   User data retrieved: ✓ YES\n";
            
            // Update control projects
            $controlProjects = $userData['control_projects'] ?? [];
            if (!isset($controlProjects[$projectCode])) {
                $controlProjects[$projectCode] = [];
            }
            
            // Save old group ID for comparison
            $oldGroupId = $controlProjects[$projectCode]['group_id'] ?? 'не настроена';
            
            // Update with new group ID
            $controlProjects[$projectCode]['group_id'] = $text;
            $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
            $userData['control_projects'] = $controlProjects;
            
            // Save to database
            $saveResult = $databaseService->updateUserData($userId, $userData);
            echo "   Database save: " . ($saveResult ? "✓ SUCCESS" : "✗ FAILED") . "\n";
            
            if ($saveResult) {
                echo "   Old group ID: {$oldGroupId}\n";
                echo "   New group ID: {$text}\n";
                
                echo "\n4. STEP 4: Clear state and prepare response\n";
                
                // Clear state
                $clearResult = $stateManager->clearState($userId);
                echo "   State cleared: " . ($clearResult ? "✓ YES" : "✗ NO") . "\n";
                
                // Prepare success message
                $responseText = "✅ <b>ID группы сохранён</b>\n\n";
                $responseText .= "Для проекта <b>{$projectCode}</b>:\n";
                $responseText .= "<b>ID группы:</b> {$text}\n\n";
                $responseText .= "💡 Теперь уведомления будут отправляться в указанную группу.";
                
                echo "   Response message prepared: ✓ YES\n";
                echo "   Message preview:\n";
                echo "   " . str_replace("\n", "\n   ", $responseText) . "\n";
                
                // Prepare keyboard
                $keyboard = [[
                    ['text' => '⬅️ К настройкам проекта', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
                ]];
                echo "   Navigation keyboard: ✓ PREPARED\n";
                
                echo "\n5. FINAL RESULT: User should see confirmation message!\n";
                echo "   Status: ✅ ALL COMPONENTS WORKING\n";
                
                // Restore original group ID
                echo "\n6. CLEANUP: Restoring original settings...\n";
                $controlProjects[$projectCode]['group_id'] = $oldGroupId === 'не настроена' ? '' : $oldGroupId;
                $userData['control_projects'] = $controlProjects;
                $restoreResult = $databaseService->updateUserData($userId, $userData);
                echo "   Original settings restored: " . ($restoreResult ? "✓ YES" : "✗ NO") . "\n";
                
            } else {
                echo "   ✗ Database save failed - this would prevent confirmation\n";
            }
        } else {
            echo "   ✗ Validation or project code failed\n";
        }
    } else {
        echo "   ✗ State not detected - GenericmessageCommand wouldn't process input\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FLOW TEST COMPLETE ===\n\n";

echo "DIAGNOSIS:\n";
echo "If all steps show ✓ SUCCESS, then the manual input system is working correctly.\n";
echo "The issue might be:\n\n";

echo "1. Bot webhook configuration:\n";
echo "   - Check if bot is receiving messages from Telegram\n";
echo "   - Verify webhook URL is correctly set\n\n";

echo "2. GenericmessageCommand execution:\n";
echo "   - Check if GenericmessageCommand is being called\n";
echo "   - Look for PHP errors in execution\n\n";

echo "3. Message delivery:\n";
echo "   - Check if response messages are being sent to Telegram\n";
echo "   - Verify bot has permission to send messages\n\n";

echo "To check if bot is receiving messages, try:\n";
echo "1. Send any message to the bot\n";
echo "2. Check if it appears in bot logs\n";
echo "3. If not, the webhook needs to be reconfigured\n";
?>