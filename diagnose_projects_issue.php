<?php

/**
 * Диагностика проблемы с кнопкой "Проекты"
 * Проверяем уровни доступа пользователей в базе данных
 */

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ ПРОЕКТЫ ===\n\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

try {
    echo "1. Подключение к базе данных...\n";
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "✓ Сервисы инициализированы\n\n";
    
    echo "2. Проверка всех пользователей в базе данных...\n";
    $allUsers = $databaseService->getAllUsers();
    
    if (empty($allUsers)) {
        echo "✗ В базе данных нет пользователей!\n";
        echo "  Это означает, что пользователи ещё не взаимодействовали с ботом.\n";
        echo "  Кнопка 'Проекты' не появится для незарегистрированных пользователей.\n\n";
        
        echo "РЕШЕНИЕ:\n";
        echo "1. Попросите пользователя отправить любое сообщение боту (например /start)\n";
        echo "2. После этого пользователь будет добавлен в базу данных с уровнем доступа 0 (гость)\n";
        echo "3. Администратор должен повысить уровень доступа пользователя до 1 или выше\n";
        echo "4. После этого кнопка 'Проекты' появится у пользователя\n\n";
        
    } else {
        echo "✓ Найдено пользователей в базе: " . count($allUsers) . "\n\n";
        
        echo "Список пользователей и их уровни доступа:\n";
        foreach ($allUsers as $user) {
            $userId = $user['user_id'];
            $accessLevel = $user['data']['access_level'] ?? 0;
            $levelName = $adminService->getAccessLevelName($accessLevel);
            $fio = $user['data']['fio'] ?? 'Не указано';
            
            echo "  ID: {$userId} | Уровень: {$accessLevel} ({$levelName}) | ФИО: {$fio}\n";
            
            // Проверяем, должна ли быть видна кнопка Проекты
            if ($accessLevel >= 1) {
                echo "    ✓ Кнопка 'Проекты' должна быть видна\n";
            } else {
                echo "    ✗ Кнопка 'Проекты' НЕ видна (уровень доступа слишком низкий)\n";
            }
        }
    }
    
    echo "\n3. Проверка супер-администраторов...\n";
    $superAdmins = $adminService->getSuperAdmins();
    echo "Супер-администраторы: " . implode(', ', $superAdmins) . "\n";
    
    foreach ($superAdmins as $superAdminId) {
        $level = $adminService->getUserAccessLevel($superAdminId);
        echo "  Супер-админ ID {$superAdminId}: уровень {$level}\n";
    }
    
    echo "\n4. Создание тестового пользователя с правильным уровнем доступа...\n";
    $testUserId = 999999999; // тестовый ID
    
    // Добавляем тестового пользователя в базу данных
    $userData = [
        'access_level' => 1, // пользователь
        'fio' => 'Тестовый Пользователь',
        'functional_direction' => 'Тестирование'
    ];
    
    $success = $databaseService->updateUserData($testUserId, $userData);
    if ($success) {
        echo "✓ Тестовый пользователь создан с ID {$testUserId}\n";
        
        // Проверяем его уровень доступа
        $level = $adminService->getUserAccessLevel($testUserId);
        echo "✓ Уровень доступа тестового пользователя: {$level}\n";
        
        // Проверяем создание меню для него
        $keyboard = $messageService->createMainMenuKeyboard($testUserId);
        $hasProjectsButton = false;
        
        foreach ($keyboard as $row) {
            foreach ($row as $button) {
                if (isset($button['text']) && strpos($button['text'], 'Проекты') !== false) {
                    $hasProjectsButton = true;
                    echo "✓ Кнопка 'Проекты' найдена в меню тестового пользователя\n";
                    break 2;
                }
            }
        }
        
        if (!$hasProjectsButton) {
            echo "✗ Кнопка 'Проекты' НЕ найдена в меню тестового пользователя\n";
        }
        
    } else {
        echo "✗ Не удалось создать тестового пользователя\n";
    }
    
    echo "\n5. Инструкции по решению проблемы...\n";
    echo "Если кнопка 'Проекты' не работает для конкретного пользователя:\n\n";
    
    echo "ШАГ 1: Узнайте Telegram ID пользователя\n";
    echo "  - Попросите пользователя отправить любое сообщение боту\n";
    echo "  - ID пользователя будет в логах или можно использовать @userinfobot\n\n";
    
    echo "ШАГ 2: Проверьте уровень доступа пользователя\n";
    echo "  - Запустите этот скрипт ещё раз\n";
    echo "  - Найдите пользователя в списке выше\n\n";
    
    echo "ШАГ 3: Повысьте уровень доступа пользователя\n";
    echo "  - Выполните следующий PHP код для повышения уровня:\n";
    echo "  \$adminService->updateUserAccessLevel(\$userId, 1); // для обычного пользователя\n";
    echo "  \$adminService->updateUserAccessLevel(\$userId, 2); // для супервайзера\n";
    echo "  \$adminService->updateUserAccessLevel(\$userId, 3); // для администратора\n\n";
    
    echo "ШАГ 4: Создайте скрипт для быстрого повышения уровня доступа\n";
    echo "<?php\n";
    echo "require_once 'CustomCommands/Services/DatabaseService.php';\n";
    echo "require_once 'CustomCommands/Services/MessageService.php';\n";
    echo "require_once 'CustomCommands/Services/AdminService.php';\n";
    echo "\$messageService = new Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\MessageService();\n";
    echo "\$adminService = new Longman\\TelegramBot\\Commands\\SystemCommands\\Services\\AdminService(\$messageService);\n";
    echo "\$userId = 123456789; // замените на реальный ID пользователя\n";
    echo "\$adminService->updateUserAccessLevel(\$userId, 1);\n";
    echo "echo \"Пользователю \$userId установлен уровень доступа 1\";\n";
    echo "?>\n\n";
    
} catch (Exception $e) {
    echo "✗ ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трассировка:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";