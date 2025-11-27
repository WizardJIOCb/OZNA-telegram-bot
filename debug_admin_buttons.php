<?php

// Debug script to test admin button functionality

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

echo "=== Admin Button Debug Script ===\n\n";

// Test 1: Check access requests
echo "1. Checking access requests...\n";
$requests = $adminService->getAllAccessRequests();
echo "Found " . count($requests) . " requests\n";

if (!empty($requests)) {
    foreach ($requests as $index => $request) {
        echo "  Request $index:\n";
        foreach ($request as $key => $value) {
            echo "    $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
        }
    }
} else {
    echo "  No requests found\n";
}

echo "\n";

// Test 2: Check if clear method works
echo "2. Testing clearAllAccessRequests method...\n";
try {
    $adminService->clearAllAccessRequests();
    echo "  clearAllAccessRequests executed without error\n";
} catch (Exception $e) {
    echo "  Error in clearAllAccessRequests: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check file permissions
echo "3. Checking access requests file...\n";
$filePath = __DIR__ . '/CustomCommands/Services/../../data/access_requests_db.json';
echo "  File path: $filePath\n";
echo "  File exists: " . (file_exists($filePath) ? 'Yes' : 'No') . "\n";
if (file_exists($filePath)) {
    echo "  File readable: " . (is_readable($filePath) ? 'Yes' : 'No') . "\n";
    echo "  File writable: " . (is_writable($filePath) ? 'Yes' : 'No') . "\n";
    $content = file_get_contents($filePath);
    echo "  File content length: " . strlen($content) . " bytes\n";
}

echo "\n";

// Test 4: Check AdminHandler canHandle method
echo "4. Testing AdminHandler canHandle method...\n";
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

$handler = new AdminHandler($adminService, $messageService);

$testCallbacks = [
    'category_Админка_Заявки',
    'adm_req_clear',
    'adm_req_approve_123456',
    'adm_req_reject_123456',
    'adm_req_view_123456'
];

foreach ($testCallbacks as $callback) {
    $canHandle = $handler->canHandle($callback);
    echo "  Callback '$callback': " . ($canHandle ? 'YES' : 'NO') . "\n";
}

echo "\n=== Debug Script Complete ===\n";