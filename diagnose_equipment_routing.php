<?php
/**
 * Diagnostic script to trace callback routing for ШМР equipment buttons
 */

echo "=== ДИАГНОСТИКА МАРШРУТИЗАЦИИ КНОПОК ШМР ===\n\n";

$testCallbacks = [
    'cat_ШМР_ЭлектрОб',
    'cat_ШМР_ПожСигн',
    'cat_ШМР_ОхрСигн',
    'cat_ШМР_Автом'
];

echo "📋 ПРОВЕРКА CALLBACK'ОВ:\n\n";

foreach ($testCallbacks as $callback) {
    echo "Callback: '{$callback}'\n";
    
    // Step 1: Mapping (extractCategoryFromCallback)
    $shortFormats = [
        'cat_ШМР_ЭлектрОб' => 'Табель_ШМР_Электрооборудование',
        'cat_ШМР_ПожСигн' => 'Табель_ШМР_ПожарнаяСигнализация',
        'cat_ШМР_ОхрСигн' => 'Табель_ШМР_ОхраннаяСигнализация',
        'cat_ШМР_Автом' => 'Табель_ШМР_Автоматизация',
    ];
    
    $mapped = $shortFormats[$callback] ?? 'NOT MAPPED';
    echo "  → После mapping: '{$mapped}'\n";
    
    // Step 2: Check isActivityConfirmation
    $isConfirmation = false; // Эти кнопки БЕЗ _yes, поэтому НЕ должны быть confirmation
    echo "  → isActivityConfirmation: " . ($isConfirmation ? 'TRUE' : 'FALSE') . "\n";
    
    // Step 3: Check isActivityExecution
    // Pattern: /^cat_ШМР_(.+)_yes$/u
    $isExecution = preg_match('/^cat_ШМР_(.+)_yes$/u', $callback);
    echo "  → isActivityExecution: " . ($isExecution ? 'TRUE (ОШИБКА!)' : 'FALSE (правильно)') . "\n";
    
    // Step 4: Where should it go?
    echo "  → Должно попасть в: handleOtherWorkTypes\n";
    echo "  → Там должен быть handler для: '{$mapped}'\n";
    echo "\n";
}

echo "🔍 ВЫВОД:\n";
echo "Callbacks БЕЗ '_yes' должны попадать в handleOtherWorkTypes,\n";
echo "где есть handlers на строках 3383-3404 (после наших изменений)\n";
echo "\n";
echo "✅ Handlers существуют и должны показывать МЕНЮ\n";
echo "❓ Почему показывается отчет?\n";
echo "\n";
echo "ВОЗМОЖНАЯ ПРИЧИНА:\n";
echo "Проверьте, что нет РАННЕЙ обработки в route() методе,\n";
echo "которая перехватывает эти callbacks ДО handleOtherWorkTypes!\n";
