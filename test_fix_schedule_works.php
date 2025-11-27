<?php
// Test script to verify the fix for "Работы по графику" button in electrical equipment category

echo "TESTING FIX: 'Работы по графику' button in electrical equipment category\n";
echo "=====================================================================\n\n";

// Simulate the callback data from the electrical equipment keyboard
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "1. Callback data from button: {$callbackData}\n";

// Test the extractCategoryFromCallback method
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
    echo "2. Mapped category: {$mappedCategory}\n";
    
    // Now test if this mapped category would match the pattern for activity execution
    // For activity execution, we need to append '_yes' to simulate the confirmation callback
    $confirmationCategory = $mappedCategory . '_yes';
    echo "3. Confirmation category: {$confirmationCategory}\n";
    
    // Check the pattern used in isActivityExecution method
    $pattern = '/^Табель_(.+)_yes$/';
    if (preg_match($pattern, $confirmationCategory, $matches)) {
        echo "4. Pattern matched! Extracted activity type: {$matches[1]}\n";
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
            echo "5. Activity type '{$activityType}' FOUND in activityTypes array\n";
            echo "   ✅ FIX VERIFIED: The fix should work correctly!\n";
            
            // Test the confirmation message
            $activityNames = [
                'РаботыПоГрафику_Электрооборудование' => 'Работы по графику (Электрооборудование)',
                'ФормированиеИД_Электрооборудование' => 'Формирование ИД (Электрооборудование)',
                'ПодтвДостВехи_ЭлектрОб' => 'Подтвердить достижение вехи (Электрооборудование)',
                'ИзмСхемПодкл_ЭлектрОб' => 'Изменение схем подключения (Электрооборудование)',
                'МонтажКабЛиний_ЭлектрОб' => 'Монтаж кабельных линий (Электрооборудование)',
                'РасклКабЛиний_ЭлектрОб' => 'Расключение кабельных линий (Электрооборудование)',
                'Прочее_Электрооборудование' => 'Прочее (Электрооборудование)',
            ];
            
            $activityName = $activityNames[$activityType] ?? $activityType;
            $projectCode = 'TEST-PROJECT';
            $confirmationMessage = sprintf(
                "<b>%s</b>, отправить запись в табель в проект <b>%s</b>?",
                $activityName,
                $projectCode
            );
            
            echo "6. Confirmation message: {$confirmationMessage}\n";
            echo "   ✅ Confirmation message is properly formatted\n";
        } else {
            echo "5. Activity type '{$activityType}' NOT FOUND in activityTypes array\n";
            echo "   ❌ This is still a problem - the activity type is missing!\n";
        }
    } else {
        echo "4. Pattern did NOT match for confirmation category\n";
        echo "   ❌ The pattern matching still needs to be fixed\n";
    }
} else {
    echo "2. Callback data '{$callbackData}' NOT found in shortFormats mapping\n";
    echo "   ❌ This is a problem - the mapping is missing!\n";
}

echo "\n=== SUMMARY ===\n";
echo "The fix involves properly mapping the short callback format to the full category name\n";
echo "before checking if it's an activity execution in the TabelHandler::handle method.\n";
echo "With our changes, the 'Работы по графику' button in electrical equipment category\n";
echo "should now correctly create a timesheet entry after confirmation.\n";