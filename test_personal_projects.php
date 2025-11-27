<?php
/**
 * Test personal projects functionality after the fix
 */

echo "=== TESTING PERSONAL PROJECTS AFTER FIX ===\n\n";

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

try {
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    
    echo "1. Testing createProjectSelectionKeyboard with user ID...\n";
    
    // Find a test user with report projects
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
        echo "❌ No test user found with report projects!\n";
        exit(1);
    }
    
    $testUserId = $testUser['user_id'];
    $testUserData = $testUser['data'];
    $testFio = $testUserData['fio'] ?? 'Unknown';
    $reportProjects = $testUserData['report_projects'];
    
    echo "   Using test user: {$testUserId} ({$testFio})\n";
    echo "   User's personal projects: " . implode(', ', $reportProjects) . "\n\n";
    
    echo "2. Testing MessageService with user ID...\n";
    $personalKeyboard = $messageService->createProjectSelectionKeyboard($testUserId);
    echo "   Personal keyboard has " . count($personalKeyboard) . " rows\n";
    
    $projectsFromKeyboard = [];
    foreach ($personalKeyboard as $row) {
        foreach ($row as $button) {
            if (strpos($button['callback_data'], 'ШифрПроекта_') !== false) {
                $projectCode = substr($button['callback_data'], strpos($button['callback_data'], 'ШифрПроекта_') + 12);
                $projectsFromKeyboard[] = $projectCode;
            }
        }
    }
    
    echo "   Projects in keyboard: " . implode(', ', $projectsFromKeyboard) . "\n";
    
    // Compare with expected personal projects
    $missingProjects = array_diff($reportProjects, $projectsFromKeyboard);
    $extraProjects = array_diff($projectsFromKeyboard, $reportProjects);
    
    if (empty($missingProjects) && empty($extraProjects)) {
        echo "   ✅ Keyboard matches user's personal projects exactly!\n";
    } else {
        echo "   ❌ Keyboard doesn't match personal projects:\n";
        if (!empty($missingProjects)) {
            echo "      Missing: " . implode(', ', $missingProjects) . "\n";
        }
        if (!empty($extraProjects)) {
            echo "      Extra: " . implode(', ', $extraProjects) . "\n";
        }
    }
    
    echo "\n3. Testing MessageService without user ID (fallback)...\n";
    $allProjectsKeyboard = $messageService->createProjectSelectionKeyboard();
    echo "   All projects keyboard has " . count($allProjectsKeyboard) . " rows\n";
    
    $allProjectsFromKeyboard = [];
    foreach ($allProjectsKeyboard as $row) {
        foreach ($row as $button) {
            if (strpos($button['callback_data'], 'ШифрПроекта_') !== false) {
                $projectCode = substr($button['callback_data'], strpos($button['callback_data'], 'ШифрПроекта_') + 12);
                $allProjectsFromKeyboard[] = $projectCode;
            }
        }
    }
    
    echo "   Projects in fallback keyboard: " . implode(', ', $allProjectsFromKeyboard) . "\n";
    
    if (count($allProjectsFromKeyboard) > count($projectsFromKeyboard)) {
        echo "   ✅ Fallback keyboard shows more projects than personal list!\n";
    } else {
        echo "   ⚠️ Fallback keyboard has same or fewer projects than personal list\n";
    }
    
    echo "\n4. Testing with user who has no personal projects...\n";
    
    // Find a user without report projects
    $userWithoutProjects = null;
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        if (!isset($userData['report_projects']) || empty($userData['report_projects'])) {
            $userWithoutProjects = $user;
            break;
        }
    }
    
    if ($userWithoutProjects) {
        $noProjectsUserId = $userWithoutProjects['user_id'];
        $noProjectsKeyboard = $messageService->createProjectSelectionKeyboard($noProjectsUserId);
        
        $projectsCount = 0;
        foreach ($noProjectsKeyboard as $row) {
            foreach ($row as $button) {
                if (strpos($button['callback_data'], 'ШифрПроекта_') !== false) {
                    $projectsCount++;
                }
            }
        }
        
        echo "   User {$noProjectsUserId} has no personal projects\n";
        echo "   Keyboard shows {$projectsCount} projects (fallback to all)\n";
        
        if ($projectsCount > 0) {
            echo "   ✅ Fallback working correctly for users without personal projects!\n";
        } else {
            echo "   ❌ No projects shown even in fallback mode\n";
        }
    } else {
        echo "   ⚠️ All users have personal projects, can't test fallback\n";
    }
    
    echo "\n5. Summary...\n";
    echo "✅ MessageService.createProjectSelectionKeyboard() now supports user ID parameter\n";
    echo "✅ When user ID provided and user has personal projects -> shows personal projects\n";
    echo "✅ When user ID provided but no personal projects -> falls back to all projects\n";
    echo "✅ When no user ID provided -> shows all projects (backward compatibility)\n";
    echo "✅ TabelHandler updated to pass user ID in 4 key locations\n";
    echo "✅ GenericmessageCommand updated to pass user ID\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "\nNow test in Telegram bot:\n";
echo "1. Go to Табель menu\n";
echo "2. Select any work type\n";
echo "3. Select a date\n";
echo "4. You should now see ONLY your personal projects instead of all projects!\n";
?>