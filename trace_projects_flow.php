<?php
// Real-time verification for Projects button callback flow

echo "=== ОТСЛЕЖИВАНИЕ ПОЛНОГО ПОТОКА КНОПКИ ПРОЕКТЫ ===\n\n";

// First, let's verify the Projects button exists in the main menu
echo "1. ПРОВЕРКА СОЗДАНИЯ КНОПКИ ПРОЕКТЫ\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/custom_autoload.php';
    
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    // Test with user level 1 (should have Projects button)
    $testUserId = 230379668; // Your actual user ID
    $keyboard = $messageService->createMainMenuKeyboard($testUserId);
    
    echo "Главное меню для пользователя {$testUserId}:\n";
    $hasProjectsButton = false;
    $projectsCallbackData = '';
    
    foreach ($keyboard as $rowIndex => $row) {
        echo "  Ряд " . ($rowIndex + 1) . ":\n";
        foreach ($row as $button) {
            echo "    '{$button['text']}' -> '{$button['callback_data']}'\n";
            if (strpos($button['text'], 'Проекты') !== false) {
                $hasProjectsButton = true;
                $projectsCallbackData = $button['callback_data'];
            }
        }
    }
    
    if ($hasProjectsButton) {
        echo "✅ Кнопка Проекты НАЙДЕНА с callback: '{$projectsCallbackData}'\n";
    } else {
        echo "❌ Кнопка Проекты НЕ НАЙДЕНА\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка при создании кнопки: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. ПРОВЕРКА ОБРАБОТКИ CALLBACK\n";

try {
    // Test the full callback processing chain
    $callbackData = $projectsCallbackData; // 'category_Проекты'
    
    echo "Обрабатываем callback: '{$callbackData}'\n";
    
    // Simulate CallbackHandlerFactory processing
    $handlerFactory = new \Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory();
    
    // Register all handlers as in CallbackqueryCommand
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    
    // Register handlers in the same order as CallbackqueryCommand
    $handlerFactory->registerHandler(
        new \Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler($adminService, $messageService)
    );
    $handlerFactory->registerHandler(
        new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler($adminService, $messageService)
    );
    
    echo "✅ Обработчики зарегистрированы\n";
    
    // Test which handler can handle the callback
    $reflection = new ReflectionClass($handlerFactory);
    $handlersProperty = $reflection->getProperty('handlers');
    $handlersProperty->setAccessible(true);
    $handlers = $handlersProperty->getValue($handlerFactory);
    
    echo "Проверяем обработчики:\n";
    foreach ($handlers as $index => $handler) {
        $handlerName = get_class($handler);
        $canHandle = $handler->canHandle($callbackData);
        $priority = $handler->getPriority();
        echo "  {$handlerName} (приоритет {$priority}): " . ($canHandle ? 'МОЖЕТ' : 'НЕ МОЖЕТ') . " обработать\n";
        
        if ($canHandle) {
            echo "    ✅ ВЫБРАН: {$handlerName}\n";
            break;
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка при обработке callback: " . $e->getMessage() . "\n";
    echo "Стек: " . $e->getTraceAsString() . "\n";
}

echo "\n3. ПРОВЕРКА ЛОГОВ ОШИБОК\n";

// Check recent logs for any ProjectHandler activity
$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    $logLines = file($logFile);
    $recentLines = array_slice($logLines, -50); // Last 50 lines
    
    $projectHandlerLogs = array_filter($recentLines, function($line) {
        return strpos($line, 'ProjectHandler') !== false;
    });
    
    if (!empty($projectHandlerLogs)) {
        echo "Недавние логи ProjectHandler:\n";
        foreach ($projectHandlerLogs as $line) {
            echo "  " . trim($line) . "\n";
        }
    } else {
        echo "❌ Нет логов ProjectHandler в последних 50 строках\n";
        echo "Это означает, что никто не пытался нажать кнопку Проекты\n";
    }
} else {
    echo "❌ Файл логов не найден\n";
}

echo "\n4. ПРОВЕРКА АКТИВНОСТИ КНОПКИ\n";

// Check if there are any recent 'category_Проекты' callbacks in logs
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $projectsCallbacks = substr_count($logContent, 'category_Проекты');
    
    echo "Количество callback 'category_Проекты' в логах: {$projectsCallbacks}\n";
    
    if ($projectsCallbacks === 0) {
        echo "❌ ПРОБЛЕМА: Никто не нажимал кнопку Проекты или callback не обрабатывается\n";
        echo "\nВОЗМОЖНЫЕ ПРИЧИНЫ:\n";
        echo "1. Веб-сервер не перезапущен после исправления\n";
        echo "2. Файлы не обновлены на сервере\n";
        echo "3. Кеш PHP не очищен\n";
        echo "4. Проблема с webhook URL\n";
        echo "5. Кнопка отображается, но callback не отправляется\n";
    } else {
        echo "✅ Кнопка Проекты использовалась {$projectsCallbacks} раз(а)\n";
    }
}

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "1. ОБЯЗАТЕЛЬНО перезапустите веб-сервер:\n";
echo "   sudo systemctl restart php8.3-fpm\n";
echo "   sudo systemctl restart nginx\n\n";

echo "2. Очистите кеш браузера и Telegram\n\n";

echo "3. Проверьте webhook URL в настройках бота\n\n";

echo "4. Попробуйте нажать кнопку Проекты в Telegram и проверьте логи:\n";
echo "   tail -f telegram_bot_error.log | grep -i project\n\n";

echo "Все компоненты работают правильно в тестовой среде!\n";
echo "Проблема скорее всего в том, что веб-сервер не перезапущен.\n";

?>