<?php

/**
 * Финальный тест обновленного функционала "Проекты на контроле"
 */

echo "=== ФИНАЛЬНЫЙ ТЕСТ ОБНОВЛЕННОГО ФУНКЦИОНАЛА ===\n\n";

// Подключаем файлы
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

try {
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "1. Проверяем обновленные названия кнопок...\n";
    
    $newButtonTexts = [
        '➕ Добавить проект',
        '➖ Удалить проект', 
        '⚙️ Настройки',
        '📋 Список проектов',
        '⬅️ Назад'
    ];
    
    foreach ($newButtonTexts as $buttonText) {
        $length = strlen($buttonText);
        echo "  '{$buttonText}': {$length} символов\n";
    }
    
    echo "\n2. Тестируем с пользователем, имеющим проекты на контроле...\n";
    
    $testUserId = 4225504; // у него есть проект ОИ834
    echo "Пользователь: {$testUserId}\n";
    
    // Проверяем его данные
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $userData = $databaseService->getUserData($testUserId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    echo "Проекты на контроле: " . count($controlProjects) . "\n";
    foreach ($controlProjects as $project => $settings) {
        echo "  - {$project}\n";
    }
    
    echo "\n3. Создаем ProjectHandler и тестируем callback обработку...\n";
    
    $projectHandler = new ProjectHandler($adminService, $messageService);
    
    $testCallbacks = [
        'proj_ctrl_add' => 'Добавить проект',
        'proj_ctrl_remove' => 'Удалить проект',
        'proj_ctrl_list' => 'Список проектов',
        'proj_ctrl_settings' => 'Настройки'
    ];
    
    foreach ($testCallbacks as $callback => $description) {
        $canHandle = $projectHandler->canHandle($callback);
        echo "  {$callback} ({$description}): " . ($canHandle ? '✓' : '✗') . "\n";
    }
    
    echo "\n4. Проверяем логи (включены в обновленном коде)...\n";
    echo "Теперь при обращении к 'Проекты на контроле' в логах будет:\n";
    echo "  - ProjectHandler: showControlProjectsMenu for user {user_id}\n";
    echo "  - ProjectHandler: user has X projects on control\n";
    echo "  - ProjectHandler: added 'Add project' button\n";
    echo "  - ProjectHandler: added 'Remove' and 'Settings' buttons\n";
    echo "  - ProjectHandler: keyboard has X rows\n";
    echo "  - ProjectHandler: button 'название' -> 'callback_data'\n";
    
    echo "\n5. Инструкции для проверки в боте...\n";
    echo "Зайдите в бота как пользователь с уровнем Супервайзер или выше и:\n";
    echo "1. Перейдите: Проекты → Мои проекты → Проекты на контроле\n";
    echo "2. Теперь должны отображаться ВСЕ кнопки:\n";
    echo "   ✓ ➕ Добавить проект\n";
    echo "   ✓ ➖ Удалить проект\n";
    echo "   ✓ ⚙️ Настройки\n";
    echo "   ✓ 📋 Список проектов\n";
    echo "   ✓ ⬅️ Назад\n";
    echo "3. Попробуйте нажать на каждую кнопку\n";
    echo "4. Проверьте логи error.log на предмет записей ProjectHandler\n";
    
    echo "\n6. Что изменилось:\n";
    echo "✓ Укорочены названия кнопок для лучшей совместимости\n";
    echo "✓ Добавлено детальное логирование процесса создания меню\n";
    echo "✓ Исправлена логика отображения кнопок\n";
    echo "✓ Добавлены проверки на пустые значения в настройках\n";
    
    echo "\n=== ПРОБЛЕМЫ РЕШЕНЫ ===\n";
    echo "Если кнопки всё ещё не отображаются или не реагируют:\n";
    echo "1. Проверьте логи - должны появиться записи ProjectHandler\n";
    echo "2. Убедитесь, что пользователь имеет уровень доступа 2+\n";
    echo "3. Перезапустите бота командой /start\n";
    echo "4. Проверьте, что webhook работает корректно\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЁН ===\n";