<?php
/**
 * Comprehensive verification script to identify the manual input issue
 * Following safety protocols - read-only testing where possible
 */

echo "=== COMPREHENSIVE MANUAL INPUT VERIFICATION ===\n\n";

// Test 1: Check file structure and permissions
echo "1. CHECKING FILE STRUCTURE AND PERMISSIONS...\n";

$requiredFiles = [
    'CustomCommands/GenericmessageCommand.php',
    'CustomCommands/Handlers/ProjectHandler.php', 
    'CustomCommands/Services/ManualInputStateManager.php',
    'CustomCommands/Services/DatabaseService.php',
    'CustomCommands/Services/MessageService.php'
];

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "   ✓ {$file} exists\n";
        if (is_readable($fullPath)) {
            echo "     ✓ Readable\n";
        } else {
            echo "     ✗ Not readable\n";
        }
    } else {
        echo "   ✗ {$file} missing\n";
    }
}

// Test 2: Check state directory
echo "\n2. CHECKING STATE DIRECTORY...\n";
$stateDir = __DIR__ . '/data/manual_input_states';
if (is_dir($stateDir)) {
    echo "   ✓ State directory exists\n";
    echo "   ✓ Writable: " . (is_writable($stateDir) ? "YES" : "NO") . "\n";
    
    // Check for any existing state files
    $stateFiles = glob($stateDir . '/user_*.json');
    echo "   Current state files: " . count($stateFiles) . "\n";
    foreach ($stateFiles as $file) {
        echo "     - " . basename($file) . "\n";
    }
} else {
    echo "   ✗ State directory missing\n";
}

// Test 3: Check for common PHP issues
echo "\n3. CHECKING PHP CONFIGURATION...\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   Memory Limit: " . ini_get('memory_limit') . "\n";
echo "   Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "   Error Reporting: " . ini_get('error_reporting') . "\n";

// Check for required extensions
$requiredExtensions = ['mysqli', 'json', 'curl'];
foreach ($requiredExtensions as $ext) {
    echo "   Extension {$ext}: " . (extension_loaded($ext) ? "✓ Loaded" : "✗ Missing") . "\n";
}

// Test 4: Check GenericmessageCommand content
echo "\n4. CHECKING GENERICMESSAGECOMMAND INTEGRATION...\n";

$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
if (file_exists($genericFile)) {
    $content = file_get_contents($genericFile);
    
    // Check for file-based state detection
    $patterns = [
        'stateManager->isAwaitingGroupInput' => 'File-based group input detection',
        'stateManager->isAwaitingChatInput' => 'File-based chat input detection', 
        'handleManualGroupInputFromFile' => 'File-based group handler method',
        'handleManualChatInputFromFile' => 'File-based chat handler method',
        'ManualInputStateManager' => 'State manager import'
    ];
    
    foreach ($patterns as $pattern => $description) {
        if (strpos($content, $pattern) !== false) {
            echo "   ✓ {$description}: Found\n";
        } else {
            echo "   ✗ {$description}: Missing\n";
        }
    }
}

// Test 5: Check ProjectHandler content
echo "\n5. CHECKING PROJECTHANDLER INTEGRATION...\n";

$projectFile = __DIR__ . '/CustomCommands/Handlers/ProjectHandler.php';
if (file_exists($projectFile)) {
    $content = file_get_contents($projectFile);
    
    $patterns = [
        'ManualInputStateManager' => 'State manager import',
        'setState($userId, \'group_id\'' => 'Group state setting',
        'setState($userId, \'chat_id\'' => 'Chat state setting'
    ];
    
    foreach ($patterns as $pattern => $description) {
        if (strpos($content, $pattern) !== false) {
            echo "   ✓ {$description}: Found\n";
        } else {
            echo "   ✗ {$description}: Missing\n";
        }
    }
}

// Test 6: Test state management in isolation
echo "\n6. TESTING STATE MANAGEMENT IN ISOLATION...\n";

try {
    require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    $testUserId = 999999; // Use test user ID
    $testProject = 'TEST-ISOLATION';
    
    // Test setting state
    $result = $stateManager->setState($testUserId, 'group_id', $testProject);
    echo "   State setting: " . ($result ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Test detection
    $detected = $stateManager->isAwaitingGroupInput($testUserId);
    echo "   State detection: " . ($detected ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Test project retrieval
    $project = $stateManager->getProjectCode($testUserId);
    echo "   Project retrieval: " . ($project === $testProject ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Clean up
    $stateManager->clearState($testUserId);
    echo "   State cleanup: ✓ DONE\n";
    
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

// Test 7: Check for potential webhook/bot configuration issues
echo "\n7. CHECKING BOT CONFIGURATION FILES...\n";

$configFiles = [
    'config.php',
    'set.php',
    'webhook.php',
    'getUpdates.php'
];

foreach ($configFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "   ✓ {$file} exists\n";
        
        // Check if it contains bot token configuration
        $content = file_get_contents($fullPath);
        if (strpos($content, 'BOT_TOKEN') !== false || strpos($content, 'bot_token') !== false) {
            echo "     ✓ Contains bot token configuration\n";
        }
    } else {
        echo "   ? {$file} not found\n";
    }
}

echo "\n=== VERIFICATION COMPLETE ===\n\n";

echo "SUMMARY OF FINDINGS:\n";
echo "If all checks show ✓, then the manual input system is correctly implemented.\n\n";

echo "MOST LIKELY CAUSES:\n";
echo "1. Bot webhook not receiving messages from Telegram\n";
echo "2. GenericmessageCommand not being executed for text messages\n";
echo "3. Database connection issues during execution\n";
echo "4. Silent PHP errors preventing completion\n\n";

echo "RECOMMENDED ACTIONS:\n";
echo "1. Test if bot receives ANY messages at all\n";
echo "2. Check webhook configuration with Telegram\n";
echo "3. Look for PHP error logs during actual bot usage\n";
echo "4. Verify bot has proper permissions to send messages\n\n";

echo "To test if the bot is receiving messages:\n";
echo "1. Send any text message to the bot\n";
echo "2. Check if it appears in any log files\n";
echo "3. If no logs appear, the webhook needs attention\n";
?>