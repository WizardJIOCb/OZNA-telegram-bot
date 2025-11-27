<?php
/**
 * Test script to verify the Tabel button functionality
 */

// Include required files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

echo "Testing Tabel button functionality...\n";

try {
    // Create MessageService instance
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    // Test the createWorkTypeKeyboard method
    $keyboard = $messageService->createWorkTypeKeyboard();
    
    echo "SUCCESS: createWorkTypeKeyboard() method works correctly\n";
    echo "Keyboard data:\n";
    print_r($keyboard);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "Test completed.\n";