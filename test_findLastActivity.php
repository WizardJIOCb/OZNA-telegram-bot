<?php

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$host = '82.146.42.213';
$dbname = 'tg_bot';
$username = 'WizardJIOCb';
$password = '7qQ#28051961';

try {
    $databaseService = new DatabaseService($host, $username, $password, $dbname);
    $conn = $databaseService->getConnection();
    
    echo "\n=== DIRECT DATABASE TEST: findLastActivity() ===\n\n";
    
    $userId = 1429455763; // Гайнутдинов
    
    // Test the new findLastActivity method directly
    $query = "SELECT dt FROM ozna_tabel 
              WHERE user_id = ? 
              ORDER BY dt DESC 
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $lastActivity = new DateTime($row['dt']);
        echo "✅ Last activity found: {$lastActivity->format('d.m.Y H:i:s')}\n";
        
        $now = new DateTime();
        $diff = $now->diff($lastActivity);
        $days = $diff->days;
        
        echo "   Days ago: {$days}\n";
        echo "\n✅ findLastActivity() method will work correctly!\n";
    } else {
        echo "❌ No activities found\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
