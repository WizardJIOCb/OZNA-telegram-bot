<?php
// Web-based script to change user access levels from Super Admin to User
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
    <title>Change User Levels</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Change User Access Levels</h1>";

try {
    // Initialize services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();

    // Users to demote from Super Admin to User level
    $usersToDemote = [
        1090118386 => 'Близи',
        444234398 => 'Олег olegpysh'
    ];

    echo "<h2>Changing User Access Levels:</h2>";
    echo "<ul>";

    foreach ($usersToDemote as $userId => $userName) {
        echo "<li><strong>Processing user: {$userName} ({$userId})</strong><br>";

        // Update user access level to USER (1)
        $success = $databaseService->updateUserAccessLevel($userId, DatabaseService::ACCESS_LEVEL_USER);

        if ($success) {
            echo "<span class='success'>✓ Successfully updated access level to User</span><br>";

            // Verify the change
            $newLevel = $databaseService->getUserAccessLevel($userId);
            $levelName = $databaseService->getAccessLevelName($newLevel);
            echo "<span class='success'>✓ Verified new level: {$newLevel} ({$levelName})</span>";
        } else {
            echo "<span class='error'>✗ Failed to update access level</span>";
        }

        echo "</li><br>";
    }

    echo "</ul>";
    echo "<p class='success'><strong>User level changes completed!</strong></p>";
    echo "<p class='info'>Note: Super Admin status is controlled by the AdminService superAdmins array.<br>";
    echo "That array has already been updated to remove these users.</p>";

} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body>
</html>";
?>