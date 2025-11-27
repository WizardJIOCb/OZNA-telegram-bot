<?php

// Test script to verify admin button fixes

// Include the necessary files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

echo "=== Testing Admin Button Fixes ===\n\n";

// Create services
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$handler = new AdminHandler($adminService, $messageService);

// Test 1: Check if AdminHandler can handle adm_req_* callbacks
echo "1. Testing AdminHandler canHandle method...\n";

$testCallbacks = [
    'category_Админка_Заявки',
    'adm_req_clear',
    'adm_req_approve_123456',
    'adm_req_reject_123456',
    'adm_req_view_123456'
];

foreach ($testCallbacks as $callback) {
    $canHandle = $handler->canHandle($callback);
    echo "   Callback '$callback': " . ($canHandle ? 'YES' : 'NO') . "\n";
}

echo "\n";

// Test 2: Check current access requests
echo "2. Checking current access requests...\n";
$requests = $adminService->getAllAccessRequests();
echo "   Found " . count($requests) . " requests\n";

if (!empty($requests)) {
    foreach ($requests as $index => $request) {
        echo "   Request $index: User ID " . $request['user_id'] . ", Status: " . ($request['status'] ?? 'unknown') . "\n";
    }
}

echo "\n";

// Test 3: Test clearing all access requests
echo "3. Testing clearAllAccessRequests...\n";
echo "   Clearing all requests...\n";
$adminService->clearAllAccessRequests();

// Check requests after clearing
$requestsAfter = $adminService->getAllAccessRequests();
echo "   Found " . count($requestsAfter) . " requests after clearing\n";

echo "\n=== Test Complete ===\n";