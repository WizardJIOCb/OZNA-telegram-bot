<?php

echo "=== ПРОВЕРКА ИСПРАВЛЕНИЯ ФОРМАТА ДЛЯ ШЕФ-НАЛАДОЧНЫХ РАБОТ ===\n\n";

// Читаем содержимое файла TabelEntry.php для проверки
$tabelFile = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "🔍 АНАЛИЗ МАССИВОВ \$projectActivities В TABELENTRY.PHP:\n";
echo "==================================================\n\n";

// Находим все вхождения $projectActivities массивов
$pattern1 = '/\$projectActivities\s*=\s*\[([^\]]+)\];/';
preg_match_all($pattern1, $tabelFile, $matches);

$arrayCount = count($matches[0]);
echo "Найдено массивов \$projectActivities: $arrayCount\n\n";

$shefFoundInArrays = 0;
$arrayContents = [];

for ($i = 0; $i < $arrayCount; $i++) {
    echo "МАССИВ " . ($i + 1) . ":\n";
    echo "Содержимое: " . trim($matches[1][$i]) . "\n";
    
    $arrayContent = $matches[1][$i];
    $arrayContents[] = $arrayContent;
    
    if (strpos($arrayContent, 'ШефНаладочныеРаботы') !== false) {
        echo "✅ Содержит 'ШефНаладочныеРаботы'\n";
        $shefFoundInArrays++;
    } else {
        echo "❌ НЕ содержит 'ШефНаладочныеРаботы'\n";
    }
    echo "\n";
}

echo "📊 СТАТИСТИКА:\n";
echo "=============\n";
echo "Всего массивов \$projectActivities: $arrayCount\n";
echo "Массивов с 'ШефНаладочныеРаботы': $shefFoundInArrays\n";

if ($shefFoundInArrays === $arrayCount && $arrayCount >= 2) {
    echo "✅ ИСПРАВЛЕНИЕ УСПЕШНО: Все массивы синхронизированы!\n\n";
} else {
    echo "❌ ПРОБЛЕМА ОСТАЕТСЯ: Не все массивы содержат 'ШефНаладочныеРаботы'\n\n";
}

echo "🔍 ПРОВЕРКА СИНХРОНИЗАЦИИ МАССИВОВ:\n";
echo "===================================\n\n";

if ($arrayCount >= 2) {
    // Сравним содержимое массивов (убирая пробелы и переносы строк)
    $normalizedArrays = array_map(function($content) {
        return preg_replace('/\s+/', '', $content);
    }, $arrayContents);
    
    // Проверяем, содержат ли все массивы основные проектные активности
    $coreActivities = ['УчастиеВЗПИ', 'УчастиеВОПИ', 'ШефНаладочныеРаботы'];
    
    for ($i = 0; $i < $arrayCount; $i++) {
        echo "МАССИВ " . ($i + 1) . " содержит:\n";
        foreach ($coreActivities as $activity) {
            if (strpos($arrayContents[$i], $activity) !== false) {
                echo "  ✅ $activity\n";
            } else {
                echo "  ❌ $activity (ОТСУТСТВУЕТ)\n";
            }
        }
        echo "\n";
    }
}

echo "🔍 ПРОВЕРКА ФОРМАТОВ СООБЩЕНИЙ:\n";
echo "===============================\n\n";

// Проверим, что оба массива используются в правильных местах
$confirmationContext = strpos($tabelFile, 'getConfirmationMessage') !== false;
$reportContext = strpos($tabelFile, 'createTabelMessage') !== false;

echo "Методы найдены:\n";
echo "- getConfirmationMessage: " . ($confirmationContext ? "✅ ДА" : "❌ НЕТ") . "\n";
echo "- createTabelMessage: " . ($reportContext ? "✅ ДА" : "❌ НЕТ") . "\n\n";

// Проверим правильные форматы для проектных активностей
$correctConfirmationPattern = 'sprintf(.*"Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель\?"';
$correctReportPattern = 'sprintf(.*"<b>%s</b>\\\\r\\\\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>"';

$hasCorrectConfirmation = preg_match('/' . $correctConfirmationPattern . '/', $tabelFile);
$hasCorrectReport = preg_match('/' . $correctReportPattern . '/', $tabelFile);

echo "Форматы сообщений:\n";
echo "- Подтверждение: " . ($hasCorrectConfirmation ? "✅ Правильный формат найден" : "❌ Правильный формат НЕ найден") . "\n";
echo "- Отчет: " . ($hasCorrectReport ? "✅ Правильный формат найден" : "❌ Правильный формат НЕ найден") . "\n\n";

echo "🎯 ОБЩАЯ ОЦЕНКА ИСПРАВЛЕНИЯ:\n";
echo "============================\n\n";

$score = 0;
$maxScore = 4;

if ($shefFoundInArrays === $arrayCount && $arrayCount >= 2) $score++;
if ($confirmationContext && $reportContext) $score++;
if ($hasCorrectConfirmation) $score++;
if ($hasCorrectReport) $score++;

echo "Набрано баллов: $score из $maxScore\n\n";

if ($score === $maxScore) {
    echo "🎉 ОТЛИЧНО! Исправление выполнено полностью:\n";
    echo "   ✅ 'ШефНаладочныеРаботы' добавлен во все нужные массивы\n";
    echo "   ✅ Используются правильные форматы сообщений\n";
    echo "   ✅ Система готова к корректной работе\n\n";
} else {
    echo "⚠️ ВНИМАНИЕ! Исправление выполнено частично.\n";
    echo "   Проверьте детали выше и завершите оставшиеся исправления.\n\n";
}

echo "📚 РЕКОМЕНДАЦИИ:\n";
echo "================\n";
echo "1. Всегда добавляйте новые проектные активности в ОБА массива \$projectActivities\n";
echo "2. Используйте стандартный формат для всех проектных активностей\n";
echo "3. Тестируйте как подтверждение, так и финальный отчет\n";
echo "4. Документируйте изменения в ACTIVITY_FORMAT_STANDARD.md\n\n";

echo "📝 Для детальной информации смотрите: ACTIVITY_FORMAT_STANDARD.md\n";

?>