<?php
// Test script to check the automation equipment callback handling
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "=== Testing Automation Equipment Callback Handling ===\n";

// Test the CallbackHandlerFactory
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test canHandle method
$callbackData = 'category_Табель_Автоматизация';
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler canHandle('$callbackData'): " . ($canHandle ? 'true' : 'false') . "\n";

// Test with "Работы по графику" automation equipment
$callbackData2 = 'category_Табель_РабПоГр_Автоматизация';
$canHandle2 = $tabelHandler->canHandle($callbackData2);
echo "TabelHandler canHandle('$callbackData2'): " . ($canHandle2 ? 'true' : 'false') . "\n";

// Test with confirmation callback
$callbackData3 = 'category_Табель_РабПоГр_Автом_yes';
$canHandle3 = $tabelHandler->canHandle($callbackData3);
echo "TabelHandler canHandle('$callbackData3'): " . ($canHandle3 ? 'true' : 'false') . "\n";

// Test extractCategoryFromCallback method
function extractCategoryFromCallback($callbackData, $tabelHandler) {
    // Use reflection to access private method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    return $method->invokeArgs($tabelHandler, [$callbackData]);
}

$category = extractCategoryFromCallback($callbackData, $tabelHandler);
echo "Extracted category from '$callbackData': $category\n";

$category2 = extractCategoryFromCallback($callbackData2, $tabelHandler);
echo "Extracted category from '$callbackData2': $category2\n";

// Test isActivityExecution method
function isActivityExecution($category, $tabelHandler) {
    // Use reflection to access private method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('isActivityExecution');
    $method->setAccessible(true);
    return $method->invokeArgs($tabelHandler, [$category]);
}

$isActivityExecution = isActivityExecution($callbackData3, $tabelHandler);
echo "isActivityExecution('$callbackData3'): " . ($isActivityExecution ? 'true' : 'false') . "\n";

echo "\nTest completed!\n";
?>