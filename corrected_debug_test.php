<?php
// Corrected debug test using the existing MessageService with proper namespace

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Include the MessageService class directly
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Test sending debug messages using the existing MessageService
echo "=== Corrected Debug Test ===\n";

try {
    // Create an instance of MessageService
    $messageService = new MessageService();
    
    // Your chat ID from the logs
    $chatId = '5261465966';
    
    echo "Sending debug messages to chat ID: {$chatId}\n";
    
    // Send test debug messages in the format you want
    $debugMessages = [
        "🔍 DEBUG: back_button='pressed'",
        "🔍 DEBUG: callback_data='Табель_ШМ_Back'",
        "🔍 DEBUG: handler='TabelHandler'",
        "🔍 DEBUG: method='handleBackToSupervision'",
        "🔍 DEBUG: status='testing'",
        "🔍 DEBUG: action='button_pressed'",
        "🔍 DEBUG: result='awaiting_response'"
    ];
    
    foreach ($debugMessages as $message) {
        echo "Sending: {$message}\n";
        
        // Send the message
        $result = $messageService->sendMessage($chatId, $message);
        
        if (is_object($result) && method_exists($result, 'isOk') && $result->isOk()) {
            echo "✓ Message sent successfully\n";
        } else {
            echo "✗ Failed to send message\n";
            if (is_object($result) && method_exists($result, 'getDescription')) {
                echo "  Error: " . $result->getDescription() . "\n";
            }
        }
        
        // Small delay to avoid rate limiting
        sleep(1);
    }
    
    echo "\n=== Debug Test Complete ===\n";
    echo "Check your Telegram chat for the debug messages.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>