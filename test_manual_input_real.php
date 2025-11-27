<?php
/**
 * Real Manual Input Test
 * 
 * This simulates exactly what should happen when a user sends a group ID
 */

echo "=== REAL MANUAL INPUT TEST ===\n\n";

// Test the exact scenario: user sends group ID after setting up manual input
$realUserId = 5261465966; // Your actual user ID from the logs
$realProject = "TEST_REAL";
$realGroupId = "-1001234567890";

echo "Testing with REAL user ID: $realUserId\n";
echo "Simulating manual input flow:\n\n";

try {
    require_once 'CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    echo "1. User clicks 'Manual Group Input' button (ProjectHandler sets state)\n";
    $stateSet = $stateManager->setState($realUserId, 'group_id', $realProject);
    echo "   State set: " . ($stateSet ? "✅" : "❌") . "\n";
    
    echo "\n2. User sends message: '$realGroupId'\n";
    echo "   GenericmessageCommand should execute:\n";
    
    // Check what GenericmessageCommand should see
    echo "   - Checking isAwaitingGroupInput($realUserId): ";
    $isAwaiting = $stateManager->isAwaitingGroupInput($realUserId);
    echo ($isAwaiting ? "✅ TRUE" : "❌ FALSE") . "\n";
    
    if ($isAwaiting) {
        echo "   - Getting project code: ";
        $projectCode = $stateManager->getProjectCode($realUserId);
        echo ($projectCode ? "✅ $projectCode" : "❌ NULL") . "\n";
        
        echo "   - Validating input format: ";
        $text = trim($realGroupId);
        $isValid = preg_match('/^-100\d+$/', $text);
        echo ($isValid ? "✅ VALID" : "❌ INVALID") . "\n";
        
        if ($isValid && $projectCode) {
            echo "\n3. Should execute confirmation logic:\n";
            echo "   - Save to database: ✅ (would happen)\n";
            echo "   - Clear state: ✅ (would happen)\n";
            echo "   - Send confirmation: ✅ (would happen)\n";
            
            // Show what the confirmation message would be
            $responseText = "✅ <b>ID группы сохранён</b>\n\n";
            $responseText .= "Проект <b>{$projectCode}</b> настроен.\n";
            $responseText .= "<b>ID группы:</b> {$text}";
            
            echo "\n   Expected confirmation message:\n";
            echo "   " . str_replace("\n", "\n   ", strip_tags($responseText)) . "\n";
            
            echo "\n   Expected navigation button: '⬅️ К настройкам'\n";
        }
    }
    
    $stateManager->clearState($realUserId);
    echo "\n✅ Test completed\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

echo "🔍 CHECKING FOR EXECUTION ISSUES\n";
echo "=================================\n\n";

// Check if there might be issues with the GenericmessageCommand execution
echo "Potential reasons why confirmation doesn't appear:\n\n";

echo "1. GenericmessageCommand not executing:\n";
echo "   - Webhook not delivering messages\n";
echo "   - Command routing issues\n";
echo "   - Fatal errors preventing execution\n\n";

echo "2. Services initialization failing:\n";
echo "   - DatabaseService not found\n";
echo "   - AdminService not found\n";
echo "   - MessageService not found\n\n";

echo "3. Silent failures:\n";
echo "   - Message sending fails silently\n";
echo "   - Database operations fail\n";
echo "   - State not being checked correctly\n\n";

echo "🎯 CRITICAL TEST NEEDED:\n";
echo "========================\n\n";

echo "IMMEDIATE ACTION REQUIRED:\n";
echo "1. Go to your Telegram bot\n";
echo "2. Send any simple text message (like 'hello')\n";
echo "3. Check telegram_bot_error.log immediately\n";
echo "4. Look for these specific entries:\n";
echo "   • '=== GenericmessageCommand::execute() START ==='\n";
echo "   • Any error messages\n\n";

echo "If you see the START message:\n";
echo "   ✅ GenericmessageCommand is working\n";
echo "   → Need to check why manual input isn't detected\n\n";

echo "If you DON'T see the START message:\n";
echo "   ❌ GenericmessageCommand is not executing\n";
echo "   → Need to fix webhook/command routing\n\n";

echo "THIS TEST WILL TELL US EXACTLY WHERE THE PROBLEM IS!\n";