<?php
// Real database check script
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Checking real database for users...\n\n";

try {
    // Create services
    $databaseService = new DatabaseService();
    
    echo "Step 1: Get all users from database\n";
    $allUsers = $databaseService->getAllUsers();
    echo "Total users found: " . count($allUsers) . "\n\n";
    
    if (empty($allUsers)) {
        echo "WARNING: No users found in database!\n";
        echo "This might be the issue - the database might be empty or not accessible.\n";
    } else {
        echo "Users in database:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($allUsers as $index => $user) {
            $userId = $user['user_id'] ?? 'N/A';
            $userData = $user['data'] ?? [];
            $fio = $userData['fio'] ?? 'Без ФИО';
            $accessLevel = $userData['access_level'] ?? 0;
            $functionalDirection = $userData['functional_direction'] ?? 'Не указано';
            
            echo "User #" . ($index + 1) . ":\n";
            echo "  User ID: {$userId}\n";
            echo "  FIO: {$fio}\n";
            echo "  Access Level: {$accessLevel}\n";
            echo "  Functional Direction: {$functionalDirection}\n";
            echo str_repeat("-", 80) . "\n";
        }
    }
    
    echo "\nStep 2: Filter users with access level > 0\n";
    $usersWithAccess = [];
    foreach ($allUsers as $user) {
        $accessLevel = $user['data']['access_level'] ?? 0;
        if ($accessLevel > 0) {
            $usersWithAccess[] = $user;
        }
    }
    
    echo "Users with access (level > 0): " . count($usersWithAccess) . "\n";
    
    if (empty($usersWithAccess)) {
        echo "\nWARNING: No users with access level > 0 found!\n";
        echo "This explains why the list is empty.\n";
        echo "All users in the database have access level 0 (Guest).\n";
    } else {
        echo "\nUsers with access:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($usersWithAccess as $index => $user) {
            $userId = $user['user_id'] ?? 'N/A';
            $userData = $user['data'] ?? [];
            $fio = $userData['fio'] ?? 'Без ФИО';
            $accessLevel = $userData['access_level'] ?? 0;
            
            echo "User #" . ($index + 1) . ":\n";
            echo "  User ID: {$userId}\n";
            echo "  FIO: {$fio}\n";
            echo "  Access Level: {$accessLevel}\n";
            echo str_repeat("-", 80) . "\n";
        }
    }
    
    // Create AdminService to test getAllUsersWithAccess
    echo "\nStep 3: Test AdminService.getAllUsersWithAccess()\n";
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    $usersWithAccessFromService = $adminService->getAllUsersWithAccess();
    echo "Users returned by AdminService: " . count($usersWithAccessFromService) . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nCheck completed!\n";
