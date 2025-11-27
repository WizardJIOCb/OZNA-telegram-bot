<?php
echo "=== ДИАГНОСТИКА WEBHOOK ===\n\n";

$token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';

$ch = curl_init("https://api.telegram.org/bot{$token}/getWebhookInfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$resp = curl_exec($ch);
curl_close($ch);

$data = json_decode($resp, true);

if ($data && $data['ok']) {
    $info = $data['result'];
    
    echo "📍 URL: " . ($info['url'] ?: '❌ НЕ УСТАНОВЛЕН') . "\n";
    echo "📊 Pending updates: " . ($info['pending_update_count'] ?? 0) . "\n";
    echo "🔄 Max connections: " . ($info['max_connections'] ?? 'N/A') . "\n";
    
    if (isset($info['last_error_message'])) {
        echo "\n❌ ПОСЛЕДНЯЯ ОШИБКА:\n";
        echo "   Сообщение: " . $info['last_error_message'] . "\n";
        echo "   Дата: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
    } else {
        echo "\n✅ Ошибок нет\n";
    }
    
    if (isset($info['last_synchronization_error_date'])) {
        echo "\n⚠️  Last sync error: " . date('Y-m-d H:i:s', $info['last_synchronization_error_date']) . "\n";
    }
} else {
    echo "❌ Ошибка получения webhook info\n";
}
