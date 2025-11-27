<?php
// Script to change user access levels from Super Admin to User
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Initialize services
$databaseService = new DatabaseService();
$messageService = new MessageService();

// Users to demote from Super Admin to User level
$usersToDemote = [
    1090118386 => 'Близи',
    444234398 => 'Олег olegpysh'
];

echo "Changing User Access Levels:\n";
echo "===========================\n";

foreach ($usersToDemote as $userId => $userName) {
    echo "Processing user: {$userName} ({$userId})\n";
    
    // Update user access level to USER (1)
    $success = $databaseService->updateUserAccessLevel($userId, DatabaseService::ACCESS_LEVEL_USER);
    
    if ($success) {
        echo "  ✓ Successfully updated access level to User\n";
        
        // Verify the change
        $newLevel = $databaseService->getUserAccessLevel($userId);
        $levelName = $databaseService->getAccessLevelName($newLevel);
        echo "  ✓ Verified new level: {$newLevel} ({$levelName})\n";
    } else {
        echo "  ✗ Failed to update access level\n";
    }
    
    echo "\n";
}

echo "User level changes completed!\n";
echo "Note: Super Admin status is controlled by the AdminService superAdmins array.\n";
echo "That array has already been updated to remove these users.\n";
?>