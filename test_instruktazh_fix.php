<?php
/**
 * Test the Инструктаж button fix
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TESTING INSTRUKTAZH BUTTON FIX ===\n\n";

// Include required files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

try {
    // Initialize services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    echo "1. TESTING CALLBACK FLOW:\n";
    echo "=========================\n\n";
    
    // Test the complete flow for Инструктаж button
    $initialCallback = 'cat_ТабИнстр';
    $confirmationCallback = 'cat_ТабИнстр_yes';
    
    echo "Initial button: '{$initialCallback}'\n";
    echo "Confirmation button: '{$confirmationCallback}'\n\n";
    
    // Use reflection to access private methods
    $reflection = new ReflectionClass($tabelHandler);
    
    // Test 1: canHandle
    echo "2. TESTING canHandle():\n";
    $canHandle1 = $tabelHandler->canHandle($initialCallback);
    $canHandle2 = $tabelHandler->canHandle($confirmationCallback);
    echo "   canHandle('{$initialCallback}'): " . ($canHandle1 ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    echo "   canHandle('{$confirmationCallback}'): " . ($canHandle2 ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";
    
    // Test 2: isActivityConfirmation
    echo "3. TESTING isActivityConfirmation():\n";
    $isActivityConfirmationMethod = $reflection->getMethod('isActivityConfirmation');
    $isActivityConfirmationMethod->setAccessible(true);
    
    $isConfirmation1 = $isActivityConfirmationMethod->invokeArgs($tabelHandler, [$initialCallback]);
    $isConfirmation2 = $isActivityConfirmationMethod->invokeArgs($tabelHandler, [$confirmationCallback]);
    echo "   isActivityConfirmation('{$initialCallback}'): " . ($isConfirmation1 ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    echo "   isActivityConfirmation('{$confirmationCallback}'): " . ($isConfirmation2 ? 'TRUE' : 'FALSE') . "\n\n";
    
    // Test 3: isActivityExecution
    echo "4. TESTING isActivityExecution():\n";
    $isActivityExecutionMethod = $reflection->getMethod('isActivityExecution');
    $isActivityExecutionMethod->setAccessible(true);
    
    $isActivityExecution1 = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$initialCallback]);
    $isActivityExecution2 = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$confirmationCallback]);
    echo "   isActivityExecution('{$initialCallback}'): " . ($isActivityExecution1 ? 'TRUE' : 'FALSE ❌') . "\n";
    echo "   isActivityExecution('{$confirmationCallback}'): " . ($isActivityExecution2 ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";
    
    echo "=== EXPECTED FLOW ===\n";
    echo "1. User clicks 'Инструктаж' → '{$initialCallback}'\n";
    echo "   ✅ canHandle: " . ($canHandle1 ? 'YES' : 'NO') . "\n";
    echo "   ✅ isActivityConfirmation: " . ($isConfirmation1 ? 'YES' : 'NO') . "\n";
    echo "   → Should show confirmation dialog with 'Да/Нет' buttons\n\n";
    
    echo "2. User clicks 'Да' → '{$confirmationCallback}'\n";
    echo "   ✅ canHandle: " . ($canHandle2 ? 'YES' : 'NO') . "\n";
    echo "   ✅ isActivityExecution: " . ($isActivityExecution2 ? 'YES' : 'NO') . "\n";
    echo "   → Should create tabel entry for 'Инструктаж'\n\n";
    
    // Check the final status
    $allTestsPassed = $canHandle1 && $isConfirmation1 && $canHandle2 && $isActivityExecution2;
    
    if ($allTestsPassed) {
        echo "🎉 SUCCESS: All tests passed!\n";
        echo "The Инструктаж button should now work correctly.\n";
        echo "Please restart your web server and test.\n";
    } else {
        echo "❌ FAILURE: Some tests failed.\n";
        if (!$canHandle1) echo "   • Initial callback not handled\n";
        if (!$isConfirmation1) echo "   • Initial callback not recognized as confirmation\n";
        if (!$canHandle2) echo "   • Confirmation callback not handled\n";
        if (!$isActivityExecution2) echo "   • Confirmation callback not recognized as execution\n";
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>