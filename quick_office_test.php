<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

// Initialize services
$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

// Test Гайнутдинов - has only non-work activities
$userId = 1429455763;

echo "Testing office worker fix for User ID: $userId\n\n";

// Get first and last entries
$conn = $dbService->getConnection();
$query = "SELECT dt FROM ozna_tabel WHERE user_id = ? ORDER BY dt ASC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$firstEntry = $result->fetch_assoc();

$query = "SELECT dt FROM ozna_tabel WHERE user_id = ? ORDER BY dt DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$lastEntry = $result->fetch_assoc();

echo "First entry: " . $firstEntry['dt'] . "\n";
echo "Last entry:  " . $lastEntry['dt'] . "\n\n";

// Get free-time list
$freeTimeEmployees = $personnelService->getFreetimeEmployees('Пусконаладка');

// Find the user
foreach ($freeTimeEmployees as $emp) {
    if ($emp['user_id'] == $userId) {
        echo "Free-time start date: " . $emp['last_work_date'] . "\n";
        
        $firstDate = new DateTime($firstEntry['dt']);
        $freeTimeDate = DateTime::createFromFormat('d.m.Y', $emp['last_work_date']);
        
        if ($firstDate->format('Y-m-d') === $freeTimeDate->format('Y-m-d')) {
            echo "\n✅ SUCCESS! Free-time starts at FIRST activity\n";
        } else {
            echo "\n❌ ERROR! Dates don't match\n";
            echo "Expected: " . $firstDate->format('d.m.Y') . "\n";
            echo "Got:      " . $emp['last_work_date'] . "\n";
        }
        break;
    }
}
