<?php
// Тест функциональности бота

echo "Testing bot functionality\n";
echo "======================\n";

// Проверяем, существуют ли необходимые файлы
$requiredFiles = [
    __DIR__ . '/hook.php',
    __DIR__ . '/config.php',
    __DIR__ . '/CustomCommands/CallbackqueryCommand.php',
    __DIR__ . '/CustomCommands/TextmenuCommand.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "✓ File exists: " . str_replace(__DIR__, '', $file) . "\n";
    } else {
        echo "✗ File missing: " . str_replace(__DIR__, '', $file) . "\n";
    }
}

// Проверяем содержимое hook.php
$hookContent = file_get_contents(__DIR__ . '/hook.php');
if (strpos($hookContent, 'CallbackqueryCommand') !== false) {
    echo "✓ hook.php references CallbackqueryCommand\n";
} else {
    echo "✗ hook.php does not reference CallbackqueryCommand\n";
}

// Проверяем, правильно ли подключаются команды
if (strpos($hookContent, "addCommandsPaths") !== false) {
    echo "✓ hook.php adds command paths\n";
} else {
    echo "✗ hook.php does not add command paths\n";
}

// Проверяем, есть ли в CallbackqueryCommand.php правильное имя команды
$callbackCommandContent = file_get_contents(__DIR__ . '/CustomCommands/CallbackqueryCommand.php');
if (strpos($callbackCommandContent, "protected \$name = 'callbackquery'") !== false) {
    echo "✓ CallbackqueryCommand has correct name\n";
} else {
    echo "✗ CallbackqueryCommand does not have correct name\n";
}

// Проверяем, зарегистрирован ли ProfileHandler
if (strpos($callbackCommandContent, "ProfileHandler") !== false) {
    echo "✓ CallbackqueryCommand registers ProfileHandler\n";
} else {
    echo "✗ CallbackqueryCommand does not register ProfileHandler\n";
}

echo "\nTest completed.\n";