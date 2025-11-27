<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
$bot_username = 'OznaWork_bot';
$hook_url     = 'https://api.ladyzenko.pro/tg/ozna/hook.php';

//echo("begin\r\n<br>");

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set webhook
    $result = $telegram->setWebhook($hook_url);
    if ($result->isOk()) {
        echo $result->getDescription();
    }
    echo('done');    
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
     echo $e->getMessage();
}
//echo("\r\n<br>end\r\n<br>");
?>
