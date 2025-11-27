<?php
/**
 * Debug script to test cache behavior
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;

echo "=== Testing Query Cache Behavior ===\n\n";

// Test 1: Query the database directly
echo "Test 1: Direct database query\n";
$db1 = new DatabaseService();
$projects1 = $db1->getAllActiveAndPendingProjects();
echo "Projects found: " . count($projects1) . "\n";
echo "Projects: " . implode(", ", $projects1) . "\n\n";

// Test 2: Query again with same DatabaseService instance
echo "Test 2: Query again with SAME DatabaseService instance\n";
$projects2 = $db1->getAllActiveAndPendingProjects();
echo "Projects found: " . count($projects2) . "\n";
echo "Projects: " . implode(", ", $projects2) . "\n\n";

// Test 3: Query with NEW DatabaseService instance
echo "Test 3: Query with NEW DatabaseService instance\n";
$db2 = new DatabaseService();
$projects3 = $db2->getAllActiveAndPendingProjects();
echo "Projects found: " . count($projects3) . "\n";
echo "Projects: " . implode(", ", $projects3) . "\n\n";

// Test 4: Using ProjectSelector
echo "Test 4: Using ProjectSelector with new DatabaseService\n";
$selector1 = new ProjectSelector(new DatabaseService());
$projects4 = $selector1->getAllProjectsIncludingPending();
echo "Projects found: " . count($projects4) . "\n";
echo "Projects: " . implode(", ", $projects4) . "\n\n";

// Test 5: Using ProjectSelector again (like user does)
echo "Test 5: Using ProjectSelector again (simulating second call)\n";
$selector2 = new ProjectSelector(new DatabaseService());
$projects5 = $selector2->getAllProjectsIncludingPending();
echo "Projects found: " . count($projects5) . "\n";
echo "Projects: " . implode(", ", $projects5) . "\n\n";

// Test 6: Check if specific projects exist
echo "Test 6: Check if ТЕСТ777 and ТЕСТ888 exist\n";
echo "  ТЕСТ777 exists: " . ($db1->projectExists('ТЕСТ777') ? 'YES' : 'NO') . "\n";
echo "  ТЕСТ888 exists: " . ($db1->projectExists('ТЕСТ888') ? 'YES' : 'NO') . "\n";

echo "\n=== Test Complete ===\n";
