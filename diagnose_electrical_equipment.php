<?php
/**
 * Diagnostic script for electrical equipment button issue
 * 
 * This script helps diagnose why the electrical equipment button is not working correctly.
 */

// Include the necessary files
require_once 'vendor/autoload.php';

// Simulate the callback data that would be sent when the electrical equipment button is pressed
$callbackData = 'category_Табель_Электрооборудование';
$category = 'Табель_ПусконаладочныеРаботы'; // This would be the category passed to the handler

echo "=== Electrical Equipment Button Diagnostic ===\n";
echo "Callback data: $callbackData\n";
echo "Category: $category\n\n";

// Test 1: Check if the callback data matches the expected format
echo "Test 1: Callback data format check\n";
echo "--------------------------------\n";
$hasElectro = strpos($callbackData, 'Электрооборудование') !== false;
$hasEncodedElectro = strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false;
$hasCategoryElectro = strpos($callbackData, 'category_Табель_Электрооборудование') === 0;
$hasEncodedCategoryElectro = strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0;
$exactMatch = $callbackData === 'category_Табель_Электрооборудование';

echo "Contains 'Электрооборудование': " . ($hasElectro ? 'YES' : 'NO') . "\n";
echo "Contains encoded 'Электрооборудование': " . ($hasEncodedElectro ? 'YES' : 'NO') . "\n";
echo "Starts with 'category_Табель_Электрооборудование': " . ($hasCategoryElectro ? 'YES' : 'NO') . "\n";
echo "Starts with encoded category: " . ($hasEncodedCategoryElectro ? 'YES' : 'NO') . "\n";
echo "Exact match: " . ($exactMatch ? 'YES' : 'NO') . "\n";

$specialHandlingMatch = $hasElectro || $hasEncodedElectro || $hasCategoryElectro || $hasEncodedCategoryElectro;
echo "Special handling condition match: " . ($specialHandlingMatch ? 'YES' : 'NO') . "\n\n";

// Test 2: Simulate category extraction
echo "Test 2: Category extraction simulation\n";
echo "-------------------------------------\n";

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

$extractedCategory = extractCategoryFromCallback($callbackData);
echo "Extracted category: '$extractedCategory'\n";

$secondConditionMatch = ($extractedCategory === 'Табель_Электрооборудование' || 
                        $extractedCategory === 'cat_ТабЭлОб' || 
                        $extractedCategory === 'cat_ТабЭлОб_Наз');
echo "Second condition match: " . ($secondConditionMatch ? 'YES' : 'NO') . "\n\n";

// Test 3: Check if either condition would trigger the electrical equipment menu
echo "Test 3: Handler flow check\n";
echo "-------------------------\n";
if ($specialHandlingMatch) {
    echo "✅ Special handling condition would trigger handleElectricalEquipmentMenu()\n";
} else if ($secondConditionMatch) {
    echo "✅ Second condition would trigger handleElectricalEquipmentMenu()\n";
} else {
    echo "❌ Neither condition would trigger handleElectricalEquipmentMenu()\n";
}

echo "\n=== Diagnostic Summary ===\n";
if ($specialHandlingMatch || $secondConditionMatch) {
    echo "✅ The electrical equipment button callback should be handled correctly\n";
    echo "   If it's not working, the issue might be:\n";
    echo "   1. Telegram bot execution environment issue\n";
    echo "   2. Logging not working properly\n";
    echo "   3. MessageService sendMessage() not working correctly\n";
    echo "   4. Actual callback data differs from what we're testing\n";
} else {
    echo "❌ There's an issue with the callback handling logic\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Check the actual callback data being sent by Telegram (use logging)\n";
echo "2. Verify that error_log() is working correctly\n";
echo "3. Check if the MessageService sendMessage() method is working\n";
echo "4. Add more detailed logging to the TabelHandler handle() method\n";

?>