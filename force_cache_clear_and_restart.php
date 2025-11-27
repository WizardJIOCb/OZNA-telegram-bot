<?php

echo "=== FORCE CACHE CLEAR AND BOT RESTART ===\n\n";

// Step 1: Clear OPcache if available
echo "🔄 ШАГ 1: Очистка OPcache\n";
echo "==========================\n";

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ OPcache успешно очищен\n";
    } else {
        echo "❌ Не удалось очистить OPcache\n";
    }
} else {
    echo "ℹ️ OPcache не активен\n";
}

// Step 2: Check if realpath cache is enabled and clear it
echo "\n🔄 ШАГ 2: Очистка realpath cache\n";
echo "==================================\n";

if (function_exists('clearstatcache')) {
    clearstatcache(true);
    echo "✅ Realpath cache очищен\n";
} else {
    echo "❌ clearstatcache недоступен\n";
}

// Step 3: Force reload of specific files
echo "\n🔄 ШАГ 3: Принудительная перезагрузка ключевых файлов\n";
echo "=====================================================\n";

$key_files = [
    'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php',
    'c:\ozna_bot\CustomCommands\Services\MessageService.php',
    'c:\ozna_bot\CustomCommands\Services\TabelEntry.php'
];

foreach ($key_files as $file) {
    if (file_exists($file)) {
        $timestamp = filemtime($file);
        $size = filesize($file);
        echo "✅ {$file}\n";
        echo "   Размер: {$size} байт, Изменен: " . date('Y-m-d H:i:s', $timestamp) . "\n";
        
        // Force file to be re-read by checking its hash
        $hash = md5_file($file);
        echo "   MD5: {$hash}\n";
    } else {
        echo "❌ Файл не найден: {$file}\n";
    }
}

// Step 4: Test the specific callback mapping
echo "\n🔄 ШАГ 4: Тестирование callback mapping\n";
echo "=====================================\n";

try {
    // Load the interface first
    require_once 'c:\ozna_bot\CustomCommands\Services\CallbackHandlerInterface.php';
    // Load other dependencies
    require_once 'c:\ozna_bot\CustomCommands\Services\DatabaseService.php';
    require_once 'c:\ozna_bot\CustomCommands\Services\MessageService.php';
    require_once 'c:\ozna_bot\CustomCommands\Services\ProjectSelector.php';
    require_once 'c:\ozna_bot\CustomCommands\Services\TabelEntry.php';
    require_once 'c:\ozna_bot\CustomCommands\Services\AdminService.php';
    
    // Try to load TabelHandler and test the specific method
    require_once 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
    
    echo "✅ TabelHandler загружен успешно\n";
    
    // Use reflection to check if the method exists
    $reflection = new ReflectionClass('Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler');
    
    if ($reflection->hasMethod('extractCategoryFromCallback')) {
        echo "✅ Метод extractCategoryFromCallback найден\n";
        
        // Test the callback mapping - skip creating instance due to dependencies
        echo "⚠️ Skipping instance creation due to constructor dependencies\n";
        echo "Instead, let's check the method source code directly:\n";
        
        $source = file_get_contents('c:\\ozna_bot\\CustomCommands\\Handlers\\TabelHandler.php');
        if (strpos($source, "'cat_ШМР_ПровМонтПлощ' => 'Табель_ПроверкаМонтажнойПлощадки'") !== false) {
            echo "✅ Callback mapping найден в коде!\n";
        } else {
            echo "❌ Callback mapping НЕ найден в коде!\n";
        }
        
    } else {
        echo "❌ Метод extractCategoryFromCallback не найден\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Step 5: Create restart flag
echo "\n🔄 ШАГ 5: Создание флага рестарта\n";
echo "=================================\n";

$restart_flag = 'c:\ozna_bot\force_reload_' . time() . '.flag';
if (file_put_contents($restart_flag, date('Y-m-d H:i:s'))) {
    echo "✅ Флаг рестарта создан: {$restart_flag}\n";
} else {
    echo "❌ Не удалось создать флаг рестарта\n";
}

echo "\n✅ ОЧИСТКА ЗАВЕРШЕНА\n";
echo "====================\n";
echo "Рекомендации:\n";
echo "1. Попробуйте сейчас нажать кнопку 'Проверка монтажной площадки'\n";
echo "2. Если проблема сохраняется, возможно, нужно перезапустить веб-сервер\n";
echo "3. Проверьте логи в telegram_bot_error.log\n";

?>