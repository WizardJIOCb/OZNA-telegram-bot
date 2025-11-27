<?php
/**
 * Script to count users with access level 1 (Пользователь)
 */

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Подсчет пользователей с уровнем доступа 1 ===\n\n";

try {
    // Initialize services
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    $databaseService = new DatabaseService();
    
    echo "Сервисы инициализированы успешно.\n\n";
    
    // Get all users from database
    echo "Получение всех пользователей из базы данных...\n";
    $allUsers = $databaseService->getAllUsers();
    
    echo "Всего пользователей в системе: " . count($allUsers) . "\n\n";
    
    // Count users with access level 1
    $usersWithLevel1 = 0;
    $usersList = [];
    
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        
        // Get access level from user data
        $accessLevel = $userData['access_level'] ?? 0;
        
        // Check if user is super admin (they have highest access level)
        $isSuperAdmin = $adminService->isSuperAdmin($userId);
        if ($isSuperAdmin) {
            $effectiveLevel = 4; // Super admin level
        } else {
            $effectiveLevel = $accessLevel;
        }
        
        // Count users with access level 1
        if ($effectiveLevel == 1) {
            $usersWithLevel1++;
            $fio = $userData['fio'] ?? 'Без ФИО';
            $usersList[] = [
                'id' => $userId,
                'fio' => $fio,
                'access_level' => $accessLevel
            ];
        }
    }
    
    echo "Количество пользователей с уровнем доступа 1 (Пользователь): {$usersWithLevel1}\n\n";
    
    // Show list of users with level 1
    if (!empty($usersList)) {
        echo "Список пользователей с уровнем доступа 1:\n";
        echo str_repeat("-", 50) . "\n";
        foreach ($usersList as $user) {
            echo "ID: {$user['id']}, ФИО: {$user['fio']}\n";
        }
        echo str_repeat("-", 50) . "\n";
    }
    
    echo "\n=== Подсчет завершен ===\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>