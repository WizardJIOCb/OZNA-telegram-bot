<?php
/**
 * Check status of ОИ779 project
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Checking ОИ779 Status ===\n\n";

$db = new DatabaseService();

// Check if project exists
echo "Checking if ОИ779 exists in database...\n";
$exists = $db->projectExists('ОИ779');
echo "Result: " . ($exists ? "YES" : "NO") . "\n\n";

// Get all active and pending projects
echo "Getting all active and pending projects...\n";
$allProjects = $db->getAllActiveAndPendingProjects();

if (in_array('ОИ779', $allProjects)) {
    echo "✓ ОИ779 IS in the active/pending projects list\n";
} else {
    echo "✗ ОИ779 NOT in the active/pending projects list\n";
}

echo "\nTotal projects in database: " . count($allProjects) . "\n";
echo "Projects containing 'ОИ': " . implode(", ", array_filter($allProjects, fn($p) => str_contains($p, 'ОИ'))) . "\n";

// Check projects.json
echo "\n--- Checking projects.json ---\n";
$jsonPath = __DIR__ . '/data/projects.json';
$projects = json_decode(file_get_contents($jsonPath), true);

if (in_array('ОИ779', $projects)) {
    echo "✓ ОИ779 exists in projects.json\n";
} else {
    echo "✗ ОИ779 NOT in projects.json\n";
}

echo "\n=== Check Complete ===\n";
echo "\nIf ОИ779 should be active but isn't showing, you need to:\n";
echo "1. Check the database table 'project_moderation' directly\n";
echo "2. Make sure is_active = 1 for ОИ779\n";
echo "3. OR add it to projects.json if it was previously approved\n";
