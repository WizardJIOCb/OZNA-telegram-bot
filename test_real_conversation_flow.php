<?php
/**
 * Test the exact conversation flow that would happen in practice
 * Simulating user clicking manual input button then typing ID
 */

echo "=== TESTING REAL CONVERSATION FLOW ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$userId = 4225504;
$chatId = 4225504;
$projectCode = 'ОИ789';

echo "Simulating real user flow:\n";
echo "User ID: {$userId}\n";
echo "Chat ID: {$chatId}\n";
echo "Project: {$projectCode}\n\n";

echo "STEP 1: User clicks 'Ввести ID группы вручную' button\n";
echo "This would trigger: proj_ctrl_group_manual_{$projectCode}\n\n";

// Simulate what ProjectHandler::showManualGroupInput() does
try {
    $conversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_group_input');
    $conversation->notes = [
        'state' => 'waiting_group_id',
        'project_code' => $projectCode,
        'step' => 'group_id_input'
    ];
    $conversation->update();
    
    echo "✓ ProjectHandler sets conversation state\n";
    echo "  State: {$conversation->notes['state']}\n";
    echo "  Project: {$conversation->notes['project_code']}\n\n";
    
} catch (Exception $e) {
    echo "❌ Error in step 1: " . $e->getMessage() . "\n";
    exit(1);
}

echo "STEP 2: User types group ID: -1001234567890\n";
echo "This should trigger GenericmessageCommand\n\n";

// Simulate what GenericmessageCommand::isAwaitingManualGroupInput() should do
try {
    // This is how the detection should work in GenericmessageCommand
    $checkConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_group_input');
    $isWaiting = isset($checkConversation->notes['state']) && $checkConversation->notes['state'] === 'waiting_group_id';
    
    echo "Detection result: " . ($isWaiting ? "✓ DETECTED" : "❌ NOT DETECTED") . "\n";
    
    if ($isWaiting) {
        echo "✓ GenericmessageCommand would call handleManualGroupInput()\n";
        
        // Simulate the validation and saving process
        $inputText = '-1001234567890';
        echo "  Input: {$inputText}\n";
        
        // Validate format
        $isValid = preg_match('/^-100\d+$/', trim($inputText));
        echo "  Validation: " . ($isValid ? "✓ Valid format" : "❌ Invalid format") . "\n";
        
        if ($isValid) {
            // Simulate database update
            $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
            $userData = $databaseService->getUserData($userId);
            $controlProjects = $userData['control_projects'] ?? [];
            
            if (!isset($controlProjects[$projectCode])) {
                $controlProjects[$projectCode] = [];
            }
            
            $controlProjects[$projectCode]['group_id'] = trim($inputText);
            $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
            
            $userData['control_projects'] = $controlProjects;
            $databaseService->updateUserData($userId, $userData);
            
            echo "  ✓ Database updated\n";
            
            // Cleanup conversation
            $conversation->stop();
            echo "  ✓ Conversation state cleared\n";
            
            echo "  ✓ Success message would be sent\n";
            echo "  ✓ Navigation back to project settings\n";
        }
    } else {
        echo "❌ GenericmessageCommand would NOT process the input\n";
        echo "This means the user gets NO FEEDBACK\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error in step 2: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING CHAT ID FLOW ===\n\n";

echo "STEP 1: User clicks 'Ввести ID чата вручную' button\n";

try {
    $chatConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_chat_input');
    $chatConversation->notes = [
        'state' => 'waiting_chat_id',
        'project_code' => $projectCode,
        'step' => 'chat_id_input'
    ];
    $chatConversation->update();
    
    echo "✓ ProjectHandler sets chat conversation state\n\n";
    
} catch (Exception $e) {
    echo "❌ Error setting chat conversation: " . $e->getMessage() . "\n";
}

echo "STEP 2: User types chat ID: 123456789\n";

try {
    $checkChatConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_chat_input');
    $isWaitingChat = isset($checkChatConversation->notes['state']) && $checkChatConversation->notes['state'] === 'waiting_chat_id';
    
    echo "Chat detection result: " . ($isWaitingChat ? "✓ DETECTED" : "❌ NOT DETECTED") . "\n";
    
    if ($isWaitingChat) {
        echo "✓ Chat ID would be processed and saved\n";
        $chatConversation->stop();
    }
    
} catch (Exception $e) {
    echo "❌ Error checking chat conversation: " . $e->getMessage() . "\n";
}

echo "\n=== FLOW TEST COMPLETE ===\n";
echo "If detection is working, users should get feedback.\n";
echo "If detection fails, users get no response to their input.\n";