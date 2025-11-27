<?php
/**
 * Check user access level and profile information
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== User Access Check ===\n\n";

// For testing purposes, let's use a real user ID from the database
// You would replace this with your actual user ID
$userId = 230379668; // This appears to be one of the admin IDs from hook.php

echo "Checking access for user ID: {$userId}\n\n";

try {
    // Create database service
    $databaseService = new DatabaseService();
    
    // Get user access level
    $accessLevel = $databaseService->getUserAccessLevel($userId);
    
    echo "User access level: {$accessLevel}\n";
    
    // Get access level name
    $adminService = new AdminService(new MessageService());
    $levelName = $adminService->getAccessLevelName($accessLevel);
    
    echo "Access level name: {$levelName}\n\n";
    
    // Check if user is super admin
    $isSuperAdmin = $adminService->isSuperAdmin($userId);
    echo "Is super admin: " . ($isSuperAdmin ? 'YES' : 'NO') . "\n\n";
    
    // Get user data
    $userData = $databaseService->getUserData($userId);
    
    echo "User data:\n";
    print_r($userData);
    
    echo "\n";
    
    // Check specific fields
    $fio = $userData['fio'] ?? 'Not set';
    $functionalDirection = $userData['functional_direction'] ?? 'Not set';
    $accessLevelDb = $userData['access_level'] ?? 'Not set';
    
    echo "FIO: {$fio}\n";
    echo "Functional direction: {$functionalDirection}\n";
    echo "Access level in database: {$accessLevelDb}\n\n";
    
    // Check access requirements
    echo "=== Access Requirements Check ===\n";
    
    if ($accessLevel == 0) {
        echo "❌ USER HAS GUEST ACCESS LEVEL (0)\n";
        echo "   This will prevent access to tabel functionality including electrical equipment button\n";
        echo "   Solution: Contact an administrator to request access level upgrade\n\n";
    } else {
        echo "✅ User has access level {$accessLevel} ({$levelName})\n\n";
    }
    
    if ($fio === 'Без ФИО' || empty($fio)) {
        echo "❌ USER MISSING FIO\n";
        echo "   This will prevent access to tabel functionality\n";
        echo "   Solution: Set FIO using the /fio command\n\n";
    } else {
        echo "✅ User has FIO set: {$fio}\n\n";
    }
    
    if ($functionalDirection === 'Не указано' || empty($functionalDirection)) {
        echo "❌ USER MISSING FUNCTIONAL DIRECTION\n";
        echo "   This will prevent access to tabel functionality\n";
        echo "   Solution: Set functional direction using the /profile command\n\n";
    } else {
        echo "✅ User has functional direction set: {$functionalDirection}\n\n";
    }
    
    // Test the electrical equipment menu access specifically
    echo "=== Electrical Equipment Menu Access Check ===\n";
    
    // With our fix, electrical equipment menus should be accessible even without FIO/functional direction
    // as long as the user has at least guest access (level 0)
    if ($accessLevel > 0) {
        echo "✅ User has sufficient access level for electrical equipment menu\n";
        echo "   (Access level {$accessLevel} > 0)\n\n";
    } else {
        echo "❌ User has guest access level (0)\n";
        echo "   This will prevent ALL tabel functionality including electrical equipment button\n";
        echo "   Solution: Contact administrator to upgrade access level\n\n";
    }
    
    echo "=== Recommendations ===\n";
    
    if ($accessLevel == 0) {
        echo "1. Contact an administrator to request access level upgrade\n";
        echo "2. Your user ID is: {$userId}\n";
        echo "3. Ask to be granted at least User access level (1)\n\n";
    }
    
    if ($fio === 'Без ФИО' || empty($fio)) {
        echo "1. Set your FIO using the command: /fio Your Full Name\n";
        echo "2. Example: /fio Ivanov Ivan Ivanovich\n\n";
    }
    
    if ($functionalDirection === 'Не указано' || empty($functionalDirection)) {
        echo "1. Set your functional direction using the /profile command\n";
        echo "2. Select your functional direction from the menu\n\n";
    }
    
    if ($accessLevel > 0) {
        echo "✅ All requirements met for electrical equipment button access!\n";
        echo "   With our fix, electrical equipment menus don't require FIO/functional direction\n";
        echo "   If button still doesn't work, try:\n";
        echo "   1. Restarting Telegram app\n";
        echo "   2. Checking internet connection\n";
        echo "   3. Contacting bot administrator\n\n";
    }
    
} catch (Exception $e) {
    echo "Error checking user access: " . $e->getMessage() . "\n";
    echo "This might indicate a database connection issue\n";
    echo "Check that the database server is accessible and credentials are correct\n";
}

echo "=== User Access Check Complete ===\n";
?>