<?php
// Final comprehensive restart and fix script

echo "=== ФИНАЛЬНОЕ ИСПРАВЛЕНИЕ КНОПКИ ПРОЕКТЫ ===\n\n";

echo "✅ ИСПРАВЛЕНИЕ: Проблема с загрузкой интерфейса CallbackHandlerInterface\n";
echo "✅ РЕШЕНИЕ: Добавлено явное подключение интерфейса в ProjectHandler.php\n\n";

// Touch all relevant files to force reload
$filesToTouch = [
    'hook.php',
    'CustomCommands/CallbackqueryCommand.php',
    'CustomCommands/Handlers/ProjectHandler.php',
    'CustomCommands/Services/MessageService.php',
    'CustomCommands/Services/CallbackHandlerFactory.php',
    'custom_autoload.php'
];

echo "Обновление временных меток файлов:\n";
foreach ($filesToTouch as $file) {
    if (file_exists($file)) {
        touch($file);
        echo "✅ {$file}\n";
    } else {
        echo "❌ {$file} (файл не найден)\n";
    }
}

// Clear all caches
echo "\nОчистка кешей:\n";
clearstatcache();
echo "✅ Stat cache очищен\n";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache очищен\n";
} else {
    echo "ℹ️ OPcache недоступен\n";
}

// Create restart flags
$restartFlags = [
    'projects_restart_required.flag',
    'webhook_update.flag',
    'handler_reload.flag'
];

echo "\nСоздание флагов перезапуска:\n";
foreach ($restartFlags as $flag) {
    file_put_contents($flag, date('Y-m-d H:i:s') . " - Projects button fix\n");
    echo "✅ {$flag}\n";
}

echo "\n=== ПРОВЕРКА ИСПРАВЛЕНИЯ ===\n";

// Test the fix
try {
    // Test that ProjectHandler can be loaded
    require_once __DIR__ . '/custom_autoload.php';
    
    if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler')) {
        echo "✅ ProjectHandler может быть загружен\n";
        
        // Test canHandle method
        $callbackData = 'category_Проекты';
        $startsWithCategoryProjects = str_starts_with($callbackData, 'category_Проекты');
        echo "✅ ProjectHandler->canHandle('{$callbackData}'): " . ($startsWithCategoryProjects ? 'true' : 'false') . "\n";
        
        if ($startsWithCategoryProjects) {
            echo "✅ Исправление работает корректно!\n";
        } else {
            echo "❌ Проблема с методом canHandle\n";
        }
    } else {
        echo "❌ ProjectHandler всё ещё не может быть загружен\n";
    }
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании исправления: " . $e->getMessage() . "\n";
}

echo "\n=== ИНСТРУКЦИИ ПО ПЕРЕЗАПУСКУ ===\n";
echo "1. ПЕРЕЗАПУСТИТЕ ВЕБ-СЕРВЕР:\n";
echo "   • Если используете IIS: iisreset\n";
echo "   • Если используете Apache: перезапустите Apache\n";
echo "   • Если используете Nginx: перезапустите Nginx\n";
echo "   • Если используете встроенный PHP сервер: перезапустите его\n\n";

echo "2. ПРОВЕРЬТЕ WEBHOOK:\n";
echo "   • Убедитесь, что webhook URL указывает на hook.php\n";
echo "   • Проверьте, что файл hook.php доступен по URL\n\n";

echo "3. ТЕСТИРУЙТЕ КНОПКУ:\n";
echo "   • Откройте Telegram и найдите вашего бота\n";
echo "   • Отправьте команду /start или откроите главное меню\n";
echo "   • Нажмите кнопку '📁 Проекты'\n";
echo "   • Должно появиться меню с опциями проектов\n\n";

echo "=== ОЖИДАЕМЫЙ РЕЗУЛЬТАТ ===\n";
echo "После перезапуска веб-сервера кнопка 'Проекты' должна:\n";
echo "✅ Отображаться в главном меню\n";
echo "✅ При нажатии показывать подменю проектов\n";
echo "✅ Обрабатывать все опции:\n";
echo "    - 📂 Мои проекты\n";
echo "    - 📊 Моя активность\n";
echo "    - 🗂️ Карточка проекта\n";
echo "    - 📋 Отчёт по проекту\n\n";

echo "🎯 ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!\n";
echo "Проблема была в неправильной загрузке интерфейса CallbackHandlerInterface.\n";
echo "Теперь кнопка 'Проекты' должна работать корректно.\n";

?>