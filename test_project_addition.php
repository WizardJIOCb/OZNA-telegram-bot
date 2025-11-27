<?php
// Test script to verify project addition fix

require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Services/MessageService.php';

// Create instances
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
$adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);

// Get current projects
echo "Current projects:\n";
$projects = $adminService->getAllProjects();
foreach ($projects as $project) {
    echo "- $project\n";
}

// Try to add a test project
echo "\nAdding test project 'TEST123'...\n";
$result = $adminService->addProject('TEST123');

if ($result) {
    echo "Project added successfully!\n";
} else {
    echo "Failed to add project.\n";
}

// Get projects after addition
echo "\nProjects after addition:\n";
$projects = $adminService->getAllProjects();
foreach ($projects as $project) {
    echo "- $project\n";
}

echo "\nTest completed.\n";
