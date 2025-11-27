<?php

// Simple test to check core functionality

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Core Functionality Test ===\n\n";

// Include required files
require_once __DIR__ . '/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

// Test data
$testCallbacks = [
    'adm_req_clear',
    'adm_req_view_111111111',
    'adm_req_approve_111111111',
    'adm_req_reject_111111111',
    'category_Админка_Заявки'
];

echo "Testing core functionality:\n\n";

// Create services
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$adminHandler = new AdminHandler($adminService, $messageService);

foreach ($testCallbacks as $callback) {
    echo "Processing callback: '{$callback}'\n";
    
    // Test if AdminHandler can handle this callback
    $canHandle = $adminHandler->canHandle($callback);
    echo "  AdminHandler canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    if ($canHandle) {
        echo "  -> AdminHandler would handle this callback\n";
    }
    
    echo "\n";
}

// Test AdminService functionality
echo "Testing AdminService functionality:\n";
$requests = $adminService->getAllAccessRequests();
echo "  Found " . count($requests) . " access requests\n";

if (!empty($requests)) {
    foreach ($requests as $index => $request) {
        echo "    Request #{$index}: User ID {$request['user_id']}, Name: {$request['user_name']}\n";
    }
}

echo "\nCore functionality test completed.\n";