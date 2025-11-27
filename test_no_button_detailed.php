<?php

// Test the detailed flow for the "No" button in confirmation dialogs

echo "=== Testing 'No' Button Detailed Flow ===\n\n";

// 1. Simulate the callback data from the "No" button
$callbackData = 'category_Табель';
echo "1. Callback data from 'No' button: '{$callbackData}'\n";
echo "   Length: " . strlen($callbackData) . " bytes\n";
echo "   Hex: " . bin2hex($callbackData) . "\n\n";

// 2. Simulate category extraction (like in CallbackHandlerFactory)
$category = '';
if (strpos($callbackData, 'category_') === 0) {
    $category = substr($callbackData, strlen('category_'));
}
echo "2. Extracted category: '{$category}'\n\n";

// 3. Test TabelHandler canHandle method
function tabelHandlerCanHandle($callbackData) {
    $result = strpos($callbackData, 'category_Табель') === 0 || strpos($callbackData, 'cat_Таб') === 0;
    return $result;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "3. TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n\n";

// 4. Test TabelHandler extractCategoryFromCallback method
function extractCategoryFromCallback($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for electrical equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
            'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
            'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
            'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
            'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
            'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

$actualCategory = extractCategoryFromCallback($callbackData);
echo "4. TabelHandler extractCategoryFromCallback result: '{$actualCategory}'\n\n";

// 5. Test if the actual category matches the main menu condition
$isMainMenu = ($actualCategory === 'Табель');
echo "5. Is main menu category: " . ($isMainMenu ? 'true' : 'false') . "\n\n";

// 6. Test handler priorities and canHandle methods
echo "6. Handler priority check:\n";

// Simulate handler priorities
$handlers = [
    'MainMenuHandler' => 50,
    'AdminHandler' => 25,
    'TabelHandler' => 20
];

echo "   Sorted by priority:\n";
uasort($handlers, function($a, $b) { return $b - $a; });
foreach ($handlers as $handler => $priority) {
    echo "     {$priority}: {$handler}\n";
}

// 7. Test canHandle for each handler
echo "\n7. canHandle results:\n";

// MainMenuHandler canHandle
$mainMenuCanHandle = $callbackData === 'category_Главное';
echo "   MainMenuHandler canHandle('{$callbackData}'): " . ($mainMenuCanHandle ? 'true' : 'false') . "\n";

// AdminHandler canHandle
$startsWithCategoryAdmin = strpos($callbackData, 'category_Админка') === 0;
$startsWithCategoryAdm = strpos($callbackData, 'category_Адм_') === 0;
$equalsCategoryMain = $callbackData === 'category_Главное';
$startsWithAdmReq = strpos($callbackData, 'adm_req_') === 0;
$isAdmReqCallback = $startsWithAdmReq;
$isAccessLevelCallback = strpos($callbackData, 'acc_lvl_') === 0;
$adminCanHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
echo "   AdminHandler canHandle('{$callbackData}'): " . ($adminCanHandle ? 'true' : 'false') . "\n";

// TabelHandler canHandle
$tabelCanHandle = tabelHandlerCanHandle($callbackData);
echo "   TabelHandler canHandle('{$callbackData}'): " . ($tabelCanHandle ? 'true' : 'false') . "\n";

// 8. Determine which handler will process the callback
echo "\n8. Handler selection:\n";
$selectedHandler = '';
foreach ($handlers as $handler => $priority) {
    $canHandle = false;
    switch ($handler) {
        case 'MainMenuHandler':
            $canHandle = $callbackData === 'category_Главное';
            break;
        case 'AdminHandler':
            $startsWithCategoryAdmin = strpos($callbackData, 'category_Админка') === 0;
            $startsWithCategoryAdm = strpos($callbackData, 'category_Адм_') === 0;
            $equalsCategoryMain = $callbackData === 'category_Главное';
            $startsWithAdmReq = strpos($callbackData, 'adm_req_') === 0;
            $isAdmReqCallback = $startsWithAdmReq;
            $isAccessLevelCallback = strpos($callbackData, 'acc_lvl_') === 0;
            $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
            break;
        case 'TabelHandler':
            $canHandle = tabelHandlerCanHandle($callbackData);
            break;
    }
    
    echo "   Checking {$handler}: " . ($canHandle ? 'CAN HANDLE' : 'cannot handle') . "\n";
    if ($canHandle && $selectedHandler === '') {
        $selectedHandler = $handler;
        echo "   -> SELECTED {$handler} to handle the callback\n";
    }
}

echo "\n9. Expected behavior:\n";
echo "   - {$selectedHandler} should handle the callback\n";
if ($selectedHandler === 'TabelHandler') {
    echo "   - It should show the main tabel menu because actualCategory is '{$actualCategory}' and isMainMenu is " . ($isMainMenu ? 'true' : 'false') . "\n";
}

echo "\n=== Test Complete ===\n";