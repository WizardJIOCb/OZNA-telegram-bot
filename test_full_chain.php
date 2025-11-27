<?php
// Full chain test for access requests button

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

echo "Testing full chain for access requests button...\n";

try {
    // Create service instances
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Test AdminHandler
    echo "\n1. Testing AdminHandler:\n";
    $adminHandler = new AdminHandler($adminService, $messageService);
    
    // Test canHandle
    $callbackData = 'category_Админка_Заявки';
    echo "Testing canHandle for '{$callbackData}':\n";
    $canHandle = $adminHandler->canHandle($callbackData);
    echo "Result: " . ($canHandle ? 'true' : 'false') . "\n";
    
    // Test handle method (simulated)
    echo "\n2. Testing handle method simulation:\n";
    
    // We'll directly call showAccessRequestsList since that's what gets called
    $reflection = new ReflectionClass($adminHandler);
    $method = $reflection->getMethod('showAccessRequestsList');
    $method->setAccessible(true);
    
    $chatId = '5261465966'; // Your user ID
    echo "Calling showAccessRequestsList for chatId: {$chatId}\n";
    
    $result = $method->invoke($adminHandler, $chatId);
    
    echo "Result:\n";
    print_r($result);
    
    if (is_object($result) && method_exists($result, 'isOk')) {
        echo "isOk(): " . ($result->isOk() ? 'true' : 'false') . "\n";
        if (!$result->isOk()) {
            echo "Error description: " . $result->getDescription() . "\n";
            echo "Error code: " . $result->getErrorCode() . "\n";
        }
    }
    
    echo "\nFull chain test completed!\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>