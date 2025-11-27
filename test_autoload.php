<?php
// Test autoloading

echo "=== ТЕСТИРОВАНИЕ АВТОЗАГРУЗКИ ===\n";

// Include the autoloader
require_once __DIR__ . '/custom_autoload.php';

echo "1. Проверка автозагрузки CallbackHandlerInterface\n";
if (interface_exists('Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface')) {
    echo "✅ CallbackHandlerInterface существует\n";
} else {
    echo "❌ CallbackHandlerInterface НЕ НАЙДЕН\n";
    
    // Try to manually load it
    $interfacePath = __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
    if (file_exists($interfacePath)) {
        require_once $interfacePath;
        echo "Попытка ручной загрузки...\n";
        if (interface_exists('Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface')) {
            echo "✅ CallbackHandlerInterface загружен вручную\n";
        } else {
            echo "❌ CallbackHandlerInterface всё равно НЕ НАЙДЕН\n";
        }
    } else {
        echo "❌ Файл интерфейса не найден: {$interfacePath}\n";
    }
}

echo "\n2. Проверка автозагрузки ProjectHandler\n";
if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler')) {
    echo "✅ ProjectHandler существует\n";
} else {
    echo "❌ ProjectHandler НЕ НАЙДЕН\n";
    
    // Try to manually load it
    $handlerPath = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
    if (file_exists($handlerPath)) {
        require_once $handlerPath;
        echo "Попытка ручной загрузки...\n";
        if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler')) {
            echo "✅ ProjectHandler загружен вручную\n";
        } else {
            echo "❌ ProjectHandler всё равно НЕ НАЙДЕН\n";
        }
    } else {
        echo "❌ Файл обработчика не найден: {$handlerPath}\n";
    }
}

echo "\n3. Тестирование пространства имён\n";
echo "Текущее пространство имён: " . __NAMESPACE__ . "\n";

// Test the namespace resolution
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

echo "Импортированные классы:\n";
echo "- CallbackHandlerInterface: " . (interface_exists(CallbackHandlerInterface::class) ? "OK" : "FAIL") . "\n";
echo "- ProjectHandler: " . (class_exists(ProjectHandler::class) ? "OK" : "FAIL") . "\n";

?>