<?php
// Test callback handling for user management button
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;

echo "Testing callback handling for 'acc_lvl_manage_users'...\n\n";

try {
    // Create services
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    $accessLevelHandler = new AccessLevelHandler($adminService, $messageService);
    
    // Test 1: Check if handler can handle this callback
    echo "Test 1: Can handler handle 'acc_lvl_manage_users'?\n";
    $canHandle = $accessLevelHandler->canHandle('acc_lvl_manage_users');
    echo "Result: " . ($canHandle ? "YES" : "NO") . "\n\n";
    
    if (!$canHandle) {
        echo "ERROR: AccessLevelHandler cannot handle 'acc_lvl_manage_users' callback!\n";
        echo "This means the button press won't be processed by this handler.\n\n";
    }
    
    // Test 2: Check super admin user ID and access level
    $superAdminId = 5261465966; // Your user ID
    echo "Test 2: Check super admin access level\n";
    $accessLevel = $adminService->getUserAccessLevel($superAdminId);
    echo "Super admin user ID: {$superAdminId}\n";
    echo "Access level: {$accessLevel}\n";
    echo "Access level name: " . $adminService->getAccessLevelName($accessLevel) . "\n\n";
    
    if ($accessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
        echo "WARNING: Super admin doesn't have admin access level!\n";
        echo "This would prevent the user list from showing.\n\n";
    }
    
    // Test 3: Get users with access
    echo "Test 3: Get users with access\n";
    $usersWithAccess = $adminService->getAllUsersWithAccess();
    echo "Users with access found: " . count($usersWithAccess) . "\n\n";
    
    if (empty($usersWithAccess)) {
        echo "ERROR: No users with access found!\n";
        echo "The list would be empty.\n\n";
    } else {
        echo "First 5 users with access:\n";
        echo str_repeat("-", 60) . "\n";
        $count = min(5, count($usersWithAccess));
        for ($i = 0; $i < $count; $i++) {
            $user = $usersWithAccess[$i];
            $userId = $user['user_id'];
            $userData = $user['data'] ?? [];
            $fio = $userData['fio'] ?? 'Без ФИО';
            $accessLevel = $userData['access_level'] ?? 0;
            $levelName = $adminService->getAccessLevelName($accessLevel);
            
            echo "User " . ($i + 1) . ":\n";
            echo "  ID: {$userId}\n";
            echo "  FIO: {$fio}\n";
            echo "  Level: {$levelName} ({$accessLevel})\n";
            echo str_repeat("-", 60) . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "All tests completed!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
