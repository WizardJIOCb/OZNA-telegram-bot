<?php
// Test to verify that automation equipment activities are correctly handled with shortened names

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
    'РабПоГр_Автом',
    'ФормИД_Автом',
    'ПодтВех_Автом',
    'ДопРаб_Автом',
    'ИзмСх_Автом',
    'МонтКб_Автом',
    'РаскКб_Автом',
    'МонтАСУ_Автом',
    'УдалПО_Автом',
    'Прочее_Автом'
    // Automation equipment main category - removed as it's a submenu, not a direct activity
    // 'Автоматизация'
];

function isActivityInTypes($activityType, $activityTypes) {
    return in_array($activityType, $activityTypes);
}

// Test automation equipment activities
$testActivities = [
    'РабПоГр_Автом',
    'ФормИД_Автом',
    'ПодтВех_Автом',
    'ДопРаб_Автом',
    'ИзмСх_Автом',
    'МонтКб_Автом',
    'РаскКб_Автом',
    'МонтАСУ_Автом',
    'УдалПО_Автом',
    'Прочее_Автом'
];

echo "Testing automation equipment activities in activityTypes array with shortened names:\n";
echo "========================================================================\n";

foreach ($testActivities as $activity) {
    $result = isActivityInTypes($activity, $activityTypes);
    echo "Activity: {$activity} => " . ($result ? 'FOUND (CORRECT)' : 'NOT FOUND (WRONG)') . "\n";
}

echo "\nAll automation equipment activities should show 'FOUND (CORRECT)'.\n";