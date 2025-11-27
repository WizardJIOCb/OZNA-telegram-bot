<?php
require_once 'vendor/autoload.php';
require_once 'CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$db = new DatabaseService();

// User IDs with access level 3 (admins):
// 4225504 - Петров Алексей Владимирович
// 5791428629 - Давлетшин Альберт Хамзович
// 7897006540 - Иванов Иван Иванович

echo "=== Current Admins (Level 3) ===\n\n";
$admins = [4225504, 5791428629, 7897006540];

foreach ($admins as $userId) {
    $userData = $db->getUserData($userId);
    $fio = $userData['fio'] ?? 'Unknown';
    $accessLevel = $userData['access_level'] ?? 'NOT SET';
    echo "User ID: {$userId}, FIO: {$fio}, Access Level: {$accessLevel}\n";
}

echo "\n\n=== Promoting first admin to Super Admin (Level 4) ===\n\n";

// Let's promote the first one
$targetUserId = 4225504; // Петров Алексей Владимирович

$userData = $db->getUserData($targetUserId);
$userData['access_level'] = 4;

if ($db->updateUserData($targetUserId, $userData)) {
    echo "✅ Successfully promoted user {$targetUserId} to Super Admin (Level 4)\n";
    
    // Verify
    $updatedData = $db->getUserData($targetUserId);
    echo "Verification - Access Level: " . ($updatedData['access_level'] ?? 'NOT SET') . "\n";
} else {
    echo "❌ Failed to promote user\n";
}

echo "\n\n=== Checking for Super Admins again ===\n\n";
$superAdmins = $db->getAllSuperAdmins();
echo "Found " . count($superAdmins) . " super admins: " . implode(', ', $superAdmins) . "\n";
