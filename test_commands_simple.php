<?php

echo "Testing commands functionality (simplified)...\n\n";

// Тест 1: Проверка существующих команд
echo "=== Check 1: Existing Commands ===\n";
$commandsDir = __DIR__ . '/CustomCommands';
if (is_dir($commandsDir)) {
    $files = scandir($commandsDir);
    $commandFiles = [];
    
    foreach ($files as $file) {
        if (strpos($file, 'Command.php') !== false) {
            $commandFiles[] = $file;
            echo "✓ Found command: {$file}\n";
        }
    }
    
    echo "Total command files found: " . count($commandFiles) . "\n\n";
} else {
    echo "✗ CustomCommands directory not found\n\n";
}

// Тест 2: Проверка структуры команд
echo "=== Check 2: Command Structure ===\n";
$requiredCommands = [
    'StartCommand.php',
    'TextmenuCommand.php'
];

foreach ($requiredCommands as $command) {
    $commandPath = __DIR__ . '/CustomCommands/' . $command;
    if (file_exists($commandPath)) {
        echo "✓ {$command} exists\n";
        
        // Проверяем содержимое файла
        $content = file_get_contents($commandPath);
        if (strpos($content, 'class') !== false) {
            echo "  ✓ Class definition found\n";
        } else {
            echo "  ✗ Class definition not found\n";
        }
        
        if (strpos($content, 'execute()') !== false) {
            echo "  ✓ execute() method found\n";
        } else {
            echo "  ✗ execute() method not found\n";
        }
    } else {
        echo "✗ {$command} not found\n";
    }
    echo "\n";
}

// Тест 3: Проверка сервисов
echo "=== Check 3: Services ===\n";
$servicesDir = __DIR__ . '/CustomCommands/Services';
if (is_dir($servicesDir)) {
    $serviceFiles = scandir($servicesDir);
    
    foreach ($serviceFiles as $file) {
        if (strpos($file, '.php') !== false) {
            echo "✓ Found service: {$file}\n";
        }
    }
    
    echo "Total service files found: " . (count($serviceFiles) - 2) . "\n\n"; // -2 for . and ..
} else {
    echo "✗ Services directory not found\n\n";
}

// Тест 4: Проверка подключения сервисов (без базы данных)
echo "=== Check 4: Service Connections (simplified) ===\n";
try {
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    // Создаем экземпляр сервиса сообщений
    $messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "✓ MessageService created successfully\n";
    
    // Проверяем методы сервиса сообщений
    if (method_exists($messageService, 'createMainMenuReplyKeyboard')) {
        echo "✓ createMainMenuReplyKeyboard method exists\n";
    } else {
        echo "✗ createMainMenuReplyKeyboard method not found\n";
    }
    
    if (method_exists($messageService, 'sendMessageWithReplyKeyboard')) {
        echo "✓ sendMessageWithReplyKeyboard method exists\n";
    } else {
        echo "✗ sendMessageWithReplyKeyboard method not found\n";
    }
    
} catch (Exception $e) {
    echo "✗ Service creation failed: " . $e->getMessage() . "\n";
}

echo "\nCommands test completed.\n";