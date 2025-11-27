<?php
// Тест логирования

echo "Testing logging\n";
echo "=============\n";

// Включаем логирование ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/test_logging.log');

// Пишем в лог
error_log("Test log entry from test_logging.php");

// Проверяем, существует ли файл лога
if (file_exists(__DIR__ . '/test_logging.log')) {
    echo "✓ Log file exists\n";
    
    // Читаем последние строки лога
    $logContent = file_get_contents(__DIR__ . '/test_logging.log');
    if (strpos($logContent, 'Test log entry from test_logging.php') !== false) {
        echo "✓ Log entry found\n";
    } else {
        echo "✗ Log entry not found\n";
    }
} else {
    echo "✗ Log file does not exist\n";
}

// Проверяем, есть ли логи от бота
$botLogFiles = [
    __DIR__ . '/php-telegram-bot-debug.log',
    __DIR__ . '/php-telegram-bot-error.log',
    __DIR__ . '/php-telegram-bot-update.log'
];

foreach ($botLogFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "✓ Bot log file exists: " . basename($logFile) . "\n";
        
        // Проверяем размер файла
        $size = filesize($logFile);
        echo "  File size: $size bytes\n";
        
        // Читаем последние 10 строк
        $lines = file($logFile);
        $lastLines = array_slice($lines, -10);
        echo "  Last 10 lines:\n";
        foreach ($lastLines as $line) {
            echo "    $line";
        }
    } else {
        echo "✗ Bot log file does not exist: " . basename($logFile) . "\n";
    }
}

echo "\nTest completed.\n";