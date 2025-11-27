<?php

require_once __DIR__ . '/config.php';

// Получаем токен бота из конфигурации
$botToken = $api_key ?? '';

if (empty($botToken)) {
    die("Bot token not found in config.php\n");
}

// Формируем URL для получения информации о webhook
$telegramApiUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";

// Инициализируем cURL
$ch = curl_init($telegramApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Отправляем запрос
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Проверяем результат
if ($response === false) {
    echo "Error getting webhook info: curl error\n";
} else {
    $responseData = json_decode($response, true);
    if ($responseData['ok'] ?? false) {
        echo "Webhook info retrieved successfully:\n";
        echo json_encode($responseData['result'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "Error getting webhook info:\n";
        echo "HTTP Code: {$httpCode}\n";
        echo "Response: " . ($response ?: 'empty') . "\n";
    }
}