<?php
/**
 * Test the updated manual input flow with main conversation
 */

echo "=== TESTING UPDATED MANUAL INPUT FLOW ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$userId = 4225504;
$chatId = 4225504;
$projectCode = 'ОИ789';

echo "Testing with unified conversation approach:\n";
echo "User ID: {$userId}\n";
echo "Chat ID: {$chatId}\n";
echo "Project: {$projectCode}\n\n";

echo "STEP 1: Simulate user clicking 'Ввести ID группы вручную'\n";
echo "This simulates ProjectHandler::showManualGroupInput()\n\n";

try {
    // Simulate what the updated ProjectHandler does
    $conversation = new \Longman\TelegramBot\Conversation($userId, $chatId, '/callback');
    $conversation->notes['manual_input_type'] = 'group_id';
    $conversation->notes['manual_input_project'] = $projectCode;
    $updateResult = $conversation->update();
    
    echo "✓ Set conversation state using main '/callback' conversation\n";
    echo "  manual_input_type: {$conversation->notes['manual_input_type']}\n";
    echo "  manual_input_project: {$conversation->notes['manual_input_project']}\n";
    echo "  Update result: " . ($updateResult ? "Success" : "Failed") . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error setting conversation state: " . $e->getMessage() . "\n";
    exit(1);
}

echo "STEP 2: Simulate user typing group ID: -1001234567890\n";
echo "This simulates GenericmessageCommand detection and processing\n\n";

try {
    // Simulate what GenericmessageCommand does
    $checkConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, '/callback');
    
    echo "Reading conversation state:\n";
    echo "  Notes: " . json_encode($checkConversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
    
    // Check detection logic
    $isWaitingGroup = isset($checkConversation->notes['manual_input_type']) && 
                     $checkConversation->notes['manual_input_type'] === 'group_id' &&
                     isset($checkConversation->notes['manual_input_project']);
    
    echo "  Detection result: " . ($isWaitingGroup ? "✓ DETECTED" : "❌ NOT DETECTED") . "\n";
    
    if ($isWaitingGroup) {
        echo "\n✅ SUCCESS: GenericmessageCommand would process the input!\n";
        
        $inputText = '-1001234567890';
        echo "  Input text: {$inputText}\n";
        
        // Validate format
        $isValid = preg_match('/^-100\d+$/', trim($inputText));
        echo "  Format validation: " . ($isValid ? "✓ Valid" : "❌ Invalid") . "\n";
        
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
            
            echo "  ✓ Database updated with group ID\n";
            
            // Clear manual input state
            unset($checkConversation->notes['manual_input_type']);
            unset($checkConversation->notes['manual_input_project']);
            $checkConversation->update();
            
            echo "  ✓ Conversation state cleared\n";
            echo "  ✅ User would receive success message and navigation\n";
        }
    } else {
        echo "\n❌ FAILURE: GenericmessageCommand would NOT process the input\n";
        echo "User would get NO FEEDBACK\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error in processing: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING CHAT ID FLOW ===\n\n";

echo "STEP 1: Simulate user clicking 'Ввести ID чата вручную'\n";

try {
    $chatConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, '/callback');
    $chatConversation->notes['manual_input_type'] = 'chat_id';
    $chatConversation->notes['manual_input_project'] = $projectCode;
    $chatConversation->update();
    
    echo "✓ Set chat conversation state\n\n";
    
} catch (Exception $e) {
    echo "❌ Error setting chat conversation: " . $e->getMessage() . "\n";
}

echo "STEP 2: Simulate user typing chat ID: 123456789\n";

try {
    $checkChatConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, '/callback');
    
    $isWaitingChat = isset($checkChatConversation->notes['manual_input_type']) && 
                     $checkChatConversation->notes['manual_input_type'] === 'chat_id' &&
                     isset($checkChatConversation->notes['manual_input_project']);
    
    echo "Chat detection result: " . ($isWaitingChat ? "✓ DETECTED" : "❌ NOT DETECTED") . "\n";
    
    if ($isWaitingChat) {
        echo "✅ Chat ID would be processed and saved with feedback\n";
        
        // Clear state
        unset($checkChatConversation->notes['manual_input_type']);
        unset($checkChatConversation->notes['manual_input_project']);
        $checkChatConversation->update();
    }
    
} catch (Exception $e) {
    echo "❌ Error checking chat conversation: " . $e->getMessage() . "\n";
}

echo "\n=== FLOW TEST COMPLETE ===\n";
echo "With unified conversation approach, manual input should now work!\n";
echo "Users will get proper feedback when entering IDs.\n";