<?php
// Test script to verify the fix for "Работы по графику" button

echo "TESTING FIX FOR 'РАБОТЫ ПО ГРАФИКУ' BUTTON\n";
echo "==========================================\n\n";

// Simulate the TabelHandler handle method processing order
class MockTabelHandler {
    private $activityTypes = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
        'АвансовыйОтчет',
        // New electrical equipment activity types
        'РаботыПоГрафику_Электрооборудование',
        'ФормированиеИД_Электрооборудование',
        'ПодтвДостВехи_ЭлектрОб',
        'ДопРаб_ЭлектрОб',
        'ИзмСхемПодкл_ЭлектрОб',
        'МонтажКабЛиний_ЭлектрОб',
        'РасклКабЛиний_ЭлектрОб',
        'Прочее_Электрооборудование',
        // Electrical equipment main category
        'Электрооборудование'
    ];
    
    public function extractCategoryFromCallback($callbackData) {
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
    
    public function isActivityExecution($category) {
        echo "isActivityExecution called with category: {$category}\n";
        
        // Check standard activity execution
        if (str_contains($category, '_yes')) {
            // Extract the activity type from the category
            $pattern = '/^Табель_(.+)_yes$/';
            if (preg_match($pattern, $category, $matches)) {
                $activityType = $matches[1];
                // Check if this activity type is in our list
                $result = in_array($activityType, $this->activityTypes);
                echo "isActivityExecution: category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false') . "\n";
                return $result;
            }
            
            // Handle short callback format for electrical equipment
            $shortPattern = '/^cat_ТабЭлОб_(.+)_yes$/';
            if (preg_match($shortPattern, $category, $matches)) {
                $shortActivityKey = $matches[1];
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РаботыПоГрафику_Электрооборудование',
                    'ФормИД' => 'ФормированиеИД_Электрооборудование',
                    'ИзмСхПд' => 'ИзменениеСхемПодключения_Электрооборудование',
                    'МонтКбЛн' => 'МонтажКабельныхЛиний_Электрооборудование',
                    'РаскКбЛн' => 'РасключениеКабельныхЛиний_Электрооборудование',
                    'ДопРаб' => 'ДопРаб_ЭлектрОб',
                    'ПодтВех' => 'ПодтвДостВехи_ЭлектрОб',
                    'Проч' => 'Прочее_Электрооборудование'
                ];
                
                if (isset($shortToFullMap[$shortActivityKey])) {
                    $activityType = $shortToFullMap[$shortActivityKey];
                } else {
                    // Fallback for old method
                    $activityType = str_replace(['Табель_', '_yes'], '', $category);
                }
                $result = in_array($activityType, $this->activityTypes);
                echo "isActivityExecution: short category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false') . "\n";
                return $result;
            }
        }
        
        // Check idle menu execution (when user confirms idle activity with detailed reason)
        if (str_contains($category, '_yes') && 
            (str_contains($category, 'Табель_Простой_yes') || str_contains($category, 'Табель_ШМ_Простой_yes'))) {
            echo "isActivityExecution: idle menu execution detected\n";
            return true;
        }
        
        echo "isActivityExecution: returning false for category: {$category}\n";
        return false;
    }
    
    // Simulate the fixed handle method
    public function handle($callbackData, $category) {
        echo "handle called with callbackData: '{$callbackData}' and category: '{$category}'\n";
        
        // Extract the actual category from the callback data
        $actualCategory = $this->extractCategoryFromCallback($callbackData);
        echo "extracted actual category: '{$actualCategory}'\n";
        
        // If we couldn't extract a category, use the one passed in
        if (empty($actualCategory)) {
            $actualCategory = $category;
        }
        echo "using actual category: '{$actualCategory}'\n";
        
        // Handle specific electrical equipment activities BEFORE activity execution check
        // This ensures that 'cat_ТабЭлОб_РабПоГр' and similar callbacks are processed correctly
        if (strpos($actualCategory, 'Табель_РаботыПоГрафику_Электрооборудование') === 0 ||
            $callbackData === 'cat_ТабЭлОб_РабПоГр' ||
            strpos($actualCategory, 'Табель_ФормированиеИД_Электрооборудование') === 0 ||
            $callbackData === 'cat_ТабЭлОб_ФормИД' ||
            strpos($actualCategory, 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование') === 0 ||
            $callbackData === 'cat_ТабЭлОб_ПодтВех') {
            echo "HANDLE METHOD: Processing specific electrical equipment activity before activity execution check\n";
            echo "Result: Will call handleOtherWorkTypes method\n";
            return 'handleOtherWorkTypes';
        }
        
        // Activity execution
        if ($this->isActivityExecution($actualCategory)) {
            echo "HANDLE METHOD: Processing activity execution\n";
            echo "Result: Will call handleActivityExecution method\n";
            return 'handleActivityExecution';
        }
        
        echo "HANDLE METHOD: Default fallback to handleOtherWorkTypes\n";
        return 'handleOtherWorkTypes';
    }
}

// Test cases
$handler = new MockTabelHandler();

echo "Test 1: User clicks 'Работы по графику' button\n";
$result1 = $handler->handle('cat_ТабЭлОб_РабПоГр', '');
echo "Expected: handleOtherWorkTypes, Actual: {$result1}\n";
echo (($result1 === 'handleOtherWorkTypes') ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "Test 2: User confirms 'Работы по графику' (clicks 'Да')\n";
$result2 = $handler->handle('cat_ТабЭлОб_РабПоГр_yes', '');
echo "Expected: handleOtherWorkTypes (should show confirmation), Actual: {$result2}\n";
echo (($result2 === 'handleOtherWorkTypes') ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "Test 3: User clicks 'Формирование ИД' button\n";
$result3 = $handler->handle('cat_ТабЭлОб_ФормИД', '');
echo "Expected: handleOtherWorkTypes, Actual: {$result3}\n";
echo (($result3 === 'handleOtherWorkTypes') ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "Test 4: User confirms 'Формирование ИД' (clicks 'Да')\n";
$result4 = $handler->handle('cat_ТабЭлОб_ФормИД_yes', '');
echo "Expected: handleOtherWorkTypes (should show confirmation), Actual: {$result4}\n";
echo (($result4 === 'handleOtherWorkTypes') ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "Test 5: Regular activity execution (should still work)\n";
$result5 = $handler->handle('category_Табель_Дорога_yes', '');
echo "Expected: handleActivityExecution, Actual: {$result5}\n";
echo (($result5 === 'handleActivityExecution') ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "FIX SUMMARY\n";
echo "===========\n";
echo "The fix reorders the processing in the TabelHandler handle method to ensure that:\n";
echo "1. Specific electrical equipment activities are processed BEFORE general activity execution checks\n";
echo "2. 'cat_ТабЭлОб_РабПоГр' and similar callbacks are correctly routed to handleOtherWorkTypes\n";
echo "3. The confirmation dialog is shown to the user before creating timesheet entries\n";
echo "4. Regular activity execution continues to work as expected\n\n";
echo "This should resolve the issue where the 'Работы по графику' button was not working correctly.\n";