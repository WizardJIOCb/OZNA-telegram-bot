<?php
// Targeted debug script to understand the exact issue with "Работы по графику" callback

echo "TARGETED DEBUG: 'Работы по графику' callback processing\n";
echo "====================================================\n\n";

// Simulate what happens in the actual TabelHandler
class TargetedDebug {
    public function debugActualIssue() {
        echo "SIMULATING THE EXACT SCENARIO:\n";
        echo "==============================\n";
        
        // These are the actual values that would be passed to the handle method
        $callbackData = 'cat_ТабЭлОб_РабПоГр';  // This is what comes from Telegram
        $category = 'cat_ТабЭлОб_РабПоГр';      // This is what's passed as the category parameter
        
        echo "Input values:\n";
        echo "- callbackData: '{$callbackData}'\n";
        echo "- category: '{$category}'\n\n";
        
        // Step 1: Check special electrical equipment handling
        echo "1. Checking special electrical equipment handling...\n";
        $specialHandling = (strpos($callbackData, 'Электрооборудование') !== false || 
                          strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
                          strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
                          strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0);
        echo "   Special handling triggered: " . ($specialHandling ? 'YES' : 'NO') . "\n\n";
        
        if ($specialHandling) {
            echo "❌ ISSUE: Special handling would redirect to electrical equipment menu!\n";
            return;
        }
        
        // Step 2: Extract actual category from callback data
        echo "2. Extracting actual category from callback data...\n";
        $actualCategory = $this->extractCategoryFromCallback($callbackData);
        echo "   Extracted actualCategory: '{$actualCategory}'\n\n";
        
        // Step 3: Check if this is "Работы по графику"
        echo "3. Checking if this is 'Работы по графику'...\n";
        $isScheduleWorks = ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
                           $actualCategory === 'cat_ТабЭлОб_РабПоГр');
        echo "   Is 'Работы по графику': " . ($isScheduleWorks ? 'YES' : 'NO') . "\n\n";
        
        if ($isScheduleWorks) {
            echo "✅ SUCCESS: Should show confirmation buttons!\n";
            return;
        }
        
        // Step 4: Check other conditions that might interfere
        echo "4. Checking other potential issues...\n";
        
        // Check date selection
        echo "   - Date selection check: " . ($this->isDateSelection($category) ? 'YES' : 'NO') . "\n";
        
        // Check project selection
        echo "   - Project selection check: " . ($this->isProjectSelection($category) ? 'YES' : 'NO') . "\n";
        
        // Check idle menu callback
        echo "   - Idle menu callback check: " . ($this->isIdleMenuCallback($category) ? 'YES' : 'NO') . "\n";
        
        // Check activity execution with original category
        echo "   - Activity execution (original): " . ($this->isActivityExecution($category) ? 'YES' : 'NO') . "\n";
        
        // Check activity execution with mapped category
        $mappedCategory = $this->extractCategoryFromCallback($category);
        if ($mappedCategory && $mappedCategory !== $category) {
            $confirmationCategory = $mappedCategory . '_yes';
            echo "   - Mapped category: '{$mappedCategory}'\n";
            echo "   - Confirmation category: '{$confirmationCategory}'\n";
            echo "   - Activity execution (mapped): " . ($this->isActivityExecution($confirmationCategory) ? 'YES' : 'NO') . "\n";
        }
        
        // Check activity confirmation
        echo "   - Activity confirmation: " . ($this->isActivityConfirmation($category) ? 'YES' : 'NO') . "\n\n";
        
        echo "❓ If we reach here, none of the specific handlers matched.\n";
        echo "   This would fall through to handleOtherWorkTypes.\n";
    }
    
    private function extractCategoryFromCallback(string $callbackData): string {
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
    
    private function isDateSelection(string $category): bool {
        // Simplified check - in real implementation this would be more complex
        return strpos($category, 'Табель_') === 0 && (
            strpos($category, 'Сегодня') !== false ||
            strpos($category, 'Вчера') !== false ||
            strpos($category, 'Позавчера') !== false ||
            strpos($category, 'ДругаяДата') !== false
        );
    }
    
    private function isProjectSelection(string $category): bool {
        return strpos($category, 'Табель_ШифрПроекта_') === 0;
    }
    
    private function isIdleMenuCallback(string $category): bool {
        return $category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой';
    }
    
    private function isActivityExecution(string $category): bool {
        // Check standard activity execution
        if (strpos($category, '_yes') !== false) {
            // Extract the activity type from the category
            $pattern = '/^Табель_(.+)_yes$/';
            if (preg_match($pattern, $category, $matches)) {
                // In real implementation, we'd check against activityTypes array
                // For this debug, we'll just check if it's the schedule works activity
                $activityType = $matches[1];
                return $activityType === 'РаботыПоГрафику_Электрооборудование';
            }
        }
        
        return false;
    }
    
    private function isActivityConfirmation(string $category): bool {
        // Simplified check for this debug
        $activityTypes = [
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
        
        foreach ($activityTypes as $activity) {
            if ($category === "Табель_{$activity}") {
                return true;
            }
        }
        
        return false;
    }
}

$debug = new TargetedDebug();
$debug->debugActualIssue();

echo "\n" . str_repeat("=", 60) . "\n";
echo "CONCLUSION:\n";
echo "If the debug shows 'SUCCESS: Should show confirmation buttons!',\n";
echo "then the issue is likely not in the TabelHandler logic itself,\n";
echo "but possibly in:\n";
echo "1. How the callback is being triggered\n";
echo "2. How the response is being sent\n";
echo "3. Telegram client issues\n";
echo "4. Caching issues\n";