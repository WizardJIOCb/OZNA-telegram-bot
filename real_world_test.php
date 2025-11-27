<?php

require_once 'config.php';
require_once 'custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Entities\CallbackQuery;

// Create mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$adminService = new AdminService($messageService);
$tabelEntry = new TabelEntry($databaseService, $messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

echo "Real world test for 'Работы по графику' button...\n";

// Simulate the exact callback that would come from Telegram
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Received callback data: {$callbackData}\n";

// Test canHandle first
$canHandle = $tabelHandler->canHandle($callbackData);
echo "TabelHandler->canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";

if (!$canHandle) {
    echo "ERROR: TabelHandler cannot handle this callback!\n";
    exit(1);
}

// Create a mock CallbackQuery object as it would exist in the real application
$callbackQuery = new CallbackQuery([
    'data' => $callbackData,
    'message' => [
        'chat' => [
            'id' => 123456789
        ]
    ],
    'from' => [
        'id' => 987654321,
        'first_name' => 'TestUser'
    ]
]);

// Mock conversation
class MockConversation {
    public $notes = ['project_code' => 'TEST-001'];
    
    public function update() {
        // Mock update method
    }
}

$conversation = new MockConversation();

// Now let's trace exactly what happens in the handle method
echo "\n=== Tracing handle method execution ===\n";

// Extract category as done in the handle method
$reflection = new ReflectionClass($tabelHandler);
$extractMethod = $reflection->getMethod('extractCategoryFromCallback');
$extractMethod->setAccessible(true);
$extractedCategory = $extractMethod->invokeArgs($tabelHandler, [$callbackData]);
echo "Extracted category: '{$extractedCategory}'\n";

// Simulate the handle method flow step by step
echo "\n--- Simulating handle method conditions ---\n";

// 1. Special handling for electrical equipment with encoding issues
$specialHandling = (strpos($callbackData, 'Электрооборудование') !== false || 
    strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
    strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
    strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0);
echo "1. Special electrical equipment handling: " . ($specialHandling ? 'YES' : 'NO') . "\n";

// 2. Handle main tabel menu
$isMainTabelMenu = ($extractedCategory === 'Табель');
echo "2. Main tabel menu: " . ($isMainTabelMenu ? 'YES' : 'NO') . "\n";

// 3. Handle electrical equipment menu with short callback format
$isElectricalEquipmentMenu = ($extractedCategory === 'Табель_Электрооборудование' || 
    $extractedCategory === 'cat_ТабЭлОб' || 
    $extractedCategory === 'cat_ТабЭлОб_Наз');
echo "3. Electrical equipment menu: " . ($isElectricalEquipmentMenu ? 'YES' : 'NO') . "\n";

// 4. Other conditions would be checked here...

// 5. Finally, handleOtherWorkTypes would be called
echo "5. Would call handleOtherWorkTypes with category: '{$extractedCategory}'\n";

// Now simulate handleOtherWorkTypes with the extracted category
echo "\n=== Simulating handleOtherWorkTypes with extractedCategory ===\n";

// Check the conditions in handleOtherWorkTypes
$scheduleWorksCondition = ($extractedCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
    $extractedCategory === 'cat_ТабЭлОб_РабПоГр');
echo "Schedule works condition: " . ($scheduleWorksCondition ? 'MATCHED' : 'NOT MATCHED') . "\n";

$electricalEquipmentCondition = ($extractedCategory === 'Табель_Электрооборудование' || 
    $extractedCategory === 'cat_ТабЭлОб' ||
    $extractedCategory === 'cat_ТабЭлОб_Наз' ||
    $extractedCategory === 'Табель_ПусконаладочныеРаботы' ||
    (strpos($extractedCategory, 'Табель_Электрооборудование') !== false) ||
    (strpos($extractedCategory, 'cat_ТабЭлОб') === 0 && $extractedCategory !== 'cat_ТабЭлОб_РабПоГр'));
echo "Electrical equipment menu condition: " . ($electricalEquipmentCondition ? 'MATCHED' : 'NOT MATCHED') . "\n";

if ($scheduleWorksCondition) {
    echo "\nSUCCESS: The 'Работы по графику' button should be handled correctly!\n";
    echo "It would execute the activity directly without showing 'Неизвестная команда табеля'.\n";
} else {
    echo "\nERROR: The 'Работы по графику' button is not being handled correctly.\n";
    echo "It would fall through to show 'Неизвестная команда табеля'.\n";
    
    // Let's debug why
    echo "\nDebugging why schedule works condition doesn't match:\n";
    echo "extractedCategory: '{$extractedCategory}'\n";
    echo "Expected: 'Табель_РаботыПоГрафику_Электрооборудование' or 'cat_ТабЭлОб_РабПоГр'\n";
    echo "Match 1 (Табель_РаботыПоГрафику_Электрооборудование): " . 
         ($extractedCategory === 'Табель_РаботыПоГрафику_Электрооборудование' ? 'YES' : 'NO') . "\n";
    echo "Match 2 (cat_ТабЭлОб_РабПоГр): " . 
         ($extractedCategory === 'cat_ТабЭлОб_РабПоГр' ? 'YES' : 'NO') . "\n";
}

echo "\nTest completed.\n";