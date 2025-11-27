<?php
// Test script to simulate the full mobile brigade flow

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
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

echo "=== Full Mobile Brigade Flow Test ===\n\n";

// Create all required services
$databaseService = new DatabaseService();
$messageService = new MessageService();
$projectSelector = new ProjectSelector();
$tabelEntry = new TabelEntry($databaseService, $messageService);
$adminService = new AdminService($messageService);

// Create all handlers
$adminHandler = new AdminHandler($adminService, $messageService);
$accessLevelHandler = new AccessLevelHandler($adminService, $messageService);
$supervisorHandler = new SupervisorHandler($messageService, $adminService);
$tabelHandler = new TabelHandler($databaseService, $messageService, $projectSelector, $tabelEntry, $adminService);
$profileHandler = new ProfileHandler($messageService);
$functionalDirectionHandler = new FunctionalDirectionHandler($messageService);
$helpHandler = new HelpHandler($messageService);
$mainMenuHandler = new MainMenuHandler($messageService);

// Create CallbackHandlerFactory and register all handlers
$callbackHandlerFactory = new CallbackHandlerFactory();

// Register handlers in the same order as in CallbackqueryCommand
$callbackHandlerFactory->registerHandler($adminHandler);
$callbackHandlerFactory->registerHandler($accessLevelHandler);
$callbackHandlerFactory->registerHandler($supervisorHandler);
$callbackHandlerFactory->registerHandler($tabelHandler);
$callbackHandlerFactory->registerHandler($profileHandler);
$callbackHandlerFactory->registerHandler($functionalDirectionHandler);
$callbackHandlerFactory->registerHandler($helpHandler);
$callbackHandlerFactory->registerHandler($mainMenuHandler);

echo "Handler registration order and priorities:\n";
echo "========================================\n";

// Use reflection to access private handlers property
$reflection = new ReflectionClass($callbackHandlerFactory);
$handlersProperty = $reflection->getProperty('handlers');
$handlersProperty->setAccessible(true);
$registeredHandlers = $handlersProperty->getValue($callbackHandlerFactory);

foreach ($registeredHandlers as $index => $handler) {
    echo ($index + 1) . ". " . get_class($handler) . " (priority: " . $handler->getPriority() . ")\n";
}

echo "\n";

// Test 1: Check the work type keyboard creation
echo "Test 1: Work Type Keyboard Creation\n";
echo "==================================\n";

$workTypeKeyboard = $messageService->createWorkTypeKeyboard();
echo "Work type keyboard created with " . count($workTypeKeyboard) . " rows:\n";
foreach ($workTypeKeyboard as $index => $row) {
    echo "  Row " . ($index + 1) . ": " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    
    // Check if mobile brigade button exists
    if (isset($row[0]) && isset($row[0]['text']) && $row[0]['text'] === '💼 Мобильная бригада') {
        echo "  -> ✅ Mobile brigade button found\n";
        echo "  -> Callback data: " . $row[0]['callback_data'] . "\n";
    }
}

echo "\n";

// Test 2: Simulate clicking the mobile brigade button
echo "Test 2: Simulating Mobile Brigade Button Click\n";
echo "=============================================\n";

// Get the callback data from the mobile brigade button
$mobileBrigadeCallbackData = null;
foreach ($workTypeKeyboard as $row) {
    if (isset($row[0]) && isset($row[0]['text']) && $row[0]['text'] === '💼 Мобильная бригада') {
        $mobileBrigadeCallbackData = $row[0]['callback_data'];
        break;
    }
}

if ($mobileBrigadeCallbackData) {
    echo "Callback data from button: '{$mobileBrigadeCallbackData}'\n";
    echo "Length: " . strlen($mobileBrigadeCallbackData) . " bytes\n";
    echo "Hex: " . bin2hex($mobileBrigadeCallbackData) . "\n\n";
    
    // Simulate the CallbackHandlerFactory processing
    echo "Simulating CallbackHandlerFactory processing:\n";
    
    // Store original callback data
    $originalCallbackData = $mobileBrigadeCallbackData;
    
    // Early electrical equipment check
    if (strpos($mobileBrigadeCallbackData, 'Электрооборудование') !== false || 
        strpos($mobileBrigadeCallbackData, 'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
        strpos($mobileBrigadeCallbackData, 'category_Табель_Электрооборудование') === 0 ||
        strpos($mobileBrigadeCallbackData, 'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
        echo "  1. Early electrical equipment check: MATCH (would correct)\n";
        $mobileBrigadeCallbackData = 'category_Табель_Электрооборудование';
    } else {
        echo "  1. Early electrical equipment check: NO MATCH\n";
    }
    
    // Fix encoding issues
    $mobileBrigadeCallbackData = mb_convert_encoding($mobileBrigadeCallbackData, 'UTF-8', 'auto');
    echo "  2. After encoding fix: '{$mobileBrigadeCallbackData}'\n";
    
    // Fix known Cyrillic patterns
    $mobileBrigadeCallbackData = fixKnownCyrillicPatterns($mobileBrigadeCallbackData);
    echo "  3. After Cyrillic fix: '{$mobileBrigadeCallbackData}'\n";
    
    // Extract category
    $category = '';
    if (strpos($mobileBrigadeCallbackData, 'category_') === 0) {
        $category = substr($mobileBrigadeCallbackData, strlen('category_'));
        echo "  4. Extracted category: '{$category}'\n";
    } else {
        echo "  4. No category prefix found\n";
        $category = $mobileBrigadeCallbackData;
    }
    
    echo "\n";
    
    // Test each handler's canHandle method in priority order
    echo "Testing handlers in priority order:\n";
    echo "==================================\n";
    
    $handlerFound = false;
    foreach ($registeredHandlers as $index => $handler) {
        $handlerName = get_class($handler);
        $canHandle = $handler->canHandle($mobileBrigadeCallbackData);
        echo ($index + 1) . ". " . $handlerName . " (priority: " . $handler->getPriority() . "): " . ($canHandle ? 'YES' : 'NO') . "\n";
        
        if ($canHandle && !$handlerFound) {
            echo "   -> ✅ This handler will process the callback\n";
            $handlerFound = true;
            
            // Check if it's the TabelHandler
            if ($handlerName === 'Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler') {
                echo "   -> 🎯 TabelHandler will handle mobile brigade callback\n";
                
                // Check if the category matches mobile brigade conditions
                if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
                    echo "   -> ✅ Category matches mobile brigade condition\n";
                    echo "   -> 📞 TabelHandler should call handleMobileBrigadeDateSelection()\n";
                } else {
                    echo "   -> ❌ Category does not match mobile brigade condition\n";
                    echo "   -> Expected: 'Табель_Мобильная_Бригада' or 'Табель_МобБриг'\n";
                    echo "   -> Got: '{$category}'\n";
                }
            }
        } elseif ($canHandle && $handlerFound) {
            echo "   -> This handler could also handle it, but won't be called due to priority\n";
        }
    }
    
    if (!$handlerFound) {
        echo "❌ No handler found to process the callback!\n";
    }
} else {
    echo "❌ Mobile brigade button not found in work type keyboard!\n";
}

echo "\n=== Test Complete ===\n";

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