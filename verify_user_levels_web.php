<?php
// Web-based script to verify current user access levels
// This script can be accessed through a web browser instead of command line

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Simple authentication - in production, you should use proper authentication
$valid_ips = ['127.0.0.1', '::1'];
if (!in_array($_SERVER['REMOTE_ADDR'], $valid_ips)) {
    die('Access denied');
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Verify User Levels</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .super-admin { color: red; font-weight: bold; }
        .admin { color: orange; font-weight: bold; }
        .user { color: green; font-weight: bold; }
        .guest { color: gray; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Current User Access Levels</h1>";

try {
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

    echo "<h2>User Access Levels:</h2>";
    echo "<ul>";

    foreach ($users as $userId => $userName) {
        // Check if user is super admin
        $isSuperAdmin = $adminService->isSuperAdmin($userId);
        
        // Get database access level
        $dbLevel = $databaseService->getUserAccessLevel($userId);
        
        // Get level name
        $levelName = $adminService->getAccessLevelName($dbLevel);
        
        // Determine CSS class based on level
        $cssClass = '';
        switch ($dbLevel) {
            case DatabaseService::ACCESS_LEVEL_SUPER_ADMIN:
                $cssClass = 'super-admin';
                break;
            case DatabaseService::ACCESS_LEVEL_ADMIN:
                $cssClass = 'admin';
                break;
            case DatabaseService::ACCESS_LEVEL_USER:
                $cssClass = 'user';
                break;
            case DatabaseService::ACCESS_LEVEL_GUEST:
                $cssClass = 'guest';
                break;
        }
        
        echo "<li><strong>{$userName} ({$userId})</strong><br>";
        echo "  Super Admin: " . ($isSuperAdmin ? '<span class="super-admin">Yes</span>' : 'No') . "<br>";
        echo "  Database Level: <span class='{$cssClass}'>{$dbLevel} ({$levelName})</span><br>";
        echo "</li><br>";
    }

    echo "</ul>";

    // Show all super admins
    echo "<h2>All Current Super Admins:</h2>";
    echo "<ul>";
    $superAdmins = $adminService->getSuperAdmins();
    foreach ($superAdmins as $adminId) {
        echo "<li>{$adminId}</li>";
    }
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";
?>