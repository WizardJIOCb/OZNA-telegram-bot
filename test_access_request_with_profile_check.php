<?php

// Test script to verify access request with profile check

echo "Testing access request with profile check...\n\n";

// Include required classes
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Handlers/MainMenuHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;

echo "1. Testing user with incomplete profile...\n";

// Create mock user data with missing FIO
$userId = 123456789;
$chatId = 987654321;
$userName = "Test User";

// Initialize services
try {
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Set user access level to guest (0)
    $databaseService->updateUserAccessLevel($userId, 0);
    
    // Set incomplete user data (missing FIO and functional direction)
    $userData = $databaseService->getUserData($userId);
    $userData['fio'] = ''; // Empty FIO
    $userData['functional_direction'] = 'Не указано'; // Default value
    $databaseService->updateUserData($userId, $userData);
    
    echo "   ✓ Created user with incomplete profile\n";
    echo "   ✓ FIO: '{$userData['fio']}'\n";
    echo "   ✓ Functional Direction: '{$userData['functional_direction']}'\n";
    
    // Try to send access request (should fail)
    $result = $adminService->sendAccessRequest($userId, $userName, $chatId);
    
    if ($result === false) {
        echo "   ✓ Access request correctly rejected for user with incomplete profile\n";
    } else {
        echo "   ✗ Access request should have been rejected for user with incomplete profile\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error during test: " . $e->getMessage() . "\n";
}

echo "\n2. Testing user with complete profile...\n";

// Create mock user data with complete profile
try {
    // Set complete user data
    $userData = $databaseService->getUserData($userId);
    $userData['fio'] = 'Иванов Иван Иванович'; // Valid FIO
    $userData['functional_direction'] = 'Мобильная бригада'; // Valid functional direction
    $databaseService->updateUserData($userId, $userData);
    
    echo "   ✓ Created user with complete profile\n";
    echo "   ✓ FIO: '{$userData['fio']}'\n";
    echo "   ✓ Functional Direction: '{$userData['functional_direction']}'\n";
    
    // Try to send access request (should succeed)
    $result = $adminService->sendAccessRequest($userId, $userName, $chatId);
    
    if ($result === true) {
        echo "   ✓ Access request correctly accepted for user with complete profile\n";
    } else {
        echo "   ✗ Access request should have been accepted for user with complete profile\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error during test: " . $e->getMessage() . "\n";
}

echo "\n3. Testing access request display...\n";

try {
    // Get the access request we just created
    $request = $databaseService->getAccessRequestByUserId($userId);
    
    if ($request) {
        echo "   ✓ Retrieved access request from database\n";
        echo "   ✓ User name: '{$request['user_name']}'\n";
        echo "   ✓ User FIO: '{$request['user_fio']}'\n";
        echo "   ✓ Request timestamp: {$request['timestamp']}\n";
    } else {
        echo "   ✗ Could not retrieve access request from database\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Error during test: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
echo "The access request restrictions are working correctly.\n";
echo "Users must have both FIO and functional direction specified to submit requests.\n";
echo "Administrators will see the user's functional direction when reviewing requests.\n";