<?php
/**
 * Verify what's actually on the production server
 * Run this on the SERVER, not locally
 */

echo "=== VERIFICATION SCRIPT ===\n\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Script location: " . __FILE__ . "\n\n";

// Check if this is running on Windows or Linux
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
echo "Operating System: " . ($isWindows ? "Windows (LOCAL)" : "Linux (SERVER)") . "\n\n";

if ($isWindows) {
    echo "⚠️ WARNING: This script is running on Windows (your local machine)\n";
    echo "⚠️ You need to upload this script to the server and run it there!\n";
    echo "⚠️ Or access it via: https://ozna.ruc-nw.ru/verify_server_state.php\n\n";
}

// Check critical files
$files = [
    'CustomCommands/CallbackqueryCommand.php' => 'Conversation fix',
    'CustomCommands/Services/CallbackHandlerFactory.php' => 'proj_mod_ routing',
    'CustomCommands/Handlers/ProjectHandler.php' => 'Button location',
    'CustomCommands/Handlers/ProjectModerationHandler.php' => 'Moderation handler',
];

echo "=== FILE CHECKS ===\n\n";

foreach ($files as $file => $description) {
    $path = __DIR__ . '/' . $file;
    
    echo "File: {$file}\n";
    echo "Description: {$description}\n";
    
    if (!file_exists($path)) {
        echo "Status: ❌ FILE NOT FOUND\n\n";
        continue;
    }
    
    echo "Status: ✅ EXISTS\n";
    echo "Size: " . filesize($path) . " bytes\n";
    echo "Modified: " . date('Y-m-d H:i:s', filemtime($path)) . "\n";
    
    $content = file_get_contents($path);
    
    // Specific checks
    if ($file === 'CustomCommands/CallbackqueryCommand.php') {
        // Check for the FIXED Conversation initialization
        if (strpos($content, '$callback_query->getFrom()->getId()') !== false) {
            echo "Content: ✅ FIXED - Using userId for Conversation\n";
        } else {
            echo "Content: ❌ NOT FIXED - Still using chatId for Conversation\n";
            echo "Action: UPLOAD THIS FILE TO SERVER!\n";
        }
    }
    
    if ($file === 'CustomCommands/Services/CallbackHandlerFactory.php') {
        // Check for proj_mod_ handler
        if (strpos($content, "proj_mod_") !== false && strpos($content, "project moderation category") !== false) {
            echo "Content: ✅ FIXED - Has proj_mod_ handler\n";
        } else {
            echo "Content: ❌ NOT FIXED - Missing proj_mod_ handler\n";
            echo "Action: UPLOAD THIS FILE TO SERVER!\n";
        }
    }
    
    if ($file === 'CustomCommands/Handlers/ProjectHandler.php') {
        // Check if button was moved to My Projects menu
        $lines = file($path);
        $foundInMyProjects = false;
        $foundInReportProjects = false;
        
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'showMyProjectsMenu') !== false) {
                // Check next 20 lines for the button
                for ($i = $lineNum; $i < min($lineNum + 20, count($lines)); $i++) {
                    if (strpos($lines[$i], 'proj_mod_add_new') !== false) {
                        $foundInMyProjects = true;
                        break;
                    }
                }
            }
            
            if (strpos($line, 'showReportProjectsMenu') !== false) {
                // Check next 30 lines for the button
                for ($i = $lineNum; $i < min($lineNum + 30, count($lines)); $i++) {
                    if (strpos($lines[$i], 'proj_mod_add_new') !== false) {
                        $foundInReportProjects = true;
                        break;
                    }
                }
            }
        }
        
        if ($foundInMyProjects && !$foundInReportProjects) {
            echo "Content: ✅ FIXED - Button in My Projects menu\n";
        } elseif ($foundInReportProjects) {
            echo "Content: ❌ NOT FIXED - Button still in Report Projects menu\n";
            echo "Action: UPLOAD THIS FILE TO SERVER!\n";
        } else {
            echo "Content: ⚠️ UNCLEAR - Cannot find button\n";
        }
    }
    
    echo "\n";
}

echo "=== NEXT STEPS ===\n\n";

if ($isWindows) {
    echo "1. Upload this file to: ozna.ruc-nw.ru:/var/www/ozna/\n";
    echo "2. Access: https://ozna.ruc-nw.ru/verify_server_state.php\n";
    echo "3. Check which files need to be uploaded\n";
    echo "4. Upload missing/outdated files\n";
    echo "5. Clear OPcache: https://ozna.ruc-nw.ru/clear_cache.php\n";
} else {
    echo "You are on the SERVER!\n";
    echo "Check the results above:\n";
    echo "- Files with ❌ need to be uploaded from local machine\n";
    echo "- After uploading, clear OPcache\n";
}
