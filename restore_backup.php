<?php
/**
 * Restore script for comprehensive project data backup
 * This restores both file-based and database-based project data
 */

if ($argc < 2) {
    echo "Usage: php restore_backup.php <backup_directory>\n";
    echo "Example: php restore_backup.php backups/2025-01-01_12-30-45\n";
    exit(1);
}

$backupDir = $argv[1];

if (!is_dir($backupDir)) {
    echo "Error: Backup directory '$backupDir' not found!\n";
    exit(1);
}

echo "=== RESTORING PROJECT DATA FROM BACKUP ===\n\n";
echo "Backup directory: $backupDir\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

// 1. Restore projects.json file
echo "1. Restoring projects.json file...\n";
$backupProjectsFile = $backupDir . '/projects.json';
$targetProjectsFile = __DIR__ . '/data/projects.json';

if (file_exists($backupProjectsFile)) {
    // Create backup of current file if it exists
    if (file_exists($targetProjectsFile)) {
        $currentBackup = $targetProjectsFile . '.pre-restore-' . date('Y-m-d_H-i-s');
        copy($targetProjectsFile, $currentBackup);
        echo "   ✓ Current projects.json backed up to: " . basename($currentBackup) . "\n";
    }
    
    // Restore the file
    copy($backupProjectsFile, $targetProjectsFile);
    
    $projects = json_decode(file_get_contents($targetProjectsFile), true);
    echo "   ✓ Restored " . count($projects) . " projects to projects.json\n";
} else {
    echo "   ⚠️ No projects.json found in backup\n";
}

// 2. Restore user project data
echo "\n2. Restoring user project data...\n";
$backupUserDataFile = $backupDir . '/user_project_data.json';

if (file_exists($backupUserDataFile)) {
    try {
        $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        $userProjectData = json_decode(file_get_contents($backupUserDataFile), true);
        
        $restoredUsers = 0;
        $skippedUsers = 0;
        
        foreach ($userProjectData as $userData) {
            $userId = $userData['user_id'];
            
            // Get current user data
            $currentUserData = $databaseService->getUserData($userId);
            
            // Merge project data
            $hasChanges = false;
            
            if (isset($userData['report_projects'])) {
                $currentUserData['report_projects'] = $userData['report_projects'];
                $hasChanges = true;
            }
            
            if (isset($userData['control_projects'])) {
                $currentUserData['control_projects'] = $userData['control_projects'];
                $hasChanges = true;
            }
            
            // Update user data if there are changes
            if ($hasChanges) {
                $success = $databaseService->updateUserData($userId, $currentUserData);
                if ($success) {
                    $restoredUsers++;
                    echo "   ✓ Restored data for user {$userId} ({$userData['fio']})\n";
                } else {
                    $skippedUsers++;
                    echo "   ✗ Failed to restore data for user {$userId}\n";
                }
            } else {
                $skippedUsers++;
                echo "   - No project data to restore for user {$userId}\n";
            }
        }
        
        echo "   ✓ Restored project data for $restoredUsers users\n";
        if ($skippedUsers > 0) {
            echo "   - Skipped $skippedUsers users (no changes or errors)\n";
        }
        
    } catch (Exception $e) {
        echo "   ✗ Database restore failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️ No user_project_data.json found in backup\n";
}

// 3. Restore access requests
echo "\n3. Restoring access requests...\n";
$backupAccessFile = $backupDir . '/access_requests.json';
$targetAccessFile = __DIR__ . '/data/access_requests.json';

if (file_exists($backupAccessFile)) {
    // Create backup of current file if it exists
    if (file_exists($targetAccessFile)) {
        $currentBackup = $targetAccessFile . '.pre-restore-' . date('Y-m-d_H-i-s');
        copy($targetAccessFile, $currentBackup);
        echo "   ✓ Current access_requests.json backed up to: " . basename($currentBackup) . "\n";
    }
    
    // Restore the file
    copy($backupAccessFile, $targetAccessFile);
    
    $requests = json_decode(file_get_contents($targetAccessFile), true);
    echo "   ✓ Restored " . count($requests) . " access requests\n";
} else {
    echo "   ⚠️ No access_requests.json found in backup\n";
}

// 4. Verification
echo "\n4. Verification...\n";
echo "Running verification checks...\n";

// Verify projects file
if (file_exists($targetProjectsFile)) {
    $projects = json_decode(file_get_contents($targetProjectsFile), true);
    if (is_array($projects) && count($projects) > 0) {
        echo "   ✓ projects.json verified: " . count($projects) . " projects loaded\n";
    } else {
        echo "   ✗ projects.json verification failed: invalid or empty\n";
    }
} else {
    echo "   ✗ projects.json not found after restore\n";
}

// Test database connection and user data
try {
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $testUsers = $databaseService->getAllUsers();
    echo "   ✓ Database connection verified: " . count($testUsers) . " users found\n";
} catch (Exception $e) {
    echo "   ✗ Database verification failed: " . $e->getMessage() . "\n";
}

echo "\n=== RESTORE COMPLETED ===\n";
echo "Summary:\n";
echo "- Project files: " . (file_exists($targetProjectsFile) ? "✓ RESTORED" : "✗ MISSING") . "\n";
echo "- User data: ✓ PROCESSED\n";
echo "- Access requests: " . (file_exists($targetAccessFile) ? "✓ RESTORED" : "✗ MISSING") . "\n";

echo "\nRecommendation: Run verification script to ensure everything works correctly:\n";
echo "php verify_data_integrity.php\n";

?>