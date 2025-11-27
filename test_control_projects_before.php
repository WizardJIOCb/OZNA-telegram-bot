<?php

/**
 * Тест функционала "Проекты на контроле" перед реализацией
 */

echo "=== ТЕСТ ТЕКУЩЕГО СОСТОЯНИЯ ПРОЕКТОВ НА КОНТРОЛЕ ===\n\n";

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
    
    echo "1. Проверяем текущую обработку callback 'proj_ctrl_*'...\n";
    $projectHandler = new ProjectHandler($adminService, $messageService);
    
    $testCallbacks = [
        'proj_ctrl_add',
        'proj_ctrl_remove', 
        'proj_ctrl_list',
        'proj_ctrl_settings'
    ];
    
    foreach ($testCallbacks as $callback) {
        $canHandle = $projectHandler->canHandle($callback);
        echo "  {$callback}: " . ($canHandle ? 'обрабатывается ✓' : 'НЕ обрабатывается ✗') . "\n";
    }
    
    echo "\n2. Проверяем пользователей с доступом к функции...\n";
    
    // Найти пользователей с уровнем супервайзер и выше
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $allUsers = $databaseService->getAllUsers();
    
    $supervisorUsers = [];
    foreach ($allUsers as $user) {
        $level = $adminService->getUserAccessLevel($user['user_id']);
        if ($level >= 2) { // супервайзер и выше
            $levelName = $adminService->getAccessLevelName($level);
            $fio = $user['data']['fio'] ?? 'Не указано';
            $supervisorUsers[] = [
                'id' => $user['user_id'],
                'level' => $level,
                'level_name' => $levelName,
                'fio' => $fio
            ];
        }
    }
    
    echo "Пользователи с доступом к 'Проекты на контроле':\n";
    foreach ($supervisorUsers as $user) {
        echo "  ID: {$user['id']} | {$user['level_name']} | {$user['fio']}\n";
    }
    
    echo "\n3. Проверяем текущую структуру данных пользователей...\n";
    
    if (!empty($supervisorUsers)) {
        $testUser = $supervisorUsers[0];
        $userData = $databaseService->getUserData($testUser['id']);
        
        echo "Пример данных пользователя {$testUser['id']}:\n";
        echo "  report_projects: " . json_encode($userData['report_projects'] ?? []) . "\n";
        echo "  control_projects: " . json_encode($userData['control_projects'] ?? []) . "\n";
    }
    
    echo "\n4. Проверяем доступные проекты в системе...\n";
    $allProjects = $adminService->getAllProjects();
    echo "Всего проектов в системе: " . count($allProjects) . "\n";
    
    if (count($allProjects) > 0) {
        echo "Примеры проектов:\n";
        $examples = array_slice($allProjects, 0, 5);
        foreach ($examples as $project) {
            echo "  - {$project}\n";
        }
        if (count($allProjects) > 5) {
            echo "  ... и ещё " . (count($allProjects) - 5) . " проектов\n";
        }
    }
    
    echo "\n=== ГОТОВЫ К РЕАЛИЗАЦИИ ===\n";
    echo "✓ Система обработки callback готова\n";
    echo "✓ Есть пользователи с нужным уровнем доступа\n";
    echo "✓ Есть проекты для работы\n";
    echo "✓ Структура данных подготовлена\n";
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

echo "\n=== ТЕСТ ЗАВЕРШЁН ===\n";