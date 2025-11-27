<?php
// Test to verify that automation equipment activities are still treated as direct activities

// Include the activity types array from TabelHandler
$activityTypes = [
    // Standard activity types
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
    'Электрооборудование',
    // Automation equipment activity types
    'РабПоГр_Автоматизация',
    'ФормИД_Автоматизация',
    'ПодтВех_Автоматизация',
    'ДопРаб_Автоматизация',
    'ИзмСх_Автоматизация',
    'МонтКб_Автоматизация',
    'РаскКб_Автоматизация',
    'МонтАСУ_Автоматизация',
    'УдалПО_Автоматизация',
    'Прочее_Автоматизацияция'
    // Automation equipment main category - removed as it's a submenu, not a direct activity
    // 'Автоматизация'
];

$submenuCategories = [
    'Электрооборудование',
    'Автоматизация',
    'ПожарнойСигнализации',
    'ОтоплениеИВентиляции',
    'ТехнОборудование',
    'Метрология',
    'КомплОпробование',
    'РежимнаяНаладка'
];

function isActivityConfirmation($category, $activityTypes, $submenuCategories) {
    // Extract the activity type from the category
    $strippedCategory = str_replace('Табель_', '', $category);
    
    // Check standard activity types
    foreach ($activityTypes as $activity) {
        // Skip submenu categories
        if (in_array($activity, $submenuCategories)) {
            continue;
        }
        
        if ($strippedCategory === $activity) {
            return true;
        }
    }
    
    return false;
}

// Test automation equipment activities
$testCategories = [
    'Табель_РабПоГр_Автоматизация',
    'Табель_ФормИД_Автоматизация',
    'Табель_ПодтВех_Автоматизация',
    'Табель_ДопРаб_Автоматизация',
    'Табель_ИзмСх_Автоматизация',
    'Табель_МонтКб_Автоматизация',
    'Табель_РаскКб_Автоматизация',
    'Табель_МонтАСУ_Автоматизация',
    'Табель_УдалПО_Автоматизация',
    'Табель_Прочее_Автоматизацияция'
];

echo "Testing automation equipment activities:\n";
echo "=====================================\n";

foreach ($testCategories as $category) {
    $result = isActivityConfirmation($category, $activityTypes, $submenuCategories);
    echo "Category: {$category} => " . ($result ? 'DIRECT ACTIVITY (CORRECT)' : 'SUBMENU (WRONG)') . "\n";
}

echo "\nAll automation equipment activities should show 'DIRECT ACTIVITY (CORRECT)'.\n";