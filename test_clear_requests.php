<?php

// Test script to simulate clearing all access requests

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Clear Access Requests ===\n\n";

// Include required files
require_once __DIR__ . '/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

echo "Testing clear all access requests functionality:\n\n";

// Create services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Check current requests
echo "Current access requests:\n";
$requests = $adminService->getAllAccessRequests();
echo "  Found " . count($requests) . " access requests\n";

if (!empty($requests)) {
    foreach ($requests as $index => $request) {
        echo "    Request #{$index}: User ID {$request['user_id']}, Name: {$request['user_name']}\n";
    }
} else {
    echo "  No access requests found\n";
}

// Simulate clearing all requests
echo "\nClearing all access requests...\n";
$adminService->clearAllAccessRequests();

// Check requests after clearing
echo "\nAccess requests after clearing:\n";
$requests = $adminService->getAllAccessRequests();
echo "  Found " . count($requests) . " access requests\n";

if (empty($requests)) {
    echo "  All access requests cleared successfully!\n";
} else {
    echo "  ERROR: Requests were not cleared!\n";
    foreach ($requests as $index => $request) {
        echo "    Request #{$index}: User ID {$request['user_id']}, Name: {$request['user_name']}\n";
    }
}

// Restore test data
echo "\nRestoring test data...\n";
$testRequests = [
    [
        'user_id' => '111111111',
        'user_name' => 'Тестовый Пользователь 1',
        'user_telegram_name' => 'TestUser1',
        'user_fio' => 'Тестовый Пользователь 1 Полное Имя',
        'chat_id' => '111111111',
        'timestamp' => 1756718315
    ],
    [
        'user_id' => '222222222',
        'user_name' => 'Тестовый Пользователь 2',
        'user_telegram_name' => 'TestUser2',
        'user_fio' => 'Тестовый Пользователь 2 Полное Имя',
        'chat_id' => '222222222',
        'timestamp' => 1756718315
    ]
];

// Write directly to the file
$filePath = __DIR__ . '/data/access_requests.json';
$jsonData = json_encode($testRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($filePath, $jsonData);

echo "\nTest data restored.\n";
echo "\nTest completed.\n";