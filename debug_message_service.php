<?php
// Debug script for MessageService

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Debugging MessageService...\n";

try {
    // Create message service instance
    $messageService = new MessageService();
    
    // Test simple message sending
    echo "\n1. Testing simple message sending:\n";
    $chatId = '5261465966'; // Your user ID
    $text = "Test message from debug script";
    
    echo "Sending message to chat_id: {$chatId}\n";
    echo "Message text: {$text}\n";
    
    // Capture the output
    ob_start();
    $result = $messageService->sendMessage($chatId, $text);
    $output = ob_get_clean();
    
    echo "Method output:\n";
    echo $output . "\n";
    
    echo "Method result:\n";
    print_r($result);
    
    // Check if result is a ServerResponse object
    if (is_object($result) && method_exists($result, 'isOk')) {
        echo "Result is ServerResponse object\n";
        echo "isOk(): " . ($result->isOk() ? 'true' : 'false') . "\n";
        if (!$result->isOk()) {
            echo "Error: " . $result->getDescription() . "\n";
            echo "Error code: " . $result->getErrorCode() . "\n";
        } else {
            echo "Message sent successfully!\n";
        }
    }
    
    // Test message with keyboard
    echo "\n2. Testing message with keyboard:\n";
    $textWithKeyboard = "Test message with keyboard";
    $keyboard = [
        [
            ['text' => 'Test Button', 'callback_data' => 'category_test']
        ]
    ];
    
    echo "Sending message with keyboard to chat_id: {$chatId}\n";
    echo "Message text: {$textWithKeyboard}\n";
    echo "Keyboard: " . json_encode($keyboard) . "\n";
    
    // Capture the output
    ob_start();
    $resultWithKeyboard = $messageService->sendMessage($chatId, $textWithKeyboard, $keyboard);
    $outputWithKeyboard = ob_get_clean();
    
    echo "Method output:\n";
    echo $outputWithKeyboard . "\n";
    
    echo "Method result:\n";
    print_r($resultWithKeyboard);
    
    // Check if result is a ServerResponse object
    if (is_object($resultWithKeyboard) && method_exists($resultWithKeyboard, 'isOk')) {
        echo "Result is ServerResponse object\n";
        echo "isOk(): " . ($resultWithKeyboard->isOk() ? 'true' : 'false') . "\n";
        if (!$resultWithKeyboard->isOk()) {
            echo "Error: " . $resultWithKeyboard->getDescription() . "\n";
            echo "Error code: " . $resultWithKeyboard->getErrorCode() . "\n";
        } else {
            echo "Message with keyboard sent successfully!\n";
        }
    }
    
    echo "\nDebug completed!\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>