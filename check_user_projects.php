<?php
/**
 * Check what's in user's personal project list
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

// ЗАМЕНИ НА СВОЙ USER ID!
$yourUserId = 123456789; // ← ПОМЕНЯЙ ЭТО!

echo "=== Checking User Projects ===\n\n";
echo "User ID: {$yourUserId}\n\n";

$db = new DatabaseService();
$userData = $db->getUserData($yourUserId);

echo "Raw user data:\n";
print_r($userData);

echo "\n\nReport Projects:\n";
$reportProjects = $userData['report_projects'] ?? [];

if (empty($reportProjects)) {
    echo "  (empty - no projects in personal list)\n";
} else {
    foreach ($reportProjects as $index => $project) {
        $marker = ($project === 'ОИ779') ? " ← THIS IS IT!" : "";
        echo "  [{$index}] {$project}{$marker}\n";
    }
}

echo "\n\nChecking if ОИ779 is in the list...\n";
if (in_array('ОИ779', $reportProjects)) {
    echo "✓ YES - ОИ779 IS in your personal list!\n";
    echo "That's why it doesn't appear in 'Add Project' menu!\n";
} else {
    echo "✗ NO - ОИ779 is NOT in your personal list\n";
}

echo "\n=== Done ===\n";
