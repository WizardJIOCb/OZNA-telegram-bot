<?php
/**
 * Test script to verify the actual controlled project report sending
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== TESTING WITH ACTUAL CONFIGURED PROJECT ===\n\n";

try {
    // Initialize services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    
    // Test user ID
    $testUserId = 230379668;
    
    // Get user data
    $userData = $databaseService->getUserData($testUserId);
    $controlProjects = $userData['control_projects'] ?? [];
    
    echo "1. USER'S CONFIGURED CONTROL PROJECTS:\n";
    foreach ($controlProjects as $projectCode => $settings) {
        echo "   • Project: {$projectCode}\n";
        echo "     - Group ID: {$settings['group_id']}\n";
        echo "     - Chat ID: {$settings['chat_id']}\n\n";
    }
    
    // Use the first configured project
    $testProjectCode = array_keys($controlProjects)[0] ?? null;
    
    if (!$testProjectCode) {
        echo "❌ No controlled projects configured for this user\n";
        exit;
    }
    
    echo "2. TESTING WITH PROJECT: {$testProjectCode}\n";
    
    // Test parameters
    $testFio = $userData['fio'] ?? 'Test User';
    $testDate = date('d.m.Y');
    $testActivityType = 'Электромонтажные';
    $testChatId = 'test_chat';
    
    echo "   • FIO: {$testFio}\n";
    echo "   • Date: {$testDate}\n";
    echo "   • Project: {$testProjectCode}\n";
    echo "   • Activity: {$testActivityType}\n\n";
    
    echo "3. SENDING TEST REPORT (this will actually send messages):\n";
    
    // This will trigger the actual sending
    $success = $tabelEntry->addEntry($testUserId, $testFio, $testDate, $testProjectCode, $testActivityType, $testChatId);
    
    if ($success) {
        echo "   ✅ Report entry created successfully!\n";
        echo "   📤 Report sent to functional direction group: Мобильная бригада\n";
        echo "   📤 Report copy sent to controlled project settings:\n";
        echo "      • Group: {$controlProjects[$testProjectCode]['group_id']}\n";
        echo "      • Chat: {$controlProjects[$testProjectCode]['chat_id']}\n";
        
        if ($controlProjects[$testProjectCode]['chat_id'] === 'personal') {
            echo "      • Note: 'personal' means copy sent to user's personal chat\n";
        }
    } else {
        echo "   ❌ Report entry failed!\n";
    }
    
    echo "\n=== REAL TEST COMPLETED ===\n";
    echo "Check the Telegram groups to verify that:\n";
    echo "1. The report appeared in the functional direction group (Мобильная бригада)\n";
    echo "2. A copy also appeared in the controlled project group: {$controlProjects[$testProjectCode]['group_id']}\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>