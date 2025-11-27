<?php

// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

// Create Telegram API object
$telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

try {
    // Set webhook
    $webhook_url = 'https://api.ladyzenko.pro/tg/ozna/hook.php';
    
    $result = Longman\TelegramBot\Request::setWebhook(['url' => $webhook_url]);
    
    if ($result->isOk()) {
        echo "Webhook set successfully!\n";
        echo "Description: " . $result->getDescription() . "\n";
    } else {
        echo "Error setting webhook: " . $result->getDescription() . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

?>