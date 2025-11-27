<?php
/**
 * Full flow debug for the schedule button issue
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Full Flow Debug for Schedule Button ===\n";

// Test the exact callback that should be sent by the button
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "1. Input callback data: {$callbackData}\n";
echo "   Length: " . strlen($callbackData) . " bytes\n";
echo "   Hex: " . bin2hex($callbackData) . "\n\n";

// Simulate CallbackHandlerFactory processing step by step
echo "2. Simulating CallbackHandlerFactory processing...\n";

// Fix encoding issues (as done in CallbackHandlerFactory)
$processedCallbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
echo "   After encoding fix: '{$processedCallbackData}'\n";

// Fix Cyrillic patterns (as done in CallbackHandlerFactory)
$processedCallbackData = fixKnownCyrillicPatterns($processedCallbackData);
echo "   After Cyrillic fix: '{$processedCallbackData}'\n";

// Extract category (as done in CallbackHandlerFactory)
$category = extractCategory($processedCallbackData);
echo "   Extracted category: '{$category}'\n\n";

// Check if TabelHandler would handle this
echo "3. Checking if TabelHandler would handle this category...\n";
if ($category === 'Табель_РаботыПоГрафику_Электрооборудование') {
    echo "   ✅ TabelHandler should handle this directly\n";
    echo "   Expected behavior: Direct execution without confirmation\n";
} else {
    echo "   ❌ TabelHandler would not recognize this category\n";
    echo "   This would result in 'Неизвестная команда табеля'\n";
}

echo "\n=== Debug Complete ===\n";

// Helper functions
function fixKnownCyrillicPatterns($str) {
    // Replace the known corrupted patterns
    $replacements = [
        'Р Р°Р±РѕС‚С‹РїРѕРіСЂР°С„РёРєСѓ' => 'РаботыПоГрафику',
        'Р Р°Р±РџРѕР“СЂ' => 'РаботыПоГрафику',
        'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование'
    ];
    
    foreach ($replacements as $corrupted => $fixed) {
        if (strpos($str, $corrupted) !== false) {
            $str = str_replace($corrupted, $fixed, $str);
        }
    }
    
    return $str;
}

function extractCategory($callbackData) {
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
    
    // Handle other categories
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle date selection callbacks
    if (strpos($callbackData, 'date_') === 0) {
        return substr($callbackData, strlen('date_'));
    }
    
    // Handle FIO selection callbacks
    if (strpos($callbackData, 'fio_') === 0) {
        return substr($callbackData, strlen('fio_'));
    }
    
    return '';
}
?>