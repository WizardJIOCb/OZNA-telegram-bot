<?php
// Тестовый скрипт для отладки callback запросов

// Включаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_callback_test.log');

echo "Debug callback test script\n";
echo "========================\n";

// Проверяем, существуют ли нужные файлы
$requiredFiles = [
    __DIR__ . '/CustomCommands/CallbackqueryCommand.php',
    __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php',
    __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ File exists: $file\n";
    } else {
        echo "✗ File missing: $file\n";
    }
}

// Проверяем кодировку файла ProfileHandler
$profileHandlerFile = __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';
if (file_exists($profileHandlerFile)) {
    $content = file_get_contents($profileHandlerFile);
    echo "\nProfileHandler file encoding check:\n";
    echo "File size: " . strlen($content) . " bytes\n";
    echo "MB encoding check: " . (mb_check_encoding($content, 'UTF-8') ? 'UTF-8 valid' : 'UTF-8 invalid') . "\n";
    
    // Проверяем наличие ключевых строк
    if (strpos($content, 'Профиль_Просмотр') !== false) {
        echo "✓ Found 'Профиль_Просмотр' in ProfileHandler\n";
    } else {
        echo "✗ 'Профиль_Просмотр' not found in ProfileHandler\n";
    }
    
    if (strpos($content, 'category_Профиль_Просмотр') !== false) {
        echo "✓ Found 'category_Профиль_Просмотр' in ProfileHandler\n";
    } else {
        echo "✗ 'category_Профиль_Просмотр' not found in ProfileHandler\n";
    }
}

echo "\nTest completed.\n";