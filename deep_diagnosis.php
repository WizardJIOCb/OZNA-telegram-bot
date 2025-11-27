<?php
/**
 * ГЛУБОКАЯ ДИАГНОСТИКА: Пошаговая проверка обработки коллбеков
 */

echo "🔍 ГЛУБОКАЯ ДИАГНОСТИКА ШЕФЕРСКИХ РАБОТ\n";
echo "=======================================\n\n";

echo "📋 ПРОВЕРИМ КАЖДЫЙ ШАГ ОБРАБОТКИ\n";
echo "================================\n\n";

// Симулируем полный процесс
$testCallback = 'cat_ШМР_Дорога';
echo "Тестируем коллбек: '{$testCallback}'\n\n";

echo "ШАГ 1: extractCategoryFromCallback\n";
echo "----------------------------------\n";

// Симулируем логику extractCategoryFromCallback
echo "1.1 Проверка на 'category_': ";
if (strpos($testCallback, 'category_') === 0) {
    echo "✅ Найдено\n";
    $result = substr($testCallback, strlen('category_'));
    echo "    Результат: '{$result}'\n";
} else {
    echo "❌ НЕ найдено\n";
}

echo "1.2 Проверка на короткий формат: ";
$conditions = [
    "strpos('{$testCallback}', 'cat_Таб') === 0" => strpos($testCallback, 'cat_Таб') === 0,
    "strpos('{$testCallback}', 'cat_Гарантии_') === 0" => strpos($testCallback, 'cat_Гарантии_') === 0,
    "strpos('{$testCallback}', 'cat_ШМР_') === 0" => strpos($testCallback, 'cat_ШМР_') === 0
];

$anyConditionTrue = false;
foreach ($conditions as $condition => $result) {
    echo "\n    {$condition}: " . ($result ? "✅ TRUE" : "❌ FALSE");
    if ($result) $anyConditionTrue = true;
}

echo "\n\n1.3 Общий результат условия: " . ($anyConditionTrue ? "✅ ПРОХОДИТ" : "❌ НЕ ПРОХОДИТ") . "\n\n";

if ($anyConditionTrue) {
    echo "1.4 Поиск в массиве shortFormats:\n";
    
    $shortFormats = [
        'cat_ШМР_РабПоГр' => 'Табель_ШМ_РабПоГрафику',
        'cat_ШМР_ПодгПоезд' => 'Табель_ШМ_ПодготовкаКПоездке',
        'cat_ШМР_Дорога' => 'Табель_ШМ_Дорога',
        'cat_ШМР_Инстр' => 'Табель_ШМ_Инструктаж',
        'cat_ШМР_СдачаОтч' => 'Табель_ШМ_СдачаАвансовогоОтчета'
    ];
    
    if (isset($shortFormats[$testCallback])) {
        $mappedCategory = $shortFormats[$testCallback];
        echo "    ✅ Найден маппинг: '{$testCallback}' → '{$mappedCategory}'\n";
    } else {
        echo "    ❌ Маппинг НЕ найден для '{$testCallback}'\n";
        echo "    Доступные ключи:\n";
        foreach (array_keys($shortFormats) as $key) {
            echo "      - '{$key}'\n";
        }
    }
}

echo "\nШАГ 2: Проверка обработчиков в TabelHandler\n";
echo "-------------------------------------------\n";

if (isset($mappedCategory)) {
    $tabelContent = file_get_contents('c:\ozna_bot\CustomCommands\Handlers\TabelHandler.php');
    
    echo "2.1 Ищем обработчик для категории: '{$mappedCategory}'\n";
    
    $searchPattern = "if (\$category === '{$mappedCategory}')";
    if (strpos($tabelContent, $searchPattern) !== false) {
        echo "    ✅ Обработчик найден: {$searchPattern}\n";
    } else {
        echo "    ❌ Обработчик НЕ найден: {$searchPattern}\n";
        
        // Попробуем найти похожие строки
        echo "    Ищем похожие обработчики:\n";
        if (preg_match_all("/if \(\\\$category === '[^']*ШМ[^']*'\)/", $tabelContent, $matches)) {
            foreach ($matches[0] as $match) {
                echo "      Найдено: {$match}\n";
            }
        } else {
            echo "      Никаких ШМ обработчиков не найдено!\n";
        }
    }
}

echo "\nШАГ 3: Проверка порядка выполнения\n";
echo "----------------------------------\n";

$methodPos = strpos($tabelContent, 'private function handleOtherWorkTypes(');
$earlyRoutingPos = strpos($tabelContent, "if (\$category === 'Табель_ШМ_ШМР')");

if ($methodPos !== false && $earlyRoutingPos !== false) {
    if ($earlyRoutingPos < $methodPos) {
        echo "3.1 ✅ Early routing для основной кнопки работает правильно\n";
    } else {
        echo "3.1 ❌ Early routing в неправильном месте\n";
    }
} else {
    echo "3.1 ❌ Не найден early routing или метод handleOtherWorkTypes\n";
}

echo "\n🎯 ДИАГНОЗ\n";
echo "=========\n";

if (!$anyConditionTrue) {
    echo "❌ ПРОБЛЕМА: Коллбеки cat_ШМР_* НЕ распознаются как короткий формат!\n";
    echo "Решение: Нужно исправить условие в extractCategoryFromCallback\n";
} elseif (!isset($mappedCategory)) {
    echo "❌ ПРОБЛЕМА: Маппинг для коллбека НЕ найден!\n";
    echo "Решение: Нужно добавить маппинг в массив shortFormats\n";
} elseif (!strpos($tabelContent, $searchPattern)) {
    echo "❌ ПРОБЛЕМА: Обработчик для категории НЕ найден!\n";
    echo "Решение: Нужно добавить обработчик в handleOtherWorkTypes\n";
} else {
    echo "🤔 СТРАННО: Всё должно работать, но не работает!\n";
    echo "Возможные причины:\n";
    echo "- Коллбек не доходит до TabelHandler\n";
    echo "- Исключение в обработчике\n";
    echo "- Проблема в другом месте\n";
}

echo "\n💡 СЛЕДУЮЩИЙ ШАГ\n";
echo "===============\n";
echo "Нужно добавить подробное логирование и проверить реальные логи\n";
echo "после нажатия кнопки субактивности.\n";

?>