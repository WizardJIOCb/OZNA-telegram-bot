<?php
// Script to send debug messages to Telegram in the format: "🔍 DEBUG: тип='value'"

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

// Load configuration
$config = require __DIR__ . '/config.php';

// Bot configuration
$api_key = $config['api_key'];
$bot_username = $config['bot_username'];

// Initialize the Telegram bot
$telegram = new Telegram($api_key, $bot_username);

// Chat ID to send debug messages to (this should be your personal chat ID)
// For testing purposes, we'll use a placeholder - you'll need to replace this with your actual chat ID
$chat_id = '5261465966'; // This appears to be your chat ID based on the logs

/**
 * Send a debug message to Telegram in the requested format
 */
function sendDebugMessage($chat_id, $type, $value, $telegram) {
    $message = "🔍 DEBUG: {$type}='{$value}'";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
    ];
    
    return Request::sendMessage($data);
}

/**
 * Send multiple debug messages to test different scenarios
 */
function sendTestDebugMessages($chat_id, $telegram) {
    echo "Sending debug messages to Telegram...\n";
    
    // Test messages
    $tests = [
        ['type' => 'тип', 'value' => ''],
        ['type' => 'callback', 'value' => 'Табель_ШМ_Back'],
        ['type' => 'handler', 'value' => 'TabelHandler'],
        ['type' => 'method', 'value' => 'handleBackToSupervision'],
        ['type' => 'status', 'value' => 'success'],
        ['type' => 'error', 'value' => 'button_not_responding'],
        ['type' => 'category', 'value' => 'Шеф-монтажные работы'],
        ['type' => 'action', 'value' => 'back_button_pressed'],
    ];
    
    foreach ($tests as $test) {
        $result = sendDebugMessage($chat_id, $test['type'], $test['value'], $telegram);
        
        if ($result->isOk()) {
            echo "✓ Sent debug message: {$test['type']}='{$test['value']}'\n";
        } else {
            echo "✗ Failed to send debug message: " . $result->getDescription() . "\n";
        }
        
        // Small delay to avoid rate limiting
        sleep(1);
    }
    
    echo "Debug messages sent!\n";
}

// Run the test
echo "=== Telegram Debug Message Test ===\n";
echo "Chat ID: {$chat_id}\n";
echo "Bot Username: {$bot_username}\n\n";

try {
    sendTestDebugMessages($chat_id, $telegram);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure the bot is properly configured and the chat ID is correct.\n";
}

echo "\n=== Test Complete ===\n";
?>