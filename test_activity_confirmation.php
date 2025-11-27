<?php
// Test to check if isActivityConfirmation properly handles "Сварочные"

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

// Categories that should show submenus instead of confirmation
$submenuCategories = [
    'Электрооборудование',
    'ПожарнойСигнализации',
    'ОтоплениеИВентиляции',
    'ТехнОборудование',
    'Метрология',
    'КомплОпробование',
    'РежимнаяНаладка'
];

function isActivityConfirmation($category, $activityTypes, $submenuCategories) {
    // Check standard activity types
    foreach ($activityTypes as $activity) {
        // Skip submenu categories
        if (in_array($activity, $submenuCategories)) {
            continue;
        }
        
        if ($category === "Табель_{$activity}") {
            return true;
        }
    }
    
    // Check idle menu final confirmations (when user confirms after selecting detailed reason)
    if (strpos($category, '_yes') !== false && 
        (strpos($category, 'Табель_Простой') !== false || strpos($category, 'Табель_ШМ_Простой') !== false)) {
        return true;
    }
    
    return false;
}

// Test with "Сварочные"
$testCategory = 'Табель_Сварочные';
$result = isActivityConfirmation($testCategory, $activityTypes, $submenuCategories);

echo "Testing isActivityConfirmation with category: {$testCategory}\n";
echo "Result: " . ($result ? 'true' : 'false') . "\n";

// Test with another category for comparison
$testCategory2 = 'Табель_Электромонтажные';
$result2 = isActivityConfirmation($testCategory2, $activityTypes, $submenuCategories);

echo "\nTesting isActivityConfirmation with category: {$testCategory2}\n";
echo "Result: " . ($result2 ? 'true' : 'false') . "\n";
?>