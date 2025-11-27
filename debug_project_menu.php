<?php

// Debug script to understand what's happening with the project management menu
echo "Debugging Project Management Menu Issue\n";
echo "=====================================\n\n";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/hook.php';

echo "1. Testing if required classes exist...\n";

// Test if classes exist
$classesToTest = [
    'Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectManagementHandler',
    'Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler',
    'Longman\TelegramBot\Commands\SystemCommands\Services\MessageService',
    'Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory'
];

foreach ($classesToTest as $class) {
    if (class_exists($class)) {
        echo "  ✓ {$class} exists\n";
    } else {
        echo "  ✗ {$class} does NOT exist\n";
    }
}

echo "\n2. Testing ProjectManagementHandler...\n";

try {
    // Create MessageService
    $messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    echo "  ✓ MessageService created\n";
    
    // Create ProjectManagementHandler
    $projectHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectManagementHandler($messageService);
    echo "  ✓ ProjectManagementHandler created\n";
    
    // Test getPriority
    $priority = $projectHandler->getPriority();
    echo "  ✓ getPriority() returned: {$priority}\n";
    
    // Test canHandle
    $testCallbacks = [
        'category_УправлениеПроектом',
        'category_УправлениеПроектом_Персонал',
        'category_Админка'
    ];
    
    foreach ($testCallbacks as $callback) {
        $canHandle = $projectHandler->canHandle($callback);
        echo "  ✓ canHandle('{$callback}') returned: " . ($canHandle ? 'true' : 'false') . "\n";
    }
    
} catch (Exception $e) {
    echo "  ✗ Error creating ProjectManagementHandler: " . $e->getMessage() . "\n";
}

echo "\n3. Testing MessageService...\n";

try {
    // Test createMainMenuReplyKeyboard
    $replyKeyboard = $messageService->createMainMenuReplyKeyboard(4); // Super admin level
    echo "  ✓ createMainMenuReplyKeyboard(4) returned " . count($replyKeyboard) . " rows\n";
    
    foreach ($replyKeyboard as $rowIndex => $row) {
        $buttons = [];
        foreach ($row as $button) {
            $buttons[] = $button['text'];
        }
        echo "    Row {$rowIndex}: " . implode(', ', $buttons) . "\n";
    }
    
} catch (Exception $e) {
    echo "  ✗ Error testing MessageService: " . $e->getMessage() . "\n";
}

echo "\nDebug completed!\n";