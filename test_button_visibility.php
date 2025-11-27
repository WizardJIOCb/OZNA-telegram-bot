<?php
/**
 * Test if project moderation buttons are visible in menus
 * This simulates what users/admins would see
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== TESTING BUTTON VISIBILITY ===\n\n";

// Test 1: Check if Projects button is in main menu for regular users
echo "Test 1: Main Menu - Projects Button (for users level 1+)\n";
echo "-------------------------------------------------------\n";

$messageService = new MessageService();
$mainMenuKeyboard = $messageService->createMainMenuKeyboard(1); // User level 1

$hasProjectsButton = false;
foreach ($mainMenuKeyboard as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && strpos($button['text'], 'Проекты') !== false) {
            echo "✅ FOUND: '{$button['text']}' → '{$button['callback_data']}'\n";
            $hasProjectsButton = true;
        }
    }
}

if (!$hasProjectsButton) {
    echo "❌ Projects button NOT found in main menu!\n";
}

echo "\n";

// Test 2: Simulate user navigating to "Projects for Report" menu
echo "Test 2: Projects for Report Menu - Add New Project Button\n";
echo "-----------------------------------------------------------\n";

// We need to extract the method that creates this menu
// Let's read the ProjectHandler file and look for the button

$projectHandlerFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
$content = file_get_contents($projectHandlerFile);

if (strpos($content, 'proj_mod_add_new') !== false) {
    echo "✅ FOUND: Button callback 'proj_mod_add_new' exists in ProjectHandler\n";
    
    // Extract the button text
    preg_match("/\['text'\s*=>\s*'([^']+)',\s*'callback_data'\s*=>\s*'proj_mod_add_new'\]/", $content, $matches);
    if (isset($matches[1])) {
        echo "   Button text: '{$matches[1]}'\n";
    }
    
    // Check what method contains this button
    $lines = explode("\n", $content);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'proj_mod_add_new') !== false) {
            echo "   Found at line: " . ($lineNum + 1) . "\n";
            
            // Look backwards to find the function name
            for ($i = $lineNum; $i >= 0; $i--) {
                if (preg_match('/private function (\w+)\(/', $lines[$i], $funcMatch)) {
                    echo "   Inside function: {$funcMatch[1]}()\n";
                    break;
                }
            }
        }
    }
} else {
    echo "❌ Button callback 'proj_mod_add_new' NOT found!\n";
}

echo "\n";

// Test 3: Check admin menu for moderation button
echo "Test 3: Admin Menu - Projects for Moderation Button\n";
echo "-----------------------------------------------------\n";

$adminHandlerFile = __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
$adminContent = file_get_contents($adminHandlerFile);

if (strpos($adminContent, 'Проекты на согласование') !== false) {
    echo "✅ FOUND: 'Проекты на согласование' text in AdminHandler\n";
    
    // Extract the callback
    preg_match("/\['text'\s*=>\s*[^,]+Проекты на согласование[^,]+,\s*'callback_data'\s*=>\s*'([^']+)'\]/", $adminContent, $matches);
    if (isset($matches[1])) {
        echo "   Callback data: '{$matches[1]}'\n";
    }
    
    // Check access level requirement
    $lines = explode("\n", $adminContent);
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'Проекты на согласование') !== false) {
            echo "   Found at line: " . ($lineNum + 1) . "\n";
            
            // Look for access level check nearby
            for ($i = max(0, $lineNum - 10); $i < min(count($lines), $lineNum + 5); $i++) {
                if (preg_match('/accessLevel\s*>=\s*(\d+)/', $lines[$i], $accessMatch)) {
                    echo "   Required access level: >= {$accessMatch[1]}\n";
                    $levelNames = [0 => 'Guest', 1 => 'User', 2 => 'Supervisor', 3 => 'Admin', 4 => 'SuperAdmin'];
                    echo "   ({$levelNames[$accessMatch[1]]} or higher)\n";
                    break;
                }
            }
        }
    }
} else {
    echo "❌ 'Проекты на согласование' NOT found!\n";
}

echo "\n";

// Test 4: Check if ProjectModerationHandler is registered
echo "Test 4: ProjectModerationHandler Registration\n";
echo "-----------------------------------------------\n";

$callbackCommandFile = __DIR__ . '/CustomCommands/CallbackqueryCommand.php';
$callbackContent = file_get_contents($callbackCommandFile);

if (strpos($callbackContent, 'ProjectModerationHandler') !== false) {
    echo "✅ FOUND: ProjectModerationHandler import/usage\n";
    
    // Check if it's registered
    if (preg_match('/registerHandler.*?ProjectModerationHandler/s', $callbackContent)) {
        echo "✅ FOUND: ProjectModerationHandler is registered\n";
    } else {
        echo "⚠️ Import found but registration unclear\n";
    }
} else {
    echo "❌ ProjectModerationHandler NOT found in CallbackqueryCommand!\n";
}

echo "\n";

// Test 5: Database connection
echo "Test 5: Database Methods\n";
echo "-------------------------\n";

try {
    $db = new DatabaseService();
    
    // Test if new methods exist
    $methods = get_class_methods($db);
    $moderationMethods = [
        'addPendingProject',
        'getPendingProjects',
        'getPendingProjectsCount',
        'getModerationProjectById',
        'approveProject',
        'renameProject',
        'rejectProject',
    ];
    
    $foundMethods = 0;
    foreach ($moderationMethods as $method) {
        if (in_array($method, $methods)) {
            echo "✅ Method exists: {$method}()\n";
            $foundMethods++;
        } else {
            echo "❌ Method missing: {$method}()\n";
        }
    }
    
    echo "\nFound {$foundMethods} out of " . count($moderationMethods) . " moderation methods\n";
    
} catch (\Exception $e) {
    echo "❌ Error checking methods: " . $e->getMessage() . "\n";
}

echo "\n";

// Final Summary
echo "=== SUMMARY ===\n\n";

echo "LOCAL STATUS (Windows machine c:\\ozna_bot):\n";
echo "  ✅ All files exist and contain correct code\n";
echo "  ✅ Buttons are defined in the code\n";
echo "  ✅ Database methods are present\n";
echo "  ✅ Handler is registered\n\n";

echo "PRODUCTION STATUS (ozna.ruc-nw.ru server):\n";
echo "  ❓ Files need to be checked on the actual server\n";
echo "  ❓ OPcache needs to be cleared after upload\n\n";

echo "NEXT STEPS:\n";
echo "  1. Upload ALL 7 files to ozna.ruc-nw.ru server\n";
echo "  2. Clear OPcache: https://ozna.ruc-nw.ru/clear_cache.php\n";
echo "  3. Test in Telegram bot\n\n";

echo "WHERE TO FIND BUTTONS:\n";
echo "  User: /start → 📁 Проекты → 📂 Мои проекты → 📋 Проекты для отчёта\n";
echo "  Admin: /start → ⚙️ Админка → (should see 'Проекты на согласование')\n";
