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

echo "Tracing actual flow for 'Работы по графику' button...\n";

// Test the specific callback for "Работы по графику"
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Callback data: {$callbackData}\n";

// Simulate what happens in the handle method
echo "\n=== Simulating handle method ===\n";

// Extract the actual category from the callback data (as done in handle method)
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('extractCategoryFromCallback');
$method->setAccessible(true);
$actualCategory = $method->invokeArgs($tabelHandler, [$callbackData]);
echo "Actual category (from extractCategoryFromCallback): '{$actualCategory}'\n";

// But the handle method is called with a different category parameter
// Let's see what that might be by checking the mapping
echo "\nChecking category mapping...\n";
echo "The handle method would be called with the original category parameter\n";
echo "But extractCategoryFromCallback gives us: '{$actualCategory}'\n";

// Now let's see what happens in handleOtherWorkTypes with both values
echo "\n=== Simulating handleOtherWorkTypes with actualCategory ===\n";
$category = $actualCategory; // This is what's passed to handleOtherWorkTypes

// Mock conversation object
class MockConversation {
    public $notes = [];
    
    public function update() {
        // Mock update method
    }
}

$conversation = new MockConversation();
$conversation->notes['project_code'] = 'TEST-001';

// Check each condition in order as they appear in the code
echo "Checking conditions in handleOtherWorkTypes with category = '{$category}':\n";

// 1. Schedule works condition (should match)
$scheduleWorksCondition = ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
    $category === 'cat_ТабЭлОб_РабПоГр');
echo "1. Schedule works condition: " . ($scheduleWorksCondition ? 'MATCHED' : 'NOT MATCHED') . "\n";

// 2. Electrical equipment menu condition (should NOT match now)
$electricalEquipmentCondition = ($category === 'Табель_Электрооборудование' || 
    $category === 'cat_ТабЭлОб' ||
    $category === 'cat_ТабЭлОб_Наз' ||
    $category === 'Табель_ПусконаладочныеРаботы' ||
    (strpos($category, 'Табель_Электрооборудование') !== false) ||
    (strpos($category, 'cat_ТабЭлОб') === 0 && $category !== 'cat_ТабЭлОб_РабПоГр'));
echo "2. Electrical equipment menu condition: " . ($electricalEquipmentCondition ? 'MATCHED' : 'NOT MATCHED') . "\n";

if ($scheduleWorksCondition) {
    echo "\nSUCCESS: Schedule works condition matches! This should handle the 'Работы по графику' button correctly.\n";
} else {
    echo "\nISSUE: Schedule works condition does not match. Let's check why...\n";
    echo "Category: {$category}\n";
    echo "Expected: 'Табель_РаботыПоГрафику_Электрооборудование' or 'cat_ТабЭлОб_РабПоГр'\n";
    echo "Match 1: " . ($category === 'Табель_РаботыПоГрафику_Электрооборудование' ? 'YES' : 'NO') . "\n";
    echo "Match 2: " . ($category === 'cat_ТабЭлОб_РабПоГр' ? 'YES' : 'NO') . "\n";
}

echo "\nTest completed.\n";