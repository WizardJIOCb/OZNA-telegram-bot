<?php
// Debug script for showAccessRequestsList method

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

echo "Debugging showAccessRequestsList method...\n";

try {
    // Create service instances
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Check requests data
    echo "\n1. Checking access requests data:\n";
    $requests = $adminService->getAllAccessRequests();
    echo "Total requests: " . count($requests) . "\n";
    
    if (!empty($requests)) {
        foreach ($requests as $index => $request) {
            echo "Request #" . ($index + 1) . ":\n";
            foreach ($request as $key => $value) {
                echo "  {$key}: " . print_r($value, true) . "\n";
            }
        }
    } else {
        echo "No requests found.\n";
    }
    
    // Test the showAccessRequestsList method
    echo "\n2. Testing showAccessRequestsList method:\n";
    $adminHandler = new AdminHandler($adminService, $messageService);
    
    // Use reflection to call private method
    $reflection = new ReflectionClass($adminHandler);
    $method = $reflection->getMethod('showAccessRequestsList');
    $method->setAccessible(true);
    
    // Test with your user ID
    $chatId = '5261465966';
    echo "Calling showAccessRequestsList for chat_id: {$chatId}\n";
    
    // Capture the output
    ob_start();
    $result = $method->invoke($adminHandler, $chatId);
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