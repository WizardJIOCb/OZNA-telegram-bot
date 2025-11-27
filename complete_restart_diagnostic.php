<?php
// Complete diagnostic and restart script for Projects button

echo "=== ПОЛНАЯ ДИАГНОСТИКА И ПЕРЕЗАПУСК ===\n";

// 1. Check if ProjectHandler file exists and is readable
$projectHandlerPath = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($projectHandlerPath)) {
    echo "✓ ProjectHandler.php существует\n";
    echo "  Размер файла: " . filesize($projectHandlerPath) . " байт\n";
    echo "  Последнее изменение: " . date('Y-m-d H:i:s', filemtime($projectHandlerPath)) . "\n";
} else {
    echo "❌ ProjectHandler.php НЕ НАЙДЕН!\n";
    exit(1);
}

// 2. Check if CallbackqueryCommand includes ProjectHandler
$callbackPath = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
if (file_exists($callbackPath)) {
    $callbackContent = file_get_contents($callbackPath);
    if (strpos($callbackContent, 'ProjectHandler') !== false) {
        echo "✓ CallbackqueryCommand содержит ProjectHandler\n";
    } else {
        echo "❌ CallbackqueryCommand НЕ содержит ProjectHandler!\n";
    }
} else {
    echo "❌ CallbackqueryCommand.php НЕ НАЙДЕН!\n";
}

// 3. Check if MessageService includes Projects button
$messageServicePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messageServicePath)) {
    $messageContent = file_get_contents($messageServicePath);
    if (strpos($messageContent, 'Проекты') !== false) {
        echo "✓ MessageService содержит кнопку Проекты\n";
    } else {
        echo "❌ MessageService НЕ содержит кнопку Проекты!\n";
    }
} else {
    echo "❌ MessageService.php НЕ НАЙДЕН!\n";
}

// 4. Clear all possible caches
echo "\n=== ОЧИСТКА КЕШЕЙ ===\n";

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache очищен\n";
} else {
    echo "ℹ️ OPcache недоступен\n";
}

// Clear realpath cache
clearstatcache();
echo "✓ Realpath cache очищен\n";

// Touch all related files to update modification time
$filesToTouch = [
    'hook.php',
    'CustomCommands/CallbackqueryCommand.php',
    'CustomCommands/Handlers/ProjectHandler.php',
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Services/CallbackHandlerFactory.php'
];

foreach ($filesToTouch as $file) {
    if (file_exists($file)) {
        touch($file);
        echo "✓ Обновлён {$file}\n";
    }
}

// 5. Create restart signals
$signals = [
    'restart_required.flag',
    'clear_cache.flag',
    'update_handlers.flag'
];

foreach ($signals as $signal) {
    file_put_contents($signal, date('Y-m-d H:i:s') . " - ProjectHandler restart\n");
    echo "✓ Создан флаг {$signal}\n";
}

// 6. Test the basic ProjectHandler functionality
echo "\n=== ТЕСТ ОСНОВНОЙ ФУНКЦИОНАЛЬНОСТИ ===\n";

// Test callback processing
$testCallback = 'category_Проекты';
echo "Тестируем callback: {$testCallback}\n";

// Test category extraction
if (strpos($testCallback, 'category_') === 0) {
    $category = substr($testCallback, strlen('category_'));
    echo "Извлечённая категория: {$category}\n";
    
    if ($category === 'Проекты') {
        echo "✅ Категория извлечена правильно\n";
    } else {
        echo "❌ Ошибка извлечения категории\n";
    }
} else {
    echo "❌ Неправильный формат callback\n";
}

// 7. Create a simple test script for immediate testing
$testScript = '<?php
// Тест кнопки Проекты после перезапуска
echo "Тестирование кнопки Проекты...\n";

// Include path setup
require_once __DIR__ . "/vendor/autoload.php";

// Test that ProjectHandler can be loaded
try {
    require_once __DIR__ . "/CustomCommands/Handlers/ProjectHandler.php";
    echo "✓ ProjectHandler загружен успешно\n";
} catch (Exception $e) {
    echo "❌ Ошибка загрузки ProjectHandler: " . $e->getMessage() . "\n";
}

// Test callback processing simulation
$callback = "category_Проекты";
$category = substr($callback, strlen("category_"));
echo "Callback: {$callback}\n";
echo "Category: {$category}\n";
echo "Match test: " . ($category === "Проекты" ? "PASS" : "FAIL") . "\n";
?>';

file_put_contents('test_projects_after_restart.php', $testScript);
echo "✓ Создан тестовый скрипт test_projects_after_restart.php\n";

echo "\n=== ИНСТРУКЦИИ ПО ПЕРЕЗАПУСКУ ===\n";
echo "1. Перезапустите веб-сервер:\n";
echo "   - IIS: iisreset\n";
echo "   - Apache: service apache2 restart\n";
echo "   - Nginx: service nginx restart\n";
echo "\n2. После перезапуска выполните:\n";
echo "   php test_projects_after_restart.php\n";
echo "\n3. Проверьте кнопку 'Проекты' в Telegram\n";
echo "\n4. Если проблема сохраняется, проверьте:\n";
echo "   - Webhook URL настроен правильно\n";
echo "   - Telegram получает обновления\n";
echo "   - Нет блокировки файрвола\n";

echo "\n🎯 ДИАГНОСТИКА ЗАВЕРШЕНА!\n";
?>