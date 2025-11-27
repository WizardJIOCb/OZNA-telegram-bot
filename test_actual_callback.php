<?php
/**
 * Test what the actual callback data looks like for supervision confirmations
 */

echo "=== ТЕСТ РЕАЛЬНОГО ФОРМАТА CALLBACK'А ===\n\n";

// Test the confirmation keyboard generation
echo "1. ПРОВЕРКА ГЕНЕРАЦИИ КЛАВИАТУРЫ ПОДТВЕРЖДЕНИЯ:\n";

// From MessageService, the supervision project activities use this format:
$supervisionButtons = [
    'category_Табель_ШМ_УчастиеВЗПИ',
    'category_Табель_ШМ_РазрПрограммы',
    'category_Табель_ШМ_РазрГрафика',
    'category_Табель_ШМ_ОргДопоставки',
    'category_Табель_ШМ_УчастиеВОПИ',
    'category_Табель_ШМ_ИспобязМастера'
];

echo "Кнопки активностей ШМР:\n";
foreach ($supervisionButtons as $button) {
    echo "   - {$button}\n";
}

echo "\n2. СИМУЛЯЦИЯ СОЗДАНИЯ КНОПКИ ПОДТВЕРЖДЕНИЯ:\n";

// When a user clicks an activity, TabelHandler creates confirmation keyboard
// From the handlers, they use this format:
// $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_ШМ_УчастиеВЗПИ_yes");

$confirmationCallbacks = [];
foreach ($supervisionButtons as $button) {
    $confirmationCallback = $button . '_yes';
    $confirmationCallbacks[] = $confirmationCallback;
    echo "   {$button} → {$confirmationCallback}\n";
}

echo "\n3. СИМУЛЯЦИЯ extractCategoryFromCallback:\n";

function simulateExtractCategory($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    return $callbackData;
}

foreach ($confirmationCallbacks as $callback) {
    $extracted = simulateExtractCategory($callback);
    echo "   {$callback} → '{$extracted}'\n";
}

echo "\n4. ПРОВЕРКА ПАТТЕРНА В isActivityExecution:\n";

// Our fix in isActivityExecution uses this pattern:
$pattern = '/^category_Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/u';

foreach ($confirmationCallbacks as $callback) {
    $matches = preg_match($pattern, $callback);
    echo "   {$callback} → " . ($matches ? "✅ МАТЧИТ" : "❌ НЕ МАТЧИТ") . "\n";
}

echo "\n5. ПРОВЕРКА ПАТТЕРНА В handleActivityExecution:\n";

// Our fix in handleActivityExecution uses this pattern:
$pattern2 = '/^Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/u';

foreach ($confirmationCallbacks as $callback) {
    $extracted = simulateExtractCategory($callback);
    $matches = preg_match($pattern2, $extracted);
    echo "   {$extracted} → " . ($matches ? "✅ МАТЧИТ" : "❌ НЕ МАТЧИТ") . "\n";
}

echo "\n=== ДИАГНОСТИКА ===\n";
echo "Если паттерн в isActivityExecution НЕ матчит, то callback не распознается как выполнение активности.\n";
echo "Если паттерн в handleActivityExecution НЕ матчит, то activityType не извлекается.\n";
echo "\nПроверьте результаты выше, чтобы понять, где проблема.\n";
?>