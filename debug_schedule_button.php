<?php
// Simple debug script to test the schedule button callback processing

require_once 'vendor/autoload.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/DatabaseService.php';
require_once 'CustomCommands/Services/ProjectSelector.php';
require_once 'CustomCommands/Services/TabelEntry.php';
require_once 'CustomCommands/Services/AdminService.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

// Mock services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler instance
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Test callback data
$testCallbacks = [
    'cat_ТабЭлОб_РабПоГр',
    'category_Табель_РаботыПоГрафику_Электрооборудование'
];

echo "Testing callback processing for schedule button:\n\n";

foreach ($testCallbacks as $callbackData) {
    echo "Testing callback: {$callbackData}\n";
    
    // Check if handler can handle this callback
    $canHandle = $tabelHandler->canHandle($callbackData);
    echo "Can handle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    // Test category extraction
    // We'll use reflection to access the private method
    $reflection = new ReflectionClass($tabelHandler);
    $method = $reflection->getMethod('extractCategoryFromCallback');
    $method->setAccessible(true);
    $category = $method->invoke($tabelHandler, $callbackData);
    echo "Extracted category: {$category}\n";
    
    echo "------------------------\n";
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Debug Schedule Button Processing ===\n";

// Simulate the callback data that should be received
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Input callback data: {$callbackData}\n";
echo "Input callback data length: " . strlen($callbackData) . "\n";
echo "Input callback data hex: " . bin2hex($callbackData) . "\n";

// Test the category extraction
$category = '';
if (strpos($callbackData, 'cat_') === 0) {
    // Handle short format callbacks
    $shortCallbackMap = [
        'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
        'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
        'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
        'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
        'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
        'cat_ТабЭлОб_ДопРаб' => 'Табель_ДополнительныеРаботы_Электрооборудование',
        'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
        'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование',
        'cat_ТабЭлОб' => 'Табель_Электрооборудование',
        'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование'
    ];
    
    if (isset($shortCallbackMap[$callbackData])) {
        $category = $shortCallbackMap[$callbackData];
        echo "Mapped category from short callback: {$category}\n";
    } else {
        echo "No mapping found for short callback\n";
    }
} else {
    // Handle regular category format
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "Extracted category from regular callback: {$category}\n";
    }
}

echo "Final category: {$category}\n";

// Test if this category should be handled directly
if ($category === 'Табель_РаботыПоГрафику_Электрооборудование') {
    echo "✅ This category should be handled directly without confirmation\n";
    echo "Expected behavior: Direct report generation\n";
} else {
    echo "❌ This category is not recognized for direct handling\n";
}

// Test Cyrillic pattern fixing
function fixKnownCyrillicPatterns($str) {
    echo "Testing Cyrillic pattern fixing...\n";
    echo "Input: {$str}\n";
    echo "Input hex: " . bin2hex($str) . "\n";
    
    $replacements = [
        'Р Р°Р±РѕС‚С‹РїРѕРіСЂР°С„РёРєСѓ' => 'РаботыПоГрафику',
        'Р Р°Р±РџРѕР“СЂ' => 'РаботыПоГрафику',
        'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            echo "Found corrupted pattern '{$corrupted}', replacing with '{$fixed}'\n";
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    echo "Output: {$str}\n";
    echo "Output hex: " . bin2hex($str) . "\n";
    return $str;
}

// Test with potentially corrupted data
$corruptedCallback = 'cat_ТабЭлОб_Р Р°Р±РџРѕР“СЂ';
echo "\nTesting with potentially corrupted callback: {$corruptedCallback}\n";
fixKnownCyrillicPatterns($corruptedCallback);

echo "=== Debug Complete ===\n";
?>