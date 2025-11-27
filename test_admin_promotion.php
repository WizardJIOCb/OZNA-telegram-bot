<?php
/**
 * Test script for admin promotion functionality
 */

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Тестирование функциональности добавления администратора ===\n\n";

try {
    // Initialize services
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "Сервисы инициализированы успешно.\n\n";
    
    // Test user ID (you can change this to test with different users)
    $testUserId = 123456789; // Test user ID
    $adminUserId = 230379668; // This should be a super admin (from the hardcoded list)
    
    echo "Тестирование для пользователя ID: {$testUserId}\n";
    echo "Администратор ID: {$adminUserId}\n\n";
    
    // Check if admin user is super admin
    $isAdminSuperAdmin = $adminService->isSuperAdmin($adminUserId);
    echo "Пользователь {$adminUserId} является суперадмином: " . ($isAdminSuperAdmin ? "Да" : "Нет") . "\n";
    
    // Get current access level of test user
    $currentLevel = $adminService->getUserAccessLevel($testUserId);
    $levelName = $adminService->getAccessLevelName($currentLevel);
    echo "Текущий уровень доступа пользователя {$testUserId}: {$levelName} ({$currentLevel})\n";
    
    // Try to promote user to admin level
    echo "\nПопытка повышения уровня доступа пользователя {$testUserId} до Администратора...\n";
    
    // Check if admin user has required access level
    $hasRequiredAccess = $adminService->hasAccessLevel($adminUserId, DatabaseService::ACCESS_LEVEL_SUPER_ADMIN);
    echo "Администратор {$adminUserId} имеет уровень Суперадмин: " . ($hasRequiredAccess ? "Да" : "Нет") . "\n";
    
    if ($hasRequiredAccess) {
        // Promote user to admin level
        $result = $adminService->updateUserAccessLevel($testUserId, DatabaseService::ACCESS_LEVEL_ADMIN);
        echo "Результат повышения уровня доступа: " . ($result ? "Успешно" : "Ошибка") . "\n";
        
        if ($result) {
            // Get updated access level
            $newLevel = $adminService->getUserAccessLevel($testUserId);
            $newLevelName = $adminService->getAccessLevelName($newLevel);
            echo "Новый уровень доступа пользователя {$testUserId}: {$newLevelName} ({$newLevel})\n";
            
            // Verify promotion
            if ($newLevel === DatabaseService::ACCESS_LEVEL_ADMIN) {
                echo "✅ Пользователь успешно повышен до уровня Администратор!\n";
            } else {
                echo "❌ Ошибка: Уровень доступа не был изменен корректно.\n";
            }
        }
    } else {
        echo "❌ Администратор не имеет достаточных прав для назначения администраторов.\n";
        echo "Только Суперадминистратор может назначать Администраторов.\n";
    }
    
    // Show all super admins
    echo "\nСписок суперадминов:\n";
    $superAdmins = $adminService->getSuperAdmins();
    foreach ($superAdmins as $superAdminId) {
        echo "  - {$superAdminId}\n";
    }
    
    echo "\n=== Тестирование завершено ===\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
}

?>