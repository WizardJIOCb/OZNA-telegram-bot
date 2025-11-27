<?php
/**
 * Diagnostic script to compare Chat ID vs Group ID input handling
 * Following safety protocols for non-programmer user
 */

echo "=== COMPARING CHAT ID vs GROUP ID INPUT HANDLING ===\n\n";

echo "ISSUE REPORTED:\n";
echo "✓ Chat ID input: Shows confirmation 'ID чата настроен'\n";
echo "✗ Group ID input: Nothing happens\n\n";

echo "Let me test both input handlers to find the difference...\n\n";

try {
    require_once __DIR__ . '/CustomCommands/Services/ManualInputStateManager.php';
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    $userId = 230379668;
    $projectCode = 'ОИ834';
    $testGroupId = '-1001234567890';
    $testChatId = '123456789';
    
    echo "1. TESTING GROUP ID INPUT FLOW...\n";
    
    // Test Group ID state setting
    $groupStateSet = $stateManager->setState($userId, 'group_id', $projectCode);
    echo "   Group state set: " . ($groupStateSet ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Test Group ID detection
    $groupDetected = $stateManager->isAwaitingGroupInput($userId);
    echo "   Group state detected: " . ($groupDetected ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    if ($groupDetected) {
        // Test Group ID validation
        $groupValid = preg_match('/^-100\d+$/', trim($testGroupId));
        echo "   Group ID validation: " . ($groupValid ? "✓ VALID" : "✗ INVALID") . "\n";
        
        // Test project code retrieval
        $groupProject = $stateManager->getProjectCode($userId);
        echo "   Group project code: " . ($groupProject ? "✓ {$groupProject}" : "✗ MISSING") . "\n";
    }
    
    $stateManager->clearState($userId);
    
    echo "\n2. TESTING CHAT ID INPUT FLOW...\n";
    
    // Test Chat ID state setting
    $chatStateSet = $stateManager->setState($userId, 'chat_id', $projectCode);
    echo "   Chat state set: " . ($chatStateSet ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    // Test Chat ID detection
    $chatDetected = $stateManager->isAwaitingChatInput($userId);
    echo "   Chat state detected: " . ($chatDetected ? "✓ SUCCESS" : "✗ FAILED") . "\n";
    
    if ($chatDetected) {
        // Test Chat ID validation
        $chatValid = preg_match('/^-?\d+$/', trim($testChatId));
        echo "   Chat ID validation: " . ($chatValid ? "✓ VALID" : "✗ INVALID") . "\n";
        
        // Test project code retrieval
        $chatProject = $stateManager->getProjectCode($userId);
        echo "   Chat project code: " . ($chatProject ? "✓ {$chatProject}" : "✗ MISSING") . "\n";
    }
    
    $stateManager->clearState($userId);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n3. CHECKING GENERICMESSAGECOMMAND HANDLERS...\n";

$genericFile = __DIR__ . '/CustomCommands/GenericmessageCommand.php';
if (file_exists($genericFile)) {
    $content = file_get_contents($genericFile);
    
    // Check if both handler methods exist
    $hasGroupHandler = strpos($content, 'handleManualGroupInputFromFile') !== false;
    $hasChatHandler = strpos($content, 'handleManualChatInputFromFile') !== false;
    
    echo "   Group handler method: " . ($hasGroupHandler ? "✓ EXISTS" : "✗ MISSING") . "\n";
    echo "   Chat handler method: " . ($hasChatHandler ? "✓ EXISTS" : "✗ MISSING") . "\n";
    
    // Check execution order in execute() method
    $executePos = strpos($content, 'public function execute()');
    if ($executePos !== false) {
        $executeSection = substr($content, $executePos, 5000);
        
        $groupCheckPos = strpos($executeSection, '$this->stateManager->isAwaitingGroupInput($userId)');
        $chatCheckPos = strpos($executeSection, '$this->stateManager->isAwaitingChatInput($userId)');
        
        echo "   Group input check position: " . ($groupCheckPos !== false ? "✓ FOUND at {$groupCheckPos}" : "✗ NOT FOUND") . "\n";
        echo "   Chat input check position: " . ($chatCheckPos !== false ? "✓ FOUND at {$chatCheckPos}" : "✗ NOT FOUND") . "\n";
        
        if ($groupCheckPos !== false && $chatCheckPos !== false) {
            echo "   Execution order: " . ($groupCheckPos < $chatCheckPos ? "Group first" : "Chat first") . "\n";
        }
    }
}

echo "\n4. SIMULATING BOTH INPUT TYPES...\n";

echo "   Group ID input simulation:\n";
echo "   User enters: {$testGroupId}\n";
echo "   Expected response: '✅ ID группы сохранён'\n";
echo "   Expected navigation: 'К настройкам проекта'\n\n";

echo "   Chat ID input simulation:\n";
echo "   User enters: {$testChatId}\n";
echo "   Expected response: '✅ ID чата сохранён'\n";
echo "   Expected navigation: 'Вернуться к настройкам проекта'\n\n";

echo "=== ANALYSIS RESULTS ===\n\n";

echo "POSSIBLE CAUSES FOR GROUP ID NOT WORKING:\n";
echo "1. Group ID handler method has an error or exception\n";
echo "2. Group ID validation is failing\n";
echo "3. Group ID state detection is not working\n";
echo "4. Database save is failing for group ID\n";
echo "5. Response message is not being sent\n\n";

echo "NEXT STEPS:\n";
echo "1. Check if cURL extension is enabled (critical for message sending)\n";
echo "2. Look for PHP errors in the error logs\n";
echo "3. Test Group ID handler in isolation\n";
echo "4. Compare the exact code between Group and Chat handlers\n\n";

echo "Since Chat ID confirmation works but Group ID doesn't,\n";
echo "the issue is likely in the Group ID handler implementation\n";
echo "or the Group ID validation logic.\n";

?>