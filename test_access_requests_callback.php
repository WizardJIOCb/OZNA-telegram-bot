<?php
/**
 * Test script for debugging the "Заявки на доступ" button functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Access Requests Callback ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

// Create services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Create AdminHandler
$adminHandler = new AdminHandler($adminService, $messageService);

// Test if AdminHandler can handle the callback for "Заявки на доступ"
$callbackData = 'category_Админка_Заявки';
echo "Testing if AdminHandler can handle callback: '{$callbackData}'\n";
$canHandle = $adminHandler->canHandle($callbackData);
echo "Result: " . ($canHandle ? 'YES' : 'NO') . "\n\n";

// Test with a test user ID that should be an admin (replace with actual admin user ID)
$testAdminUserId = 5261465966; // This should be an admin user ID
echo "Testing access level for user ID: {$testAdminUserId}\n";
$userAccessLevel = $adminService->getUserAccessLevel($testAdminUserId);
echo "User access level: {$userAccessLevel}\n";
$levelName = $adminService->getAccessLevelName($userAccessLevel);
echo "Level name: {$levelName}\n\n";

// Check if user has admin access
$hasAdminAccess = $userAccessLevel >= 3;
echo "Has admin access (level 3 or higher): " . ($hasAdminAccess ? 'YES' : 'NO') . "\n\n";

// Test getting all access requests
echo "Getting all access requests...\n";
$requests = $adminService->getAllAccessRequests();
echo "Total requests found: " . count($requests) . "\n";

if (!empty($requests)) {
    echo "Displaying first request:\n";
    $firstRequest = $requests[0];
    print_r($firstRequest);
}

echo "\n=== Test Complete ===\n";