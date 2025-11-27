<?php

echo "=== ПОШАГОВЫЙ ТЕСТ ОБРАБОТКИ КНОПКИ 'ПРОВЕРКА МОНТАЖНОЙ ПЛОЩАДКИ' ===\n\n";

echo "ШАГ 1: ПРОВЕРКА CALLBACK'А ИЗ MessageService\n";
echo "===========================================\n";

// Проверяем что кнопка создается с правильным callback'ом
echo "Кнопка в MessageService должна иметь:\n";
echo "  text: '🏗️ Проверка монтажной площадки'\n";
echo "  callback_data: 'cat_ШМР_АналФунд'\n\n";

echo "ШАГ 2: ПРОВЕРКА СОПОСТАВЛЕНИЯ В routeTabelCallback\n";
echo "=================================================\n";

$callbackData = 'cat_ШМР_АналФунд';
echo "Входящий callback: '{$callbackData}'\n";

// Имитируем логику extractCategoryFromCallback
function extractCategoryFromCallback($callbackData) {
    // Проверяем сопоставления из routeTabelCallback
    $shortFormats = [
        'cat_ШМР_АналФунд' => 'Табель_ПроверкаМонтажнойПлощадки'
    ];
    
    if (isset($shortFormats[$callbackData])) {
        return $shortFormats[$callbackData];
    }
    
    return substr($callbackData, strlen('cat_'));
}

$category = extractCategoryFromCallback($callbackData);
echo "Сопоставленная category: '{$category}'\n";

if ($category === 'Табель_ПроверкаМонтажнойПлощадки') {
    echo "✅ ПРАВИЛЬНОЕ сопоставление\n";
} else {
    echo "❌ НЕПРАВИЛЬНОЕ сопоставление!\n";
}

echo "\nШАГ 3: ПРОВЕРКА ОБРАБОТКИ В handleOtherWorkTypes\n";
echo "===============================================\n";

if ($category === 'Табель_ПроверкаМонтажнойПлощадки') {
    echo "✅ Условие if (\$category === 'Табель_ПроверкаМонтажнойПлощадки') должно сработать\n";
    echo "✅ Должен выполниться блок:\n";
    echo "   - error_log(\"TabelHandler: handling mounting area inspection confirmation\")\n";
    echo "   - getConfirmationMessage('ПроверкаМонтажнойПлощадки', \$projectCode)\n";
    echo "   - createConfirmationKeyboard(\"cat_ШМР_АналФунд_yes\")\n";
    echo "   - sendMessage с диалогом подтверждения\n";
} else {
    echo "❌ Условие НЕ сработает - проблема в сопоставлении!\n";
}

echo "\nШАГ 4: ПРОВЕРКА getConfirmationMessage\n";
echo "=====================================\n";

// Проверяем что ПроверкаМонтажнойПлощадки находится в нужном массиве
$commissioningActivities = [
    'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
    'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
    'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
    'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
];

$activityType = 'ПроверкаМонтажнойПлощадки';
if (in_array($activityType, $commissioningActivities)) {
    echo "✅ '{$activityType}' найдена в commissioningActivities\n";
    echo "✅ Должно использовать формат: 'Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?'\n";
} else {
    echo "❌ '{$activityType}' НЕ найдена в commissioningActivities!\n";
}

echo "\nШАГ 5: ПРОВЕРКА ОЖИДАЕМОГО РЕЗУЛЬТАТА\n";
echo "====================================\n";

$projectCode = "ОИ834";
$activityTypeMappings = [
    'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки',
];
$activityName = $activityTypeMappings[$activityType] ?? $activityType;

$expectedConfirmation = sprintf(
    "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?",
    $projectCode,
    $activityName
);

echo "Ожидаемое сообщение подтверждения:\n";
echo "'{$expectedConfirmation}'\n";

echo "\nКнопки подтверждения:\n";
echo "  [Да] - callback: 'cat_ШМР_АналФунд_yes'\n";
echo "  [Нет] - callback: 'cat_no'\n";

echo "\n=== ДИАГНОСТИЧЕСКИЕ РЕКОМЕНДАЦИИ ===\n";
echo "1. Проверьте логи на наличие сообщения: 'TabelHandler: handling mounting area inspection confirmation'\n";
echo "2. Если сообщение НЕТ - проблема в сопоставлении callback'а\n";
echo "3. Если сообщение ЕСТЬ, но нет диалога - проблема в createConfirmationKeyboard или sendMessage\n";
echo "4. Убедитесь что в conversation есть 'project_code'\n";

echo "\n=== ВОЗМОЖНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ ===\n";
echo "1. ❌ Кнопка в MessageService имеет неправильный callback\n";
echo "2. ❌ Сопоставление в routeTabelCallback неправильное\n";
echo "3. ❌ Условие в handleOtherWorkTypes не срабатывает\n";
echo "4. ❌ Отсутствует project_code в conversation\n";
echo "5. ❌ Ошибка в createConfirmationKeyboard\n";

?>