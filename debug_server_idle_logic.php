<?php

/**
 * Test to determine what getIdleReasonFromCallback logic is actually being used by the server
 */

require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== DEBUGGING SERVER IDLE LOGIC ===\n\n";

try {
    // Create services (minimal setup)
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    echo "✅ TabelHandler created successfully\n";
    
    // Use reflection to access the private getIdleReasonFromCallback method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('getIdleReasonFromCallback');
    $method->setAccessible(true);
    
    echo "✅ Reflection method accessed successfully\n\n";
    
    // Test with the exact scenario that the user is experiencing
    $testCallbacks = [
        'category_Табель_Простой_1_def' => 'Expected: Ожидание допоставки (Дефицит по спецификации)',
        'category_Табель_Простой_def' => 'Missing category number',
        'Простой_1_def' => 'No category prefix',
        'Простой_def' => 'Only subcategory'
    ];
    
    foreach ($testCallbacks as $callback => $description) {
        echo "Testing: {$callback}\n";
        echo "Description: {$description}\n";
        
        try {
            $result = $method->invoke($tabelHandler, $callback);
            echo "Result: '{$result}'\n";
            
            if ($result === 'Дефицит по спецификации') {
                echo "🎯 MATCH! This produces the user's exact result\n";
            } elseif ($result === 'Ожидание допоставки (Дефицит по спецификации)') {
                echo "✅ CORRECT! This is what we want\n";
            } else {
                echo "❓ Different result\n";
            }
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // Additional test: Check if the method has the new logic by testing the mapping
    echo "=== CHECKING LOGIC TYPE ===\n";
    $testWithParentLookup = 'category_Табель_Простой_def';
    $result = $method->invoke($tabelHandler, $testWithParentLookup);
    
    if ($result === 'Ожидание допоставки (Дефицит по спецификации)') {
        echo "✅ NEW LOGIC CONFIRMED: Server has subcategoryToParent mapping\n";
        echo "The issue is NOT with the logic itself.\n";
    } elseif ($result === 'Дефицит по спецификации') {
        echo "🚨 OLD LOGIC DETECTED: Server is using simple subcategories array\n";
        echo "The server is not using the updated code!\n";
    } else {
        echo "❓ UNEXPECTED RESULT: '{$result}'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Failed to create TabelHandler: " . $e->getMessage() . "\n";
    echo "This might indicate a configuration or dependency issue.\n";
}

echo "\n=== FILE VERIFICATION ===\n";

// Check which file is actually being loaded
$reflection = new ReflectionClass('Longman\\TelegramBot\\Commands\\SystemCommands\\Handlers\\TabelHandler');
$filePath = $reflection->getFileName();
echo "TabelHandler loaded from: {$filePath}\n";

// Check the file modification time
$fileTime = filemtime($filePath);
echo "File last modified: " . date('Y-m-d H:i:s', $fileTime) . "\n";

// Check for the specific logic in the file
$fileContent = file_get_contents($filePath);
if (strpos($fileContent, 'subcategoryToParent') !== false) {
    echo "✅ File contains NEW logic (subcategoryToParent)\n";
} elseif (strpos($fileContent, 'subcategories') !== false) {
    echo "⚠️ File contains subcategories array (check if it's new or old format)\n";
} else {
    echo "❓ Could not determine logic type from file content\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. If OLD LOGIC is detected, the server needs to restart to reload the updated code\n";
echo "2. If NEW LOGIC is confirmed but user still sees old result, check for:\n";
echo "   - PHP-FPM/Apache restart needed\n";
echo "   - OpCache issues (opcache_reset())\n";
echo "   - Different code path being executed\n";
echo "   - Conversation state caching issues\n";