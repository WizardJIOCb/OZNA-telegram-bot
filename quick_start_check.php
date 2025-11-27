<?php
/**
 * Простая проверка /start команды без чтения большого лога
 */

echo "=== ПРОВЕРКА КНОПКИ /start ===\n\n";

// 1. Проверяем StartCommand
echo "1. ПРОВЕРКА StartCommand:\n";
$startFile = __DIR__ . '/CustomCommands/StartCommand.php';
if (file_exists($startFile)) {
    echo "   ✅ Файл найден\n";
    
    // Проверяем основные компоненты
    $content = file_get_contents($startFile);
    $hasClass = strpos($content, 'class StartCommand') !== false;
    $hasExecute = strpos($content, 'function execute()') !== false;
    $hasMessageService = strpos($content, 'MessageService') !== false;
    
    echo "   ✅ Класс: " . ($hasClass ? "ДА" : "НЕТ") . "\n";
    echo "   ✅ Метод execute(): " . ($hasExecute ? "ДА" : "НЕТ") . "\n";
    echo "   ✅ MessageService: " . ($hasMessageService ? "ДА" : "НЕТ") . "\n";
} else {
    echo "   ❌ StartCommand.php НЕ найден\n";
}

// 2. Проверяем webhook (если доступен curl)
echo "\n2. ПРОВЕРКА WEBHOOK:\n";
$config = include(__DIR__ . '/config.php');
$botToken = $config['api_key'] ?? '';

if (!empty($botToken) && function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$botToken}/getWebhookInfo");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && $data['ok']) {
            $result = $data['result'];
            echo "   ✅ Webhook работает\n";
            echo "   📍 URL: " . substr($result['url'] ?? 'Нет', 0, 50) . "...\n";
            echo "   📊 Ожидает: " . ($result['pending_update_count'] ?? 0) . " обновлений\n";
            
            if (isset($result['last_error_date'])) {
                $errorAge = time() - $result['last_error_date'];
                echo "   ⚠️ Последняя ошибка: " . ($errorAge < 3600 ? "недавно" : "давно") . "\n";
            } else {
                echo "   ✅ Ошибок нет\n";
            }
        } else {
            echo "   ❌ Webhook не отвечает корректно\n";
        }
    } else {
        echo "   ❌ Не удалось подключиться к Telegram API\n";
    }
} else {
    echo "   ⚠️ Нет токена или cURL для проверки\n";
}

// 3. Тестируем создание клавиатуры
echo "\n3. ТЕСТ СОЗДАНИЯ МЕНЮ:\n";
try {
    require_once(__DIR__ . '/CustomCommands/Services/MessageService.php');
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    // Пробуем создать главное меню
    $keyboard = $messageService->createMainMenuReplyKeyboard();
    if (is_array($keyboard) && !empty($keyboard)) {
        echo "   ✅ Главное меню создается корректно\n";
        echo "   📋 Кнопок: " . count($keyboard) . " ряда\n";
    } else {
        echo "   ❌ Ошибка создания главного меню\n";
    }
} catch (Exception $e) {
    echo "   ❌ Ошибка MessageService: " . $e->getMessage() . "\n";
}

// 4. Проверяем последние несколько строк лога без загрузки всего файла
echo "\n4. ПОСЛЕДНИЕ ЗАПИСИ В ЛОГЕ:\n";
$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    // Читаем только последние 2KB файла
    $handle = fopen($logFile, 'r');
    if ($handle) {
        fseek($handle, -2048, SEEK_END);
        $content = fread($handle, 2048);
        fclose($handle);
        
        $lines = explode("\n", $content);
        $recentLines = array_slice($lines, -5); // Последние 5 строк
        
        echo "   📋 Последние записи:\n";
        foreach ($recentLines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                echo "      " . substr($line, 0, 80) . "...\n";
            }
        }
    }
}

// 5. Рекомендации
echo "\n5. ЧТО ДЕЛАТЬ:\n";
echo "================\n\n";

echo "НЕМЕДЛЕННЫЕ ДЕЙСТВИЯ:\n";
echo "1. Откройте Telegram и найдите вашего бота\n";
echo "2. Отправьте команду /start\n";
echo "3. Если меню не появилось:\n";
echo "   • Удалите диалог с ботом\n";
echo "   • Найдите бота заново\n";
echo "   • Снова отправьте /start\n\n";

echo "ЕСЛИ ЭТО НЕ ПОМОГЛО:\n";
echo "• Большой лог файл (>100MB) может замедлять бота\n";
echo "• Рекомендую очистить логи:\n";
echo "  rm telegram_bot_error.log; touch telegram_bot_error.log\n";
echo "• Перезапустите веб-сервер\n\n";

echo "ДЛЯ ДИАГНОСТИКИ ПОСЛЕ /start:\n";
echo "• Проверьте новые записи в логе: Get-Content -Tail 5 telegram_bot_error.log\n";
echo "• Если новых записей нет - проблема с доставкой сообщений\n";
echo "• Если есть ошибки - скопируйте и отправьте их мне\n\n";

echo "=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>