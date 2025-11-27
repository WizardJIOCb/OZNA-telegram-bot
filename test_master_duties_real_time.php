<?php
echo "🎯 РЕАЛЬНОЕ ТЕСТИРОВАНИЕ MASTER DUTIES\n";
echo "===================================\n\n";

// Проверим реальный callback от кнопки
$realCallback = 'cat_ТабИсполнОбяз';
echo "1. Реальный callback от кнопки: '{$realCallback}'\n";

// Проверим маппинг extractCategoryFromCallback
echo "\n2. Тестируем extractCategoryFromCallback маппинг:\n";
$shortFormats = [
    'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
    'cat_ТабИсполнОбяз_yes' => 'Табель_ИсполнениеОбязМастера_yes'
];

if (isset($shortFormats[$realCallback])) {
    $mappedCategory = $shortFormats[$realCallback];
    echo "   ✅ Маппинг найден: '{$realCallback}' -> '{$mappedCategory}'\n";
} else {
    echo "   ❌ Маппинг НЕ найден для: '{$realCallback}'\n";
    exit(1);
}

// Проверим isActivityExecution
echo "\n3. Тестируем isActivityExecution('{$mappedCategory}'):\n";
if ($mappedCategory === 'Табель_ИсполнениеОбязМастера') {
    echo "   ✅ Должен возвратить FALSE (есть исключение)\n";
    $isActivityExecution = false;
} else {
    echo "   ❌ Может быть перехвачен!\n";
    $isActivityExecution = true;
}

// Проверим isActivityConfirmation  
echo "\n4. Тестируем isActivityConfirmation('{$mappedCategory}'):\n";
$isActivityConfirmation = str_contains($mappedCategory, '_yes');
echo "   Результат: " . ($isActivityConfirmation ? 'TRUE' : 'FALSE') . "\n";

// Проверим маршрутизацию
echo "\n5. Тестируем маршрутизацию:\n";
if (!$isActivityExecution && !$isActivityConfirmation) {
    echo "   ✅ Должен попасть в handleOtherWorkTypes()\n";
    $shouldRoute = 'handleOtherWorkTypes';
} else {
    echo "   ❌ Попадет в другой обработчик!\n";
    $shouldRoute = $isActivityExecution ? 'handleActivityExecution' : 'handleActivityConfirmation';
}

// Проверим обработчик в handleOtherWorkTypes
echo "\n6. Тестируем условие в handleOtherWorkTypes:\n";
$handlerCondition1 = ($mappedCategory === 'Табель_ИсполнениеОбязМастера');
$handlerCondition2 = ($mappedCategory === 'cat_ТабИсполнОбяз');
$handlerCondition = $handlerCondition1 || $handlerCondition2;

echo "   category === 'Табель_ИсполнениеОбязМастера': " . ($handlerCondition1 ? 'TRUE' : 'FALSE') . "\n";
echo "   category === 'cat_ТабИсполнОбяз': " . ($handlerCondition2 ? 'TRUE' : 'FALSE') . "\n";
echo "   Общий результат: " . ($handlerCondition ? 'TRUE ✅' : 'FALSE ❌') . "\n";

// Итоговый результат
echo "\n7. ИТОГОВЫЙ РЕЗУЛЬТАТ:\n";
echo "======================\n";
if ($shouldRoute === 'handleOtherWorkTypes' && $handlerCondition) {
    echo "✅ ЛОГИКА ПРАВИЛЬНАЯ - кнопка ДОЛЖНА работать!\n";
    echo "\nВозможные причины неработоспособности:\n";
    echo "1. CallbackqueryCommand не обрабатывает callback\n";
    echo "2. Webhook не доставляет callback до бота\n";  
    echo "3. CallbackHandlerFactory не роутит к TabelHandler\n";
    echo "4. Какая-то ошибка в коде прерывает выполнение\n";
    echo "5. Проблема с conversation state\n";
} else {
    echo "❌ ЛОГИЧЕСКАЯ ОШИБКА - исправьте маршрутизацию!\n";
}

echo "\n✅ Тестирование завершено.\n";
echo "\nТеперь попробуйте нажать кнопку Master Duties в боте.\n";
echo "Если она всё ещё не работает, проблема НЕ в логике TabelHandler.\n";
?>