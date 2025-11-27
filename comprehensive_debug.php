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

echo "Comprehensive debugging of 'Работы по графику' button issue...\n";

// Test the specific callback for "Работы по графику"
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Testing callback: {$callbackData}\n";

// Test canHandle
$canHandle = $tabelHandler->canHandle($callbackData);
echo "canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";

// Test category extraction
$reflection = new ReflectionClass($tabelHandler);
$method = $reflection->getMethod('extractCategoryFromCallback');
$method->setAccessible(true);
$category = $method->invokeArgs($tabelHandler, [$callbackData]);
echo "Extracted category: {$category}\n";

// Let's manually simulate what happens in the handle method
echo "\nSimulating handle method flow...\n";
echo "handle called with category: '{$category}' and callbackData: '{$callbackData}'\n";

// Special handling for electrical equipment with encoding issues
if (strpos($callbackData, 'Электрооборудование') !== false || 
    strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
    strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
    strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
    echo "Special handling for electrical equipment callback - would call handleElectricalEquipmentMenu\n";
} else {
    echo "No special handling for electrical equipment\n";
}

// Extract the actual category from the callback data
$actualCategory = $method->invokeArgs($tabelHandler, [$callbackData]);
echo "Extracted actual category: '{$actualCategory}'\n";

// Handle electrical equipment menu with short callback format
if ($actualCategory === 'Табель_Электрооборудование' || 
    $actualCategory === 'cat_ТабЭлОб' || 
    $actualCategory === 'cat_ТабЭлОб_Наз') {
    echo "Would handle electrical equipment menu\n";
}

// Now let's simulate the handleOtherWorkTypes method step by step
echo "\nSimulating handleOtherWorkTypes method step by step...\n";

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
echo "Checking conditions in handleOtherWorkTypes:\n";

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

// 3. Check if we would reach the end (unknown command)
if (!$scheduleWorksCondition && !$electricalEquipmentCondition) {
    echo "3. Would reach 'Неизвестная команда табеля'\n";
} else {
    echo "3. Would NOT reach 'Неизвестная команда табеля'\n";
}

echo "\nDetailed analysis:\n";
echo "Category: {$category}\n";
echo "strpos('cat_ТабЭлОб', category): " . strpos($category, 'cat_ТабЭлОб') . "\n";
echo "category !== 'cat_ТабЭлОб_РабПоГр': " . ($category !== 'cat_ТабЭлОб_РабПоГр' ? 'true' : 'false') . "\n";

echo "\nTest completed.\n";