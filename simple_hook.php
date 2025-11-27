<?php
// Создаем лог файл при любом обращении
file_put_contents(__DIR__ . '/simple_test.log', date('Y-m-d H:i:s') . " - Simple hook executed\n", FILE_APPEND);

try {
    // Пробуем только autoload, без наших классов
    require __DIR__ . '/vendor/autoload.php';
    file_put_contents(__DIR__ . '/simple_test.log', date('Y-m-d H:i:s') . " - Autoload successful\n", FILE_APPEND);
    
    $bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
    $bot_username = 'OznaWork_bot';
    
    // Создаем минимальный объект Telegram
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    file_put_contents(__DIR__ . '/simple_test.log', date('Y-m-d H:i:s') . " - Telegram object created\n", FILE_APPEND);
    
    // НЕ добавляем пути к командам - работаем только со стандартными
    $result = $telegram->handle();
    file_put_contents(__DIR__ . '/simple_test.log', date('Y-m-d H:i:s') . " - Handle result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n", FILE_APPEND);
    
} catch (Exception $e) {
    file_put_contents(__DIR__ . '/simple_error.log', date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
} catch (Error $e) {
    file_put_contents(__DIR__ . '/simple_error.log', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

file_put_contents(__DIR__ . '/simple_test.log', date('Y-m-d H:i:s') . " - Simple hook finished\n", FILE_APPEND);
echo "OK";
?>