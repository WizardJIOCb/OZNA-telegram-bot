<?php
// Reset webhook to use the fixed hook.php file
error_reporting(E_ALL);
ini_set('display_errors', 1);

$bot_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';

echo "=== Resetting Webhook to Fixed Version ===\n\n";

// First, delete the current webhook
echo "Step 1: Deleting current webhook...\n";
$delete_url = "https://api.telegram.org/bot{$bot_token}/deleteWebhook";
$delete_context = stream_context_create(['http' => ['method' => 'POST']]);
$delete_response = file_get_contents($delete_url, false, $delete_context);
$delete_result = json_decode($delete_response, true);

if ($delete_result && $delete_result['ok']) {
    echo "✓ Current webhook deleted successfully\n\n";
} else {
    echo "⚠ Issue deleting webhook: " . ($delete_result['description'] ?? 'Unknown error') . "\n\n";
}

// Wait a moment
sleep(2);

// Set the new webhook to our fixed hook.php
echo "Step 2: Setting new webhook to fixed hook.php...\n";
$webhook_url = 'https://ozna-bot.ozonetechpvtltd.com/tg/ozna/hook.php';
$set_url = "https://api.telegram.org/bot{$bot_token}/setWebhook";
$set_data = http_build_query(['url' => $webhook_url]);
$set_context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $set_data
    ]
]);

$set_response = file_get_contents($set_url, false, $set_context);
$set_result = json_decode($set_response, true);

if ($set_result && $set_result['ok']) {
    echo "✓ New webhook set successfully to: {$webhook_url}\n\n";
} else {
    echo "✗ Failed to set webhook: " . ($set_result['description'] ?? 'Unknown error') . "\n\n";
}

// Verify the webhook
echo "Step 3: Verifying webhook status...\n";
$info_url = "https://api.telegram.org/bot{$bot_token}/getWebhookInfo";
$info_response = file_get_contents($info_url);
$info_result = json_decode($info_response, true);

if ($info_result && $info_result['ok']) {
    $info = $info_result['result'];
    echo "Webhook URL: " . ($info['url'] ?? 'Not set') . "\n";
    echo "Pending updates: " . ($info['pending_update_count'] ?? 0) . "\n";
    
    if (isset($info['last_error_date'])) {
        echo "Last error: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
        echo "Error message: " . ($info['last_error_message'] ?? 'No message') . "\n";
    } else {
        echo "✓ No recent errors\n";
    }
} else {
    echo "✗ Could not get webhook info\n";
}

echo "\n=== Reset Complete ===\n";
echo "Please test the warranty equipment buttons now.\n";
?>