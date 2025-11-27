<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use CustomCommands\Handlers\TabelHandler;
use Services\MessageService;
use Services\TabelEntryService;
use Services\ProjectSelectorService;
use Services\DatabaseService;
use Services\AdminService;

echo "=== Testing Submenu Fix ===\n\n";

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
    
    echo "1. Testing electrical equipment keyboard:\n";
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "   Keyboard created with " . count($keyboard) . " rows\n";
    
    // Check that all buttons use short callback format
    $shortFormatCount = 0;
    $longFormatCount = 0;
    
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            $callback = $button['callback_data'];
            if (str_starts_with($callback, 'cat_')) {
                $shortFormatCount++;
                echo "   ✓ Short format: '{$button['text']}' -> '$callback'\n";
            } else {
                $longFormatCount++;
                echo "   ✗ Long format: '{$button['text']}' -> '$callback'\n";
            }
        }
    }
    
    echo "\n2. Summary:\n";
    echo "   Short format buttons: $shortFormatCount\n";
    echo "   Long format buttons: $longFormatCount\n";
    
    if ($longFormatCount == 0) {
        echo "   ✅ SUCCESS: All buttons use short callback format!\n";
    } else {
        echo "   ❌ FAIL: Some buttons still use long callback format\n";
    }
    
    echo "\n3. Testing canHandle method:\n";
    $testCallback = 'cat_ТабЭлОб';
    $canHandle = $tabelHandler->canHandle($testCallback);
    echo "   Callback: '$testCallback'\n";
    echo "   canHandle result: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";
    
    if ($canHandle) {
        echo "   ✅ SUCCESS: TabelHandler can handle electrical equipment callback!\n";
    } else {
        echo "   ❌ FAIL: TabelHandler cannot handle electrical equipment callback\n";
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}