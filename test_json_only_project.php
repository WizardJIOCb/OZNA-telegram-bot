<?php
/**
 * Test projectExists() with projects that exist ONLY in JSON
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();

echo "=== Finding Projects in JSON but NOT in Database ===\n\n";

// Read projects.json
$projectsFile = __DIR__ . '/data/projects.json';
$jsonProjects = json_decode(file_get_contents($projectsFile), true);

echo "Total projects in JSON: " . count($jsonProjects) . "\n\n";

// Use reflection to access database
$reflection = new \ReflectionClass($dbService);
$method = $reflection->getMethod('getConnection');
$method->setAccessible(true);
$conn = $method->invoke($dbService);

// Find projects that are in JSON but not in database
$jsonOnlyProjects = [];
foreach ($jsonProjects as $project) {
    $upperProject = strtoupper(trim($project));
    
    // Check if exists in database
    $query = "SELECT COUNT(*) as count FROM project_moderation 
              WHERE UPPER(current_name) = ? AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $upperProject);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $jsonOnlyProjects[] = $project;
    }
}

echo "Projects in JSON but NOT in database: " . count($jsonOnlyProjects) . "\n";
foreach ($jsonOnlyProjects as $proj) {
    echo "  - {$proj}\n";
}

if (count($jsonOnlyProjects) > 0) {
    echo "\n=== Testing projectExists() with JSON-only project ===\n\n";
    $testProject = $jsonOnlyProjects[0];
    echo "Testing: '{$testProject}'\n";
    $exists = $dbService->projectExists($testProject);
    echo "  Result: " . ($exists ? "✅ EXISTS (CORRECT!)" : "❌ NOT FOUND (BUG!)") . "\n";
}

echo "\n=== Test Complete ===\n";
