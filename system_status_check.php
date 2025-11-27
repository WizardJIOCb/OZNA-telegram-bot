<?php
// Скрипт для проверки состояния системы

echo "=== ПРОВЕРКА СОСТОЯНИЯ СИСТЕМЫ ===\n\n";

// 1. Проверка основных файлов
echo "1. ПРОВЕРКА ФАЙЛОВ\n";
echo "==================\n";

$requiredFiles = [
    'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php',
    'c:\ozna_bot\CustomCommands\Services\MessageService.php',
    'c:\ozna_bot\webhook.php',
    'c:\ozna_bot\hook.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        $modified = date('Y-m-d H:i:s', filemtime($file));
        echo "✅ {$file} (размер: {$size} байт, изменен: {$modified})\n";
    } else {
        echo "❌ {$file} - НЕ НАЙДЕН!\n";
    }
}

echo "\n";

// 2. Проверка лог-файлов
echo "2. ПРОВЕРКА ЛОГОВ\n";
echo "=================\n";

$logFiles = [
    'c:\ozna_bot\error.log',
    'c:\ozna_bot\debug.log',
    'C:\xampp\apache\logs\error.log',
    'C:\xampp\php\logs\php_error_log'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $size = filesize($logFile);
        $modified = date('Y-m-d H:i:s', filemtime($logFile));
        echo "✅ {$logFile} (размер: {$size} байт, последнее изменение: {$modified})\n";
        
        // Показать последние строки лога
        if ($size > 0) {
            $lines = file($logFile);
            $lastLines = array_slice($lines, -3);
            echo "   Последние записи:\n";
            foreach ($lastLines as $line) {
                echo "   " . trim($line) . "\n";
            }
        }
    } else {
        echo "❌ {$logFile} - не найден\n";
    }
    echo "\n";
}

// 3. Проверка функции в TabelHandler
echo "3. ПРОВЕРКА ФУНКЦИИ handleSupervisionDateSelection\n";
echo "==================================================\n";

$tabelHandlerFile = 'c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php';
if (file_exists($tabelHandlerFile)) {
    $content = file_get_contents($tabelHandlerFile);
    
    // Поиск функции handleSupervisionDateSelection
    if (strpos($content, 'function handleSupervisionDateSelection') !== false) {
        echo "✅ Функция handleSupervisionDateSelection найдена\n";
        
        // Проверяем логику обработки Шеф-монтажа
        if (strpos($content, "category === 'Табель_Шеф_монтаж'") !== false) {
            echo "✅ Логика обработки 'Табель_Шеф_монтаж' найдена\n";
        } else {
            echo "❌ Логика обработки 'Табель_Шеф_монтаж' НЕ НАЙДЕНА!\n";
        }
        
        if (strpos($content, 'handleSupervisionDateSelection') !== false) {
            echo "✅ Вызов handleSupervisionDateSelection найден\n";
        } else {
            echo "❌ Вызов handleSupervisionDateSelection НЕ НАЙДЕН!\n";
        }
    } else {
        echo "❌ Функция handleSupervisionDateSelection НЕ НАЙДЕНА!\n";
    }
} else {
    echo "❌ Файл TabelHandler.php не найден!\n";
}

echo "\n";

// 4. Проверка веб-сервера
echo "4. ПРОВЕРКА ВЕБ-СЕРВЕРА\n";
echo "=======================\n";

// Проверяем, запущен ли веб-сервер
$testUrl = 'http://localhost/ozna_bot/webhook.php';
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($testUrl, false, $context);
if ($response !== false) {
    echo "✅ Веб-сервер отвечает на {$testUrl}\n";
    echo "   Ответ: " . substr($response, 0, 100) . "...\n";
} else {
    echo "❌ Веб-сервер НЕ отвечает на {$testUrl}\n";
    echo "   Возможно, веб-сервер не запущен или URL неправильный\n";
}

echo "\n";

// 5. Рекомендации
echo "5. РЕКОМЕНДАЦИИ ДЛЯ ИСПРАВЛЕНИЯ\n";
echo "===============================\n";

echo "Если меню 'Отдел ШМР' не открывается, попробуйте:\n\n";

echo "1. ПЕРЕЗАПУСК ВЕБ-СЕРВЕРА:\n";
echo "   - Остановите Apache/Nginx\n";
echo "   - Подождите 5 секунд\n";
echo "   - Запустите Apache/Nginx снова\n\n";

echo "2. ОЧИСТКА КЭША:\n";
echo "   - Очистите кэш браузера\n";
echo "   - Перезапустите Telegram на устройстве\n\n";

echo "3. ПРОВЕРКА ЛОГОВ:\n";
echo "   - Откройте бота в Telegram\n";
echo "   - Нажмите кнопку 'Отдел ШМР'\n";
echo "   - Сразу проверьте логи ошибок\n\n";

echo "4. ТЕСТИРОВАНИЕ:\n";
echo "   - Попробуйте другие кнопки (Отдел ПНР, Мобильная бригада)\n";
echo "   - Сравните их поведение с кнопкой ШМР\n\n";

echo "5. ПРОВЕРКА БАЗЫ ДАННЫХ:\n";
echo "   - Убедитесь, что база данных доступна\n";
echo "   - Проверьте подключение к БД\n";

?>