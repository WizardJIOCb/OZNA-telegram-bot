<?php
/**
 * Simple test script for debugging the "Заявки на доступ" functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Simple Test Access Requests ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Test getting all access requests
echo "Getting all access requests...\n";
$requests = $adminService->getAllAccessRequests();
echo "Total requests found: " . count($requests) . "\n";

if (!empty($requests)) {
    echo "Displaying first request:\n";
    $firstRequest = $requests[0];
    print_r($firstRequest);
    
    echo "\nChecking request details:\n";
    echo "User ID: " . $firstRequest['user_id'] . "\n";
    echo "User Name: " . $firstRequest['user_name'] . "\n";
    echo "Timestamp: " . date('Y-m-d H:i:s', $firstRequest['timestamp']) . "\n";
}

// Test user access level
$testUserId = 5261465966; // This should be an admin user ID
echo "\nTesting access level for user ID: {$testUserId}\n";
$userAccessLevel = $adminService->getUserAccessLevel($testUserId);
echo "User access level: {$userAccessLevel}\n";
$levelName = $adminService->getAccessLevelName($userAccessLevel);
echo "Level name: {$levelName}\n";

echo "\n=== Test Complete ===\n";