<?php
// Debug script to test commissioning callback handling

require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

echo "=== Debug Commissioning Callback Test ===\n";

// Create instances of required services
$databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
$projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
$tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
$adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
    $databaseService,
    $messageService,
    $projectSelector,
    $tabelEntry,
    $adminService
);

// Test the commissioning works button callback
$callbackData = 'category_Табель_ПусконаладочныеРаботы';
echo "Testing callback data: $callbackData\n";

// Check if TabelHandler can handle this callback
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'YES' : 'NO') . "\n";

// Test the old callback that was causing conflict
$oldCallbackData = 'category_Табель_ПНЛ';
echo "\nTesting old callback data: $oldCallbackData\n";
$canHandleOld = $tabelHandler->canHandle($oldCallbackData);
echo "TabelHandler canHandle old callback result: " . ($canHandleOld ? 'YES' : 'NO') . "\n";

// Test MessageService keyboard creation
echo "\n=== MessageService Keyboard Test ===\n";
$commissioningKeyboard = $messageService->createCommissioningKeyboard();
echo "Commissioning keyboard:\n";
print_r($commissioningKeyboard);

$commissioningWorksKeyboard = $messageService->createCommissioningWorksKeyboard();
echo "\nCommissioning works keyboard:\n";
print_r($commissioningWorksKeyboard);

echo "\n=== Test Complete ===\n";