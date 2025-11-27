<?php
// Test script to debug fire alarm callback processing

// Simulate the callback data that would be sent when clicking "Пожарная сигнализация" button
$callbackData = 'category_Табель_ПожарнаяСигнализация';

echo "Testing fire alarm callback processing\n";
echo "Callback data: {$callbackData}\n\n";

// Simulate TabelHandler canHandle method
function canHandle($callbackData) {
    echo "TabelHandler: canHandle called with callbackData: '{$callbackData}'\n";
    
    // Special handling for electrical equipment callback with encoding issues
    // Check for both correctly encoded and corrupted versions
    if (strpos($callbackData, 'Электрооборудование') !== false || 
        strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
        strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
        strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
        echo "TabelHandler: canHandle detected electrical equipment callback\n";
        return true;
    }
    
    // Check for both original and short callback formats
    $result = strpos($callbackData, 'category_Табель') === 0 || strpos($callbackData, 'cat_Таб') === 0;
    echo "TabelHandler: canHandle result for '{$callbackData}': " . ($result ? 'true' : 'false') . "\n";
    return $result;
}

// Simulate TabelHandler extractCategoryFromCallback method
function extractCategoryFromCallback($callbackData) {
    echo "TabelHandler: extractCategoryFromCallback called with callbackData: '{$callbackData}'\n";
    
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "TabelHandler: extractCategoryFromCallback extracted category: '{$category}'\n";
        return $category;
    }
    
    // Handle short category format for electrical equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормИД_ЭлектрОб',
            'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзмСхПд_ЭлектрОб',
            'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтКбЛн_ЭлектрОб',
            'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РаскКбЛн_ЭлектрОб',
            'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
            'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтВех_ЭлектрОб',
            'cat_ТабЭлОб_Проч' => 'Табель_Прочее_ЭлектрОб',
            // Handle confirmation callbacks as well
            'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
            'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes',
            'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
            'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ЭлектрОб_yes',
            'cat_ТабЭлОб_РаскКбЛн_yes' => 'Табель_РаскКбЛн_ЭлектрОб_yes',
            'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes',
            'cat_ТабЭлОб_ПодтВех_yes' => 'Табель_ПодтВех_ЭлектрОб_yes',
            'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            $category = $shortFormats[$callbackData];
            echo "TabelHandler: extractCategoryFromCallback mapped short format to: '{$category}'\n";
            return $category;
        }
        
        // Handle other short formats as needed
        $category = substr($callbackData, strlen('cat_'));
        echo "TabelHandler: extractCategoryFromCallback extracted short format category: '{$category}'\n";
        return $category;
    }
    
    echo "TabelHandler: extractCategoryFromCallback returning empty string\n";
    return '';
}

// Test the methods
$canHandleResult = canHandle($callbackData);
echo "\n";

$category = extractCategoryFromCallback($callbackData);
echo "\n";

echo "Final results:\n";
echo "canHandle: " . ($canHandleResult ? 'true' : 'false') . "\n";
echo "extracted category: '{$category}'\n";

// Simulate handleOtherWorkTypes method
function handleOtherWorkTypes($category) {
    echo "\nTabelHandler: handleOtherWorkTypes called with category: {$category}\n";
    
    // Handle fire alarm equipment menu
    if ($category === 'Табель_ПожарнаяСигнализация') {
        echo "TabelHandler: handleOtherWorkTypes: handling fire alarm equipment menu\n";
        echo "This should call handleFireAlarmEquipmentMenu()\n";
        return true;
    }
    
    echo "TabelHandler: handleOtherWorkTypes: no match found\n";
    return false;
}

$handled = handleOtherWorkTypes($category);
echo "handleOtherWorkTypes result: " . ($handled ? 'handled' : 'not handled') . "\n";

?>