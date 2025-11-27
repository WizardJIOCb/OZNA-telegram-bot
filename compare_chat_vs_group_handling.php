<?php
/**
 * Diagnostic script to compare Chat ID vs Group ID handling
 * Following safety protocols for non-programmer user
 */

echo "=== COMPARING CHAT ID vs GROUP ID HANDLING ===\n\n";

echo "ISSUE REPORTED:\n";
echo "✓ Chat ID input: Shows confirmation message\n";
echo "✗ Group ID input: No response at all\n\n";

echo "Let me check what's different between these two processes...\n\n";

try {
    // Load required services
    require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    
    $userId = 230379668;
    $projectCode = 'ОИ834';
    
    echo "1. TESTING CHAT ID FLOW (Working)...\n";
    
    // Test chat ID state setting
    $chatStateSet = $stateManager->setState($userId, 'chat_id', $projectCode);
    echo "   Chat state setting: " . ($chatStateSet ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Test chat ID detection
    $chatDetected = $stateManager->isAwaitingChatInput($userId);
    echo "   Chat state detection: " . ($chatDetected ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Clear chat state
    $stateManager->clearState($userId);
    
    echo "\n2. TESTING GROUP ID FLOW (Not working)...\n";
    
    // Test group ID state setting
    $groupStateSet = $stateManager->setState($userId, 'group_id', $projectCode);
    echo "   Group state setting: " . ($groupStateSet ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Test group ID detection
    $groupDetected = $stateManager->isAwaitingGroupInput($userId);
    echo "   Group state detection: " . ($groupDetected ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Clear group state
    $stateManager->clearState($userId);
    
    echo "\n3. CHECKING GENERICMESSAGECOMMAND HANDLERS...\n";
    
    // Check if both handler methods exist
    $genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
    if (file_exists($genericFile)) {
        $content = file_get_contents($genericFile);
        
        // Check for handler methods
        $hasGroupHandler = strpos($content, 'handleManualGroupInputFromFile') !== false;
        $hasChatHandler = strpos($content, 'handleManualChatInputFromFile') !== false;
        
        echo "   Group handler method: " . ($hasGroupHandler ? "✓ FOUND" : "✗ MISSING") . "\n";
        echo "   Chat handler method: " . ($hasChatHandler ? "✓ FOUND" : "✗ MISSING") . "\n";
        
        // Check for state detection calls
        $hasGroupDetection = strpos($content, '$this->stateManager->isAwaitingGroupInput($userId)') !== false;
        $hasChatDetection = strpos($content, '$this->stateManager->isAwaitingChatInput($userId)') !== false;
        
        echo "   Group detection call: " . ($hasGroupDetection ? "✓ FOUND" : "✗ MISSING") . "\n";
        echo "   Chat detection call: " . ($hasChatDetection ? "✓ FOUND" : "✗ MISSING") . "\n";
        
        // Check order of execution in the execute() method
        echo "\n4. CHECKING EXECUTION ORDER...\n";
        
        $executePos = strpos($content, 'public function execute()');
        if ($executePos !== false) {
            $executeSection = substr($content, $executePos, 5000);
            
            $groupPos = strpos($executeSection, 'isAwaitingGroupInput');
            $chatPos = strpos($executeSection, 'isAwaitingChatInput');
            
            if ($groupPos !== false && $chatPos !== false) {
                echo "   Group check position: " . $groupPos . "\n";
                echo "   Chat check position: " . $chatPos . "\n";
                echo "   Execution order: " . ($groupPos < $chatPos ? "✓ Group checked first" : "Chat checked first") . "\n";
            }
        }
    }
    
    echo "\n5. TESTING INPUT VALIDATION...\n";
    
    // Test group ID validation
    $testGroupId = '-1001234567890';
    $groupValid = preg_match('/^-100\d+$/', trim($testGroupId));
    echo "   Group ID validation: " . ($groupValid ? "✓ VALID" : "✗ INVALID") . "\n";
    echo "   Test Group ID: {$testGroupId}\n";
    
    // Test chat ID validation  
    $testChatId = '123456789';
    $chatValid = preg_match('/^-?\d+$/', trim($testChatId));
    echo "   Chat ID validation: " . ($chatValid ? "✓ VALID" : "✗ INVALID") . "\n";
    echo "   Test Chat ID: {$testChatId}\n";
    
    echo "\n6. SIMULATING COMPLETE FLOWS...\n";
    
    echo "   CHAT ID FLOW SIMULATION:\n";
    echo "   1. User clicks 'Ввести ID чата вручную'\n";
    echo "   2. ProjectHandler sets chat_id state\n";
    echo "   3. User types: 123456789\n";
    echo "   4. GenericmessageCommand detects chat state\n";
    echo "   5. Calls handleManualChatInputFromFile()\n";
    echo "   6. Shows: '✅ ID чата сохранён'\n";
    echo "   → This is WORKING for you\n\n";
    
    echo "   GROUP ID FLOW SIMULATION:\n";
    echo "   1. User clicks 'Ввести ID группы вручную'\n";
    echo "   2. ProjectHandler sets group_id state\n";
    echo "   3. User types: -1001234567890\n";
    echo "   4. GenericmessageCommand should detect group state\n";
    echo "   5. Should call handleManualGroupInputFromFile()\n";
    echo "   6. Should show: '✅ ID группы сохранён'\n";
    echo "   → This is NOT WORKING for you\n\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "=== DIAGNOSIS COMPLETE ===\n\n";

echo "POSSIBLE CAUSES FOR GROUP ID ISSUE:\n";
echo "1. Group ID handler method has an error that prevents execution\n";
echo "2. Group ID validation is failing silently\n";
echo "3. Group ID state detection is not working properly\n";
echo "4. There's a difference in how group vs chat states are processed\n\n";

echo "NEXT STEPS:\n";
echo "I need to examine the exact code in both handler methods\n";
echo "to find what's different between them.\n";
?>