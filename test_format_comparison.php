<?php

echo "=== СРАВНЕНИЕ ФОРМАТОВ АКТИВНОСТЕЙ ===\n\n";

// Простое сравнение паттернов на основе анализа кода
$projectCode = "ОИ834";

echo "🔍 ПРОВЕРКА ПАТТЕРНОВ НА ОСНОВЕ КОДА:\n";
echo "=============================================\n\n";

// Эталонные паттерны из кода
$confirmationPattern = "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?";
$reportPattern = "<b>%s</b>\r\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>";

echo "1. ЭТАЛОННЫЕ ПАТТЕРНЫ:\n";
echo "   Подтверждение: '$confirmationPattern'\n";
echo "   Отчет: '$reportPattern'\n\n";

// Примеры для разных активностей
$activities = [
    ['Участие в ОПИ', 'УчастиеВОПИ'],
    ['Шеф-наладочные работы', 'ШефНаладочныеРаботы']
];

echo "2. ПРИМЕРЫ СООБЩЕНИЙ:\n";
echo "======================\n\n";

foreach ($activities as [$activityName, $activityCode]) {
    // Подтверждение
    $confirmation = sprintf($confirmationPattern, $projectCode, $activityName);
    echo "$activityName (Подтверждение):\n";
    echo "   $confirmation\n\n";
    
    // Отчет
    $report = sprintf($reportPattern, "Иванов И.И.", "12:30 15.03.2024", $projectCode, $activityName);
    echo "$activityName (Отчет):\n";
    echo "   $report\n\n";
}

echo "3. АНАЛИЗ КОДА:\n";
echo "===============\n\n";

echo "Проектные активности в TabelEntry.php должны использовать:\n";
echo "- Массив \$projectActivities в getConfirmationMessage()\n";
echo "- Массив \$projectActivities в createTabelMessage()\n";
echo "- Одинаковые форматы сообщений\n\n";

echo "4. ПРОВЕРКА НАЛИЧИЯ В МАССИВАХ:\n";
echo "==========================================\n\n";

// Простое чтение файла для поиска строк
$tabelFile = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Поиск массивов projectActivities
$confirmationCount = substr_count($tabelFile, '\$projectActivities');
$shefCount = substr_count($tabelFile, 'ШефНаладочныеРаботы');

echo "Массивы \$projectActivities в TabelEntry.php: $confirmationCount\n";
echo "Упоминания 'ШефНаладочныеРаботы': $shefCount\n\n";

if ($confirmationCount >= 2 && $shefCount >= 1) {
    echo "✅ ШефНаладочныеРаботы НАЙДЕНЫ в файле\n";
} else {
    echo "❌ ОШИБКА: Не все массивы содержат ШефНаладочныеРаботы\n";
}

?>

$projectCode = "ОИ834";

echo "🔍 ПРОВЕРКА ФОРМАТОВ СООБЩЕНИЙ ПОДТВЕРЖДЕНИЯ:\n";
echo "============================================\n\n";

// Тест для установленного формата (УчастиеВОПИ)
$confirmationOPI = $tabelEntry->getConfirmationMessage('УчастиеВОПИ', $projectCode);
echo "1. ЭТАЛОННЫЙ ФОРМАТ (УчастиеВОПИ):\n";
echo "   $confirmationOPI\n\n";

// Тест для нового формата (ШефНаладочныеРаботы)
$confirmationSHEF = $tabelEntry->getConfirmationMessage('ШефНаладочныеРаботы', $projectCode);
echo "2. НОВЫЙ ФОРМАТ (ШефНаладочныеРаботы):\n";
echo "   $confirmationSHEF\n\n";

echo "🔍 ПРОВЕРКА ФОРМАТОВ ФИНАЛЬНЫХ ОТЧЕТОВ:\n";
echo "=====================================\n\n";

// Тест для финального отчета УчастиеВОПИ
$fio = "Иванов И.И.";
$date = "12:30 15.03.2024";
$activityText = "Участие в ОПИ";

$reportOPI = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'УчастиеВОПИ', $activityText);
echo "1. ЭТАЛОННЫЙ ОТЧЕТ (УчастиеВОПИ):\n";
echo "   $reportOPI\n\n";

// Тест для финального отчета ШефНаладочныеРаботы  
$activityTextSHEF = "Шеф-наладочные работы";

$reportSHEF = $tabelEntry->createTabelMessage($fio, $date, $projectCode, 'ШефНаладочныеРаботы', $activityTextSHEF);
echo "2. НОВЫЙ ОТЧЕТ (ШефНаладочныеРаботы):\n";
echo "   $reportSHEF\n\n";

echo "🔍 АНАЛИЗ СООТВЕТСТВИЯ ФОРМАТОВ:\n";
echo "===============================\n\n";

// Проверим соответствие паттернов
$opiPattern = "Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?";
$shefPattern = $confirmationSHEF;

echo "3. ПАТТЕРН ПОДТВЕРЖДЕНИЯ:\n";
echo "   Эталонный: 'Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?'\n";
echo "   УчастиеВОПИ: '$confirmationOPI'\n";
echo "   ШефНаладочныеРаботы: '$confirmationSHEF'\n";

if ($confirmationOPI === $confirmationSHEF) {
    echo "   ✅ ФОРМАТЫ ПОДТВЕРЖДЕНИЯ СОВПАДАЮТ\n\n";
} else {
    echo "   ❌ ФОРМАТЫ ПОДТВЕРЖДЕНИЯ НЕ СОВПАДАЮТ\n\n";
}

// Проверим паттерны финальных отчетов
$reportPatternOPI = preg_replace('/<b>[^<]+<\/b>/', '<b>%s</b>', $reportOPI);
$reportPatternSHEF = preg_replace('/<b>[^<]+<\/b>/', '<b>%s</b>', $reportSHEF);

echo "4. ПАТТЕРН ФИНАЛЬНОГО ОТЧЕТА:\n";
echo "   УчастиеВОПИ:    '$reportOPI'\n";
echo "   ШефНаладочныеРаботы: '$reportSHEF'\n";

if (strpos($reportOPI, "Проект <b>$projectCode</b>: Добавлена запись в табель:") !== false && 
    strpos($reportSHEF, "Проект <b>$projectCode</b>: Добавлена запись в табель:") !== false) {
    echo "   ✅ ФОРМАТЫ ФИНАЛЬНЫХ ОТЧЕТОВ СОВПАДАЮТ\n\n";
} else {
    echo "   ❌ ФОРМАТЫ ФИНАЛЬНЫХ ОТЧЕТОВ НЕ СОВПАДАЮТ\n\n";
}

echo "=== ЗАКЛЮЧЕНИЕ ===\n";
echo "Все проектные активности должны использовать единый формат:\n";
echo "1. Подтверждение: 'Проект <b>%s</b>: <b>%s</b>. Отправить запись в табель?'\n";
echo "2. Отчет: '<b>%s</b>\\r\\n✅%s, Проект <b>%s</b>: Добавлена запись в табель: <b>%s</b>'\n";

?>