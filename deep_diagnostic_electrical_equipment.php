<?php
// Deep diagnostic for electrical equipment button issue

echo "=== УГЛУБЛЕННАЯ ДИАГНОСТИКА КНОПКИ ЭЛЕКТРООБОРУДОВАНИЯ ===\n\n";

// Step 1: Check the actual button configuration in MessageService
echo "1. ПРОВЕРКА КОНФИГУРАЦИИ КНОПКИ В MESSAGESERVICE:\n";
echo "================================================\n";

$electricalButtonConfigs = [
    'Из createCommissioningWorksKeyboard()' => [
        'text' => '⚡ Электрооборудование',
        'callback_data' => 'cat_ТабЭлОб'
    ],
    'Из createElectricalEquipmentKeyboard()' => [
        'text' => '📅 Работы по графику',
        'callback_data' => 'cat_ТабЭлОб_РабПоГр'
    ]
];

foreach ($electricalButtonConfigs as $source => $config) {
    echo "$source:\n";
    echo "  text: '{$config['text']}'\n";
    echo "  callback_data: '{$config['callback_data']}'\n";
    echo "  length: " . strlen($config['callback_data']) . " bytes\n\n";
}

// Step 2: Simulate the complete flow
echo "2. СИМУЛЯЦИЯ ПОЛНОГО ПОТОКА ОБРАБОТКИ:\n";
echo "======================================\n";

$callbackData = 'cat_ТабЭлОб';
echo "Входящий callback: '$callbackData'\n\n";

// Step 2.1: canHandle check
echo "2.1 Проверка canHandle():\n";
$canHandle1 = str_starts_with($callbackData, 'category_Табель');
$canHandle2 = str_starts_with($callbackData, 'cat_ТабПНРаботы');
$canHandle3 = str_starts_with($callbackData, 'cat_Таб');
$canHandleResult = $canHandle1 || $canHandle2 || $canHandle3;

echo "  str_starts_with('category_Табель'): " . ($canHandle1 ? 'true' : 'false') . "\n";
echo "  str_starts_with('cat_ТабПНРаботы'): " . ($canHandle2 ? 'true' : 'false') . "\n";
echo "  str_starts_with('cat_Таб'): " . ($canHandle3 ? 'true' : 'false') . "\n";
echo "  РЕЗУЛЬТАТ canHandle: " . ($canHandleResult ? 'ДА ✅' : 'НЕТ ❌') . "\n\n";

// Step 2.2: extractCategoryFromCallback
echo "2.2 Извлечение категории extractCategoryFromCallback():\n";

function extractCategoryFromCallbackDiagnostic($callbackData) {
    echo "  Входящие данные: '$callbackData'\n";
    
    // Handle original category format
    if (strpos($callbackData, 'category_') === 0) {
        $result = substr($callbackData, strlen('category_'));
        echo "  Найден формат 'category_': '$result'\n";
        return $result;
    }
    
    // Handle short category format for commissioning equipment
    if (strpos($callbackData, 'cat_Таб') === 0) {
        echo "  Найден короткий формат 'cat_Таб'\n";
        
        // Map short formats to full category names
        $shortFormats = [
            'cat_ТабЭлОб' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
            'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
            'cat_ТабЭлОб_ФормИД' => 'Табель_ФормИД_ЭлектрОб'
        ];
        
        if (isset($shortFormats[$callbackData])) {
            $result = $shortFormats[$callbackData];
            echo "  Найдено соответствие в shortFormats: '$result'\n";
            return $result;
        }
        
        // Handle other short formats as needed
        $result = substr($callbackData, strlen('cat_'));
        echo "  Использован fallback для короткого формата: '$result'\n";
        return $result;
    }
    
    echo "  Не найдено соответствие, возвращаем пустую строку\n";
    return '';
}

$extractedCategory = extractCategoryFromCallbackDiagnostic($callbackData);
echo "  РЕЗУЛЬТАТ извлечения: '$extractedCategory'\n\n";

// Step 2.3: Check routing in handle() method
echo "2.3 Проверка маршрутизации в handle():\n";

// Check if it's date selection
echo "  isDateSelection(): НЕТ (это не дата)\n";
echo "  isProjectSelection(): НЕТ (это не проект)\n";
echo "  Другие специальные проверки: НЕТ\n";

// Check isActivityExecution
echo "  Проверка isActivityExecution():\n";

function isActivityExecutionDiagnostic($category) {
    echo "    Входящая категория: '$category'\n";
    
    // Check if this is a menu callback that should NOT be treated as activity execution
    $menuCallbacks = [
        'cat_ТабЭлОб',     // Electrical equipment main menu
        'cat_ТабАвтом',    // Automation equipment main menu
        'cat_ТабПожСигн'   // Fire alarm equipment main menu
    ];
    
    echo "    Проверка меню callbacks: [" . implode(', ', $menuCallbacks) . "]\n";
    
    if (in_array($category, $menuCallbacks)) {
        echo "    ✅ НАЙДЕНО в menu callbacks - возвращаем FALSE\n";
        return false;
    }
    
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        echo "    Содержит '_yes' - это activity execution\n";
        return true;
    }
    
    echo "    ❌ НЕ найдено в menu callbacks и не содержит '_yes'\n";
    return false;
}

$isActivityExecution = isActivityExecutionDiagnostic($callbackData);
echo "  РЕЗУЛЬТАТ isActivityExecution: " . ($isActivityExecution ? 'ДА (создать отчет) ❌' : 'НЕТ (не создавать отчет) ✅') . "\n\n";

// Step 2.4: Check isActivityConfirmation  
echo "  Проверка isActivityConfirmation():\n";

function isActivityConfirmationDiagnostic($category) {
    echo "    Входящая категория: '$category'\n";
    
    // Standard activity types check would go here
    echo "    Проверка стандартных типов активности: НЕТ СООТВЕТСТВИЙ\n";
    
    // Check short callback format for electrical equipment
    $shortToFullMap = [
        'РабПоГр' => 'РабПоГр_ЭлектрОб',
        'ФормИД' => 'ФормИД_ЭлектрОб',
        'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
        'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
        'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
        'ДопРаб' => 'ДопРаб_ЭлектрОб',
        'ПодтВех' => 'ПодтВех_ЭлектрОб',
        'Проч' => 'Прочее_ЭлектрОб',
        'ТабПНРаботы' => 'ПусконаладочныеРаботы'
    ];
    
    foreach ($shortToFullMap as $shortKey => $fullActivity) {
        if ($category === "cat_ТабЭлОб_{$shortKey}") {
            echo "    ✅ НАЙДЕНО соответствие для electrical activity: $shortKey\n";
            return true;
        }
        if ($category === "cat_{$shortKey}") {
            echo "    ✅ НАЙДЕНО соответствие для commissioning works: $shortKey\n";
            return true;
        }
    }
    
    echo "    ❌ НЕТ СООТВЕТСТВИЙ для activity confirmation\n";
    return false;
}

$isActivityConfirmation = isActivityConfirmationDiagnostic($callbackData);
echo "  РЕЗУЛЬТАТ isActivityConfirmation: " . ($isActivityConfirmation ? 'ДА (показать подтверждение) ✅' : 'НЕТ ❌') . "\n\n";

// Step 2.5: Final routing decision
echo "2.4 ФИНАЛЬНОЕ РЕШЕНИЕ МАРШРУТИЗАЦИИ:\n";
if ($isActivityExecution) {
    echo "  ➜ handleActivityExecution() - СОЗДАТЬ ОТЧЕТ ❌\n";
} elseif ($isActivityConfirmation) {
    echo "  ➜ handleActivityConfirmation() - ПОКАЗАТЬ ПОДТВЕРЖДЕНИЕ ✅\n";
} else {
    echo "  ➜ handleOtherWorkTypes() - ОБРАБОТАТЬ КАК ДРУГОЙ ТИП ✅\n";
}

echo "\n3. ПРОВЕРКА HANDLEOTHERWORKTYPES():\n";
echo "===================================\n";

function handleOtherWorkTypesDiagnostic($category) {
    echo "Категория для обработки: '$category'\n";
    
    // Handle electrical equipment menu (short format)
    if ($category === 'cat_ТабЭлОб') {
        echo "✅ СОВПАДЕНИЕ: cat_ТабЭлОб - вызов handleElectricalEquipmentMenu()\n";
        return 'handleElectricalEquipmentMenu()';
    }
    
    // Handle electrical equipment menu (full format from extractCategoryFromCallback)
    if ($category === 'Табель_Электрооборудование') {
        echo "✅ СОВПАДЕНИЕ: Табель_Электрооборудование - вызов handleElectricalEquipmentMenu()\n";
        return 'handleElectricalEquipmentMenu()';
    }
    
    echo "❌ НЕТ СОВПАДЕНИЙ - вернуть 'Неизвестная команда табеля'\n";
    return 'Неизвестная команда табеля';
}

$otherWorkTypesResult = handleOtherWorkTypesDiagnostic($callbackData);
echo "РЕЗУЛЬТАТ: $otherWorkTypesResult\n\n";

// Step 4: Analysis and potential issues
echo "4. АНАЛИЗ ПОТЕНЦИАЛЬНЫХ ПРОБЛЕМ:\n";
echo "================================\n";

echo "ПРОБЛЕМА 1: Различие между callbackData и extractedCategory\n";
echo "  callbackData: '$callbackData'\n";
echo "  extractedCategory: '$extractedCategory'\n";
echo "  Проблема: handleOtherWorkTypes получает callbackData, а не extractedCategory!\n\n";

echo "ПРОБЛЕМА 2: Порядок проверок в routeTabelCallback()\n";
echo "  Если isActivityExecution() вызывается ДО handleOtherWorkTypes(),\n";
echo "  то callback может быть неправильно обработан.\n\n";

echo "ПРОБЛЕМА 3: Состояние разговора (Conversation)\n";
echo "  Возможно, в состоянии разговора уже есть project_code,\n";
echo "  что влияет на логику обработки.\n\n";

echo "5. РЕКОМЕНДАЦИИ ПО ИСПРАВЛЕНИЮ:\n";
echo "===============================\n";
echo "1. Убедиться, что handleOtherWorkTypes() получает правильную категорию\n";
echo "2. Проверить порядок вызовов в routeTabelCallback()\n";
echo "3. Добавить отладочные логи в реальный код\n";
echo "4. Проверить состояние conversation\n\n";

echo "=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>