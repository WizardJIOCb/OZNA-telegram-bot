<?php

// Simple test script to verify projects button functionality without database

require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;

echo "Testing projects button functionality (simple test)...\n";

// Initialize services
$messageService = new MessageService();

// Create a mock admin service (without database)
class MockAdminService {
    public function getUserAccessLevel($userId) {
        // Return a valid access level for testing
        return 1; // USER level
    }
    
    public function getAllProjects() {
        return ['ОИ834', 'ОИ651', 'ОИ696', 'ОИ795', 'БКНС115'];
    }
}

$mockAdminService = new MockAdminService();

// Test project handler canHandle method
$projectHandler = new ProjectHandler($mockAdminService, $messageService);

// Test various callback data
$testCallbacks = [
    'category_Проекты',
    'category_Проекты_МоиПроекты',
    'category_Проекты_МояАктивность',
    'proj_list_add',
    'proj_list_remove',
    'category_Супервайзер', // This should NOT be handled by ProjectHandler
    'category_Админка'      // This should NOT be handled by ProjectHandler
];

echo "Testing ProjectHandler canHandle method:\n";
foreach ($testCallbacks as $callback) {
    $canHandle = $projectHandler->canHandle($callback);
    echo "ProjectHandler canHandle '{$callback}': " . ($canHandle ? 'YES' : 'NO') . "\n";
}

// Test SupervisorHandler canHandle method
$supervisorHandler = new SupervisorHandler($messageService, $mockAdminService);

echo "\nTesting SupervisorHandler canHandle method:\n";
foreach ($testCallbacks as $callback) {
    $canHandle = $supervisorHandler->canHandle($callback);
    echo "SupervisorHandler canHandle '{$callback}': " . ($canHandle ? 'YES' : 'NO') . "\n";
}

echo "\nTest completed.\n";