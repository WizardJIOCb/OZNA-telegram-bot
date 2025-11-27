<?php

echo "=== ПРОВЕРКА ТОЧНОГО ИСПРАВЛЕНИЯ ДЛЯ ПРОВЕРКА МОНТАЖНОЙ ПЛОЩАДКИ ===\n\n";

// Имитируем точные данные, которые должны поступить в createTabelMessage
$fio = "Иванов Иван Иванович";
$date = "06.10.2025";
$projectCode = "ХХХ";
$activityType = "ПроверкаМонтажнойПлощадки";
$idleReason = "";

echo "1. ВХОДНЫЕ ПАРАМЕТРЫ:\n";
echo "   fio: '{$fio}'\n";
echo "   date: '{$date}'\n";
echo "   projectCode: '{$projectCode}'\n";
echo "   activityType: '{$activityType}'\n";
echo "   idleReason: '{$idleReason}'\n\n";

// Проверяем сопоставление
$activityTypeMappings = [
    'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки',
];

$activityText = $activityTypeMappings[$activityType] ?? $activityType;
echo "2. СОПОСТАВЛЕНИЕ АКТИВНОСТИ:\n";
echo "   Найдено в массиве: " . (isset($activityTypeMappings[$activityType]) ? "ДА" : "НЕТ") . "\n";
echo "   activityText: '{$activityText}'\n\n";

// Проверяем принадлежность к commissioningActivities
$commissioningActivities = [
    'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
    'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
    // Supervision works sub-activities
    'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
    // Simple supervision works activities
    'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
];

echo "3. ПРОВЕРКА ПРИНАДЛЕЖНОСТИ К COMMISSIONING ACTIVITIES:\n";
if (in_array($activityType, $commissioningActivities)) {
    echo "   ✅ НАЙДЕНО в commissioningActivities - будет использовать commissioning формат\n";
    
    // Генерируем сообщение
    $result = sprintf(
        "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
        $fio,
        $date,
        $projectCode,
        $activityText
    );
    
    echo "\n4. РЕЗУЛЬТАТ:\n";
    echo "   Сгенерированное сообщение:\n";
    echo "   '{$result}'\n\n";
    
    echo "5. АНАЛИЗ ПРОБЛЕМЫ:\n";
    
    // Проверяем каждый компонент
    if (empty($fio)) {
        echo "   ❌ ПРОБЛЕМА: fio пустое!\n";
    } else {
        echo "   ✅ fio корректное\n";
    }
    
    if (empty($date)) {
        echo "   ❌ ПРОБЛЕМА: date пустое!\n";
    } else {
        echo "   ✅ date корректное\n";
    }
    
    if (empty($projectCode) || $projectCode === 'Unknown') {
        echo "   ⚠️ ВОЗМОЖНАЯ ПРОБЛЕМА: projectCode = '{$projectCode}'\n";
    } else {
        echo "   ✅ projectCode корректное\n";
    }
    
    if (empty($activityText)) {
        echo "   ❌ ПРОБЛЕМА: activityText пустое!\n";
    } else {
        echo "   ✅ activityText корректное\n";
    }
    
    // Проверяем, содержит ли результат информацию об активности
    if (strpos($result, $activityText) !== false) {
        echo "   ✅ Результат содержит информацию об активности\n";
    } else {
        echo "   ❌ КРИТИЧЕСКАЯ ОШИБКА: Результат НЕ содержит информацию об активности!\n";
    }
    
    // Проверяем на проблемные символы
    if (strpos($result, 'о  добавлена') !== false) {
        echo "   ❌ НАЙДЕН ДЕФЕКТ: Двойной пробел 'о  добавлена' в результате!\n";
        echo "   Это указывает на то, что activityText был пустым при генерации\n";
    } else {
        echo "   ✅ Дефект с двойным пробелом НЕ обнаружен\n";
    }
    
} else {
    echo "   ❌ НЕ НАЙДЕНО в commissioningActivities - КРИТИЧЕСКАЯ ОШИБКА!\n";
}

echo "\n=== ЗАКЛЮЧЕНИЕ ===\n";
echo "Если все проверки прошли успешно, но проблема остается, то:\n";
echo "1. Проверьте логи на наличие отладочных сообщений\n";
echo "2. Убедитесь, что сервер был перезапущен после изменений\n";
echo "3. Проверьте, что параметры передаются правильно в createTabelMessage\n";
echo "4. Убедитесь, что нет других проблем с кодировкой или логикой\n";

?>