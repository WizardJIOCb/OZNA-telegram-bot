<?php

require_once 'config.php';
require_once 'custom_autoload.php';

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
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "Debugging the 'Работы по графику' button issue...\n";

// Test the actual callback that users are experiencing
$testCallback = 'cat_ТабЭлОб_РабПоГр';  // Short format used in the UI

echo "\n--- Testing with actual callback: {$testCallback} ---\n";
echo "Callback length: " . strlen($testCallback) . " bytes (within 64-byte limit)\n";

// Test canHandle
$canHandle = $tabelHandler->canHandle($testCallback);
echo "TabelHandler->canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";

// Test category extraction
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('extractCategoryFromCallback');
$method->setAccessible(true);
$category = $method->invokeArgs($tabelHandler, [$testCallback]);
echo "Extracted category: {$category}\n";

// Test if this matches our condition
$scheduleWorksCondition = ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
                          $testCallback === 'cat_ТабЭлОб_РабПоГр');

echo "Matches schedule works condition: " . ($scheduleWorksCondition ? 'YES' : 'NO') . "\n";

if ($scheduleWorksCondition) {
    echo "\n✓ This should be handled by the schedule works handler!\n";
} else {
    echo "\n✗ This would fall through to the unknown command handler.\n";
    
    // Let's see what condition it would match
    $electricalMenuCondition = ($category === 'Табель_Электрооборудование' || 
                               $testCallback === 'cat_ТабЭлОб' ||
                               $testCallback === 'cat_ТабЭлОб_Наз' ||
                               $category === 'Табель_ПусконаладочныеРаботы' ||
                               (strpos($category, 'Табель_Электрооборудование') !== false) ||
                               (strpos($testCallback, 'cat_ТабЭлОб') === 0 && $testCallback !== 'cat_ТабЭлОб_РабПоГр'));
    
    echo "Would match electrical menu condition: " . ($electricalMenuCondition ? 'YES' : 'NO') . "\n";
    
    if ($electricalMenuCondition) {
        echo "This explains the issue - the callback is being caught by the general electrical menu handler\n";
        echo "instead of the specific schedule works handler!\n";
    }
}

echo "\nDebug completed.\n";