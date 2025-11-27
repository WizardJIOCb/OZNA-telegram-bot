<?php
/**
 * Web-accessible diagnostic script to test database freshness
 * Access via: http://your-domain/test_db_fresh.php
 */

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "<h1>Database Freshness Test</h1>";
echo "<p>Testing if we can see freshly added projects immediately...</p>";

$testProject = "ВЕБТЕСТ" . rand(1000, 9999);
$userId = 999999;

echo "<h2>Step 1: Add test project '{$testProject}'</h2>";
$db1 = new DatabaseService();
$insertId = $db1->addPendingProject($testProject, $userId);

if ($insertId) {
    echo "<p style='color: green;'>✓ Added successfully, ID: {$insertId}</p>";
} else {
    echo "<p style='color: red;'>✗ Failed to add</p>";
    exit;
}

echo "<h2>Step 2: IMMEDIATELY check if visible (same script execution)</h2>";
$db2 = new DatabaseService();
$projects = $db2->getAllActiveAndPendingProjects();

if (in_array($testProject, $projects)) {
    echo "<p style='color: green;'>✓ Project IS visible immediately in same script!</p>";
} else {
    echo "<p style='color: red;'>✗ Project NOT visible (SERIOUS BUG!)</p>";
    echo "<p>Found " . count($projects) . " projects total</p>";
}

echo "<h2>Step 3: Direct SQL query</h2>";
$exists = $db2->projectExists($testProject);
echo "<p>projectExists() returned: " . ($exists ? "<span style='color: green;'>YES</span>" : "<span style='color: red;'>NO</span>") . "</p>";

echo "<h2>Cleanup</h2>";
$db2->deleteProjectFromDatabase($testProject);
echo "<p>✓ Test project removed</p>";

echo "<hr>";
echo "<h3>Conclusion:</h3>";
echo "<p>If the project was NOT visible immediately, the problem is with MySQL connection or transaction handling.</p>";
echo "<p>If it WAS visible, the problem is with how Telegram webhook processes requests (parallel execution or caching).</p>";

echo "<hr>";
echo "<p><small>Check your PHP error_log for detailed DatabaseService logs</small></p>";
