<?php

// Debug script to test access requests functionality

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Debug Access Requests ===\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

echo "1. Loading access requests from file...\n";
$requests = $adminService->getAllAccessRequests();
echo "Total requests found: " . count($requests) . "\n\n";

if (!empty($requests)) {
    echo "2. Displaying requests:\n";
    foreach ($requests as $index => $request) {
        echo "Request #" . ($index + 1) . ":\n";
        echo "  User ID: " . $request['user_id'] . "\n";
        echo "  User Name: " . $request['user_name'] . "\n";
        echo "  Chat ID: " . $request['chat_id'] . "\n";
        echo "  Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
        echo "  ---\n";
    }
}

echo "\n3. Testing clear all access requests...\n";
echo "Before clearing: " . count($adminService->getAllAccessRequests()) . " requests\n";

// Clear all requests
$adminService->clearAllAccessRequests();

echo "After clearing: " . count($adminService->getAllAccessRequests()) . " requests\n";

echo "\n4. Verifying file content...\n";
$filePath = __DIR__ . '/data/access_requests.json';
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    echo "File content:\n" . $content . "\n";
} else {
    echo "File does not exist\n";
}

echo "\n=== Debug Complete ===\n";