<?php
/**
 * Force delete specific problem projects
 */

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

echo "=== Force Delete Problem Projects ===\n\n";

$projectsToDelete = [
    'ТЕСТ1234',
    '📁 Проекты',
    'TEST922',
    'TEST1234',
    'OI743.03',
    'ОИ743.03'
];

try {
    $databaseService = new DatabaseService();
    
    echo "Projects to delete:\n";
    foreach ($projectsToDelete as $project) {
        echo "  - '{$project}'\n";
    }
    echo "\n";
    
    $totalDeleted = 0;
    
    foreach ($projectsToDelete as $projectName) {
        echo "Processing '{$projectName}'...\n";
        
        // Use the public deleteProjectFromDatabase method
        $result = $databaseService->deleteProjectFromDatabase($projectName);
        
        if ($result) {
            echo "  ✅ Successfully deleted\n\n";
            $totalDeleted++;
        } else {
            echo "  ℹ️  Not found or already deleted\n\n";
        }
    }
    
    echo str_repeat('=', 60) . "\n";
    echo "✅ Complete! Deleted {$totalDeleted} projects\n\n";
    
    // Show remaining active projects
    echo "Getting list of remaining active projects...\n";
    $activeProjects = $databaseService->getAllActiveAndPendingProjects();
    
    echo "\nActive projects remaining: " . count($activeProjects) . "\n";
    echo str_repeat('-', 60) . "\n";
    
    foreach ($activeProjects as $project) {
        echo "  • {$project}\n";
    }
    
    echo "\n✅ Cleanup complete!\n";
    echo "\nThe deleted projects will no longer appear in:\n";
    echo "  • Админка → Управление проектами → Список проектов\n";
    echo "  • Админка → Управление проектами → Удалить проект\n";
    echo "  • Проекты для отчёта → Добавить проект\n";
    echo "  • Проекты на контроле → Добавить проект\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
