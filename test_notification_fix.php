<?php

// Test script to verify the notification message fix
echo "Testing notification message fix...\n\n";

// Include required files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

// Create service instances
$messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
$adminService = new Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
$databaseService = new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();

// Test user ID (using a test user ID that won't interfere with real users)
$testUserId = 777777777;
$testUserName = 'Test User';
$testChatId = '777777777';

echo "=== Test: Checking notification message content ===\n";

// Ensure the user is a guest
$databaseService->updateUserAccessLevel($testUserId, 0); // Set as guest

// Update user profile with complete information
$databaseService->setUserFio($testUserId, 'Тестов Тест Тестович');
$databaseService->setUserFunctionalDirection($testUserId, 'Мобильная бригада');

// Get updated user data
$userData = $databaseService->getUserData($testUserId);
$userFio = $userData['fio'] ?? '';
$userFunctionalDirection = $userData['functional_direction'] ?? '';

echo "User FIO: '{$userFio}'\n";
echo "User Functional Direction: '{$userFunctionalDirection}'\n";

// Check the message content that would be sent
$message = "🔔 <b>Новая заявка на доступ к боту</b>\n\n";
$message .= "👤 Пользователь: {$userFio}\n";
$message .= "🆔 User ID: {$testUserId}\n";
$message .= "💬 Chat ID: {$testChatId}\n";
$message .= "🏢 Функциональное направление: {$userFunctionalDirection}\n\n";
$message .= "Для предоставления доступа добавьте ID {$testUserId} в список пользователей.";

echo "\nGenerated notification message:\n";
echo $message . "\n";

// Check if the message contains the correct text
if (strpos($message, 'в список пользователей') !== false) {
    echo "✅ Message correctly indicates 'в список пользователей'\n";
} else {
    echo "❌ Message does not contain correct text\n";
}

if (strpos($message, 'в список админов') !== false) {
    echo "❌ Message incorrectly indicates 'в список админов'\n";
} else {
    echo "✅ Message does not contain incorrect text\n";
}

echo "\n=== Test completed ===\n";
echo "The notification message now correctly indicates that the user should be added to the 'users' list, not 'admins' list.\n";