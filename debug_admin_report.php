<?php

// Debug script to test report submission for administrators

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

echo "Debugging report submission for administrators...\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Test different access levels
$testUserIds = [
    // Replace these with actual user IDs from your system
    123456789,  // Regular user (level 1)
    987654321,  // Administrator (level 3)
    5261465966  // Super admin (level 4)
];

foreach ($testUserIds as $userId) {
    echo "\nTesting user ID: {$userId}\n";
    
    // Get user access level
    $userAccessLevel = $adminService->getUserAccessLevel($userId);
    echo "Access Level: {$userAccessLevel}\n";
    
    // Check if user is super admin
    $isSuperAdmin = $adminService->isSuperAdmin($userId);
    echo "Is Super Admin: " . ($isSuperAdmin ? 'Yes' : 'No') . "\n";
    
    // Test the restriction logic
    echo "Testing restriction logic...\n";
    
    // For a super admin (level 4), should be able to bypass restrictions
    if ($userAccessLevel >= 3 || $isSuperAdmin) {
        echo "User can bypass duplicate entry restrictions (level 3+ or super admin)\n";
    } else {
        echo "User is subject to duplicate entry restrictions\n";
    }
}

echo "\nDebug completed.\n";