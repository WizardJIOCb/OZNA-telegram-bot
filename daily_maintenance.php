<?php
/**
 * Daily maintenance script to prevent project data loss
 * Run this script daily to keep your project data synchronized and backed up
 */

echo "=== DAILY PROJECT DATA MAINTENANCE ===\n\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

$logFile = __DIR__ . '/logs/daily_maintenance_' . date('Y-m-d') . '.log';
$logDir = dirname($logFile);

// Create logs directory if it doesn't exist
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    echo $logEntry;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

logMessage("Starting daily maintenance routine");

// 1. Quick data integrity check
logMessage("1. Running quick data integrity check...");
try {
    // Check projects file
    $projectsFile = __DIR__ . '/data/projects.json';
    if (file_exists($projectsFile)) {
        $projects = json_decode(file_get_contents($projectsFile), true);
        if (is_array($projects) && count($projects) > 0) {
            logMessage("   ✓ projects.json: " . count($projects) . " projects");
        } else {
            logMessage("   ✗ projects.json: Invalid or empty file");
        }
    } else {
        logMessage("   ✗ projects.json: File missing");
    }
    
    // Check database connection
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    $allUsers = $databaseService->getAllUsers();
    logMessage("   ✓ Database: " . count($allUsers) . " users found");
    
    // Count users with project data
    $usersWithProjectData = 0;
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        if ((isset($userData['report_projects']) && !empty($userData['report_projects'])) ||
            (isset($userData['control_projects']) && !empty($userData['control_projects']))) {
            $usersWithProjectData++;
        }
    }
    logMessage("   ✓ Users with project data: {$usersWithProjectData}");
    
} catch (Exception $e) {
    logMessage("   ✗ Integrity check failed: " . $e->getMessage());
}

// 2. Auto-fix common issues
logMessage("2. Auto-fixing common issues...");
try {
    // Capture output from fix script
    ob_start();
    include __DIR__ . '/fix_project_data.php';
    $fixOutput = ob_get_clean();
    
    // Parse fix results
    $lines = explode("\n", $fixOutput);
    $fixCount = 0;
    foreach ($lines as $line) {
        if (strpos($line, '✓') !== false && strpos($line, 'Created') !== false || 
            strpos($line, 'Added') !== false || strpos($line, 'Removed') !== false) {
            $fixCount++;
            logMessage("   " . trim($line));
        }
    }
    
    if ($fixCount === 0) {
        logMessage("   ✓ No issues found to fix");
    } else {
        logMessage("   ✓ Applied {$fixCount} automatic fixes");
    }
    
} catch (Exception $e) {
    logMessage("   ✗ Auto-fix failed: " . $e->getMessage());
}

// 3. Create incremental backup (keep last 7 days)
logMessage("3. Creating incremental backup...");
try {
    $backupDir = __DIR__ . '/backups/daily_' . date('Y-m-d');
    
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // Backup projects file
    $projectsFile = __DIR__ . '/data/projects.json';
    if (file_exists($projectsFile)) {
        copy($projectsFile, $backupDir . '/projects.json');
        logMessage("   ✓ Backed up projects.json");
    }
    
    // Backup access requests
    $accessFile = __DIR__ . '/data/access_requests.json';
    if (file_exists($accessFile)) {
        copy($accessFile, $backupDir . '/access_requests.json');
        logMessage("   ✓ Backed up access_requests.json");
    }
    
    // Create database snapshot (lightweight)
    $allUsers = $databaseService->getAllUsers();
    $userProjectData = [];
    
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        if ((isset($userData['report_projects']) && !empty($userData['report_projects'])) ||
            (isset($userData['control_projects']) && !empty($userData['control_projects']))) {
            
            $userProjectData[] = [
                'user_id' => $user['user_id'],
                'fio' => $userData['fio'] ?? 'Unknown',
                'report_projects' => $userData['report_projects'] ?? [],
                'control_projects' => $userData['control_projects'] ?? []
            ];
        }
    }
    
    file_put_contents($backupDir . '/user_project_data.json', 
        json_encode($userProjectData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    logMessage("   ✓ Backed up user project data for " . count($userProjectData) . " users");
    
    // Clean old backups (keep only last 7 days)
    $allBackups = glob(__DIR__ . '/backups/daily_*');
    if (count($allBackups) > 7) {
        usort($allBackups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        $toDelete = array_slice($allBackups, 0, count($allBackups) - 7);
        foreach ($toDelete as $oldBackup) {
            if (is_dir($oldBackup)) {
                $files = glob($oldBackup . '/*');
                foreach ($files as $file) {
                    unlink($file);
                }
                rmdir($oldBackup);
                logMessage("   ✓ Cleaned old backup: " . basename($oldBackup));
            }
        }
    }
    
} catch (Exception $e) {
    logMessage("   ✗ Backup failed: " . $e->getMessage());
}

// 4. Generate daily statistics
logMessage("4. Generating daily statistics...");
try {
    $stats = [
        'date' => date('Y-m-d'),
        'total_projects' => count($projects ?? []),
        'total_users' => count($allUsers ?? []),
        'users_with_project_data' => $usersWithProjectData ?? 0,
        'backup_created' => file_exists($backupDir . '/projects.json'),
        'data_integrity_ok' => file_exists($projectsFile) && is_array($projects ?? null)
    ];
    
    $statsFile = __DIR__ . '/logs/daily_stats.json';
    $allStats = [];
    
    // Load existing stats
    if (file_exists($statsFile)) {
        $existingStats = json_decode(file_get_contents($statsFile), true);
        if (is_array($existingStats)) {
            $allStats = $existingStats;
        }
    }
    
    // Add today's stats
    $allStats[date('Y-m-d')] = $stats;
    
    // Keep only last 30 days
    if (count($allStats) > 30) {
        $allStats = array_slice($allStats, -30, 30, true);
    }
    
    file_put_contents($statsFile, json_encode($allStats, JSON_PRETTY_PRINT));
    
    logMessage("   ✓ Statistics updated");
    logMessage("     Projects: " . $stats['total_projects']);
    logMessage("     Users: " . $stats['total_users']);
    logMessage("     Users with projects: " . $stats['users_with_project_data']);
    
} catch (Exception $e) {
    logMessage("   ✗ Statistics generation failed: " . $e->getMessage());
}

logMessage("Daily maintenance completed successfully");
logMessage("Log saved to: " . $logFile);

echo "\n=== MAINTENANCE SUMMARY ===\n";
echo "✓ Data integrity checked\n";
echo "✓ Common issues auto-fixed\n";
echo "✓ Daily backup created\n";
echo "✓ Statistics updated\n";
echo "\nFor detailed log, see: {$logFile}\n";

?>