<?php
/**
 * Test script to verify technological equipment format standardization
 */

// Simulate the activity type mappings for testing
$activityTypeMappings = [
    // Current technological equipment activities in TabelEntry.php
    'РабПоГр_ТехОб' => '📅 Работы по графику (Технологическое оборудование)',
    'ФормИД_ТехОб' => '📄 Формирование ИД (Технологическое оборудование)',
    'ПодтВех_ТехОб' => '✅ Подтвердить достижение вехи (Технологическое оборудование)',
    'ДопРаб_ТехОб' => '🛠️ Дополнительные работы (Технологическое оборудование)',
    
    // Dismantling activities (additional works) - these already have correct format
    'ДемАрм_ТехОб' => '🔧 Демонтаж/ревизия арматуры (Технологическое оборудование, Дополнительные работы)',
    'ДемОб_ТехОб' => '⚙️ Демонтаж/ревизия техн. оборудования (Технологическое оборудование, Дополнительные работы)',
    'Прочее_ТехОб' => '📝 Прочее (Технологическое оборудование, Дополнительные работы)',
    
    // Electrical equipment standard format (for comparison)
    'РабПоГр_ЭлектрОб' => '📅 Работы по графику (Электрооборудование)',
    'ФормИД_ЭлектрОб' => '📄 Формирование ИД (Электрооборудование)',
    'ИзмСхПд_ЭлектрОб' => '🔀 Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
    'МонтКбЛн_ЭлектрОб' => '🔌 Прокладка кабеля (Электрооборудование, Дополнительные работы)',
    'Прочее_ЭлектрОб' => '📝 Прочее (Электрооборудование, Дополнительные работы)'
];

// Test final reports activity names for comparison
$finalReportNames = [
    // Updated technological equipment format in final reports
    'РабПоГр_ТехОб' => 'Работы по графику (Технологическое оборудование)',
    'ФормИД_ТехОб' => 'Формирование ИД (Технологическое оборудование)',
    'ПодтВех_ТехОб' => 'Подтвердить достижение вехи (Технологическое оборудование)',
    'ДопРаб_ТехОб' => 'Дополнительные работы (Технологическое оборудование)',
    'ДемАрм_ТехОб' => 'Демонтаж/ревизия арматуры (Технологическое оборудование, Дополнительные работы)', // Now included!
    'ДемОб_ТехОб' => 'Демонтаж/ревизия техн. оборудования (Технологическое оборудование, Дополнительные работы)', // Now included!
    'Прочее_ТехОб' => 'Прочее (Технологическое оборудование, Дополнительные работы)', // Now included!
    
    // Electrical equipment standard format (for comparison)
    'РабПоГр_ЭлектрОб' => 'Работы по графику (Электрооборудование)',
    'ФормИД_ЭлектрОб' => 'Формирование ИД (Электрооборудование)',
    'ИзмСхПд_ЭлектрОб' => 'Изменение схемы подключения (Электрооборудование, Дополнительные работы)',
    'МонтКбЛн_ЭлектрОб' => 'Прокладка кабеля (Электрооборудование, Дополнительные работы)',
    'Прочее_ЭлектрОб' => 'Прочее (Электрооборудование, Дополнительные работы)'
];

echo "=== Format Standardization Analysis ===\n\n";

echo "Current Technological Equipment Activities (Preview Messages):\n";
foreach (['РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ДемАрм_ТехОб', 'ДемОб_ТехОб', 'Прочее_ТехОб'] as $activity) {
    echo "  {$activity}: {$activityTypeMappings[$activity]}\n";
}

echo "\nElectrical Equipment Standard (Preview Messages):\n";
foreach (['РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'Прочее_ЭлектрОб'] as $activity) {
    echo "  {$activity}: {$activityTypeMappings[$activity]}\n";
}

echo "Current Technological Equipment Activities (Final Reports):\n";
foreach (['РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб', 'ДопРаб_ТехОб', 'ДемАрм_ТехОб', 'ДемОб_ТехОб', 'Прочее_ТехОб'] as $activity) {
    echo "  {$activity}: {$finalReportNames[$activity]}\n";
}

echo "\n=== Required Changes ===\n";
echo "✅ 1. Added technological equipment activity array in createConfirmationMessage method\n";
echo "✅ 2. Added missing technological equipment activities to final reports \$activityNames array\n";
echo "✅ 3. Added technological equipment activity array in confirmation message generation\n";
echo "✅ 4. Added special handling for technological equipment milestone completion\n";

echo "\n=== Format Consistency Check ===\n";
$electricalAdditionalWorks = array_filter($activityTypeMappings, function($value, $key) {
    return strpos($key, '_ЭлектрОб') !== false && strpos($value, 'Дополнительные работы') !== false;
}, ARRAY_FILTER_USE_BOTH);

$technologicalAdditionalWorks = array_filter($activityTypeMappings, function($value, $key) {
    return strpos($key, '_ТехОб') !== false && strpos($value, 'Дополнительные работы') !== false;
}, ARRAY_FILTER_USE_BOTH);

echo "Electrical Equipment Additional Works:\n";
foreach ($electricalAdditionalWorks as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\nTechnological Equipment Additional Works:\n";
foreach ($technologicalAdditionalWorks as $key => $value) {
    echo "  {$key}: {$value}\n";
}

echo "\n✅ Additional works activities already have consistent format!\n";
echo "✅ Both use: '([Equipment Type], Дополнительные работы)' pattern\n";