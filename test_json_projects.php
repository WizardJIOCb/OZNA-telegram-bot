<?php
/**
 * Simple test with known projects from JSON
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();

echo "=== Testing Projects from projects.json ===\n\n";

// Test a few projects that should be in JSON
$testProjects = [
    "ОИ696",    // From JSON line 3
    "ОИ795",    // From JSON line 4
    "ОИ810",    // From JSON line 5
    "БКНС115",  // From JSON line 11
    "ИП002",    // From JSON line 48
];

foreach ($testProjects as $project) {
    echo "Testing: '{$project}'\n";
    $exists = $dbService->projectExists($project);
    echo "  Result: " . ($exists ? "✅ EXISTS" : "❌ NOT FOUND") . "\n\n";
}

echo "=== Test Complete ===\n";
echo "\nAll these projects should show EXISTS because they're in projects.json\n";
