<?php
require_once 'vendor/autoload.php';
require_once 'CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$db = new DatabaseService();

echo "=== Checking for Super Admins ===\n\n";

// Get all super admins
$superAdmins = $db->getAllSuperAdmins();

echo "Found " . count($superAdmins) . " super admins: " . implode(', ', $superAdmins) . "\n\n";

// Also check all users and their access levels
echo "=== All users with access levels ===\n\n";

$query = "SELECT user_id, data FROM ozna_data";
$conn = $db->getConnection();
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $userId = $row['user_id'];
        $data = json_decode($row['data'], true);
        $accessLevel = $data['access_level'] ?? 'NOT SET';
        $fio = $data['fio'] ?? 'Unknown';
        
        echo "User ID: {$userId}, FIO: {$fio}, Access Level: {$accessLevel}\n";
        
        if ($accessLevel == 4 || $accessLevel === '4') {
            echo "  ^^ THIS IS A SUPER ADMIN!\n";
        }
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
