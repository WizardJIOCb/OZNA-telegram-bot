<?php

require __DIR__ . '/vendor/autoload.php';

try {
    // Telegram API Key and Username
    $bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
    $bot_username = 'OznaWork_bot';

    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set the webhook URL
    $webhook_url = 'https://api.ladyzenko.pro/tg/ozna/hook_no_db.php';
    $result = $telegram->setWebhook($webhook_url);

    if ($result->isOk()) {
        echo 'Webhook successfully set to: ' . $webhook_url . "\n";
    } else {
        echo 'Failed to set webhook: ' . $result->getDescription() . "\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}