<?php

echo "System Diagnostic for Telegram Bot\n";
echo "=================================\n\n";

// Проверка 1: Версия PHP
echo "=== PHP Version ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n\n";

// Проверка 2: Необходимые расширения
echo "=== Required Extensions ===\n";
$requiredExtensions = ['mysqli', 'json', 'mbstring', 'curl'];
foreach ($requiredExtensions as $extension) {
    if (extension_loaded($extension)) {
        echo "✓ {$extension} - Loaded\n";
    } else {
        echo "✗ {$extension} - Not loaded\n";
    }
}
echo "\n";

// Проверка 3: Настройки PHP
echo "=== PHP Settings ===\n";
echo "display_errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "log_errors: " . (ini_get('log_errors') ? 'On' : 'Off') . "\n";
echo "error_log: " . ini_get('error_log') . "\n\n";

// Проверка 4: Файлы конфигурации
echo "=== Configuration Files ===\n";
$configFiles = [
    'config.php',
    'hook.php',
    'composer.json'
];

foreach ($configFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ {$file} - Exists\n";
    } else {
        echo "✗ {$file} - Missing\n";
    }
}
echo "\n";

// Проверка 5: Структура команд
echo "=== Command Structure ===\n";
$commandsDir = __DIR__ . '/CustomCommands';
if (is_dir($commandsDir)) {
    $commandFiles = glob($commandsDir . '/*Command.php');
    echo "Found " . count($commandFiles) . " command files\n";
    
    // Проверяем наличие StartCommand
    if (file_exists($commandsDir . '/StartCommand.php')) {
        echo "✓ StartCommand.php - Exists\n";
    } else {
        echo "✗ StartCommand.php - Missing\n";
    }
    
    // Проверяем наличие TextmenuCommand
    if (file_exists($commandsDir . '/TextmenuCommand.php')) {
        echo "✓ TextmenuCommand.php - Exists\n";
    } else {
        echo "✗ TextmenuCommand.php - Missing\n";
    }
} else {
    echo "✗ CustomCommands directory not found\n";
}
echo "\n";

// Проверка 6: Сервисы
echo "=== Services ===\n";
$servicesDir = __DIR__ . '/CustomCommands/Services';
if (is_dir($servicesDir)) {
    $serviceFiles = glob($servicesDir . '/*.php');
    echo "Found " . count($serviceFiles) . " service files\n";
    
    $requiredServices = [
        'MessageService.php',
        'AdminService.php',
        'DatabaseService.php'
    ];
    
    foreach ($requiredServices as $service) {
        if (file_exists($servicesDir . '/' . $service)) {
            echo "✓ {$service} - Exists\n";
        } else {
            echo "✗ {$service} - Missing\n";
        }
    }
} else {
    echo "✗ Services directory not found\n";
}
echo "\n";

// Проверка 7: Подключение к базе данных (без использования mysqli)
echo "=== Database Connection Test ===\n";
$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
    echo "✓ config.php found\n";
    
    // Читаем конфигурацию напрямую
    $configContent = file_get_contents($configPath);
    
    // Ищем параметры базы данных
    if (preg_match('/\$host\s*=\s*[\'"]([^\'"]+)[\'"]/', $configContent, $hostMatches)) {
        echo "Database host found: " . $hostMatches[1] . "\n";
    } else {
        echo "? Database host not found in config\n";
    }
    
    if (preg_match('/\$username\s*=\s*[\'"]([^\'"]+)[\'"]/', $configContent, $userMatches)) {
        echo "Database username found: " . $userMatches[1] . "\n";
    } else {
        echo "? Database username not found in config\n";
    }
} else {
    echo "✗ config.php not found\n";
}
echo "\n";

// Проверка 8: Webhook
echo "=== Webhook Status ===\n";
$hookPath = __DIR__ . '/hook.php';
if (file_exists($hookPath)) {
    echo "✓ hook.php - Exists\n";
    
    // Проверяем содержимое
    $hookContent = file_get_contents($hookPath);
    if (strpos($hookContent, 'TelegramBot') !== false) {
        echo "✓ TelegramBot initialization found\n";
    } else {
        echo "? TelegramBot initialization not found\n";
    }
} else {
    echo "✗ hook.php - Missing\n";
}
echo "\n";

// Проверка 9: Логи ошибок
echo "=== Error Logs ===\n";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo "✓ Error log found: {$errorLog}\n";
    
    // Проверяем последние строки лога
    $lines = file($errorLog);
    $lastLines = array_slice($lines, -10);
    echo "Last 10 lines of error log:\n";
    foreach ($lastLines as $line) {
        if (strpos($line, 'Fatal error') !== false || strpos($line, 'Warning') !== false) {
            echo "  >>> " . trim($line) . "\n";
        }
    }
} else {
    echo "? Error log not configured or not found\n";
}
echo "\n";

// Проверка 10: Права доступа к файлам
echo "=== File Permissions ===\n";
$importantFiles = [
    'config.php',
    'hook.php',
    'CustomCommands/StartCommand.php',
    'CustomCommands/Services/MessageService.php'
];

foreach ($importantFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    if (file_exists($filePath)) {
        $perms = fileperms($filePath);
        echo "✓ {$file} - Permissions: " . substr(sprintf('%o', $perms), -4) . "\n";
    } else {
        echo "✗ {$file} - Not found\n";
    }
}
echo "\n";

echo "System diagnostic completed.\n";