<?php
// Simple webhook fix script

echo "=== WEBHOOK DIAGNOSTIC AND FIX ===\n";

$bot_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$webhook_url = 'https://api.ladyzenko.pro/tg/ozna/hook.php';

echo "Bot token: " . substr($bot_token, 0, 10) . "...\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. Check current webhook status
echo "1. Checking current webhook status...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/getWebhookInfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && $data['ok']) {
        $result = $data['result'];
        echo "✓ Current webhook URL: " . ($result['url'] ?? 'Not set') . "\n";
        echo "✓ Pending updates: " . $result['pending_update_count'] . "\n";
        echo "✓ Last error: " . ($result['last_error_message'] ?? 'None') . "\n";
        echo "✓ Last error date: " . ($result['last_error_date'] ?? 'None') . "\n";
    } else {
        echo "❌ Failed to get webhook info: " . ($data['description'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ HTTP Error: $httpCode\n";
}

echo "\n2. Setting new webhook...\n";

// 2. Set the webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/setWebhook");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['url' => $webhook_url]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && $data['ok']) {
        echo "✓ Webhook set successfully!\n";
        echo "✓ Description: " . $data['description'] . "\n";
    } else {
        echo "❌ Failed to set webhook: " . ($data['description'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ HTTP Error: $httpCode\n";
}

echo "\n3. Verifying webhook...\n";

// 3. Verify the webhook was set
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/getWebhookInfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && $data['ok']) {
        $result = $data['result'];
        echo "✓ Verified webhook URL: " . ($result['url'] ?? 'Not set') . "\n";
        echo "✓ Pending updates: " . $result['pending_update_count'] . "\n";
        
        if ($result['url'] === $webhook_url) {
            echo "✅ WEBHOOK IS CORRECTLY SET!\n";
        } else {
            echo "❌ Webhook URL mismatch!\n";
        }
    }
}

echo "\n=== INSTRUCTIONS ===\n";
echo "If webhook is correctly set:\n";
echo "1. Click the 'Отдел ПНР' button in Telegram\n";
echo "2. Check the logs immediately: Get-Content c:\\ozna_bot\\telegram_bot_error.log -Tail 10\n";
echo "3. Look for today's date and 'Enhanced Debug Information'\n";
echo "\nIf still no logs appear, the issue is server-side configuration.\n";
?>