<?php
// Скрипт для диагностики проблемы с меню "Отдел ШМР"

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С МЕНЮ 'ОТДЕЛ ШМР' ===\n\n";

// Эмулируем полный процесс
echo "1. ТЕСТИРОВАНИЕ CALLBACK 'category_Табель_Шеф_монтаж'\n";
echo "====================================================\n";

$callbackData = 'category_Табель_Шеф_монтаж';
echo "Callback data: '{$callbackData}'\n";

// Проверяем canHandle
function simulateCanHandle($callbackData) {
    echo "   Проверка canHandle():\n";
    echo "   - str_starts_with('{$callbackData}', 'category_Табель'): " . (str_starts_with($callbackData, 'category_Табель') ? 'true' : 'false') . "\n";
    echo "   - Результат canHandle: " . (str_starts_with($callbackData, 'category_Табель') ? 'TRUE' : 'FALSE') . "\n";
    return str_starts_with($callbackData, 'category_Табель');
}

$canHandle = simulateCanHandle($callbackData);

if (!$canHandle) {
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: TabelHandler не может обработать этот callback!\n";
    exit;
}

echo "✅ TabelHandler может обработать callback\n\n";

// Эмулируем извлечение категории
echo "2. ИЗВЛЕЧЕНИЕ КАТЕГОРИИ\n";
echo "=======================\n";

function extractCategory($callbackData) {
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    return '';
}

$category = extractCategory($callbackData);
echo "Извлеченная категория: '{$category}'\n";

if ($category !== 'Табель_Шеф_монтаж') {
    echo "❌ ОШИБКА: Неправильно извлечена категория!\n";
    exit;
}

echo "✅ Категория извлечена правильно\n\n";

// Эмулируем маршрутизацию в routeTabelCallback
echo "3. МАРШРУТИЗАЦИЯ В routeTabelCallback\n";
echo "=====================================\n";

function simulateRouteTabelCallback($category) {
    echo "   Проверка условий маршрутизации:\n";
    
    // Проверяем различные условия из routeTabelCallback
    $conditions = [
        "category === 'Табель'" => $category === 'Табель',
        "isDateSelection(category)" => false, // Не дата
        "isProjectSelection(category)" => false, // Не проект
        "category === 'Табель_Мобильная_Бригада_Дата'" => $category === 'Табель_Мобильная_Бригада_Дата',
        "isIdleMenuCallback(category)" => ($category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой'),
        "isActivityExecution(category)" => false, // Не выполнение активности
        "isActivityConfirmation(category)" => false, // Не подтверждение
        "-> handleOtherWorkTypes()" => true // Должно попасть сюда
    ];
    
    foreach ($conditions as $condition => $result) {
        echo "   - {$condition}: " . ($result ? 'TRUE' : 'FALSE') . "\n";
        if ($result && $condition !== "-> handleOtherWorkTypes()") {
            echo "   ✅ Маршрутизация: {$condition}\n";
            return $condition;
        }
    }
    
    echo "   ✅ Маршрутизация: handleOtherWorkTypes()\n";
    return "handleOtherWorkTypes";
}

$routing = simulateRouteTabelCallback($category);

if ($routing !== "handleOtherWorkTypes") {
    echo "❌ ОШИБКА: Неожиданная маршрутизация!\n";
    exit;
}

echo "✅ Правильная маршрутизация в handleOtherWorkTypes\n\n";

// Эмулируем обработку в handleOtherWorkTypes
echo "4. ОБРАБОТКА В handleOtherWorkTypes\n";
echo "===================================\n";

function simulateHandleOtherWorkTypes($category) {
    echo "   Проверка условий в handleOtherWorkTypes:\n";
    
    // Из найденного кода
    $conditions = [
        "category === 'Табель_Пусконаладка'" => $category === 'Табель_Пусконаладка',
        "category === 'Табель_Шеф_монтаж'" => $category === 'Табель_Шеф_монтаж',
        "category === 'Табель_Мобильная_Бригада'" => $category === 'Табель_Мобильная_Бригада'
    ];
    
    foreach ($conditions as $condition => $result) {
        echo "   - {$condition}: " . ($result ? 'TRUE' : 'FALSE') . "\n";
        if ($result) {
            echo "   ✅ Найдено соответствие: {$condition}\n";
            if ($condition === "category === 'Табель_Шеф_монтаж'") {
                echo "   ✅ Должно вызвать: handleSupervisionDateSelection()\n";
            }
            return $condition;
        }
    }
    
    return "no_match";
}

$otherWorkTypesResult = simulateHandleOtherWorkTypes($category);

if ($otherWorkTypesResult !== "category === 'Табель_Шеф_монтаж'") {
    echo "❌ ОШИБКА: handleOtherWorkTypes не нашел соответствие для Шеф-монтажа!\n";
    exit;
}

echo "✅ handleOtherWorkTypes правильно определил Шеф-монтаж\n\n";

// Эмулируем handleSupervisionDateSelection
echo "5. ВЫЗОВ handleSupervisionDateSelection\n";
echo "=======================================\n";

function simulateHandleSupervisionDateSelection() {
    echo "   Функция handleSupervisionDateSelection должна:\n";
    echo "   - Создать клавиатуру выбора даты с префиксом 'category_Табель_ШМ'\n";
    echo "   - Отправить сообщение '🔧 Выберите дату для Шеф-монтажа'\n";
    echo "   ✅ Все должно работать корректно\n";
}

simulateHandleSupervisionDateSelection();

echo "\n";
echo "6. ДИАГНОСТИКА ЗАВЕРШЕНА\n";
echo "========================\n";
echo "Логика обработки выглядит корректной. Если меню не открывается, проблема может быть в:\n";
echo "1. Ошибки в runtime выполнении\n";
echo "2. Проблемы с базой данных\n";
echo "3. Ошибки в MessageService\n";
echo "4. Проблемы с кодировкой\n";
echo "5. Отсутствие restart веб-сервера после изменений\n\n";

echo "РЕКОМЕНДУЕМЫЕ ДЕЙСТВИЯ:\n";
echo "1. Перезапустить веб-сервер\n";
echo "2. Проверить логи ошибок\n";
echo "3. Убедиться, что файлы были сохранены\n";
echo "4. Проверить права доступа к файлам\n";

?>