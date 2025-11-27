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

echo "Final test of 'Работы по графику' button fix...\n";

// Test the actual callback that users are experiencing
$testCallback = 'cat_ТабЭлОб_РабПоГр';  // Short format used in the UI

echo "\n--- Testing with actual callback: {$testCallback} ---\n";

// Test category extraction
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('extractCategoryFromCallback');
$method->setAccessible(true);
$category = $method->invokeArgs($tabelHandler, [$testCallback]);
echo "Extracted category: {$category}\n";

// Test the specific schedule works condition
$scheduleWorksCondition = ($category === 'Табель_РаботыПоГрафику_Электрооборудование');
echo "Schedule works condition: " . ($scheduleWorksCondition ? 'MATCH' : 'NO MATCH') . "\n";

// Test the electrical equipment menu condition
$electricalMenuCondition = ($category === 'Табель_Электрооборудование' || 
                           $category === 'cat_ТабЭлОб' ||
                           $category === 'cat_ТабЭлОб_Наз' ||
                           $category === 'Табель_ПусконаладочныеРаботы' ||
                           (strpos($category, 'Табель_Электрооборудование') !== false));

echo "Electrical equipment menu condition: " . ($electricalMenuCondition ? 'MATCH' : 'NO MATCH') . "\n";
echo "  - strpos(category, 'Табель_Электрооборудование'): " . 
     (strpos($category, 'Табель_Электрооборудование') !== false ? 'YES' : 'NO') . "\n";

echo "\n--- Determining which handler would be called ---\n";
if ($scheduleWorksCondition) {
    echo "✓ Schedule works handler would be called (CORRECT!)\n";
} elseif ($electricalMenuCondition) {
    echo "✗ Electrical equipment menu handler would be called (INCORRECT!)\n";
} else {
    echo "? Unknown command handler would be called (INCORRECT!)\n";
}

echo "\nTest completed. The fix should now work correctly.\n";