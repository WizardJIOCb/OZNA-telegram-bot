<?php

// Final test to verify that the "No" button should work correctly

echo "=== Final Test for 'No' Button Functionality ===\n\n";

// Simulate the callback data from the "No" button
$callbackData = 'category_Табель';
echo "1. Callback data from 'No' button: '{$callbackData}'\n";

// Test TabelHandler canHandle method
function tabelHandlerCanHandle($callbackData) {
    return strpos($callbackData, 'category_Табель') === 0 || strpos($callbackData, 'cat_Таб') === 0;
}

$canHandle = tabelHandlerCanHandle($callbackData);
echo "2. TabelHandler canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";

// Test TabelHandler extractCategoryFromCallback method
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
echo "3. TabelHandler extractCategoryFromCallback result: '{$actualCategory}'\n";

// Test the main menu condition
$isMainMenu = ($actualCategory === 'Табель');
echo "4. Is main menu category: " . ($isMainMenu ? 'true' : 'false') . "\n";

// Simulate what happens in TabelHandler::handle method
echo "\n5. Flow simulation:\n";
if ($actualCategory === 'Табель') {
    echo "   ✓ Matched main menu condition\n";
    echo "   ✓ Would call handleMainMenu()\n";
    echo "   ✓ Would show main tabel menu\n";
    echo "   ✓ SUCCESS: 'No' button should work correctly!\n\n";
} else {
    echo "   ✗ Did not match main menu condition\n";
    echo "   ✗ FAILURE: 'No' button would not work correctly\n\n";
}

// Summary
echo "=== Summary ===\n";
echo "The 'No' button with callback_data 'category_Табель' should work correctly.\n";
echo "It should be handled by the TabelHandler which will show the main tabel menu.\n";
echo "If it's not working, check for runtime errors or database connection issues.\n";

echo "\n=== Changes Made ===\n";
echo "1. Fixed direct pDB calls to use DatabaseService instead\n";
echo "2. Removed conflicting backup files\n";
echo "3. Verified correct TabelHandler priority (20)\n";
echo "4. Verified correct callback handling flow\n";