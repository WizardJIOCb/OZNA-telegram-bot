<?php

// Test script to verify that administrators receive access request notifications

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Testing admin notification fix...\n";

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Test getting all administrators
echo "Getting all administrators...\n";
$administrators = $adminService->getAllAdministrators();

echo "Found " . count($administrators) . " administrators:\n";
foreach ($administrators as $admin) {
    $userId = $admin['user_id'];
    $accessLevel = $admin['data']['access_level'] ?? 0;
    echo "  User ID: {$userId}, Access Level: {$accessLevel}\n";
}

// Test getting super admins
echo "\nGetting super admins...\n";
$superAdmins = $adminService->getSuperAdmins();
echo "Found " . count($superAdmins) . " super admins:\n";
foreach ($superAdmins as $adminId) {
    echo "  User ID: {$adminId}\n";
}

echo "\nTest completed.\n";