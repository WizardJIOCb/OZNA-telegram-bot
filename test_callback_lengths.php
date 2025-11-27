<?php
/**
 * Test script to check the length of all callback data in the electrical equipment keyboard
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Load required files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';
require_once __DIR__ . '/Custome/DB.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Testing Callback Data Lengths ===\n\n";

try {
    // Create MessageService
    $messageService = new MessageService();
    
    // Create the electrical equipment keyboard
    $keyboard = $messageService->createElectricalEquipmentKeyboard();
    
    echo "Electrical Equipment Keyboard Callback Data Lengths:\n";
    echo "==================================================\n";
    
    foreach ($keyboard as $rowIndex => $row) {
        foreach ($row as $buttonIndex => $button) {
            $text = $button['text'];
            $callbackData = $button['callback_data'];
            $length = strlen($callbackData);
            $hex = bin2hex($callbackData);
            
            echo "Row {$rowIndex}, Button {$buttonIndex}:\n";
            echo "  Text: {$text}\n";
            echo "  Callback Data: {$callbackData}\n";
            echo "  Length: {$length} bytes\n";
            echo "  Hex: {$hex}\n";
            
            if ($length > 64) {
                echo "  ⚠️  WARNING: Exceeds Telegram's 64-byte limit!\n";
            }
            
            echo "\n";
        }
    }
    
    echo "=== Testing Original Electrical Equipment Button ===\n";
    $originalCallback = 'category_Табель_Электрооборудование';
    $originalLength = strlen($originalCallback);
    echo "Original electrical equipment button callback: {$originalCallback}\n";
    echo "Length: {$originalLength} bytes\n";
    if ($originalLength > 64) {
        echo "⚠️  WARNING: Exceeds Telegram's 64-byte limit!\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "The callback data length issue is likely causing the problems.\n";
    echo "We need to find a way to reduce the length while maintaining functionality.\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";