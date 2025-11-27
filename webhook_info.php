<?php
// Проверяем webhook через прямой API запрос
$bot_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';

// Получаем информацию о webhook
$url = "https://api.telegram.org/bot{$bot_token}/getWebhookInfo";
$response = file_get_contents($url);
$data = json_decode($response, true);

echo "<h3>Информация о Webhook:</h3>";
echo "<pre>";
print_r($data);
echo "</pre>";

if ($data['ok']) {
    $info = $data['result'];
    
    echo "<h3>Детали:</h3>";
    echo "<p><strong>URL:</strong> " . $info['url'] . "</p>";
    echo "<p><strong>Pending updates:</strong> " . $info['pending_update_count'] . "</p>";
    
    if (isset($info['last_error_date'])) {
        echo "<p><strong>Последняя ошибка:</strong> " . date('Y-m-d H:i:s', $info['last_error_date']) . "</p>";
        echo "<p><strong>Сообщение об ошибке:</strong> " . $info['last_error_message'] . "</p>";
    }
    
    if (isset($info['last_synchronization_error_date'])) {
        echo "<p><strong>Ошибка синхронизации:</strong> " . date('Y-m-d H:i:s', $info['last_synchronization_error_date']) . "</p>";
    }
}
?>