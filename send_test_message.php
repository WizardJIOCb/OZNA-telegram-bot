<?php

// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';

// Create Telegram API object
$telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

try {
    // Send a test message to a known user (replace with your user ID)
    $chat_id = '5261465966'; // Боря's chat ID from the logs
    
    $result = Longman\TelegramBot\Request::sendMessage([
        'chat_id' => $chat_id,
        'text' => 'Test message from bot - проверка связи'
    ]);
    
    if ($result->isOk()) {
        echo "Message sent successfully!\n";
    } else {
        echo "Error sending message: " . $result->getDescription() . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

?>