<?php

// Test the actual flow for the "No" button in confirmation dialogs
// This simulates what happens when the "No" button is pressed

echo "=== Testing Actual 'No' Button Flow ===\n\n";

// 1. Simulate the callback data from the "No" button
$callbackData = 'category_Табель';
echo "1. Callback data from 'No' button: '{$callbackData}'\n";

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

// 5. Test the main menu condition
$isMainMenu = ($actualCategory === 'Табель');
echo "5. Is main menu category: " . ($isMainMenu ? 'true' : 'false') . "\n\n";

// 6. Simulate what happens in TabelHandler::handle method
echo "6. Simulating TabelHandler::handle method:\n";

if ($actualCategory === 'Табель') {
    echo "   -> Matched main menu condition\n";
    echo "   -> Would call handleMainMenu()\n";
    echo "   -> Would show main tabel menu\n\n";
    
    // Simulate what handleMainMenu does
    echo "7. Simulating handleMainMenu():\n";
    echo "   -> Would create work type keyboard\n";
    echo "   -> Would send message: '<b>📊 Табель</b>\\n\\nВыберите вид работ:'\n\n";
} else {
    echo "   -> Did not match main menu condition\n";
    echo "   -> Would continue to other conditions\n\n";
}

// 8. Test the work type keyboard creation
echo "8. Simulating work type keyboard creation:\n";
$workTypeKeyboard = [
    [
        ['text' => '💼 Мобильная бригада', 'callback_data' => 'category_Табель_Мобильная_Бригада']
    ],
    [
        ['text' => '🔧 Отдел ШМР', 'callback_data' => 'category_Табель_Шеф_монтаж']
    ],
    [
        ['text' => '⚙️ Отдел ПНР', 'callback_data' => 'category_Табель_Пусконаладка']
    ],
    [
        ['text' => '📊 Текущая деятельность', 'callback_data' => 'category_Табель_Текущая']
    ]
];

echo "   Work type keyboard created with " . count($workTypeKeyboard) . " rows:\n";
foreach ($workTypeKeyboard as $index => $row) {
    echo "     Row " . ($index + 1) . ": " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== Test Complete ===\n";
echo "Conclusion: The 'No' button should work correctly and show the main tabel menu.\n";
echo "If it's not working, there might be a runtime error or issue with dependencies.\n";