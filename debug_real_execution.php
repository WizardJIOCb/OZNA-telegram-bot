<?php
/**
 * Debug the real execution path to find where it's failing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUGGING REAL EXECUTION PATH ===\n\n";

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
    
    // Test the exact callback that's failing
    $problematicCallback = 'cat_ТабИнстр_yes';
    
    echo "Testing problematic callback: '{$problematicCallback}'\n\n";
    
    // Use reflection to access private methods and trace the execution path
    $reflection = new ReflectionClass($tabelHandler);
    
    // Step 1: Test canHandle
    echo "1. Testing canHandle():\n";
    $canHandle = $tabelHandler->canHandle($problematicCallback);
    echo "   Result: " . ($canHandle ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";
    
    if (!$canHandle) {
        echo "❌ PROBLEM: TabelHandler cannot handle the callback!\n";
        echo "This means it never gets to the TabelHandler.\n";
        return;
    }
    
    // Step 2: Test routeTabelCallback path
    echo "2. Tracing routeTabelCallback() execution:\n";
    
    // Check isConfirmationCallback
    $isConfirmationCallbackMethod = $reflection->getMethod('isConfirmationCallback');
    $isConfirmationCallbackMethod->setAccessible(true);
    $isConfirmation = $isConfirmationCallbackMethod->invokeArgs($tabelHandler, [$problematicCallback]);
    echo "   isConfirmationCallback: " . ($isConfirmation ? 'TRUE' : 'FALSE') . "\n";
    
    // Check isActivityExecution
    $isActivityExecutionMethod = $reflection->getMethod('isActivityExecution');
    $isActivityExecutionMethod->setAccessible(true);
    $isActivityExecution = $isActivityExecutionMethod->invokeArgs($tabelHandler, [$problematicCallback]);
    echo "   isActivityExecution: " . ($isActivityExecution ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    
    // Check isActivityConfirmation
    $isActivityConfirmationMethod = $reflection->getMethod('isActivityConfirmation');
    $isActivityConfirmationMethod->setAccessible(true);
    $isActivityConfirmation = $isActivityConfirmationMethod->invokeArgs($tabelHandler, [$problematicCallback]);
    echo "   isActivityConfirmation: " . ($isActivityConfirmation ? 'TRUE' : 'FALSE') . "\n\n";
    
    // Step 3: Determine routing path
    echo "3. Routing decision:\n";
    if ($isActivityExecution) {
        echo "   → Should route to handleActivityExecution() ✅\n";
    } elseif ($isActivityConfirmation) {
        echo "   → Should route to handleActivityConfirmation()\n";
    } else {
        echo "   → Will route to handleOtherWorkTypes() ❌\n";
        echo "   → This is where 'неизвестная команда табеля' comes from!\n";
    }
    
    echo "\n4. Testing handleOtherWorkTypes():\n";
    
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
                    return 5261465966;
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
    
    $mockCallbackQuery = new MockCallbackQuery($problematicCallback);
    $mockConversation = new MockConversation();
    
    // Test what happens in handleOtherWorkTypes
    $handleOtherWorkTypesMethod = $reflection->getMethod('handleOtherWorkTypes');
    $handleOtherWorkTypesMethod->setAccessible(true);
    
    // Extract just the category part
    $category = str_replace(['category_', 'cat_'], '', $problematicCallback);
    echo "   Category passed to handleOtherWorkTypes: '{$category}'\n";
    
    // Check what conditions in handleOtherWorkTypes might match
    $categoryMappings = [
        'Табель_Пусконаладка' => 'handleCommissioningDateSelection',
        'Табель_Шеф_монтаж' => 'handleSupervisionDateSelection',
        'cat_ТабЭлОб' => 'handleElectricalEquipmentMenu',
        'Табель_Электрооборудование' => 'handleElectricalEquipmentMenu',
        'cat_ТабЭлОб_ДопРаб' => 'handleAdditionalWorksElectricalMenu',
        'Табель_Мобильная_Бригада' => 'handleMobileBrigadeDateSelection',
        'ТабПНРаботы' => 'handleCommissioningWorksMenu'
    ];
    
    $matchFound = false;
    foreach ($categoryMappings as $pattern => $handler) {
        if ($category === $pattern) {
            echo "   ✅ Would match: {$pattern} → {$handler}\n";
            $matchFound = true;
            break;
        }
    }
    
    if (!$matchFound) {
        echo "   ❌ No matching condition found in handleOtherWorkTypes\n";
        echo "   → This leads to 'Неизвестная команда табеля'\n";
    }
    
    echo "\n=== CONCLUSION ===\n";
    if ($isActivityExecution) {
        echo "✅ The logic should work correctly.\n";
        echo "If you're still seeing 'неизвестная команда табеля', it means:\n";
        echo "   1. The web server hasn't restarted (most likely)\n";
        echo "   2. A different version of TabelHandler.php is being used\n";
        echo "   3. The callback data is being corrupted somewhere\n";
    } else {
        echo "❌ The logic is still broken. The callback is not being recognized as activity execution.\n";
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?>