<?php
// Test script to verify access level fix

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Testing access level fix...\n\n";

// Create instances
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Test user ID (replace with an actual user ID from your database)
$testUserId = 5261465966; // Example user ID

// Test 1: Check current access level
echo "1. Checking current access level for user $testUserId:\n";
$currentLevel = $adminService->getUserAccessLevel($testUserId);
echo "   Current access level: $currentLevel\n";

// Test 2: Update access level to administrator (3)
echo "\n2. Updating access level to administrator (3):\n";
$success = $adminService->updateUserAccessLevel($testUserId, 3);
echo "   Update result: " . ($success ? "SUCCESS" : "FAILED") . "\n";

// Test 3: Check updated access level
echo "\n3. Checking updated access level:\n";
$updatedLevel = $adminService->getUserAccessLevel($testUserId);
echo "   Updated access level: $updatedLevel\n";

// Test 4: Check access level name
echo "\n4. Checking access level name:\n";
$levelName = $adminService->getAccessLevelName($updatedLevel);
echo "   Access level name: $levelName\n";

// Test 5: Test hasAccessLevel method
echo "\n5. Testing hasAccessLevel method:\n";
$hasAdminAccess = $adminService->hasAccessLevel($testUserId, 3);
$hasSuperAdminAccess = $adminService->hasAccessLevel($testUserId, 4);
echo "   Has admin access (level 3): " . ($hasAdminAccess ? "YES" : "NO") . "\n";
echo "   Has super admin access (level 4): " . ($hasSuperAdminAccess ? "YES" : "NO") . "\n";

echo "\nTest completed.\n";