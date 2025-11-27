<?php
/**
 * Check which files exist on the server and their modification dates
 * This helps diagnose whether project moderation files have been uploaded
 */

echo "=== CHECKING SERVER FILES FOR PROJECT MODERATION ===\n\n";

$filesToCheck = [
    'CustomCommands/Handlers/ProjectModerationHandler.php' => 'NEW FILE - Project Moderation Handler',
    'CustomCommands/Services/CallbackHandlerFactory.php' => 'CRITICAL FIX - answerCallbackQuery',
    'CustomCommands/Handlers/ProjectHandler.php' => 'User button: Add New Project',
    'CustomCommands/Handlers/AdminHandler.php' => 'Admin button: Projects for Moderation',
    'CustomCommands/Services/DatabaseService.php' => '10 new moderation methods',
    'CustomCommands/CallbackqueryCommand.php' => 'Handler registration',
    'CustomCommands/GenericmessageCommand.php' => 'Text input handling',
];

foreach ($filesToCheck as $file => $description) {
    $fullPath = __DIR__ . '/' . $file;
    echo "File: {$file}\n";
    echo "Description: {$description}\n";
    
    if (file_exists($fullPath)) {
        echo "Status: ✅ EXISTS\n";
        echo "Size: " . filesize($fullPath) . " bytes\n";
        echo "Modified: " . date('Y-m-d H:i:s', filemtime($fullPath)) . "\n";
        
        // Check for specific markers
        $content = file_get_contents($fullPath);
        
        if ($file === 'CustomCommands/Handlers/ProjectModerationHandler.php') {
            if (strpos($content, 'class ProjectModerationHandler') !== false) {
                echo "Content: ✅ ProjectModerationHandler class found\n";
            } else {
                echo "Content: ❌ ProjectModerationHandler class NOT found\n";
            }
        }
        
        if ($file === 'CustomCommands/Handlers/ProjectHandler.php') {
            if (strpos($content, 'proj_mod_add_new') !== false) {
                echo "Content: ✅ 'Add new project' button found\n";
            } else {
                echo "Content: ❌ 'Add new project' button NOT found\n";
            }
        }
        
        if ($file === 'CustomCommands/Handlers/AdminHandler.php') {
            if (strpos($content, 'Проекты на согласование') !== false) {
                echo "Content: ✅ 'Projects for moderation' button found\n";
            } else {
                echo "Content: ❌ 'Projects for moderation' button NOT found\n";
            }
        }
        
        if ($file === 'CustomCommands/Services/CallbackHandlerFactory.php') {
            if (strpos($content, 'Request::answerCallbackQuery') !== false) {
                echo "Content: ✅ answerCallbackQuery fix found\n";
            } else {
                echo "Content: ❌ answerCallbackQuery fix NOT found\n";
            }
        }
        
        if ($file === 'CustomCommands/CallbackqueryCommand.php') {
            if (strpos($content, 'ProjectModerationHandler') !== false) {
                echo "Content: ✅ ProjectModerationHandler registered\n";
            } else {
                echo "Content: ❌ ProjectModerationHandler NOT registered\n";
            }
        }
        
    } else {
        echo "Status: ❌ FILE DOES NOT EXIST\n";
    }
    
    echo "\n";
}

echo "=== DATABASE CHECK ===\n\n";

// Check if database table exists
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';

try {
    $db = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    // Try to call one of the new methods
    $pendingCount = $db->getPendingProjectsCount();
    echo "✅ Database table 'project_moderation' exists\n";
    echo "✅ Method getPendingProjectsCount() works\n";
    echo "Pending projects count: {$pendingCount}\n";
    
} catch (\Exception $e) {
    echo "❌ Database check failed: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n\n";

echo "If all files show ✅ EXISTS and ✅ Content checks pass:\n";
echo "  → Files are on server, check OPcache and callback routing\n\n";

echo "If any files show ❌ FILE DOES NOT EXIST:\n";
echo "  → Those files need to be uploaded to the server\n\n";

echo "If files exist but content checks fail:\n";
echo "  → Files are outdated, need to be re-uploaded\n";
