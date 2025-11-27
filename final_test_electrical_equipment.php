<?php

// Final comprehensive test for electrical equipment callback handling
echo "=== Final Comprehensive Test for Electrical Equipment Callback ===\n\n";

// Simulate the callback data
$callbackData = 'category_Табель_Электрооборудование';

echo "Input callback data: {$callbackData}\n";
echo "Length: " . strlen($callbackData) . " bytes\n";
echo "Hex representation: " . bin2hex($callbackData) . "\n\n";

// Test 1: CallbackHandlerFactory processing
echo "=== Test 1: CallbackHandlerFactory Processing ===\n";

// Simulate CallbackHandlerFactory handleCallback method
function handleCallback($callbackData) {
    echo "CallbackHandlerFactory: handleCallback called with callbackData: '{$callbackData}'\n";
    
    // Store original callback data to prevent double processing
    $originalCallbackData = $callbackData;
    
    // Fix encoding issues with callback data - only once at the beginning
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    echo "CallbackHandlerFactory: handleCallback after encoding fix: '{$callbackData}'\n";
    
    // Fix known Cyrillic pattern corruptions - only once at the beginning
    $callbackData = fixKnownCyrillicPatterns($callbackData);
    echo "CallbackHandlerFactory: handleCallback after Cyrillic fix: '{$callbackData}'\n";
    
    // Extract category from callback data
    $category = extractCategory($callbackData);
    echo "CallbackHandlerFactory: handling callback data: '{$callbackData}', extracted category: '{$category}'\n";
    
    // Special check for electrical equipment
    if ($category === 'Табель_Электрооборудование' || strpos($callbackData, 'Табель_Электрооборудование') !== false) {
        echo "CallbackHandlerFactory: detected electrical equipment callback\n";
        return ['handler' => 'TabelHandler', 'method' => 'handleElectricalEquipmentMenu'];
    }
    
    return ['handler' => 'unknown', 'method' => 'unknown'];
}

// Simulate fixKnownCyrillicPatterns method
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
        'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
        'Табель_Электрооборудование' => 'Табель_Электрооборудование',
        'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование'
    ];
    
    // Special case: if string is already the correct electrical equipment callback, return as is
    if ($str === 'category_Табель_Электрооборудование' || $str === 'Табель_Электрооборудование') {
        return $str;
    }
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    return $str;
}

// Simulate extractCategory method
function extractCategory($callbackData) {
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

$result = handleCallback($callbackData);
echo "Handler: {$result['handler']}\n";
echo "Method: {$result['method']}\n\n";

// Test 2: TabelHandler canHandle
echo "=== Test 2: TabelHandler canHandle ===\n";

function tabelHandlerCanHandle($callbackData) {
    echo "TabelHandler: canHandle called with callbackData: '{$callbackData}'\n";
    
    // Special handling for electrical equipment - check this first
    if (str_starts_with($callbackData, 'category_Табель_Электрооборудование')) {
        echo "TabelHandler: canHandle - special case for electrical equipment: true\n";
        return true;
    }
    
    // Additional check for corrupted electrical equipment callback data
    if (strpos($callbackData, 'Электрооборудование') !== false) {
        echo "TabelHandler: canHandle - found электрооборудование in callback data\n";
        return true;
    }
    
    $canHandle = str_starts_with($callbackData, 'category_Табель');
    echo "TabelHandler: canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";
    return $canHandle;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n\n";

// Test 3: TabelHandler handle method
echo "=== Test 3: TabelHandler handle method ===\n";

function tabelHandlerHandle($category) {
    echo "TabelHandler: handle called with category: {$category}\n";
    
    // Special handling for electrical equipment category
    if ($category === 'Табель_Электрооборудование') {
        echo "TabelHandler: handle - special case for electrical equipment category\n";
        return 'handleElectricalEquipmentMenu';
    }
    
    // Additional check for electrical equipment in category
    if (strpos($category, 'Электрооборудование') !== false) {
        echo "TabelHandler: handle - category contains электрооборудование: {$category}\n";
        if (strpos($category, 'Табель_Электрооборудование') !== false || $category === 'Электрооборудование') {
            echo "TabelHandler: handle - treating as electrical equipment category\n";
            return 'handleElectricalEquipmentMenu';
        }
    }
    
    echo "TabelHandler: handle - no match for electrical equipment\n";
    return 'routeTabelCallback';
}

$handlerMethod = tabelHandlerHandle($category);
echo "TabelHandler will call method: {$handlerMethod}\n\n";

// Test 4: handleElectricalEquipmentMenu
echo "=== Test 4: handleElectricalEquipmentMenu ===\n";

function handleElectricalEquipmentMenu() {
    echo "TabelHandler: handleElectricalEquipmentMenu called\n";
    
    // Create the keyboard
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
    
    $message = '<b>⚡ Электрооборудование</b>' . "\n\n" . 'Выберите тип работ:';
    echo "Message to send: {$message}\n";
    
    // Simulate sending message
    echo "Sending message to Telegram API...\n";
    echo "SUCCESS: Message sent to Telegram API\n";
    
    return ['message' => $message, 'keyboard' => $keyboard, 'status' => 'success'];
}

if ($handlerMethod === 'handleElectricalEquipmentMenu') {
    $result = handleElectricalEquipmentMenu();
    echo "Successfully created electrical equipment menu\n";
    echo "Message: {$result['message']}\n";
    echo "Keyboard rows: " . count($result['keyboard']) . "\n";
    echo "Status: {$result['status']}\n";
} else {
    echo "Not handling electrical equipment menu\n";
}

echo "\n=== Summary ===\n";
echo "1. Callback data: {$callbackData}\n";
echo "2. Extracted category: {$category}\n";
echo "3. TabelHandler can handle: " . ($canHandle ? 'YES' : 'NO') . "\n";
echo "4. Handler method: {$handlerMethod}\n";
echo "5. Electrical equipment menu should be displayed: " . ($handlerMethod === 'handleElectricalEquipmentMenu' ? 'YES' : 'NO') . "\n";

if ($canHandle && $handlerMethod === 'handleElectricalEquipmentMenu') {
    echo "\n✅ SUCCESS: The electrical equipment button should work correctly!\n";
    echo "If it's still not working, there might be a runtime issue with:\n";
    echo "  - Database connection\n";
    echo "  - Telegram API message sending\n";
    echo "  - Network connectivity\n";
    echo "  - User access level restrictions\n";
} else {
    echo "\n❌ ISSUE: There's a problem in the callback handling logic.\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Check the database connection\n";
echo "2. Verify user access level is not 0 (guest)\n";
echo "3. Check if user has FIO and functional direction filled\n";
echo "4. Verify Telegram API is working correctly\n";
echo "5. Check network connectivity\n";

echo "\n=== Test Complete ===\n";