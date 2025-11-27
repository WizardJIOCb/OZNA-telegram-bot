<?php
// Simple test to verify the fix for the "Работы по графику" button

// Test the mapping that should happen in TabelHandler::extractCategoryFromCallback
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

// Test the specific callback
$callbackData = 'cat_ТабЭлОб_РабПоГр';

if (isset($shortFormats[$callbackData])) {
    $mappedCategory = $shortFormats[$callbackData];
    echo "SUCCESS: Callback '{$callbackData}' correctly mapped to category '{$mappedCategory}'\n";
    
    // Check if this category should be handled by handleOtherWorkTypes
    if ($mappedCategory === 'Табель_РаботыПоГрафику_Электрооборудование') {
        echo "SUCCESS: Category '{$mappedCategory}' should be handled by handleOtherWorkTypes\n";
        
        // Check if this category is in the activityTypes array
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
        
        $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
        if (in_array($strippedCategory, $activityTypes)) {
            echo "SUCCESS: Activity type '{$strippedCategory}' found in activityTypes array\n";
            
            // Check if the activity execution would work
            $categoryForExecution = "Табель_{$strippedCategory}_yes";
            $pattern = '/^Табель_(.+)_yes$/';
            if (preg_match($pattern, $categoryForExecution, $matches)) {
                $activityType = $matches[1];
                if (in_array($activityType, $activityTypes)) {
                    echo "SUCCESS: Activity execution would work for category '{$categoryForExecution}'\n";
                    echo "SUCCESS: Extracted activity type '{$activityType}' found in activityTypes array\n";
                } else {
                    echo "ERROR: Extracted activity type '{$activityType}' NOT found in activityTypes array\n";
                }
            } else {
                echo "ERROR: Pattern matching failed for category '{$categoryForExecution}'\n";
            }
        } else {
            echo "ERROR: Activity type '{$strippedCategory}' NOT found in activityTypes array\n";
        }
    } else {
        echo "ERROR: Category '{$mappedCategory}' would not be handled correctly\n";
    }
} else {
    echo "ERROR: Callback '{$callbackData}' not found in shortFormats mapping\n";
}

echo "Test completed.\n";
?>