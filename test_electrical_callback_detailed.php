<?php

// Detailed test of electrical equipment callback with full flow simulation
echo "=== Detailed Electrical Equipment Callback Test ===\n\n";

// Simulate the callback data that should trigger the electrical equipment menu
$callbackData = 'category_Табель_Электрооборудование';

echo "Input callback data: {$callbackData}\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Hex representation: " . bin2hex($callbackData) . "\n\n";

// Simulate CallbackqueryCommand processing
echo "=== CallbackqueryCommand Processing ===\n";

// Early exit check
if (strpos($callbackData, 'category_') !== 0 && strpos($callbackData, 'adm_req_') !== 0 && strpos($callbackData, 'acc_lvl_') !== 0) {
    echo "CallbackqueryCommand: exiting - no category specified\n";
    exit;
} else {
    echo "CallbackqueryCommand: continuing - category specified\n";
}

// Simulate CallbackHandlerFactory processing
echo "\n=== CallbackHandlerFactory Processing ===\n";

// Store original callback data to prevent double processing
$originalCallbackData = $callbackData;

// Fix encoding issues with callback data - only once at the beginning
$callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
echo "After encoding fix: '{$callbackData}'\n";

// Fix known Cyrillic pattern corruptions - only once at the beginning
$callbackData = fixKnownCyrillicPatterns($callbackData);
echo "After Cyrillic fix: '{$callbackData}'\n";

// Extract category from callback data
$category = extractCategory($callbackData);
echo "Extracted category: '{$category}'\n\n";

// Simulate handler checking in priority order
echo "=== Handler Priority Check ===\n";

// Define handlers with their priorities
$handlers = [
    'AdminHandler' => 60,
    'AccessLevelHandler' => 25,
    'SupervisorHandler' => 25,
    'TabelHandler' => 20,
    'ProfileHandler' => 15,
    'FunctionalDirectionHandler' => 10,
    'HelpHandler' => 1,
    'MainMenuHandler' => 50
];

// Sort handlers by priority (highest first)
uasort($handlers, function($a, $b) {
    return $b - $a;
});

echo "Handler priorities (highest first):\n";
foreach ($handlers as $handler => $priority) {
    echo "  {$handler}: {$priority}\n";
}
echo "\n";

// Test each handler's canHandle method
echo "=== Testing Handler canHandle Methods ===\n";

$selectedHandler = null;

foreach ($handlers as $handler => $priority) {
    $canHandle = false;
    
    switch ($handler) {
        case 'AdminHandler':
            // Check specifically for electrical equipment callback data
            if (strpos($callbackData, 'Электрооборудование') !== false) {
                $canHandle = false; // AdminHandler should not handle electrical equipment
                echo "AdminHandler: detected электрооборудование, returning false\n";
            } else {
                $startsWithCategoryAdmin = str_starts_with($callbackData, 'category_Админка');
                $startsWithCategoryAdm = str_starts_with($callbackData, 'category_Адм_');
                $equalsCategoryMain = $callbackData === 'category_Главное';
                $startsWithAdmReq = str_starts_with($callbackData, 'adm_req_');
                $isAdmReqCallback = $startsWithAdmReq;
                $isAccessLevelCallback = str_starts_with($callbackData, 'acc_lvl_');
                $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && 
                            ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
                
                if ($callbackData === 'category_Админка_ЗапросДоступ') {
                    $canHandle = true;
                }
            }
            break;
            
        case 'TabelHandler':
            // Special handling for electrical equipment - check this first
            if (str_starts_with($callbackData, 'category_Табель_Электрооборудование')) {
                $canHandle = true;
            } else if (strpos($callbackData, 'Электрооборудование') !== false) {
                $canHandle = true;
            } else {
                $canHandle = str_starts_with($callbackData, 'category_Табель');
            }
            break;
            
        case 'ProfileHandler':
            $canHandle = str_starts_with($callbackData, 'category_Профиль');
            break;
            
        case 'HelpHandler':
            $canHandle = str_starts_with($callbackData, 'category_Помощь');
            break;
            
        case 'MainMenuHandler':
            $canHandle = str_starts_with($callbackData, 'category_Главное');
            break;
            
        case 'AccessLevelHandler':
            $canHandle = str_starts_with($callbackData, 'acc_lvl_');
            break;
            
        case 'SupervisorHandler':
            $canHandle = str_starts_with($callbackData, 'category_Супервайзер');
            break;
            
        case 'FunctionalDirectionHandler':
            $canHandle = str_starts_with($callbackData, 'category_Профиль_ФН');
            break;
    }
    
    echo "{$handler} canHandle: " . ($canHandle ? 'true' : 'false') . "\n";
    
    if ($canHandle) {
        echo "  -> {$handler} will handle this callback\n";
        $selectedHandler = $handler;
        break;
    }
}

echo "\n=== Handler Execution ===\n";

if ($selectedHandler === 'TabelHandler') {
    echo "Executing TabelHandler...\n";
    
    // Simulate TabelHandler handle method
    if ($category === 'Табель_Электрооборудование') {
        echo "TabelHandler: handle - special case for electrical equipment category\n";
        echo "TabelHandler: handleElectricalEquipmentMenu will be called\n";
        
        // Simulate handleElectricalEquipmentMenu
        echo "\n=== handleElectricalEquipmentMenu Execution ===\n";
        echo "Calling MessageService->createElectricalEquipmentKeyboard()\n";
        
        // Simulate the keyboard creation
        $keyboard = [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'category_Табель_РаботыПоГрафику_Электрооборудование']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'category_Табель_ФормированиеИД_Электрооборудование']
            ],
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'category_Табель_ИзмСхемПодкл_ЭлектрОб']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'category_Табель_МонтажКабЛиний_ЭлектрОб']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'category_Табель_РасклКабЛиний_ЭлектрОб']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'category_Табель_ДопРаб_ЭлектрОб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'category_Табель_ПодтвДостВехи_ЭлектрОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'category_Табель_Прочее_Электрооборудование']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ПусконаладочныеРаботы']
            ]
        ];
        
        echo "Created electrical equipment keyboard with " . count($keyboard) . " rows\n";
        foreach ($keyboard as $index => $row) {
            echo "Row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        }
        
        echo "\nSending message with keyboard...\n";
        echo "Message: '<b>⚡ Электрооборудование</b>\n\nВыберите тип работ:'\n";
        echo "SUCCESS: Electrical equipment menu should be displayed!\n";
    }
} else {
    echo "No handler found to process the callback\n";
}

echo "\n=== Summary ===\n";
echo "The callback should be handled by TabelHandler and display the electrical equipment menu.\n";
echo "If it's still not working, check for runtime issues like database connection problems.\n";

// Helper functions
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
        'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
        'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
        'Р”Р°С‚Р°' => 'Дата',
        'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
        'Р’С‡РµСЂР°' => 'Вчера',
        'РџСЂРѕС„РёР»СЊ' => 'Профиль',
        'РџСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
        'Р¤РРћ' => 'ФИО',
        'Р¤Рќ' => 'ФН',
        'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
        'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
        'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
        'РўР°Р±РµР»СЊ' => 'Табель',
        'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
        'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
        'РџРѕРЅРёР·РёС‚СЊ' => 'Понизить',
        // Add missing patterns for commissioning works
        'РџРќР›' => 'ПНЛ',
        'РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
        'Р Р°Р±РѕС‚С‹' => 'Работы',
        // Add missing patterns for the main buttons (order matters - more specific first)
        'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
        'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
        // Add individual word patterns
        'РјРѕРЅС‚Р°Р¶' => 'монтаж',
        // Add pattern for commissioning works abbreviation
        'РџРќР ' => 'ПНР',
        // Add pattern for preparation
        'РџРѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
        // Add pattern for electrical equipment - this is the key fix
        'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
        // Add pattern for full electrical equipment callback - this is the key fix
        'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
        // Add additional patterns for double-encoded Cyrillic that might be causing issues
        'Табель_Электрооборудование' => 'Табель_Электрооборудование',
        'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    return $str;
}

function extractCategory(string $callbackData): string {
    // Special handling for electrical equipment - check this first and return immediately
    if (strpos($callbackData, 'category_Табель_Электрооборудование') === 0) {
        $category = 'Табель_Электрооборудование';
        return $category;
    }
    
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        
        // Fix encoding for category
        $category = mb_convert_encoding($category, 'UTF-8', 'auto');
        
        // Fix known Cyrillic pattern corruptions for category as well
        $category = fixKnownCyrillicPatterns($category);
        
        // Special case: if category is exactly 'Табель_Электрооборудование', return it immediately
        if ($category === 'Табель_Электрооборудование') {
            return $category;
        }
        
        // Additional check for electrical equipment after fixes
        if (strpos($category, 'Табель_Электрооборудование') === 0) {
            $finalCategory = 'Табель_Электрооборудование';
            return $finalCategory;
        }
        
        return $category;
    }
    
    return $callbackData;
}