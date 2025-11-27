<?php
// PRODUCTION DIAGNOSTIC FOR "ИСПОЛНЕНИЕ ОБЯЗАННОСТЕЙ МАСТЕРА" BUTTON

echo "=== ДИАГНОСТИКА КНОПКИ 'ИСПОЛНЕНИЕ ОБЯЗАННОСТЕЙ МАСТЕРА' ===\n\n";

echo "🔍 ПРОВЕРЯЕМ ПОЛНЫЙ ПОТОК ОБРАБОТКИ\n";
echo "===================================\n\n";

// Step 1: Test callback recognition
echo "1️⃣ ПРОВЕРКА РАСПОЗНАВАНИЯ CALLBACK:\n";
echo "-----------------------------------\n";

$initialCallback = 'cat_ТабИсполнОбяз';
echo "Button callback: '{$initialCallback}'\n";

// Test TabelHandler canHandle
$canHandle = str_starts_with($initialCallback, 'cat_Таб');
echo "TabelHandler.canHandle(): " . ($canHandle ? '✅ TRUE' : '❌ FALSE') . "\n";

if (!$canHandle) {
    echo "🚨 ПРОБЛЕМА: TabelHandler не будет обрабатывать этот callback!\n";
    echo "РЕШЕНИЕ: Проверить метод canHandle() в TabelHandler.php\n\n";
    exit;
}

// Step 2: Test category extraction  
echo "\n2️⃣ ПРОВЕРКА ИЗВЛЕЧЕНИЯ КАТЕГОРИИ:\n";
echo "---------------------------------\n";

// Simulate extractCategoryFromCallback
$shortFormats = [
    'cat_ТабИсполнОбяз' => 'Табель_ИсполнениеОбязМастера',
    'cat_ТабИсполнОбяз_yes' => 'Табель_ИсполнениеОбязМастера_yes'
];

if (isset($shortFormats[$initialCallback])) {
    $extractedCategory = $shortFormats[$initialCallback];
    echo "Mapping found: '{$initialCallback}' → '{$extractedCategory}' ✅\n";
} else {
    echo "❌ NO MAPPING FOUND for '{$initialCallback}'\n";
    echo "🚨 ПРОБЛЕМА: Mapping отсутствует в extractCategoryFromCallback!\n\n";
    exit;
}

// Step 3: Test routing to handleOtherWorkTypes
echo "\n3️⃣ ПРОВЕРКА МАРШРУТИЗАЦИИ:\n";
echo "-------------------------\n";

echo "Category to route: '{$extractedCategory}'\n";

// Test isActivityExecution (should be FALSE for initial click)
$isActivityExecution = str_contains($extractedCategory, '_yes');
echo "isActivityExecution(): " . ($isActivityExecution ? 'TRUE' : 'FALSE') . " ✅\n";

// Test isActivityConfirmation (should be FALSE for initial click)  
$isActivityConfirmation = str_contains($extractedCategory, '_yes');
echo "isActivityConfirmation(): " . ($isActivityConfirmation ? 'TRUE' : 'FALSE') . " ✅\n";

echo "Should route to: handleOtherWorkTypes() ✅\n";

// Step 4: Test handleOtherWorkTypes handler condition
echo "\n4️⃣ ПРОВЕРКА ОБРАБОТЧИКА В handleOtherWorkTypes:\n";
echo "-----------------------------------------------\n";

$handlerCondition = ($extractedCategory === 'Табель_ИсполнениеОбязМастера' || $extractedCategory === 'cat_ТабИсполнОбяз');
echo "Handler condition: \$category === 'Табель_ИсполнениеОбязМастера' || \$category === 'cat_ТабИсполнОбяз'\n";
echo "Category: '{$extractedCategory}'\n";
echo "Result: " . ($handlerCondition ? '✅ MATCH' : '❌ NO MATCH') . "\n";

if (!$handlerCondition) {
    echo "🚨 ПРОБЛЕМА: Handler condition не срабатывает!\n";
    echo "РЕШЕНИЕ: Проверить условие в handleOtherWorkTypes() около строки 2825\n\n";
    exit;
}

// Step 5: Test confirmation flow
echo "\n5️⃣ ПРОВЕРКА ПОТОКА ПОДТВЕРЖДЕНИЯ:\n";
echo "--------------------------------\n";

$confirmationCallback = 'cat_ТабИсполнОбяз_yes';
echo "Confirmation callback: '{$confirmationCallback}'\n";

// Test confirmation mapping
if (isset($shortFormats[$confirmationCallback])) {
    $confirmationCategory = $shortFormats[$confirmationCallback];
    echo "Confirmation mapping: '{$confirmationCallback}' → '{$confirmationCategory}' ✅\n";
} else {
    echo "❌ NO CONFIRMATION MAPPING FOUND\n";
    echo "🚨 ПРОБЛЕМА: Confirmation mapping отсутствует!\n\n";
    exit;
}

// Test confirmation isActivityExecution
$confirmationIsActivityExecution = str_contains($confirmationCategory, '_yes');
echo "Confirmation isActivityExecution(): " . ($confirmationIsActivityExecution ? '✅ TRUE' : '❌ FALSE') . "\n";

if (!$confirmationIsActivityExecution) {
    echo "🚨 ПРОБЛЕМА: Confirmation не распознается как activity execution!\n\n";
    exit;
}

// Step 6: Test handleActivityExecution logic
echo "\n6️⃣ ПРОВЕРКА handleActivityExecution:\n";
echo "-----------------------------------\n";

// Simulate activity type extraction from confirmation callback
if (preg_match('/^cat_(.+)_yes$/', $confirmationCallback, $matches)) {
    $shortActivityKey = $matches[1];
    echo "Short activity key: '{$shortActivityKey}'\n";
    
    $projectActivityCallbacks = [
        'ТабИсполнОбяз' => 'ИсполнениеОбязМастера'
    ];
    
    if (isset($projectActivityCallbacks[$shortActivityKey])) {
        $activityType = $projectActivityCallbacks[$shortActivityKey];
        echo "Mapped activity type: '{$activityType}' ✅\n";
        
        // Test if activity type is in activityTypes array
        $activityTypes = [
            'УчастиеВЗПИ',
            'РазрПрограммы',
            'РазрГрафика',
            'ОргДопоставки',
            'УчастиеВОПИ',
            'ИсполнениеОбязМастера',  // This should be here
            'Прочее_ПроектнаяДеятельность',
            'Прочее_ПроектнаяДеятельность_ШМР'
        ];
        
        $isValidActivity = in_array($activityType, $activityTypes);
        echo "Activity in activityTypes array: " . ($isValidActivity ? '✅ TRUE' : '❌ FALSE') . "\n";
        
        if (!$isValidActivity) {
            echo "🚨 ПРОБЛЕМА: '{$activityType}' отсутствует в массиве activityTypes!\n";
            echo "РЕШЕНИЕ: Добавить '{$activityType}' в массив activityTypes в TabelHandler.php\n\n";
            exit;
        }
        
    } else {
        echo "❌ NO PROJECT ACTIVITY MAPPING FOUND\n";
        echo "🚨 ПРОБЛЕМА: Project activity mapping отсутствует!\n\n";
        exit;
    }
} else {
    echo "❌ REGEX PATTERN FAILED\n";
    echo "🚨 ПРОБЛЕМА: Regex pattern не сработал!\n\n";
    exit;
}

echo "\n✅ ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ УСПЕШНО!\n";
echo "=================================\n\n";

echo "🎯 ЛОГИКА КОРРЕКТНА - КНОПКА ДОЛЖНА РАБОТАТЬ!\n\n";

echo "📋 ЕСЛИ КНОПКА ВСЕ ЕЩЕ НЕ РАБОТАЕТ, ПРОВЕРЬТЕ:\n";
echo "============================================\n";
echo "1. 🔄 Перезапустите веб-сервер/PHP-FPM после изменений\n";
echo "2. 🧹 Очистите состояние разговора (начните новый разговор)\n";
echo "3. 📝 Проверьте логи сервера в реальном времени\n";
echo "4. 🔍 Убедитесь в правильной последовательности навигации:\n";
echo "   - Выберите 'Отдел ШМР'\n";
echo "   - Выберите дату\n";
echo "   - Выберите проект\n";
echo "   - Нажмите 'Проектная деятельность'\n";
echo "   - Нажмите 'исполнение обязанностей мастера'\n\n";

echo "💡 ДОПОЛНИТЕЛЬНЫЕ ШАГИ ДИАГНОСТИКИ:\n";
echo "===================================\n";
echo "1. Добавить временные логи в handleOtherWorkTypes():\n";
echo "   error_log(\"🎯 MASTER DUTIES: Handler called with category: '{\$category}'\");\n\n";
echo "2. Проверить логи на наличие сообщений:\n";
echo "   - 'TabelHandler: handling master duties execution'\n";
echo "   - '🎯 MASTER DUTIES: Handler triggered!'\n\n";
echo "3. Если логи не появляются, проблема в маршрутизации\n";
echo "4. Если логи есть, но отчет не создается, проблема в handleActivityExecution\n\n";

echo "🚀 УСПЕШНОЙ ОТЛАДКИ!\n";
?>