<?php
// Тестируем обработку коллбеков гарантийного оборудования

echo "=== ДИАГНОСТИКА КОЛЛБЕКОВ ГАРАНТИЙНОГО ОБОРУДОВАНИЯ ===\n\n";

// Симуляция части функции extractCategoryFromCallback из TabelHandler
function extractCategoryFromCallback($callbackData) {
    // Обрезка префикса category_
    if (str_starts_with($callbackData, 'category_')) {
        return substr($callbackData, strlen('category_'));
    }
    
    // Mapping table из TabelHandler (нужно найти в реальном коде)
    $shortFormats = [
        'category_Табель_РабГарантии_ЭлектрОб' => 'Табель_РабГарантии_ЭлектрОб',
        'category_Табель_РабГарантии_СрИзм' => 'Табель_РабГарантии_СрИзм',
        'category_Табель_РабГарантии_НасОб' => 'Табель_РабГарантии_НасОб',
        'category_Табель_РабГарантии_ТрубАрм' => 'Табель_РабГарантии_ТрубАрм',
        'category_Табель_РабГарантии_ИспМех' => 'Табель_РабГарантии_ИспМех',
        'category_Табель_РабГарантии_КонтрОб' => 'Табель_РабГарантии_КонтрОб',
        'category_Табель_РабГарантии_Прочее' => 'Табель_РабГарантии_Прочее'
    ];
    
    if (isset($shortFormats[$callbackData])) {
        return $shortFormats[$callbackData];
    }
    
    return '';
}

// Тестируем коллбеки гарантийного оборудования
$warrantyCallbacks = [
    'category_Табель_РабГарантии_ЭлектрОб',
    'category_Табель_РабГарантии_СрИзм',
    'category_Табель_РабГарантии_НасОб',
    'category_Табель_РабГарантии_ТрубАрм',
    'category_Табель_РабГарантии_ИспМех',
    'category_Табель_РабГарантии_КонтрОб',
    'category_Табель_РабГарантии_Прочее'
];

echo "1. ТЕСТИРОВАНИЕ МАППИНГА КОЛЛБЕКОВ:\n";
echo "=====================================\n";

foreach ($warrantyCallbacks as $callback) {
    $mapped = extractCategoryFromCallback($callback);
    echo "Коллбек: {$callback}\n";
    echo "Маппинг: {$mapped}\n";
    
    // Проверяем, есть ли обработчик для этого маппинга
    if ($mapped === 'Табель_РабГарантии_ЭлектрОб') {
        echo "  ✅ Есть обработчик в handleOtherWorkTypes\n";
    } elseif (str_starts_with($mapped, 'Табель_РабГарантии_')) {
        echo "  ✅ Должен быть обработчик для: {$mapped}\n";
    } else {
        echo "  ❌ Нет обработчика\n";
    }
    echo "\n";
}

echo "2. ПРОВЕРКА ДЛИНЫ КОЛЛБЕКОВ:\n";
echo "============================\n";
foreach ($warrantyCallbacks as $callback) {
    $length = strlen($callback);
    echo "Коллбек: {$callback}\n";
    echo "Длина: {$length} байт ";
    if ($length > 64) {
        echo "❌ ПРЕВЫШАЕТ ЛИМИТ TELEGRAM!\n";
    } else {
        echo "✅ В пределах лимита\n";
    }
    echo "\n";
}

echo "3. ПРОВЕРКА КОЛЛБЕКОВ ПОДТВЕРЖДЕНИЯ:\n";
echo "===================================\n";

$confirmationCallbacks = [
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_Гарантии_СрИзм_yes',
    'cat_Гарантии_НасОб_yes',
    'cat_Гарантии_ТрубАрм_yes',
    'cat_Гарантии_ИспМех_yes',
    'cat_Гарантии_КонтрОб_yes',
    'cat_Гарантии_Прочее_yes'
];

foreach ($confirmationCallbacks as $callback) {
    $length = strlen($callback);
    echo "Коллбек подтверждения: {$callback}\n";
    echo "Длина: {$length} байт ";
    if ($length > 64) {
        echo "❌ ПРЕВЫШАЕТ ЛИМИТ!\n";
    } else {
        echo "✅ В пределах лимита\n";
    }
    echo "\n";
}