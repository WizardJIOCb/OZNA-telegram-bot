<?php
/**
 * Check if ОИ651 exists in database and test projectExists()
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();

echo "=== Checking ОИ651 Project ===\n\n";

$testProject = "ОИ651";

echo "1. Testing projectExists() with different cases:\n";
$testCases = [
    "ОИ651",
    "ои651",
    strtoupper("ОИ651"),
    strtolower("ОИ651"),
    trim(strtoupper("ОИ651"))
];

foreach ($testCases as $testCase) {
    $exists = $dbService->projectExists($testCase);
    echo "  Input: '{$testCase}' -> Exists: " . ($exists ? "YES" : "NO") . "\n";
}

echo "\n2. Getting all projects from database:\n";
$allProjects = $dbService->getAllActiveAndPendingProjects();
echo "Total projects: " . count($allProjects) . "\n";
foreach ($allProjects as $proj) {
    if (stripos($proj, '651') !== false) {
        echo "  FOUND: '{$proj}'\n";
        echo "    - Bytes: " . bin2hex($proj) . "\n";
        echo "    - Length: " . strlen($proj) . "\n";
    }
}

echo "\n3. Searching for any project containing '651':\n";
$searchTerm = "651";
foreach ($allProjects as $proj) {
    if (stripos($proj, $searchTerm) !== false) {
        $exists = $dbService->projectExists($proj);
        echo "  Project: '{$proj}' -> projectExists() = " . ($exists ? "YES" : "NO") . "\n";
    }
}

echo "\n4. Direct query to check all entries with '651':\n";
// Use reflection to access private connection
$reflection = new \ReflectionClass($dbService);
$method = $reflection->getMethod('getConnection');
$method->setAccessible(true);
$conn = $method->invoke($dbService);

$query = "SELECT id, project_code, original_name, current_name, status, is_active, added_by, added_at 
          FROM project_moderation 
          WHERE current_name LIKE '%651%'
          ORDER BY added_at DESC";

$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    echo "  Found " . $result->num_rows . " entries:\n";
    while ($row = $result->fetch_assoc()) {
        echo "    ID:{$row['id']} | '{$row['current_name']}' | status:{$row['status']} | active:{$row['is_active']} | user:{$row['added_by']} | {$row['added_at']}\n";
        echo "      Bytes: " . bin2hex($row['current_name']) . "\n";
    }
} else {
    echo "  No entries found!\n";
}

echo "\n5. Testing UPPER() comparison:\n";
$testName = "ОИ651";
$upperTest = strtoupper($testName);
echo "  Original: '{$testName}' (bytes: " . bin2hex($testName) . ")\n";
echo "  Upper: '{$upperTest}' (bytes: " . bin2hex($upperTest) . ")\n";
echo "  Same? " . ($testName === $upperTest ? "YES" : "NO") . "\n";

echo "\n=== Test Complete ===\n";
