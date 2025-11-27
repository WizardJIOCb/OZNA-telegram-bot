<?php
/**
 * Deep diagnostic of conversation flow for manual input
 * Testing the actual conversation state management
 */

echo "=== DEEP DIAGNOSTIC: Conversation Flow ===\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

echo "1. Testing conversation state detection...\n";

// Test user and project data
$userId = 4225504;
$chatId = 4225504;
$projectCode = 'ОИ789';

try {
    // Test conversation creation for group input
    echo "   Creating group input conversation...\n";
    $groupConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_group_input');
    
    // Set the state as ProjectHandler would
    $groupConversation->notes = [
        'state' => 'waiting_group_id',
        'project_code' => $projectCode,
        'step' => 'group_id_input'
    ];
    $groupConversation->update();
    
    echo "   ✓ Group conversation created with state: {$groupConversation->notes['state']}\n";
    
    // Test conversation creation for chat input
    echo "   Creating chat input conversation...\n";
    $chatConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_chat_input');
    
    // Set the state as ProjectHandler would
    $chatConversation->notes = [
        'state' => 'waiting_chat_id',
        'project_code' => $projectCode,
        'step' => 'chat_id_input'
    ];
    $chatConversation->update();
    
    echo "   ✓ Chat conversation created with state: {$chatConversation->notes['state']}\n";
    
} catch (Exception $e) {
    echo "   ❌ Error creating conversations: " . $e->getMessage() . "\n";
}

echo "\n2. Testing state detection logic...\n";

// Simulate the detection logic from GenericmessageCommand
try {
    // Test group input detection
    $groupConversationCheck = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_group_input');
    $isWaitingGroup = isset($groupConversationCheck->notes['state']) && $groupConversationCheck->notes['state'] === 'waiting_group_id';
    echo "   Group input waiting state: " . ($isWaitingGroup ? "✓ Detected" : "❌ Not detected") . "\n";
    
    // Test chat input detection
    $chatConversationCheck = new \Longman\TelegramBot\Conversation($userId, $chatId, 'manual_chat_input');
    $isWaitingChat = isset($chatConversationCheck->notes['state']) && $chatConversationCheck->notes['state'] === 'waiting_chat_id';
    echo "   Chat input waiting state: " . ($isWaitingChat ? "✓ Detected" : "❌ Not detected") . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Error checking conversation states: " . $e->getMessage() . "\n";
}

echo "\n3. Testing input validation...\n";

// Test group ID validation
$testGroupId = '-1001234567890';
$isValidGroup = preg_match('/^-100\d+$/', $testGroupId);
echo "   Group ID '{$testGroupId}' validation: " . ($isValidGroup ? "✓ Valid" : "❌ Invalid") . "\n";

// Test chat ID validation
$testChatId = '123456789';
$isValidChat = preg_match('/^-?\d+$/', $testChatId);
echo "   Chat ID '{$testChatId}' validation: " . ($isValidChat ? "✓ Valid" : "❌ Invalid") . "\n";

echo "\n4. Testing database update simulation...\n";

try {
    // Check if DatabaseService exists
    if (class_exists('\\Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\DatabaseService')) {
        echo "   ✓ DatabaseService class available\n";
        
        $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        echo "   ✓ DatabaseService instance created\n";
        
        // Get user data (this will test database connectivity)
        $userData = $databaseService->getUserData($userId);
        echo "   ✓ User data retrieved\n";
        
        // Simulate updating control projects
        $controlProjects = $userData['control_projects'] ?? [];
        echo "   Current control projects: " . count($controlProjects) . "\n";
        
    } else {
        echo "   ❌ DatabaseService class not available\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n5. Checking GenericmessageCommand routing...\n";

// Check if GenericmessageCommand is properly configured
$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
$content = file_get_contents($genericFile);

// Check command name
if (preg_match('/protected \$name = [\'"]([^\'"]+)[\'"]/', $content, $matches)) {
    $commandName = $matches[1];
    echo "   GenericmessageCommand name: '{$commandName}'\n";
    
    if ($commandName === 'genericmessage') {
        echo "   ✓ Correct command name for text message handling\n";
    } else {
        echo "   ⚠️ Command name may cause routing issues\n";
    }
} else {
    echo "   ❌ Could not determine command name\n";
}

// Check if manual input handlers are called in execute method
if (strpos($content, 'isAwaitingManualGroupInput') !== false && 
    strpos($content, 'isAwaitingManualChatInput') !== false) {
    echo "   ✓ Manual input checks present in execute method\n";
} else {
    echo "   ❌ Manual input checks missing from execute method\n";
}

echo "\n6. Clean up test conversations...\n";
try {
    if (isset($groupConversation)) {
        $groupConversation->stop();
        echo "   ✓ Group conversation cleaned up\n";
    }
    if (isset($chatConversation)) {
        $chatConversation->stop();
        echo "   ✓ Chat conversation cleaned up\n";
    }
} catch (Exception $e) {
    echo "   ⚠️ Cleanup warning: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "If conversations are created but detection fails, the issue may be:\n";
echo "• Different chat ID used in conversation vs detection\n";
echo "• Conversation command name mismatch\n";
echo "• GenericmessageCommand not being called for text messages\n";
echo "• Conversation database not accessible\n";