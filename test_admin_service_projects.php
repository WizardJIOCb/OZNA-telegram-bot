<?php

// Simple test to verify AdminService is reading the updated projects
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing AdminService project loading...\n\n";

// Initialize services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Get projects from AdminService
$projects = $adminService->getAllProjects();

echo "Projects loaded by AdminService: " . count($projects) . "\n";
echo "Project list:\n";
foreach ($projects as $index => $project) {
    echo ($index + 1) . ". $project\n";
}

// Verify if the JSON file exists and what it contains
$jsonFile = __DIR__ . '/data/projects.json';
echo "\n\nDirect JSON file check:\n";
echo "File exists: " . (file_exists($jsonFile) ? 'Yes' : 'No') . "\n";

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $jsonProjects = json_decode($jsonContent, true);
    
    echo "Projects in JSON file: " . count($jsonProjects) . "\n";
    echo "Sample from JSON: ";
    echo implode(', ', array_slice($jsonProjects, 0, 5)) . "...\n";
    
    // Check if specific new projects are in the JSON
    $testProjects = ['ОИ661', 'ОИ711', 'ОИ811'];
    foreach ($testProjects as $testProject) {
        echo "JSON contains {$testProject}: " . (in_array($testProject, $jsonProjects) ? 'Yes' : 'No') . "\n";
    }
    
    // Check if AdminService projects match JSON projects
    $adminProjectsSet = array_flip($projects);
    $jsonProjectsSet = array_flip($jsonProjects);
    
    $inJsonNotAdmin = array_diff_key($jsonProjectsSet, $adminProjectsSet);
    $inAdminNotJson = array_diff_key($adminProjectsSet, $jsonProjectsSet);
    
    echo "\nProjects in JSON but not in AdminService: " . count($inJsonNotAdmin) . "\n";
    if (!empty($inJsonNotAdmin)) {
        echo "  " . implode(', ', array_keys($inJsonNotAdmin)) . "\n";
    }
    
    echo "Projects in AdminService but not in JSON: " . count($inAdminNotJson) . "\n";
    if (!empty($inAdminNotJson)) {
        echo "  " . implode(', ', array_keys($inAdminNotJson)) . "\n";
    }
}

echo "\nTest completed.\n";