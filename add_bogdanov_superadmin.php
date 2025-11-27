<?php
require_once 'vendor/autoload.php';
require_once 'CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$db = new DatabaseService();

echo "=== Searching for Богданов Борис Сергеевич ===\n\n";

$query = "SELECT user_id, data FROM ozna_data WHERE data LIKE '%Богданов%'";
$conn = $db->getConnection();
$result = $conn->query($query);

if ($result) {
    $found = false;
    while ($row = $result->fetch_assoc()) {
        $userId = $row['user_id'];
        $data = json_decode($row['data'], true);
        $fio = $data['fio'] ?? 'Unknown';
        $accessLevel = $data['access_level'] ?? 'NOT SET';
        
        echo "User ID: {$userId}, FIO: {$fio}, Access Level: {$accessLevel}\n";
        
        if (stripos($fio, 'Богданов') !== false && stripos($fio, 'Борис') !== false) {
            $found = true;
            echo "  ^^ FOUND!\n\n";
            
            echo "=== Promoting to Super Admin (Level 4) ===\n";
            $userData = $db->getUserData($userId);
            $userData['access_level'] = 4;
            
            if ($db->updateUserData($userId, $userData)) {
                echo "✅ Successfully promoted {$fio} (ID: {$userId}) to Super Admin (Level 4)\n";
            } else {
                echo "❌ Failed to promote user\n";
            }
        }
    }
    
    if (!$found) {
        echo "\n❌ User 'Богданов Борис Сергеевич' not found in database!\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}

echo "\n\n=== Current Super Admins (Level 4) ===\n\n";
$superAdmins = $db->getAllSuperAdmins();
echo "Found " . count($superAdmins) . " super admins\n\n";

foreach ($superAdmins as $adminId) {
    $userData = $db->getUserData($adminId);
    $fio = $userData['fio'] ?? 'Unknown';
    echo "User ID: {$adminId}, FIO: {$fio}\n";
}
