<?php
/**
 * Test: Add project → Reject → Re-add (simulating user workflow)
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;

echo "=== Testing Reject & Re-Add Workflow ===\n\n";

$testProject = "ТЕСТРЕДЖЕКТ" . rand(100, 999);
$userId = 123456;
$moderatorId = 999999;

echo "Test project: {$testProject}\n\n";

// Step 1: User adds project
echo "STEP 1: User adds project '{$testProject}'\n";
$db1 = new DatabaseService();
$insertId = $db1->addPendingProject($testProject, $userId);
if ($insertId) {
    echo "  ✓ Added with ID: {$insertId}\n";
} else {
    echo "  ✗ Failed to add\n";
    exit(1);
}

// Step 2: Check if it appears in list
echo "\nSTEP 2: Check if project appears in list\n";
$db2 = new DatabaseService();
$selector = new ProjectSelector($db2);
$projects = $selector->getAllProjectsIncludingPending();
if (in_array($testProject, $projects)) {
    echo "  ✓ Project IS visible\n";
} else {
    echo "  ✗ Project NOT visible\n";
}

// Step 3: Admin rejects project
echo "\nSTEP 3: Admin REJECTS project (ID: {$insertId})\n";
$db3 = new DatabaseService();
$rejected = $db3->rejectProject($insertId, $moderatorId, "Test rejection");
if ($rejected) {
    echo "  ✓ Project rejected successfully\n";
} else {
    echo "  ✗ Failed to reject\n";
}

// Step 4: Check if it disappears from list
echo "\nSTEP 4: Check if rejected project disappears\n";
$db4 = new DatabaseService();
$selector2 = new ProjectSelector($db4);
$projects2 = $selector2->getAllProjectsIncludingPending();
if (in_array($testProject, $projects2)) {
    echo "  ✗ Project STILL visible (should be hidden)\n";
} else {
    echo "  ✓ Project correctly hidden\n";
}

// Step 5: User RE-ADDS the same project
echo "\nSTEP 5: User RE-ADDS '{$testProject}' (previously rejected)\n";
$db5 = new DatabaseService();
$insertId2 = $db5->addPendingProject($testProject, $userId);
if ($insertId2) {
    echo "  ✓ Re-added successfully with ID: {$insertId2}\n";
} else {
    echo "  ✗ Failed to re-add (UNIQUE KEY conflict?)\n";
    exit(1);
}

// Step 6: Check if it appears again
echo "\nSTEP 6: Check if re-added project appears in list\n";
$db6 = new DatabaseService();
$selector3 = new ProjectSelector($db6);
$projects3 = $selector3->getAllProjectsIncludingPending();
if (in_array($testProject, $projects3)) {
    echo "  ✓ Project IS visible after re-adding!\n";
} else {
    echo "  ✗ Project NOT visible (THIS IS THE BUG!)\n";
}

// Cleanup
echo "\nCLEANUP: Deactivating test project\n";
$db6->deleteProjectFromDatabase($testProject);
echo "  ✓ Cleanup complete\n";

echo "\n=== Test Complete ===\n";
