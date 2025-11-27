<?php
/**
 * Script to add the first admin to the system
 */

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Добавление первого администратора в систему ===\n\n";

try {
    // Initialize services
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "Сервисы инициализированы успешно.\n\n";
    
    // Show current super admins
    echo "Текущий список суперадминов:\n";
    $superAdmins = $adminService->getSuperAdmins();
    if (empty($superAdmins)) {
        echo "  Список суперадминов пуст.\n";
    } else {
        foreach ($superAdmins as $index => $superAdminId) {
            echo "  " . ($index + 1) . ". {$superAdminId}\n";
        }
    }
    
    // Ask for user ID to promote
    echo "\nВведите User ID пользователя, которого хотите назначить администратором: ";
    $handle = fopen("php://stdin", "r");
    $userIdInput = trim(fgets($handle));
    fclose($handle);
    
    // Validate user ID
    if (!is_numeric($userIdInput) || $userIdInput <= 0) {
        echo "❌ Неверный формат User ID. Должно быть положительное число.\n";
        exit(1);
    }
    
    $userId = (int)$userIdInput;
    
    // Check current access level
    $currentLevel = $adminService->getUserAccessLevel($userId);
    $currentLevelName = $adminService->getAccessLevelName($currentLevel);
    
    echo "Текущий уровень доступа пользователя {$userId}: {$currentLevelName} ({$currentLevel})\n";
    
    // If user is already an admin or super admin, show warning
    if ($currentLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
        echo "⚠️  Пользователь уже имеет уровень администратора или выше.\n";
        echo "Вы уверены, что хотите изменить его уровень? (y/n): ";
        
        $handle = fopen("php://stdin", "r");
        $response = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($response) !== 'y') {
            echo "Операция отменена.\n";
            exit(0);
        }
    }
    
    // Promote user to admin level
    echo "\nПовышение пользователя {$userId} до уровня Администратор...\n";
    
    $result = $adminService->updateUserAccessLevel($userId, DatabaseService::ACCESS_LEVEL_ADMIN);
    
    if ($result) {
        // Get updated access level
        $newLevel = $adminService->getUserAccessLevel($userId);
        $newLevelName = $adminService->getAccessLevelName($newLevel);
        
        echo "✅ Пользователь {$userId} успешно повышен до уровня {$newLevelName} ({$newLevel})!\n";
        
        // Show updated super admin list
        echo "\nОбновленный список суперадминов:\n";
        $superAdmins = $adminService->getSuperAdmins();
        if (empty($superAdmins)) {
            echo "  Список суперадминов пуст.\n";
        } else {
            foreach ($superAdmins as $index => $superAdminId) {
                echo "  " . ($index + 1) . ". {$superAdminId}\n";
            }
        }
    } else {
        echo "❌ Ошибка при повышении уровня доступа пользователя.\n";
        exit(1);
    }
    
    echo "\n=== Операция завершена успешно ===\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo "Трассировка: " . $e->getTraceAsString() . "\n";
    exit(1);
}

?>