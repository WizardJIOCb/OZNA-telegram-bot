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

echo "Debugging 'Неизвестная команда табеля' issue...\n";

// Test all electrical equipment callbacks to see which one might be causing the issue
$callbacks = [
    'cat_ТабЭлОб',
    'cat_ТабЭлОб_Наз',
    'cat_ТабЭлОб_РабПоГр',
    'cat_ТабЭлОб_ФормИД',
    'cat_ТабЭлОб_ИзмСхПд',
    'cat_ТабЭлОб_МонтКбЛн',
    'cat_ТабЭлОб_РаскКбЛн',
    'cat_ТабЭлОб_ДопРаб',
    'cat_ТабЭлОб_ПодтВех',
    'cat_ТабЭлОб_Проч',
    'category_Табель_Электрооборудование',
    'category_Табель_РаботыПоГрафику_Электрооборудование',
    'category_Табель_ФормированиеИД_Электрооборудование',
    'category_Табель_ИзменениеСхемПодключения_Электрооборудование',
    'category_Табель_МонтажКабельныхЛиний_Электрооборудование',
    'category_Табель_РасключениеКабельныхЛиний_Электрооборудование',
    'category_Табель_ДопРаб_ЭлектрОб',
    'category_Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
    'category_Табель_Прочее_Электрооборудование'
];

echo "Testing all electrical equipment callbacks...\n";
foreach ($callbacks as $callback) {
    echo "\nTesting callback: {$callback}\n";
    
    // Test canHandle
    $canHandle = $tabelHandler->canHandle($callback);
    echo "  canHandle: " . ($canHandle ? 'true' : 'false') . "\n";
    
    // Test category extraction
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    $category = $method->invokeArgs($tabelHandler, [$callback]);
    echo "  extracted category: {$category}\n";
}

// Let's also test the specific case from the electrical equipment menu
echo "\n\nTesting electrical equipment menu flow...\n";

// Simulate what happens when user clicks on electrical equipment menu
$callbackData = 'cat_ТабЭлОб'; // This should work
echo "Testing electrical equipment menu callback: {$callbackData}\n";

$canHandle = $tabelHandler->canHandle($callbackData);
echo "canHandle: " . ($canHandle ? 'true' : 'false') . "\n";

if ($canHandle) {
    // Extract category
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    $category = $method->invokeArgs($tabelHandler, [$callbackData]);
    echo "extracted category: {$category}\n";
    
    // Test if this category would be handled in handleOtherWorkTypes
    echo "Checking if category would be handled in handleOtherWorkTypes...\n";
    
    // These are the conditions from handleOtherWorkTypes method
    $conditions = [
        'electrical_equipment_menu' => ($category === 'Табель_Электрооборудование' || 
            $category === 'cat_ТабЭлОб' ||
            $category === 'cat_ТабЭлОб_Наз'),
        'schedule_works' => ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
            $category === 'cat_ТабЭлОб_РабПоГр'),
        'id_formation' => ($category === 'Табель_ФормированиеИД_Электрооборудование' || 
            $category === 'cat_ТабЭлОб_ФормИД'),
        'milestone_confirmation' => ($category === 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование' || 
            $category === 'Табель_ПодтвДостВехи_ЭлектрОб' ||
            $category === 'cat_ТабЭлОб_ПодтВех'),
        'additional_works' => ($category === 'Табель_ДопРаб_ЭлектрОб' || 
            $category === 'Табель_ДополнительныеРаботы_Электрооборудование' ||
            $category === 'cat_ТабЭлОб_ДопРаб'),
        'scheme_changes' => ($category === 'Табель_ИзменениеСхемПодключения_Электрооборудование' || 
            $category === 'Табель_ИзмСхемПодкл_ЭлектрОб' ||
            $category === 'cat_ТабЭлОб_ИзмСхПд'),
        'cable_installation' => ($category === 'Табель_МонтажКабельныхЛиний_Электрооборудование' || 
            $category === 'Табель_МонтажКабЛиний_ЭлектрОб' ||
            $category === 'cat_ТабЭлОб_МонтКбЛн'),
        'cable_wiring' => ($category === 'Табель_РасключениеКабельныхЛиний_Электрооборудование' || 
            $category === 'Табель_РасклКабЛиний_ЭлектрОб' ||
            $category === 'cat_ТабЭлОб_РаскКбЛн'),
        'other_works' => ($category === 'Табель_Прочее_Электрооборудование' || 
            $category === 'cat_ТабЭлОб_Проч'),
        'back_to_additional_works' => ($category === 'Табель_ЭлектрОб'),
        'back_to_electrical_menu' => ($category === 'Табель_ПусконаладочныеРаботы'),
    ];
    
    $matched = false;
    foreach ($conditions as $conditionName => $result) {
        if ($result) {
            echo "  -> Matches condition: {$conditionName}\n";
            $matched = true;
        }
    }
    
    if (!$matched) {
        echo "  -> Would fall through to 'Неизвестная команда табеля'\n";
    } else {
        echo "  -> Would NOT fall through to 'Неизвестная команда табеля'\n";
    }
}

echo "\nTest completed.\n";