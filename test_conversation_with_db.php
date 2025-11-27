<?php
/**
 * Test conversation functionality with proper database initialization
 */

echo "=== CONVERSATION TEST WITH DATABASE INITIALIZATION ===\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    // Initialize database connection like in hook.php
    echo "1. Initializing database connection...\n";
    
    $bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
    $bot_username = 'OznaWork_bot';
    
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    // Enable MySQL
    $mysql_credentials = [
        'host'     => '82.146.42.213',
        'port'     => 3306,
        'user'     => 'WizardJIOCb',
        'password' => '7qQ#28051961',
        'database' => 'tg_bot',
    ];
    
    $pdo = $telegram->enableMySql($mysql_credentials);
    
    if ($pdo) {
        echo "   ✅ Database connection initialized successfully\n\n";
    } else {
        echo "   ❌ Database connection initialization failed\n\n";
        exit(1);
    }
    
    // Test user and chat data
    $userId = 4225504; // Test user ID
    $chatId = 4225504; // Test chat ID
    
    echo "2. Testing conversation functionality...\n";
    echo "   User ID: {$userId}\n";
    echo "   Chat ID: {$chatId}\n\n";
    
    // Create a simple conversation with a unique command
    $command = 'test_conversation_db_' . time(); // Unique command to avoid conflicts
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
    echo "3. Testing conversation update...\n";
    $updateResult = $conversation->update();
    echo "   Update result: " . ($updateResult ? "SUCCESS" : "FAILED") . "\n\n";
    
    if ($updateResult) {
        // Try to read the conversation back
        echo "4. Testing conversation retrieval...\n";
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
    echo "\n5. Cleaning up...\n";
    $conversation->stop();
    echo "   Conversation stopped\n";
    
    echo "\n=== TEST COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}