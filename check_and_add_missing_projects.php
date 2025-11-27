<?php

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Checking and adding missing projects...\n\n";

// Initialize services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Required projects list from user request
$requiredProjects = [
    'ОИ834', 'ОИ651', 'ОИ696', 'ОИ795', 'ОИ810', 'ОИ768', 'ОИ837', 'ОИ562',
    'ОИ788', 'ОИ862', 'БКНС115', 'БКНС120', 'ОИ860', 'ОИ789', 'ОИ796', 'ОИ785',
    'ОИ848', 'ОИ784', 'ОИ786', 'ОИ849', 'ОИ661', 'ОИ711', 'ОИ761', 'ОИ805',
    'ОИ791', 'ОИ654', 'ОИ726', 'ОИ765', 'ОИ798', 'ОИ572', 'ОИ846', 'ОИ850',
    'ОИ819', 'ОИ774', 'ОИ717', 'ОИ833', 'ОИ321', 'ОИ839', 'ОИ420-04', 'ОИ420-02',
    'ОИ876', 'ОИ832', 'ОИ873', 'ОИ826', 'ОИ764', 'ОИ840', 'ИП002', 'ОИ632',
    'ОИ719', 'ОИ811'
];

// Get current projects
$currentProjects = $adminService->getAllProjects();

echo "Current projects in system: " . count($currentProjects) . "\n";
foreach ($currentProjects as $project) {
    echo "- $project\n";
}

echo "\nRequired projects: " . count($requiredProjects) . "\n";

// Find missing projects
$missingProjects = [];
foreach ($requiredProjects as $project) {
    if (!in_array($project, $currentProjects)) {
        $missingProjects[] = $project;
    }
}

if (empty($missingProjects)) {
    echo "\n✅ All required projects are already in the system!\n";
} else {
    echo "\nMissing projects (" . count($missingProjects) . "):\n";
    foreach ($missingProjects as $project) {
        echo "- $project\n";
    }

    echo "\nAdding missing projects...\n";
    $addedCount = 0;
    $failedCount = 0;

    foreach ($missingProjects as $project) {
        $result = $adminService->addProject($project);
        if ($result) {
            echo "✅ Added: $project\n";
            $addedCount++;
        } else {
            echo "❌ Failed to add: $project\n";
            $failedCount++;
        }
    }

    echo "\nSummary:\n";
    echo "- Added: $addedCount projects\n";
    echo "- Failed: $failedCount projects\n";
}

// Get final project list
$finalProjects = $adminService->getAllProjects();
echo "\nFinal project list (" . count($finalProjects) . " projects):\n";
foreach ($finalProjects as $project) {
    echo "- $project\n";
}

// Check if all required projects are now present
$stillMissing = [];
foreach ($requiredProjects as $project) {
    if (!in_array($project, $finalProjects)) {
        $stillMissing[] = $project;
    }
}

if (empty($stillMissing)) {
    echo "\n🎉 SUCCESS: All required projects are now in the system!\n";
} else {
    echo "\n⚠️ WARNING: The following projects are still missing:\n";
    foreach ($stillMissing as $project) {
        echo "- $project\n";
    }
}

echo "\nDone!\n";