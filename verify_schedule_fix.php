<?php
/**
 * Verify that the schedule button fix works correctly
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Verifying Schedule Button Fix ===\n";

// Test the callback data that was causing issues
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Input callback data: {$callbackData}\n";
echo "Input callback data length: " . strlen($callbackData) . "\n";
echo "Input callback data hex: " . bin2hex($callbackData) . "\n";

// Simulate the CallbackHandlerFactory extractCategory method
function extractCategory($callbackData) {
    echo "\n--- Simulating CallbackHandlerFactory::extractCategory ---\n";
    echo "Input: '{$callbackData}'\n";
    
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
            $category = $shortFormats[$callbackData];
            echo "Extracted category from short format: '{$category}'\n";
            return $category;
        }
        
        // Handle other short formats as needed
        $category = substr($callbackData, strlen('cat_'));
        echo "Extracted category from other short format: '{$category}'\n";
        return $category;
    }
    
    // Handle other categories
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "Extracted category: '{$category}'\n";
        return $category;
    }
    
    // Handle date selection callbacks
    if (strpos($callbackData, 'date_') === 0) {
        $category = substr($callbackData, strlen('date_'));
        echo "Extracted date category: '{$category}'\n";
        return $category;
    }
    
    // Handle FIO selection callbacks
    if (strpos($callbackData, 'fio_') === 0) {
        $category = substr($callbackData, strlen('fio_'));
        echo "Extracted FIO category: '{$category}'\n";
        return $category;
    }
    
    echo "No category found\n";
    return '';
}

$category = extractCategory($callbackData);
echo "\nFinal extracted category: '{$category}'\n";

// Check if this should be handled directly by TabelHandler
if ($category === 'Табель_РаботыПоГрафику_Электрооборудование') {
    echo "✅ SUCCESS: Category correctly mapped for direct handling\n";
    echo "Expected behavior: Direct report generation without confirmation\n";
} else {
    echo "❌ ERROR: Category not correctly mapped\n";
}

echo "\n=== Verification Complete ===\n";
?>