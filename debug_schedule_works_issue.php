<?php
// Debug script to test the "Работы по графику" button issue in electrical equipment category

echo "DEBUG: Testing 'Работы по графику' button in electrical equipment category\n";
echo "=====================================================================\n\n";

// Simulate the callback data from the electrical equipment keyboard
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "1. Callback data from button: {$callbackData}\n";

// Test the regex pattern used in TabelHandler::isActivityExecution
$category = $callbackData;
echo "2. Category for testing: {$category}\n";

// Check if this matches the pattern in isActivityExecution method
$pattern = '/^Табель_(.+)_yes$/';
if (preg_match($pattern, $category, $matches)) {
    echo "3. Pattern matched! Extracted activity type: {$matches[1]}\n";
    $activityType = $matches[1];
} else {
    echo "3. Pattern did NOT match. This is the issue!\n";
    echo "   The pattern expects the category to start with 'Табель_' but our category is '{$category}'\n";
    echo "   Our category starts with 'cat_ТабЭлОб_РабПоГр'\n";
}

echo "\n4. Checking how the category should be processed:\n";
// In TabelHandler, the category is extracted from callback data
// Let's see how it's done in extractCategoryFromCallback method

// Handle short category format for electrical equipment
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
    $mappedCategory = $shortFormats[$callbackData];
    echo "5. Mapped category: {$mappedCategory}\n";
    
    // Now test if this mapped category would match the pattern for activity execution
    // For activity execution, we need to append '_yes' to simulate the confirmation callback
    $confirmationCategory = $mappedCategory . '_yes';
    echo "6. Confirmation category: {$confirmationCategory}\n";
    
    if (preg_match($pattern, $confirmationCategory, $matches)) {
        echo "7. Pattern matched! Extracted activity type: {$matches[1]}\n";
        $activityType = $matches[1];
        
        // Check if this activity type exists in the activityTypes array
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
        
        if (in_array($activityType, $activityTypes)) {
            echo "8. Activity type '{$activityType}' FOUND in activityTypes array\n";
            echo "   This should work correctly!\n";
        } else {
            echo "8. Activity type '{$activityType}' NOT FOUND in activityTypes array\n";
            echo "   This is a problem - the activity type is missing!\n";
        }
    } else {
        echo "7. Pattern did NOT match for confirmation category\n";
    }
} else {
    echo "5. Callback data '{$callbackData}' NOT found in shortFormats mapping\n";
    echo "   This is a problem - the mapping is missing!\n";
}

echo "\n=== SOLUTION ===\n";
echo "The issue is in the pattern matching in TabelHandler::isActivityExecution method.\n";
echo "The method expects categories to start with 'Табель_' but electrical equipment\n";
echo "categories use the short format starting with 'cat_ТабЭлОб_'.\n\n";

echo "The fix should be in the handle method of TabelHandler to properly map\n";
echo "the short callback format to the full category name BEFORE checking\n";
echo "if it's an activity execution.\n";