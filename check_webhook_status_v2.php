<?php
// Alternative webhook check without curl
$botToken = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';

echo "Checking webhook status using file_get_contents...\n\n";

// Get webhook info
$url = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json',
        'ignore_errors' => true,
    ],
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Failed to connect to Telegram API\n";
    echo "Error: " . error_get_last()['message'] . "\n";
    exit(1);
}

$data = json_decode($response, true);

if ($data && isset($data['result'])) {
    $info = $data['result'];
    
    echo "📌 Webhook URL: " . ($info['url'] ?? 'Not set') . "\n";
    echo "📌 Pending updates: " . ($info['pending_update_count'] ?? 0) . "\n";
    echo "📌 Last error: " . ($info['last_error_message'] ?? 'None') . "\n";
    
    if (isset($info['last_error_date'])) {
        echo "📌 Last error date: " . date('Y-m-d H:i:s', $info['last_error_date']) . " (" . $info['last_error_date'] . ")\n";
    }
    
    echo "📌 Max connections: " . ($info['max_connections'] ?? 'Default') . "\n";
    
    if (isset($info['last_synchronization_error_date'])) {
        echo "⚠️ Last sync error: " . date('Y-m-d H:i:s', $info['last_synchronization_error_date']) . "\n";
    }
    
    if (($info['pending_update_count'] ?? 0) > 0) {
        echo "\n⚠️ WARNING: There are {$info['pending_update_count']} pending updates!\n";
        echo "This means Telegram is trying to send updates but the webhook is not responding.\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
    echo "Full response:\n";
    echo json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "\n";
} else {
    echo "❌ Failed to get webhook info\n";
    echo "Response: {$response}\n";
}
