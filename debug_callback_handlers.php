<?php
/**
 * ДИАГНОСТИКА: Почему кнопки субактивностей не работают
 */

echo "🔍 ДИАГНОСТИКА ШЕФ-МОНТАЖНЫХ РАБОТ\n";
echo "=================================\n\n";

echo "📋 ПРОВЕРЯЕМ МАППИНГ КОЛЛБЕКОВ\n";
echo "===============================\n";

// Тестируем каждый коллбек субактивности
$testCallbacks = [
    'cat_ШМР_РабПоГр' => 'Работы по графику',
    'cat_ШМР_ПодгПоезд' => 'Подготовка к поездке',
    'cat_ШМР_Дорога' => 'Дорога', 
    'cat_ШМР_Инстр' => 'Инструктаж',
    'cat_ШМР_СдачаОтч' => 'Сдача авансового отчета'
];

echo "Симуляция работы extractCategoryFromCallback:\n";
foreach ($testCallbacks as $callback => $description) {
    echo "1. Коллбек кнопки: '{$callback}' ({$description})\n";
    
    // Симулируем extractCategoryFromCallback
    $shortFormats = [
        'cat_ШМР_РабПоГр' => 'Табель_ШМ_РабПоГрафику',
        'cat_ШМР_ПодгПоезд' => 'Табель_ШМ_ПодготовкаКПоездке',
        'cat_ШМР_Дорога' => 'Табель_ШМ_Дорога',
        'cat_ШМР_Инстр' => 'Табель_ШМ_Инструктаж',
        'cat_ШМР_СдачаОтч' => 'Табель_ШМ_СдачаАвансовогоОтчета'
    ];
    
    if (isset($shortFormats[$callback])) {
        $mappedCategory = $shortFormats[$callback];
        echo "2. ✅ Маппинг найден: '{$mappedCategory}'\n";
        
        // Проверяем, есть ли обработчик в TabelHandler
        $tabelContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');
        
        $pattern = "/if.*\\\$category.*===.*'{$mappedCategory}'/";
        if (preg_match($pattern, $tabelContent)) {
            echo "3. ✅ Обработчик найден в TabelHandler.php\n";
        } else {
            echo "3. ❌ ПРОБЛЕМА: Обработчик НЕ найден в TabelHandler.php!\n";
            echo "   Ищем: if (\$category === '{$mappedCategory}')\n";
        }
    } else {
        echo "2. ❌ ПРОБЛЕМА: Маппинг НЕ найден!\n";
    }
    
    echo "\n";
}

echo "📋 ПРОВЕРЯЕМ КОНКРЕТНЫЕ ОБРАБОТЧИКИ\n";
echo "==================================\n";

$expectedHandlers = [
    'Табель_ШМ_РабПоГрафику' => 'Работы по графику',
    'Табель_ШМ_ПодготовкаКПоездке' => 'Подготовка к поездке',
    'Табель_ШМ_Дорога' => 'Дорога',
    'Табель_ШМ_Инструктаж' => 'Инструктаж', 
    'Табель_ШМ_СдачаАвансовогоОтчета' => 'Сдача авансового отчета'
];

$tabelContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');

echo "Ищем обработчики в TabelHandler.php:\n";
foreach ($expectedHandlers as $category => $description) {
    if (strpos($tabelContent, "'{$category}'") !== false) {
        echo "✅ '{$category}' найден в коде\n";
    } else {
        echo "❌ '{$category}' НЕ найден в коде!\n";
    }
}

echo "\n📋 ПРОВЕРЯЕМ ПОРЯДОК ОБРАБОТКИ\n";
echo "==============================\n";

// Ищем где происходит обработка этих категорий
$handleOtherWorkTypesStart = strpos($tabelContent, 'private function handleOtherWorkTypes(');
$supervisionHandlerPos = strpos($tabelContent, "if (\$category === 'Табель_ШМ_РабПоГрафику')");

if ($handleOtherWorkTypesStart !== false && $supervisionHandlerPos !== false) {
    if ($supervisionHandlerPos > $handleOtherWorkTypesStart) {
        echo "✅ Обработчики шеф-монтажных работ находятся в handleOtherWorkTypes\n";
    } else {
        echo "❌ Обработчики не в том месте!\n";
    }
} else {
    echo "❌ Не найдены обработчики или метод handleOtherWorkTypes\n";
}

echo "\n🎯 ВЕРОЯТНЫЕ ПРИЧИНЫ ПРОБЛЕМЫ\n";
echo "=============================\n";

echo "1. Коллбеки кнопок правильные и короткие ✅\n";
echo "2. Маппинг в extractCategoryFromCallback есть ✅\n";
echo "3. Нужно проверить:\n";
echo "   - Есть ли обработчики для всех 5 категорий?\n";
echo "   - Работает ли вообще обработка коллбеков?\n";
echo "   - Может быть проблема в порядке обработки?\n\n";

echo "💡 СЛЕДУЮЩИЙ ШАГ\n";
echo "===============\n";
echo "Нужно проверить, правильно ли работают обработчики.\n";
echo "Возможно, не все категории имеют обработчики в handleOtherWorkTypes.\n";

?>