<?php
/**
 * Test script to simulate user workflow
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;

echo "=== Simulating User Workflow ===\n\n";

$testProject = "ТЕСТ" . rand(1000, 9999);
$userId = 123456; // Test user ID

echo "Test project name: {$testProject}\n\n";

// Step 1: User adds project (creates DatabaseService instance #1)
echo "STEP 1: User adds project '{$testProject}'\n";
$db1 = new DatabaseService();
$insertResult = $db1->addPendingProject($testProject, $userId);
if ($insertResult) {
    echo "  ✓ Project added successfully, ID: {$insertResult}\n";
} else {
    echo "  ✗ Failed to add project\n";
    exit(1);
}

// Small delay to simulate user navigation
usleep(100000); // 100ms

// Step 2: User opens "Projects for Report" (creates DatabaseService instance #2)
echo "\nSTEP 2: User opens 'Projects for Report' menu\n";
$db2 = new DatabaseService();
$selector = new ProjectSelector($db2);
$projects = $selector->getAllProjectsIncludingPending();

if (in_array($testProject, $projects)) {
    echo "  ✓ Project '{$testProject}' IS visible in list\n";
} else {
    echo "  ✗ Project '{$testProject}' NOT visible in list\n";
    echo "  Projects found: " . implode(", ", array_slice($projects, -5)) . " (last 5)\n";
}

// Step 3: Direct database query with new connection
echo "\nSTEP 3: Direct query with THIRD connection\n";
$db3 = new DatabaseService();
$exists = $db3->projectExists($testProject);
echo "  Project exists check: " . ($exists ? "YES" : "NO") . "\n";

// Step 4: Cleanup
echo "\nSTEP 4: Cleanup - deactivating test project\n";
$deleted = $db3->deleteProjectFromDatabase($testProject);
echo "  Cleanup result: " . ($deleted ? "SUCCESS" : "FAILED") . "\n";

echo "\n=== Test Complete ===\n";
