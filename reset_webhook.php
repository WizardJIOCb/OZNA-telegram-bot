<?php
/**
 * Clear webhook and reset bot
 */

echo "=== Resetting Webhook ===\n\n";

// Load config
$config = require __DIR__ . '/config.php';
$bot_token = $config['api_key'];

echo "Bot token loaded\n\n";

// Delete webhook
echo "1. Deleting webhook...\n";
$url = "https://api.telegram.org/bot{$bot_token}/deleteWebhook";
$response = file_get_contents($url);
$result = json_decode($response, true);

if ($result['ok']) {
    echo "✓ Webhook deleted\n\n";
} else {
    echo "✗ Failed: " . $result['description'] . "\n\n";
}

// Get webhook info
echo "2. Getting webhook info...\n";
$url = "https://api.telegram.org/bot{$bot_token}/getWebhookInfo";
$response = file_get_contents($url);
$result = json_decode($response, true);

if ($result['ok']) {
    echo "✓ Current webhook:\n";
    echo "  URL: " . ($result['result']['url'] ?: 'Not set') . "\n";
    echo "  Last error: " . ($result['result']['last_error_message'] ?: 'None') . "\n";
    echo "  Pending updates: " . ($result['result']['pending_update_count'] ?? 0) . "\n\n";
}

// Set webhook
echo "3. Setting webhook...\n";
$webhook_url = "https://api.ladyzenko.pro/tg/ozna/hook.php";
$url = "https://api.telegram.org/bot{$bot_token}/setWebhook?url=" . urlencode($webhook_url);
$response = file_get_contents($url);
$result = json_decode($response, true);

if ($result['ok']) {
    echo "✓ Webhook set to: $webhook_url\n\n";
} else {
    echo "✗ Failed: " . $result['description'] . "\n\n";
}

// Get updates to clear queue
echo "4. Clearing update queue...\n";
$url = "https://api.telegram.org/bot{$bot_token}/getUpdates?offset=-1";
$response = file_get_contents($url);
$result = json_decode($response, true);

if ($result['ok']) {
    echo "✓ Update queue cleared\n\n";
}

echo "=== Webhook Reset Complete ===\n";
echo "\nTry using the bot now!\n";
