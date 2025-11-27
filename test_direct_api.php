<?php
// Simple direct approach to test Telegram API
$bot_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$chat_id = 230379668; // Your ID for testing

$message = "🧪 Прямой тест API - если видите это, то все работает!";

// Use simple curl
$url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
$data = [
    'chat_id' => $chat_id,
    'text' => $message
];

echo "🧪 Тестирую прямое подключение к Telegram API...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Ошибка cURL: {$error}\n";
} else {
    echo "📡 HTTP код: {$httpCode}\n";
    echo "📨 Ответ API: {$response}\n\n";
    
    $result = json_decode($response, true);
    if ($result && $result['ok']) {
        echo "✅ ПРЯМОЙ ТЕСТ УСПЕШЕН!\n";
        echo "🚀 API работает - можно отправлять рассылку!\n";
    } else {
        echo "❌ API вернул ошибку: " . ($result['description'] ?? 'Неизвестная ошибка') . "\n";
    }
}
?>