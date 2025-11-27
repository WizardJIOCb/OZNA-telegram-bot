<?php
// Простой тест для проверки работоспособности бота

echo "=== ПРОСТОЙ ТЕСТ РАБОТОСПОСОБНОСТИ БОТА ===\n\n";

// Проверяем файл hook.php
echo "1. ПРОВЕРКА ФАЙЛА hook.php\n";
echo "===========================\n";

$hookFile = 'c:\ozna_bot\hook.php';
if (file_exists($hookFile)) {
    echo "✅ Файл hook.php найден\n";
    echo "   Размер: " . filesize($hookFile) . " байт\n";
    echo "   Последнее изменение: " . date('Y-m-d H:i:s', filemtime($hookFile)) . "\n";
} else {
    echo "❌ Файл hook.php НЕ НАЙДЕН!\n";
    exit;
}

echo "\n";

// Проверяем наличие логов после работы бота
echo "2. ПРОВЕРКА ЛОГОВ БОТА\n";
echo "======================\n";

$logFile = 'c:\ozna_bot\telegram_bot_error.log';
if (file_exists($logFile)) {
    $size = filesize($logFile);
    echo "✅ Лог файл telegram_bot_error.log найден (размер: {$size} байт)\n";
    
    if ($size > 0) {
        echo "   Последние записи в логе:\n";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -5);
        foreach ($lastLines as $line) {
            echo "   " . trim($line) . "\n";
        }
    }
} else {
    echo "❌ Лог файл telegram_bot_error.log не найден\n";
}

echo "\n";

// Проверяем конфигурацию базы данных
echo "3. ПРОВЕРКА ПОДКЛЮЧЕНИЯ К БАЗЕ ДАННЫХ\n";
echo "=====================================\n";

try {
    $dsn = "mysql:host=82.146.42.213;port=3306;dbname=tg_bot;charset=utf8mb4";
    $username = "WizardJIOCb";
    $password = "7qQ#28051961";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✅ Подключение к базе данных успешно\n";
    
    // Проверяем основные таблицы
    $tables = ['tabel_entries', 'user', 'project'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Таблица {$table} существует\n";
        } else {
            echo "❌ Таблица {$table} НЕ НАЙДЕНА!\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Ошибка подключения к базе данных: " . $e->getMessage() . "\n";
}

echo "\n";

// Проверяем работу основных компонентов
echo "4. ПРОВЕРКА ОСНОВНЫХ КОМПОНЕНТОВ\n";
echo "================================\n";

try {
    require_once 'custom_autoload.php';
    
    echo "✅ Автозагрузчик подключен\n";
    
    // Проверяем основные классы
    $classes = [
        'Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService',
        'Longman\TelegramBot\Commands\SystemCommands\Services\MessageService',
        'Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler'
    ];
    
    foreach ($classes as $class) {
        if (class_exists($class)) {
            echo "✅ Класс {$class} доступен\n";
        } else {
            echo "❌ Класс {$class} НЕ НАЙДЕН!\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка загрузки компонентов: " . $e->getMessage() . "\n";
}

echo "\n";

// Рекомендации
echo "5. ДИАГНОЗ И РЕКОМЕНДАЦИИ\n";
echo "=========================\n";

echo "ДИАГНОЗ: Логика обработки меню 'Отдел ШМР' в коде присутствует и выглядит корректной.\n\n";

echo "ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ:\n";
echo "1. Веб-сервер не запущен или работает некорректно\n";
echo "2. Изменения в коде не применились из-за кэширования\n";
echo "3. Ошибки в runtime, которые не логируются\n";
echo "4. Проблемы с обработкой callback'ов в Telegram API\n\n";

echo "ПЛАН ДЕЙСТВИЙ:\n";
echo "1. Перезапустите веб-сервер (Apache/Nginx)\n";
echo "2. Очистите все кэши (браузер, PHP OPcache если включен)\n";
echo "3. Попробуйте нажать кнопку 'Отдел ШМР' в боте\n";
echo "4. Сразу проверьте лог telegram_bot_error.log на новые записи\n";
echo "5. Если проблема сохраняется, попробуйте другие кнопки для сравнения\n\n";

echo "БЫСТРАЯ ПРОВЕРКА:\n";
echo "- Попробуйте сначала кнопку 'Табель', затем 'Отдел ШМР'\n";
echo "- Если ничего не происходит, проблема в обработке callback'ов\n";
echo "- Если появляется ошибка, проблема в коде выполнения\n";

?>