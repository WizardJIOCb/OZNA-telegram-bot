<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

echo "=== TESTING OFFICE WORKERS FIX ===\n\n";
echo "Testing employees with ONLY non-work activities\n";
echo "Expected: Free-time should start at FIRST activity, not last\n\n";

// Initialize services
$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

// Test case: Гайнутдинов - has only "Участие в ЗПИ" entries
$userId = 1429455763;
echo "Test User ID: $userId (Гайнутдинов)\n";
echo str_repeat("-", 70) . "\n";

// Get ALL entries for this user
$conn = $dbService->getConnection();
$query = "SELECT dt, message FROM ozna_tabel WHERE user_id = ? ORDER BY dt ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = $row;
}

echo "Total entries: " . count($entries) . "\n\n";

if (count($entries) > 0) {
    echo "FIRST entry:\n";
    echo "  Date: " . $entries[0]['dt'] . "\n";
    echo "  Activity: " . $entries[0]['message'] . "\n\n";
    
    echo "LAST entry:\n";
    echo "  Date: " . $entries[count($entries) - 1]['dt'] . "\n";
    echo "  Activity: " . $entries[count($entries) - 1]['message'] . "\n\n";
    
    echo "All entries:\n";
    foreach ($entries as $i => $entry) {
        echo sprintf("  %2d. %s - %s\n", $i + 1, $entry['dt'], $entry['message']);
    }
    echo "\n";
}

// Get free-time employees for Пусконаладка
echo str_repeat("=", 70) . "\n";
echo "Checking free-time list for Пусконаладка...\n\n";

$freeTimeEmployees = $personnelService->getFreetimeEmployees('Пусконаладка');

// Find our test user in the results
$found = false;
foreach ($freeTimeEmployees as $emp) {
    if ($emp['user_id'] == $userId) {
        $found = true;
        echo "✓ Found in free-time list!\n\n";
        echo "Result:\n";
        echo "  FIO: " . $emp['fio'] . "\n";
        echo "  Free-time start: " . $emp['last_work_date'] . "\n";
        echo "  Days on free-time: " . $emp['days'] . "\n";
        echo "  Icon: " . $emp['icon'] . "\n\n";
        
        // Validate the date
        if (count($entries) > 0) {
            $firstActivityDate = new DateTime($entries[0]['dt']);
            $freeTimeDate = DateTime::createFromFormat('d.m.Y', $emp['last_work_date']);
            
            echo "Validation:\n";
            echo "  First activity: " . $firstActivityDate->format('Y-m-d') . "\n";
            echo "  Free-time start: " . $freeTimeDate->format('Y-m-d') . "\n";
            
            if ($firstActivityDate->format('Y-m-d') === $freeTimeDate->format('Y-m-d')) {
                echo "\n✅ ПРАВИЛЬНО! Дата начала свободной вахты = первая активность в табеле\n";
            } else {
                echo "\n❌ ОШИБКА! Даты не совпадают!\n";
                echo "   Должно быть: " . $firstActivityDate->format('d.m.Y') . "\n";
                echo "   Показывает: " . $emp['last_work_date'] . "\n";
            }
        }
        break;
    }
}

if (!$found) {
    echo "❌ User NOT found in free-time list!\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "Test complete!\n";
