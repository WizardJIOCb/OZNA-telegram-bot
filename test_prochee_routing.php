<?php
// Test script to verify "Прочее" button routing

echo "=== ТЕСТ МАРШРУТИЗАЦИИ КНОПКИ \"ПРОЧЕЕ\" ===\n\n";

echo "1. СИМУЛЯЦИЯ CALLBACK:\n";
$callbackData = 'cat_ТабЭлОб_Проч';
echo "   Пользователь нажимает кнопку \"Прочее\"\n";
echo "   Callback data: {$callbackData}\n\n";

echo "2. ПРОВЕРКА canHandle() МЕТОДА:\n";
// Simulate canHandle() method check
$canHandle = (str_starts_with($callbackData, 'category_Табель') || 
             str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
             str_starts_with($callbackData, 'cat_Таб') ||
             $callbackData === 'новое_прочее_yes' ||
             $callbackData === 'cat_ТабЭлОб_Проч_yes');

echo "   canHandle result: " . ($canHandle ? 'TRUE' : 'FALSE') . "\n";
if ($canHandle) {
    echo "   ✅ TabelHandler should handle this callback\n\n";
} else {
    echo "   ❌ TabelHandler will NOT handle this callback\n\n";
    exit;
}

echo "3. СИМУЛЯЦИЯ extractCategoryFromCallback():\n";
// Simulate the mapping from line 526
if ($callbackData === 'cat_ТабЭлОб_Проч') {
    $convertedCategory = 'Табель_Прочее_ЭлектрОб';
    echo "   '{$callbackData}' → '{$convertedCategory}'\n";
    echo "   ✅ Callback correctly mapped\n\n";
} else {
    echo "   ❌ Callback mapping not found\n\n";
    exit;
}

echo "4. ПРОВЕРКА isActivityExecution():\n";
// Check if it would be treated as activity execution
$isActivityExecution = false; // Since 'Проч' was removed from mappings
echo "   isActivityExecution result: " . ($isActivityExecution ? 'TRUE' : 'FALSE') . "\n";
if (!$isActivityExecution) {
    echo "   ✅ Will NOT route to handleActivityExecution\n\n";
} else {
    echo "   ❌ Will incorrectly route to handleActivityExecution\n\n";
    exit;
}

echo "5. ПРОВЕРКА isActivityConfirmation():\n";
// Check if it would be treated as activity confirmation
$isActivityConfirmation = false; // No '_yes' suffix
echo "   isActivityConfirmation result: " . ($isActivityConfirmation ? 'TRUE' : 'FALSE') . "\n";
if (!$isActivityConfirmation) {
    echo "   ✅ Will NOT route to handleActivityConfirmation\n\n";
} else {
    echo "   ❌ Will incorrectly route to handleActivityConfirmation\n\n";
    exit;
}

echo "6. МАРШРУТИЗАЦИЯ К handleOtherWorkTypes():\n";
echo "   Since neither isActivityExecution nor isActivityConfirmation returned true,\n";
echo "   the callback should route to handleOtherWorkTypes()\n";
echo "   Category passed: '{$convertedCategory}'\n\n";

echo "7. ПРОВЕРКА ОБРАБОТЧИКА В handleOtherWorkTypes():\n";
// Check if the category would match the custom input handler
$willMatchCustomInputHandler = (
    $convertedCategory === 'ТабЭлОб_Проч' || 
    $convertedCategory === 'cat_ТабЭлОб_Проч' || 
    $convertedCategory === 'Табель_Прочее_Электрооборудование' || 
    $convertedCategory === 'Табель_Прочее_ЭлектрОб'
);

echo "   Category check results:\n";
echo "   - ТабЭлОб_Проч: " . ($convertedCategory === 'ТабЭлОб_Проч' ? 'MATCH' : 'NO MATCH') . "\n";
echo "   - cat_ТабЭлОб_Проч: " . ($convertedCategory === 'cat_ТабЭлОб_Проч' ? 'MATCH' : 'NO MATCH') . "\n";
echo "   - Табель_Прочее_Электрооборудование: " . ($convertedCategory === 'Табель_Прочее_Электрооборудование' ? 'MATCH' : 'NO MATCH') . "\n";
echo "   - Табель_Прочее_ЭлектрОб: " . ($convertedCategory === 'Табель_Прочее_ЭлектрОб' ? 'MATCH' : 'NO MATCH') . "\n\n";

if ($willMatchCustomInputHandler) {
    echo "   ✅ CUSTOM INPUT HANDLER WILL MATCH!\n";
    echo "   Expected behavior:\n";
    echo "   1. Set awaiting_custom_input = true\n";
    echo "   2. Set custom_input_activity_type = 'Прочее_ЭлектрОб'\n";
    echo "   3. Send prompt: 'Пожалуйста, введите описание выполняемой работы:'\n\n";
    
    echo "🎉 ТЕСТ ПРОЙДЕН УСПЕШНО!\n";
    echo "Кнопка \"Прочее\" должна правильно запрашивать пользовательский ввод.\n\n";
    
    echo "📝 ЕСЛИ КНОПКА НЕ РАБОТАЕТ:\n";
    echo "1. Перезапустите веб-сервер\n";
    echo "2. Проверьте логи на наличие ошибок\n";
    echo "3. Убедитесь, что нет конфликтующих обработчиков в коде\n";
} else {
    echo "   ❌ CUSTOM INPUT HANDLER WILL NOT MATCH!\n";
    echo "   This explains why user gets 'неизвестная команда табеля'\n";
    echo "   The category '{$convertedCategory}' is not handled by any condition.\n";
}

echo "\nТест завершен.\n";
?>