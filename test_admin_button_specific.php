<?php

// Specific test for Admin button functionality
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/TextmenuCommand.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== SPECIFIC ADMIN BUTTON TEST ===\n";

// Test 1: Check if main menu contains Admin button
echo "\n1. Checking main menu structure...\n";
$messageService = new MessageService();
$mainMenu = $messageService->createMainMenuReplyKeyboard();

$adminButtonFound = false;
foreach ($mainMenu as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && ($button['text'] === '⚙️ Админка' || $button['text'] === 'Админка')) {
            $adminButtonFound = true;
            echo "✅ Admin button found in main menu: " . $button['text'] . "\n";
        }
    }
}

if (!$adminButtonFound) {
    echo "❌ Admin button NOT found in main menu\n";
}

// Test 2: Check TextmenuCommand handling
echo "\n2. Checking TextmenuCommand handling...\n";
$textmenuContent = file_get_contents(__DIR__ . '/CustomCommands/TextmenuCommand.php');

// Check for Admin button cases
$adminCases = ['⚙️ Админка', 'Админка'];
foreach ($adminCases as $case) {
    if (strpos($textmenuContent, "case '$case':") !== false) {
        echo "✅ TextmenuCommand handles case: '$case'\n";
    } else {
        echo "❌ TextmenuCommand does NOT handle case: '$case'\n";
    }
}

// Check if handleAdminMenu method exists
if (strpos($textmenuContent, 'private function handleAdminMenu') !== false) {
    echo "✅ handleAdminMenu method exists\n";
} else {
    echo "❌ handleAdminMenu method does NOT exist\n";
}

// Test 3: Check AdminService functionality
echo "\n3. Checking AdminService functionality...\n";
try {
    $adminService = new AdminService($messageService);
    echo "✅ AdminService instantiated successfully\n";
    
    // Test access level checking
    // Using a known super admin ID
    $testUserId = 230379668; // This should be a super admin
    $accessLevel = $adminService->getUserAccessLevel($testUserId);
    echo "✅ User access level check: $accessLevel\n";
    
    $isSuperAdmin = $adminService->isSuperAdmin($testUserId);
    echo "✅ User is super admin: " . ($isSuperAdmin ? 'true' : 'false') . "\n";
    
    $levelName = $adminService->getAccessLevelName($accessLevel);
    echo "✅ Access level name: $levelName\n";
    
} catch (Exception $e) {
    echo "❌ AdminService error: " . $e->getMessage() . "\n";
}

// Test 4: Simulate Admin button click
echo "\n4. Simulating Admin button click...\n";
echo "This would normally be handled by TextmenuCommand when user clicks '⚙️ Админка'\n";
echo "The expected behavior:\n";
echo "  - If user has admin access: Show admin panel with project management and access requests\n";
echo "  - If user doesn't have admin access: Show request access prompt\n";

echo "\n=== TEST COMPLETED ===\n";
echo "\nIf the admin button is still not working, check:\n";
echo "1. Telegram bot logs for any errors\n";
echo "2. Make sure the webhook is properly configured\n";
echo "3. Verify that the user has the correct access level in the database\n";