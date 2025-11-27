<?php
// Проверка логов бота

echo "Checking bot logs\n";
echo "===============\n";

// Проверяем, есть ли логи от бота
$logFiles = [
    __DIR__ . '/php-telegram-bot-debug.log',
    __DIR__ . '/php-telegram-bot-error.log',
    __DIR__ . '/php-telegram-bot-update.log'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        echo "✓ Log file exists: " . basename($logFile) . "\n";
        
        // Проверяем размер файла
        $size = filesize($logFile);
        echo "  File size: $size bytes\n";
        
        // Читаем последние 20 строк
        $lines = file($logFile);
        $lastLines = array_slice($lines, -20);
        echo "  Last 20 lines:\n";
        foreach ($lastLines as $line) {
            echo "    $line";
        }
        echo "\n";
    } else {
        echo "✗ Log file does not exist: " . basename($logFile) . "\n";
    }
}

// Проверяем общий лог PHP
$phpErrorLog = ini_get('error_log');
if ($phpErrorLog && file_exists($phpErrorLog)) {
    echo "✓ PHP error log exists: $phpErrorLog\n";
    
    // Читаем последние 20 строк
    $lines = file($phpErrorLog);
    $lastLines = array_slice($lines, -20);
    echo "  Last 20 lines:\n";
    foreach ($lastLines as $line) {
        echo "    $line";
    }
    echo "\n";
} else {
    echo "✗ PHP error log not configured or does not exist\n";
}

// Проверяем, есть ли ошибки в логах
$errorPatterns = [
    'Fatal error',
    'Parse error',
    'Warning',
    'Notice',
    'ProfileHandler',
    'CallbackqueryCommand'
];

echo "Checking for errors in logs:\n";
foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        foreach ($errorPatterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                echo "  Found '$pattern' in " . basename($logFile) . "\n";
            }
        }
    }
}

echo "\nLog check completed.\n";