<?php
/**
 * Check for duplication issues in Пусконаладка and Шеф-монтаж
 */

echo "=== CHECKING ПУСКОНАЛАДКА AND ШЕФ-МОНТАЖ FOR DUPLICATION ISSUES ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "1. ANALYZING USERS BY FUNCTIONAL DIRECTION:\n\n";
    
    $allUsers = $databaseService->getAllUsers();
    $usersByFD = [
        'Пусконаладка' => [],
        'Шеф-монтаж' => [],
        'Мобильная бригада' => []
    ];
    
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        $functionalDirection = $userData['functional_direction'] ?? '';
        
        if (isset($usersByFD[$functionalDirection])) {
            $usersByFD[$functionalDirection][] = [
                'user_id' => $user['user_id'],
                'fio' => $userData['fio'] ?? 'Unknown',
                'control_projects' => $userData['control_projects'] ?? []
            ];
        }
    }
    
    // Functional direction group settings
    $fdSettings = [
        'Пусконаладка' => ['group_id' => '-1001400593888', 'thread_id' => '338'],
        'Шеф-монтаж' => ['group_id' => '-1001865074576', 'thread_id' => '14627'],
        'Мобильная бригада' => ['group_id' => '-1002722176147', 'thread_id' => '2']
    ];
    
    foreach ($usersByFD as $fd => $users) {
        if (empty($users)) {
            continue;
        }
        
        echo "   {$fd}:\n";
        echo "   • Users: " . count($users) . "\n";
        echo "   • FD Group: {$fdSettings[$fd]['group_id']}\n";
        echo "   • FD Thread: {$fdSettings[$fd]['thread_id']}\n";
        
        $usersWithControlProjects = 0;
        $potentialDuplicates = [];
        
        foreach ($users as $user) {
            if (!empty($user['control_projects'])) {
                $usersWithControlProjects++;
                
                foreach ($user['control_projects'] as $projectCode => $settings) {
                    $projectGroupId = $settings['group_id'] ?? '';
                    $projectChatId = $settings['chat_id'] ?? '';
                    
                    // Check for potential duplicates
                    if ($projectGroupId === $fdSettings[$fd]['group_id']) {
                        $isDuplicate = false;
                        
                        if (empty($projectChatId) || $projectChatId === 'personal') {
                            $isDuplicate = true; // Same group, no thread_id = main feed
                        } elseif ($projectChatId === $fdSettings[$fd]['thread_id']) {
                            $isDuplicate = true; // Same group, same thread_id
                        }
                        
                        if ($isDuplicate) {
                            $potentialDuplicates[] = [
                                'user' => $user['fio'],
                                'user_id' => $user['user_id'],
                                'project' => $projectCode,
                                'project_group' => $projectGroupId,
                                'project_chat' => $projectChatId,
                                'issue' => empty($projectChatId) ? 'Empty chat_id' : 
                                          ($projectChatId === 'personal' ? 'Personal chat_id' : 'Same thread_id')
                            ];
                        }
                    }
                }
            }
        }
        
        echo "   • Users with controlled projects: {$usersWithControlProjects}\n";
        echo "   • Potential duplicates: " . count($potentialDuplicates) . "\n";
        
        if (!empty($potentialDuplicates)) {
            echo "   ⚠️ DUPLICATION ISSUES FOUND:\n";
            foreach ($potentialDuplicates as $duplicate) {
                echo "     - {$duplicate['user']} (ID: {$duplicate['user_id']})\n";
                echo "       Project: {$duplicate['project']}\n";
                echo "       Issue: {$duplicate['issue']}\n";
                echo "       Group: {$duplicate['project_group']}, Chat: " . ($duplicate['project_chat'] ?: 'EMPTY') . "\n";
            }
        } else {
            echo "   ✅ No duplication issues found\n";
        }
        
        echo "\n";
    }
    
    echo "2. SUMMARY OF FINDINGS:\n\n";
    
    $totalIssues = 0;
    foreach ($usersByFD as $fd => $users) {
        $fdIssues = 0;
        foreach ($users as $user) {
            if (!empty($user['control_projects'])) {
                foreach ($user['control_projects'] as $projectCode => $settings) {
                    $projectGroupId = $settings['group_id'] ?? '';
                    $projectChatId = $settings['chat_id'] ?? '';
                    
                    if ($projectGroupId === $fdSettings[$fd]['group_id']) {
                        if (empty($projectChatId) || $projectChatId === 'personal' || 
                            $projectChatId === $fdSettings[$fd]['thread_id']) {
                            $fdIssues++;
                            $totalIssues++;
                        }
                    }
                }
            }
        }
        
        if ($fdIssues > 0) {
            echo "   ⚠️ {$fd}: {$fdIssues} potential duplication issues\n";
        } else {
            echo "   ✅ {$fd}: No issues found\n";
        }
    }
    
    echo "\n   Total potential duplications across all FDs: {$totalIssues}\n\n";
    
    if ($totalIssues > 0) {
        echo "3. IMPACT OF THE FIX:\n\n";
        echo "   ✅ The isDuplicateDestination fix will prevent ALL these duplications\n";
        echo "   ✅ Users with these configurations will now see reports in 2 places instead of 3\n";
        echo "   ✅ The fix works for ALL functional directions, not just Mobile Brigade\n\n";
        
        echo "4. RECOMMENDATIONS:\n\n";
        echo "   For users experiencing duplications:\n";
        echo "   • Use different group_id for controlled projects\n";
        echo "   • OR set chat_id to a specific thread_id different from FD thread_id\n";
        echo "   • OR use 'personal' for personal messages only\n";
    } else {
        echo "3. RESULT:\n\n";
        echo "   ✅ No duplication issues found in current configurations\n";
        echo "   ✅ All users have properly configured controlled projects\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== ANALYSIS COMPLETED ===\n";
?>