<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/hook.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Test user ID (your ID from the hook.php file)
$userId = 230379668;

// Check user access level
$userAccessLevel = $adminService->getUserAccessLevel($userId);
echo "User ID: {$userId}\n";
echo "User access level: {$userAccessLevel}\n";

// Check if user is super admin
$isSuperAdmin = $adminService->isSuperAdmin($userId);
echo "Is super admin: " . ($isSuperAdmin ? 'true' : 'false') . "\n";

// Get user data
$userData = \pDB::getUserData($userId);
echo "User data:\n";
print_r($userData);

?>