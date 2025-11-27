<?php

// Test to debug the "No" button issue

echo "=== Debugging 'No' Button Issue ===\n\n";

// 1. Check the TabelHandler priority
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';

// Create mock services
$databaseService = new Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
$messageService = new Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
$projectSelector = new Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
$tabelEntry = new Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService);
$adminService = new Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

echo "1. TabelHandler priority: " . $tabelHandler->getPriority() . "\n";

// 2. Test canHandle method
$callbackData = 'category_Табель';
$canHandle = $tabelHandler->canHandle($callbackData);
echo "2. TabelHandler canHandle('{$callbackData}'): " . ($canHandle ? 'true' : 'false') . "\n";

// 3. Test the flow with a mock callback
echo "3. Testing callback flow:\n";

// Simulate what happens in CallbackHandlerFactory
echo "   Callback data: {$callbackData}\n";
echo "   Extracted category: " . substr($callbackData, strlen('category_')) . "\n";

echo "\n=== Test Complete ===\n";