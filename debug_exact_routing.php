<?php
// Точный дебаг маршрутизации для cat_ТабИнстр_yes

echo "=== ТОЧНЫЙ ДЕБАГ МАРШРУТИЗАЦИИ ===\n\n";

$problematicCallback = 'cat_ТабИнстр_yes';
$workingCallback = 'cat_ТабЭлОб_РабПоГр_yes';

echo "Проблемный callback: {$problematicCallback}\n";
echo "Рабочий callback: {$workingCallback}\n\n";

echo "=== ПРОВЕРКА КАЖДОГО ШАГА МАРШРУТИЗАЦИИ ===\n\n";

// Проверяем routeTabelCallback порядок
echo "Порядок проверок в routeTabelCallback:\n";
echo "1. isActivityExecution() - проверяем\n";
echo "2. isActivityConfirmation() - проверяем\n";
echo "3. handleOtherWorkTypes() - fallback\n\n";

// Тестируем isActivityExecution для рабочего callback
echo "РАБОЧИЙ CALLBACK: {$workingCallback}\n";
echo "isActivityExecution() проверки:\n";

// Электрооборудование pattern
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $workingCallback, $matches)) {
    echo "✓ Матчит pattern: ^cat_ТабЭлОб_(.+)_yes\$\n";
    echo "✓ Извлеченный ключ: {$matches[1]}\n";
    echo "✓ Результат: isActivityExecution() = TRUE\n";
    echo "✓ Роутится в: handleActivityExecution()\n";
} else {
    echo "✗ НЕ матчит electrical pattern\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Тестируем isActivityExecution для проблемного callback
echo "ПРОБЛЕМНЫЙ CALLBACK: {$problematicCallback}\n";
echo "isActivityExecution() проверки:\n";

// Электрооборудование pattern
if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $problematicCallback)) {
    echo "✓ Матчит electrical pattern\n";
} else {
    echo "✗ НЕ матчит electrical pattern (ожидаемо)\n";
}

// Основной commissioning pattern
if (preg_match('/^cat_(.+)_yes$/u', $problematicCallback, $matches)) {
    echo "✓ Матчит general pattern: ^cat_(.+)_yes\$\n";
    echo "✓ Извлеченный ключ: '{$matches[1]}'\n";
    
    // Проверяем массив
    $basicCommissioningActivities = [
        'ТабИнстр' => 'Инструктаж',
        'ТабДорога' => 'Дорога',
        'ТабПодгПоезд' => 'ПодготовкаКПоездке',
        'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
    ];
    
    if (isset($basicCommissioningActivities[$matches[1]])) {
        echo "✓ Ключ найден в basicCommissioningActivities\n";
        echo "✓ Activity type: {$basicCommissioningActivities[$matches[1]]}\n";
        echo "✓ Результат: isActivityExecution() = TRUE\n";
        echo "✓ ДОЛЖЕН роутиться в: handleActivityExecution()\n";
    } else {
        echo "✗ Ключ НЕ найден в basicCommissioningActivities\n";
        echo "✗ Результат: isActivityExecution() = FALSE\n";
    }
} else {
    echo "✗ НЕ матчит general pattern\n";
}

echo "\n=== АНАЛИЗ ПРОБЛЕМЫ ===\n\n";

if (preg_match('/^cat_(.+)_yes$/u', $problematicCallback, $matches)) {
    $basicCommissioningActivities = [
        'ТабИнстр' => 'Инструктаж',
        'ТабДорога' => 'Дорога',
        'ТабПодгПоезд' => 'ПодготовкаКПоездке',
        'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
    ];
    
    if (isset($basicCommissioningActivities[$matches[1]])) {
        echo "🤔 СТРАННО: Логика показывает что isActivityExecution() должен вернуть TRUE\n";
        echo "🤔 Но callback все равно падает в handleOtherWorkTypes()\n\n";
        echo "ВОЗМОЖНЫЕ ПРИЧИНЫ:\n";
        echo "1. ❌ Сервер использует старую версию файла (кэш)\n";
        echo "2. ❌ В реальном коде есть другой код, который мешает\n";
        echo "3. ❌ Проблема в порядке вызовов методов\n";
        echo "4. ❌ Есть проблема в методе in_array(\$activityType, \$this->activityTypes)\n\n";
    }
}

echo "=== СЛЕДУЮЩИЙ ШАГ ===\n\n";
echo "Нужно проверить содержимое \$this->activityTypes массива!\n";
echo "Возможно 'Инструктаж' не включен в список разрешенных активностей!\n";
?>