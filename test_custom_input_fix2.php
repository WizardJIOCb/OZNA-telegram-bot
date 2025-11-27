<?php

// More specific test to verify the fix for custom input handling in "Прочее" activities

echo "Testing callback processing logic\n";

// Test the pattern matching for the category
$category = 'Табель_Прочее_Электрооборудование_yes';

echo "Category: {$category}\n";

// Check if our special condition works
if (substr_compare($category, '_Прочее_Электрооборудование_yes', -strlen('_Прочее_Электрооборудование_yes')) === 0) {
    $activityType = 'Прочее_Электрооборудование';
    echo "Pattern matched correctly!\n";
    echo "Activity type extracted: {$activityType}\n";
    
    // Check if this activity type exists in our activity types list
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
        // 'ДопРаб_ЭлектрОб', // Removed as it's a submenu, not a direct activity
        'ИзмСхемПодкл_ЭлектрОб',
        'МонтажКабЛиний_ЭлектрОб',
        'РасклКабЛиний_ЭлектрОб',
        'Прочее_Электрооборудование',
        // Electrical equipment main category
        'Электрооборудование'
    ];
    
    if (in_array($activityType, $activityTypes)) {
        echo "Activity type found in the list! Fix should work correctly.\n";
    } else {
        echo "Activity type NOT found in the list! Fix may not work.\n";
    }
} else {
    echo "Pattern did not match.\n";
}

// Also test the isActivityExecution method logic
echo "\nTesting isActivityExecution method logic:\n";

// Simulate the logic from the isActivityExecution method
if (strpos($category, '_yes') !== false) {
    echo "Category contains '_yes'\n";
    
    // Special handling for "Прочее" activities with custom input
    if (substr_compare($category, '_Прочее_Электрооборудование_yes', -strlen('_Прочее_Электрооборудование_yes')) === 0) {
        $activityType = 'Прочее_Электрооборудование';
        $result = in_array($activityType, $activityTypes);
        echo "Custom input electrical category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false') . "\n";
        echo "This should return true, indicating the category is a valid activity execution.\n";
    }
}

echo "Test completed.\n";

?>