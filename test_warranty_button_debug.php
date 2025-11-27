<?php
/**
 * Test script to debug warranty equipment button creation
 */

require_once 'vendor/autoload.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== WARRANTY EQUIPMENT BUTTON DEBUG ===\n\n";

try {
    $messageService = new MessageService();
    
    echo "1. Testing warranty equipment keyboard creation:\n";
    echo "================================================\n";
    
    $keyboard = $messageService->createWarrantyEquipmentKeyboard();
    
    echo "Keyboard created successfully!\n";
    echo "Number of rows: " . count($keyboard) . "\n\n";
    
    foreach ($keyboard as $rowIndex => $row) {
        echo "Row " . ($rowIndex + 1) . ":\n";
        foreach ($row as $button) {
            $text = $button['text'] ?? 'N/A';
            $callbackData = $button['callback_data'] ?? 'N/A';
            $callbackLength = strlen($callbackData);
            
            echo "  Text: {$text}\n";
            echo "  Callback: {$callbackData}\n";
            echo "  Length: {$callbackLength} bytes\n";
            echo "  Valid UTF-8: " . (mb_check_encoding($callbackData, 'UTF-8') ? '✅ YES' : '❌ NO') . "\n";
            echo "  Has invalid chars: " . (preg_match('/[^\x20-\x7E\x{0080}-\x{FFFF}]/u', $callbackData) ? '❌ YES' : '✅ NO') . "\n";
            echo "  Length OK: " . ($callbackLength <= 64 ? '✅ YES' : '❌ NO') . "\n";
            
            // Check for specific problematic characters
            if (strpos($callbackData, "\n") !== false) {
                echo "  ❌ Contains newline characters!\n";
            }
            if (strpos($callbackData, "\r") !== false) {
                echo "  ❌ Contains carriage return characters!\n";
            }
            if (strpos($callbackData, "\t") !== false) {
                echo "  ❌ Contains tab characters!\n";
            }
            
            echo "\n";
        }
    }
    
    echo "2. Testing keyboard JSON encoding:\n";
    echo "==================================\n";
    
    $jsonKeyboard = json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ JSON encoding error: " . json_last_error_msg() . "\n";
    } else {
        echo "✅ JSON encoding successful\n";
        echo "JSON length: " . strlen($jsonKeyboard) . " bytes\n";
    }
    
    echo "\n3. Testing individual warranty button creation:\n";
    echo "===============================================\n";
    
    // Test creating a simple warranty button manually
    $testButton = [
        'text' => '⚡ Электрооборудование',
        'callback_data' => 'category_Табель_РабГарантии_ЭлектрОб'
    ];
    
    $testKeyboard = [[$testButton]];
    $testJson = json_encode(['inline_keyboard' => $testKeyboard], JSON_UNESCAPED_UNICODE);
    
    echo "Test button: " . json_encode($testButton, JSON_UNESCAPED_UNICODE) . "\n";
    echo "Test keyboard JSON: " . $testJson . "\n";
    echo "JSON error: " . (json_last_error() !== JSON_ERROR_NONE ? json_last_error_msg() : 'None') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";