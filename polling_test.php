<?php
// Простой polling скрипт для тестирования бота локально

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/polling_test.log');

echo "=== ТЕСТ БОТА В POLLING РЕЖИМЕ ===\n\n";

try {
    // Загружаем автозагрузчик
    require __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/custom_autoload.php';

    $bot_api_key  = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ';
    $bot_username = 'OznaWork_bot';

    // Создаем объект Telegram API
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Включаем MySQL
    $mysql_credentials = [
        'host'     => '82.146.42.213',
        'port'     => 3306,
        'user'     => 'WizardJIOCb',
        'password' => '7qQ#28051961',
        'database' => 'tg_bot',
    ];
    $telegram->enableMySql($mysql_credentials);

    // Добавляем пути к командам
    $telegram->addCommandsPaths(array(__DIR__ . '/CustomCommands'));

    // Включаем админов
    $telegram->enableAdmins([
        230379668,  // Your ID
        5261465966  // Боря
    ]);

    echo "✅ Telegram бот инициализирован\n";
    echo "✅ Подключение к БД установлено\n";
    echo "✅ Команды загружены\n\n";

    echo "Запускаем polling для тестирования...\n";
    echo "=====================================\n";

    // Запускаем polling
    $offset = 0;
    $timeout = 10;
    $checkCount = 0;
    $maxChecks = 6; // 1 минута тестирования

    while ($checkCount < $maxChecks) {
        echo "🔄 Проверка " . ($checkCount + 1) . "/{$maxChecks} (offset: {$offset})...\n";
        
        try {
            // Получаем обновления
            $response = $telegram->handleGetUpdates([
                'offset' => $offset,
                'limit' => 10,
                'timeout' => $timeout
            ]);

            if ($response->isOk()) {
                $updates = $response->getResult();
                
                if (!empty($updates)) {
                    echo "📨 Получено " . count($updates) . " обновлений:\n";
                    
                    foreach ($updates as $update) {
                        $updateId = $update->getUpdateId();
                        $offset = $updateId + 1;
                        
                        // Обрабатываем обновление
                        $processResult = $telegram->processUpdate($update);
                        
                        // Логируем информацию об обновлении
                        if ($update->getMessage()) {
                            $message = $update->getMessage();
                            $text = $message->getText() ?? 'медиа';
                            $chatId = $message->getChat()->getId();
                            echo "  📝 Сообщение от {$chatId}: {$text}\n";
                        } elseif ($update->getCallbackQuery()) {
                            $callback = $update->getCallbackQuery();
                            $data = $callback->getData();
                            $chatId = $callback->getMessage()->getChat()->getId();
                            echo "  🎯 Callback от {$chatId}: {$data}\n";
                            
                            // Проверяем, если это callback ШМР
                            if (strpos($data, 'Шеф_монтаж') !== false || strpos($data, 'ШМР') !== false) {
                                echo "     🔧 ШМР CALLBACK ОБНАРУЖЕН!\n";
                            }
                        }
                        
                        echo "     ✅ Обработано (ID: {$updateId})\n";
                    }
                } else {
                    echo "⏳ Новых сообщений нет\n";
                }
            } else {
                echo "❌ Ошибка polling: " . $response->getDescription() . "\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Исключение: " . $e->getMessage() . "\n";
        }
        
        $checkCount++;
        
        if ($checkCount < $maxChecks) {
            sleep(10); // Ждем 10 секунд
        }
    }

    echo "\n=== ТЕСТИРОВАНИЕ ЗАВЕРШЕНО ===\n";
    echo "Теперь попробуйте использовать бота:\n";
    echo "1. Отправьте команду /start\n";
    echo "2. Нажмите 'Табель'\n";
    echo "3. Нажмите 'Отдел ШМР'\n";
    echo "4. Следите за выводом здесь\n";

} catch (Exception $e) {
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Стек вызовов: " . $e->getTraceAsString() . "\n";
}

?>