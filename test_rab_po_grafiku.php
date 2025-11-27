<?php
/**
 * Test script for "Работы по графику" and "Формирование ИД" buttons
 */

echo "Testing 'Работы по графику' and 'Формирование ИД' buttons...\n\n";

// Test "Работы по графику"
$callbackData1 = 'cat_ТабЭлОб_РабПоГр';
echo "1. Testing 'Работы по графику' button:\n";
echo "   Callback data: {$callbackData1}\n";

// Simulate the extractCategoryFromCallback method
function extractCategoryFromCallback(string $callbackData): string
{
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

$actualCategory1 = extractCategoryFromCallback($callbackData1);
echo "   Extracted category: {$actualCategory1}\n";

// Simulate the handleOtherWorkTypes method for "Работы по графику"
function handleOtherWorkTypes(string $category): string
{
    echo "   Processing category: {$category}\n";
    
    // Handle works by schedule for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_РаботыПоГрафику_Электрооборудование' || 
        $category === 'cat_ТабЭлОб_РабПоГр') {
        echo "   ✅ Matched 'Работы по графику' condition\n";
        return "showConfirmationMessage";
    }

    // Handle ID formation for electrical equipment (must come before general electrical equipment check)
    if ($category === 'Табель_ФормированиеИД_Электрооборудование' || 
        $category === 'cat_ТабЭлОб_ФормИД') {
        echo "   ✅ Matched 'Формирование ИД' condition\n";
        return "showConfirmationMessage";
    }
    
    // Other conditions...
    return "otherProcessing";
}

$result1 = handleOtherWorkTypes($actualCategory1);
echo "   Result: {$result1}\n";

// Test "Формирование ИД"
$callbackData2 = 'cat_ТабЭлОб_ФормИД';
echo "\n2. Testing 'Формирование ИД' button:\n";
echo "   Callback data: {$callbackData2}\n";

$actualCategory2 = extractCategoryFromCallback($callbackData2);
echo "   Extracted category: {$actualCategory2}\n";

$result2 = handleOtherWorkTypes($actualCategory2);
echo "   Result: {$result2}\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test complete!\n";
echo "Both buttons should show confirmation messages.\n";
echo str_repeat("=", 50) . "\n";