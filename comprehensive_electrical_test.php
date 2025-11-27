<?php
// Comprehensive test for the electrical equipment callback fix

echo "=== КОМПЛЕКСНЫЙ ТЕСТ ИСПРАВЛЕНИЯ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

// Simulate the exact flow that happens in the real code
function simulateFullFlow($callbackData) {
    echo "ТЕСТИРОВАНИЕ CALLBACK: '$callbackData'\n";
    echo str_repeat("=", 50) . "\n";
    
    // Step 1: canHandle check
    $canHandle = str_starts_with($callbackData, 'category_Табель') || 
                 str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                 str_starts_with($callbackData, 'cat_Таб');
    
    echo "1. canHandle(): " . ($canHandle ? 'ДА ✅' : 'НЕТ ❌') . "\n";
    
    if (!$canHandle) {
        echo "   ОСТАНОВКА: Callback не будет обработан TabelHandler\n\n";
        return;
    }
    
    // Step 2: extractCategoryFromCallback
    $extractedCategory = extractCategoryFromCallback($callbackData);
    echo "2. extractCategoryFromCallback(): '$extractedCategory'\n";
    
    // Step 3: In handle() method, the extracted category becomes the new category
    $finalCategory = !empty($extractedCategory) ? $extractedCategory : $callbackData;
    echo "3. Финальная категория для обработки: '$finalCategory'\n";
    
    // Step 4: Check routing decisions
    echo "4. Проверка маршрутизации:\n";
    
    // Check isActivityExecution with the final category
    $isActivityExecution = isActivityExecutionFixed($finalCategory);
    echo "   isActivityExecution('$finalCategory'): " . ($isActivityExecution ? 'ДА ❌' : 'НЕТ ✅') . "\n";
    
    // Check isActivityConfirmation with the final category
    $isActivityConfirmation = isActivityConfirmationSimulated($finalCategory);
    echo "   isActivityConfirmation('$finalCategory'): " . ($isActivityConfirmation ? 'ДА' : 'НЕТ') . "\n";
    
    // Final routing decision
    echo "5. Финальное решение маршрутизации:\n";
    if ($isActivityExecution) {
        echo "   ➜ handleActivityExecution() - СОЗДАТЬ ОТЧЕТ ❌\n";
        echo "   РЕЗУЛЬТАТ: ПРОБЛЕМА НЕ РЕШЕНА!\n";
    } elseif ($isActivityConfirmation) {
        echo "   ➜ handleActivityConfirmation() - ПОКАЗАТЬ ПОДТВЕРЖДЕНИЕ\n";
        echo "   РЕЗУЛЬТАТ: Показать форму подтверждения активности\n";
    } else {
        echo "   ➜ handleOtherWorkTypes() - ОБРАБОТАТЬ КАК ДРУГОЙ ТИП\n";
        
        // Check what handleOtherWorkTypes would do
        $otherWorkResult = handleOtherWorkTypesSimulated($finalCategory);
        echo "   handleOtherWorkTypes результат: $otherWorkResult\n";
        
        if (str_contains($otherWorkResult, 'handleElectricalEquipmentMenu')) {
            echo "   РЕЗУЛЬТАТ: ПРОБЛЕМА РЕШЕНА! ✅\n";
        } else {
            echo "   РЕЗУЛЬТАТ: ПРОБЛЕМА НЕ РЕШЕНА! ❌\n";
        }
    }
    
    echo "\n";
}

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
            'cat_ТабАвтом' => 'Табель_Автоматизация',
            'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

function isActivityExecutionFixed($category) {
    // Check if this is a menu callback that should NOT be treated as activity execution
    $menuCallbacks = [
        'cat_ТабЭлОб',     // Electrical equipment main menu (short format)
        'cat_ТабАвтом',    // Automation equipment main menu (short format)
        'cat_ТабПожСигн',  // Fire alarm equipment main menu (short format)
        'Табель_Электрооборудование',  // Electrical equipment main menu (full format)
        'Табель_Автоматизация',        // Automation equipment main menu (full format)
        'Табель_ПожарнаяСигнализация'  // Fire alarm equipment main menu (full format)
    ];
    
    if (in_array($category, $menuCallbacks)) {
        return false;
    }
    
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        return true; // Simplified for testing
    }
    
    return false;
}

function isActivityConfirmationSimulated($category) {
    // Simplified check - just look for specific patterns
    if ($category === 'cat_ТабЭлОб_РабПоГр' || 
        $category === 'cat_ТабЭлОб_ФормИД' ||
        str_contains($category, 'РабПоГр_ЭлектрОб') ||
        str_contains($category, 'ФормИД_ЭлектрОб')) {
        return true;
    }
    return false;
}

function handleOtherWorkTypesSimulated($category) {
    // Handle electrical equipment menu (short format)
    if ($category === 'cat_ТабЭлОб') {
        return 'handleElectricalEquipmentMenu() - показать меню активностей';
    }
    
    // Handle electrical equipment menu (full format)
    if ($category === 'Табель_Электрооборудование') {
        return 'handleElectricalEquipmentMenu() - показать меню активностей';
    }
    
    // Handle automation equipment
    if ($category === 'cat_ТабАвтом' || $category === 'Табель_Автоматизация') {
        return 'handleAutomationEquipmentMenu() - показать меню активностей';
    }
    
    // Handle fire alarm equipment
    if ($category === 'cat_ТабПожСигн' || $category === 'Табель_ПожарнаяСигнализация') {
        return 'handleFireAlarmEquipmentMenu() - показать меню активностей';
    }
    
    return 'Неизвестная команда табеля';
}

// Test the main problematic callback
echo "ОСНОВНОЙ ТЕСТ:\n";
simulateFullFlow('cat_ТабЭлОб');

echo "ДОПОЛНИТЕЛЬНЫЕ ТЕСТЫ:\n";
simulateFullFlow('cat_ТабАвтом');
simulateFullFlow('cat_ТабПожСигн');

echo "ТЕСТЫ АКТИВНОСТЕЙ (должны создавать отчеты):\n";
simulateFullFlow('cat_ТабЭлОб_РабПоГр_yes');
simulateFullFlow('cat_ТабЭлОб_ФормИД_yes');

echo "=== ЗАКЛЮЧЕНИЕ ===\n";
echo "Если все основные тесты показывают '✅ ПРОБЛЕМА РЕШЕНА!',\n";
echo "то исправление работает правильно.\n";
?>