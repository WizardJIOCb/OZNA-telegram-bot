<?php
// Include the autoloader or require necessary files
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test construction works "Дорога" button callback
echo "Testing construction works 'Дорога' button...\n";
$callbackData = 'cat_ТабСтройМонт_Дорога_yes';
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler canHandle('{$callbackData}'): " . ($canHandle ? 'YES' : 'NO') . "\n";

// Test if it's recognized as activity execution
echo "Testing isActivityExecution...\n";
// Use reflection to access private method
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('isActivityExecution');
$method->setAccessible(true);
$isActivityExecution = $method->invoke($tabelHandler, $callbackData);
echo "isActivityExecution('{$callbackData}'): " . ($isActivityExecution ? 'YES' : 'NO') . "\n";

// Test construction works "Подготовка к служебной поездке" button callback
echo "\nTesting construction works 'Подготовка к служебной поездке' button...\n";
$callbackData2 = 'cat_ТабПодгПоездки_yes';
$canHandle2 = $tabelHandler->canHandle($callbackData2);
echo "TabelHandler canHandle('{$callbackData2}'): " . ($canHandle2 ? 'YES' : 'NO') . "\n";

// Test if it's recognized as activity execution
echo "Testing isActivityExecution...\n";
$isActivityExecution2 = $method->invoke($tabelHandler, $callbackData2);
echo "isActivityExecution('{$callbackData2}'): " . ($isActivityExecution2 ? 'YES' : 'NO') . "\n";

echo "\nTest completed!\n";