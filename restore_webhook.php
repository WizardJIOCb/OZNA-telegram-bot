<?php
echo "=== ВОССТАНОВЛЕНИЕ WEBHOOK ===\n\n";

$token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$webhookUrl = 'https://api.ladyzenko.pro/tg/ozna/hook.php';

echo "🔧 Устанавливаю правильный webhook: $webhookUrl\n\n";

$ch = curl_init("https://api.telegram.org/bot{$token}/setWebhook");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'url' => $webhookUrl,
    'max_connections' => 40
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";

$data = json_decode($resp, true);

if ($data && $data['ok']) {
    echo "✅ WEBHOOK УСТАНОВЛЕН!\n\n";
} else {
    echo "❌ ОШИБКА: " . ($data['description'] ?? 'Unknown') . "\n\n";
    exit(1);
}

// Проверка
echo "🔍 Проверка webhook...\n";
$ch = curl_init("https://api.telegram.org/bot{$token}/getWebhookInfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$resp = curl_exec($ch);
curl_close($ch);

$info = json_decode($resp, true);
if ($info && $info['ok']) {
    $result = $info['result'];
    echo "   URL: " . $result['url'] . "\n";
    echo "   Pending: " . ($result['pending_update_count'] ?? 0) . "\n";
    
    if (isset($result['last_error_message'])) {
        echo "   ⚠️  Last error: " . $result['last_error_message'] . "\n";
    } else {
        echo "   ✅ Нет ошибок\n";
    }
}

echo "\n════════════════════════════════════════\n";
echo "✅ ГОТОВО! Бот должен работать!\n";
echo "   Проверьте в Telegram: /start\n";
echo "════════════════════════════════════════\n";
