<?php
// Web-based test script to check Admin button functionality

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Admin Button Test</title>
</head>
<body>
<h1>Admin Button Test</h1>";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "<p>Testing Admin button functionality...</p>";

// Initialize services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Test user ID (using a known super admin)
$testUserId = 230379668; // This should be a super admin

echo "<p>Testing with user ID: {$testUserId}</p>";

// Check if the user is a super admin
$isSuperAdmin = $adminService->isSuperAdmin($testUserId);
echo "<p>Is super admin: " . ($isSuperAdmin ? 'Yes' : 'No') . "</p>";

// Get user access level
$userAccessLevel = $adminService->getUserAccessLevel($testUserId);
echo "<p>User access level: {$userAccessLevel}</p>";

// Get access level name
$levelName = $adminService->getAccessLevelName($userAccessLevel);
echo "<p>Access level name: {$levelName}</p>";

// Check if user has admin access
$hasAdminAccess = $userAccessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN;
echo "<p>Has admin access: " . ($hasAdminAccess ? 'Yes' : 'No') . "</p>";

// Test getting all projects
$projects = $adminService->getAllProjects();
echo "<p>Number of projects: " . count($projects) . "</p>";

echo "<h2>All Projects:</h2>";
echo "<ul>";
foreach ($projects as $project) {
    echo "<li>{$project}</li>";
}
echo "</ul>";

echo "<p>Test completed.</p>
</body>
</html>";
?>