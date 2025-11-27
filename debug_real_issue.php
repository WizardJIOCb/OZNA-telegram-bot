<?php
/**
 * Debug script to understand the real duplication issue
 */

echo "=== DEBUGGING REAL DUPLICATION ISSUE ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    // Find user with OI834 project
    echo "1. SEARCHING FOR USER WITH OI834 PROJECT:\n\n";
    
    $allUsers = $databaseService->getAllUsers();
    $targetUser = null;
    
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (isset($controlProjects['ОИ834'])) {
            $targetUser = [
                'user_id' => $user['user_id'],
                'fio' => $userData['fio'] ?? 'Unknown',
                'functional_direction' => $userData['functional_direction'] ?? 'Unknown',
                'control_projects' => $controlProjects
            ];
            break;
        }
    }
    
    if (!$targetUser) {
        echo "   ❌ No user found with OI834 project\n";
        exit;
    }
    
    echo "   ✅ Found user: {$targetUser['fio']} (ID: {$targetUser['user_id']})\n";
    echo "   • Functional Direction: {$targetUser['functional_direction']}\n\n";
    
    echo "2. ANALYZING OI834 PROJECT SETTINGS:\n\n";
    
    $projectSettings = $targetUser['control_projects']['ОИ834'];
    echo "   Project OI834 settings:\n";
    echo "   • Group ID: " . ($projectSettings['group_id'] ?? 'NOT SET') . "\n";
    echo "   • Chat ID: " . ($projectSettings['chat_id'] ?? 'NOT SET') . "\n\n";
    
    echo "3. SIMULATING DUPLICATION CHECK:\n\n";
    
    // Get sender's functional direction parameters
    $functionalDirection = $targetUser['functional_direction'];
    
    $senderGroupAndThread = [];
    switch ($functionalDirection) {
        case 'Мобильная бригада':
            $senderGroupAndThread = ['group_id' => '-1002722176147', 'thread_id' => '2'];
            break;
        case 'Пусконаладка':
            $senderGroupAndThread = ['group_id' => '-1001400593888', 'thread_id' => '338'];
            break;
        case 'Шеф-монтаж':
            $senderGroupAndThread = ['group_id' => '-1001865074576', 'thread_id' => '14627'];
            break;
        default:
            $senderGroupAndThread = ['group_id' => '-1002722176147', 'thread_id' => '2'];
    }
    
    echo "   Sender (functional direction) parameters:\n";
    echo "   • FD: {$functionalDirection}\n";
    echo "   • Group ID: {$senderGroupAndThread['group_id']}\n";
    echo "   • Thread ID: {$senderGroupAndThread['thread_id']}\n\n";
    
    echo "4. DUPLICATION LOGIC CHECK:\n\n";
    
    // Check if group_id matches
    $projectGroupId = $projectSettings['group_id'] ?? '';
    $senderGroupId = $senderGroupAndThread['group_id'];
    
    echo "   Step 1: Group ID comparison\n";
    echo "   • Project group_id: '{$projectGroupId}'\n";
    echo "   • Sender group_id: '{$senderGroupId}'\n";
    echo "   • Match? " . ($projectGroupId === $senderGroupId ? 'YES' : 'NO') . "\n\n";
    
    if ($projectGroupId !== $senderGroupId) {
        echo "   ✅ RESULT: No duplication (different groups)\n";
    } else {
        echo "   ⚠️ Same group! Checking thread_id...\n\n";
        
        $projectThreadId = $projectSettings['chat_id'] ?? null;
        $senderThreadId = $senderGroupAndThread['thread_id'];
        
        echo "   Step 2: Thread ID comparison\n";
        echo "   • Project chat_id (thread_id): " . ($projectThreadId ?: 'EMPTY/NULL') . "\n";
        echo "   • Sender thread_id: '{$senderThreadId}'\n";
        
        if (empty($projectThreadId) || $projectThreadId === 'personal') {
            echo "   • Project thread_id is empty or 'personal'\n";
            echo "   • Message will go to main group feed (NO thread_id)\n";
            echo "   • Sender message goes to thread '{$senderThreadId}'\n";
            echo "   • Different destinations? YES\n";
            echo "   ✅ RESULT: No duplication (different destinations)\n";
        } else {
            echo "   • Thread ID match? " . ($projectThreadId === $senderThreadId ? 'YES' : 'NO') . "\n";
            if ($projectThreadId === $senderThreadId) {
                echo "   ❌ RESULT: DUPLICATION DETECTED!\n";
            } else {
                echo "   ✅ RESULT: No duplication (different threads)\n";
            }
        }
    }
    
    echo "\n5. THE REAL ISSUE:\n\n";
    
    if ($projectGroupId === $senderGroupId && (empty($projectSettings['chat_id']) || $projectSettings['chat_id'] === 'personal')) {
        echo "   🎯 IDENTIFIED THE PROBLEM:\n";
        echo "   • Project uses same group as functional direction\n";
        echo "   • But project has empty/personal chat_id\n";
        echo "   • This means:\n";
        echo "     - Normal report: group + thread_id = specific topic\n";
        echo "     - Controlled project: same group + NO thread_id = main feed\n";
        echo "   • Result: Message appears in group TWICE but in different places!\n\n";
        
        echo "   🔧 FIXING THE LOGIC:\n";
        echo "   Current logic incorrectly assumes this is NOT duplication\n";
        echo "   But it IS duplication - same group, different thread destinations\n";
        echo "   Users see it as 3 places because they see both the topic and main feed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== DEBUG COMPLETED ===\n";
?>