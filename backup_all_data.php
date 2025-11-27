<?php
/**
 * Comprehensive backup script for all project data
 * This ensures both file-based and database-based project data is preserved
 */

echo "=== COMPREHENSIVE PROJECT DATA BACKUP ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

// Create backup directory with timestamp
$backupDir = __DIR__ . '/backups/' . date('Y-m-d_H-i-s');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

echo "Creating backup in: $backupDir\n\n";

// 1. Backup projects.json file
echo "1. Backing up projects.json file...\n";
$projectsFile = __DIR__ . '/data/projects.json';
if (file_exists($projectsFile)) {
    $backupProjectsFile = $backupDir . '/projects.json';
    copy($projectsFile, $backupProjectsFile);
    
    $projects = json_decode(file_get_contents($projectsFile), true);
    echo "   ✓ Backed up " . count($projects) . " projects from file\n";
    echo "   File saved to: " . basename($backupProjectsFile) . "\n";
} else {
    echo "   ⚠️ projects.json file not found\n";
}

// 2. Backup database project data
echo "\n2. Backing up database project data...\n";
try {
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $allUsers = $databaseService->getAllUsers();
    
    $userProjectData = [];
    $totalUsers = 0;
    $usersWithProjects = 0;
    
    foreach ($allUsers as $user) {
        $totalUsers++;
        $userData = $user['data'];
        $userId = $user['user_id'];
        
        // Check if user has any project-related data
        $hasProjectData = false;
        $userProjects = [
            'user_id' => $userId,
            'fio' => $userData['fio'] ?? 'Unknown',
            'functional_direction' => $userData['functional_direction'] ?? 'Unknown'
        ];
        
        if (isset($userData['report_projects']) && !empty($userData['report_projects'])) {
            $userProjects['report_projects'] = $userData['report_projects'];
            $hasProjectData = true;
        }
        
        if (isset($userData['control_projects']) && !empty($userData['control_projects'])) {
            $userProjects['control_projects'] = $userData['control_projects'];
            $hasProjectData = true;
        }
        
        if ($hasProjectData) {
            $userProjectData[] = $userProjects;
            $usersWithProjects++;
        }
    }
    
    // Save user project data
    $userDataFile = $backupDir . '/user_project_data.json';
    file_put_contents($userDataFile, json_encode($userProjectData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "   ✓ Backed up project data for $usersWithProjects out of $totalUsers users\n";
    echo "   File saved to: " . basename($userDataFile) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Database backup failed: " . $e->getMessage() . "\n";
}

// 3. Backup access requests
echo "\n3. Backing up access requests...\n";
$accessRequestsFile = __DIR__ . '/data/access_requests.json';
if (file_exists($accessRequestsFile)) {
    $backupAccessFile = $backupDir . '/access_requests.json';
    copy($accessRequestsFile, $backupAccessFile);
    
    $requests = json_decode(file_get_contents($accessRequestsFile), true);
    echo "   ✓ Backed up " . count($requests) . " access requests\n";
    echo "   File saved to: " . basename($backupAccessFile) . "\n";
} else {
    echo "   ⚠️ access_requests.json file not found\n";
}

// 4. Create backup summary
echo "\n4. Creating backup summary...\n";
$summaryFile = $backupDir . '/backup_summary.txt';
$summary = "BACKUP SUMMARY\n";
$summary .= "==============\n";
$summary .= "Backup Date: " . date('Y-m-d H:i:s') . "\n";
$summary .= "Backup Directory: $backupDir\n\n";

$summary .= "FILES BACKED UP:\n";
$summary .= "- projects.json: " . (file_exists($backupDir . '/projects.json') ? "✓ YES" : "✗ NO") . "\n";
$summary .= "- user_project_data.json: " . (file_exists($backupDir . '/user_project_data.json') ? "✓ YES" : "✗ NO") . "\n";
$summary .= "- access_requests.json: " . (file_exists($backupDir . '/access_requests.json') ? "✓ YES" : "✗ NO") . "\n\n";

if (isset($projects)) {
    $summary .= "PROJECT STATISTICS:\n";
    $summary .= "- Total projects in file: " . count($projects) . "\n";
    $summary .= "- Users with project data: " . ($usersWithProjects ?? 0) . "\n";
    $summary .= "- Total users: " . ($totalUsers ?? 0) . "\n\n";
}

$summary .= "RESTORE INSTRUCTIONS:\n";
$summary .= "1. Copy projects.json to data/projects.json\n";
$summary .= "2. Run restore_user_project_data.php to restore user data\n";
$summary .= "3. Copy access_requests.json to data/access_requests.json\n";

file_put_contents($summaryFile, $summary);
echo "   ✓ Backup summary created: " . basename($summaryFile) . "\n";

echo "\n=== BACKUP COMPLETED SUCCESSFULLY ===\n";
echo "Backup location: $backupDir\n";
echo "\nTo restore this backup:\n";
echo "1. php restore_backup.php $backupDir\n";
echo "2. Or manually copy files and run verification\n";

?>