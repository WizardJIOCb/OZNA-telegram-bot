<?php
/**
 * ДИАГНОСТИКА: Тест форматирования отчетов для ШМР vs Автоматизация
 */

echo "🔍 ДИАГНОСТИКА: СРАВНЕНИЕ ФОРМАТОВ ОТЧЕТОВ\n";
echo "==========================================\n\n";

// Имитируем метод createTabelMessage из TabelEntry
function simulateCreateTabelMessage($fio, $date, $projectCode, $activityType, $idleReason = '') {
    // Имитируем логику из реального TabelEntry.php
    
    // Activity type mappings
    $activityTypeMappings = [
        // Шеф-монтажные работы
        'ШМ_РабПоГрафику' => '📅 Работы по графику',
        'ШМ_СдачаАвансовогоОтчета' => '💰 Сдача авансового отчета',
        // Автоматизация
        'РабПоГр_Автом' => '📅 Работы по графику (Автоматизация)'
    ];
    
    $activityText = $activityTypeMappings[$activityType] ?? $activityType;
    
    // Проверяем format logic из реального TabelEntry.php
    
    // Supervision Installation Works - use same format as Project Activities  
    $supervisionInstallationWorks = [
        'ШМ_РабПоГрафику', 'ШМ_ПодготовкаКПоездке', 'ШМ_Дорога', 'ШМ_Инструктаж', 'ШМ_СдачаАвансовогоОтчета'
    ];
    
    if (in_array($activityType, $supervisionInstallationWorks)) {
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
    
    // Fallback - default format (что возможно и происходит)
    return sprintf(
        "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>",
        $fio,
        $date,
        $projectCode,
        $activityText
    );
}

// Тестовые данные
$testData = [
    'fio' => 'Иванов Иван Иванович',
    'date' => '27.09.2025',
    'projectCode' => 'ОИ837'
];

echo "📋 ТЕСТОВЫЕ ДАННЫЕ:\n";
echo "ФИО: {$testData['fio']}\n";
echo "Дата: {$testData['date']}\n";  
echo "Проект: {$testData['projectCode']}\n\n";

echo "🧪 ТЕСТ 1: АВТОМАТИЗАЦИЯ (ЭТАЛОН)\n";
echo "=================================\n";
$automationReport = simulateCreateTabelMessage(
    $testData['fio'],
    $testData['date'],
    $testData['projectCode'],
    'РабПоГр_Автом'
);
echo "Активность: РабПоГр_Автом\n";
echo "Отчет:\n{$automationReport}\n\n";

echo "🧪 ТЕСТ 2: ШЕФ-МОНТАЖ (ДОЛЖЕН БЫТЬ КАК ЭТАЛОН)\n";
echo "==============================================\n";
$supervisionReport = simulateCreateTabelMessage(
    $testData['fio'],
    $testData['date'],
    $testData['projectCode'],
    'ШМ_СдачаАвансовогоОтчета'
);
echo "Активность: ШМ_СдачаАвансовогоОтчета\n";
echo "Отчет:\n{$supervisionReport}\n\n";

echo "📊 АНАЛИЗ РЕЗУЛЬТАТОВ:\n";
echo "======================\n";

if ($automationReport === $supervisionReport) {
    echo "✅ ФОРМАТЫ ИДЕНТИЧНЫ!\n";
    echo "Проблема НЕ в логике форматирования.\n\n";
} else {
    echo "❌ ФОРМАТЫ ОТЛИЧАЮТСЯ!\n\n";
    echo "Эталон (Автоматизация):\n'{$automationReport}'\n\n";
    echo "ШМР (Шеф-монтаж):\n'{$supervisionReport}'\n\n";
}

echo "🧪 ТЕСТ 3: ПРОВЕРКА ДРУГОГО ТИПА ШМР\n";
echo "====================================\n";
$supervisionReport2 = simulateCreateTabelMessage(
    $testData['fio'],
    $testData['date'],
    $testData['projectCode'],
    'ШМ_РабПоГрафику'
);
echo "Активность: ШМ_РабПоГрафику\n";
echo "Отчет:\n{$supervisionReport2}\n\n";

echo "💡 ЗАКЛЮЧЕНИЕ:\n";
echo "==============\n";
echo "Если все отчеты ШМР показывают правильный формат (как эталон),\n";
echo "то проблема в другом месте:\n\n";
echo "1. 🎯 Неправильный тип активности попадает в createTabelMessage()\n";
echo "2. 🔄 Проблема в маппинге коллбэков\n";
echo "3. 📝 Используется старая версия файла TabelEntry.php\n";
echo "4. 💾 Проблема с кэшированием PHP-кода\n\n";

echo "🔍 СЛЕДУЮЩИЕ ШАГИ:\n";
echo "==================\n";
echo "1. Проверить, какой точно тип активности передается в createTabelMessage()\n";
echo "2. Добавить debug логи в реальный код\n";
echo "3. Убедиться, что используется правильная версия файлов\n";

?>