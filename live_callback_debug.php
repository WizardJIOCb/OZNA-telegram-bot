<?php
/**
 * Live callback debugging - captures real callback data during Telegram testing
 * This will help identify what's actually happening vs what we expect
 */

echo "=== СИСТЕМА ЖИВОЙ ОТЛАДКИ CALLBACK'ОВ ===\n\n";

// Add logging to TabelHandler to capture real callback data
$handlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
$handlerContent = file_get_contents($handlerPath);

// Check if we already have debug logging
if (strpos($handlerContent, 'LIVE_DEBUG_CALLBACK') !== false) {
    echo "✅ Система отладки уже активна в TabelHandler.php\n";
} else {
    echo "⚠️ Добавляю систему живой отладки в TabelHandler.php...\n";
    
    // Find the handle method and add debug logging at the very beginning
    $handleMethodPos = strpos($handlerContent, 'public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse');
    
    if ($handleMethodPos !== false) {
        // Find the opening brace after the method signature
        $bracePos = strpos($handlerContent, '{', $handleMethodPos);
        
        if ($bracePos !== false) {
            $debugCode = "\n        // LIVE_DEBUG_CALLBACK - Capture real callback data during testing\n";
            $debugCode .= "        \$actualCallbackData = \$callbackQuery->getData();\n";
            $debugCode .= "        error_log(\"🔴 LIVE_DEBUG_CALLBACK: Raw callback data: '{\$actualCallbackData}'\");\n";
            $debugCode .= "        error_log(\"🔴 LIVE_DEBUG_CALLBACK: Extracted category: '{\$category}'\");\n";
            $debugCode .= "        error_log(\"🔴 LIVE_DEBUG_CALLBACK: User ID: \" . \$callbackQuery->getFrom()->getId());\n";
            $debugCode .= "        error_log(\"🔴 LIVE_DEBUG_CALLBACK: Chat ID: \" . \$callbackQuery->getMessage()->getChat()->getId());\n";
            $debugCode .= "        \n";
            
            // Insert the debug code right after the opening brace
            $modifiedContent = substr_replace($handlerContent, $debugCode, $bracePos + 1, 0);
            
            file_put_contents($handlerPath, $modifiedContent);
            echo "✅ Система живой отладки добавлена в TabelHandler.php\n";
        }
    }
}

echo "\n📋 ИНСТРУКЦИИ ДЛЯ ЖИВОГО ТЕСТИРОВАНИЯ:\n";
echo "1. Откройте отдельное окно терминала\n";
echo "2. Запустите команду: tail -f /путь/к/логам/telegram_bot.log\n";
echo "   (или где бы ни хранились логи вашего бота)\n";
echo "3. В Телеграме проведите тест:\n";
echo "   - Отдел ШМР → Проектная деятельность → любая активность\n";
echo "   - Нажмите кнопку 'Да'\n";
echo "4. Найдите строки с '🔴 LIVE_DEBUG_CALLBACK' в логах\n";
echo "5. Скопируйте эти строки и покажите мне\n\n";

echo "⚠️ АЛЬТЕРНАТИВНЫЙ СПОСОБ ПОЛУЧЕНИЯ ЛОГОВ:\n";
echo "Если логи недоступны, можно создать файл debug_log.txt:\n\n";

// Add file logging as backup
$fileLogCode = '
        // LIVE_DEBUG_CALLBACK - Alternative file logging
        $debugInfo = date("Y-m-d H:i:s") . " | Raw: {$actualCallbackData} | Category: {$category} | UserID: " . $callbackQuery->getFrom()->getId() . "\n";
        file_put_contents(__DIR__ . "/../../debug_callback_log.txt", $debugInfo, FILE_APPEND | LOCK_EX);
';

if (strpos($handlerContent, 'debug_callback_log.txt') === false) {
    // Find where to insert file logging
    $liveDebugPos = strpos($handlerContent, 'LIVE_DEBUG_CALLBACK: Chat ID');
    if ($liveDebugPos !== false) {
        $lineEndPos = strpos($handlerContent, "\n", $liveDebugPos);
        if ($lineEndPos !== false) {
            $modifiedContent = file_get_contents($handlerPath);
            $modifiedContent = substr_replace($modifiedContent, $fileLogCode, $lineEndPos, 0);
            file_put_contents($handlerPath, $modifiedContent);
            echo "✅ Альтернативное логирование в файл добавлено\n";
        }
    }
}

echo "\nТеперь проведите тест в Телеграме, и мы увидим реальные данные!\n";
echo "Если используется файловое логирование, проверьте файл: debug_callback_log.txt\n";

echo "\n=== ГОТОВО К ЖИВОМУ ТЕСТИРОВАНИЮ ===\n";
?>