<?php

echo "Fixing user access levels...\n\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Список известных супер-админов
$superAdmins = [
    230379668,  // Главный админ
    5261465966  // Боря
];

try {
    // Создаем сервисы
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "Connected to database successfully.\n\n";
    
    // Получаем всех пользователей
    echo "Retrieving all users...\n";
    $allUsers = $databaseService->getAllUsers();
    echo "Found " . count($allUsers) . " users in the database.\n\n";
    
    // Анализируем уровни доступа пользователей
    echo "Analyzing user access levels:\n";
    $accessLevelStats = [
        'guest' => 0,
        'user' => 0,
        'supervisor' => 0,
        'admin' => 0,
        'super_admin' => 0,
        'undefined' => 0
    ];
    
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        
        if (isset($userData['access_level'])) {
            $accessLevel = $userData['access_level'];
            switch ($accessLevel) {
                case 0:
                    $accessLevelStats['guest']++;
                    break;
                case 1:
                    $accessLevelStats['user']++;
                    break;
                case 2:
                    $accessLevelStats['supervisor']++;
                    break;
                case 3:
                    $accessLevelStats['admin']++;
                    break;
                case 4:
                    $accessLevelStats['super_admin']++;
                    break;
                default:
                    $accessLevelStats['undefined']++;
                    break;
            }
        } else {
            $accessLevelStats['undefined']++;
        }
    }
    
    echo "Access level statistics:\n";
    foreach ($accessLevelStats as $level => $count) {
        echo "  {$level}: {$count}\n";
    }
    
    echo "\n";
    
    // Проверяем и устанавливаем супер-админов
    echo "Checking and setting super admins...\n";
    foreach ($superAdmins as $superAdminId) {
        echo "Checking user ID: {$superAdminId}\n";
        
        // Получаем данные пользователя
        $userData = $databaseService->getUserData($superAdminId);
        echo "  Current user data: " . json_encode($userData, JSON_UNESCAPED_UNICODE) . "\n";
        
        // Устанавливаем флаг супер-админа
        $userData['is_super_admin'] = true;
        
        // Устанавливаем уровень доступа супер-админа
        $userData['access_level'] = 4; // ACCESS_LEVEL_SUPER_ADMIN
        
        // Обновляем данные пользователя
        $result = $databaseService->updateUserData($superAdminId, $userData);
        if ($result) {
            echo "  ✓ Successfully updated user {$superAdminId} as super admin\n";
        } else {
            echo "  ✗ Failed to update user {$superAdminId} as super admin\n";
        }
    }
    
    echo "\n";
    
    // Создаем тестового пользователя с полным доступом
    echo "Creating test user with full access...\n";
    $testUserId = 123456789;
    $testUserData = [
        'fio' => 'Test User',
        'functional_direction' => 'Не указано',
        'access_level' => 1 // ACCESS_LEVEL_USER
    ];
    
    $result = $databaseService->updateUserData($testUserId, $testUserData);
    if ($result) {
        echo "✓ Successfully created test user with ID {$testUserId}\n";
    } else {
        echo "✗ Failed to create test user with ID {$testUserId}\n";
    }
    
    echo "\n";
    
    // Проверяем работу AdminService
    echo "Testing AdminService functionality...\n";
    foreach ($superAdmins as $superAdminId) {
        $isSuperAdmin = $adminService->isSuperAdmin($superAdminId);
        echo "  User {$superAdminId} is super admin: " . ($isSuperAdmin ? 'Yes' : 'No') . "\n";
        
        $accessLevel = $adminService->getUserAccessLevel($superAdminId);
        echo "  User {$superAdminId} access level: {$accessLevel}\n";
    }
    
    echo "\n";
    
    // Получаем список всех супер-админов
    echo "Getting list of all super admins...\n";
    $allSuperAdmins = $adminService->getSuperAdmins();
    echo "Current super admins: " . json_encode($allSuperAdmins) . "\n";
    
    echo "\n";
    
    echo "User access levels fix completed successfully!\n";
    echo "Super admins have been properly configured.\n";
    echo "Test user has been created with basic access.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}