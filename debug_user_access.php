<?php

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Замените на ваш user_id для тестирования
$testUserId = 123456789; // Замените на реальный user_id

echo "Debugging user access level for user ID: {$testUserId}\n\n";

try {
    // Создаем сервисы
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Получаем уровень доступа пользователя
    $accessLevel = $adminService->getUserAccessLevel($testUserId);
    echo "User access level: {$accessLevel}\n";
    
    // Получаем имя уровня доступа
    $accessLevelName = $adminService->getAccessLevelName($accessLevel);
    echo "Access level name: {$accessLevelName}\n\n";
    
    // Получаем данные пользователя напрямую из базы
    $userData = $databaseService->getUserData($testUserId);
    echo "Raw user data from database:\n";
    print_r($userData);
    echo "\n";
    
    // Проверяем, является ли пользователь супер-админом
    $isSuperAdmin = $adminService->isSuperAdmin($testUserId);
    echo "Is super admin: " . ($isSuperAdmin ? 'Yes' : 'No') . "\n\n";
    
    // Получаем список супер-админов
    $superAdmins = $adminService->getSuperAdmins();
    echo "Current super admins:\n";
    print_r($superAdmins);
    echo "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}