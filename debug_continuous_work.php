<?php
/**
 * Debug continuous work detection for specific user
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$userId = 1429455763; // Гайнутдинов

echo "=== DEBUGGING CONTINUOUS WORK DETECTION ===\n\n";

$dbService = new DatabaseService();
$conn = $dbService->getConnection();

// Get all entries for last 180 days
echo "First, checking ALL entries (no filters):\n";
$query_all = "SELECT dt, message FROM ozna_tabel 
          WHERE user_id = ? 
          AND dt >= DATE_SUB(NOW(), INTERVAL 180 DAY)
          ORDER BY dt DESC LIMIT 20";

$stmt_all = $conn->prepare($query_all);
$stmt_all->bind_param("i", $userId);
$stmt_all->execute();
$result_all = $stmt_all->get_result();

echo "All entries (up to 20):\n";
while ($row = $result_all->fetch_assoc()) {
    $msg = $row['message'];
    echo "  " . $row['dt'] . ":\n";
    echo "    Full message: $msg\n";
    echo "    Contains 'Участие в ЗПИ': " . (strpos($msg, 'Участие в ЗПИ') !== false ? 'YES' : 'NO') . "\n";
    echo "    Contains 'Текущая деятельность': " . (strpos($msg, 'Текущая деятельность') !== false ? 'YES' : 'NO') . "\n";
    echo "    Contains 'Подготовка к служебной поездке': " . (strpos($msg, 'Подготовка к служебной поездке') !== false ? 'YES' : 'NO') . "\n\n";
}

echo "\nNow checking with activity filters:\n";
$query = "SELECT dt, message FROM ozna_tabel 
          WHERE user_id = ? 
          AND dt >= DATE_SUB(NOW(), INTERVAL 180 DAY)
          AND message NOT LIKE '%Участие в ЗПИ%'
          AND message NOT LIKE '%Текущая деятельность%'
          AND message NOT LIKE '%Подготовка к служебной поездке%'
          ORDER BY dt DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = [
        'dt' => new DateTime($row['dt']),
        'message' => $row['message']
    ];
}

echo "Total entries found: " . count($entries) . "\n\n";

if (empty($entries)) {
    echo "No entries found!\n";
    exit;
}

// Simulate the continuous work detection with detailed logging
$consecutiveDays = 0;
$currentPeriodEnd = null;

echo "=== ANALYZING CONTINUOUS WORK PERIODS ===\n\n";

for ($i = 0; $i < count($entries); $i++) {
    $currentDate = $entries[$i]['dt'];
    $currentDateStr = $currentDate->format('Y-m-d');
    
    if ($i == 0) {
        $currentPeriodEnd = $currentDate;
        $consecutiveDays = 1;
        echo "[Entry $i] $currentDateStr - FIRST ENTRY, starting period (days: $consecutiveDays)\n";
        continue;
    }
    
    $previousDate = $entries[$i - 1]['dt'];
    $previousDateStr = $previousDate->format('Y-m-d');
    
    // Calculate gap
    $interval = $currentDate->diff($previousDate);
    $gap = (int)$interval->days;
    
    echo "[Entry $i] $currentDateStr - Gap from previous ($previousDateStr): $gap days";
    
    // Allow up to 4 days gap to still count as continuous work
    if ($gap <= 4) {
        // Continuous period continues
        $consecutiveDays++;
        echo " - CONTINUES period (total days: $consecutiveDays)\n";
    } else {
        // Period broke
        echo " - BREAKS period\n";
        if ($consecutiveDays > 4) {
            // Found continuous work period!
            echo "    ✓ FOUND CONTINUOUS WORK PERIOD! End date: " . $currentPeriodEnd->format('Y-m-d') . " (Duration: $consecutiveDays days)\n";
            echo "    This is the result that should be returned!\n";
            break;
        } else {
            echo "    Period too short ($consecutiveDays days, need >4)\n";
        }
        // Start new period
        $currentPeriodEnd = $currentDate;
        $consecutiveDays = 1;
        echo "    Starting new period from $currentDateStr\n";
    }
}

// Check last period
echo "\n=== FINAL PERIOD CHECK ===\n";
echo "Consecutive days at end: $consecutiveDays\n";
if ($consecutiveDays > 4) {
    echo "✓ FOUND CONTINUOUS WORK PERIOD! End date: " . $currentPeriodEnd->format('Y-m-d') . " (Duration: $consecutiveDays days)\n";
} else {
    echo "✗ Last period too short ($consecutiveDays days, need >4)\n";
}
