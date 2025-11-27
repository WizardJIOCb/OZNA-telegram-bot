<?php
/**
 * Find who has the project on control that's causing Mobile Brigade messages
 */

echo "=== FINDING THE SOURCE OF MOBILE BRIGADE DUPLICATION ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    
    $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
    
    echo "1. SEARCHING FOR USERS WITH MOBILE BRIGADE GROUP IN CONTROLLED PROJECTS:\n\n";
    
    $allUsers = $databaseService->getAllUsers();
    $mobileBrigadeGroupId = '-1002722176147';
    $suspiciousUsers = [];
    
    foreach ($allUsers as $user) {
        $userData = $user['data'];
        $controlProjects = $userData['control_projects'] ?? [];
        
        foreach ($controlProjects as $projectCode => $settings) {
            $projectGroupId = $settings['group_id'] ?? '';
            
            if ($projectGroupId === $mobileBrigadeGroupId) {
                $suspiciousUsers[] = [
                    'user_id' => $user['user_id'],
                    'fio' => $userData['fio'] ?? 'Unknown',
                    'functional_direction' => $userData['functional_direction'] ?? 'NOT SET',
                    'project' => $projectCode,
                    'settings' => $settings
                ];
            }
        }
    }
    
    if (empty($suspiciousUsers)) {
        echo "   ❌ No users found with Mobile Brigade group in controlled projects\n";
        echo "   This means the issue is NOT in controlled project settings\n\n";
        
        echo "2. CHECKING FOR OTHER POSSIBLE CAUSES:\n\n";
        echo "   Let's check if there are any users with empty/null functional direction:\n\n";
        
        $usersWithEmptyFD = [];
        foreach ($allUsers as $user) {
            $userData = $user['data'];
            $functionalDirection = $userData['functional_direction'] ?? null;
            
            if (empty($functionalDirection)) {
                $controlProjects = $userData['control_projects'] ?? [];
                if (!empty($controlProjects)) {
                    $usersWithEmptyFD[] = [
                        'user_id' => $user['user_id'],
                        'fio' => $userData['fio'] ?? 'Unknown',
                        'control_projects' => $controlProjects
                    ];
                }
            }
        }
        
        if (!empty($usersWithEmptyFD)) {
            echo "   ⚠️ Found users with empty functional direction:\n";
            foreach ($usersWithEmptyFD as $user) {
                echo "   • {$user['fio']} (ID: {$user['user_id']})\n";
                echo "     This user defaults to 'Мобильная бригада' in the code!\n";
                foreach ($user['control_projects'] as $project => $settings) {
                    echo "     Project: {$project} → Group: " . ($settings['group_id'] ?? 'EMPTY') . "\n";
                }
                echo "\n";
            }
        }
        
    } else {
        echo "   ⚠️ Found " . count($suspiciousUsers) . " users with Mobile Brigade group in controlled projects:\n\n";
        
        foreach ($suspiciousUsers as $user) {
            echo "   • {$user['fio']} (ID: {$user['user_id']})\n";
            echo "     Functional Direction: {$user['functional_direction']}\n";
            echo "     Project: {$user['project']}\n";
            echo "     Group ID: {$user['settings']['group_id']}\n";
            echo "     Chat ID: " . ($user['settings']['chat_id'] ?? 'EMPTY') . "\n";
            
            // This explains the issue!
            if ($user['functional_direction'] !== 'Мобильная бригада') {
                echo "     🐛 BUG: Non-Mobile Brigade user using Mobile Brigade group!\n";
                echo "     When they receive controlled project copies, it goes to Mobile Brigade group\n";
            } else {
                echo "     ✅ This is expected for Mobile Brigade users\n";
            }
            echo "\n";
        }
    }
    
    echo "3. TESTING WHICH PROJECT YOU USED:\n\n";
    echo "   Which project did you test with? Let's check who has it on control:\n\n";
    
    $commonProjects = ['ОИ834', 'ОИ784', 'ОИ785', 'ОИ786', 'БКНС115', 'БКНС120', 'БКНС125'];
    
    foreach ($commonProjects as $testProject) {
        echo "   Project {$testProject} is controlled by:\n";
        $found = false;
        
        foreach ($allUsers as $user) {
            $userData = $user['data'];
            $controlProjects = $userData['control_projects'] ?? [];
            
            if (isset($controlProjects[$testProject])) {
                $found = true;
                $settings = $controlProjects[$testProject];
                echo "   • {$userData['fio']} (FD: " . ($userData['functional_direction'] ?? 'NOT SET') . ")\n";
                echo "     Group: " . ($settings['group_id'] ?? 'EMPTY') . ", Chat: " . ($settings['chat_id'] ?? 'EMPTY') . "\n";
                
                if (($settings['group_id'] ?? '') === $mobileBrigadeGroupId) {
                    echo "     🎯 THIS IS THE CAUSE! Mobile Brigade group configured here!\n";
                }
            }
        }
        
        if (!$found) {
            echo "   • No one has this project on control\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== INVESTIGATION COMPLETED ===\n";
?>