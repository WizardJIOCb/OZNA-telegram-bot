<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/PersonnelService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\PersonnelService;

$dbService = new DatabaseService();
$personnelService = new PersonnelService($dbService);

echo "=== TESTING MULTIPLE OFFICE WORKERS ===\n\n";

// Get office workers with only non-work activities
$conn = $dbService->getConnection();

// Find users with only non-work activities from Пусконаладка
$query = "SELECT DISTINCT u.user_id, u.data
          FROM ozna_users u
          WHERE JSON_EXTRACT(u.data, '$.functional_direction') = 'Пусконаладка'
          LIMIT 5";

$result = $conn->query($query);

$reflection = new ReflectionClass($personnelService);
$hasWorkMethod = $reflection->getMethod('hasAnyWorkActivities');
$hasWorkMethod->setAccessible(true);
$firstMethod = $reflection->getMethod('findFirstActivity');
$firstMethod->setAccessible(true);
$lastMethod = $reflection->getMethod('findLastActivity');
$lastMethod->setAccessible(true);

$officeWorkers = 0;

while ($row = $result->fetch_assoc()) {
    $userId = $row['user_id'];
    $data = json_decode($row['data'], true);
    $fio = $data['fio'] ?? 'Unknown';
    
    $hasWork = $hasWorkMethod->invoke($personnelService, $userId);
    
    if (!$hasWork) {
        $officeWorkers++;
        $firstActivity = $firstMethod->invoke($personnelService, $userId);
        $lastActivity = $lastMethod->invoke($personnelService, $userId);
        
        echo "Office Worker #$officeWorkers:\n";
        echo "  FIO: $fio\n";
        echo "  User ID: $userId\n";
        echo "  First activity: " . ($firstActivity ? $firstActivity->format('d.m.Y') : 'NULL') . "\n";
        echo "  Last activity:  " . ($lastActivity ? $lastActivity->format('d.m.Y') : 'NULL') . "\n";
        echo "  ✅ Will use FIRST activity as free-time start\n\n";
    }
}

if ($officeWorkers === 0) {
    echo "No office-only workers found in first 5 users.\n";
} else {
    echo "Found $officeWorkers office worker(s) with only non-work activities.\n";
}
