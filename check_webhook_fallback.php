<?php

$config = require_once __DIR__ . '/config.php';

// Получаем токен бота из конфигурации
$botToken = $config['api_key'] ?? '';

if (empty($botToken)) {
    die("Bot token not found in config.php\n");
}

// Формируем URL для получения информации о webhook
$telegramApiUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";

// Используем file_get_contents вместо cURL
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$response = file_get_contents($telegramApiUrl, false, $context);

// Проверяем результат
if ($response === false) {
    echo "Error getting webhook info: network error\n";
} else {
    $responseData = json_decode($response, true);
    if ($responseData['ok'] ?? false) {
        echo "Webhook info retrieved successfully:\n";
        echo "URL: " . ($responseData['result']['url'] ?? 'Not set') . "\n";
        echo "Pending update count: " . ($responseData['result']['pending_update_count'] ?? 0) . "\n";
        echo "Last error message: " . ($responseData['result']['last_error_message'] ?? 'None') . "\n";
        echo "Last error date: " . (isset($responseData['result']['last_error_date']) ? date('Y-m-d H:i:s', $responseData['result']['last_error_date']) : 'None') . "\n";
    } else {
        echo "Error getting webhook info:\n";
        echo "Response: " . ($response ?: 'empty') . "\n";
    }
}