<?php
/**
 * Check bot configuration and message routing
 */

echo "=== CHECKING BOT MESSAGE ROUTING ===\n\n";

// Check if there are disabled or conflicting GenericmessageCommand files
echo "1. CHECKING FOR DISABLED COMMANDS:\n";
$commandsDir = __DIR__ . '/CustomCommands/';
$files = scandir($commandsDir);

foreach ($files as $file) {
    if (strpos($file, 'GenericmessageCommand') !== false) {
        echo "   Found: {$file}\n";
        
        if (strpos($file, '.disabled') !== false) {
            echo "     ⚠️ This file is DISABLED\n";
        } else if ($file === 'GenericmessageCommand.php') {
            echo "     ✓ This is the ACTIVE file\n";
        }
    }
}

echo "\n2. CHECKING CURRENT ACTIVE GENERICMESSAGECOMMAND:\n";
$activeFile = $commandsDir . 'GenericmessageCommand.php';
if (file_exists($activeFile)) {
    $content = file_get_contents($activeFile);
    
    // Check command name
    if (preg_match('/protected \$name = [\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        echo "   Command name: '{$matches[1]}'\n";
        if ($matches[1] === 'genericmessage') {
            echo "   ✓ Correct name for handling text messages\n";
        } else {
            echo "   ❌ Incorrect name - should be 'genericmessage'\n";
        }
    }
    
    // Check if it handles manual input
    $hasManualInput = strpos($content, 'stateManager->isAwaitingGroupInput') !== false;
    echo "   Manual input handling: " . ($hasManualInput ? "✓ Present" : "❌ Missing") . "\n";
    
    // Check execute method
    if (strpos($content, 'public function execute()') !== false) {
        echo "   ✓ Has execute method\n";
    } else {
        echo "   ❌ Missing execute method\n";
    }
}

echo "\n3. CHECKING TEXTMENUCOMMAND CONFIGURATION:\n";
$textmenuFile = $commandsDir . 'TextmenuCommand.php';
if (file_exists($textmenuFile)) {
    $content = file_get_contents($textmenuFile);
    
    if (preg_match('/protected \$name = [\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        echo "   TextmenuCommand name: '{$matches[1]}'\n";
        if ($matches[1] === 'genericmessage') {
            echo "   ❌ CONFLICT: TextmenuCommand is using 'genericmessage' name!\n";
            echo "   ❌ This will prevent GenericmessageCommand from receiving messages!\n";
        } else {
            echo "   ✓ No naming conflict\n";
        }
    }
}

echo "\n4. CHECKING FOR ROUTING CONFLICTS:\n";
// Check if GenericmessageCommand properly routes menu selections to TextmenuCommand
if (file_exists($activeFile)) {
    $content = file_get_contents($activeFile);
    
    if (strpos($content, 'TextmenuCommand') !== false) {
        echo "   ✓ GenericmessageCommand routes to TextmenuCommand\n";
    } else {
        echo "   ⚠️ No TextmenuCommand routing found\n";
    }
    
    // Check menu routing logic
    $menuItems = ['Табель', 'Помощь', 'Профиль', 'Проекты', 'Админка'];
    $hasMenuRouting = false;
    foreach ($menuItems as $item) {
        if (strpos($content, $item) !== false) {
            $hasMenuRouting = true;
            break;
        }
    }
    echo "   Menu routing logic: " . ($hasMenuRouting ? "✓ Present" : "❌ Missing") . "\n";
}

echo "\n5. TESTING SPECIFIC CASE - MANUAL INPUT:\n";
// Simulate the exact scenario
try {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/custom_autoload.php';
    
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    $userId = 230379668;
    $projectCode = 'ОИ834';
    
    // Set state as if user clicked "Enter group ID manually"
    $stateManager->setState($userId, 'group_id', $projectCode);
    echo "   State set for manual group input\n";
    
    // Check if the state would be detected
    $isWaiting = $stateManager->isAwaitingGroupInput($userId);
    echo "   State detection: " . ($isWaiting ? "✓ DETECTED" : "❌ NOT DETECTED") . "\n";
    
    if ($isWaiting) {
        // Test the validation that would happen
        $testGroupId = '-1002722176147';
        $isValid = preg_match('/^-100\d+$/', $testGroupId);
        echo "   Input validation: " . ($isValid ? "✓ VALID" : "❌ INVALID") . "\n";
        
        if ($isValid) {
            echo "   ✅ Everything should work - input would be processed\n";
        }
    }
    
    $stateManager->clearState($userId);
    
} catch (Exception $e) {
    echo "   ❌ Error in simulation: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNOSIS RESULTS ===\n";
echo "If TextmenuCommand has 'genericmessage' name:\n";
echo "  → This is the ROOT CAUSE of the problem\n";
echo "  → GenericmessageCommand never receives text messages\n";
echo "  → Manual input is ignored\n\n";

echo "If all configurations are correct:\n";
echo "  → The issue is on the server/webhook side\n";
echo "  → Bot may not be running the latest code\n";
echo "  → Webhook may not be properly configured\n";

?>