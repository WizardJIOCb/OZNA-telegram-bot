<?php
// Test script to simulate actual callback processing with potential encoding issues

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;

echo "=== Actual Callback Processing Test ===\n\n";

// Create all required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create TabelHandler
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);

// Create CallbackHandlerFactory
$callbackHandlerFactory = new CallbackHandlerFactory();

// Register TabelHandler
$callbackHandlerFactory->registerHandler($tabelHandler);

// Simulate different possible callback data scenarios
$testCallbacks = [
    // Normal case
    'category_Табель_Мобильная_Бригада',
    
    // Potentially corrupted cases (based on previous logs)
    'category_РўР°Р±РµР»СЊ_РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°',
    
    // Another potential corruption
    'category_Табель_РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°',
];

foreach ($testCallbacks as $index => $callbackData) {
    echo "Test " . ($index + 1) . ": '{$callbackData}'\n";
    echo "Length: " . strlen($callbackData) . " bytes\n";
    echo "Hex: " . bin2hex($callbackData) . "\n";
    
    // Simulate the CallbackHandlerFactory processing
    echo "Processing steps:\n";
    
    // 1. Early electrical equipment check
    if (strpos($callbackData, 'Электрооборудование') !== false || 
        strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
        strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
        strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
        echo "  1. Early electrical equipment check: MATCH\n";
        // Force correction
        $callbackData = 'category_Табель_Электрооборудование';
        echo "  -> Corrected to: '{$callbackData}'\n";
    } else {
        echo "  1. Early electrical equipment check: NO MATCH\n";
    }
    
    // 2. Encoding fix
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    echo "  2. After encoding fix: '{$callbackData}'\n";
    
    // 3. Cyrillic pattern fix (simulate the method)
    $callbackData = fixKnownCyrillicPatterns($callbackData);
    echo "  3. After Cyrillic fix: '{$callbackData}'\n";
    
    // 4. Extract category
    $category = '';
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "  4. Extracted category: '{$category}'\n";
    } else {
        echo "  4. No category prefix found\n";
        $category = $callbackData;
    }
    
    // 5. Test TabelHandler canHandle
    $canHandle = $tabelHandler->canHandle($callbackData);
    echo "  5. TabelHandler->canHandle: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    if ($canHandle) {
        echo "  -> TabelHandler would process this callback\n";
        
        // Check if it matches mobile brigade condition
        if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
            echo "  -> Would call handleMobileBrigadeDateSelection()\n";
        } else {
            echo "  -> Would NOT call handleMobileBrigadeDateSelection()\n";
            echo "  -> Category: '{$category}'\n";
        }
    } else {
        echo "  -> TabelHandler would NOT process this callback\n";
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n";

// Copy of the fixKnownCyrillicPatterns method for testing
function fixKnownCyrillicPatterns($str) {
    // Special case: if string is already the correct electrical equipment callback, return as is
    if ($str === 'category_Табель_Электрооборудование' || $str === 'Табель_Электрооборудование') {
        return $str;
    }
    
    // Handle the specific corrupted electrical equipment callback data
    if ($str === 'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') {
        return 'category_Табель_Электрооборудование';
    }
    
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера',
        'РЎРѕСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤РРћ' => 'ФИО',
        'Р¤РЊ' => 'ФН',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
        'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
        'РЎСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
        'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
        'РўР°Р±РµР»СЊ' => 'Табель',
        'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
        'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
        'РџРѕРЅРёР·РёССЊ' => 'Понизить',
        // Add missing patterns for commissioning works
        'РџРќР›' => 'ПНЛ',
        'РЎСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
        'Р Р°Р±Р±РѕС‚С‹' => 'Работы',
        // Add missing patterns for the main buttons (order matters - more specific first)
        'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
        'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
        // Add individual word patterns
        'РјРѕРЅС‚Р°Р¶' => 'монтаж',
        // Add pattern for commissioning works abbreviation
        'РџРќР ' => 'ПНР',
        // Add pattern for preparation
        'Р РѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
        // Add pattern for electrical equipment - this is the key fix
        'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
        // Add pattern for full electrical equipment callback - this is the key fix
        'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
        // Add additional patterns for double-encoded Cyrillic that might be causing issues
        'Табель_Электрооборудование' => 'Табель_Электрооборудование',
        'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование',
        // Handle mixed encoding cases
        'РўР°Р±РµР»СЊ_Электрооборудование' => 'Табель_Электрооборудование',
        // Handle the specific partially corrupted case
        'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРинРµ' => 'Электрооборудование',
        // Handle the exact double-encoded pattern we're seeing in the test
        'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
        // Handle mobile brigade corruption
        'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    return $str;
}

?>