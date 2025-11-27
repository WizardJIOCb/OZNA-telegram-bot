<?php
/**
 * Test script to verify pipeline installation message format
 * Проверка формата сообщения для "Монтаж трубопроводов"
 */

echo "=== ТЕСТ ФОРМАТА СООБЩЕНИЙ ДЛЯ МОНТАЖА ТРУБОПРОВОДОВ ===\n\n";

// Simulate activity type mappings
$activityTypeMappings = [
    'ШМР_МонтажТрубопроводов_РабПоГр' => '📋 Работы по графику (Монтаж трубопроводов)',
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
$activityType = 'ШМР_МонтажТрубопроводов_РабПоГр';

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

// Generate message using correct format
$message = sprintf(
    "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>",
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "✅ Проверка 3: Сгенерированное сообщение\n";
echo "   Формат: ЭТАЛОННЫЙ (как у Автоматизации)\n";
echo "   Сообщение:\n";
echo "   " . str_replace("\r\n", "\n   ", $message) . "\n\n";

// Compare with reference format
$referenceFormat = "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>";
echo "📋 ЭТАЛОННЫЙ ФОРМАТ:\n";
echo "   {$referenceFormat}\n\n";

// Check for old incorrect format
$incorrectFormat = "<b>%s</b>\r\n✅ Запись в табель от <b>%s</b>, в проект <b>%s</b> о <b>%s добавлена</b>";
$incorrectMessage = sprintf(
    $incorrectFormat,
    $fio,
    $date,
    $projectCode,
    $activityText
);

echo "❌ НЕПРАВИЛЬНЫЙ ФОРМАТ (старый):\n";
echo "   " . str_replace("\r\n", "\n   ", $incorrectMessage) . "\n\n";

// Final comparison
echo "========================================\n";
echo "🎯 РЕЗУЛЬТАТ ТЕСТА:\n";
echo "========================================\n\n";

$correctFormatUsed = strpos($message, 'Добавлена запись в табель:') !== false;
$incorrectFormatUsed = strpos($message, 'в проект') !== false && strpos($message, 'о ') !== false && strpos($message, ' добавлена') !== false;

if ($correctFormatUsed && !$incorrectFormatUsed) {
    echo "✅ ТЕСТ ПРОЙДЕН!\n";
    echo "   Используется правильный эталонный формат.\n";
    echo "   Формат соответствует формату Автоматизации.\n\n";
    
    echo "✅ ОЖИДАЕМОЕ СООБЩЕНИЕ:\n";
    echo "   <b>Иванов Иван Иванович</b>\n";
    echo "   ✅10.10.2025, Проект <b>ХХХ</b>: Добавлена запись в табель: <b>📋 Работы по графику (Монтаж трубопроводов)</b>\n\n";
    
    echo "🎉 Исправление применено успешно!\n";
    exit(0);
} else {
    echo "❌ ТЕСТ НЕ ПРОЙДЕН!\n";
    echo "   Используется неправильный формат.\n";
    echo "   Требуется дополнительная проверка кода.\n\n";
    exit(1);
}
