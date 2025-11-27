<?php

// Test script for access request fix in real environment
echo "Testing access request fix in real environment...\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

// Create service instances
$messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
$adminService = new Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
$databaseService = new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();

// Test user ID (using a test user ID that won't interfere with real users)
$testUserId = 888888888;

echo "=== Test 1: Guest user with incomplete profile ===\n";

// Ensure the user is a guest
$databaseService->updateUserAccessLevel($testUserId, 0); // Set as guest

// Ensure profile is incomplete
$databaseService->setUserFio($testUserId, ''); // Empty FIO
$databaseService->setUserFunctionalDirection($testUserId, 'Не указано'); // Unspecified functional direction

// Get user data to verify
$userData = $databaseService->getUserData($testUserId);
$userFio = $userData['fio'] ?? '';
$userFunctionalDirection = $userData['functional_direction'] ?? '';

echo "User FIO: '{$userFio}'\n";
echo "User Functional Direction: '{$userFunctionalDirection}'\n";

// Try to send access request through AdminService
// This should fail because of the validation in sendAccessRequest method
try {
    $result = $adminService->sendAccessRequest($testUserId, 'Test User', '888888888');
    echo "Access request result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    if (!$result) {
        echo "✅ Correctly rejected access request for user with incomplete profile\n";
    } else {
        echo "❌ Incorrectly allowed access request for user with incomplete profile\n";
    }
} catch (Exception $e) {
    echo "Error during access request: " . $e->getMessage() . "\n";
}

echo "\n=== Test 2: Guest user with complete profile ===\n";

// Update user profile with complete information
$databaseService->setUserFio($testUserId, 'Тестов Тест Тестович');
$databaseService->setUserFunctionalDirection($testUserId, 'Мобильная бригада');

// Get updated user data
$userData = $databaseService->getUserData($testUserId);
$userFio = $userData['fio'] ?? '';
$userFunctionalDirection = $userData['functional_direction'] ?? '';

echo "User FIO: '{$userFio}'\n";
echo "User Functional Direction: '{$userFunctionalDirection}'\n";

// Try to send access request (should succeed)
try {
    $result = $adminService->sendAccessRequest($testUserId, 'Test User', '888888888');
    echo "Access request result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
    
    if ($result) {
        echo "✅ Correctly allowed access request for user with complete profile\n";
    } else {
        echo "❌ Incorrectly rejected access request for user with complete profile\n";
    }
} catch (Exception $e) {
    echo "Error during access request: " . $e->getMessage() . "\n";
}

echo "\n=== Test 3: Cleaning up test data ===\n";
// Reset user to guest level
$databaseService->updateUserAccessLevel($testUserId, 0);
echo "✅ Test user reset to guest level\n";

echo "\n=== Test completed ===\n";
echo "The fix ensures that guests must complete their profile before submitting access requests.\n";