<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/pDB.php';

use CustomCommands\Handlers\TabelHandler;
use Services\MessageService;
use Services\TabelEntryService;
use Services\ProjectSelectorService;
use Services\DatabaseService;
use Services\AdminService;

echo "=== Testing Current Submenu State ===\n\n";

try {
    // Initialize services
    $messageService = new MessageService();
    $tabelEntryService = new TabelEntryService();
    $projectSelector = new ProjectSelectorService();
    $databaseService = new DatabaseService();
    $adminService = new AdminService();
    
    // Create TabelHandler instance
    $tabelHandler = new TabelHandler(
        $messageService,
        $tabelEntryService,
        $projectSelector,
        $databaseService,
        $adminService
    );
    
    echo "1. Testing canHandle for electrical equipment callback:\n";
    $testCallback = 'cat_ТабЭлОб';
    $canHandle = $tabelHandler->canHandle($testCallback);
    echo "   Callback: '$testCallback'\n";
    echo "   canHandle result: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n\n";
    
    echo "2. Testing category extraction:\n";
    // Use reflection to access private method
    $reflection = new ReflectionClass($tabelHandler);
    $extractMethod = $reflection->getMethod('extractCategoryFromCallback');
    $extractMethod->setAccessible(true);
    
    $extractedCategory = $extractMethod->invoke($tabelHandler, $testCallback);
    echo "   Input: '$testCallback'\n";
    echo "   Extracted category: '$extractedCategory'\n\n";
    
    echo "3. Testing routing logic in handleOtherWorkTypes:\n";
    
    // Check if the routing condition exists in the code
    $handlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
    $handlerContent = file_get_contents($handlerPath);
    
    if (strpos($handlerContent, "if (\$category === 'ТабЭлОб')") !== false) {
        echo "   ✓ Routing condition for 'ТабЭлОб' found in code\n";
    } else {
        echo "   ✗ Routing condition for 'ТабЭлОб' NOT found in code\n";
    }
    
    if (strpos($handlerContent, 'handleElectricalEquipmentMenu') !== false) {
        echo "   ✓ handleElectricalEquipmentMenu method found in code\n";
    } else {
        echo "   ✗ handleElectricalEquipmentMenu method NOT found in code\n";
    }
    
    echo "\n4. Testing MessageService keyboard creation:\n";
    
    // Check if createElectricalEquipmentKeyboard method exists
    if (method_exists($messageService, 'createElectricalEquipmentKeyboard')) {
        echo "   ✓ createElectricalEquipmentKeyboard method exists\n";
        
        try {
            $keyboard = $messageService->createElectricalEquipmentKeyboard();
            echo "   ✓ Keyboard created successfully with " . count($keyboard) . " rows\n";
            
            // Show first few buttons
            if (count($keyboard) > 0 && isset($keyboard[0][0])) {
                echo "   First button text: '" . $keyboard[0][0]['text'] . "'\n";
                echo "   First button callback: '" . $keyboard[0][0]['callback_data'] . "'\n";
            }
        } catch (Exception $e) {
            echo "   ✗ Error creating keyboard: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ✗ createElectricalEquipmentKeyboard method does NOT exist\n";
    }
    
    echo "\n5. Testing handler priority:\n";
    $priority = $tabelHandler->getPriority();
    echo "   TabelHandler priority: $priority\n";
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}