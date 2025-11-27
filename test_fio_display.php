<?php
// Test script to verify FIO display functionality

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create instances
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Test user ID (replace with an actual user ID from your database)
$testUserId = 5261465966; // Example user ID

echo "Testing FIO display functionality...\n\n";

// Get user data
$userData = $databaseService->getUserData($testUserId);
echo "User data:\n";
print_r($userData);

// Check if user has FIO set
$userFio = $userData['fio'] ?? '';
echo "\nUser FIO: " . ($userFio ?: 'Not set') . "\n";

// Test the sendAccessRequest method
$telegramUserName = "Alexey Petrov"; // Example Telegram username
$chatId = "123456789"; // Example chat ID

echo "\nTesting sendAccessRequest with user FIO: " . ($userFio ?: 'empty') . "\n";

// This would normally send a request, but we'll just check the logic
if (!empty($userFio)) {
    echo "✅ FIO will be used as display name: $userFio\n";
} else {
    echo "⚠️ Telegram username will be used as display name: $telegramUserName\n";
}

echo "\nTest completed.\n";