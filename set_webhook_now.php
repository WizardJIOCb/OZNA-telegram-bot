<?php
echo "=== УСТАНОВКА WEBHOOK ===\n\n";

$token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$webhookUrl = 'https://ozna.ruc-nw.ru/hook.php';

echo "🔧 Устанавливаю webhook: $webhookUrl\n\n";

$ch = curl_init("https://api.telegram.org/bot{$token}/setWebhook");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'url' => $webhookUrl,
    'max_connections' => 40,
    'drop_pending_updates' => false
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";

$data = json_decode($resp, true);

if ($data && $data['ok']) {
    echo "✅ WEBHOOK УСТАНОВЛЕН!\n\n";
    
    // Проверка
    $ch = curl_init("https://api.telegram.org/bot{$token}/getWebhookInfo");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($ch);
    curl_close($ch);
    
    $info = json_decode($resp, true);
    if ($info && $info['ok']) {
        echo "📍 Текущий webhook: " . $info['result']['url'] . "\n";
        echo "📊 Pending updates: " . ($info['result']['pending_update_count'] ?? 0) . "\n";
    }
} else {
    echo "❌ ОШИБКА: " . ($data['description'] ?? 'Unknown') . "\n";
    echo "Response: $resp\n";
}
