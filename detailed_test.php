<?php
// Detailed test to understand the callback handling flow

require_once 'vendor/autoload.php';

// Simulate the TabelHandler extractCategoryFromCallback method
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

// Test the electrical equipment callbacks
echo "=== Detailed Test of Callback Handling ===\n\n";

$callbacks = [
    'cat_ТабЭлОб_РабПоГр',  // Работы по графику
    'cat_ТабЭлОб_ФормИД',   // Формирование ИД
    'cat_ТабЭлОб',          // Main electrical equipment menu
    'cat_ТабЭлОб_Наз'       // Back to main electrical equipment menu
];

foreach ($callbacks as $callbackData) {
    echo "Testing callback: {$callbackData}\n";
    
    // Step 1: Extract category from callback data (TabelHandler method)
    $actualCategory = extractCategoryFromCallback($callbackData);
    echo "  Extracted actual category: '{$actualCategory}'\n";
    
    // Step 2: Check conditions in handleOtherWorkTypes
    // Handle works by schedule for electrical equipment
    if ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
        $actualCategory === 'cat_ТабЭлОб_РабПоГр') {
        echo "  Would handle: Works by schedule for electrical equipment\n";
    }
    
    // Handle ID formation for electrical equipment
    if ($actualCategory === 'Табель_ФормированиеИД_Электрооборудование' || 
        $actualCategory === 'cat_ТабЭлОб_ФормИД') {
        echo "  Would handle: ID formation for electrical equipment\n";
    }
    
    // Handle electrical equipment menu
    if ($actualCategory === 'Табель_Электрооборудование' || 
        $actualCategory === 'cat_ТабЭлОб' ||
        $actualCategory === 'cat_ТабЭлОб_Наз') {
        echo "  Would handle: Electrical equipment menu\n";
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n";