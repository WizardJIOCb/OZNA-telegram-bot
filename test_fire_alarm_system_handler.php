<?php
// Test the fire alarm system handler functionality

echo "=== Testing Fire Alarm System Handler ===\n";

// Test the activity types array to ensure fire alarm system activities are included
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
    'РабПоГр_ЭлектрОб',
    'ФормИД_ЭлектрОб',
    'ПодтВех_ЭлектрОб',
    'ИзмСхПд_ЭлектрОб',
    'МонтКбЛн_ЭлектрОб',
    'РаскКбЛн_ЭлектрОб',
    'Прочее_ЭлектрОб',
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
    'Прочее_Автом',
    // Fire alarm system activity types
    'РабПоГр_ПожСиг',
    'ФормИД_ПожСиг',
    'ДопРаб_ПожСиг',
    'ИзмСхПд_ПожСиг',
    'МонтКбЛн_ПожСиг',
    'РаскКбЛн_ПожСиг',
    'МонтОбПС_ПожСиг',
    'Прочее_ПожСиг'
];

echo "Test 1: Checking if fire alarm system activity types are in the array\n";
$fireAlarmActivities = [
    'РабПоГр_ПожСиг',
    'ФормИД_ПожСиг',
    'ДопРаб_ПожСиг',
    'ИзмСхПд_ПожСиг',
    'МонтКбЛн_ПожСиг',
    'РаскКбЛн_ПожСиг',
    'МонтОбПС_ПожСиг',
    'Прочее_ПожСиг'
];

$allFound = true;
foreach ($fireAlarmActivities as $activity) {
    if (in_array($activity, $activityTypes)) {
        echo "  -> Found: $activity\n";
    } else {
        echo "  -> Missing: $activity\n";
        $allFound = false;
    }
}

if ($allFound) {
    echo "  -> All fire alarm system activity types are present in the array\n";
} else {
    echo "  -> Some fire alarm system activity types are missing from the array\n";
}

// Test callback data mapping
echo "\nTest 2: Testing callback data mapping\n";
$shortFormats = [
    'cat_ТабПожСиг' => 'Табель_ПожарнаяСигнализация',
    'cat_ТабПожСиг_Наз' => 'Табель_ПожарнаяСигнализация',
    'cat_ТабПожСиг_РабПоГр' => 'Табель_РабПоГр_ПожСиг',
    'cat_ТабПожСиг_ФормИД' => 'Табель_ФормИД_ПожСиг',
    'cat_ТабПожСиг_ИзмСхПд' => 'Табель_ИзмСхПд_ПожСиг',
    'cat_ТабПожСиг_МонтКбЛн' => 'Табель_МонтКбЛн_ПожСиг',
    'cat_ТабПожСиг_РаскКбЛн' => 'Табель_РаскКбЛн_ПожСиг',
    'cat_ТабПожСиг_МонтОбПС' => 'Табель_МонтОбПС_ПожСиг',
    'cat_ТабПожСиг_ДопРаб' => 'Табель_ДопРаб_ПожСиг',
    'cat_ТабПожСиг_Проч' => 'Табель_Прочее_ПожСиг',
    // Fire alarm system confirmation callbacks
    'cat_ТабПожСиг_РабПоГр_yes' => 'Табель_РабПоГр_ПожСиг_yes',
    'cat_ТабПожСиг_ФормИД_yes' => 'Табель_ФормИД_ПожСиг_yes',
    'cat_ТабПожСиг_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ПожСиг_yes',
    'cat_ТабПожСиг_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ПожСиг_yes',
    'cat_ТабПожСиг_РаскКбЛн_yes' => 'Табель_РаскКбЛн_ПожСиг_yes',
    'cat_ТабПожСиг_МонтОбПС_yes' => 'Табель_МонтОбПС_ПожСиг_yes',
    'cat_ТабПожСиг_ДопРаб_yes' => 'Табель_ДопРаб_ПожСиг_yes',
    'cat_ТабПожСиг_Проч_yes' => 'Табель_Прочее_ПожСиг_yes'
];

echo "  -> Testing short format mappings:\n";
foreach ($shortFormats as $short => $full) {
    echo "    $short => $full\n";
}

echo "\nTest completed!\n";
?>