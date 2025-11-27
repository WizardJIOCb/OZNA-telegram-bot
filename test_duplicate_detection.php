<?php
/**
 * Test duplicate project detection
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();

echo "=== Testing Duplicate Project Detection ===\n\n";

// Get all projects in database
echo "1. Getting all projects from database...\n";
$allProjects = $dbService->getAllActiveAndPendingProjects();
echo "Found " . count($allProjects) . " projects:\n";
foreach ($allProjects as $proj) {
    echo "  - {$proj}\n";
}

echo "\n2. Testing projectExists() for each project...\n";
foreach ($allProjects as $proj) {
    $exists = $dbService->projectExists($proj);
    $existsLower = $dbService->projectExists(strtolower($proj));
    $existsUpper = $dbService->projectExists(strtoupper($proj));
    
    echo "  Project: {$proj}\n";
    echo "    - Exact match: " . ($exists ? 'YES' : 'NO') . "\n";
    echo "    - Lowercase: " . ($existsLower ? 'YES' : 'NO') . "\n";
    echo "    - Uppercase: " . ($existsUpper ? 'YES' : 'NO') . "\n";
}

echo "\n3. Testing with a non-existent project...\n";
$fakeProject = "FAKE_PROJECT_9999";
$exists = $dbService->projectExists($fakeProject);
echo "  Project: {$fakeProject}\n";
echo "  Exists: " . ($exists ? 'YES (ERROR!)' : 'NO (correct)') . "\n";

echo "\n4. Direct database query to check for duplicates...\n";
// Create direct connection
$mysqli = new \mysqli('localhost', 'buhuser', '123456', 'buh_db');
$mysqli->set_charset("utf8mb4");

$query = "SELECT current_name, COUNT(*) as count 
          FROM project_moderation 
          WHERE status IN ('pending', 'active', 'renamed')
          AND is_active = 1
          GROUP BY UPPER(current_name)
          HAVING count > 1";

$result = $mysqli->query($query);
if ($result && $result->num_rows > 0) {
    echo "  FOUND DUPLICATES:\n";
    while ($row = $result->fetch_assoc()) {
        echo "    - {$row['current_name']}: {$row['count']} entries\n";
    }
} else {
    echo "  No duplicates found (good!)\n";
}

echo "\n5. Checking all entries in project_moderation table...\n";
$query = "SELECT id, current_name, status, is_active, added_by, added_at 
          FROM project_moderation 
          ORDER BY current_name, added_at DESC";
$result = $mysqli->query($query);
if ($result) {
    echo "  Total entries: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        echo "    ID:{$row['id']} | {$row['current_name']} | status:{$row['status']} | active:{$row['is_active']} | user:{$row['added_by']} | {$row['added_at']}\n";
    }
}

$mysqli->close();

echo "\n=== Test Complete ===\n";
