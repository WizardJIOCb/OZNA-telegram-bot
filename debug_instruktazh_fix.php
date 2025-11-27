<?php
/**
 * Debug script to test the exact Инструктаж callback processing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUGGING INSTRUKTAZH CALLBACK ===\n\n";

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

try {
    // Initialize services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $projectSelector = new ProjectSelector();
    $tabelEntry = new TabelEntry($databaseService, $messageService);
    $adminService = new AdminService($messageService);
    
    // Create TabelHandler
    $tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
    
    // Test the exact problematic callback
    $problematicCallback = 'cat_ТабИнстр_yes';
    echo "Testing callback: '{$problematicCallback}'\n";
    echo "Callback length: " . strlen($problematicCallback) . " bytes\n\n";
    
    // Test canHandle
    echo "1. Testing canHandle():\n";
    $canHandle = $tabelHandler->canHandle($problematicCallback);
    echo "   canHandle result: " . ($canHandle ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";
    
    // Use reflection to access private methods
    $reflection = new ReflectionClass($tabelHandler);
    
    // Test isActivityExecution directly
    echo "2. Testing isActivityExecution():\n";
    $isActivityExecutionMethod = $reflection->getMethod('isActivityExecution');
    $isActivityExecutionMethod->setAccessible(true);
    
    $isActivityExecution = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$problematicCallback]);
    echo "   isActivityExecution result: " . ($isActivityExecution ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    // Test the exact pattern matching
    if (preg_match('/^cat_(.+)_yes$/', $problematicCallback, $matches)) {
        $shortActivityKey = $matches[1];
        echo "   Extracted shortActivityKey: '{$shortActivityKey}'\n";
        
        $basicCommissioningActivities = [
            'ТабИнстр' => 'Инструктаж',
            'ТабДорога' => 'Дорога',
            'ТабПодгПоезд' => 'ПодготовкаКПоездке',
            'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
            'ТабОхрСигн' => 'ОхраннаяСигнализация',
            'ТабОтоплВент' => 'ОтоплениеИВентиляция',
            'ТабТехОб' => 'ТехнологическоеОборудование',
            'ТабМетрол' => 'Метрология',
            'ТабКомплОпр' => 'КомплексноеОпробование',
            'ТабРежНал' => 'РежимнаяНаладка'
        ];
        
        if (isset($basicCommissioningActivities[$shortActivityKey])) {
            $activityType = $basicCommissioningActivities[$shortActivityKey];
            echo "   Mapped to activityType: '{$activityType}'\n";
            
            // Get the actual activityTypes array from TabelHandler
            $activityTypesProperty = $reflection->getProperty('activityTypes');
            $activityTypesProperty->setAccessible(true);
            $activityTypes = $activityTypesProperty->getValue($tabelHandler);
            
            echo "   Checking if '{$activityType}' is in activityTypes array...\n";
            $inArray = in_array($activityType, $activityTypes);
            echo "   in_array result: " . ($inArray ? 'TRUE ✅' : 'FALSE ❌') . "\n";
            
            if (!$inArray) {
                echo "\n   PROBLEM FOUND: '{$activityType}' is NOT in activityTypes array!\n";
                echo "   activityTypes array contains:\n";
                foreach ($activityTypes as $index => $type) {
                    echo "     [{$index}] => '{$type}'\n";
                    if ($type === $activityType) {
                        echo "         ^^^ FOUND MATCH! ✅\n";
                    }
                }
            } else {
                echo "   ✅ Activity type IS in the array - no issue here\n";
            }
        } else {
            echo "   ❌ shortActivityKey not found in basicCommissioningActivities\n";
        }
    } else {
        echo "   ❌ Pattern '/^cat_(.+)_yes$/' did not match\n";
    }
    
    echo "\n3. Testing handleActivityExecution():\n";
    $handleActivityExecutionMethod = $reflection->getMethod('handleActivityExecution');
    $handleActivityExecutionMethod->setAccessible(true);
    
    // Create mock objects for testing
    class MockCallbackQuery {
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
    
    $mockCallbackQuery = new MockCallbackQuery();
    $mockConversation = new MockConversation();
    
    try {
        $result = $handleActivityExecutionMethod->invokeArgs($tabelHandler, [$mockCallbackQuery, $problematicCallback, $mockConversation]);
        echo "   handleActivityExecution result: SUCCESS ✅\n";
        if (is_object($result) && method_exists($result, 'isOk')) {
            echo "   ServerResponse isOk: " . ($result->isOk() ? 'TRUE' : 'FALSE') . "\n";
        }
    } catch (Exception $e) {
        echo "   handleActivityExecution result: ERROR ❌\n";
        echo "   Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "The callback '{$problematicCallback}' should:\n";
    echo "1. Be handled by TabelHandler: " . ($canHandle ? '✅' : '❌') . "\n";
    echo "2. Be recognized as activity execution: " . ($isActivityExecution ? '✅' : '❌') . "\n";
    echo "3. Create a tabel entry for 'Инструктаж'\n\n";
    
    if ($canHandle && $isActivityExecution) {
        echo "🎉 SUCCESS: The fix should work correctly!\n";
        echo "If you're still seeing 'неизвестная команда табеля', please restart your web server.\n";
    } else {
        echo "❌ PROBLEM: There's still an issue with the logic.\n";
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>