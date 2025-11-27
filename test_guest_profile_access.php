<?php

// Test script to verify that guests can set their FIO and functional direction
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "Testing guest profile access functionality...\n";

// Test user ID (you can change this to a real guest user ID for testing)
$testUserId = 123456789; // Replace with actual guest user ID

// Initialize services
$messageService = new MessageService();
$adminService = new AdminService($messageService);
$databaseService = new DatabaseService();

// Check current access level
$currentLevel = $adminService->getUserAccessLevel($testUserId);
$levelName = $adminService->getAccessLevelName($currentLevel);

echo "Current access level for user {$testUserId}: {$levelName} ({$currentLevel})\n";

// Test setting FIO for guest
echo "\n1. Testing FIO setting for guest...\n";
$testFio = "Тестовый Гость ФИО";

// Use the database service directly to set FIO
$success = $databaseService->setUserFio($testUserId, $testFio);

if ($success) {
    echo "✅ FIO successfully set to: {$testFio}\n";
    
    // Verify FIO was set
    $userData = $databaseService->getUserData($testUserId);
    $setFio = $userData['fio'] ?? '';
    
    if ($setFio === $testFio) {
        echo "✅ FIO verification successful: {$setFio}\n";
    } else {
        echo "❌ FIO verification failed. Expected: {$testFio}, Got: {$setFio}\n";
    }
} else {
    echo "❌ Failed to set FIO\n";
}

// Test setting functional direction for guest
echo "\n2. Testing functional direction setting for guest...\n";
$testFn = "Мобильная бригада";

// Use the database service directly to set functional direction
$success = $databaseService->setUserFunctionalDirection($testUserId, $testFn);

if ($success) {
    echo "✅ Functional direction successfully set to: {$testFn}\n";
    
    // Verify functional direction was set
    $userData = $databaseService->getUserData($testUserId);
    $setFn = $userData['functional_direction'] ?? '';
    
    if ($setFn === $testFn) {
        echo "✅ Functional direction verification successful: {$setFn}\n";
    } else {
        echo "❌ Functional direction verification failed. Expected: {$testFn}, Got: {$setFn}\n";
    }
} else {
    echo "❌ Failed to set functional direction\n";
}

// Test that guest can now submit access request (check profile completion)
echo "\n3. Testing profile completion for access request...\n";
$userData = $databaseService->getUserData($testUserId);
$userFio = $userData['fio'] ?? '';
$userFunctionalDirection = $userData['functional_direction'] ?? '';

$profileComplete = !empty($userFio) && $userFio !== 'Без ФИО' && 
                  !empty($userFunctionalDirection) && $userFunctionalDirection !== 'Не указано';

if ($profileComplete) {
    echo "✅ Profile is complete. Guest can now submit access request.\n";
    echo "   FIO: {$userFio}\n";
    echo "   Functional Direction: {$userFunctionalDirection}\n";
} else {
    echo "❌ Profile is incomplete. Guest cannot submit access request yet.\n";
    if (empty($userFio) || $userFio === 'Без ФИО') {
        echo "   Missing FIO\n";
    }
    if (empty($userFunctionalDirection) || $userFunctionalDirection === 'Не указано') {
        echo "   Missing Functional Direction\n";
    }
}

echo "\nGuest profile access test completed.\n";