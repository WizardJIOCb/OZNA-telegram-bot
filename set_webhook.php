<?php

require_once __DIR__ . '/config.php';

// Получаем токен бота из конфигурации
$botToken = $api_key ?? '';

if (empty($botToken)) {
    die("Bot token not found in config.php\n");
}

// URL webhook
$webhookUrl = $hook_url ?? 'https://yourdomain.com/hook.php';

if (empty($webhookUrl)) {
    die("Webhook URL not found in config.php\n");
}

// Формируем URL для установки webhook
$telegramApiUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";

// Данные для отправки
$postData = [
    'url' => $webhookUrl
];

// Инициализируем cURL
$ch = curl_init($telegramApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

// Отправляем запрос
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Проверяем результат
if ($response === false) {
    echo "Error setting webhook: curl error\n";
} else {
    $responseData = json_decode($response, true);
    if ($responseData['ok'] ?? false) {
        echo "Webhook set successfully!\n";
        echo "Webhook URL: {$webhookUrl}\n";
    } else {
        echo "Error setting webhook:\n";
        echo "HTTP Code: {$httpCode}\n";
        echo "Response: " . ($response ?: 'empty') . "\n";
    }
}