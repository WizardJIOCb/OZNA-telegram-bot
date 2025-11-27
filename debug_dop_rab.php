<?php
/**
 * Debug script for "Дополнительные работы" callback processing
 */

echo "Debugging 'Дополнительные работы' callback processing...\n\n";

// Simulate the callback data
$callbackData = 'cat_ТабЭлОб_ДопРаб';
echo "1. Original callback data: {$callbackData}\n";

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

$actualCategory = extractCategoryFromCallback($callbackData);
echo "2. Extracted category: {$actualCategory}\n";

// Simulate the handleOtherWorkTypes method logic
$category = $actualCategory;

echo "3. Processing in handleOtherWorkTypes:\n";

// Check if it matches the additional works condition
if ($category === 'Табель_ДопРаб_ЭлектрОб' || 
    $category === 'Табель_ДополнительныеРаботы_Электрооборудование' ||
    $category === 'cat_ТабЭлОб_ДопРаб') {
    echo "   ✅ Matched additional works condition\n";
    echo "   Should call handleAdditionalWorksElectricalMenu()\n";
} else {
    echo "   ❌ Did not match additional works condition\n";
    
    // Check if it contains электрооборудование
    if (strpos($category, 'Электрооборудование') !== false) {
        echo "   Category contains 'Электрооборудование'\n";
        // Additional check
        if (strpos($category, 'Табель_Электрооборудование') !== false || $category === 'Электрооборудование') {
            echo "   Would redirect to electrical equipment menu\n";
        }
    }
    
    // Check if it's treated as activity confirmation
    $strippedCategory = str_replace('Табель_', '', $category);
    echo "   Stripped category: {$strippedCategory}\n";
    
    // Activity types array (simplified)
    $activityTypes = [
        'ДопРаб_ЭлектрОб',  // This is in the array
        'РаботыПоГрафику_Электрооборудование',
        'ФормированиеИД_Электрооборудование'
    ];
    
    // Submenu categories array (simplified)
    $submenuCategories = [
        'Электрооборудование'
    ];
    
    if (in_array($strippedCategory, $activityTypes) && !in_array($strippedCategory, $submenuCategories)) {
        echo "   ❌ Would be treated as activity confirmation (this is the bug!)\n";
        echo "   Because '{$strippedCategory}' is in activityTypes but not in submenuCategories\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Analysis complete!\n";
echo "The issue is that 'ДопРаб_ЭлектрОб' is in activityTypes\n";
echo "but the specific additional works condition should match first.\n";
echo str_repeat("=", 50) . "\n";