<?php

echo "ДИАГНОСТИКА ПРОБЛЕМЫ С ПОДМЕНЮ ЭЛЕКТРООБОРУДОВАНИЯ\n";
echo "================================================\n\n";

echo "🔍 ПРОВЕРЯЕМ ТЕКУЩЕЕ СОСТОЯНИЕ СИСТЕМЫ:\n";
echo "---------------------------------------\n";

// Test 1: Check if TabelHandler can handle the electrical equipment callback
function testCanHandle($callbackData) {
    echo "Тест canHandle для: '$callbackData'\n";
    
    // Simulate the current TabelHandler canHandle logic
    $canHandle = str_starts_with($callbackData, 'category_Табель') || 
                 str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                 str_starts_with($callbackData, 'cat_Таб');
    
    echo "Результат canHandle: " . ($canHandle ? "✅ ДА" : "❌ НЕТ") . "\n";
    return $canHandle;
}

// Test 2: Check extractCategoryFromCallback mapping
function testExtractCategory($callbackData) {
    echo "Тест extractCategoryFromCallback для: '$callbackData'\n";
    
    // Simulate the mapping logic
    if (strpos($callbackData, 'category_') === 0) {
        $result = substr($callbackData, strlen('category_'));
        echo "Результат: '$result' (стандартный формат)\n";
        return $result;
    }
    
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Simulate the mapping from the extractCategoryFromCallback method
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            $result = $shortFormats[$callbackData];
            echo "Результат: '$result' (короткий формат)\n";
            return $result;
        } else {
            $result = substr($callbackData, strlen('cat_'));
            echo "Результат: '$result' (обрезка префикса)\n";
            return $result;
        }
    }
    
    echo "Результат: '' (пустая строка)\n";
    return '';
}

// Test 3: Check routing logic
function testRouting($category) {
    echo "Тест маршрутизации для: '$category'\n";
    
    // Check routing in handleOtherWorkTypes
    if ($category === 'ТабЭлОб') {
        echo "Маршрут: handleElectricalEquipmentMenu() ✅\n";
        return 'electrical_submenu';
    }
    
    if ($category === 'Табель_Электрооборудование') {
        echo "Маршрут: handleElectricalEquipmentMenu() (полный формат) ✅\n";
        return 'electrical_submenu';
    }
    
    // Check simple activity mapping
    $shortCallbackMappings = [
        'ТабОхрСигн' => 'ОхраннаяСигнализация',
        'ТабОтоплВент' => 'ОтоплениеИВентиляция',
        'ТабТехОб' => 'ТехнологическоеОборудование'
    ];
    
    if (isset($shortCallbackMappings[$category])) {
        echo "Маршрут: Подтверждение активности ({$shortCallbackMappings[$category]}) ✅\n";
        return 'confirmation';
    }
    
    echo "Маршрут: 'Неизвестная команда табеля' ❌\n";
    return 'unknown';
}

echo "\n🧪 ТЕСТИРУЕМ КНОПКУ ЭЛЕКТРООБОРУДОВАНИЯ:\n";
echo "=======================================\n\n";

$electricalCallback = 'cat_ТабЭлОб';

echo "Шаг 1: Проверка canHandle\n";
$canHandleResult = testCanHandle($electricalCallback);

echo "\nШаг 2: Проверка extractCategoryFromCallback\n";
$extractedCategory = testExtractCategory($electricalCallback);

echo "\nШаг 3: Проверка маршрутизации\n";
$routingResult = testRouting($extractedCategory);

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 РЕЗУЛЬТАТЫ ДИАГНОСТИКИ:\n";
echo "=========================\n\n";

if ($canHandleResult && $extractedCategory && $routingResult === 'electrical_submenu') {
    echo "🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ! Логика работает правильно.\n\n";
    echo "Возможные причины проблемы:\n";
    echo "1. 🔍 Проблема с приоритетом обработчиков\n";
    echo "2. 🔍 Другой обработчик перехватывает callback раньше\n";
    echo "3. 🔍 Проблема с кодировкой callback данных\n";
    echo "4. 🔍 Ошибка в MessageService при создании клавиатуры\n\n";
    
    echo "РЕКОМЕНДУЕМЫЕ ДЕЙСТВИЯ:\n";
    echo "1. Проверить логи бота при нажатии кнопки\n";
    echo "2. Убедиться, что TabelHandler имеет высший приоритет\n";
    echo "3. Проверить CallbackHandlerFactory на конфликты\n";
} else {
    echo "❌ НАЙДЕНЫ ПРОБЛЕМЫ:\n\n";
    if (!$canHandleResult) {
        echo "• canHandle не распознает callback\n";
    }
    if (!$extractedCategory) {
        echo "• extractCategoryFromCallback не работает\n";
    }
    if ($routingResult !== 'electrical_submenu') {
        echo "• Маршрутизация не приводит к подменю\n";
    }
}

echo "\n📋 СЛЕДУЮЩИЕ ШАГИ:\n";
echo "==================\n";
echo "1. Протестировать кнопку в боте и проверить логи\n";
echo "2. Убедиться, что callback данные приходят правильно\n";
echo "3. Проверить порядок обработчиков в системе\n\n";

?>