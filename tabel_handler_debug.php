<?php
echo "=== ПРОВЕРКА ОБРАБОТКИ CALLBACK В TABELHANDLER ===\n\n";

// Проверим логику обработки callback в TabelHandler
$callbackData = 'cat_ТабЭлОб';

echo "Проверяемый callback: {$callbackData}\n\n";

echo "1. Проверка canHandle:\n";
echo "=====================\n";

// Имитируем метод canHandle из TabelHandler
function testCanHandle($callbackData) {
    $canHandle = str_starts_with($callbackData, 'category_Табель') || 
                 str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                 str_starts_with($callbackData, 'cat_Таб');
    return $canHandle;
}

$canHandle = testCanHandle($callbackData);
echo "TabelHandler->canHandle('{$callbackData}'): " . ($canHandle ? 'TRUE ✅' : 'FALSE ❌') . "\n\n";

if (!$canHandle) {
    echo "❌ ПРОБЛЕМА НАЙДЕНА: TabelHandler НЕ МОЖЕТ обработать этот callback!\n";
    echo "Callback должен начинаться с одного из:\n";
    echo "- 'category_Табель'\n";
    echo "- 'cat_ТабПНРаботы'\n";
    echo "- 'cat_Таб'\n\n";
    
    echo "Текущий callback: '{$callbackData}'\n";
    echo "str_starts_with('{$callbackData}', 'cat_Таб'): " . (str_starts_with($callbackData, 'cat_Таб') ? 'TRUE' : 'FALSE') . "\n\n";
} else {
    echo "✅ TabelHandler МОЖЕТ обработать этот callback\n\n";
}

echo "2. Проверка extractCategoryFromCallback:\n";
echo "========================================\n";

// Имитируем метод extractCategoryFromCallback из TabelHandler
function testExtractCategoryFromCallback($callbackData) {
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
            // ... остальные форматы
        ];
        
        if (isset($shortFormats[$callbackData])) {
            return $shortFormats[$callbackData];
        }
        
        // Handle other short formats as needed
        return substr($callbackData, strlen('cat_'));
    }
    
    return '';
}

$extractedCategory = testExtractCategoryFromCallback($callbackData);
echo "extractCategoryFromCallback('{$callbackData}'): '{$extractedCategory}'\n\n";

echo "3. Проверка маршрутизации в routeTabelCallback:\n";
echo "===============================================\n";

$category = $extractedCategory;
echo "Проверяемая category: '{$category}'\n\n";

// Проверяем условия маршрутизации из TabelHandler
$routingChecks = [
    'ТабЭлОб' => "if (\$category === 'ТабЭлОб')",
    'Табель_Электрооборудование' => "if (\$category === 'Табель_Электрооборудование')"
];

foreach ($routingChecks as $checkCategory => $condition) {
    $matches = ($category === $checkCategory);
    echo "{$condition}: " . ($matches ? 'MATCH ✅' : 'NO MATCH ❌') . "\n";
    if ($matches) {
        echo "  → Должен вызваться handleElectricalEquipmentMenu()\n";
    }
}

echo "\n4. Заключение:\n";
echo "=============\n";

if ($canHandle && !empty($extractedCategory)) {
    if ($category === 'ТабЭлОб' || $category === 'Табель_Электрооборудование') {
        echo "✅ ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ!\n";
        echo "TabelHandler должен правильно обработать callback и показать меню электрооборудования.\n\n";
        echo "Возможные причины, почему меню не показывается:\n";
        echo "1. Ошибка в handleElectricalEquipmentMenu() методе\n";
        echo "2. Проблема с отправкой сообщения в MessageService\n";
        echo "3. Проблема с базой данных или разрешениями пользователя\n";
        echo "4. Encoding/UTF-8 проблемы с текстом сообщения\n";
    } else {
        echo "❌ ПРОБЛЕМА: Извлеченная category не соответствует ожидаемым значениям\n";
        echo "Ожидается: 'ТабЭлОб' или 'Табель_Электрооборудование'\n";
        echo "Получено: '{$category}'\n";
    }
} else {
    echo "❌ ПРОБЛЕМА: TabelHandler не может обработать callback или не может извлечь category\n";
}

echo "\n=== ПРОВЕРКА ЗАВЕРШЕНА ===\n";
?>