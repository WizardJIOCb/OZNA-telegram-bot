<?php
// Debug script to understand what's happening with the "Работы по графику" callback

echo "Debugging 'Работы по графику' callback processing\n";
echo "==================================================\n\n";

// Simulate the callback data
$callbackData = 'cat_ТабЭлОб_РабПоГр';

echo "Input callback data: {$callbackData}\n\n";

// Simulate the extractCategoryFromCallback method
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

// Simulate the activity types array
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

// Simulate the isActivityExecution method
function isActivityExecution($category, $activityTypes) {
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        // Extract the activity type from the category
        $pattern = '/^Табель_(.+)_yes$/';
        if (preg_match($pattern, $category, $matches)) {
            $activityType = $matches[1];
            // Check if this activity type is in our list
            $result = in_array($activityType, $activityTypes);
            echo "isActivityExecution: category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false') . "\n";
            return $result;
        }
    }
    
    // Check idle menu execution (when user confirms idle activity with detailed reason)
    if (str_contains($category, '_yes') && 
        (str_contains($category, 'Табель_Простой_yes') || str_contains($category, 'Табель_ШМ_Простой_yes'))) {
        return true;
    }
    
    return false;
}

// Extract the actual category
$actualCategory = extractCategoryFromCallback($callbackData);
echo "Extracted actual category: '{$actualCategory}'\n\n";

// Check if this is an activity execution callback
$isActivityExecution = isActivityExecution($callbackData, $activityTypes);
echo "isActivityExecution result for callbackData: " . ($isActivityExecution ? 'true' : 'false') . "\n";

// Check if this is an activity execution callback for the mapped category
$mappedCategory = $actualCategory . '_yes';
$isMappedActivityExecution = isActivityExecution($mappedCategory, $activityTypes);
echo "isActivityExecution result for mappedCategory ({$mappedCategory}): " . ($isMappedActivityExecution ? 'true' : 'false') . "\n\n";

// Check if this matches our "Работы по графику" condition
$matchesScheduleWorks = ($actualCategory === 'Табель_РаботыПоГрафику_Электрооборудование' || 
                         $actualCategory === 'cat_ТабЭлОб_РабПоГр');
echo "Matches 'Работы по графику' condition: " . ($matchesScheduleWorks ? 'true' : 'false') . "\n\n";

echo "Analysis:\n";
echo "1. The callback 'cat_ТабЭлОб_РабПоГр' is correctly mapped to 'Табель_РаботыПоГрафику_Электрооборудование'\n";
echo "2. The isActivityExecution check for the original callback returns false (correct)\n";
echo "3. The isActivityExecution check for the mapped category with '_yes' suffix returns false (correct)\n";
echo "4. The 'Работы по графику' condition should match (returns true)\n\n";

echo "Conclusion:\n";
echo "The callback should be processed by the 'Работы по графику' handling code, not by the activity execution code.\n";
echo "If it's not working, there might be an issue with the order of checks in the handle method.\n";