<?php

/**
 * Диагностика проблем с кнопками "Проекты на контроле"
 */

echo "=== ДИАГНОСТИКА КНОПОК ПРОЕКТОВ НА КОНТРОЛЕ ===\n\n";

// Подключаем необходимые файлы
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
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "1. Проверяем callback данные длинных кнопок...\n";
    $projectHandler = new ProjectHandler($adminService, $messageService);
    
    $problematicCallbacks = [
        'proj_ctrl_add',
        'proj_ctrl_remove',
        'proj_ctrl_list',
        'proj_ctrl_settings'
    ];
    
    foreach ($problematicCallbacks as $callback) {
        $canHandle = $projectHandler->canHandle($callback);
        $length = strlen($callback);
        echo "  '{$callback}' (длина: {$length}): " . ($canHandle ? '✓' : '✗') . "\n";
    }
    
    echo "\n2. Ищем пользователя-супервайзера для тестирования...\n";
    
    $allUsers = $databaseService->getAllUsers();
    $testUser = null;
    
    foreach ($allUsers as $user) {
        $level = $adminService->getUserAccessLevel($user['user_id']);
        if ($level >= 2) {
            $testUser = $user;
            echo "Найден тестовый пользователь: ID {$user['user_id']}, уровень {$level}\n";
            break;
        }
    }
    
    if (!$testUser) {
        echo "✗ Нет пользователей с доступом к функции!\n";
        return;
    }
    
    $testUserId = $testUser['user_id'];
    
    echo "\n3. Проверяем текущие проекты на контроле у пользователя...\n";
    $userData = $databaseService->getUserData($testUserId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    echo "Проекты на контроле: " . json_encode($controlProjects) . "\n";
    echo "Количество проектов: " . count($controlProjects) . "\n";
    
    if (empty($controlProjects)) {
        echo "ПРИЧИНА: У пользователя нет проектов на контроле!\n";
        echo "Поэтому кнопки 'Удалить' и 'Настройки' не отображаются.\n";
    } else {
        echo "У пользователя есть проекты на контроле - кнопки должны отображаться.\n";
    }
    
    echo "\n4. Проверяем логику показа кнопок в коде...\n";
    
    // Эмулируем логику из showControlProjectsMenu
    $allProjects = $adminService->getAllProjects();
    echo "Доступно проектов в системе: " . count($allProjects) . "\n";
    
    $shouldShowAddButton = !empty($allProjects);
    $shouldShowRemoveButton = !empty($controlProjects);
    $shouldShowSettingsButton = !empty($controlProjects);
    
    echo "Должна показываться кнопка 'Добавить': " . ($shouldShowAddButton ? 'ДА' : 'НЕТ') . "\n";
    echo "Должна показываться кнопка 'Удалить': " . ($shouldShowRemoveButton ? 'ДА' : 'НЕТ') . "\n";
    echo "Должна показываться кнопка 'Настройки': " . ($shouldShowSettingsButton ? 'ДА' : 'НЕТ') . "\n";
    
    echo "\n5. Тестируем проблемные callback данные...\n";
    
    // Проверяем, что происходит при обработке callback
    $testCallbacks = ['proj_ctrl_add', 'proj_ctrl_list'];
    
    foreach ($testCallbacks as $testCallback) {
        echo "Тестируем callback: '{$testCallback}'\n";
        
        $canHandle = $projectHandler->canHandle($testCallback);
        if ($canHandle) {
            echo "  ✓ ProjectHandler может обработать\n";
        } else {
            echo "  ✗ ProjectHandler НЕ может обработать\n";
        }
    }
    
    echo "\n6. Проверяем длину callback данных Telegram...\n";
    echo "Ограничение Telegram для callback_data: 64 байта\n";
    
    $currentCallbacks = [
        'proj_ctrl_add' => 'Добавить проект на контроль',
        'proj_ctrl_remove' => 'Удалить проект с контроля',
        'proj_ctrl_list' => 'Список проектов на контроле',
        'proj_ctrl_settings' => 'Настроить уведомления'
    ];
    
    foreach ($currentCallbacks as $callback => $description) {
        $length = strlen($callback);
        $status = $length <= 64 ? '✓' : '✗ ПРЕВЫШЕНИЕ!';
        echo "  '{$callback}' ({$length} байт): {$status} - {$description}\n";
    }
    
    echo "\n=== РЕКОМЕНДАЦИИ ===\n";
    echo "1. Все callback данные в пределах лимита Telegram\n";
    echo "2. Кнопки 'Удалить' и 'Настройки' не показываются, потому что нет проектов на контроле\n";
    echo "3. Проблема с нажатием кнопок может быть в обработке callback данных\n";
    echo "4. Нужно добавить тестовый проект для отображения всех кнопок\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";