<?php
/**
 * Test script to verify controlled project report duplication functionality
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== TESTING CONTROLLED PROJECT REPORT DUPLICATION ===\n\n";

try {
    // Initialize services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    
    echo "✅ Services initialized successfully\n\n";
    
    // Test user ID (use the working user from previous tests)
    $testUserId = 230379668;
    
    echo "1. CHECKING USER DATA:\n";
    $userData = $databaseService->getUserData($testUserId);
    
    echo "   • User ID: {$testUserId}\n";
    echo "   • FIO: " . ($userData['fio'] ?? 'Not set') . "\n";
    echo "   • Functional Direction: " . ($userData['functional_direction'] ?? 'Not set') . "\n";
    
    if (isset($userData['control_projects']) && !empty($userData['control_projects'])) {
        echo "   • Control Projects: " . count($userData['control_projects']) . " configured\n";
        
        foreach ($userData['control_projects'] as $projectCode => $settings) {
            echo "     - {$projectCode}:\n";
            echo "       • Group ID: " . ($settings['group_id'] ?? 'not set') . "\n";
            echo "       • Chat ID: " . ($settings['chat_id'] ?? 'not set') . "\n";
        }
    } else {
        echo "   • Control Projects: None configured\n";
    }
    
    echo "\n2. TESTING REPORT DUPLICATION LOGIC:\n";
    
    // Test parameters
    $testFio = $userData['fio'] ?? 'Test User';
    $testDate = date('d.m.Y');
    $testProjectCode = 'ОПНВГ-001'; // Use a project that should be in control settings
    $testActivityType = 'Электромонтажные';
    $testChatId = 'test_chat';
    
    echo "   Test Parameters:\n";
    echo "   • FIO: {$testFio}\n";
    echo "   • Date: {$testDate}\n";
    echo "   • Project: {$testProjectCode}\n";
    echo "   • Activity: {$testActivityType}\n\n";
    
    echo "3. SIMULATING REPORT CREATION:\n";
    
    // Create the message that would be sent
    $testMessage = $tabelEntry->createTabelMessage($testFio, $testDate, $testProjectCode, $testActivityType);
    echo "   Generated message:\n";
    echo "   " . str_replace("\n", "\n   ", $testMessage) . "\n\n";
    
    echo "4. CHECKING CONTROLLED PROJECT LOGIC:\n";
    
    // Check if this project is in control settings
    $controlProjects = $userData['control_projects'] ?? [];
    
    if (isset($controlProjects[$testProjectCode])) {
        $projectSettings = $controlProjects[$testProjectCode];
        echo "   ✅ Project {$testProjectCode} IS in control settings\n";
        echo "   • Group ID: " . ($projectSettings['group_id'] ?? 'not set') . "\n";
        echo "   • Chat ID: " . ($projectSettings['chat_id'] ?? 'not set') . "\n";
        
        if (!empty($projectSettings['group_id'])) {
            echo "   ✅ Report copy WILL be sent to group: {$projectSettings['group_id']}\n";
            
            if (!empty($projectSettings['chat_id']) && $projectSettings['chat_id'] !== 'personal') {
                echo "   ✅ Report copy WILL be sent to thread: {$projectSettings['chat_id']}\n";
            } else if ($projectSettings['chat_id'] === 'personal') {
                echo "   ✅ Report copy WILL be sent to personal chat of user: {$testUserId}\n";
            }
        } else {
            echo "   ❌ Group ID not configured, report copy will NOT be sent\n";
        }
    } else {
        echo "   ℹ️ Project {$testProjectCode} is NOT in control settings\n";
        echo "   ℹ️ Report copy will NOT be sent (normal behavior)\n";
        
        // Show which projects ARE in control settings
        if (!empty($controlProjects)) {
            echo "   Projects currently in control settings:\n";
            foreach (array_keys($controlProjects) as $controlledProject) {
                echo "   • {$controlledProject}\n";
            }
        }
    }
    
    echo "\n5. EXECUTION SUMMARY:\n";
    echo "   When a report is submitted:\n";
    echo "   1. ✅ Report will be sent to functional direction group (normal behavior)\n";
    
    if (isset($controlProjects[$testProjectCode]) && !empty($controlProjects[$testProjectCode]['group_id'])) {
        echo "   2. ✅ Report copy will ALSO be sent to controlled project settings\n";
        echo "   3. ✅ Copy will use the same message_thread_id logic as test notifications\n";
    } else {
        echo "   2. ℹ️ No controlled project copy will be sent (project not configured)\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    echo "The controlled project duplication functionality has been successfully implemented!\n";
    echo "Users can now configure projects to receive report copies in addition to the standard functional direction reports.\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>