<?php

/**
 * Comprehensive test for both confirmation and final messages
 */

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;

echo "=== COMPREHENSIVE CURRENT ACTIVITY TEST ===\n\n";

try {
    // Create services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    
    echo "✅ TabelEntry instance created successfully\n\n";
    
    // Test data
    $testCases = [
        [
            'activityType' => 'МедОсмотр',
            'projectCode' => 'Текущая деятельность',
            'description' => 'Medical exam'
        ],
        [
            'activityType' => 'Аттестация', 
            'projectCode' => 'Текущая деятельность',
            'description' => 'Certification'
        ],
        [
            'activityType' => 'ПодготовкаТКП_ОПНВГ',
            'projectCode' => 'Текущая деятельность', 
            'description' => 'TKP OPNVG'
        ],
        [
            'activityType' => 'ПодготовкаТКП_МСО',
            'projectCode' => 'Текущая деятельность',
            'description' => 'TKP MSO'
        ]
    ];
    
    foreach ($testCases as $index => $testCase) {
        $testNum = $index + 1;
        echo "TEST {$testNum}: {$testCase['description']}\n";
        echo str_repeat("=", 60) . "\n";
        
        $activityType = $testCase['activityType'];
        $projectCode = $testCase['projectCode'];
        
        echo "Activity Type: {$activityType}\n";
        echo "Project Code: {$projectCode}\n\n";
        
        // Test 1: Confirmation message (preliminary report)
        echo "1. CONFIRMATION MESSAGE (Preliminary Report):\n";
        $confirmationMessage = $tabelEntry->getConfirmationMessage($activityType, $projectCode);
        echo "   Result: {$confirmationMessage}\n";
        
        // Check if it's using the correct format for Current Activities
        if (strpos($confirmationMessage, 'Проект') === false && strpos($confirmationMessage, '. Отправить запись в табель?') !== false) {
            echo "   ✅ CORRECT: Using Current Activity format (no project mention)\n";
        } else {
            echo "   ❌ WRONG: Using project format instead of Current Activity format\n";
        }
        
        echo "\n2. FINAL MESSAGE (Final Report):\n";
        // Test 2: Final message
        $fio = "Тестовый Пользователь";
        $date = "21.09.2025";
        $finalMessage = $tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType);
        echo "   Result: {$finalMessage}\n";
        
        // Check if it's using the correct format for Current Activities
        if (strpos($finalMessage, 'Запись в табель от') !== false && strpos($finalMessage, 'добавлена') !== false && strpos($finalMessage, 'Проект') === false) {
            echo "   ✅ CORRECT: Using Current Activity format (no project mention)\n";
        } else {
            echo "   ❌ WRONG: Using project format instead of Current Activity format\n";
        }
        
        echo "\n" . str_repeat("-", 60) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== WHAT TO CHECK IF TESTS STILL FAIL ===\n";
echo "1. Server restart needed - PHP code might be cached\n";
echo "2. Webhook might not be calling updated code\n";
echo "3. Different code path might be executing\n";
echo "4. Database/conversation state might have wrong project code\n";

?>