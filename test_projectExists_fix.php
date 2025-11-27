<?php
/**
 * Test the updated projectExists() method
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();

echo "=== Testing Updated projectExists() Method ===\n\n";

$testCases = [
    "ОИ651",  // Exists in projects.json
    "ОИ834",  // Exists in both projects.json AND database
    "ОИ779",  // Exists only in database
    "FAKE999" // Doesn't exist anywhere
];

foreach ($testCases as $testProject) {
    echo "Testing: '{$testProject}'\n";
    $exists = $dbService->projectExists($testProject);
    echo "  Result: " . ($exists ? "✅ EXISTS" : "❌ NOT FOUND") . "\n";
    echo "  (Check log above for details)\n\n";
}

echo "=== Test Complete ===\n";
echo "\nNow try adding ОИ651 via Telegram - it should be BLOCKED!\n";
