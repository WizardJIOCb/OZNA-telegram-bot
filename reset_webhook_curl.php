<?php
// Reset webhook using curl (if available)
$bot_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$webhook_url = 'https://ozna-bot.ozonetechpvtltd.com/tg/ozna/hook.php';

echo "=== Resetting Webhook Using cURL ===\n\n";

// Check if cURL is available
if (!function_exists('curl_init')) {
    echo "❌ cURL is not available. The webhook needs to be reset manually.\n";
    echo "Please use a web browser or another tool to:\n";
    echo "1. Delete webhook: https://api.telegram.org/bot{$bot_token}/deleteWebhook\n";
    echo "2. Set webhook: https://api.telegram.org/bot{$bot_token}/setWebhook?url=" . urlencode($webhook_url) . "\n";
    exit;
}

// Delete current webhook
echo "Step 1: Deleting current webhook...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/deleteWebhook");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

if ($result && $result['ok']) {
    echo "✓ Current webhook deleted successfully\n\n";
} else {
    echo "⚠ Issue deleting webhook: " . ($result['description'] ?? 'Unknown error') . "\n\n";
}

// Wait a moment
sleep(2);

// Set new webhook
echo "Step 2: Setting new webhook to fixed hook.php...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/setWebhook");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['url' => $webhook_url]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

if ($result && $result['ok']) {
    echo "✓ New webhook set successfully to: {$webhook_url}\n\n";
} else {
    echo "✗ Failed to set webhook: " . ($result['description'] ?? 'Unknown error') . "\n\n";
}

// Verify webhook
echo "Step 3: Verifying webhook status...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/getWebhookInfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

if ($result && $result['ok']) {
    $info = $result['result'];
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
echo "The webhook has been reset to point to the fixed hook.php file.\n";
echo "Please test the warranty equipment buttons now.\n";
?>