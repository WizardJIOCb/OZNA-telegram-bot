<?php

echo "=== ДИАГНОСТИКА АКТИВНОСТИ 'ПРОВЕРКА МОНТАЖНОЙ ПЛОЩАДКИ' ===\n\n";

// Проверяем сопоставление активности в TabelEntry
$activityTypeMappings = [
    'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки',
];

$activityType = 'ПроверкаМонтажнойПлощадки';
$activityText = $activityTypeMappings[$activityType] ?? $activityType;

echo "1. ПРОВЕРКА СОПОСТАВЛЕНИЯ:\n";
echo "   Activity Type: '{$activityType}'\n";
echo "   Activity Text: '{$activityText}'\n";
echo "   Найдено в массиве: " . (isset($activityTypeMappings[$activityType]) ? "ДА" : "НЕТ") . "\n\n";

// Проверяем, в каком массиве должна быть активность
echo "2. ПРОВЕРКА ПРИНАДЛЕЖНОСТИ К МАССИВАМ:\n";

$commissioningActivities = [
    'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
    'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
    // Supervision works sub-activities
    'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
    // Simple supervision works activities
    'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
];

echo "   В commissioningActivities: " . (in_array($activityType, $commissioningActivities) ? "ДА" : "НЕТ") . "\n";

if (in_array($activityType, $commissioningActivities)) {
    echo "   ✅ ПРАВИЛЬНО: Должен использовать формат commissioning activities\n";
    echo "   Ожидаемый формат: '<b>ФИО</b>\\r\\n✅ДАТА, Проект <b>КОД</b>: Добавлена запись в табель: <b>АКТИВНОСТЬ</b>'\n";
} else {
    echo "   ❌ ОШИБКА: Не найден в правильном массиве!\n";
}

echo "\n3. ОЖИДАЕМЫЙ РЕЗУЛЬТАТ:\n";
$fio = "Иванов Иван Иванович";
$date = "06.10.2025";
$projectCode = "ХХХ";

$expectedMessage = sprintf(
    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "   Ожидаемое сообщение:\n";
echo "   '{$expectedMessage}'\n\n";

echo "4. ПРОБЛЕМНЫЕ МЕСТА ДЛЯ ПРОВЕРКИ:\n";
echo "   - Убедиться, что activityTypeMappings содержит правильное сопоставление\n";
echo "   - Проверить, что активность включена в commissioningActivities массив\n";
echo "   - Убедиться, что createTabelMessage вызывается с правильными параметрами\n";
echo "   - Проверить логи на наличие ошибок в поиске activity text\n";

?>