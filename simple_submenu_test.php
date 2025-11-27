<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Testing Electrical Equipment Submenu Fix ===\n\n";

try {
    // Create MessageService
    $messageService = new MessageService();
    
    echo "1. Testing electrical equipment keyboard:\n";
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "   Keyboard created with " . count($keyboard) . " rows\n\n";
    
    // Check each button's callback format
    $shortFormatCount = 0;
    $longFormatCount = 0;
    
    foreach ($keyboard as $rowIndex => $row) {
        foreach ($row as $buttonIndex => $button) {
            $text = $button['text'];
            $callback = $button['callback_data'];
            
            echo "   Row $rowIndex, Button $buttonIndex: '$text'\n";
            echo "     Callback: '$callback'\n";
            
            if (str_starts_with($callback, 'cat_')) {
                $shortFormatCount++;
                echo "     ✅ SHORT FORMAT\n";
            } else if (str_starts_with($callback, 'category_')) {
                $longFormatCount++;
                echo "     ❌ LONG FORMAT\n";
            } else {
                echo "     ⚠️ UNKNOWN FORMAT\n";
            }
            echo "\n";
        }
    }
    
    echo "2. Summary:\n";
    echo "   Short format buttons: $shortFormatCount\n";
    echo "   Long format buttons: $longFormatCount\n\n";
    
    if ($longFormatCount == 0) {
        echo "✅ SUCCESS: All buttons now use short callback format!\n";
        echo "   This should fix the submenu issue where buttons appeared but didn't work.\n";
    } else {
        echo "❌ FAIL: Some buttons still use long callback format\n";
        echo "   The submenu will still have compatibility issues.\n";
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}