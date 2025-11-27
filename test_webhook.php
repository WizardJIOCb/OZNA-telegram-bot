<?php
/**
 * Test webhook processing
 */

echo "=== Testing Webhook Processing ===\n\n";

// Simulate a callback query
$testCallback = [
    'update_id' => 999999,
    'callback_query' => [
        'id' => 'test_callback_id',
        'from' => [
            'id' => 230379668,
            'first_name' => 'Test',
            'username' => 'testuser'
        ],
        'message' => [
            'message_id' => 123,
            'chat' => [
                'id' => 230379668,
                'type' => 'private'
            ],
            'date' => time(),
            'text' => 'Test'
        ],
        'chat_instance' => 'test',
        'data' => 'category_Табель'
    ]
];

echo "Test callback data: category_Табель\n\n";

// Try to process
require_once __DIR__ . '/vendor/autoload.php';

try {
    $bot_api_key = getenv('BOT_API_KEY') ?: 'your_bot_token';
    $bot_username = getenv('BOT_USERNAME') ?: 'ozna_bot';
    
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    $telegram->addCommandsPath(__DIR__ . '/CustomCommands');
    
    // Set custom update
    $update = new Longman\TelegramBot\Entities\Update($testCallback);
    
    echo "Processing update...\n";
    $result = $telegram->processUpdate($update);
    
    echo "Result: " . ($result->isOk() ? "OK" : "FAILED") . "\n";
    
    if (!$result->isOk()) {
        echo "Description: " . $result->getDescription() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Check error.log for details ===\n";

header('Content-Type: text/html; charset=utf-8');

$logFile = __DIR__ . '/webhook_test.log';
$webhookUrl = 'https://api.ladyzenko.pro/tg/ozna/hook.php';

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Проверка Webhook</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        .button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Диагностика Webhook Telegram</h1>
";

// Шаг 1: Проверка webhook URL
echo "<h2>Шаг 1: Проверка Webhook URL</h2>";
echo "<div class='status info'>";
echo "Webhook URL: <code>{$webhookUrl}</code>";
echo "</div>";

// Шаг 2: Получение информации о webhook от Telegram
echo "<h2>Шаг 2: Информация от Telegram</h2>";

$bot_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$apiUrl = "https://api.telegram.org/bot{$bot_token}/getWebhookInfo";

$response = @file_get_contents($apiUrl);

if ($response === false) {
    echo "<div class='status error'>❌ Не удалось получить информацию от Telegram API</div>";
} else {
    $data = json_decode($response, true);
    
    if ($data['ok']) {
        $info = $data['result'];
        
        echo "<div class='status success'>";
        echo "✅ Webhook настроен<br>";
        echo "<strong>URL:</strong> " . ($info['url'] ?? 'не установлен') . "<br>";
        echo "<strong>Последняя ошибка:</strong> " . ($info['last_error_message'] ?? 'нет ошибок') . "<br>";
        echo "<strong>Время последней ошибки:</strong> " . (isset($info['last_error_date']) ? date('Y-m-d H:i:s', $info['last_error_date']) : 'нет') . "<br>";
        echo "<strong>Ожидающих обновлений:</strong> " . ($info['pending_update_count'] ?? 0) . "<br>";
        echo "</div>";
        
        // Проверяем, есть ли ошибки
        if (!empty($info['last_error_message'])) {
            echo "<div class='status warning'>";
            echo "⚠️ <strong>Обнаружена ошибка webhook:</strong><br>";
            echo "<pre>" . htmlspecialchars($info['last_error_message']) . "</pre>";
            echo "</div>";
        }
        
        echo "<details>";
        echo "<summary>Полная информация о webhook</summary>";
        echo "<pre>" . htmlspecialchars(json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
        echo "</details>";
    } else {
        echo "<div class='status error'>❌ Ошибка при получении информации о webhook</div>";
    }
}

// Шаг 3: Проверка доступности hook.php
echo "<h2>Шаг 3: Проверка доступности hook.php</h2>";

$hookFile = __DIR__ . '/hook.php';
if (file_exists($hookFile)) {
    echo "<div class='status success'>✅ Файл hook.php существует</div>";
    
    // Проверяем права на файл
    if (is_readable($hookFile)) {
        echo "<div class='status success'>✅ Файл hook.php читаемый</div>";
    } else {
        echo "<div class='status error'>❌ Нет прав на чтение hook.php</div>";
    }
} else {
    echo "<div class='status error'>❌ Файл hook.php не найден</div>";
}

// Шаг 4: Проверка лога
echo "<h2>Шаг 4: Последние запросы (из лога)</h2>";

$errorLog = __DIR__ . '/telegram_bot_error.log';
if (file_exists($errorLog)) {
    $logs = file($errorLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if (empty($logs)) {
        echo "<div class='status warning'>⚠️ Лог пуст - запросы не поступали или логирование не работает</div>";
    } else {
        $recentLogs = array_slice($logs, -10);
        echo "<div class='status info'>";
        echo "Последние 10 записей в логе:<br>";
        echo "<pre>";
        foreach ($recentLogs as $log) {
            echo htmlspecialchars($log) . "\n";
        }
        echo "</pre>";
        echo "</div>";
    }
} else {
    echo "<div class='status warning'>⚠️ Файл лога не найден</div>";
}

// Инструкция
echo "<h2>📋 Инструкция по проверке</h2>";
echo "<div class='status info'>";
echo "<ol>";
echo "<li>Откройте бота в Telegram</li>";
echo "<li>Нажмите ЛЮБУЮ кнопку в боте (например, 'Профиль' или 'Табель')</li>";
echo "<li>Подождите 3 секунды</li>";
echo "<li>Обновите ЭТУ страницу (нажмите F5)</li>";
echo "<li>Если в разделе 'Последние запросы' появились новые записи - webhook работает</li>";
echo "<li>Если записей нет - webhook не получает запросы от Telegram</li>";
echo "</ol>";
echo "</div>";

echo "<div style='text-align: center; margin: 20px;'>";
echo "<button class='button' onclick='location.reload();'>🔄 Обновить страницу</button>";
echo "<button class='button' onclick='window.location.href=\"view_logs.php\"'>📋 Посмотреть полные логи</button>";
echo "</div>";

echo "</div>
</body>
</html>";
?>
