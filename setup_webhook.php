<?php
/**
 * Установка webhook с правильными параметрами
 */

echo "=== УСТАНОВКА WEBHOOK ===\n\n";

// Load config
$config = require __DIR__ . '/config.php';
$bot_token = $config['api_key'];
$webhook_url = "https://ozna.ruc-nw.ru/hook.php";

echo "Bot Token: " . substr($bot_token, 0, 10) . "...\n";
echo "Webhook URL: $webhook_url\n\n";

// 1. Delete old webhook
echo "1️⃣  Удаление старого webhook...\n";
$ch = curl_init("https://api.telegram.org/bot{$bot_token}/deleteWebhook");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
if ($result['ok']) {
    echo "   ✅ Webhook удален\n\n";
} else {
    echo "   ⚠️  " . ($result['description'] ?? 'Unknown error') . "\n\n";
}

// 2. Set new webhook with parameters
echo "2️⃣  Установка нового webhook...\n";

$postData = [
    'url' => $webhook_url,
    'max_connections' => 40,
    'allowed_updates' => json_encode(['message', 'callback_query'])
];

$ch = curl_init("https://api.telegram.org/bot{$bot_token}/setWebhook");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";

$result = json_decode($response, true);
if ($result && $result['ok']) {
    echo "   ✅ Webhook установлен!\n\n";
} else {
    echo "   ❌ ОШИБКА: " . ($result['description'] ?? 'Unknown error') . "\n";
    echo "   Response: " . substr($response, 0, 200) . "\n\n";
}

// 3. Verify webhook
echo "3️⃣  Проверка webhook...\n";
$ch = curl_init("https://api.telegram.org/bot{$bot_token}/getWebhookInfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
if ($result && $result['ok']) {
    $info = $result['result'];
    echo "   URL: " . ($info['url'] ?? 'Not set') . "\n";
    echo "   Pending updates: " . ($info['pending_update_count'] ?? 0) . "\n";
    
    if (isset($info['last_error_message'])) {
        echo "   ⚠️  Last error: " . $info['last_error_message'] . "\n";
        echo "   Error date: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
    } else {
        echo "   ✅ Нет ошибок\n";
    }
}

echo "\n════════════════════════════════════════════════════════\n";
echo "✅ ГОТОВО! Проверьте бота командой /start\n";
echo "════════════════════════════════════════════════════════\n";
