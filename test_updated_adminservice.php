<?php
// Test the updated AdminService

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "TESTING UPDATED ADMINSERVICE\n";
echo "============================\n\n";

try {
    echo "1. Creating AdminService instance...\n";
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "2. Getting all projects from AdminService...\n";
    $projects = $adminService->getAllProjects();
    
    echo "   Total projects loaded: " . count($projects) . "\n";
    echo "   Projects:\n";
    foreach ($projects as $project) {
        echo "   - {$project}\n";
    }
    
    echo "\n3. Checking if database projects are included...\n";
    $dbProjects = ['ПКПО-24', 'ПНР-001', 'ШМР-001'];
    $allIncluded = true;
    foreach ($dbProjects as $dbProject) {
        $included = in_array($dbProject, $projects);
        echo "   {$dbProject}: " . ($included ? "✓ Included" : "✗ Missing") . "\n";
        if (!$included) {
            $allIncluded = false;
        }
    }
    
    if ($allIncluded) {
        echo "\n✅ SUCCESS: All database projects are now available!\n";
    } else {
        echo "\n❌ ISSUE: Some database projects are still missing\n";
    }
    
    echo "\n4. Checking if file projects are still included...\n";
    $fileProjects = ['ОИ834', 'БКНС115', 'ОИ860'];
    $fileIncluded = true;
    foreach ($fileProjects as $fileProject) {
        $included = in_array($fileProject, $projects);
        echo "   {$fileProject}: " . ($included ? "✓ Included" : "✗ Missing") . "\n";
        if (!$included) {
            $fileIncluded = false;
        }
    }
    
    if ($fileIncluded) {
        echo "\n✅ SUCCESS: File projects are still available!\n";
    } else {
        echo "\n❌ ISSUE: Some file projects are missing\n";
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    echo "The AdminService now loads projects from both:\n";
    echo "- Database table 'project' (server projects)\n";  
    echo "- Local file 'data/projects.json' (previously stored projects)\n";
    echo "Total unique projects available: " . count($projects) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>