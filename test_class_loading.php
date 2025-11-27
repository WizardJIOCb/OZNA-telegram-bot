<?php
// Тест для проверки загрузки классов

echo "Testing class loading\n";
echo "===================\n";

// Подключаем автозагрузчик, если он есть
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ Composer autoload included\n";
} else {
    echo "✗ Composer autoload not found\n";
}

// Подключаем необходимые файлы вручную
$requiredFiles = [
    __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php',
    __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php',
    __DIR__ . '/CustomCommands/Services/MessageService.php',
    __DIR__ . '/CustomCommands/Services/AdminService.php',
    __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php',
    __DIR__ . '/CustomCommands/CallbackqueryCommand.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
        echo "✓ File included: " . basename($file) . "\n";
    } else {
        echo "✗ File missing: " . basename($file) . "\n";
    }
}

// Проверяем, существуют ли классы
$classes = [
    'Longman\TelegramBot\Commands\SystemCommands\CallbackqueryCommand',
    'Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler',
    'Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✓ Class exists: $class\n";
    } else {
        echo "✗ Class not found: $class\n";
    }
}

// Проверяем, можно ли создать экземпляры классов
try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "✓ MessageService instance created\n";
} catch (Exception $e) {
    echo "✗ Error creating MessageService: " . $e->getMessage() . "\n";
}

try {
    $profileHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler($messageService);
    echo "✓ ProfileHandler instance created\n";
} catch (Exception $e) {
    echo "✗ Error creating ProfileHandler: " . $e->getMessage() . "\n";
}

try {
    $callbackHandlerFactory = new \Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory();
    echo "✓ CallbackHandlerFactory instance created\n";
} catch (Exception $e) {
    echo "✗ Error creating CallbackHandlerFactory: " . $e->getMessage() . "\n";
}

try {
    $callbackHandlerFactory->registerHandler($profileHandler);
    echo "✓ ProfileHandler registered in CallbackHandlerFactory\n";
} catch (Exception $e) {
    echo "✗ Error registering ProfileHandler: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";