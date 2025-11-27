<?php

// Simple test for AdminService
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== SIMPLE ADMIN SERVICE TEST ===\n";

try {
    echo "1. Testing DatabaseService...\n";
    $dbService = new DatabaseService();
    echo "✅ DatabaseService instantiated successfully\n";
    
    // Test with a known user ID
    $testUserId = 230379668; // Super admin ID
    echo "2. Testing getUserAccessLevel for user $testUserId...\n";
    $accessLevel = $dbService->getUserAccessLevel($testUserId);
    echo "✅ User access level: $accessLevel\n";
    
    echo "3. Testing AdminService...\n";
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    echo "✅ AdminService instantiated successfully\n";
    
    echo "4. Testing isSuperAdmin for user $testUserId...\n";
    $isSuperAdmin = $adminService->isSuperAdmin($testUserId);
    echo "✅ User is super admin: " . ($isSuperAdmin ? 'true' : 'false') . "\n";
    
    echo "5. Testing getUserAccessLevel through AdminService...\n";
    $adminAccessLevel = $adminService->getUserAccessLevel($testUserId);
    echo "✅ User access level through AdminService: $adminAccessLevel\n";
    
    echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}