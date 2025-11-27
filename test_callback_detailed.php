<?php
// Detailed diagnostic script to test callback handling

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Detailed Callback Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .test-result { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Detailed Callback Test for 'Заявки на доступ' Button</h1>";

try {
    echo "<div class='test-result info'>Initializing services...</div>";
    
    // Initialize services
    $databaseService = new DatabaseService();
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "<div class='test-result info'>Creating handlers...</div>";
    
    // Create handlers
    $adminHandler = new AdminHandler($adminService, $messageService);
    $accessLevelHandler = new AccessLevelHandler($adminService, $messageService);
    
    echo "<div class='test-result info'>Testing handler priorities...</div>";
    
    // Test handler priorities
    echo "<div class='test-result success'>AdminHandler priority: " . $adminHandler->getPriority() . "</div>";
    echo "<div class='test-result success'>AccessLevelHandler priority: " . $accessLevelHandler->getPriority() . "</div>";
    
    // Test callback data that should be handled by AdminHandler
    $testCallbackData = 'category_Админка_Заявки';
    
    echo "<div class='test-result info'>Testing canHandle for callback data: '{$testCallbackData}'</div>";
    
    // Test if AdminHandler can handle the callback
    $adminCanHandle = $adminHandler->canHandle($testCallbackData);
    echo "<div class='test-result " . ($adminCanHandle ? 'success' : 'error') . "'>AdminHandler canHandle('{$testCallbackData}'): " . ($adminCanHandle ? 'true' : 'false') . "</div>";
    
    // Test if AccessLevelHandler can handle the callback
    $accessLevelCanHandle = $accessLevelHandler->canHandle($testCallbackData);
    echo "<div class='test-result " . ($accessLevelCanHandle ? 'success' : 'error') . "'>AccessLevelHandler canHandle('{$testCallbackData}'): " . ($accessLevelCanHandle ? 'true' : 'false') . "</div>";
    
    // Test string functions
    echo "<div class='test-result info'>Testing string functions...</div>";
    
    $startsWithAdmin = str_starts_with($testCallbackData, 'category_Админка');
    echo "<div class='test-result " . ($startsWithAdmin ? 'success' : 'error') . "'>str_starts_with('{$testCallbackData}', 'category_Админка'): " . ($startsWithAdmin ? 'true' : 'false') . "</div>";
    
    $startsWithAdminRequests = str_starts_with($testCallbackData, 'category_Админка_Заявки');
    echo "<div class='test-result " . ($startsWithAdminRequests ? 'success' : 'error') . "'>str_starts_with('{$testCallbackData}', 'category_Админка_Заявки'): " . ($startsWithAdminRequests ? 'true' : 'false') . "</div>";
    
    $exactMatch = $testCallbackData === 'category_Админка_Заявки';
    echo "<div class='test-result " . ($exactMatch ? 'success' : 'error') . "'>Exact match ('{$testCallbackData}' === 'category_Админка_Заявки'): " . ($exactMatch ? 'true' : 'false') . "</div>";
    
    echo "<div class='test-result info'>Testing CallbackHandlerFactory...</div>";
    
    // Test CallbackHandlerFactory
    $factory = new CallbackHandlerFactory();
    $factory->registerHandler($adminHandler);
    $factory->registerHandler($accessLevelHandler);
    
    echo "<div class='test-result info'>Factory registered handlers:</div>";
    echo "<pre>";
    
    // Use reflection to access private handlers property
    $reflection = new ReflectionClass($factory);
    $property = $reflection->getProperty('handlers');
    $property->setAccessible(true);
    $handlers = $property->getValue($factory);
    
    foreach ($handlers as $index => $handler) {
        echo "Handler #{$index}: " . get_class($handler) . " (priority: " . $handler->getPriority() . ")\n";
    }
    echo "</pre>";
    
    echo "<div class='test-result success'>Test completed successfully!</div>";
    
} catch (Exception $e) {
    echo "<div class='test-result error'>Error: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>
</body>
</html>";
?>