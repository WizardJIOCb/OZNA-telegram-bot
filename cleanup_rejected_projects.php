<?php
/**
 * Cleanup script to fix rejected projects that have is_active = 1
 * This fixes the issue where rejected test projects still appear in lists
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Cleanup Rejected Projects ===\n\n";

try {
    $databaseService = new DatabaseService();
    
    echo "1. Checking for rejected projects with is_active = 1...\n";
    
    $query = "SELECT id, current_name, status, is_active, added_by, added_at, moderation_comment
              FROM project_moderation 
              WHERE status = 'rejected' AND is_active = 1";
    
    $result = $databaseService->getConnection()->query($query);
    
    if (!$result) {
        echo "   ❌ Error querying database: " . $databaseService->getConnection()->error . "\n";
        exit(1);
    }
    
    $rejectedProjects = [];
    while ($row = $result->fetch_assoc()) {
        $rejectedProjects[] = $row;
    }
    
    if (empty($rejectedProjects)) {
        echo "   ✅ No rejected projects with is_active = 1 found!\n";
        echo "   Everything is clean.\n";
        exit(0);
    }
    
    echo "   ⚠️  Found " . count($rejectedProjects) . " rejected projects with is_active = 1:\n\n";
    
    foreach ($rejectedProjects as $project) {
        echo "   • {$project['current_name']} (ID: {$project['id']})\n";
        echo "     Status: {$project['status']}, is_active: {$project['is_active']}\n";
        echo "     Added: {$project['added_at']}\n";
        if ($project['moderation_comment']) {
            echo "     Comment: {$project['moderation_comment']}\n";
        }
        echo "\n";
    }
    
    echo "2. Fixing rejected projects (setting is_active = 0)...\n";
    
    $updateQuery = "UPDATE project_moderation 
                    SET is_active = 0
                    WHERE status = 'rejected' AND is_active = 1";
    
    $success = $databaseService->getConnection()->query($updateQuery);
    
    if (!$success) {
        echo "   ❌ Error updating database: " . $databaseService->getConnection()->error . "\n";
        exit(1);
    }
    
    $affectedRows = $databaseService->getConnection()->affected_rows;
    
    echo "   ✅ Successfully updated {$affectedRows} projects\n";
    echo "   All rejected projects now have is_active = 0\n\n";
    
    echo "3. Verifying fix...\n";
    
    $verifyResult = $databaseService->getConnection()->query($query);
    $remainingProjects = [];
    while ($row = $verifyResult->fetch_assoc()) {
        $remainingProjects[] = $row;
    }
    
    if (empty($remainingProjects)) {
        echo "   ✅ Verification successful! No more rejected projects with is_active = 1\n";
    } else {
        echo "   ⚠️  Warning: Still found " . count($remainingProjects) . " rejected projects with is_active = 1\n";
    }
    
    echo "\n✅ Cleanup complete!\n";
    echo "\nRejected projects will no longer appear in:\n";
    echo "  • Проекты для отчёта → Добавить проект\n";
    echo "  • Проекты на контроле → Добавить проект\n";
    echo "  • Админка → Управление проектами → Список проектов\n";
    echo "  • Админка → Управление проектами → Удалить проект\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
