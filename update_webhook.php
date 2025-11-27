<?php

require_once __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    // Set the webhook URL
    $webhook_url = 'https://oznabot.ru/hook.php';
    $result = $telegram->setWebhook($webhook_url);
    
    if ($result->isOk()) {
        echo "Webhook set successfully!\n";
        echo "Webhook URL: {$webhook_url}\n";
        echo "Result: " . $result->getDescription() . "\n";
    } else {
        echo "Failed to set webhook!\n";
        echo "Error: " . $result->getDescription() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

?>