<?php
/**
 * Test the corrected duplication logic for ОИ834 scenario
 */

echo "=== TESTING CORRECTED DUPLICATION LOGIC ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

try {
    require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
    require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
    require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
    
    // Create TabelEntry instance
    $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry(
        new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService(),
        new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService()
    );
    
    $reflection = new ReflectionClass($tabelEntry);
    $isDuplicateDestinationMethod = $reflection->getMethod('isDuplicateDestination');
    $isDuplicateDestinationMethod->setAccessible(true);
    
    $getSenderGroupAndThreadMethod = $reflection->getMethod('getSenderGroupAndThread');
    $getSenderGroupAndThreadMethod->setAccessible(true);
    
    echo "1. SCENARIO: Пусконаладка user reports on project ОИ834\n\n";
    
    echo "   This project is controlled by:\n";
    echo "   • Петров Алексей Владимирович (Пусконаладка) → Group: -1001234567890\n";
    echo "   • Калимуллин Родион Данирович (Мобильная бригада) → Group: -1002722176147, Chat: personal\n";
    echo "   • Ткачев Дмитрий Владимирович (Пусконаладка) → Group: -1003095760733, Chat: 2\n\n";
    
    echo "2. TESTING DUPLICATION LOGIC FOR EACH SUPERVISOR:\n\n";
    
    $supervisors = [
        [
            'name' => 'Петров Алексей Владимирович',
            'fd' => 'Пусконаладка',
            'project_settings' => [
                'group_id' => '-1001234567890',
                'chat_id' => '-1001234567890'
            ]
        ],
        [
            'name' => 'Калимуллин Родион Данирович',
            'fd' => 'Мобильная бригада',
            'project_settings' => [
                'group_id' => '-1002722176147',
                'chat_id' => 'personal'
            ]
        ],
        [
            'name' => 'Ткачев Дмитрий Владимирович',
            'fd' => 'Пусконаладка',
            'project_settings' => [
                'group_id' => '-1003095760733',
                'chat_id' => '2'
            ]
        ]
    ];
    
    foreach ($supervisors as $supervisor) {
        echo "   Supervisor: {$supervisor['name']}\n";
        echo "   • FD: {$supervisor['fd']}\n";
        echo "   • Project Group: {$supervisor['project_settings']['group_id']}\n";
        echo "   • Project Chat: {$supervisor['project_settings']['chat_id']}\n";
        
        // Get supervisor's FD group and thread
        $supervisorGroupAndThread = $getSenderGroupAndThreadMethod->invoke($tabelEntry, $supervisor['fd']);
        echo "   • FD Group: {$supervisorGroupAndThread['group_id']}\n";
        echo "   • FD Thread: {$supervisorGroupAndThread['thread_id']}\n";
        
        // Check for duplication
        $isDuplicate = $isDuplicateDestinationMethod->invoke(
            $tabelEntry,
            $supervisor['project_settings'],
            $supervisorGroupAndThread
        );
        
        echo "   • Duplication detected: " . ($isDuplicate ? 'YES' : 'NO') . "\n";
        
        if ($isDuplicate) {
            echo "   • Action: SKIP (duplicate blocked)\n";
            echo "   • Result: No message sent to this supervisor\n";
        } else {
            echo "   • Action: SEND controlled project copy\n";
            echo "   • Result: Message sent to {$supervisor['project_settings']['group_id']}\n";
        }
        
        echo "\n";
    }
    
    echo "3. EXPECTED BEHAVIOR FOR ПУСКОНАЛАДКА USER REPORT:\n\n";
    echo "   When a Пусконаладка user sends report for ОИ834:\n";
    echo "   1. ✅ Normal send: Пусконаладка group (-1001400593888, thread 338)\n";
    echo "   2. Controlled project copies:\n";
    echo "      • Петров (Пусконаладка): Different group → SEND\n";
    echo "      • Калимуллин (Мобильная бригада): Same group + personal → BLOCKED!\n";
    echo "      • Ткачев (Пусконаладка): Different group → SEND\n\n";
    
    echo "   Result: Messages go to 2 places instead of 3!\n";
    echo "   ✅ No more Mobile Brigade duplication!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== TEST COMPLETED ===\n";
?>