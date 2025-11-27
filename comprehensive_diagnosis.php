<?php

/**
 * Comprehensive Manual Input Diagnostics
 * Полная диагностика проблемы с ручным вводом ID группы
 */

echo "=== ПОЛНАЯ ДИАГНОСТИКА ПРОБЛЕМЫ ===\n\n";

// Проверяем все компоненты системы
require_once 'config.php';

echo "1. Проверка файловой структуры...\n";

$requiredFiles = [
    'CustomCommands/Handlers/ProjectHandler.php' => 'ProjectHandler',
    'CustomCommands/GenericmessageCommand.php' => 'GenericmessageCommand',
    'CustomCommands/Services/ManualInputStateManager.php' => 'ManualInputStateManager',
    'CustomCommands/Services/DatabaseService.php' => 'DatabaseService'
];

foreach ($requiredFiles as $file => $name) {
    $fullPath = __DIR__ . '/' . $file;
    $exists = file_exists($fullPath);
    echo "   {$name}: " . ($exists ? "✅" : "❌") . "\n";
    
    if ($exists) {
        $size = filesize($fullPath);
        $modified = date('Y-m-d H:i:s', filemtime($fullPath));
        echo "     Размер: {$size} байт, Изменен: {$modified}\n";
    }
}

echo "\n2. Тестирование системы ручного ввода...\n";

try {
    require_once 'CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    // Используем реальный ID пользователя из логов
    $realUserId = 5261465966;
    $testProject = "ДИАГНОСТИКА";
    
    echo "   Создание состояния...\n";
    $stateManager->clearState($realUserId); // Очистим сначала
    
    $setResult = $stateManager->setState($realUserId, 'group_id', $testProject);
    echo "     Установка: " . ($setResult ? "✅ УСПЕХ" : "❌ ОШИБКА") . "\n";
    
    $detectResult = $stateManager->isAwaitingGroupInput($realUserId);
    echo "     Обнаружение: " . ($detectResult ? "✅ РАБОТАЕТ" : "❌ НЕ РАБОТАЕТ") . "\n";
    
    $projectCode = $stateManager->getProjectCode($realUserId);
    echo "     Получение проекта: " . ($projectCode === $testProject ? "✅ ПРАВИЛЬНО" : "❌ НЕПРАВИЛЬНО") . "\n";
    
    // Проверим файл состояния
    $stateDir = __DIR__ . '/data/manual_input_states';
    $stateFile = $stateDir . "/user_{$realUserId}.json";
    
    echo "   Проверка файла состояния...\n";
    echo "     Путь: {$stateFile}\n";
    echo "     Существует: " . (file_exists($stateFile) ? "✅ ДА" : "❌ НЕТ") . "\n";
    
    if (file_exists($stateFile)) {
        $content = file_get_contents($stateFile);
        $data = json_decode($content, true);
        echo "     Содержимое: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    // Очистим тестовое состояние
    $stateManager->clearState($realUserId);
    echo "     Очистка: ✅ ВЫПОЛНЕНО\n";
    
} catch (Exception $e) {
    echo "   ❌ ОШИБКА: " . $e->getMessage() . "\n";
}

echo "\n3. Проверка ProjectHandler...\n";

try {
    require_once 'CustomCommands/Services/AdminService.php';
    require_once 'CustomCommands/Services/MessageService.php';
    require_once 'CustomCommands/Services/DatabaseService.php';
    require_once 'CustomCommands/Handlers/ProjectHandler.php';
    
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler($adminService, $messageService);
    
    echo "   ProjectHandler создан: ✅ УСПЕХ\n";
    
    // Проверим метод getUserControlProjects
    $reflection = new ReflectionClass($projectHandler);
    $hasMethod = $reflection->hasMethod('getUserControlProjects');
    echo "   Метод getUserControlProjects: " . ($hasMethod ? "✅ ЕСТЬ" : "❌ НЕТ") . "\n";
    
    if ($hasMethod) {
        $method = $reflection->getMethod('getUserControlProjects');
        $method->setAccessible(true);
        
        // Попробуем вызвать метод
        try {
            $result = $method->invoke($projectHandler, $realUserId);
            echo "   Вызов метода: ✅ РАБОТАЕТ\n";
            echo "   Результат: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
        } catch (Exception $e) {
            echo "   Вызов метода: ❌ ОШИБКА - " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ ОШИБКА ProjectHandler: " . $e->getMessage() . "\n";
}

echo "\n4. Проверка GenericmessageCommand...\n";

try {
    require_once 'CustomCommands/GenericmessageCommand.php';
    echo "   GenericmessageCommand загружен: ✅ УСПЕХ\n";
    
    // Проверим наличие нужных методов
    $reflection = new ReflectionClass('\\Longman\\TelegramBot\\Commands\\SystemCommands\\GenericmessageCommand');
    
    $methods = [
        'handleManualGroupInputFromFile' => 'Обработчик группового ввода',
        'handleManualChatInputFromFile' => 'Обработчик чатового ввода'
    ];
    
    foreach ($methods as $methodName => $description) {
        $hasMethod = $reflection->hasMethod($methodName);
        echo "   {$description}: " . ($hasMethod ? "✅ ЕСТЬ" : "❌ НЕТ") . "\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ ОШИБКА GenericmessageCommand: " . $e->getMessage() . "\n";
}

echo "\n5. Симуляция полного процесса...\n";

try {
    // Устанавливаем состояние (как ProjectHandler)
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    $stateManager->setState($realUserId, 'group_id', 'ОИ789');
    echo "   Состояние установлено как в ProjectHandler: ✅\n";
    
    // Проверяем обнаружение (как GenericmessageCommand)
    $isAwaiting = $stateManager->isAwaitingGroupInput($realUserId);
    echo "   GenericmessageCommand может обнаружить: " . ($isAwaiting ? "✅ ДА" : "❌ НЕТ") . "\n";
    
    // Симулируем ввод пользователя
    $testGroupId = "-1001234567890";
    $isValidFormat = preg_match('/^-100\\d+$/', $testGroupId);
    echo "   Валидация ID: " . ($isValidFormat ? "✅ ВАЛИДЕН" : "❌ НЕВАЛИДЕН") . "\n";
    
    // Очищаем состояние
    $stateManager->clearState($realUserId);
    echo "   Состояние очищено: ✅\n";
    
} catch (Exception $e) {
    echo "   ❌ ОШИБКА в симуляции: " . $e->getMessage() . "\n";
}

echo "\n6. Анализ логов...\n";

$logFile = __DIR__ . '/telegram_bot_error.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    
    // Ищем признаки работы исправленной версии
    $hasFixedVersion = strpos($content, 'FIXED VERSION 2025-09-17') !== false;
    echo "   Лог исправленной версии: " . ($hasFixedVersion ? "✅ НАЙДЕН" : "❌ НЕ НАЙДЕН") . "\n";
    
    // Ищем DEBUG_MANUAL_INPUT записи
    $hasDebugLogs = strpos($content, 'DEBUG_MANUAL_INPUT') !== false;
    echo "   Debug логи ручного ввода: " . ($hasDebugLogs ? "✅ ЕСТЬ" : "❌ НЕТ") . "\n";
    
    // Ищем последние ошибки ProjectHandler
    $hasProjectHandlerErrors = strpos($content, 'getUserControlProjects') !== false;
    echo "   Ошибки ProjectHandler: " . ($hasProjectHandlerErrors ? "❌ ЕСТЬ" : "✅ НЕТ") . "\n";
    
} else {
    echo "   ❌ Лог файл не найден\n";
}

echo "\n=== ДИАГНОЗ ===\n\n";

echo "Создайте файл test_manual_input.php со следующим содержимым:\n\n";
echo "<?php\n";
echo "// Тест для пользователя - поместите этот файл рядом с hook.php\n";
echo "require_once 'config.php';\n";
echo "require_once 'CustomCommands/Services/ManualInputStateManager.php';\n";
echo "\$manager = new \\Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\ManualInputStateManager();\n";
echo "\$manager->setState({$realUserId}, 'group_id', 'ТЕСТ');\n";
echo "echo 'Состояние установлено. Попробуйте ввести ID группы в боте.';\n";
echo "?>\n\n";

echo "Запустите этот скрипт на сервере, а затем попросите пользователя ввести ID группы.\n";
echo "Если это не поможет, проблема может быть в том, что:\n";
echo "1. Файлы на реальном сервере не обновились\n";
echo "2. Есть PHP кэширование (OpCache)\n";
echo "3. Пользователь заходит в неправильное меню\n\n";

echo "=== Диагностика завершена ===\n";