<?php

// Test script to verify projects button functionality

require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;

// Mock chat ID and user ID for testing
$testChatId = 230379668; // Same as in logs
$testUserId = 230379668;

echo "Testing projects button functionality...\n";

// Initialize services
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Check user access level
$userAccessLevel = $adminService->getUserAccessLevel($testUserId);
echo "User access level: {$userAccessLevel}\n";

// Check if user has access to projects (should be level 1 or higher)
if ($userAccessLevel >= 1) {
    echo "User has access to projects\n";
    
    // Try to get all projects
    $projects = $adminService->getAllProjects();
    echo "Number of projects: " . count($projects) . "\n";
    echo "Projects: " . implode(', ', $projects) . "\n";
    
    // Test project handler canHandle method
    $projectHandler = new ProjectHandler($adminService, $messageService);
    
    // Test various callback data
    $testCallbacks = [
        'category_Проекты',
        'category_Проекты_МоиПроекты',
        'category_Проекты_МояАктивность',
        'proj_list_add',
        'proj_list_remove'
    ];
    
    foreach ($testCallbacks as $callback) {
        $canHandle = $projectHandler->canHandle($callback);
        echo "ProjectHandler canHandle '{$callback}': " . ($canHandle ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "User does not have access to projects (access level: {$userAccessLevel})\n";
}

echo "Test completed.\n";