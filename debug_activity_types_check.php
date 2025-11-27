<?php
// Проверяем точную проблему с массивом activityTypes

echo "=== ПРОВЕРКА МАССИВА ACTIVITYTYPES ===\n\n";

// Воспроизводим массив из TabelHandler
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет',
    'Мобильная бригада',
    'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб',
    'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'Прочее_ЭлектрОб',
    'Электрооборудование',
    'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
    'ДопРаб_Автом', 'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом',
    'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом',
    'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
    'ДопРаб_ПожСигн', 'ИзмСх_ПожСигн', 'МонтКб_ПожСигн', 'РаскКб_ПожСигн',
    'МонтАСУ_ПожСигн', 'УдалПО_ПожСигн', 'Прочее_ПожСигн',
    'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
    'ДопРаб_ВентОтопл', 'ИзмСх_ВентОтопл', 'МонтКб_ВентОтопл', 'РаскКб_ВентОтопл',
    'МонтАСУ_ВентОтопл', 'УдалПО_ВентОтопл', 'Прочее_ВентОтопл',
    'ПусконаладочныеРаботы'
];

echo "Всего элементов в массиве: " . count($activityTypes) . "\n\n";

// Тестируем конкретные значения
$testValues = [
    'Инструктаж',
    'РабПоГр_ЭлектрОб',
    'ПусконаладочныеРаботы'
];

echo "=== ПРОВЕРКА КОНКРЕТНЫХ ЗНАЧЕНИЙ ===\n\n";

foreach ($testValues as $testValue) {
    echo "Тестируем: '{$testValue}'\n";
    echo "  Найден в массиве: " . (in_array($testValue, $activityTypes) ? "✓ ДА" : "✗ НЕТ") . "\n";
    echo "  Длина строки: " . strlen($testValue) . " байт\n";
    echo "  Кодировка: " . mb_detect_encoding($testValue) . "\n";
    
    // Поиск по индексу
    $index = array_search($testValue, $activityTypes);
    if ($index !== false) {
        echo "  Индекс в массиве: {$index}\n";
    }
    echo "\n";
}

echo "=== ПРОВЕРКА CALLBACK CHAIN ===\n\n";

$callback = 'cat_ТабИнстр_yes';
echo "Callback: {$callback}\n";

if (preg_match('/^cat_(.+)_yes$/u', $callback, $matches)) {
    $shortKey = $matches[1];
    echo "Extracted key: '{$shortKey}'\n";
    
    $basicCommissioningActivities = [
        'ТабИнстр' => 'Инструктаж',
        'ТабДорога' => 'Дорога',
        'ТабПодгПоезд' => 'ПодготовкаКПоездке',
        'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
    ];
    
    if (isset($basicCommissioningActivities[$shortKey])) {
        $activityType = $basicCommissioningActivities[$shortKey];
        echo "Activity type: '{$activityType}'\n";
        echo "Activity type в activityTypes: " . (in_array($activityType, $activityTypes) ? "✓ ДА" : "✗ НЕТ") . "\n";
        
        if (in_array($activityType, $activityTypes)) {
            echo "✅ ДОЛЖНО РАБОТАТЬ!\n";
        } else {
            echo "❌ ВОТ ПРОБЛЕМА!\n";
        }
    }
}

echo "\n=== ДЕБАГ КОДИРОВКИ ===\n\n";

// Проверим точную кодировку 'Инструктаж' в массиве
$instruktazh_from_array = $activityTypes[2]; // Позиция 'Инструктаж' в массиве
$instruktazh_from_mapping = 'Инструктаж';

echo "Из массива activityTypes[2]: '{$instruktazh_from_array}'\n";
echo "Из mapping: '{$instruktazh_from_mapping}'\n";
echo "Идентичны: " . ($instruktazh_from_array === $instruktazh_from_mapping ? "✓ ДА" : "✗ НЕТ") . "\n";

if ($instruktazh_from_array !== $instruktazh_from_mapping) {
    echo "ПРОБЛЕМА КОДИРОВКИ!\n";
    echo "Из массива байты: ";
    for ($i = 0; $i < strlen($instruktazh_from_array); $i++) {
        echo dechex(ord($instruktazh_from_array[$i])) . " ";
    }
    echo "\n";
    echo "Из mapping байты: ";
    for ($i = 0; $i < strlen($instruktazh_from_mapping); $i++) {
        echo dechex(ord($instruktazh_from_mapping[$i])) . " ";
    }
    echo "\n";
}
?>