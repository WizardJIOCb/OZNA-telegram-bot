<?php
/**
 * Проверка webhook бота
 */

echo "=== ПРОВЕРКА WEBHOOK ===\n\n";

$config = require __DIR__ . '/config.php';

if (empty($config['api_key'])) {
    echo "❌ api_key не определен в config.php\n";
    exit(1);
}

$botToken = $config['api_key'];

// Получаем информацию о webhook
$url = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";

$response = @file_get_contents($url);

if ($response === false) {
    echo "❌ Ошибка при запросе к Telegram API\n";
    exit(1);
}

$data = json_decode($response, true);

if (!$data['ok']) {
    echo "❌ Telegram API вернул ошибку\n";
    print_r($data);
    exit(1);
}

$webhookInfo = $data['result'];

echo "✅ Webhook информация получена:\n\n";
echo "URL: " . ($webhookInfo['url'] ?? 'НЕ УСТАНОВЛЕН') . "\n";
echo "Проверка SSL: " . ($webhookInfo['has_custom_certificate'] ? 'Да' : 'Нет') . "\n";
echo "Pending updates: " . ($webhookInfo['pending_update_count'] ?? 0) . "\n";

if (isset($webhookInfo['last_error_date'])) {
    echo "\n⚠️ ПОСЛЕДНЯЯ ОШИБКА:\n";
    echo "Дата: " . date('Y-m-d H:i:s', $webhookInfo['last_error_date']) . "\n";
    echo "Сообщение: " . ($webhookInfo['last_error_message'] ?? 'нет описания') . "\n";
}

if (isset($webhookInfo['last_synchronization_error_date'])) {
    echo "\n⚠️ ОШИБКА СИНХРОНИЗАЦИИ:\n";
    echo "Дата: " . date('Y-m-d H:i:s', $webhookInfo['last_synchronization_error_date']) . "\n";
}

echo "\n";

// Проверяем доступность webhook URL
if (!empty($webhookInfo['url'])) {
    echo "Проверка доступности webhook URL...\n";
    
    $webhookUrl = $webhookInfo['url'];
    $headers = @get_headers($webhookUrl);
    
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "✅ Webhook URL доступен\n";
    } else {
        echo "⚠️ Webhook URL недоступен\n";
    }
} else {
    echo "❌ WEBHOOK НЕ УСТАНОВЛЕН!\n";
    echo "\nЭто КРИТИЧЕСКАЯ проблема! Без webhook бот не получает обновления.\n";
}

echo "\n=== ДИАГНОСТИКА ===\n\n";

if ($webhookInfo['pending_update_count'] > 0) {
    echo "⚠️ Есть {$webhookInfo['pending_update_count']} необработанных обновлений!\n";
    echo "Это может означать что:\n";
    echo "- Webhook не отвечает правильно\n";
    echo "- Бот не обрабатывает обновления\n";
    echo "- Есть ошибки в коде\n\n";
}

if (isset($webhookInfo['last_error_date'])) {
    $errorAge = time() - $webhookInfo['last_error_date'];
    if ($errorAge < 3600) {
        echo "❌ Ошибка webhook была НЕДАВНО (менее часа назад)!\n";
        echo "Это может быть причиной проблемы!\n\n";
    }
}

echo "=== РЕКОМЕНДАЦИИ ===\n\n";

if (empty($webhookInfo['url'])) {
    echo "1. УСТАНОВИТЕ WEBHOOK:\n";
    echo "   php set.php\n\n";
} else {
    echo "1. Проверьте что set.php выполнялся после последних изменений в коде\n";
    echo "2. Если проблема только началась, попробуйте переустановить webhook:\n";
    echo "   php unset.php\n";
    echo "   php set.php\n\n";
    echo "3. Проверьте логи сервера на наличие ошибок PHP\n\n";
}

echo "4. Попробуйте нажать кнопку в боте снова после переустановки webhook\n\n";
