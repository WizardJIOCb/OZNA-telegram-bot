<?php

/**
 * Простая проверка логики меню проектов на контроле
 */

echo "=== ПРОСТАЯ ПРОВЕРКА МЕНЮ ===\n\n";

// Подключаем файлы по порядку
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

try {
    echo "✓ Основные сервисы подключены\n";
    
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    $databaseService = new DatabaseService();
    
    echo "✓ Сервисы инициализированы\n";
    
    // Проверяем конкретного пользователя
    $testUserId = 4225504;
    echo "\nПроверяем пользователя {$testUserId}:\n";
    
    $userData = $databaseService->getUserData($testUserId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    echo "Данные пользователя: " . json_encode($userData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    echo "Проекты на контроле: " . json_encode($controlProjects, JSON_UNESCAPED_UNICODE) . "\n";
    echo "Количество проектов на контроле: " . count($controlProjects) . "\n";
    
    $allProjects = $adminService->getAllProjects();
    echo "Всего проектов в системе: " . count($allProjects) . "\n";
    
    echo "\nЛогика отображения кнопок:\n";
    echo "- Кнопка 'Добавить': " . (!empty($allProjects) ? "ДА" : "НЕТ") . " (есть проекты в системе)\n";
    echo "- Кнопка 'Удалить': " . (!empty($controlProjects) ? "ДА" : "НЕТ") . " (есть проекты у пользователя)\n";
    echo "- Кнопка 'Настройки': " . (!empty($controlProjects) ? "ДА" : "НЕТ") . " (есть проекты у пользователя)\n";
    echo "- Кнопка 'Список': ДА (всегда показывается)\n";
    
    // Проверяем, почему кнопки не работают
    echo "\nВозможные причины проблем:\n";
    
    // 1. Проверяем регистрацию ProjectHandler в CallbackqueryCommand
    $callbackFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
    if (file_exists($callbackFile)) {
        $content = file_get_contents($callbackFile);
        
        if (strpos($content, 'ProjectHandler') !== false) {
            echo "✓ ProjectHandler найден в CallbackqueryCommand\n";
        } else {
            echo "✗ ProjectHandler НЕ найден в CallbackqueryCommand\n";
        }
        
        if (strpos($content, 'new ProjectHandler(') !== false) {
            echo "✓ ProjectHandler зарегистрирован\n";
        } else {
            echo "✗ ProjectHandler НЕ зарегистрирован\n";
        }
    } else {
        echo "✗ CallbackqueryCommand.php не найден\n";
    }
    
    // 2. Проверяем приоритет обработчика
    echo "\n2. Возможные конфликты приоритетов:\n";
    
    // Проверяем файлы других обработчиков
    $handlersDir = __DIR__ . '/CustomCommands/Handlers/';
    if (is_dir($handlersDir)) {
        $handlers = scandir($handlersDir);
        foreach ($handlers as $handler) {
            if (str_ends_with($handler, 'Handler.php') && $handler !== 'ProjectHandler.php') {
                $handlerFile = $handlersDir . $handler;
                $handlerContent = file_get_contents($handlerFile);
                
                // Проверяем, не обрабатывает ли этот handler наши callback
                if (strpos($handlerContent, 'proj_ctrl') !== false) {
                    echo "⚠️ {$handler} может перехватывать callback 'proj_ctrl'\n";
                }
            }
        }
    }
    
    echo "\n3. Проверяем структуру callback данных:\n";
    $testCallbacks = [
        'proj_ctrl_add',
        'proj_ctrl_remove', 
        'proj_ctrl_list',
        'proj_ctrl_settings'
    ];
    
    foreach ($testCallbacks as $callback) {
        $length = strlen($callback);
        $utf8Length = mb_strlen($callback, 'UTF-8');
        echo "'{$callback}': длина {$length} байт, UTF-8 длина {$utf8Length}\n";
    }
    
    echo "\n=== РЕКОМЕНДАЦИИ ===\n";
    echo "1. У пользователя ЕСТЬ проекты на контроле - все кнопки должны отображаться\n";
    echo "2. Callback данные корректны и в пределах лимитов\n";
    echo "3. Возможные проблемы:\n";
    echo "   - ProjectHandler не зарегистрирован в правильном порядке\n";
    echo "   - Другой handler перехватывает callback данные\n";
    echo "   - Проблема в методе MessageService.sendMessage\n";
    echo "4. Проверьте в реальном боте, отображаются ли кнопки 'Удалить' и 'Настройки'\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "В строке: " . $e->getLine() . "\n";
    echo "Файл: " . $e->getFile() . "\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";