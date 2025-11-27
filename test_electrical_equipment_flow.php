<?php

// Test the electrical equipment callback handling flow
$callbackData = 'category_Табель_Электрооборудование';

echo "Testing callback data: $callbackData\n\n";

// Test the special handling condition (lines 130-134 in TabelHandler)
$specialHandling = (strpos($callbackData, 'Электрооборудование') !== false || 
            strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
            strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
            strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0);

echo "Special handling condition result: " . ($specialHandling ? 'true' : 'false') . "\n";

// Test the extractCategoryFromCallback logic
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
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

$actualCategory = extractCategoryFromCallback($callbackData);
echo "Extracted category: $actualCategory\n";

// Test the second condition (lines 157-164 in TabelHandler)
$secondCondition = ($actualCategory === 'Табель_Электрооборудование' || 
            $actualCategory === 'cat_ТабЭлОб' || 
            $actualCategory === 'cat_ТабЭлОб_Наз');

echo "Second condition result: " . ($secondCondition ? 'true' : 'false') . "\n";

?>