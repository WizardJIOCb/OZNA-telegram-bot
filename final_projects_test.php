<?php

/**
 * Финальная проверка работы кнопки "Проекты"
 * Тестируем полный цикл работы: от появления кнопки до обработки нажатия
 */

echo "=== ФИНАЛЬНАЯ ПРОВЕРКА КНОПКИ ПРОЕКТЫ ===\n\n";

// Подключаем все необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

try {
    echo "1. Инициализация системы...\n";
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "✓ Сервисы инициализированы\n\n";
    
    echo "2. Тестируем пользователей с разными уровнями доступа...\n";
    
    // Берём реальных пользователей из базы данных
    $realUsers = [
        ['id' => 5932146202, 'expected_level' => 1, 'name' => 'Хафизов Айдар'],
        ['id' => 6293175637, 'expected_level' => 1, 'name' => 'Гарифуллин Алмаз'],
        ['id' => 5780338560, 'expected_level' => 2, 'name' => 'Ткачев Дмитрий'],
        ['id' => 5791428629, 'expected_level' => 3, 'name' => 'Давлетшин Альберт'],
        ['id' => 6645564928, 'expected_level' => 0, 'name' => 'Пользователь-гость']
    ];
    
    foreach ($realUsers as $user) {
        echo "  Тест пользователя: {$user['name']} (ID: {$user['id']})\n";
        
        $actualLevel = $adminService->getUserAccessLevel($user['id']);
        $levelName = $adminService->getAccessLevelName($actualLevel);
        
        echo "    Уровень доступа: {$actualLevel} ({$levelName})\n";
        
        // Проверяем создание главного меню
        $keyboard = $messageService->createMainMenuKeyboard($user['id']);
        $hasProjectsButton = false;
        $projectsCallbackData = '';
        
        foreach ($keyboard as $row) {
            foreach ($row as $button) {
                if (isset($button['text']) && strpos($button['text'], 'Проекты') !== false) {
                    $hasProjectsButton = true;
                    $projectsCallbackData = $button['callback_data'];
                    break 2;
                }
            }
        }
        
        if ($actualLevel >= 1) {
            if ($hasProjectsButton) {
                echo "    ✓ Кнопка 'Проекты' корректно отображается\n";
                echo "    ✓ Callback данные: {$projectsCallbackData}\n";
            } else {
                echo "    ✗ ОШИБКА: Кнопка 'Проекты' должна отображаться, но не найдена\n";
            }
        } else {
            if (!$hasProjectsButton) {
                echo "    ✓ Кнопка 'Проекты' корректно скрыта для гостя\n";
            } else {
                echo "    ✗ ОШИБКА: Кнопка 'Проекты' не должна отображаться для гостя\n";
            }
        }
        echo "\n";
    }
    
    echo "3. Тестируем обработку callback данных...\n";
    
    // Инициализируем обработчик проектов
    $projectHandler = new ProjectHandler($adminService, $messageService);
    $handlerFactory = new CallbackHandlerFactory();
    $handlerFactory->registerHandler($projectHandler);
    
    // Тестовые callback данные
    $testCallbacks = [
        'category_Проекты',
        'category_Проекты_МоиПроекты',
        'category_Проекты_ПроектыДляОтчёта',
        'proj_list_add',
        'proj_list_remove'
    ];
    
    foreach ($testCallbacks as $callback) {
        $canHandle = $projectHandler->canHandle($callback);
        $icon = $canHandle ? '✓' : '✗';
        echo "  {$icon} '{$callback}' -> обрабатывается: " . ($canHandle ? 'да' : 'нет') . "\n";
    }
    
    echo "\n4. Проверяем интеграцию с CallbackqueryCommand...\n";
    
    // Проверяем, что ProjectHandler зарегистрирован в CallbackqueryCommand
    $callbackFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
    if (file_exists($callbackFile)) {
        $content = file_get_contents($callbackFile);
        
        $checks = [
            'ProjectHandler импортирован' => strpos($content, 'use Longman\\TelegramBot\\Commands\\SystemCommands\\Handlers\\ProjectHandler;') !== false,
            'ProjectHandler зарегистрирован' => strpos($content, 'new ProjectHandler(') !== false,
            'AdminService передаётся ProjectHandler' => strpos($content, 'new ProjectHandler($adminService') !== false
        ];
        
        foreach ($checks as $checkName => $result) {
            $icon = $result ? '✓' : '✗';
            echo "  {$icon} {$checkName}\n";
        }
    } else {
        echo "  ✗ CallbackqueryCommand.php не найден\n";
    }
    
    echo "\n5. Проверяем логику обработки уровней доступа в ProjectHandler...\n";
    
    // Симуляция проверки доступа для разных уровней
    $testUserId = 5932146202; // пользователь с уровнем 1
    $userLevel = $adminService->getUserAccessLevel($testUserId);
    
    echo "  Тестовый пользователь {$testUserId}: уровень {$userLevel}\n";
    
    // Проверяем базовую проверку доступа (уровень 1+)
    if ($userLevel >= 1) {
        echo "  ✓ Базовый доступ к проектам: РАЗРЕШЁН\n";
    } else {
        echo "  ✗ Базовый доступ к проектам: ЗАПРЕЩЁН\n";
    }
    
    // Проверяем доступ к "Проекты на контроле" (уровень 2+)
    if ($userLevel >= 2) {
        echo "  ✓ Доступ к 'Проекты на контроле': РАЗРЕШЁН\n";
    } else {
        echo "  ! Доступ к 'Проекты на контроле': ЗАПРЕЩЁН (норма для уровня {$userLevel})\n";
    }
    
    echo "\n6. Итоговая диагностика...\n";
    
    $issues = [];
    $warnings = [];
    
    // Проверяем потенциальные проблемы
    if (!file_exists(__DIR__ . '/CustomCommands/Handlers/ProjectHandler.php')) {
        $issues[] = "ProjectHandler.php не найден";
    }
    
    if (!file_exists(__DIR__ . '/CustomCommands/Services/AdminService.php')) {
        $issues[] = "AdminService.php не найден";
    }
    
    if (!file_exists(__DIR__ . '/CustomCommands/Services/MessageService.php')) {
        $issues[] = "MessageService.php не найден";
    }
    
    // Проверяем наличие пользователей без доступа
    $allUsers = $adminService->getDatabaseService()->getAllUsers();
    $guestCount = 0;
    $userCount = 0;
    
    foreach ($allUsers as $user) {
        $level = $user['data']['access_level'] ?? 0;
        if ($level == 0) {
            $guestCount++;
        } elseif ($level >= 1) {
            $userCount++;
        }
    }
    
    if ($guestCount > 0) {
        $warnings[] = "{$guestCount} пользователей имеют уровень доступа 'Гость' и не видят кнопку 'Проекты'";
    }
    
    echo "РЕЗУЛЬТАТЫ:\n";
    
    if (empty($issues)) {
        echo "✓ Критических проблем не обнаружено\n";
    } else {
        echo "✗ КРИТИЧЕСКИЕ ПРОБЛЕМЫ:\n";
        foreach ($issues as $issue) {
            echo "  - {$issue}\n";
        }
    }
    
    if (!empty($warnings)) {
        echo "! ПРЕДУПРЕЖДЕНИЯ:\n";
        foreach ($warnings as $warning) {
            echo "  - {$warning}\n";
        }
    }
    
    echo "\nСТАТИСТИКА ПОЛЬЗОВАТЕЛЕЙ:\n";
    echo "  - Пользователи с доступом к 'Проекты': {$userCount}\n";
    echo "  - Гости без доступа: {$guestCount}\n";
    echo "  - Всего пользователей: " . count($allUsers) . "\n";
    
    echo "\nРЕКОМЕНДАЦИИ:\n";
    if ($guestCount > 0) {
        echo "  1. Используйте grant_projects_access.php для предоставления доступа нужным пользователям\n";
    }
    echo "  2. Попросите пользователей перезапустить бота командой /start после изменения уровня доступа\n";
    echo "  3. При проблемах проверьте логи бота и убедитесь, что webhook работает корректно\n";
    
} catch (Exception $e) {
    echo "✗ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";