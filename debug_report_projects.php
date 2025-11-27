<?php
/**
 * Debug script to check why selected projects aren't displaying in "Проекты для отчёта"
 */

echo "=== DEBUG REPORT PROJECTS DISPLAY ===\n\n";

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
    
    echo "1. Checking all users with report projects...\n";
    $allUsers = $databaseService->getAllUsers();
    $usersWithReportProjects = 0;
    
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        
        if (isset($userData['report_projects']) && !empty($userData['report_projects'])) {
            $usersWithReportProjects++;
            $fio = $userData['fio'] ?? 'Unknown';
            $reportProjects = $userData['report_projects'];
            
            echo "   User {$userId} ({$fio}): " . count($reportProjects) . " projects\n";
            echo "   Projects: " . implode(', ', $reportProjects) . "\n\n";
        }
    }
    
    if ($usersWithReportProjects === 0) {
        echo "   ❌ No users found with report projects!\n";
        echo "   This means nobody has added any projects yet.\n\n";
        
        echo "2. Let's test adding a project for a user...\n";
        
        // Find the first user in database
        if (!empty($allUsers)) {
            $testUser = $allUsers[0];
            $testUserId = $testUser['user_id'];
            $testUserData = $testUser['data'];
            $testFio = $testUserData['fio'] ?? 'Test User';
            
            echo "   Using test user: {$testUserId} ({$testFio})\n";
            
            // Get available projects
            $allProjects = $adminService->getAllProjects();
            echo "   Available projects: " . implode(', ', $allProjects) . "\n";
            
            if (!empty($allProjects)) {
                // Add first project to user's report list
                $projectToAdd = $allProjects[0];
                echo "   Adding project '{$projectToAdd}' to user's report list...\n";
                
                $reportProjects = $testUserData['report_projects'] ?? [];
                if (!in_array($projectToAdd, $reportProjects)) {
                    $reportProjects[] = $projectToAdd;
                    $testUserData['report_projects'] = $reportProjects;
                    
                    $success = $databaseService->updateUserData($testUserId, $testUserData);
                    
                    if ($success) {
                        echo "   ✅ Successfully added project to user's list!\n";
                        
                        // Verify the data was saved
                        $verifyData = $databaseService->getUserData($testUserId);
                        $verifyProjects = $verifyData['report_projects'] ?? [];
                        echo "   ✅ Verification: User now has " . count($verifyProjects) . " projects\n";
                        echo "   Projects: " . implode(', ', $verifyProjects) . "\n";
                        
                    } else {
                        echo "   ❌ Failed to save project to database!\n";
                    }
                } else {
                    echo "   ℹ️ Project already in user's list\n";
                }
            }
        }
    } else {
        echo "   ✅ Found {$usersWithReportProjects} users with report projects\n";
    }
    
    echo "\n3. Testing display logic...\n";
    
    // Test the display logic with actual data
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        
        if (isset($userData['report_projects']) && !empty($userData['report_projects'])) {
            $fio = $userData['fio'] ?? 'Unknown';
            $reportProjects = $userData['report_projects'];
            
            echo "   Testing display for user {$userId} ({$fio}):\n";
            
            $text = "📋 **Проекты для отчёта**\n\n";
            $text .= "Здесь вы можете составить личный список проектов, который будет отображаться при формировании отчётов.\n\n";
            
            if (empty($reportProjects)) {
                $text .= "У вас пока нет проектов в личном списке.\n\n";
            } else {
                $text .= "Ваши проекты для отчёта:\n";
                foreach ($reportProjects as $project) {
                    $text .= "• {$project}\n";
                }
                $text .= "\n";
            }
            
            echo "   Generated text:\n";
            echo "   " . str_replace("\n", "\n   ", $text) . "\n";
            break; // Only test first user
        }
    }
    
    echo "\n4. Final recommendations...\n";
    echo "   - Check the bot logs for ProjectHandler debug messages\n";
    echo "   - Try adding/removing projects through the bot interface\n";
    echo "   - Verify that the projects are being saved correctly\n";
    echo "   - Check if there are any encoding issues with project names\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>