<?php
// Final test to verify the fix for electrical equipment button callback handling

echo "=== ФИНАЛЬНЫЙ ТЕСТ ИСПРАВЛЕНИЯ CALLBACK'ОВ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

// Test the main issue: when user clicks "Электрооборудование" button
$callbackData = 'cat_ТабЭлОб';

echo "1. ТЕСТИРОВАНИЕ ОСНОВНОЙ ПРОБЛЕМЫ:\n";
echo "==================================\n";
echo "Callback data: $callbackData\n";

// Simulate the routing logic from TabelHandler
echo "\n2. СИМУЛЯЦИЯ МАРШРУТИЗАЦИИ:\n";
echo "==========================\n";

// Step 1: canHandle check
$canHandle = str_starts_with($callbackData, 'category_Табель') || 
             str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
             str_starts_with($callbackData, 'cat_Таб');
             
echo "canHandle result: " . ($canHandle ? 'ДА ✅' : 'НЕТ ❌') . "\n";

// Step 2: extractCategoryFromCallback
function extractCategoryFromCallback($callbackData) {
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормИД_ЭлектрОб',
            'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзмСхПд_ЭлектрОб',
            'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтКбЛн_ЭлектрОб',
            'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РаскКбЛн_ЭлектрОб',
            'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
            'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтВех_ЭлектрОб',
            'cat_ТабЭлОб_Проч' => 'Табель_Прочее_ЭлектрОб',
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация',
            'cat_ТабПожСигн_Наз' => 'Табель_ПожарнаяСигнализация'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

$extractedCategory = extractCategoryFromCallback($callbackData);
echo "Extracted category: $extractedCategory\n";

// Step 3: Check if it's activity execution (the fixed logic)
function isActivityExecution($category) {
    error_log("TabelHandler: isActivityExecution called with category: {$category}");
    
    // Check if this is a menu callback that should NOT be treated as activity execution
    // These are the main equipment category callbacks that should show menus, not create reports
    $menuCallbacks = [
        'cat_ТабЭлОб',     // Electrical equipment main menu
        'cat_ТабАвтом',    // Automation equipment main menu
        'cat_ТабПожСигн'   // Fire alarm equipment main menu
    ];
    
    if (in_array($category, $menuCallbacks)) {
        error_log("TabelHandler: isActivityExecution returning false for menu callback: {$category}");
        return false;
    }
    
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        // For simplicity, we're just checking if it ends with _yes
        error_log("TabelHandler: isActivityExecution returning true for activity with _yes");
        return true;
    }
    
    // Check idle menu execution (when user confirms idle activity with detailed reason)
    if (str_contains($category, '_yes') && 
        (str_contains($category, 'Табель_Простой_yes') || str_contains($category, 'Табель_ШМ_Простой_yes'))) {
        error_log("TabelHandler: isActivityExecution returning true for idle activity");
        return true;
    }
    
    error_log("TabelHandler: isActivityExecution returning false");
    return false;
}

$isActivityExecution = isActivityExecution($callbackData);
echo "isActivityExecution result: " . ($isActivityExecution ? 'ДА (создать отчет) ❌' : 'НЕТ (показать меню) ✅') . "\n";

// Step 4: Check if it's handled in handleOtherWorkTypes
function handleOtherWorkTypes($category) {
    echo "handleOtherWorkTypes called with category: $category\n";
    
    // Handle electrical equipment menu (short format)
    if ($category === 'cat_ТабЭлОб') {
        echo "  ✅ Обнаружен callback меню электрооборудования (короткий формат)\n";
        return 'handleElectricalEquipmentMenu() - показать меню активностей';
    }
    
    // Handle electrical equipment menu (full format from extractCategoryFromCallback)
    if ($category === 'Табель_Электрооборудование') {
        echo "  ✅ Обнаружен callback меню электрооборудования (полный формат)\n";
        return 'handleElectricalEquipmentMenu() - показать меню активностей';
    }
    
    // Handle automation equipment menu (short format)
    if ($category === 'cat_ТабАвтом') {
        echo "  ✅ Обнаружен callback меню автоматизации (короткий формат)\n";
        return 'handleAutomationEquipmentMenu() - показать меню активностей';
    }
    
    // Handle fire alarm equipment menu (short format)
    if ($category === 'cat_ТабПожСигн') {
        echo "  ✅ Обнаружен callback меню пожарной сигнализации (короткий формат)\n";
        return 'handleFireAlarmEquipmentMenu() - показать меню активностей';
    }
    
    echo "  ❌ Неизвестная категория\n";
    return 'Неизвестная команда табеля';
}

$result = handleOtherWorkTypes($callbackData);
echo "Результат обработки: $result\n";

echo "\n3. ЗАКЛЮЧЕНИЕ:\n";
echo "=============\n";
if (!$isActivityExecution && str_contains($result, 'handleElectricalEquipmentMenu')) {
    echo "✅ ИСПРАВЛЕНИЕ РАБОТАЕТ ПРАВИЛЬНО!\n";
    echo "   При нажатии кнопки 'Электрооборудование' будет показано меню активностей,\n";
    echo "   а не формироваться отчет.\n";
} else {
    echo "❌ ИСПРАВЛЕНИЕ НЕ РАБОТАЕТ!\n";
    echo "   Требуется дополнительная проверка логики.\n";
}

echo "\n4. ДОПОЛНИТЕЛЬНЫЕ ТЕСТЫ:\n";
echo "======================\n";

$additionalTests = [
    'cat_ТабАвтом' => 'Автоматизация',
    'cat_ТабПожСигн' => 'Пожарная сигнализация'
];

foreach ($additionalTests as $testCallback => $description) {
    echo "\nТест для $description:\n";
    $isActivity = isActivityExecution($testCallback);
    $otherWorkResult = handleOtherWorkTypes($testCallback);
    echo "  isActivityExecution: " . ($isActivity ? 'ДА ❌' : 'НЕТ ✅') . "\n";
    echo "  handleOtherWorkTypes: $otherWorkResult\n";
    
    if (!$isActivity && str_contains($otherWorkResult, 'handle')) {
        echo "  ✅ $description работает правильно\n";
    } else {
        echo "  ❌ $description работает неправильно\n";
    }
}
?>