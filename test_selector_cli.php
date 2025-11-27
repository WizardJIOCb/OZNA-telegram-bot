<?php
/**
 * CLI test to see what ProjectSelector returns
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;

echo "=== Testing ProjectSelector ===\n\n";

echo "Creating NEW DatabaseService...\n";
$db = new DatabaseService();

echo "Creating ProjectSelector with this DatabaseService...\n";
$selector = new ProjectSelector($db);

echo "\nCalling getAllProjectsIncludingPending()...\n";
$projects = $selector->getAllProjectsIncludingPending();

echo "\n=== RESULTS ===\n";
echo "Total projects: " . count($projects) . "\n\n";

echo "Checking for ОИ779...\n";
if (in_array('ОИ779', $projects)) {
    echo "✓ ОИ779 IS in the list!\n";
} else {
    echo "✗ ОИ779 NOT in the list!\n";
}

echo "\nFull list of projects:\n";
foreach ($projects as $p) {
    $marker = ($p === 'ОИ779') ? " ← THIS IS IT!" : "";
    echo "  • {$p}{$marker}\n";
}

echo "\n=== Check the detailed logs above ===\n";
