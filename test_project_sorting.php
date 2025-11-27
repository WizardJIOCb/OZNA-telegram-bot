<?php
/**
 * Test intelligent project sorting
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

$dbService = new DatabaseService();

echo "=== Testing Intelligent Project Sorting ===\n\n";

// Test data - mix of different project types
$testProjects = [
    'ОИ860',
    'БКНС115',
    'ОИ651',
    'ОИ696',
    'ОИ195',
    'ОИ834',
    'ОИ420-04',
    'ИП002',
    'ОИ768',
    'БКНС120',
    'ОИ111',
    'ОИ562',
    'ОИ999',
    'ОИ420-02',
    'ОИ788',
    'ХХХ',
    'ОИ243',
    'ОИ810',
    'ОИ321',
    'ОИ795'
];

echo "Original order (" . count($testProjects) . " projects):\n";
foreach ($testProjects as $idx => $project) {
    echo "  " . ($idx + 1) . ". {$project}\n";
}

echo "\n--- Applying intelligent sorting ---\n\n";

$sorted = $dbService->sortProjectsIntelligently($testProjects);

echo "Sorted order:\n";
$currentGroup = null;
foreach ($sorted as $idx => $project) {
    // Detect group change for ОИ projects
    if (preg_match('/^ОИ(\d+)/', $project, $matches)) {
        $num = (int)$matches[1];
        $group = (int)($num / 100);
        
        if ($group !== $currentGroup) {
            if ($currentGroup !== null) {
                echo "\n";
            }
            echo "  --- Группа ОИ{$group}ХХ ---\n";
            $currentGroup = $group;
        }
        echo "  " . ($idx + 1) . ". {$project}\n";
    } else {
        if ($currentGroup !== null) {
            echo "\n  --- Другие проекты ---\n";
            $currentGroup = null;
        }
        echo "  " . ($idx + 1) . ". {$project}\n";
    }
}

echo "\n=== Testing with Real Database Projects ===\n\n";

$realProjects = $dbService->getAllActiveAndPendingProjects();

echo "Projects from database (sorted):\n";
$currentGroup = null;
foreach ($realProjects as $idx => $project) {
    // Detect group change for ОИ projects
    if (preg_match('/^ОИ(\d+)/', $project, $matches)) {
        $num = (int)$matches[1];
        $group = (int)($num / 100);
        
        if ($group !== $currentGroup) {
            if ($currentGroup !== null) {
                echo "\n";
            }
            echo "  --- Группа ОИ{$group}ХХ ---\n";
            $currentGroup = $group;
        }
        echo "  " . ($idx + 1) . ". {$project}\n";
    } else {
        if ($currentGroup !== null) {
            echo "\n  --- Другие проекты ---\n";
            $currentGroup = null;
        }
        echo "  " . ($idx + 1) . ". {$project}\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "\nExpected behavior:\n";
echo "1. ОИ projects grouped: ОИ1ХХ, then ОИ2ХХ, then ОИ3ХХ, etc.\n";
echo "2. Within each group: ascending order (ОИ111, ОИ195, ОИ243)\n";
echo "3. Other projects (БКНС, ИП, XXX): alphabetically at the end\n";
