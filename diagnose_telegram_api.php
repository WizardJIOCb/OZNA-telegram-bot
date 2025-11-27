<?php
/**
 * Диагностика и исправление проблемы с Telegram API
 */

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С TELEGRAM API ===\n\n";

echo "🔍 АНАЛИЗ ПРОБЛЕМЫ:\n";
echo "   Ошибка: 'Telegram returned an invalid response!'\n";
echo "   Причина: Проблема с подключением к Telegram API\n";
echo "   Возможные причины:\n";
echo "   • Неправильный или недействительный токен бота\n";
echo "   • Проблемы с сетевым подключением\n";
echo "   • Бот заблокирован Telegram\n";
echo "   • Проблемы с SSL/HTTPS\n\n";

require_once __DIR__ . '/vendor/autoload.php';

try {
    echo "1. ПРОВЕРКА КОНФИГУРАЦИИ БОТА:\n";
    
    $config = include __DIR__ . '/config.php';
    
    if (isset($config['api_key'])) {
        $apiKey = $config['api_key'];
        echo "   ✓ API ключ найден: " . substr($apiKey, 0, 10) . "...\n";
        
        // Проверяем формат токена
        if (preg_match('/^\d{10}:[A-Za-z0-9_-]{35}$/', $apiKey)) {
            echo "   ✓ Формат токена корректный\n";
        } else {
            echo "   ❌ Формат токена некорректный\n";
            echo "   Токен должен быть в формате: 1234567890:AABBccddEE...\n";
        }
    } else {
        echo "   ❌ API ключ не найден в конфигурации\n";
    }
    
    if (isset($config['bot_username'])) {
        echo "   ✓ Username бота: {$config['bot_username']}\n";
    }
    
    echo "\n2. ПРОВЕРКА ПРЯМОГО ПОДКЛЮЧЕНИЯ К TELEGRAM:\n";
    
    if (isset($apiKey)) {
        // Пробуем прямой вызов к Telegram API
        $url = "https://api.telegram.org/bot{$apiKey}/getMe";
        
        echo "   Тестирование URL: $url\n";
        
        // Используем cURL для тестирования
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Временно отключаем проверку SSL
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "   HTTP код: $httpCode\n";
        
        if ($error) {
            echo "   ❌ cURL ошибка: $error\n";
        } elseif ($httpCode === 200) {
            echo "   ✅ Подключение к Telegram API успешно!\n";
            
            $data = json_decode($response, true);
            if ($data && isset($data['ok']) && $data['ok']) {
                echo "   ✓ Ответ API корректный\n";
                if (isset($data['result']['username'])) {
                    echo "   ✓ Имя бота: @{$data['result']['username']}\n";
                }
            } else {
                echo "   ❌ Некорректный ответ API\n";
                echo "   Ответ: " . substr($response, 0, 200) . "\n";
            }
        } else {
            echo "   ❌ HTTP ошибка: $httpCode\n";
            echo "   Ответ: " . substr($response, 0, 200) . "\n";
        }
    }
    
    echo "\n3. ТЕСТ ПРОСТОГО СООБЩЕНИЯ:\n";
    
    if (isset($apiKey) && $httpCode === 200) {
        // Пробуем отправить сообщение через прямой API
        $testChatId = 230379668; // Ваш ID
        $message = "🔧 Тест прямого API " . date('H:i:s');
        
        $sendUrl = "https://api.telegram.org/bot{$apiKey}/sendMessage";
        $postData = [
            'chat_id' => $testChatId,
            'text' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sendUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $sendResponse = curl_exec($ch);
        $sendHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $sendError = curl_error($ch);
        curl_close($ch);
        
        echo "   Отправка сообщения...\n";
        echo "   HTTP код: $sendHttpCode\n";
        
        if ($sendError) {
            echo "   ❌ cURL ошибка: $sendError\n";
        } elseif ($sendHttpCode === 200) {
            $sendData = json_decode($sendResponse, true);
            if ($sendData && isset($sendData['ok']) && $sendData['ok']) {
                echo "   ✅ СООБЩЕНИЕ ОТПРАВЛЕНО УСПЕШНО!\n";
                echo "   Проверьте ваш чат с ботом - должно прийти сообщение\n";
            } else {
                echo "   ❌ Ошибка отправки: " . ($sendData['description'] ?? 'неизвестная ошибка') . "\n";
            }
        } else {
            echo "   ❌ HTTP ошибка при отправке: $sendHttpCode\n";
            echo "   Ответ: " . substr($sendResponse, 0, 200) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Критическая ошибка: " . $e->getMessage() . "\n";
}

echo "\n4. РЕКОМЕНДАЦИИ ПО ИСПРАВЛЕНИЮ:\n\n";

echo "   ЕСЛИ API ПОДКЛЮЧЕНИЕ РАБОТАЕТ:\n";
echo "   • Проблема в библиотеке telegram-bot\n";
echo "   • Возможно нужно обновить библиотеку\n";
echo "   • Или использовать прямые API вызовы\n\n";

echo "   ЕСЛИ API ПОДКЛЮЧЕНИЕ НЕ РАБОТАЕТ:\n";
echo "   • Проверьте правильность токена бота\n";
echo "   • Убедитесь, что бот не заблокирован\n";
echo "   • Проверьте интернет-соединение\n";
echo "   • Попробуйте создать нового бота через @BotFather\n\n";

echo "   ВРЕМЕННОЕ РЕШЕНИЕ:\n";
echo "   • Если прямое API работает, можно обойти библиотеку\n";
echo "   • Создать простую функцию отправки через cURL\n";

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>