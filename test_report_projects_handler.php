<?php
/**
 * Test the actual ProjectHandler showReportProjectsMenu method
 */

echo "=== TESTING PROJECT HANDLER REPORT MENU ===\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

try {
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Create ProjectHandler
    $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
        $adminService, 
        $messageService
    );
    
    echo "1. Getting a test user with report projects...\n";
    $allUsers = $databaseService->getAllUsers();
    $testUser = null;
    
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        if (isset($userData['report_projects']) && !empty($userData['report_projects'])) {
            $testUser = $user;
            break;
        }
    }
    
    if (!$testUser) {
        echo "❌ No users found with report projects!\n";
        exit(1);
    }
    
    $testUserId = $testUser['user_id'];
    $testUserData = $testUser['data'];
    $testFio = $testUserData['fio'] ?? 'Unknown';
    $reportProjects = $testUserData['report_projects'];
    
    echo "   Using test user: {$testUserId} ({$testFio})\n";
    echo "   User has " . count($reportProjects) . " report projects: " . implode(', ', $reportProjects) . "\n\n";
    
    echo "2. Testing ProjectHandler getUserReportProjects method...\n";
    
    // Use reflection to access the private method
    $reflection = new ReflectionClass($projectHandler);
    $getUserReportProjectsMethod = $reflection->getMethod('getUserReportProjects');
    $getUserReportProjectsMethod->setAccessible(true);
    
    $retrievedProjects = $getUserReportProjectsMethod->invoke($projectHandler, $testUserId);
    
    echo "   Method returned: " . json_encode($retrievedProjects) . "\n";
    echo "   Project count: " . count($retrievedProjects) . "\n";
    
    if (empty($retrievedProjects)) {
        echo "   ❌ Method returned empty array!\n";
    } else {
        echo "   ✅ Method successfully retrieved projects\n";
    }
    
    echo "\n3. Testing message text generation...\n";
    
    $text = '📋 <b>Проекты для отчёта</b>

Здесь вы можете составить личный список проектов, который будет отображаться при формировании отчётов.

';
    
    if (empty($retrievedProjects)) {
        $text .= 'У вас пока нет проектов в личном списке.

';
    } else {
        $text .= 'Ваши проекты для отчёта:
';
        foreach ($retrievedProjects as $project) {
            $text .= "• {$project}\n";
        }
        $text .= '
';
    }
    
    echo "   Generated message text:\n";
    echo "   " . str_replace("\n", "\n   ", $text) . "\n";
    
    echo "\n4. Testing showReportProjectsMenu method (with reflection)...\n";
    
    // Test the actual menu method
    $showReportProjectsMenuMethod = $reflection->getMethod('showReportProjectsMenu');
    $showReportProjectsMenuMethod->setAccessible(true);
    
    // Mock chat ID for testing
    $testChatId = (string)$testUserId;
    
    try {
        $result = $showReportProjectsMenuMethod->invoke($projectHandler, $testChatId, $testUserId);
        echo "   ✅ showReportProjectsMenu method executed successfully\n";
        echo "   Method returned: " . ($result ? "ServerResponse object" : "null") . "\n";
    } catch (Exception $e) {
        echo "   ❌ showReportProjectsMenu method failed: " . $e->getMessage() . "\n";
        echo "   Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n5. Checking for potential issues...\n";
    
    // Check encoding
    $projectNames = implode(', ', $retrievedProjects);
    if (mb_check_encoding($projectNames, 'UTF-8')) {
        echo "   ✅ Project names are properly UTF-8 encoded\n";
    } else {
        echo "   ❌ Project names have encoding issues\n";
    }
    
    // Check if projects array is actually an array
    if (is_array($retrievedProjects)) {
        echo "   ✅ Retrieved projects is an array\n";
    } else {
        echo "   ❌ Retrieved projects is not an array: " . gettype($retrievedProjects) . "\n";
    }
    
    // Check for empty strings in array
    $hasEmptyStrings = false;
    foreach ($retrievedProjects as $project) {
        if (empty(trim($project))) {
            $hasEmptyStrings = true;
            break;
        }
    }
    
    if (!$hasEmptyStrings && !empty($retrievedProjects)) {
        echo "   ✅ No empty project names found\n";
    } elseif ($hasEmptyStrings) {
        echo "   ❌ Found empty project names in the list\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>