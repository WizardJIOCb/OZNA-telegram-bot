<?php

// Test for fixed TextmenuCommand
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/TextmenuCommand.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\UserCommands\TextmenuCommand;

echo "=== TESTING FIXED TEXTMENUCOMMAND ===\n";

try {
    echo "1. Testing TextmenuCommand class loading...\n";
    
    // Check if the class exists
    if (class_exists('Longman\TelegramBot\Commands\UserCommands\TextmenuCommand')) {
        echo "✅ TextmenuCommand class loaded successfully\n";
    } else {
        echo "❌ TextmenuCommand class NOT found\n";
        exit(1);
    }
    
    echo "2. Checking for Admin button handling...\n";
    
    // Read the file content
    $content = file_get_contents(__DIR__ . '/CustomCommands/TextmenuCommand.php');
    
    // Check for Admin button cases
    if (strpos($content, "case '⚙️ Админка':") !== false && strpos($content, "case 'Админка':") !== false) {
        echo "✅ Admin button cases found\n";
    } else {
        echo "❌ Admin button cases NOT found\n";
    }
    
    // Check for simplified handleAdminMenu method
    if (strpos($content, "handleAdminMenu(string \$chatId, int \$userId): ServerResponse") !== false) {
        echo "✅ handleAdminMenu method found\n";
    } else {
        echo "❌ handleAdminMenu method NOT found\n";
    }
    
    // Check for hardcoded super admin list
    if (strpos($content, "230379668") !== false) {
        echo "✅ Hardcoded super admin list found\n";
    } else {
        echo "❌ Hardcoded super admin list NOT found\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    echo "The fixed TextmenuCommand should now work without database connectivity.\n";
    echo "The Admin button should respond correctly for super admins (user IDs: 230379668, 5261465966).\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
}