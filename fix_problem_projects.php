<?php
/**
 * Fix problem projects: ТЕСТ1234 and 📁 Проекты
 * Also clean up any rejected projects with is_active = 1
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Fix Problem Projects ===\n\n";

try {
    $databaseService = new DatabaseService();
    $conn = $databaseService->getConnection();
    
    echo "Step 1: Finding all problematic projects...\n";
    echo str_repeat('-', 70) . "\n";
    
    // Find all projects that shouldn't be visible
    $query = "SELECT id, current_name, status, is_active, added_by, added_at, moderation_comment
              FROM project_moderation 
              WHERE (status = 'rejected' AND is_active = 1)
                 OR current_name LIKE '%📁%'
                 OR current_name LIKE '%ТЕСТ%'
                 OR current_name LIKE '%TEST%'
              ORDER BY current_name ASC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        echo "❌ Error querying database: " . $conn->error . "\n";
        exit(1);
    }
    
    $problemProjects = [];
    while ($row = $result->fetch_assoc()) {
        $problemProjects[] = $row;
    }
    
    if (empty($problemProjects)) {
        echo "✅ No problem projects found!\n";
        exit(0);
    }
    
    echo "Found " . count($problemProjects) . " potential problem projects:\n\n";
    
    foreach ($problemProjects as $idx => $project) {
        $num = $idx + 1;
        echo "{$num}. '{$project['current_name']}' (ID: {$project['id']})\n";
        echo "   Status: {$project['status']}\n";
        echo "   is_active: {$project['is_active']}\n";
        echo "   Added: {$project['added_at']} by user {$project['added_by']}\n";
        if ($project['moderation_comment']) {
            echo "   Comment: {$project['moderation_comment']}\n";
        }
        
        // Highlight issues
        $issues = [];
        if ($project['status'] === 'rejected' && $project['is_active'] == 1) {
            $issues[] = "Rejected but still active";
        }
        if (preg_match('/[\x{1F300}-\x{1F9FF}]/u', $project['current_name'])) {
            $issues[] = "Contains emoji";
        }
        if (stripos($project['current_name'], 'ТЕСТ') !== false || 
            stripos($project['current_name'], 'TEST') !== false) {
            $issues[] = "Test project";
        }
        
        if (!empty($issues)) {
            echo "   ⚠️  Issues: " . implode(", ", $issues) . "\n";
        }
        echo "\n";
    }
    
    echo str_repeat('=', 70) . "\n";
    echo "Step 2: Cleaning up projects...\n";
    echo str_repeat('-', 70) . "\n\n";
    
    // Ask for confirmation
    echo "This will:\n";
    echo "  1. Set is_active = 0 for all rejected projects\n";
    echo "  2. Set status = 'rejected' for projects with emojis\n";
    echo "  3. Set is_active = 0 for test projects (ТЕСТ/TEST)\n";
    echo "\nDo you want to proceed? (yes/no): ";
    
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $confirmation = trim(strtolower($line));
    fclose($handle);
    
    if ($confirmation !== 'yes' && $confirmation !== 'y') {
        echo "\n❌ Aborted by user.\n";
        exit(0);
    }
    
    echo "\nProceeding with cleanup...\n\n";
    
    $updatedCount = 0;
    
    foreach ($problemProjects as $project) {
        $projectId = $project['id'];
        $projectName = $project['current_name'];
        
        // Determine what to do
        $needsUpdate = false;
        $newStatus = $project['status'];
        $newIsActive = $project['is_active'];
        $comment = $project['moderation_comment'] ?? '';
        
        // If rejected but active, deactivate it
        if ($project['status'] === 'rejected' && $project['is_active'] == 1) {
            $newIsActive = 0;
            $needsUpdate = true;
            if (empty($comment)) {
                $comment = 'Деактивирован: отклонённый проект';
            }
        }
        
        // If has emoji, reject and deactivate
        if (preg_match('/[\x{1F300}-\x{1F9FF}]/u', $projectName)) {
            $newStatus = 'rejected';
            $newIsActive = 0;
            $needsUpdate = true;
            $comment = 'Удалено: недопустимое имя проекта (содержит эмодзи)';
        }
        
        // If test project and not already approved, reject it
        if ((stripos($projectName, 'ТЕСТ') !== false || stripos($projectName, 'TEST') !== false) &&
            $project['status'] === 'pending') {
            $newStatus = 'rejected';
            $newIsActive = 0;
            $needsUpdate = true;
            $comment = 'Удалено: тестовый проект';
        }
        
        if ($needsUpdate) {
            $updateQuery = "UPDATE project_moderation 
                           SET status = ?,
                               is_active = ?,
                               moderation_comment = ?
                           WHERE id = ?";
            
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sisi", $newStatus, $newIsActive, $comment, $projectId);
            
            if ($stmt->execute()) {
                echo "✅ Updated '{$projectName}' (ID: {$projectId})\n";
                echo "   New status: {$newStatus}, is_active: {$newIsActive}\n";
                echo "   Comment: {$comment}\n\n";
                $updatedCount++;
            } else {
                echo "❌ Failed to update '{$projectName}' (ID: {$projectId}): " . $stmt->error . "\n\n";
            }
        }
    }
    
    echo str_repeat('=', 70) . "\n";
    echo "✅ Cleanup complete!\n";
    echo "   Updated {$updatedCount} projects\n\n";
    
    // Verify
    echo "Verification: Checking for remaining problems...\n";
    $verifyResult = $conn->query($query);
    $remaining = [];
    while ($row = $verifyResult->fetch_assoc()) {
        if ($row['status'] === 'rejected' && $row['is_active'] == 1) {
            $remaining[] = $row['current_name'];
        }
    }
    
    if (empty($remaining)) {
        echo "✅ No rejected projects with is_active = 1 remaining!\n";
    } else {
        echo "⚠️  Still found " . count($remaining) . " issues:\n";
        foreach ($remaining as $name) {
            echo "   - {$name}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
