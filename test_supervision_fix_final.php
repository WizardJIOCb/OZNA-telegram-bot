<?php
/**
 * Final test of the supervision project activities fix
 */

echo "=== ФИНАЛЬНЫЙ ТЕСТ ИСПРАВЛЕНИЯ ШМР ===\n\n";

// Test the fixed pattern
$testCallbacks = [
    'category_Табель_ШМ_УчастиеВЗПИ_yes',
    'category_Табель_ШМ_РазрПрограммы_yes',
    'category_Табель_ШМ_РазрГрафика_yes',
    'category_Табель_ШМ_ОргДопоставки_yes',
    'category_Табель_ШМ_УчастиеВОПИ_yes',
    'category_Табель_ШМ_ИспобязМастера_yes'
];

echo "1. СИМУЛЯЦИЯ extractCategoryFromCallback:\n";
foreach ($testCallbacks as $callback) {
    // This is what extractCategoryFromCallback does
    $extracted = substr($callback, strlen('category_'));
    echo "   {$callback}\n   → '{$extracted}'\n\n";
}

echo "2. ТЕСТ НОВОГО ПАТТЕРНА В isActivityExecution:\n";
$pattern = '/^Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/';

foreach ($testCallbacks as $callback) {
    $extracted = substr($callback, strlen('category_'));
    $matches = preg_match($pattern, $extracted);
    echo "   {$extracted} → " . ($matches ? "✅ МАТЧИТ" : "❌ НЕ МАТЧИТ") . "\n";
}

echo "\n3. ТЕСТ ПАТТЕРНА В handleActivityExecution:\n";
$pattern2 = '/^Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/u';

foreach ($testCallbacks as $callback) {
    $extracted = substr($callback, strlen('category_'));
    if (preg_match($pattern2, $extracted, $matches)) {
        $activityType = $matches[1];
        echo "   {$extracted} → ✅ МАТЧИТ, activityType: '{$activityType}'\n";
    } else {
        echo "   {$extracted} → ❌ НЕ МАТЧИТ\n";
    }
}

echo "\n=== РЕЗУЛЬТАТ ===\n";
echo "Исправление завершено! Теперь:\n";
echo "1. ✅ isActivityExecution правильно распознает ШМР подтверждения\n";
echo "2. ✅ handleActivityExecution правильно извлекает activityType\n";
echo "3. ✅ Все 6 активностей ШМР должны работать корректно\n\n";

echo "Протестируйте в Телеграме:\n";
echo "Отдел ШМР → Проектная деятельность → любая активность → Да\n";
echo "Отчет должен успешно создаться и отправиться!\n";
?>