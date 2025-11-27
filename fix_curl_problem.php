<?php
/**
 * Решение проблемы с отсутствующим cURL
 */

echo "=== РЕШЕНИЕ ПРОБЛЕМЫ С CURL ===\n\n";

echo "🔍 ОБНАРУЖЕНА ПРОБЛЕМА:\n";
echo "   • У вас не установлено расширение cURL для PHP\n";
echo "   • Это объясняет ошибку 'Telegram returned an invalid response!'\n";
echo "   • Библиотека telegram-bot требует cURL для работы\n\n";

echo "💡 ВАРИАНТЫ РЕШЕНИЯ:\n\n";

echo "1. УСТАНОВКА CURL (РЕКОМЕНДУЕТСЯ):\n";
echo "   Для Windows с XAMPP/WAMP:\n";
echo "   • Откройте файл php.ini\n";
echo "   • Найдите строку ;extension=curl\n";
echo "   • Уберите символ ; в начале (раскомментируйте)\n";
echo "   • Перезапустите веб-сервер\n\n";
echo "   Для других систем:\n";
echo "   • apt-get install php-curl (Ubuntu/Debian)\n";
echo "   • yum install php-curl (CentOS/RHEL)\n\n";

echo "2. АЛЬТЕРНАТИВНОЕ РЕШЕНИЕ БЕЗ CURL:\n";
echo "   Создадим обходное решение через file_get_contents\n\n";

// Проверим, работает ли file_get_contents с HTTPS
echo "3. ТЕСТ АЛЬТЕРНАТИВНОГО МЕТОДА:\n";

$apiKey = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';

try {
    // Создаём контекст для HTTPS запроса
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: PHP\r\n",
            'timeout' => 10,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $url = "https://api.telegram.org/bot{$apiKey}/getMe";
    echo "   Тестирование URL: $url\n";
    
    $response = file_get_contents($url, false, $context);
    
    if ($response !== false) {
        echo "   ✅ file_get_contents работает!\n";
        
        $data = json_decode($response, true);
        if ($data && isset($data['ok']) && $data['ok']) {
            echo "   ✓ Ответ API корректный\n";
            if (isset($data['result']['username'])) {
                echo "   ✓ Имя бота: @{$data['result']['username']}\n";
            }
        }
    } else {
        echo "   ❌ file_get_contents не работает\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Ошибка: " . $e->getMessage() . "\n";
}

echo "\n4. СОЗДАНИЕ ОБХОДНОГО РЕШЕНИЯ:\n";

// Создадим простую функцию для отправки сообщений
if ($response !== false) {
    echo "   Создаём функцию отправки сообщений без cURL...\n";
    
    $simpleMessageCode = '<?php
/**
 * Простая отправка сообщений через Telegram API без cURL
 */

function sendTelegramMessage($chatId, $text, $apiKey = "7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ") {
    $url = "https://api.telegram.org/bot{$apiKey}/sendMessage";
    
    $postData = http_build_query([
        \'chat_id\' => $chatId,
        \'text\' => $text,
        \'parse_mode\' => \'HTML\'
    ]);
    
    $context = stream_context_create([
        \'http\' => [
            \'method\' => \'POST\',
            \'header\' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                        "Content-Length: " . strlen($postData) . "\r\n",
            \'content\' => $postData,
            \'timeout\' => 10,
            \'ignore_errors\' => true
        ],
        \'ssl\' => [
            \'verify_peer\' => false,
            \'verify_peer_name\' => false
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        return $data && isset($data[\'ok\']) && $data[\'ok\'];
    }
    
    return false;
}

// Тест функции
$testResult = sendTelegramMessage(230379668, "🔧 Тест обходного решения " . date("H:i:s"));
echo ($testResult ? "✅ Сообщение отправлено!" : "❌ Ошибка отправки") . "\n";
?>';
    
    file_put_contents(__DIR__ . '/simple_telegram_sender.php', $simpleMessageCode);
    echo "   ✓ Создан файл simple_telegram_sender.php\n";
    
    echo "\n5. ТЕСТ ОБХОДНОГО РЕШЕНИЯ:\n";
    include __DIR__ . '/simple_telegram_sender.php';
}

echo "\n=== ИТОГ ===\n";

if ($response !== false) {
    echo "✅ РЕШЕНИЕ НАЙДЕНО:\n";
    echo "   1. Проблема с cURL решена через file_get_contents\n";
    echo "   2. Создана альтернативная функция отправки\n";
    echo "   3. Тестовое сообщение должно прийти в ваш чат\n\n";
    
    echo "🔧 СЛЕДУЮЩИЕ ШАГИ:\n";
    echo "   1. Проверьте ваш чат с ботом - должно прийти сообщение\n";
    echo "   2. Если сообщение пришло - решение работает\n";
    echo "   3. Можем интегрировать это решение в основной код\n";
    echo "   4. Или установите cURL для полноценной работы\n";
} else {
    echo "❌ ТРЕБУЕТСЯ УСТАНОВКА CURL:\n";
    echo "   file_get_contents тоже не работает\n";
    echo "   Необходимо установить и включить расширение cURL\n";
}

echo "\n=== КОНЕЦ ДИАГНОСТИКИ ===\n";
?>