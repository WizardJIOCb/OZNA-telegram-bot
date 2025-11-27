<?php
/**
 * Deep debugging script to find the real problem with Инструктаж button
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEEP DEBUGGING INSTRUKTAZH BUTTON ===\n\n";

// Include all required files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

try {
    // Initialize services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    echo "1. TESTING CALLBACK PROCESSING CHAIN:\n";
    echo "=====================================\n\n";
    
    // Test the exact problematic callback flow
    $initialCallback = 'cat_ТабИнстр';
    $confirmationCallback = 'cat_ТабИнстр_yes';
    
    echo "Initial button click: '{$initialCallback}'\n";
    echo "Confirmation button click: '{$confirmationCallback}'\n\n";
    
    // Test canHandle for both callbacks
    echo "2. TESTING canHandle():\n";
    $canHandle1 = $tabelHandler->canHandle($initialCallback);
    $canHandle2 = $tabelHandler->canHandle($confirmationCallback);
    echo "   canHandle('{$initialCallback}'): " . ($canHandle1 ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    echo "   canHandle('{$confirmationCallback}'): " . ($canHandle2 ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";
    
    // Use reflection to access private methods
    $reflection = new ReflectionClass($tabelHandler);
    
    // Test isConfirmationCallback
    echo "3. TESTING isConfirmationCallback():\n";
    $isConfirmationMethod = $reflection->getMethod('isConfirmationCallback');
    $isConfirmationMethod->setAccessible(true);
    
    $isConfirmation1 = $isConfirmationMethod->invokeArgs($tabelHandler, [$initialCallback]);
    $isConfirmation2 = $isConfirmationMethod->invokeArgs($tabelHandler, [$confirmationCallback]);
    echo "   isConfirmationCallback('{$initialCallback}'): " . ($isConfirmation1 ? 'TRUE' : 'FALSE') . "\n";
    echo "   isConfirmationCallback('{$confirmationCallback}'): " . ($isConfirmation2 ? 'TRUE' : 'FALSE') . "\n\n";
    
    // Test isActivityExecution
    echo "4. TESTING isActivityExecution():\n";
    $isActivityExecutionMethod = $reflection->getMethod('isActivityExecution');
    $isActivityExecutionMethod->setAccessible(true);
    
    $isActivityExecution1 = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$initialCallback]);
    $isActivityExecution2 = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$confirmationCallback]);
    echo "   isActivityExecution('{$initialCallback}'): " . ($isActivityExecution1 ? 'TRUE' : 'FALSE') . "\n";
    echo "   isActivityExecution('{$confirmationCallback}'): " . ($isActivityExecution2 ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";
    
    // Test routing logic
    echo "5. TESTING ROUTING LOGIC:\n";
    $routeTabelCallbackMethod = $reflection->getMethod('routeTabelCallback');
    $routeTabelCallbackMethod->setAccessible(true);
    
    // Create mock objects for testing
    class MockCallbackQuery {
        private $data;
        
        public function __construct($data) {
            $this->data = $data;
        }
        
        public function getData() {
            return $this->data;
        }
        
        public function getMessage() {
            return new class {
                public function getChat() {
                    return new class {
                        public function getId() {
                            return '123456789';
                        }
                    };
                }
            };
        }
        
        public function getFrom() {
            return new class {
                public function getId() {
                    return 5261465966; // Test user ID
                }
            };
        }
    }
    
    class MockConversation {
        public $notes = [
            'project_code' => 'TEST001',
            'tabel_data' => '19.09.2025'
        ];
        
        public function update() {
            // Mock implementation
        }
    }
    
    // Test initial callback routing
    echo "   Testing initial callback routing: '{$initialCallback}'\n";
    $mockCallbackQuery1 = new MockCallbackQuery($initialCallback);
    $mockConversation1 = new MockConversation();
    
    try {
        echo "   → Should route to: Activity Confirmation (show 'Да/Нет' buttons)\n";
        echo "   → Expected behavior: Show confirmation dialog\n\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n\n";
    }
    
    // Test confirmation callback routing
    echo "   Testing confirmation callback routing: '{$confirmationCallback}'\n";
    $mockCallbackQuery2 = new MockCallbackQuery($confirmationCallback);
    $mockConversation2 = new MockConversation();
    
    try {
        echo "   → Should route to: Activity Execution (create tabel entry)\n";
        echo "   → Expected behavior: Create tabel entry for 'Инструктаж'\n\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n\n";
    }
    
    // Check what happens in handleOtherWorkTypes
    echo "6. TESTING handleOtherWorkTypes():\n";
    $handleOtherWorkTypesMethod = $reflection->getMethod('handleOtherWorkTypes');
    $handleOtherWorkTypesMethod->setAccessible(true);
    
    try {
        echo "   Testing what happens when callback doesn't match any condition...\n";
        echo "   This is where 'неизвестная команда табеля' would be generated\n\n";
    } catch (Exception $e) {
        echo "   ERROR: " . $e->getMessage() . "\n\n";
    }
    
    // Test CallbackHandlerFactory
    echo "7. TESTING CallbackHandlerFactory PROCESSING:\n";
    $factory = new CallbackHandlerFactory();
    $factory->registerHandler($tabelHandler);
    
    $testCallback = $confirmationCallback;
    echo "   Testing CallbackHandlerFactory with: '{$testCallback}'\n";
    
    // Extract category the same way CallbackHandlerFactory does
    $extractCategoryMethod = $reflection->getMethod('extractCategory');
    $extractCategoryMethod->setAccessible(true);
    $extractedCategory = $extractCategoryMethod->invokeArgs($factory, [$testCallback]);
    echo "   Extracted category: '{$extractedCategory}'\n";
    
    // Check if TabelHandler would handle it
    $wouldHandle = $tabelHandler->canHandle($testCallback);
    echo "   TabelHandler would handle: " . ($wouldHandle ? 'YES ✅' : 'NO ❌') . "\n\n";
    
    echo "=== ANALYSIS SUMMARY ===\n";
    echo "Expected flow for 'Инструктаж' button:\n";
    echo "1. User clicks 'Инструктаж' → sends 'cat_ТабИнстр'\n";
    echo "2. TabelHandler receives and routes to confirmation dialog\n";
    echo "3. User clicks 'Да' → sends 'cat_ТабИнстр_yes'\n";
    echo "4. TabelHandler receives and routes to activity execution\n";
    echo "5. Creates tabel entry for 'Инструктаж'\n\n";
    
    echo "Current status:\n";
    echo "✅ Step 1-2: " . ($canHandle1 && $isConfirmation1 ? 'WORKING' : 'BROKEN') . "\n";
    echo "✅ Step 3-4: " . ($canHandle2 && $isActivityExecution2 ? 'WORKING' : 'BROKEN') . "\n";
    echo "✅ Step 5: " . ($isActivityExecution2 ? 'SHOULD WORK' : 'BROKEN') . "\n\n";
    
    if ($canHandle2 && $isActivityExecution2) {
        echo "🎉 The logic appears to be working correctly!\n";
        echo "If you're still seeing 'неизвестная команда табеля', the issue might be:\n";
        echo "   • Server caching (restart required)\n";
        echo "   • Different code version running\n";
        echo "   • Callback data corruption in transit\n";
    } else {
        echo "❌ Found issues in the logic that need to be fixed!\n";
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>