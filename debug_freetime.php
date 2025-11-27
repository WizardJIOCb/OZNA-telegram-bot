<?php
/**
 * Debug Personnel Free Time Detection
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

echo "=== DEBUGGING FREE TIME DETECTION ===\n\n";

try {
    $databaseService = new DatabaseService();
    $personnelService = new PersonnelService($databaseService);
    
    // Test specific users
    $testUsers = [
        'Гайнутдинов',
        'Шангин',
        'Мамаев',
        'Ахунов'
    ];
    
    echo "1. Getting all Mobile Brigade users...\n";
    $allUsers = $databaseService->getAllUsers();
    
    foreach ($testUsers as $searchName) {
        echo "\n=== Searching for: {$searchName} ===\n";
        
        $found = false;
        foreach ($allUsers as $user) {
            $userData = $user['data'] ?? [];
            $fio = $userData['fio'] ?? '';
            $fn = $userData['functional_direction'] ?? '';
            
            if (stripos($fio, $searchName) !== false && $fn === 'Мобильная бригада') {
                $found = true;
                $userId = $user['user_id'];
                echo "Found: {$fio} (ID: {$userId})\n";
                echo "Functional Direction: {$fn}\n\n";
                
                // Check last entries
                $conn = $databaseService->getConnection();
                $query = "SELECT dt, message FROM ozna_tabel 
                          WHERE user_id = ? 
                          AND dt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                          ORDER BY dt DESC LIMIT 10";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                echo "Last 10 entries:\n";
                while ($row = $result->fetch_assoc()) {
                    $dt = $row['dt'];
                    $msg = substr($row['message'], 0, 80);
                    echo "  {$dt}: {$msg}\n";
                }
                
                // Check if in onsite list
                echo "\nChecking onsite status:\n";
                $sessionStart = $personnelService->findFirstEntryInCurrentSession($userId);
                if ($sessionStart) {
                    $now = new DateTime();
                    $days = $personnelService->getDaysBetween($sessionStart, $now);
                    echo "  ✓ ON SITE since {$sessionStart->format('d.m.Y')} ({$days} days)\n";
                } else {
                    echo "  ✗ NOT on site (no active session)\n";
                }
                
                // Check if in freetime list
                echo "\nChecking freetime status:\n";
                $lastWork = $personnelService->findLastContinuousWorkPeriod($userId);
                if ($lastWork) {
                    $now = new DateTime();
                    $days = $personnelService->getDaysBetween($lastWork, $now);
                    echo "  Last continuous work ended: {$lastWork->format('d.m.Y')}\n";
                    echo "  Days since last work: {$days}\n";
                    
                    if ($days <= 3) {
                        echo "  ✗ NOT in freetime (still working - less than 3 days gap)\n";
                    } else {
                        echo "  ✓ IN FREETIME ({$days} days rest)\n";
                    }
                } else {
                    echo "  ✗ NOT in freetime (no work history found)\n";
                }
            }
        }
        
        if (!$found) {
            echo "User '{$searchName}' not found in Mobile Brigade\n";
        }
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
