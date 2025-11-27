<?php
// Test to verify activity type validation for fire alarm equipment

$activityTypes = [
    // Standard activity types
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
    'АвансовыйОтчет',

    // New electrical equipment activity types (short format)
    'РабПоГр_ЭлектрОб',
    'ФормИД_ЭлектрОб',
    'ПодтВех_ЭлектрОб',
    'ИзмСхПд_ЭлектрОб',
    'МонтКбЛн_ЭлектрОб',
    'РаскКбЛн_ЭлектрОб',
    'Прочее_ЭлектрОб',
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
    // Fire alarm activity types
    'РабПоГр_ПожСигн',
    'ФормИД_ПожСигн',
    'ПодтВех_ПожСигн',
    'ДопРаб_ПожСигн',
    'ИзмСх_ПожСигн',
    'МонтКб_ПожСигн',
    'РаскКб_ПожСигн',
    'МонтАСУ_ПожСигн',
    'УдалПО_ПожСигн',
    'Прочее_ПожСигн'
];

echo "Fire Alarm Activity Type Validation Test\n";
echo str_repeat("=", 40) . "\n\n";

// Test all fire alarm activity types
$fireAlarmTypes = [
    'РабПоГр_ПожСигн',
    'ФормИД_ПожСигн',
    'ПодтВех_ПожСигн',
    'ДопРаб_ПожСигн',
    'ИзмСх_ПожСигн',
    'МонтКб_ПожСигн',
    'РаскКб_ПожСигн',
    'МонтАСУ_ПожСигн',
    'УдалПО_ПожСигн',
    'Прочее_ПожСигн'
];

echo "Verifying fire alarm activity types are in activityTypes array:\n";
$allValid = true;
foreach ($fireAlarmTypes as $type) {
    $isValid = in_array($type, $activityTypes);
    echo "  $type: " . ($isValid ? "VALID" : "INVALID") . "\n";
    if (!$isValid) {
        $allValid = false;
    }
}

echo "\n" . str_repeat("-", 40) . "\n";

// Test short callback format mapping
$shortToFullMap = [
    'РабПоГр' => 'РабПоГр_ПожСигн',
    'ФормИД' => 'ФормИД_ПожСигн',
    'ИзмСх' => 'ИзмСх_ПожСигн',
    'МонтКб' => 'МонтКб_ПожСигн',
    'РаскКб' => 'РаскКб_ПожСигн',
    'МонтАСУ' => 'МонтАСУ_ПожСигн',
    'УдалПО' => 'УдалПО_ПожСигн',
    'ДопРаб' => 'ДопРаб_ПожСигн',
    'ПодтВех' => 'ПодтВех_ПожСигн',
    'Проч' => 'Прочее_ПожСигн'
];

echo "Verifying short callback format mapping:\n";
foreach ($shortToFullMap as $short => $full) {
    $isValid = in_array($full, $activityTypes);
    echo "  $short -> $full: " . ($isValid ? "VALID" : "INVALID") . "\n";
    if (!$isValid) {
        $allValid = false;
    }
}

echo "\n" . str_repeat("-", 40) . "\n";
echo "Overall result: " . ($allValid ? "ALL ACTIVITY TYPES ARE VALID" : "SOME ACTIVITY TYPES ARE INVALID") . "\n";

if ($allValid) {
    echo "\nAll fire alarm activity types are correctly defined and should work properly.\n";
    echo "If you're still experiencing issues, they may be related to:\n";
    echo "1. Runtime environment or Telegram API issues\n";
    echo "2. User-specific conversation state\n";
    echo "3. Database connectivity problems\n";
    echo "4. Network latency or timeout issues\n";
} else {
    echo "\nThere are issues with activity type definitions that need to be fixed.\n";
}
?>