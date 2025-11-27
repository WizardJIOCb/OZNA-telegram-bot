<?php
/**
 * ПРОСТОЙ ТЕСТ: Проверяем логику форматирования отчетов
 */

echo "🔍 ПРОСТОЙ ТЕСТ: ЛОГИКА ФОРМАТИРОВАНИЯ ОТЧЕТОВ\n";
echo "==============================================\n\n";

// Читаем код TabelEntry.php для анализа
$content = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

echo "📋 ПРОВЕРЯЕМ МАССИВЫ АКТИВНОСТЕЙ:\n";
echo "=================================\n";

// Ищем массив Supervision Installation Works
$pattern = '/\$supervisionInstallationWorks\s*=\s*\[(.*?)\];/s';
if (preg_match($pattern, $content, $matches)) {
    echo "✅ Найден массив supervisionInstallationWorks:\n";
    echo $matches[0] . "\n\n";
} else {
    echo "❌ Массив supervisionInstallationWorks не найден!\n\n";
}

// Ищем массив Automation Equipment Activities
$pattern = '/\$automationEquipmentActivities\s*=\s*\[(.*?)\];/s';
if (preg_match($pattern, $content, $matches)) {
    echo "✅ Найден массив automationEquipmentActivities:\n";
    echo $matches[0] . "\n\n";
} else {
    echo "❌ Массив automationEquipmentActivities не найден!\n\n";
}

echo "📋 ПРОВЕРЯЕМ ФОРМАТЫ ОТЧЕТОВ:\n";
echo "=============================\n";

// Ищем формат для Supervision Installation Works
$pattern = '/in_array\(\$activityType,\s*\$supervisionInstallationWorks\).*?sprintf\((.*?)\);/s';
if (preg_match($pattern, $content, $matches)) {
    echo "✅ Найден формат для supervisionInstallationWorks:\n";
    echo "sprintf(" . trim($matches[1]) . ");\n\n";
} else {
    echo "❌ Формат для supervisionInstallationWorks не найден!\n\n";
}

// Ищем формат для Automation Equipment Activities
$pattern = '/in_array\(\$activityType,\s*\$automationEquipmentActivities\).*?sprintf\((.*?)\);/s';
if (preg_match($pattern, $content, $matches)) {
    echo "✅ Найден формат для automationEquipmentActivities:\n";
    echo "sprintf(" . trim($matches[1]) . ");\n\n";
} else {
    echo "❌ Формат для automationEquipmentActivities не найден!\n\n";
}

echo "📋 ПРОВЕРЯЕМ НАЛИЧИЕ АКТИВНОСТЕЙ В МАССИВАХ:\n";
echo "===========================================\n";

$testActivities = [
    'ШМ_РабПоГрафику' => 'Supervision Installation Works',
    'ШМ_СдачаАвансовогоОтчета' => 'Supervision Installation Works',
    'РабПоГр_Автом' => 'Automation Equipment Activities'
];

foreach ($testActivities as $activity => $expectedArray) {
    if (strpos($content, "'{$activity}'") !== false) {
        echo "✅ Активность '{$activity}' найдена в коде\n";
        
        // Проверяем, в каком именно массиве она находится
        if (strpos($content, "\$supervisionInstallationWorks") !== false && 
            strpos($content, "'{$activity}'") < strpos($content, "\$automationEquipmentActivities")) {
            echo "   → Находится в массиве: supervisionInstallationWorks\n";
        } else {
            echo "   → Проверяем расположение...\n";
        }
    } else {
        echo "❌ Активность '{$activity}' НЕ найдена в коде\n";
    }
    echo "\n";
}

echo "💡 АНАЛИЗ ПРОБЛЕМЫ:\n";
echo "==================\n";
echo "Если массивы и форматы найдены, но отчеты все еще неправильные,\n";
echo "то проблема может быть в:\n\n";
echo "1. 🎯 Неправильном типе активности, который передается в createTabelMessage()\n";
echo "2. 🔄 Логике обработки коллбэков в TabelHandler (короткие → полные названия)\n";
echo "3. 📝 Порядке проверки условий в createTabelMessage()\n\n";

echo "🔍 СЛЕДУЮЩИЙ ШАГ:\n";
echo "================\n";
echo "Нужно проверить, какой именно тип активности попадает в createTabelMessage()\n";
echo "когда пользователь нажимает кнопку шеф-монтажных работ.\n";

?>