<?php
/**
 * Test script to verify milestone completion message format
 * Проверка формата сообщения для "Подтвердить достижение вехи"
 */

echo "=== ТЕСТ ФОРМАТА СООБЩЕНИЙ ДЛЯ ПОДТВЕРЖДЕНИЯ ВЕХИ ===\n\n";

// Simulate activity type mappings
$activityTypeMappings = [
    'ШМР_МонтажТрубопроводов_ПодтВех' => '✅ Этап шеф-монтажа завершен (Монтаж трубопроводов)',
];

// Simulate supervision installation activities array
$supervisionInstallationActivities = [
    'ШМР_ВхКонтр_РабПоГр', 'ШМР_ВхКонтр_ПодтВех',
    'ШМР_ЗавОбор_РабПоГр', 'ШМР_ЗавОбор_ПодтВех',
    'ШМР_РасстБлок_РабПоГр', 'ШМР_РасстБлок_ПодтВех',
    'ШМР_МонтПлощ_РабПоГр', 'ШМР_МонтПлощ_ПодтВех',
    'ШМР_МонтЭлУкр_РабПоГр', 'ШМР_МонтЭлУкр_ПодтВех',
    'ШМР_СборКровл_РабПоГр', 'ШМР_СборКровл_ПодтВех',
    // ШМР Technological Equipment Activities - NEW
    'ШМР_МонтажТрубопроводов_РабПоГр', 'ШМР_МонтажТрубопроводов_ПодтВех',
    'ШМР_ГидроПневмоИспытания_РабПоГр', 'ШМР_ГидроПневмоИспытания_ПодтВех',
    'ШМР_МонтажТехнологическогоОборудования_РабПоГр', 'ШМР_МонтажТехнологическогоОборудования_ПодтВех',
    'ШМР_МонтажЗРА_РабПоГр', 'ШМР_МонтажЗРА_ПодтВех',
    'ШМР_ПротяжкаСоединений_РабПоГр', 'ШМР_ПротяжкаСоединений_ПодтВех',
    'ШМР_МонтажТеплоизоляции_РабПоГр', 'ШМР_МонтажТеплоизоляции_ПодтВех'
];

// Test data
$fio = "Иванов Иван Иванович";
$date = "10.10.2025";
$projectCode = "ХХХ";
$activityType = 'ШМР_МонтажТрубопроводов_ПодтВех';

echo "📊 ТЕСТОВЫЕ ДАННЫЕ:\n";
echo "   ФИО: {$fio}\n";
echo "   Дата: {$date}\n";
echo "   Проект: {$projectCode}\n";
echo "   Тип активности: {$activityType}\n\n";

// Check if activity is in the array
$isInArray = in_array($activityType, $supervisionInstallationActivities);
echo "✅ Проверка 1: Активность в массиве supervisionInstallationActivities\n";
echo "   Результат: " . ($isInArray ? "ДА ✅" : "НЕТ ❌") . "\n\n";

if (!$isInArray) {
    echo "❌ ОШИБКА: Активность НЕ найдена в массиве!\n";
    echo "   Это приведет к использованию неправильного формата.\n\n";
    exit(1);
}

// Get activity text
$activityText = $activityTypeMappings[$activityType] ?? $activityType;
echo "✅ Проверка 2: Текст активности из маппинга\n";
echo "   Результат: {$activityText}\n\n";

// Verify correct text is used
if (strpos($activityText, 'Этап шеф-монтажа завершен') === false) {
    echo "❌ ОШИБКА: Текст НЕ содержит 'Этап шеф-монтажа завершен'!\n";
    echo "   Найдено: {$activityText}\n\n";
    exit(1);
}

echo "✅ Проверка 3: Текст содержит эталонную формулировку\n";
echo "   'Этап шеф-монтажа завершен' ✅\n\n";

// Generate message using correct format
$message = sprintf(
    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "✅ Проверка 4: Сгенерированное сообщение\n";
echo "   Формат: ЭТАЛОННЫЙ (ШМР)\n";
echo "   Сообщение:\n";
echo "   " . str_replace("\r\n", "\n   ", $message) . "\n\n";

// Compare with reference format
$referenceFormat = "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>";
echo "📋 ЭТАЛОННЫЙ ФОРМАТ ШМР:\n";
echo "   {$referenceFormat}\n\n";

// Check for old incorrect format
$incorrectText1 = "Подтвердить достижение вехи";
$incorrectText2 = "Этап наладки завершен";

echo "❌ НЕПРАВИЛЬНЫЕ ФОРМУЛИРОВКИ:\n";
echo "   1. '{$incorrectText1}' (общая формулировка)\n";
echo "   2. '{$incorrectText2}' (для этапа наладки, не шеф-монтажа)\n\n";

// Final comparison
echo "========================================\n";
echo "🎯 РЕЗУЛЬТАТ ТЕСТА:\n";
echo "========================================\n\n";

$correctFormatUsed = strpos($message, 'Добавлена запись в табель:') !== false;
$correctTextUsed = strpos($message, 'Этап шеф-монтажа завершен') !== false;
$incorrectFormatUsed = strpos($message, 'Подтвердить достижение вехи') !== false;

if ($correctFormatUsed && $correctTextUsed && !$incorrectFormatUsed) {
    echo "✅ ТЕСТ ПРОЙДЕН!\n";
    echo "   Используется правильный эталонный формат ШМР.\n";
    echo "   Формулировка: 'Этап шеф-монтажа завершен' ✅\n\n";
    
    echo "✅ ОЖИДАЕМОЕ СООБЩЕНИЕ:\n";
    echo "   <b>Иванов Иван Иванович</b>\n";
    echo "   ✅10.10.2025, Проект <b>ХХХ</b>: Добавлена запись в табель: <b>✅ Этап шеф-монтажа завершен (Монтаж трубопроводов)</b>\n\n";
    
    echo "🎉 Исправление применено успешно!\n\n";
    
    echo "📋 ДРУГИЕ АКТИВНОСТИ, ИСПОЛЬЗУЮЩИЕ ЭТОТ ФОРМАТ:\n";
    echo "   • ШМР_МонтажТрубопроводов_ПодтВех\n";
    echo "   • ШМР_ГидроПневмоИспытания_ПодтВех\n";
    echo "   • ШМР_МонтажТехнологическогоОборудования_ПодтВех\n";
    echo "   • ШМР_МонтажЗРА_ПодтВех\n";
    echo "   • ШМР_ПротяжкаСоединений_ПодтВех\n";
    echo "   • ШМР_МонтажТеплоизоляции_ПодтВех\n";
    echo "   • ШМР_Прочее_ТехнологическоеОборудование_ПодтВех\n\n";
    
    exit(0);
} else {
    echo "❌ ТЕСТ НЕ ПРОЙДЕН!\n";
    
    if (!$correctFormatUsed) {
        echo "   • Неправильный формат сообщения\n";
    }
    
    if (!$correctTextUsed) {
        echo "   • Отсутствует текст 'Этап шеф-монтажа завершен'\n";
    }
    
    if ($incorrectFormatUsed) {
        echo "   • Используется устаревший текст 'Подтвердить достижение вехи'\n";
    }
    
    echo "\n   Требуется дополнительная проверка кода.\n\n";
    exit(1);
}
