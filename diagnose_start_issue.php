<?php
/**
 * Диагностика проблемы с кнопкой /start
 */

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ /start ===\n\n";

// 1. Проверяем существование StartCommand
echo "1. ПРОВЕРКА ФАЙЛА StartCommand:\n";
$startCommandFile = __DIR__ . '/CustomCommands/StartCommand.php';
if (file_exists($startCommandFile)) {
    echo "   ✅ Файл StartCommand.php найден\n";
    
    // Проверяем содержимое
    $content = file_get_contents($startCommandFile);
    if (strpos($content, 'class StartCommand') !== false) {
        echo "   ✅ Класс StartCommand найден\n";
    } else {
        echo "   ❌ Класс StartCommand НЕ найден\n";
    }
    
    if (strpos($content, 'public function execute()') !== false) {
        echo "   ✅ Метод execute() найден\n";
    } else {
        echo "   ❌ Метод execute() НЕ найден\n";
    }
} else {
    echo "   ❌ Файл StartCommand.php НЕ найден\n";
}

// 2. Проверяем последние записи в логах
echo "\n2. ПРОВЕРКА ЛОГОВ ОШИБОК:\n";
$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    echo "   ✅ Файл лога найден\n";
    
    // Читаем последние 20 строк
    $lines = file($logFile);
    $recentLines = array_slice($lines, -20);
    
    echo "   📋 Последние записи:\n";
    foreach ($recentLines as $line) {
        $line = trim($line);
        if (!empty($line)) {
            echo "      " . substr($line, 0, 100) . "...\n";
        }
    }
} else {
    echo "   ❌ Файл лога НЕ найден\n";
}

// 3. Проверяем webhook статус
echo "\n3. ПРОВЕРКА WEBHOOK:\n";
$config = include(__DIR__ . '/config.php');
$botToken = $config['api_key'] ?? '';

if (!empty($botToken)) {
    echo "   ✅ Токен бота найден\n";
    
    // Проверяем webhook через curl
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$botToken}/getWebhookInfo");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data && $data['ok']) {
                $result = $data['result'];
                echo "   ✅ Webhook активен\n";
                echo "   📍 URL: " . ($result['url'] ?? 'Не установлен') . "\n";
                echo "   📊 Ожидающих обновлений: " . ($result['pending_update_count'] ?? 0) . "\n";
                
                if (isset($result['last_error_date'])) {
                    echo "   ⚠️ Последняя ошибка: " . date('Y-m-d H:i:s', $result['last_error_date']) . "\n";
                    echo "   📝 Сообщение об ошибке: " . ($result['last_error_message'] ?? 'Нет сообщения') . "\n";
                }
            } else {
                echo "   ❌ Ошибка получения информации о webhook\n";
            }
        } else {
            echo "   ❌ Не удалось подключиться к Telegram API\n";
        }
    } else {
        echo "   ⚠️ cURL недоступен для проверки webhook\n";
    }
} else {
    echo "   ❌ Токен бота НЕ найден в config.php\n";
}

// 4. Проверяем hook.php
echo "\n4. ПРОВЕРКА ФАЙЛА HOOK.PHP:\n";
$hookFile = __DIR__ . '/hook.php';
if (file_exists($hookFile)) {
    echo "   ✅ Файл hook.php найден\n";
    
    $hookContent = file_get_contents($hookFile);
    if (strpos($hookContent, 'StartCommand') !== false) {
        echo "   ✅ Упоминание StartCommand найдено в hook.php\n";
    } else {
        echo "   ⚠️ StartCommand не упоминается напрямую в hook.php\n";
    }
    
    if (strpos($hookContent, 'addCommandsPaths') !== false) {
        echo "   ✅ Путь к командам настроен\n";
    } else {
        echo "   ❌ Путь к командам НЕ настроен\n";
    }
} else {
    echo "   ❌ Файл hook.php НЕ найден\n";
}

// 5. Рекомендации
echo "\n5. РЕКОМЕНДАЦИИ ПО РЕШЕНИЮ:\n";
echo "=================================\n\n";

echo "ЕСЛИ ВСЕ ПРОВЕРКИ ПРОШЛИ УСПЕШНО:\n";
echo "• Проблема может быть в Telegram кэше\n";
echo "• Попробуйте удалить диалог с ботом и начать заново\n";
echo "• Подождите 10-15 минут для обновления кэша\n\n";

echo "ЕСЛИ ЕСТЬ ОШИБКИ WEBHOOK:\n";
echo "• Проверьте доступность вашего сервера\n";
echo "• Убедитесь, что SSL сертификат действителен\n";
echo "• Перезапустите webhook: php set_webhook.php\n\n";

echo "ЕСЛИ StartCommand НЕ НАЙДЕН:\n";
echo "• Восстановите файл StartCommand.php\n";
echo "• Проверьте права доступа к файлам\n";
echo "• Убедитесь, что autoloader работает корректно\n\n";

echo "ДЛЯ ДАЛЬНЕЙШЕЙ ДИАГНОСТИКИ:\n";
echo "• Отправьте боту команду /start\n";
echo "• Сразу проверьте логи: Get-Content -Tail 10 telegram_bot_error.log\n";
echo "• Если новых записей нет - проблема с webhook\n";
echo "• Если есть записи с ошибками - проблема в коде\n\n";

echo "=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>