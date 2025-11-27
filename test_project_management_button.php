<?php

// Test script to verify project management button functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing project management button functionality...\n";

// Test 1: Check if main menu keyboard is created correctly for super admin
echo "\n1. Testing main menu keyboard creation for super admin...\n";
$messageService = new MessageService();

// Test with super admin access level (4)
$superAdminMenuKeyboard = $messageService->createMainMenuReplyKeyboard(4);

echo "Super admin menu keyboard structure:\n";
foreach ($superAdminMenuKeyboard as $rowIndex => $row) {
    echo "  Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $buttonIndex => $button) {
        echo "    Button " . ($buttonIndex + 1) . ": " . $button['text'] . "\n";
    }
}

// Check if project management button is present
$hasProjectManagementButton = false;
foreach ($superAdminMenuKeyboard as $row) {
    foreach ($row as $button) {
        if ($button['text'] === '🛠️ Управление проектом') {
            $hasProjectManagementButton = true;
            break 2;
        }
    }
}

if ($hasProjectManagementButton) {
    echo "✅ Project management button found for super admin\n";
} else {
    echo "❌ Project management button NOT found for super admin\n";
}

// Test 2: Check that regular admin doesn't see the button
echo "\n2. Testing main menu keyboard creation for regular admin...\n";
$adminMenuKeyboard = $messageService->createMainMenuReplyKeyboard(3);

$hasProjectManagementButtonAdmin = false;
foreach ($adminMenuKeyboard as $row) {
    foreach ($row as $button) {
        if ($button['text'] === '🛠️ Управление проектом') {
            $hasProjectManagementButtonAdmin = true;
            break 2;
        }
    }
}

if (!$hasProjectManagementButtonAdmin) {
    echo "✅ Project management button correctly hidden for regular admin\n";
} else {
    echo "❌ Project management button incorrectly shown for regular admin\n";
}

// Test 3: Check that guest doesn't see the button
echo "\n3. Testing main menu keyboard creation for guest...\n";
$guestMenuKeyboard = $messageService->createMainMenuReplyKeyboard(0);

$hasProjectManagementButtonGuest = false;
foreach ($guestMenuKeyboard as $row) {
    foreach ($row as $button) {
        if ($button['text'] === '🛠️ Управление проектом') {
            $hasProjectManagementButtonGuest = true;
            break 2;
        }
    }
}

if (!$hasProjectManagementButtonGuest) {
    echo "✅ Project management button correctly hidden for guest\n";
} else {
    echo "❌ Project management button incorrectly shown for guest\n";
}

echo "\nTest completed!\n";