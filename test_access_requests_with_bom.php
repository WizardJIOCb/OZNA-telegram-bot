<?php

// Test script to verify access requests functionality with BOM
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing access requests functionality with BOM...\n";

// Initialize services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Create a file with BOM for testing
$filePath = __DIR__ . '/data/access_requests.json';
$bomData = "\xEF\xBB\xBF" . '[{"user_id": "12345", "user_name": "Test User", "timestamp": 1234567890}]';
file_put_contents($filePath, $bomData);

echo "Created file with BOM content.\n";

// Check current access requests
echo "Current access requests:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";
echo "Data: " . json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Check file content
echo "\nFile content:\n";
if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    echo "Raw content (hex): " . bin2hex($content) . "\n";
    $decoded = json_decode($adminService->removeBOM($content), true);
    echo "Decoded content: " . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "File not found!\n";
}

// Clear the requests
echo "\nClearing access requests...\n";
$adminService->clearAllAccessRequests();

// Check again
echo "After clearing:\n";
$requests = $adminService->getAllAccessRequests();
echo "Count: " . count($requests) . "\n";

// Clean up - clear the file properly
file_put_contents($filePath, '[]');

echo "\nTest completed and file cleaned up.\n";