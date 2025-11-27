<?php
/**
 * Manual Input Diagnostic Script
 * 
 * This script will help identify why manual input isn't working by checking
 * potential routing and execution issues
 */

echo "=== Manual Input Diagnostic ===\n\n";

// 1. Check if GenericmessageCommand can be instantiated
echo "1. Command Instantiation Test:\n";

try {
    require_once 'CustomCommands/GenericmessageCommand.php';
    echo "   GenericmessageCommand loaded: ✅\n";
    
    // Try to create mock objects needed for the command
    $mockTelegram = null; // We can't instantiate this without real setup
    $mockUpdate = null;   // We can't instantiate this without real setup
    
    echo "   Command file syntax valid: ✅\n";
} catch (Exception $e) {
    echo "   Command loading failed: ❌ " . $e->getMessage() . "\n";
}

// 2. Check conversation handling
echo "\n2. Conversation System Check:\n";

$conversationFile = 'vendor/longman/telegram-bot/src/Conversation.php';
if (file_exists($conversationFile)) {
    echo "   Conversation class available: ✅\n";
} else {
    echo "   Conversation class available: ❌\n";
}

// Check for conversation database tables
$dbFile = 'data/telegram_bot.db';
if (file_exists($dbFile)) {
    echo "   Database file exists: ✅\n";
    
    try {
        $pdo = new PDO("sqlite:$dbFile");
        $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='conversation'");
        if ($result && $result->fetch()) {
            echo "   Conversation table exists: ✅\n";
        } else {
            echo "   Conversation table exists: ❌\n";
        }
    } catch (Exception $e) {
        echo "   Database check failed: ❌ " . $e->getMessage() . "\n";
    }
} else {
    echo "   Database file exists: ❌\n";
}

// 3. Check command registration and routing
echo "\n3. Command System Check:\n";

// Check if there are any command conflicts
$systemCommandsDir = 'CustomCommands';
$genericCommandFile = $systemCommandsDir . '/GenericmessageCommand.php';

if (file_exists($genericCommandFile)) {
    echo "   GenericmessageCommand file exists: ✅\n";
    
    $content = file_get_contents($genericCommandFile);
    
    // Check command properties
    if (strpos($content, "protected \$name = 'genericmessage'") !== false) {
        echo "   Command name properly set: ✅\n";
    } else {
        echo "   Command name properly set: ❌\n";
    }
    
    // Check if command extends SystemCommand
    if (strpos($content, 'extends SystemCommand') !== false) {
        echo "   Extends SystemCommand: ✅\n";
    } else {
        echo "   Extends SystemCommand: ❌\n";
    }
    
    // Check execute method
    if (strpos($content, 'public function execute()') !== false) {
        echo "   Execute method exists: ✅\n";
    } else {
        echo "   Execute method exists: ❌\n";
    }
} else {
    echo "   GenericmessageCommand file exists: ❌\n";
}

// 4. Check manual input specific logic
echo "\n4. Manual Input Logic Check:\n";

if (isset($content)) {
    // Check state manager initialization
    if (strpos($content, 'ManualInputStateManager') !== false) {
        echo "   StateManager integration: ✅\n";
    } else {
        echo "   StateManager integration: ❌\n";
    }
    
    // Check manual input handling in execute method
    $executeStart = strpos($content, 'public function execute()');
    $executeEnd = strpos($content, 'public function', $executeStart + 1);
    if ($executeEnd === false) $executeEnd = strlen($content);
    
    $executeMethod = substr($content, $executeStart, $executeEnd - $executeStart);
    
    if (strpos($executeMethod, 'isAwaitingGroupInput') !== false) {
        echo "   Group input check in execute: ✅\n";
    } else {
        echo "   Group input check in execute: ❌\n";
    }
    
    if (strpos($executeMethod, 'isAwaitingChatInput') !== false) {
        echo "   Chat input check in execute: ✅\n";
    } else {
        echo "   Chat input check in execute: ❌\n";
    }
    
    if (strpos($executeMethod, 'handleManualGroupInput') !== false) {
        echo "   Group input handler call: ✅\n";
    } else {
        echo "   Group input handler call: ❌\n";
    }
    
    if (strpos($executeMethod, 'handleManualChatInput') !== false) {
        echo "   Chat input handler call: ✅\n";
    } else {
        echo "   Chat input handler call: ❌\n";
    }
}

// 5. Test actual state checking execution
echo "\n5. Real State Checking Test:\n";

require_once 'CustomCommands/Services/ManualInputStateManager.php';
$stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();

$testUserId = 99888;
$testProject = "DIAGNOSTIC";

// Set state and immediately check
echo "   Setting test state... ";
$stateSet = $stateManager->setState($testUserId, 'group_id', $testProject);
echo $stateSet ? "✅" : "❌";
echo "\n";

echo "   Checking state immediately... ";
$stateFound = $stateManager->isAwaitingGroupInput($testUserId);
echo $stateFound ? "✅" : "❌";
echo "\n";

echo "   Getting project code... ";
$projectCode = $stateManager->getProjectCode($testUserId);
echo ($projectCode === $testProject) ? "✅" : "❌";
echo "\n";

$stateManager->clearState($testUserId);

// 6. Check for any blocking issues
echo "\n6. Potential Blocking Issues:\n";

// Check file permissions
$dataDir = __DIR__ . '/data';
$stateDir = $dataDir . '/manual_input_states';

echo "   Data directory writable: ";
echo is_writable($dataDir) ? "✅" : "❌";
echo "\n";

echo "   State directory writable: ";
echo is_writable($stateDir) ? "✅" : "❌";
echo "\n";

// Check if there are any error logs
$errorLogFile = 'php_errors.log';
if (file_exists($errorLogFile)) {
    echo "   Error log file exists: ⚠️ (check for errors)\n";
} else {
    echo "   No error log file: ✅\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "\n🔍 DIAGNOSIS SUMMARY:\n";
echo "If all checks above show ✅, then the manual input system\n";
echo "should be working. If it's still not responding, the issue\n";
echo "is likely in the Telegram webhook delivery or bot configuration.\n\n";

echo "💡 NEXT STEPS:\n";
echo "1. Check if webhook is properly configured\n";
echo "2. Verify bot token is correct\n";
echo "3. Check server logs for incoming webhooks\n";
echo "4. Test with a simple text message to see if GenericmessageCommand responds\n";