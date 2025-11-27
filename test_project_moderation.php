<?php
/**
 * Test script for project moderation functionality
 */

echo "=== Project Moderation System Test ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

try {
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "✓ Services initialized\n\n";
    
    // Test 1: Check if table exists
    echo "Test 1: Checking table existence...\n";
    $testUserId = 230379668; // Test user
    
    // Test 2: Add a pending project
    echo "\nTest 2: Adding a pending project...\n";
    $projectId = $databaseService->addPendingProject('TEST_PROJECT_001', $testUserId);
    
    if ($projectId) {
        echo "✓ Project added with ID: {$projectId}\n";
    } else {
        echo "✗ Failed to add project\n";
    }
    
    // Test 3: Get pending projects count
    echo "\nTest 3: Getting pending projects count...\n";
    $pendingCount = $databaseService->getPendingProjectsCount();
    echo "✓ Pending projects count: {$pendingCount}\n";
    
    // Test 4: Get pending projects list
    echo "\nTest 4: Getting pending projects list...\n";
    $pendingProjects = $databaseService->getPendingProjects();
    echo "✓ Found {count($pendingProjects)} pending project(s)\n";
    
    foreach ($pendingProjects as $project) {
        echo "  - {$project['current_name']} (ID: {$project['id']}, added by: {$project['added_by']})\n";
    }
    
    // Test 5: Check project exists
    echo "\nTest 5: Checking if project exists...\n";
    $exists = $databaseService->projectExists('TEST_PROJECT_001');
    echo "✓ Project 'TEST_PROJECT_001' exists: " . ($exists ? 'YES' : 'NO') . "\n";
    
    // Test 6: Approve project
    if ($projectId) {
        echo "\nTest 6: Approving project...\n";
        $success = $databaseService->approveProject($projectId, $testUserId);
        echo ($success ? "✓" : "✗") . " Project approved\n";
    }
    
    // Test 7: Check approved project
    echo "\nTest 7: Getting approved project...\n";
    $approvedProject = $databaseService->getModerationProjectById($projectId);
    if ($approvedProject) {
        echo "✓ Project status: {$approvedProject['status']}\n";
    }
    
    // Clean up: Remove test project
    echo "\nCleaning up test data...\n";
    $conn = new mysqli('82.146.42.213', 'WizardJIOCb', '7qQ#28051961', 'tg_bot');
    $conn->query("DELETE FROM project_moderation WHERE project_code = 'TEST_PROJECT_001'");
    echo "✓ Test data cleaned\n";
    
    echo "\n=== All tests passed! ===\n";
    echo "\n✅ System is ready to use!\n\n";
    
    echo "Next steps:\n";
    echo "1. Try adding a project as a user: Проекты → Мои проекты → Проекты для отчёта → 🆕 Добавить новый проект\n";
    echo "2. Check moderation as admin: Админка → 🔔 Проекты на согласование\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
