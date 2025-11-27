<?php
/**
 * Debug specific PNR users
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

echo "=== DEBUGGING PNR USERS FREE TIME ===\n\n";

try {
    $databaseService = new DatabaseService();
    $personnelService = new PersonnelService($databaseService);
    
    $userIds = [
        1429455763, // Гайнутдинов
        5396921781, // Шангин
        693548959,  // Мамаев
        5850784048  // Ахунов
    ];
    
    foreach ($userIds as $userId) {
        $userData = $databaseService->getUserData($userId);
        $fio = $userData['fio'] ?? 'Unknown';
        
        echo "\n=== {$fio} (ID: {$userId}) ===\n";
        
        // Get last 15 entries
        $conn = $databaseService->getConnection();
        $query = "SELECT dt, message FROM ozna_tabel 
                  WHERE user_id = ? 
                  AND dt >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                  ORDER BY dt DESC LIMIT 15";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "\nLast 15 entries:\n";
        while ($row = $result->fetch_assoc()) {
            $dt = $row['dt'];
            $msg = substr($row['message'], 0, 100);
            echo "  {$dt}: {$msg}\n";
        }
        
        // Check onsite status
        echo "\n--- ONSITE CHECK ---\n";
        $sessionStart = $personnelService->findFirstEntryInCurrentSession($userId);
        if ($sessionStart) {
            $now = new DateTime();
            $days = $personnelService->getDaysBetween($sessionStart, $now);
            echo "✓ ON SITE since {$sessionStart->format('d.m.Y')} ({$days} days)\n";
        } else {
            echo "✗ NOT on site\n";
        }
        
        // Check freetime status
        echo "\n--- FREETIME CHECK ---\n";
        $lastWork = $personnelService->findLastContinuousWorkPeriod($userId);
        if ($lastWork) {
            $now = new DateTime();
            $days = $personnelService->getDaysBetween($lastWork, $now);
            echo "Last continuous work ended: {$lastWork->format('d.m.Y')}\n";
            echo "Days since last work: {$days}\n";
            
            if ($days <= 3) {
                echo "✗ NOT in freetime list (gap <= 3 days, still considered working)\n";
            } else {
                echo "✓ SHOULD BE in freetime list ({$days} days rest)\n";
            }
        } else {
            echo "✗ No continuous work period found (need >4 consecutive days)\n";
        }
        
        // Check what freetime method returns
        echo "\n--- ACTUAL FREETIME METHOD RESULT ---\n";
        $freetimeEmployees = $personnelService->getFreetimeEmployees('Пусконаладка');
        $foundInList = false;
        foreach ($freetimeEmployees as $emp) {
            if ($emp['user_id'] == $userId) {
                $foundInList = true;
                echo "✓ FOUND in freetime list:\n";
                echo "  Icon: {$emp['icon']}\n";
                echo "  Days: {$emp['days']}\n";
                echo "  Last work: {$emp['last_work_date']}\n";
                break;
            }
        }
        if (!$foundInList) {
            echo "✗ NOT FOUND in freetime list\n";
        }
        
        echo "\n" . str_repeat("-", 80) . "\n";
    }
    
    echo "\n=== DEBUG COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
