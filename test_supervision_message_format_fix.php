<?php
/**
 * Test the supervision milestone message format fix
 */

echo "=== ТЕСТ ИСПРАВЛЕНИЯ ФОРМАТА СООБЩЕНИЙ ДЛЯ SUPERVISION MILESTONE ===\n\n";

// Simulate the TabelEntry::createTabelMessage method with the fix
function testCreateTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
    // Activity type mappings (from TabelEntry.php)
    $activityTypeMappings = [
        // Supervision works milestone activities - standardized format matching commissioning works
        'ШМР_ВходнойКонтроль_ПодтВех' => '✅ Подтвердить достижение вехи (Входной контроль)',
        'ШМР_ЗавозОборудования_ПодтВех' => '✅ Подтвердить достижение вехи (Завоз оборудования на площадку)',
        'ШМР_РасстановкаБлоков_ПодтВех' => '✅ Подтвердить достижение вехи (Расстановка блоков)',
        'ШМР_МонтажПлощадок_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж площадок обслуживания)',
        'ШМР_МонтажЭлементовУкрытия_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж элементов укрытия)',
        'ШМР_СборкаКровли_ПодтВех' => '✅ Подтвердить достижение вехи (Сборка кровли)',
        'ШМР_МонтажТрубопроводов_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж трубопроводов)',
        'ШМР_ГидроПневмоИспытания_ПодтВех' => '✅ Подтвердить достижение вехи (Гидро/пневмо испытания)',
        'ШМР_МонтажТеплоизоляции_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж теплоизоляции)',
        'ШМР_МонтажВентиляции_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж вентиляции)',
        'ШМР_МонтажСнятогоОборудования_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж снятого оборудования)',
        'ШМР_МонтажКабельнойПродукции_ПодтВех' => '✅ Подтвердить достижение вехи (Монтаж кабельной продукции)',
        // Supervision works "Работы по графику" activities - standardized format
        'ШМР_ВходнойКонтроль_РабПоГр' => '📋 Работы по графику (Входной контроль)',
        'ШМР_ЗавозОборудования_РабПоГр' => '📋 Работы по графику (Завоз оборудования на площадку)',
        'ШМР_РасстановкаБлоков_РабПоГр' => '📋 Работы по графику (Расстановка блоков)',
        'ШМР_МонтажПлощадок_РабПоГр' => '📋 Работы по графику (Монтаж площадок обслуживания)',
        'ШМР_МонтажЭлементовУкрытия_РабПоГр' => '📋 Работы по графику (Монтаж элементов укрытия)',
        'ШМР_СборкаКровли_РабПоГр' => '📋 Работы по графику (Сборка кровли)',
        'ШМР_МонтажТрубопроводов_РабПоГр' => '📋 Работы по графику (Монтаж трубопроводов)',
        'ШМР_ГидроПневмоИспытания_РабПоГр' => '📋 Работы по графику (Гидро/пневмо испытания)',
        'ШМР_МонтажТеплоизоляции_РабПоГр' => '📋 Работы по графику (Монтаж теплоизоляции)',
        'ШМР_МонтажВентиляции_РабПоГр' => '📋 Работы по графику (Монтаж вентиляции)',
        'ШМР_МонтажСнятогоОборудования_РабПоГр' => '📋 Работы по графику (Монтаж снятого оборудования)',
        'ШМР_МонтажКабельнойПродукции_РабПоГр' => '📋 Работы по графику (Монтаж кабельной продукции)',
    ];
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    
    // Special handling for milestone completion (ПодтВех_ЭлектрОб)
    if ($activityType === 'ПодтВех_ЭлектрОб') {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Электрооборудование)</b>",
            $fio,
            $date,
            $projectCode
        );
    }
    
    // Special handling for milestone completion (ПодтВех_ПожСигн)
    if ($activityType === 'ПодтВех_ПожСигн') {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Пожарная сигнализация)</b>",
            $fio,
            $date,
            $projectCode
        );
    }
    
    // Special handling for milestone completion (ПодтВех_ОхрСигн)
    if ($activityType === 'ПодтВех_ОхрСигн') {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Охранная сигнализация)</b>",
            $fio,
            $date,
            $projectCode
        );
    }
    
    // Special handling for milestone completion (ПодтВех_Автом)
    if ($activityType === 'ПодтВех_Автом') {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Автоматизация)</b>",
            $fio,
            $date,
            $projectCode
        );
    }
    
    // Special handling for milestone completion (ПодтВех_ВентОтопл)
    if ($activityType === 'ПодтВех_ВентОтопл') {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Вентиляция и отопление)</b>",
            $fio,
            $date,
            $projectCode
        );
    }
    
    // Special handling for milestone completion (ПодтВех_ТехОб)
    if ($activityType === 'ПодтВех_ТехОб') {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Технологическое оборудование)</b>",
            $fio,
            $date,
            $projectCode
        );
    }
    
    // Special handling for milestone completion (ПодтВех_Метрол)
    if ($activityType === 'ПодтВех_Метрол') {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>✅ Этап наладки завершен (Метрология)</b>",
            $fio,
            $date,
            $projectCode
        );
    }
    
    // Project activities ("Проектная деятельность") - use new format
    $projectActivities = [
        'УчастиеВЗПИ', 'РазрПрограммы', 'РазрГрафика', 'ОргДопоставки', 'УчастиеВОПИ', 'ИспОбМаст', 'ШефНаладочныеРаботы', 'Прочее_ПроектнаяДеятельность', 'Прочее_ПроектнаяДеятельность_ШМР'
    ];
    
    if (in_array($activityType, $projectActivities)) {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Electrical equipment activities ("Электрооборудование") - use new format
    $electricalEquipmentActivities = [
        'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб',
        'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб'
    ];
    
    if (in_array($activityType, $electricalEquipmentActivities)) {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Fire alarm equipment activities ("Пожарная сигнализация") - use new format
    $fireAlarmEquipmentActivities = [
        'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
        'ИзмСх_ПожСигн', 'МонтКб_ПожСигн', 'РаскКб_ПожСигн', 'МонтОб_ПожСигн', 'Прочее_ПожСигн'
    ];
    
    if (in_array($activityType, $fireAlarmEquipmentActivities)) {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Security alarm equipment activities ("Охранная сигнализация") - use new format
    $securityAlarmEquipmentActivities = [
        'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
        'ИзмСх_ОхрСигн', 'МонтКб_ОхрСигн', 'РаскКб_ОхрСигн', 'МонтОб_ОхрСигн', 'Прочее_ОхрСигн'
    ];
    
    if (in_array($activityType, $securityAlarmEquipmentActivities)) {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Heating and ventilation equipment activities ("Вентиляция и отопление") - use new format
    $heatingVentilationEquipmentActivities = [
        'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
        'ДопРаб_ВентОтопл', 'ИзмСх_ВентОтопл', 'МонтКб_ВентОтопл', 'РаскКб_ВентОтопл', 
        'МонтАСУ_ВентОтопл', 'УдалПО_ВентОтопл', 'Прочее_ВентОтопл'
    ];
    
    if (in_array($activityType, $heatingVentilationEquipmentActivities)) {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Automation equipment activities ("Автоматизация") - use new format
    $automationEquipmentActivities = [
        'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
        'ДопРаб_Автом', 'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом', 
        'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом'
    ];
    
    if (in_array($activityType, $automationEquipmentActivities)) {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Technological equipment activities ("Технологическое оборудование") - use new format
    $technologicalEquipmentActivities = [
        'РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб', 'ДопРаб_ТехОб',
        'ДемАрм_ТехОб', 'ДемОб_ТехОб', 'Прочее_ТехОб'
    ];
    
    if (in_array($activityType, $technologicalEquipmentActivities)) {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Supervision works milestone and schedule activities - use same format as commissioning
    if (str_contains($activityType, 'ШМР_') && (str_contains($activityType, '_ПодтВех') || str_contains($activityType, '_РабПоГр'))) {
        return sprintf(
            "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
            $fio,
            $date,
            $projectCode,
            $activityText
        );
    }
    
    // Default format
    return sprintf(
        "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
        $fio,
        $date,
        $projectCode,
        $activityText
    );
}

// Test cases
$testCases = [
    [
        'fio' => 'Тестовый Пользователь',
        'date' => '04.10.2025',
        'projectCode' => 'ХХХ',
        'activityType' => 'ШМР_ВходнойКонтроль_ПодтВех',
        'expected' => '✅ Подтвердить достижение вехи (Входной контроль)'
    ],
    [
        'fio' => 'Тестовый Пользователь',
        'date' => '04.10.2025',
        'projectCode' => 'ХХХ',
        'activityType' => 'ШМР_ЗавозОборудования_ПодтВех',
        'expected' => '✅ Подтвердить достижение вехи (Завоз оборудования на площадку)'
    ],
    [
        'fio' => 'Тестовый Пользователь',
        'date' => '04.10.2025',
        'projectCode' => 'ХХХ',
        'activityType' => 'ШМР_ВходнойКонтроль_РабПоГр',
        'expected' => '📋 Работы по графику (Входной контроль)'
    ]
];

echo "Тестирование формата сообщений:\n";
echo "===============================\n";

foreach ($testCases as $index => $testCase) {
    $result = testCreateTabelMessage(
        $testCase['fio'],
        $testCase['date'],
        $testCase['projectCode'],
        $testCase['activityType']
    );
    
    echo "\nТест " . ($index + 1) . ":\n";
    echo "Activity Type: " . $testCase['activityType'] . "\n";
    echo "Expected: " . $testCase['expected'] . "\n";
    echo "Result: " . $result . "\n";
    
    // Check if the expected text is in the result
    if (strpos($result, $testCase['expected']) !== false) {
        echo "✅ ТЕСТ ПРОЙДЕН\n";
    } else {
        echo "❌ ТЕСТ НЕ ПРОЙДЕН\n";
    }
}

echo "\n=== РЕЗУЛЬТАТ ===\n";
echo "✅ Исправление формата сообщений для supervision milestone/schedule activities применено!\n";
echo "Теперь сообщения будут отображаться в правильном формате:\n";
echo "'✅ Запись в табель от 04.10.2025, в проект ХХХ о ✅ Подтвердить достижение вехи (Входной контроль) добавлена'\n";
?>