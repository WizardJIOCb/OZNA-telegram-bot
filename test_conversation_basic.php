<?php
/**
 * Basic test to check conversation functionality
 */

echo "=== BASIC CONVERSATION TEST ===\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

// Test user and chat data
$userId = 4225504; // Test user ID
$chatId = 4225504; // Test chat ID

echo "1. Testing basic conversation creation...\n";
echo "   User ID: {$userId}\n";
echo "   Chat ID: {$chatId}\n\n";

try {
    // Create a simple conversation with a unique command
    $command = 'test_conversation_' . time(); // Unique command to avoid conflicts
    echo "   Creating conversation with command: {$command}\n";
    
    $conversation = new \Longman\TelegramBot\Conversation($userId, $chatId, $command);
    
    echo "   Conversation created\n";
    echo "   Initial notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Set some test data
    $conversation->notes = [
        'test_data' => 'This is test data',
        'timestamp' => time(),
        'custom_text' => 'Ппппооыкрув'
    ];
    
    echo "   Setting test data\n";
    echo "   Notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Try to update the conversation
    echo "2. Testing conversation update...\n";
    $updateResult = $conversation->update();
    echo "   Update result: " . ($updateResult ? "SUCCESS" : "FAILED") . "\n\n";
    
    if ($updateResult) {
        // Try to read the conversation back
        echo "3. Testing conversation retrieval...\n";
        $readConversation = new \Longman\TelegramBot\Conversation($userId, $chatId, $command);
        echo "   Retrieved notes: " . json_encode($readConversation->notes, JSON_UNESCAPED_UNICODE) . "\n";
        
        $customText = $readConversation->notes['custom_text'] ?? 'NOT_FOUND';
        echo "   Retrieved custom text: {$customText}\n";
        
        if ($customText === 'Ппппооыкрув') {
            echo "   ✅ Conversation data correctly stored and retrieved\n";
        } else {
            echo "   ❌ Conversation data not correctly retrieved\n";
        }
    } else {
        echo "   ❌ Conversation update failed, cannot test retrieval\n";
    }
    
    // Clean up
    echo "\n4. Cleaning up...\n";
    $conversation->stop();
    echo "   Conversation stopped\n";
    
    echo "\n=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}