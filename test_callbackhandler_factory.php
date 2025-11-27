<?php
/**
 * Test the CallbackHandlerFactory with the schedule button callback
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Testing CallbackHandlerFactory with Schedule Button ===\n";

// Include the necessary classes
require_once 'vendor/autoload.php';

// Test the callback data
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "Input callback data: {$callbackData}\n";

// Simulate the CallbackHandlerFactory processing
class MockCallbackHandlerFactory {
    public function handleCallback($callbackData) {
        echo "\n--- CallbackHandlerFactory Processing ---\n";
        
        // Fix encoding issues with callback data - only once at the beginning
        $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
        echo "After encoding fix: '{$callbackData}'\n";
        
        // Fix known Cyrillic pattern corruptions - only once at the beginning
        $callbackData = $this->fixKnownCyrillicPatterns($callbackData);
        echo "After Cyrillic fix: '{$callbackData}'\n";
        
        // Extract category from callback data
        $category = $this->extractCategory($callbackData);
        echo "Extracted category: '{$category}'\n";
        
        return $category;
    }
    
    private function extractCategory(string $callbackData): string {
        echo "extractCategory called with: '{$callbackData}'\n";
        
        // Handle short category format for electrical equipment
        if (strpos($callbackData, 'cat_Таб') === 0) {
            echo "Detected short format callback\n";
            
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
                echo "Mapped to category: '{$category}'\n";
                return $category;
            }
            
            // Handle other short formats as needed
            $category = substr($callbackData, strlen('cat_'));
            echo "Extracted from other short format: '{$category}'\n";
            return $category;
        }
        
        // Handle other categories
        if (strpos($callbackData, 'category_') === 0) {
            $category = substr($callbackData, strlen('category_'));
            echo "Extracted from category_: '{$category}'\n";
            return $category;
        }
        
        // Handle date selection callbacks
        if (strpos($callbackData, 'date_') === 0) {
            $category = substr($callbackData, strlen('date_'));
            echo "Extracted from date_: '{$category}'\n";
            return $category;
        }
        
        // Handle FIO selection callbacks
        if (strpos($callbackData, 'fio_') === 0) {
            $category = substr($callbackData, strlen('fio_'));
            echo "Extracted from fio_: '{$category}'\n";
            return $category;
        }
        
        echo "No category found\n";
        return '';
    }
    
    private function fixKnownCyrillicPatterns($str) {
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
}

$factory = new MockCallbackHandlerFactory();
$category = $factory->handleCallback($callbackData);

echo "\n--- Result ---\n";
if ($category === 'Табель_РаботыПоГрафику_Электрооборудование') {
    echo "✅ SUCCESS: Category correctly extracted\n";
    echo "The TabelHandler should handle this directly without showing 'Неизвестная команда табеля'\n";
} else {
    echo "❌ FAILURE: Category not correctly extracted\n";
    echo "This would result in 'Неизвестная команда табеля'\n";
}

echo "\n=== Test Complete ===\n";
?>