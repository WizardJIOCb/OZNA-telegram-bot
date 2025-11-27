<?php

// Simple script to set webhook
$bot_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$webhook_url = 'https://api.ladyzenko.pro/tg/ozna/hook.php';

// Use cURL to set webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/setWebhook?url={$webhook_url}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "Response from Telegram API:\n";
echo $response . "\n";

// Also get current webhook info
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/getWebhookInfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "\nCurrent Webhook Info:\n";
echo $response . "\n";