<?php
/**
 * Проверка после включения cURL и OpenSSL
 */

echo "=== ПРОВЕРКА ПОСЛЕ ИСПРАВЛЕНИЯ ===\n\n";

echo "1. ПРОВЕРКА РАСШИРЕНИЙ PHP:\n";

// Проверка cURL
if (function_exists('curl_init')) {
    echo "   ✅ cURL установлен и работает\n";
} else {
    echo "   ❌ cURL всё ещё не работает\n";
}

// Проверка OpenSSL
if (extension_loaded('openssl')) {
    echo "   ✅ OpenSSL установлен и работает\n";
} else {
    echo "   ❌ OpenSSL всё ещё не работает\n";
}

// Проверка allow_url_fopen
if (ini_get('allow_url_fopen')) {
    echo "   ✅ allow_url_fopen включен\n";
} else {
    echo "   ❌ allow_url_fopen всё ещё отключен\n";
}

echo "\n2. ТЕСТ ПОДКЛЮЧЕНИЯ К TELEGRAM:\n";

if (function_exists('curl_init')) {
    try {
        $apiKey = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
        $url = "https://api.telegram.org/bot{$apiKey}/getMe";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "   ❌ cURL ошибка: $error\n";
        } elseif ($httpCode === 200) {
            echo "   ✅ Подключение к Telegram API работает!\n";
            
            $data = json_decode($response, true);
            if ($data && isset($data['ok']) && $data['ok']) {
                echo "   ✓ Бот отвечает корректно\n";
                if (isset($data['result']['username'])) {
                    echo "   ✓ Имя бота: @{$data['result']['username']}\n";
                }
            }
        } else {
            echo "   ❌ HTTP ошибка: $httpCode\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
    }
}

echo "\n3. ТЕСТ ОТПРАВКИ СООБЩЕНИЯ:\n";

if (function_exists('curl_init') && isset($httpCode) && $httpCode === 200) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        require_once __DIR__ . '/custom_autoload.php';
        require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
        
        $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
        $testMessage = "🎉 Исправление работает! " . date('H:i:s');
        
        $result = $messageService->sendMessage(230379668, $testMessage);
        
        if (method_exists($result, 'isOk') && $result->isOk()) {
            echo "   ✅ ТЕСТОВОЕ СООБЩЕНИЕ ОТПРАВЛЕНО УСПЕШНО!\n";
            echo "   Проверьте ваш чат с ботом\n";
        } else {
            echo "   ❌ Ошибка отправки\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Исключение: " . $e->getMessage() . "\n";
    }
}

echo "\n4. СЛЕДУЮЩИЕ ШАГИ:\n";

if (function_exists('curl_init')) {
    echo "   ✅ После исправления можете:\n";
    echo "   1. Открыть бота в Telegram\n";
    echo "   2. Перейти в 'Проекты на контроле' → ОИ834\n";
    echo "   3. Нажать 'Тестовое уведомление'\n";
    echo "   4. Сообщения должны прийти и в группу, и в личный чат\n";
} else {
    echo "   ❌ Сначала включите cURL в php.ini и перезапустите сервер\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>