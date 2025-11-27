<?php
echo "=== УПРОЩЕННЫЙ ТЕСТ CALLBACK'ОВ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

// Имитируем метод extractCategoryFromCallback
function extractCategoryFromCallback(string $callbackData): string
{
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
            'cat_ТабПожСигн_Наз' => 'Табель_ПожарнаяСигнализация',
            'cat_ТабПожСигн_РабПоГр' => 'Табель_РабПоГр_ПожСигн',
            'cat_ТабПожСигн_ФормИД' => 'Табель_ФормИД_ПожСигн',
            'cat_ТабПожСигн_ИзмСх' => 'Табель_ИзмСх_ПожСигн',
            'cat_ТабПожСигн_МонтКб' => 'Табель_МонтКб_ПожСигн',
            'cat_ТабПожСигн_РаскКб' => 'Табель_РаскКб_ПожСигн',
            'cat_ТабПожСигн_МонтАСУ' => 'Табель_МонтАСУ_ПожСигн',
            'cat_ТабПожСигн_УдалПО' => 'Табель_УдалПО_ПожСигн',
            'cat_ТабПожСигн_Проч' => 'Табель_Прочее_ПожСигн',
            'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн',
            'cat_ТабПожСигн_ПодтВех' => 'Табель_ПодтВех_ПожСигн',
            // Handle confirmation callbacks as well
            'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
            'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes',
            'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
            'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ЭлектрОб_yes',
            'cat_ТабЭлОб_РаскКбЛн_yes' => 'Табель_РаскКбЛн_ЭлектрОб_yes',
            'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes',
            'cat_ТабЭлОб_ПодтВех_yes' => 'Табель_ПодтВех_ЭлектрОб_yes',
            'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes',
            'cat_ТабПожСигн_РабПоГр_yes' => 'Табель_РабПоГр_ПожСигн_yes',
            'cat_ТабПожСигн_ФормИД_yes' => 'Табель_ФормИД_ПожСигн_yes',
            'cat_ТабПожСигн_ИзмСх_yes' => 'Табель_ИзмСх_ПожСигн_yes',
            'cat_ТабПожСигн_МонтКб_yes' => 'Табель_МонтКб_ПожСигн_yes',
            'cat_ТабПожСигн_РаскКб_yes' => 'Табель_РаскКб_ПожСигн_yes',
            'cat_ТабПожСигн_МонтАСУ_yes' => 'Табель_МонтАСУ_ПожСигн_yes',
            'cat_ТабПожСигн_УдалПО_yes' => 'Табель_УдалПО_ПожСигн_yes',
            'cat_ТабПожСигн_Проч_yes' => 'Табель_Прочее_ПожСигн_yes',
            'cat_ТабПожСигн_ДопРаб_yes' => 'Табель_ДопРаб_ПожСигн_yes',
            'cat_ТабПожСигн_ПодтВех_yes' => 'Табель_ПодтВех_ПожСигн_yes'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

// Имитируем метод handleOtherWorkTypes (упрощенная версия)
function handleOtherWorkTypes(string $category): string
{
    echo "handleOtherWorkTypes вызван с категорией: {$category}\n";
    
    // Handle electrical equipment menu with new short callback format
    if ($category === 'Табель_Электрооборудование' || 
        $category === 'cat_ТабЭлОб' ||
        $category === 'cat_ТабЭлОб_Наз') {
        echo "  ✅ Обнаружено меню электрооборудования\n";
        return "handleElectricalEquipmentMenu() - показать меню активностей";
    }
    
    // Handle individual commissioning activities with short callbacks
    // Route to sub-menus for equipment categories that have sub-activities
    if ($category === 'ТабЭлОб') {
        echo "  ✅ Обнаружено меню электрооборудования (короткий формат)\n";
        return "handleElectricalEquipmentMenu() - показать меню активностей";
    }
    
    if ($category === 'ТабАвтом') {
        echo "  ✅ Обнаружено меню автоматизации\n";
        return "handleAutomationEquipmentMenu()";
    }
    
    if ($category === 'ТабПожСигн') {
        echo "  ✅ Обнаружено меню пожарной сигнализации\n";
        return "handleFireAlarmEquipmentMenu()";
    }
    
    // Handle electrical equipment menu (full format from extractCategoryFromCallback)
    if ($category === 'Табель_Электрооборудование') {
        echo "  ✅ Обнаружено меню электрооборудования (полный формат)\n";
        return "handleElectricalEquipmentMenu() - показать меню активностей";
    }
    
    // Handle automation equipment menu (full format from extractCategoryFromCallback) 
    if ($category === 'Табель_Автоматизация') {
        echo "  ✅ Обнаружено меню автоматизации (полный формат)\n";
        return "handleAutomationEquipmentMenu()";
    }
    
    // Handle fire alarm equipment menu (full format from extractCategoryFromCallback)
    if ($category === 'Табель_ПожарнаяСигнализация') {
        echo "  ✅ Обнаружено меню пожарной сигнализации (полный формат)\n";
        return "handleFireAlarmEquipmentMenu()";
    }
    
    // Handle electrical equipment activities
    // Works by schedule for electrical equipment
    if ($category === 'ТабЭлОб_РабПоГр' || $category === 'cat_ТабЭлОб_РабПоГр') {
        echo "  ✅ Обнаружена активность 'Работы по графику'\n";
        return "Показать подтверждение для 'Работы по графику'";
    }

    // ID formation for electrical equipment
    if ($category === 'ТабЭлОб_ФормИД' || $category === 'cat_ТабЭлОб_ФормИД') {
        echo "  ✅ Обнаружена активность 'Формирование ИД'\n";
        return "Показать подтверждение для 'Формирование ИД'";
    }

    // Additional works for electrical equipment
    if ($category === 'ТабЭлОб_ДопРаб' || $category === 'cat_ТабЭлОб_ДопРаб') {
        echo "  ✅ Обнаружена активность 'Дополнительные работы'\n";
        return "handleAdditionalWorksElectricalMenu() - показать подменю";
    }

    // Milestone confirmation for electrical equipment
    if ($category === 'ТабЭлОб_ПодтВех' || $category === 'cat_ТабЭлОб_ПодтВех') {
        echo "  ✅ Обнаружена активность 'Подтвердить достижение вехи'\n";
        return "Показать подтверждение для 'Подтвердить достижение вехи'";
    }
    
    // Handle additional works activities for electrical equipment
    // Scheme changes for electrical equipment
    if ($category === 'ТабЭлОб_ИзмСхПд' || $category === 'cat_ТабЭлОб_ИзмСхПд') {
        echo "  ✅ Обнаружена активность 'Изменение схем подключения'\n";
        return "Показать подтверждение для 'Изменение схем подключения'";
    }

    // Cable installation for electrical equipment
    if ($category === 'ТабЭлОб_МонтКбЛн' || $category === 'cat_ТабЭлОб_МонтКбЛн') {
        echo "  ✅ Обнаружена активность 'Монтаж кабельных линий'\n";
        return "Показать подтверждение для 'Монтаж кабельных линий'";
    }

    // Cable wiring for electrical equipment
    if ($category === 'ТабЭлОб_РаскКбЛн' || $category === 'cat_ТабЭлОб_РаскКбЛн') {
        echo "  ✅ Обнаружена активность 'Расключение кабельных линий'\n";
        return "Показать подтверждение для 'Расключение кабельных линий'";
    }

    // Equipment installation for electrical equipment
    if ($category === 'ТабЭлОб_МонтОб' || $category === 'cat_ТабЭлОб_МонтОб') {
        echo "  ✅ Обнаружена активность 'Монтаж оборудования'\n";
        return "Показать подтверждение для 'Монтаж оборудования'";
    }

    // Other works for electrical equipment
    if ($category === 'ТабЭлОб_Проч' || $category === 'cat_ТабЭлОб_Проч') {
        echo "  ✅ Обнаружена активность 'Прочее'\n";
        return "Показать подтверждение для 'Прочее'";
    }
    
    echo "  ❌ Неизвестная категория\n";
    return "Неизвестная команда табеля";
}

echo "1. ТЕСТИРОВАНИЕ ИЗВЛЕЧЕНИЯ КАТЕГОРИЙ:\n";
echo "====================================\n";

$testCallbacks = [
    'cat_ТабЭлОб' => 'Табель_Электрооборудование',
    'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
    'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
    'cat_ТабЭлОб_Проч' => 'Табель_Прочее_ЭлектрОб'
];

foreach ($testCallbacks as $callback => $expected) {
    $actual = extractCategoryFromCallback($callback);
    $status = ($actual === $expected) ? '✅' : '❌';
    echo "  {$status} {$callback} → {$actual}\n";
}

echo "\n2. ТЕСТИРОВАНИЕ ОБРАБОТКИ КАТЕГОРИЙ:\n";
echo "===================================\n";

foreach ($testCallbacks as $callback => $expected) {
    $category = extractCategoryFromCallback($callback);
    echo "\nТест для callback: {$callback}\n";
    echo "Извлеченная категория: {$category}\n";
    $result = handleOtherWorkTypes($category);
    echo "  Результат: {$result}\n";
}

echo "\n3. СИМУЛЯЦИЯ ПОЛНОГО ПОТОКА:\n";
echo "===========================\n";

// Симулируем полный поток обработки
$callbackData = 'cat_ТабЭлОб';
echo "1. Получен callback от Telegram: {$callbackData}\n";

// Проверяем canHandle (упрощенно)
$canHandle = (strpos($callbackData, 'category_Табель') === 0 || 
              strpos($callbackData, 'cat_ТабПНРаботы') === 0 ||
              strpos($callbackData, 'cat_Таб') === 0);
echo "2. TabelHandler->canHandle('{$callbackData}'): " . ($canHandle ? 'ДА ✅' : 'НЕТ ❌') . "\n";

if ($canHandle) {
    // Извлекаем категорию
    $category = extractCategoryFromCallback($callbackData);
    echo "3. Извлеченная категория: {$category}\n";
    
    // Обрабатываем категорию
    echo "4. Обработка категории:\n";
    $result = handleOtherWorkTypes($category);
    echo "   Результат: {$result}\n";
    
    if (strpos($result, 'handleElectricalEquipmentMenu') !== false) {
        echo "\n✅ ВСЁ РАБОТАЕТ ПРАВИЛЬНО!\n";
        echo "   При нажатии кнопки 'Электрооборудование' должно показываться меню активностей,\n";
        echo "   а не формироваться отчет.\n";
    } else {
        echo "\n❌ ПРОБЛЕМА!\n";
        echo "   Что-то пошло не так в обработке.\n";
    }
} else {
    echo "❌ TabelHandler не может обработать этот callback!\n";
}

echo "\n4. ЗАКЛЮЧЕНИЕ:\n";
echo "=============\n";
echo "Если тесты показывают ✅, то логика обработки callback'ов правильная.\n";
echo "Проблема может быть в другом месте:\n";
echo "  - В маршрутизации внутри метода handle()\n";
echo "  - В состоянии разговора (conversation)\n";
echo "  - В уровне доступа пользователя\n";
echo "  - В кэшировании или устаревшем коде\n";

// Simple test to verify the fix for electrical equipment button callback handling

// Test data that simulates the callback data that would be received
$testCallbacks = [
    // These should be treated as menu callbacks (not activity execution)
    'cat_ТабЭлОб' => 'menu',
    'cat_ТабАвтом' => 'menu',
    'cat_ТабПожСигн' => 'menu',
    
    // These should be treated as activity execution callbacks
    'cat_ТабЭлОб_РабПоГр_yes' => 'activity',
    'cat_ТабЭлОб_ФормИД_yes' => 'activity',
    'cat_ТабПНРаботы_yes' => 'activity',
    
    // These should be treated as confirmation callbacks (not activity execution)
    'cat_ТабЭлОб_РабПоГр' => 'confirmation',
    'cat_ТабЭлОб_ФормИД' => 'confirmation',
];

echo "Testing callback categorization:\n";
echo "================================\n";

foreach ($testCallbacks as $callback => $expectedType) {
    echo "Callback: $callback\n";
    echo "Expected type: $expectedType\n";
    
    // Simulate the logic from isActivityExecution method
    $isActivityExecution = false;
    
    // Check if this is a menu callback that should NOT be treated as activity execution
    $menuCallbacks = [
        'cat_ТабЭлОб',     // Electrical equipment main menu
        'cat_ТабАвтом',    // Automation equipment main menu
        'cat_ТабПожСигн'   // Fire alarm equipment main menu
    ];
    
    if (in_array($callback, $menuCallbacks)) {
        $isActivityExecution = false;
        echo "Result: Menu callback (not activity execution)\n";
    } else if (str_contains($callback, '_yes')) {
        $isActivityExecution = true;
        echo "Result: Activity execution\n";
    } else {
        $isActivityExecution = false;
        echo "Result: Other (confirmation or menu)\n";
    }
    
    $actualType = $isActivityExecution ? 'activity' : 'menu/other';
    $status = ($actualType === $expectedType || ($expectedType === 'menu/other' && !$isActivityExecution)) ? 'PASS' : 'FAIL';
    
    echo "Status: $status\n";
    echo "------------------------\n";
}

echo "Test completed!\n";
