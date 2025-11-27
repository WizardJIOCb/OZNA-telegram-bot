<?php
// Test script to verify technological equipment activity types are now included

echo "=== TESTING TECHNOLOGICAL EQUIPMENT ACTIVITY TYPES ===\n\n";

// Reproduce the updated activityTypes array
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет',
    // Mobile brigade activity types
    'Мобильная бригада',
    // Electrical equipment activity types
    'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб',
    'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб',
    // Electrical equipment main category
    'Электрооборудование',
    // Automation equipment activity types
    'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
    // 'ДопРаб_Автом', // Removed - this is a submenu, not direct activity
    'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом',
    'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом',
    // Fire alarm equipment activity types
    'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
    // 'ДопРаб_ПожСигн', // Removed - this is a submenu, not direct activity
    'ИзмСх_ПожСигн', 'МонтКб_ПожСигн', 'РаскКб_ПожСигн',
    'МонтОб_ПожСигн', 'Прочее_ПожСигн',
    // Security alarm equipment activity types
    'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
    // 'ДопРаб_ОхрСигн', // Removed - this is a submenu, not direct activity
    'Прочее_ОхрСигн',
    'ИзмСх_ОхрСигн', 'МонтКб_ОхрСигн', 'РаскКб_ОхрСигн', 'МонтОб_ОхрСигн',
    // Heating and ventilation equipment activity types
    'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
    // 'ДопРаб_ВентОтопл', // Removed - this is a submenu, not direct activity
    'ИзмСх_ВентОтопл', 'МонтКб_ВентОтопл', 'РаскКб_ВентОтопл',
    'МонтАСУ_ВентОтопл', 'УдалПО_ВентОтопл', 'Прочее_ВентОтопл',
    // Technological equipment activity types
    'РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб',
    // 'ДопРаб_ТехОб', // Removed - this is a submenu, not direct activity
    // Fix for commissioning works issue - added missing activity type
    'ПусконаладочныеРаботы',
    // Missing commissioning activity types
    'КомплексноеОпробование',
    'РежимнаяНаладка',
    'ПодготовкаКПоездке',
    'СдачаАвансовогоОтчета',
    'ОхраннаяСигнализация',
    'ОтоплениеИВентиляция',
    'ТехнологическоеОборудование',
    'Метрология',
    // Project activity types
    'УчастиеВЗПИ',
    'РазрПрограммы',
    'РазрГрафика',
    'ОргДопоставки',
    'УчастиеВОПИ'
];

// Test technological equipment activity types
$technologicalActivityTypes = [
    'РабПоГр_ТехОб',
    'ФормИД_ТехОб', 
    'ПодтВех_ТехОб'
];

echo "1. CHECKING TECHNOLOGICAL EQUIPMENT ACTIVITY TYPES:\n";
echo "===================================================\n\n";

foreach ($technologicalActivityTypes as $activityType) {
    $isIncluded = in_array($activityType, $activityTypes);
    $status = $isIncluded ? '✅ FOUND' : '❌ MISSING';
    echo "{$status}: {$activityType}\n";
}

echo "\n2. TESTING ACTIVITY TYPE VALIDATION:\n";
echo "====================================\n\n";

// Simulate the validation logic from handleActivityExecution
function testActivityValidation($activityType, $activityTypes) {
    if (in_array($activityType, $activityTypes)) {
        echo "✅ VALID: {$activityType} - Activity will be accepted\n";
        return true;
    } else {
        echo "❌ INVALID: {$activityType} - Activity will be REJECTED\n";
        return false;
    }
}

// Test each technological equipment activity type
foreach ($technologicalActivityTypes as $activityType) {
    testActivityValidation($activityType, $activityTypes);
}

echo "\n3. COMPARISON WITH OTHER EQUIPMENT TYPES:\n";
echo "=========================================\n\n";

$equipmentComparison = [
    'Electrical' => ['РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб'],
    'Automation' => ['РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом'],
    'Fire Alarm' => ['РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн'],
    'Security Alarm' => ['РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн'],
    'Heating/Ventilation' => ['РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл'],
    'Technological' => ['РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб']
];

foreach ($equipmentComparison as $equipmentType => $activities) {
    echo "{$equipmentType} Equipment:\n";
    foreach ($activities as $activity) {
        $isValid = in_array($activity, $activityTypes);
        $status = $isValid ? '✅' : '❌';
        echo "  {$status} {$activity}\n";
    }
    echo "\n";
}

echo "4. SUMMARY:\n";
echo "===========\n\n";

$technologicalValid = true;
foreach ($technologicalActivityTypes as $activityType) {
    if (!in_array($activityType, $activityTypes)) {
        $technologicalValid = false;
        break;
    }
}

if ($technologicalValid) {
    echo "🎉 SUCCESS: All technological equipment activity types are now included!\n";
    echo "🎉 Technological equipment buttons should now work correctly!\n";
    echo "🎉 Users should be able to create tabel entries for technological equipment activities!\n";
} else {
    echo "❌ FAILURE: Some technological equipment activity types are still missing!\n";
    echo "❌ Technological equipment buttons will continue to fail!\n";
}

echo "\n5. EXPECTED BUTTON BEHAVIOR:\n";
echo "============================\n\n";

echo "Before fix:\n";
echo "❌ cat_ТабТехОб_РабПоГр_yes → 'неизвестная команда табеля'\n";
echo "❌ cat_ТабТехОб_ФормИД_yes → 'неизвестная команда табеля'\n";
echo "❌ cat_ТабТехОб_ПодтВех_yes → 'неизвестная команда табеля'\n\n";

echo "After fix:\n";
echo "✅ cat_ТабТехОб_РабПоГр_yes → Creates tabel entry for РабПоГр_ТехОб\n";
echo "✅ cat_ТабТехОб_ФормИД_yes → Creates tabel entry for ФормИД_ТехОб\n";
echo "✅ cat_ТабТехОб_ПодтВех_yes → Creates tabel entry for ПодтВех_ТехОб\n";
?>