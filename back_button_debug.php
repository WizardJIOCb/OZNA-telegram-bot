<?php
// Simple debug script to test back button functionality with direct Telegram API calls

// Bot configuration from config.php
$api_key = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$chat_id = '5261465966'; // Your chat ID from the logs

/**
 * Send a message to Telegram using direct HTTP request
 */
function sendTelegramMessage($api_key, $chat_id, $text) {
    $url = "https://api.telegram.org/bot{$api_key}/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result;
}

/**
 * Send debug message in the format: "🔍 DEBUG: тип='value'"
 */
function sendDebugMessage($api_key, $chat_id, $type, $value) {
    $message = "🔍 DEBUG: {$type}='{$value}'";
    return sendTelegramMessage($api_key, $chat_id, $message);
}

// Test messages for back button debugging
echo "=== Back Button Debug Test ===\n";

$debug_messages = [
    ['type' => 'back_button', 'value' => 'pressed'],
    ['type' => 'callback_data', 'value' => 'Табель_ШМ_Back'],
    ['type' => 'handler_check', 'value' => 'TabelHandler::canHandle'],
    ['type' => 'routing', 'value' => 'to handleBackToSupervision'],
    ['type' => 'method_call', 'value' => 'handleBackToSupervision executed'],
    ['type' => 'keyboard_shown', 'value' => 'supervision menu'],
    ['type' => 'status', 'value' => 'complete'],
];

foreach ($debug_messages as $msg) {
    echo "Sending: {$msg['type']}='{$msg['value']}'... ";
    $response = sendDebugMessage($api_key, $chat_id, $msg['type'], $msg['value']);
    
    // Check if the request was successful
    $response_data = json_decode($response, true);
    if ($response_data && isset($response_data['ok']) && $response_data['ok']) {
        echo "✓ Success\n";
    } else {
        echo "✗ Failed\n";
        if ($response_data && isset($response_data['description'])) {
            echo "  Error: " . $response_data['description'] . "\n";
        }
    }
    
    // Small delay to avoid rate limiting
    sleep(1);
}

echo "\n=== Debug Test Complete ===\n";
echo "Check your Telegram chat for the debug messages.\n";
?>