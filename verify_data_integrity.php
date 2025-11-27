<?php
/**
 * Data integrity verification script
 * Checks if all project data sources are synchronized and valid
 */

echo "=== PROJECT DATA INTEGRITY VERIFICATION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$errors = [];
$warnings = [];
$success = [];

// 1. Verify projects.json file
echo "1. Verifying projects.json file...\n";
$projectsFile = __DIR__ . '/data/projects.json';

if (file_exists($projectsFile)) {
    $jsonData = file_get_contents($projectsFile);
    $projects = json_decode($jsonData, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($projects)) {
        $projectCount = count($projects);
        echo "   ✓ File exists and contains valid JSON\n";
        echo "   ✓ Contains {$projectCount} projects\n";
        
        // Check for duplicates
        $uniqueProjects = array_unique($projects);
        if (count($uniqueProjects) !== $projectCount) {
            $warnings[] = "projects.json contains duplicate entries";
            echo "   ⚠️ Found duplicate projects in file\n";
        } else {
            echo "   ✓ No duplicate projects found\n";
        }
        
        // Show sample projects
        echo "   Sample projects: " . implode(', ', array_slice($projects, 0, 5)) . "\n";
        $success[] = "projects.json file is valid with {$projectCount} projects";
    } else {
        $errors[] = "projects.json contains invalid JSON";
        echo "   ✗ File contains invalid JSON\n";
    }
} else {
    $errors[] = "projects.json file is missing";
    echo "   ✗ File does not exist\n";
}

// 2. Verify database connection and structure
echo "\n2. Verifying database connection...\n";
try {
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    echo "   ✓ Database connection established\n";
    
    // Get all users
    $allUsers = $databaseService->getAllUsers();
    $totalUsers = count($allUsers);
    echo "   ✓ Found {$totalUsers} users in database\n";
    
    $usersWithReportProjects = 0;
    $usersWithControlProjects = 0;
    $totalReportProjects = 0;
    $totalControlProjects = 0;
    $invalidUserData = 0;
    
    foreach ($allUsers as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'];
        
        // Validate user data structure
        if (!is_array($userData)) {
            $invalidUserData++;
            continue;
        }
        
        // Check report projects
        if (isset($userData['report_projects']) && is_array($userData['report_projects'])) {
            $usersWithReportProjects++;
            $totalReportProjects += count($userData['report_projects']);
        }
        
        // Check control projects
        if (isset($userData['control_projects']) && is_array($userData['control_projects'])) {
            $usersWithControlProjects++;
            $totalControlProjects += count($userData['control_projects']);
        }
    }
    
    echo "   ✓ Users with report projects: {$usersWithReportProjects}\n";
    echo "   ✓ Users with control projects: {$usersWithControlProjects}\n";
    echo "   ✓ Total report project assignments: {$totalReportProjects}\n";
    echo "   ✓ Total control project assignments: {$totalControlProjects}\n";
    
    if ($invalidUserData > 0) {
        $warnings[] = "{$invalidUserData} users have invalid data structure";
        echo "   ⚠️ {$invalidUserData} users have invalid data structure\n";
    }
    
    $success[] = "Database connection and user data verified";
    
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
}

// 3. Cross-reference projects between file and database
echo "\n3. Cross-referencing project data...\n";
if (isset($projects) && isset($allUsers)) {
    $projectsFromFile = array_flip($projects); // For faster lookup
    $projectsFromDB = [];
    
    // Collect all projects mentioned in database
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        
        if (isset($userData['report_projects'])) {
            foreach ($userData['report_projects'] as $project) {
                $projectsFromDB[] = $project;
            }
        }
        
        if (isset($userData['control_projects'])) {
            foreach (array_keys($userData['control_projects']) as $project) {
                $projectsFromDB[] = $project;
            }
        }
    }
    
    $uniqueProjectsFromDB = array_unique($projectsFromDB);
    
    // Check for projects in DB that are not in file
    $orphanProjects = [];
    foreach ($uniqueProjectsFromDB as $project) {
        if (!isset($projectsFromFile[$project])) {
            $orphanProjects[] = $project;
        }
    }
    
    if (empty($orphanProjects)) {
        echo "   ✓ All database projects exist in projects.json\n";
        $success[] = "Project data consistency verified";
    } else {
        $warnings[] = "Found projects in database that don't exist in projects.json: " . implode(', ', $orphanProjects);
        echo "   ⚠️ Found " . count($orphanProjects) . " orphan projects in database\n";
        echo "   Orphan projects: " . implode(', ', array_slice($orphanProjects, 0, 5)) . "\n";
    }
}

// 4. Verify AdminService can load projects
echo "\n4. Verifying AdminService project loading...\n";
try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
    
    $adminProjects = $adminService->getAllProjects();
    $adminProjectCount = count($adminProjects);
    
    echo "   ✓ AdminService loaded {$adminProjectCount} projects\n";
    
    if (isset($projects) && count($projects) === $adminProjectCount) {
        echo "   ✓ AdminService project count matches file\n";
        $success[] = "AdminService project loading verified";
    } else {
        $warnings[] = "AdminService project count doesn't match file";
        echo "   ⚠️ AdminService project count doesn't match file\n";
    }
    
} catch (Exception $e) {
    $errors[] = "AdminService verification failed: " . $e->getMessage();
    echo "   ✗ AdminService verification failed: " . $e->getMessage() . "\n";
}

// 5. Summary and recommendations
echo "\n" . str_repeat("=", 50) . "\n";
echo "VERIFICATION SUMMARY\n";
echo str_repeat("=", 50) . "\n";

if (!empty($success)) {
    echo "\n✅ SUCCESSFUL CHECKS (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "   • {$item}\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $item) {
        echo "   • {$item}\n";
    }
}

if (!empty($errors)) {
    echo "\n❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $item) {
        echo "   • {$item}\n";
    }
}

echo "\n" . str_repeat("-", 50) . "\n";

if (empty($errors)) {
    if (empty($warnings)) {
        echo "🎉 ALL CHECKS PASSED! Your project data is consistent and healthy.\n";
    } else {
        echo "✅ MOSTLY HEALTHY! Some warnings found but no critical errors.\n";
    }
} else {
    echo "🚨 CRITICAL ISSUES FOUND! Please address the errors above.\n";
    echo "\nRecommended actions:\n";
    echo "1. Run backup_all_data.php before making any fixes\n";
    echo "2. Address each error systematically\n";
    echo "3. Re-run this verification script\n";
}

echo "\nFor automatic fixing of common issues, run:\n";
echo "php fix_project_data.php\n";

?>