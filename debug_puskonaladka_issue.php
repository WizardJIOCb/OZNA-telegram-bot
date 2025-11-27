<?php
/**
 * Debug the specific Пусконаладка duplication issue
 */

echo "=== DEBUGGING ПУСКОНАЛАДКА DUPLICATION ISSUE ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "1. FINDING ПУСКОНАЛАДКА USERS WITH CONTROLLED PROJECTS:\n\n";
    
    $allUsers = $databaseService->getAllUsers();
    $puskUsers = [];
    
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        $functionalDirection = $userData['functional_direction'] ?? 'NOT SET';
        
        if ($functionalDirection === 'Пусконаладка') {
            $controlProjects = $userData['control_projects'] ?? [];
            if (!empty($controlProjects)) {
                $puskUsers[] = [
                    'user_id' => $user['user_id'],
                    'fio' => $userData['fio'] ?? 'Unknown',
                    'functional_direction' => $functionalDirection,
                    'control_projects' => $controlProjects
                ];
            }
        }
    }
    
    if (empty($puskUsers)) {
        echo "   ❌ No Пусконаладка users with controlled projects found\n";
        echo "   Let's check ALL users with controlled projects:\n\n";
        
        foreach ($allUsers as $user) {
            $userData = $user['data'];
            $controlProjects = $userData['control_projects'] ?? [];
            if (!empty($controlProjects)) {
                $functionalDirection = $userData['functional_direction'] ?? 'NOT SET';
                echo "   • {$userData['fio']} (ID: {$user['user_id']})\n";
                echo "     FD: {$functionalDirection}\n";
                foreach ($controlProjects as $project => $settings) {
                    echo "     Project: {$project} → Group: {$settings['group_id']}, Chat: " . ($settings['chat_id'] ?? 'EMPTY') . "\n";
                }
                echo "\n";
            }
        }
        exit;
    }
    
    echo "   Found " . count($puskUsers) . " Пусконаладка users with controlled projects:\n\n";
    
    foreach ($puskUsers as $user) {
        echo "   User: {$user['fio']} (ID: {$user['user_id']})\n";
        echo "   FD: {$user['functional_direction']}\n";
        echo "   Controlled projects:\n";
        
        foreach ($user['control_projects'] as $projectCode => $settings) {
            echo "   • {$projectCode}:\n";
            echo "     - Group ID: " . ($settings['group_id'] ?? 'NOT SET') . "\n";
            echo "     - Chat ID: " . ($settings['chat_id'] ?? 'NOT SET') . "\n";
            
            // Check if this configuration would cause the issue
            if ($settings['group_id'] === '-1002722176147') {
                echo "     ⚠️ ISSUE FOUND: Using Mobile Brigade group!\n";
                echo "     This explains why messages appear in Mobile Brigade group\n";
            }
        }
        echo "\n";
    }
    
    echo "2. ANALYZING THE DUPLICATION LOGIC:\n\n";
    
    // Simulate the sendControlledProjectCopies logic
    echo "   Пусконаладка FD settings:\n";
    echo "   • Group ID: -1001400593888\n";
    echo "   • Thread ID: 338\n\n";
    
    echo "   When a Пусконаладка user sends a report:\n";
    echo "   1. ✅ Normal send: goes to -1001400593888 (thread 338)\n";
    echo "   2. ❓ Controlled project copies: depends on configuration\n\n";
    
    foreach ($puskUsers as $user) {
        foreach ($user['control_projects'] as $projectCode => $settings) {
            echo "   For project {$projectCode} with supervisor {$user['fio']}:\n";
            
            $projectGroupId = $settings['group_id'] ?? '';
            $projectChatId = $settings['chat_id'] ?? '';
            
            if (empty($projectGroupId)) {
                echo "   • No group configured - no copy sent\n";
            } else {
                echo "   • Copy will be sent to group: {$projectGroupId}\n";
                
                if (empty($projectChatId) || $projectChatId === 'personal') {
                    echo "   • No thread_id - message goes to main group feed\n";
                } else {
                    echo "   • With thread_id: {$projectChatId}\n";
                }
                
                // Check if this would create duplication
                if ($projectGroupId === '-1001400593888') {
                    echo "   ⚠️ DUPLICATION: Same group as Пусконаладка FD\n";
                    if (empty($projectChatId) || $projectChatId === 'personal') {
                        echo "   ❌ Will appear in main feed + topic 338\n";
                    } elseif ($projectChatId === '338') {
                        echo "   ❌ Will appear twice in topic 338\n";
                    } else {
                        echo "   ✅ Different topics - no duplication\n";
                    }
                } elseif ($projectGroupId === '-1002722176147') {
                    echo "   ❌ WRONG GROUP: Mobile Brigade group!\n";
                    echo "   🐛 This is the bug - why Mobile Brigade group?\n";
                } else {
                    echo "   ✅ Different group - no duplication\n";
                }
            }
            echo "\n";
        }
    }
    
    echo "3. SUSPECTED ROOT CAUSE:\n\n";
    echo "   The issue might be:\n";
    echo "   • A supervisor has configured a controlled project with Mobile Brigade group ID\n";
    echo "   • When they receive copies, it goes to Mobile Brigade group instead of their own\n";
    echo "   • This suggests the controlled project configuration is incorrect\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== DEBUG COMPLETED ===\n";
?>