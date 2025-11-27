<?php
echo "=== ТЕСТ ИСПРАВЛЕНИЯ МЕТОДА isActivityExecution ===\n\n";

// Имитируем метод isActivityExecution с исправлениями
function isActivityExecution(string $category): bool
{
    echo "isActivityExecution вызван с категорией: {$category}\n";
    
    // Special case: if this is the electrical equipment menu callback, it's NOT an activity execution
    if ($category === 'cat_ТабЭлОб') {
        echo "  ✅ Обнаружен callback меню электрооборудования\n";
        return false;
    }
    
    // Special case: if this is the automation equipment menu callback, it's NOT an activity execution
    if ($category === 'cat_ТабАвтом') {
        echo "  ✅ Обнаружен callback меню автоматизации\n";
        return false;
    }
    
    // Special case: if this is the fire alarm equipment menu callback, it's NOT an activity execution
    if ($category === 'cat_ТабПожСигн') {
        echo "  ✅ Обнаружен callback меню пожарной сигнализации\n";
        return false;
    }
    
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        echo "  ✅ Обнаружен callback с '_yes' - это активность для выполнения\n";
        return true;
    }
    
    echo "  ❌ Не является активностью для выполнения\n";
    return false;
}

// Тестируем различные callback'ы
$testCases = [
    'cat_ТабЭлОб' => false, // Меню электрооборудования - НЕ активность
    'cat_ТабЭлОб_РабПоГр' => false, // Активность без _yes - НЕ активность для выполнения
    'cat_ТабЭлОб_РабПоГр_yes' => true, // Подтверждение активности - активность для выполнения
    'cat_ТабАвтом' => false, // Меню автоматизации - НЕ активность
    'cat_ТабПожСигн' => false, // Меню пожарной сигнализации - НЕ активность
    'cat_ТабЭлОб_ФормИД_yes' => true, // Подтверждение активности - активность для выполнения
    'Табель_РаботыПоГрафику_Электрооборудование_yes' => true, // Полный формат - активность для выполнения
];

echo "1. ТЕСТИРОВАНИЕ:\n";
echo "================\n";

foreach ($testCases as $category => $expected) {
    $result = isActivityExecution($category);
    $status = ($result === $expected) ? '✅' : '❌';
    echo "{$status} {$category}: " . ($result ? 'активность' : 'не активность') . "\n";
}

echo "\n2. СИМУЛЯЦИЯ ПОЛНОГО ПОТОКА ОБРАБОТКИ:\n";
echo "=====================================\n";

// Симулируем обработку callback'а от кнопки "Электрооборудование"
$callbackData = 'cat_ТабЭлОб';
echo "Получен callback от кнопки 'Электрооборудование': {$callbackData}\n";

// Предполагаем, что extractCategoryFromCallback возвращает 'Табель_Электрооборудование'
$category = 'Табель_Электрооборудование';
echo "Извлеченная категория: {$category}\n";

// Проверяем isActivityExecution
$isActivityExecution = isActivityExecution($callbackData);
echo "isActivityExecution('{$callbackData}'): " . ($isActivityExecution ? 'ДА' : 'НЕТ') . "\n";

if (!$isActivityExecution) {
    echo "✅ ПРАВИЛЬНО: Не является активностью для выполнения\n";
    echo "   Должен быть обработан в handleOtherWorkTypes\n";
    
    // Проверяем handleOtherWorkTypes
    if ($category === 'Табель_Электрооборудование') {
        echo "✅ Будет вызван handleElectricalEquipmentMenu() - показ меню активностей\n";
    }
} else {
    echo "❌ НЕПРАВИЛЬНО: Определяется как активность для выполнения\n";
    echo "   Будет вызван handleActivityExecution() - формирование отчета\n";
}

echo "\n3. ЗАКЛЮЧЕНИЕ:\n";
echo "=============\n";
echo "После исправления метода isActivityExecution:\n";
echo "  - Callback 'cat_ТабЭлОб' не будет считаться активностью для выполнения\n";
echo "  - Будет правильно обработан в handleOtherWorkTypes\n";
echo "  - Вызовет handleElectricalEquipmentMenu() для показа меню активностей\n";
echo "  - Не будет формировать отчет при нажатии кнопки 'Электрооборудование'\n";
?>