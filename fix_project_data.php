<?php
/**
 * Automatic fix script for common project data issues
 * This script can resolve typical sync problems between file and database
 */

echo "=== AUTOMATIC PROJECT DATA FIX ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$fixes = [];
$errors = [];

echo "Analyzing project data for common issues...\n\n";

// 1. Fix missing projects.json
echo "1. Checking projects.json file...\n";
$projectsFile = __DIR__ . '/data/projects.json';

if (!file_exists($projectsFile)) {
    echo "   Creating missing projects.json with default projects...\n";
    
    $defaultProjects = [
        'ОИ834', 'ОИ651', 'ОИ696', 'ОИ795', 'ОИ810', 'ОИ768', 
        'ОИ837', 'ОИ562', 'ОИ788', 'ОИ860', 'ОИ862', 'БКНС115', 'БКНС120'
    ];
    
    // Create data directory if it doesn't exist
    $dataDir = dirname($projectsFile);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    $jsonData = json_encode($defaultProjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($projectsFile, $jsonData);
    
    $fixes[] = "Created projects.json with " . count($defaultProjects) . " default projects";
    echo "   ✓ Created projects.json with default projects\n";
} else {
    echo "   ✓ projects.json exists\n";
}

// 2. Fix orphan projects in database
echo "\n2. Checking for orphan projects in database...\n";
try {
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $allUsers = $databaseService->getAllUsers();
    
    // Load current projects from file
    $jsonData = file_get_contents($projectsFile);
    $fileProjects = json_decode($jsonData, true);
    $validProjects = array_flip($fileProjects); // For faster lookup
    
    $orphanProjects = [];
    $usersWithOrphans = 0;
    
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        $userHasOrphans = false;
        $userUpdated = false;
        
        // Check report projects
        if (isset($userData['report_projects']) && is_array($userData['report_projects'])) {
            $validReportProjects = [];
            foreach ($userData['report_projects'] as $project) {
                if (isset($validProjects[$project])) {
                    $validReportProjects[] = $project;
                } else {
                    $orphanProjects[] = $project;
                    $userHasOrphans = true;
                }
            }
            
            if ($userHasOrphans && count($validReportProjects) !== count($userData['report_projects'])) {
                $userData['report_projects'] = $validReportProjects;
                $userUpdated = true;
            }
        }
        
        // Check control projects
        if (isset($userData['control_projects']) && is_array($userData['control_projects'])) {
            $validControlProjects = [];
            foreach ($userData['control_projects'] as $project => $settings) {
                if (isset($validProjects[$project])) {
                    $validControlProjects[$project] = $settings;
                } else {
                    $orphanProjects[] = $project;
                    $userHasOrphans = true;
                }
            }
            
            if ($userHasOrphans && count($validControlProjects) !== count($userData['control_projects'])) {
                $userData['control_projects'] = $validControlProjects;
                $userUpdated = true;
            }
        }
        
        // Update user data if we removed orphan projects
        if ($userUpdated) {
            $success = $databaseService->updateUserData($userId, $userData);
            if ($success) {
                $usersWithOrphans++;
                echo "   ✓ Cleaned orphan projects for user {$userId}\n";
            } else {
                $errors[] = "Failed to update user {$userId}";
                echo "   ✗ Failed to update user {$userId}\n";
            }
        }
    }
    
    $uniqueOrphans = array_unique($orphanProjects);
    
    if (empty($uniqueOrphans)) {
        echo "   ✓ No orphan projects found\n";
    } else {
        $fixes[] = "Removed " . count($uniqueOrphans) . " orphan projects from {$usersWithOrphans} users";
        echo "   ✓ Removed orphan projects: " . implode(', ', $uniqueOrphans) . "\n";
    }
    
} catch (Exception $e) {
    $errors[] = "Database orphan cleanup failed: " . $e->getMessage();
    echo "   ✗ Database orphan cleanup failed: " . $e->getMessage() . "\n";
}

// 3. Add missing projects from database to file
echo "\n3. Checking for missing projects in file...\n";
try {
    $allProjectsFromDB = [];
    
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        
        if (isset($userData['report_projects'])) {
            $allProjectsFromDB = array_merge($allProjectsFromDB, $userData['report_projects']);
        }
        
        if (isset($userData['control_projects'])) {
            $allProjectsFromDB = array_merge($allProjectsFromDB, array_keys($userData['control_projects']));
        }
    }
    
    $uniqueDBProjects = array_unique($allProjectsFromDB);
    $missingInFile = [];
    
    foreach ($uniqueDBProjects as $project) {
        if (!in_array($project, $fileProjects)) {
            $missingInFile[] = $project;
        }
    }
    
    if (!empty($missingInFile)) {
        // Add missing projects to file
        $updatedProjects = array_merge($fileProjects, $missingInFile);
        sort($updatedProjects); // Sort for consistency
        
        $jsonData = json_encode($updatedProjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($projectsFile, $jsonData);
        
        $fixes[] = "Added " . count($missingInFile) . " missing projects to projects.json";
        echo "   ✓ Added missing projects to file: " . implode(', ', $missingInFile) . "\n";
    } else {
        echo "   ✓ No missing projects found\n";
    }
    
} catch (Exception $e) {
    $errors[] = "Missing projects check failed: " . $e->getMessage();
    echo "   ✗ Missing projects check failed: " . $e->getMessage() . "\n";
}

// 4. Fix duplicate projects in file
echo "\n4. Checking for duplicate projects in file...\n";
$jsonData = file_get_contents($projectsFile);
$projects = json_decode($jsonData, true);

if (is_array($projects)) {
    $uniqueProjects = array_unique($projects);
    
    if (count($uniqueProjects) !== count($projects)) {
        sort($uniqueProjects); // Sort for consistency
        $jsonData = json_encode($uniqueProjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($projectsFile, $jsonData);
        
        $duplicateCount = count($projects) - count($uniqueProjects);
        $fixes[] = "Removed {$duplicateCount} duplicate projects from file";
        echo "   ✓ Removed {$duplicateCount} duplicate projects\n";
    } else {
        echo "   ✓ No duplicate projects found\n";
    }
}

// 5. Verify AdminService can still load projects
echo "\n5. Verifying AdminService after fixes...\n";
try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    
    $adminProjects = $adminService->getAllProjects();
    $adminProjectCount = count($adminProjects);
    
    echo "   ✓ AdminService can load {$adminProjectCount} projects\n";
    $fixes[] = "AdminService verification passed";
    
} catch (Exception $e) {
    $errors[] = "AdminService verification failed after fixes: " . $e->getMessage();
    echo "   ✗ AdminService verification failed: " . $e->getMessage() . "\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "FIX SUMMARY\n";
echo str_repeat("=", 50) . "\n";

if (!empty($fixes)) {
    echo "\n✅ FIXES APPLIED (" . count($fixes) . "):\n";
    foreach ($fixes as $fix) {
        echo "   • {$fix}\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   • {$error}\n";
    }
}

if (empty($errors)) {
    echo "\n🎉 ALL FIXES COMPLETED SUCCESSFULLY!\n";
    echo "Your project data should now be consistent and synchronized.\n";
} else {
    echo "\n⚠️ SOME FIXES FAILED!\n";
    echo "Please review the errors above and fix them manually.\n";
}

echo "\nRecommendations:\n";
echo "1. Run verify_data_integrity.php to confirm all issues are resolved\n";
echo "2. Create a backup with backup_all_data.php\n";
echo "3. Test project functionality in the bot\n";

?>