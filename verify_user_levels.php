<?php
// Script to verify current user access levels
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Users to check
$users = [
    1090118386 => 'Близи',
    444234398 => 'Олег olegpysh',
    230379668 => 'Your ID (for reference)',
    5261465966 => 'Боря (for reference)'
];

echo "Current User Access Levels:\n";
echo "============================\n";

foreach ($users as $userId => $userName) {
    // Check if user is super admin
    $isSuperAdmin = $adminService->isSuperAdmin($userId);
    
    // Get database access level
    $dbLevel = $databaseService->getUserAccessLevel($userId);
    
    // Get level name
    $levelName = $adminService->getAccessLevelName($dbLevel);
    
    echo "User: {$userName} ({$userId})\n";
    echo "  Super Admin: " . ($isSuperAdmin ? 'Yes' : 'No') . "\n";
    echo "  Database Level: {$dbLevel} ({$levelName})\n";
    echo "\n";
}

// Show all super admins
echo "All Current Super Admins:\n";
echo "========================\n";
$superAdmins = $adminService->getSuperAdmins();
foreach ($superAdmins as $adminId) {
    echo "  {$adminId}\n";
}
?>