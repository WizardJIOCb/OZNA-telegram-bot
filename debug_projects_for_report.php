<?php
/**
 * Debug script to check project visibility in "Projects for Report"
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;

echo "=== Debug Projects for Report ===\n\n";

// Test with your user ID
$testUserId = 230379668; // Replace with actual user ID if needed

try {
    $databaseService = new DatabaseService();
    $projectSelector = new ProjectSelector($databaseService);
    
    echo "1. Projects from getAllActiveAndPendingProjects():\n";
    echo str_repeat('-', 60) . "\n";
    $dbProjects = $databaseService->getAllActiveAndPendingProjects();
    echo "Total: " . count($dbProjects) . " projects\n";
    foreach ($dbProjects as $project) {
        echo "  • {$project}\n";
    }
    
    echo "\n2. Projects from ProjectSelector->getAllProjectsIncludingPending():\n";
    echo str_repeat('-', 60) . "\n";
    $allProjects = $projectSelector->getAllProjectsIncludingPending();
    echo "Total: " . count($allProjects) . " projects\n";
    foreach ($allProjects as $project) {
        echo "  • {$project}\n";
    }
    
    echo "\n3. All projects in database (including inactive):\n";
    echo str_repeat('-', 60) . "\n";
    $query = "SELECT id, current_name, status, is_active, added_by, added_at 
              FROM project_moderation 
              ORDER BY added_at DESC 
              LIMIT 20";
    $result = $databaseService->getConnection()->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $activeIcon = $row['is_active'] == 1 ? '✅' : '❌';
            $statusIcon = match($row['status']) {
                'pending' => '🕐',
                'active' => '✅',
                'renamed' => '✏️',
                'rejected' => '❌',
                default => '❓'
            };
            echo "  {$activeIcon} {$statusIcon} {$row['current_name']} ";
            echo "(status: {$row['status']}, is_active: {$row['is_active']}, ";
            echo "added_by: {$row['added_by']}, added_at: {$row['added_at']})\n";
        }
    } else {
        echo "  ❌ Error: Failed to query database\n";
    }
    
    echo "\n4. Testing specific scenario:\n";
    echo str_repeat('-', 60) . "\n";
    echo "Checking if pending projects appear in the list...\n\n";
    
    $pendingQuery = "SELECT current_name, status, is_active 
                     FROM project_moderation 
                     WHERE status = 'pending' 
                     AND is_active = 1";
    $result = $databaseService->getConnection()->query($pendingQuery);
    
    $pendingProjects = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pendingProjects[] = $row['current_name'];
        }
    }
    
    if (empty($pendingProjects)) {
        echo "No pending projects found.\n";
    } else {
        echo "Pending projects in database: " . count($pendingProjects) . "\n";
        foreach ($pendingProjects as $project) {
            $inList = in_array($project, $allProjects);
            $icon = $inList ? '✅' : '❌';
            echo "  {$icon} {$project} - " . ($inList ? "IN list" : "NOT in list") . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
