<?php
/**
 * Test: Multiple different projects - add, reject, check visibility
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;

echo "=== Testing Multiple Projects Workflow ===\n\n";

$project1 = "ТЕСТААА" . rand(100, 999);
$project2 = "ТЕСТБББ" . rand(100, 999);
$userId = 123456;
$moderatorId = 999999;

echo "Project 1: {$project1}\n";
echo "Project 2: {$project2}\n\n";

// Step 1: Add PROJECT 1
echo "STEP 1: Add PROJECT 1 ('{$project1}')\n";
$db = new DatabaseService();
$id1 = $db->addPendingProject($project1, $userId);
echo "  ✓ Added with ID: {$id1}\n";

// Step 2: Check both projects in list
echo "\nSTEP 2: Check list - should see PROJECT 1 only\n";
$db = new DatabaseService();
$selector = new ProjectSelector($db);
$projects = $selector->getAllProjectsIncludingPending();
echo "  PROJECT 1 ({$project1}): " . (in_array($project1, $projects) ? "✓ VISIBLE" : "✗ HIDDEN") . "\n";
echo "  PROJECT 2 ({$project2}): " . (in_array($project2, $projects) ? "✓ VISIBLE" : "✗ HIDDEN") . "\n";

// Step 3: Add PROJECT 2
echo "\nSTEP 3: Add PROJECT 2 ('{$project2}')\n";
$db = new DatabaseService();
$id2 = $db->addPendingProject($project2, $userId);
echo "  ✓ Added with ID: {$id2}\n";

// Step 4: Check both projects in list
echo "\nSTEP 4: Check list - should see BOTH projects\n";
$db = new DatabaseService();
$selector = new ProjectSelector($db);
$projects = $selector->getAllProjectsIncludingPending();
echo "  PROJECT 1 ({$project1}): " . (in_array($project1, $projects) ? "✓ VISIBLE" : "✗ HIDDEN") . "\n";
echo "  PROJECT 2 ({$project2}): " . (in_array($project2, $projects) ? "✓ VISIBLE" : "✗ HIDDEN") . "\n";

// Step 5: REJECT PROJECT 2
echo "\nSTEP 5: REJECT PROJECT 2 (ID: {$id2})\n";
$db = new DatabaseService();
$rejected = $db->rejectProject($id2, $moderatorId, "Test rejection");
echo "  ✓ Rejected\n";

// Step 6: Check both projects - PROJECT 1 should STILL be visible!
echo "\nSTEP 6: Check list after rejecting PROJECT 2\n";
$db = new DatabaseService();
$selector = new ProjectSelector($db);
$projects = $selector->getAllProjectsIncludingPending();
echo "  PROJECT 1 ({$project1}): " . (in_array($project1, $projects) ? "✓ VISIBLE" : "✗ HIDDEN (BUG!)") . "\n";
echo "  PROJECT 2 ({$project2}): " . (in_array($project2, $projects) ? "✗ STILL VISIBLE (BUG!)" : "✓ HIDDEN") . "\n";

// Step 7: Get detailed info
echo "\nSTEP 7: Detailed database status\n";
$db = new DatabaseService();
echo "  PROJECT 1 exists: " . ($db->projectExists($project1) ? "YES" : "NO") . "\n";
echo "  PROJECT 2 exists: " . ($db->projectExists($project2) ? "YES" : "NO") . "\n";

// Cleanup
echo "\nCLEANUP\n";
$db->deleteProjectFromDatabase($project1);
$db->deleteProjectFromDatabase($project2);
echo "  ✓ Done\n";

echo "\n=== Test Complete ===\n";
