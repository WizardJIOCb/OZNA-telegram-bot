<?php
/**
 * Script to check current user access levels
 */

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Проверка текущих уровней доступа пользователей ===\n\n";

try {
    // Initialize services
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "Сервисы инициализированы успешно.\n\n";
    
    // Show all access level constants
    echo "Константы уровней доступа:\n";
    echo "  " . DatabaseService::ACCESS_LEVEL_GUEST . " - Гость\n";
    echo "  " . DatabaseService::ACCESS_LEVEL_USER . " - Пользователь\n";
    echo "  " . DatabaseService::ACCESS_LEVEL_SUPERVISOR . " - Супервайзер\n";
    echo "  " . DatabaseService::ACCESS_LEVEL_ADMIN . " - Администратор\n";
    echo "  " . DatabaseService::ACCESS_LEVEL_SUPER_ADMIN . " - Суперадминистратор\n\n";
    
    // Show super admins
    echo "Суперадмины:\n";
    $superAdmins = $adminService->getSuperAdmins();
    if (empty($superAdmins)) {
        echo "  Список суперадминов пуст.\n\n";
    } else {
        foreach ($superAdmins as $index => $superAdminId) {
            echo "  " . ($index + 1) . ". {$superAdminId}\n";
        }
        echo "\n";
    }
    
    // Test some user IDs
    $testUserIds = [
        230379668,  // First super admin
        5261465966, // Second super admin
        123456789,  // Test user
        987654321   // Another test user
    ];
    
    echo "Проверка уровней доступа для тестовых пользователей:\n";
    foreach ($testUserIds as $userId) {
        $accessLevel = $adminService->getUserAccessLevel($userId);
        $levelName = $adminService->getAccessLevelName($accessLevel);
        
        // Check if user is super admin
        $isSuperAdmin = $adminService->isSuperAdmin($userId);
        
        echo "  User ID: {$userId}\n";
        echo "    Уровень доступа: {$levelName} ({$accessLevel})\n";
        echo "    Суперадмин: " . ($isSuperAdmin ? "Да" : "Нет") . "\n";
        echo "    Доступ к админке: " . ($accessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN ? "Да" : "Нет") . "\n\n";
    }
    
    // Show access level descriptions
    echo "Описание возможностей по уровням доступа:\n";
    echo "  Гость:\n";
    echo "    • Просмотр профиля\n";
    echo "    • Заполнение профиля\n";
    echo "    • Запрос повышения до Пользователя\n\n";
    
    echo "  Пользователь:\n";
    echo "    • Все возможности Гостя\n";
    echo "    • Использование всех основных функций бота\n";
    echo "    • Работа с табелем\n\n";
    
    echo "  Администратор:\n";
    echo "    • Все возможности Пользователя\n";
    echo "    • Доступ к админке\n";
    echo "    • Управление проектами\n";
    echo "    • Подтверждение заявок от Гостей\n";
    echo "    • Повышение Гостей до Пользователей\n\n";
    
    echo "  Суперадминистратор:\n";
    echo "    • Все возможности Администратора\n";
    echo "    • Назначение Администраторов\n";
    echo "    • Полный доступ ко всем функциям бота\n\n";
    
    echo "=== Проверка завершена ===\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

?>