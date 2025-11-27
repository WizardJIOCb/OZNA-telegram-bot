<?php
/**
 * Test script to verify the "Заявки на доступ" fix
 */

echo "=== Testing Access Requests Fix ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

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
    echo "Displaying first few requests:\n";
    $count = 0;
    foreach ($requests as $request) {
        if ($count >= 3) break; // Show only first 3
        echo "  User ID: " . $request['user_id'] . "\n";
        echo "  User Name: " . $request['user_name'] . "\n";
        echo "  Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n\n";
        $count++;
    }
    
    if (count($requests) > 3) {
        echo "  ... and " . (count($requests) - 3) . " more requests\n";
    }
} else {
    echo "No access requests found.\n";
}

echo "\nTest completed.\n";