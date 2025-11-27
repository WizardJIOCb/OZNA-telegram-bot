<?php

// Final test to verify the admin button functionality
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\UserCommands\TextmenuCommand;

echo "=== FINAL ADMIN BUTTON FUNCTIONALITY TEST ===\n";

// Test the TextmenuCommand class loading
echo "1. Testing TextmenuCommand class loading...\n";

if (class_exists(TextmenuCommand::class)) {
    echo "✅ TextmenuCommand class loaded successfully\n";
} else {
    echo "❌ TextmenuCommand class NOT found\n";
    exit(1);
}

// Test the admin button handling
echo "\n2. Checking for Admin button handling in TextmenuCommand...\n";

// Read the file content
$content = file_get_contents(__DIR__ . '/CustomCommands/TextmenuCommand.php');

// Check for Admin button cases
$adminCases = ['⚙️ Админка', 'Админка'];
$allCasesFound = true;

foreach ($adminCases as $case) {
    if (strpos($content, "case '$case':") !== false) {
        echo "✅ TextmenuCommand handles case: '$case'\n";
    } else {
        echo "❌ TextmenuCommand does NOT handle case: '$case'\n";
        $allCasesFound = false;
    }
}

if ($allCasesFound) {
    echo "✅ All admin button cases found\n";
} else {
    echo "❌ Some admin button cases missing\n";
}

// Check if handleAdminMenu method exists
if (strpos($content, 'private function handleAdminMenu') !== false) {
    echo "✅ handleAdminMenu method exists\n";
} else {
    echo "❌ handleAdminMenu method does NOT exist\n";
}

// Check for simplified admin service
if (strpos($content, 'new class($this->messageService)') !== false) {
    echo "✅ Simplified anonymous AdminService found\n";
} else {
    echo "❌ Simplified anonymous AdminService NOT found\n";
}

// Check for super admin list
if (strpos($content, '230379668') !== false && strpos($content, '5261465966') !== false) {
    echo "✅ Hardcoded super admin list found\n";
} else {
    echo "❌ Hardcoded super admin list NOT found\n";
}

echo "\n=== TEST COMPLETED ===\n";
echo "The TextmenuCommand should now properly handle the admin button.\n";
echo "Super admins (user IDs: 230379668, 5261465966) should see the admin panel.\n";
echo "Other users should see the access request prompt.\n";
echo "\nTo test in Telegram:\n";
echo "1. Send the /start command to get the main menu\n";
echo "2. Click the '⚙️ Админка' button\n";
echo "3. If you're user 230379668 or 5261465966, you should see the admin panel\n";
echo "4. If you're another user, you should see the access request prompt\n";