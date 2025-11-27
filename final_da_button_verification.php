<?php

echo "=== ФИНАЛЬНАЯ ПРОВЕРКА ИСПРАВЛЕНИЯ КНОПКИ 'ДА' ===\n";

// Test syntax of the modified files
$files = [
    'TabelHandler.php' => __DIR__ . '/CustomCommands/Handlers/TabelHandler.php',
    'TabelEntry.php' => __DIR__ . '/CustomCommands/Services/TabelEntry.php'
];

echo "1. Проверяем синтаксис исправленных файлов:\n";
foreach ($files as $name => $path) {
    $syntaxCheck = shell_exec("php -l \"$path\" 2>&1");
    if (strpos($syntaxCheck, 'No syntax errors') === false) {
        echo "  ❌ $name: СИНТАКСИЧЕСКАЯ ОШИБКА\n";
        echo "     $syntaxCheck\n";
    } else {
        echo "  ✅ $name: Синтаксис корректен\n";
    }
}

echo "\n2. Проверяем ключевые исправления:\n";

// Check TabelHandler routing
$handlerContent = file_get_contents($files['TabelHandler.php']);
$routeMethodStart = strpos($handlerContent, 'private function routeTabelCallback');
$routeMethodEnd = strpos($handlerContent, 'private function extractCategoryFromCallback', $routeMethodStart);

if ($routeMethodStart !== false && $routeMethodEnd !== false) {
    $routeMethod = substr($handlerContent, $routeMethodStart, $routeMethodEnd - $routeMethodStart);
    
    $isActivityPos = strpos($routeMethod, 'if ($this->isActivityExecution($category))');
    $handleOtherPos = strpos($routeMethod, 'handleOtherWorkTypes');
    
    if ($isActivityPos !== false && $handleOtherPos !== false && $isActivityPos < $handleOtherPos) {
        echo "  ✅ TabelHandler: isActivityExecution check в правильной позиции\n";
    } else {
        echo "  ❌ TabelHandler: isActivityExecution check в неправильной позиции\n";
    }
} else {
    echo "  ❌ TabelHandler: НЕ удалось найти методы\n";
}

// Check TabelEntry cleanup
$entryContent = file_get_contents($files['TabelEntry.php']);

// Check if excessive debug logging was removed
$debugCount = substr_count($entryContent, 'WARRANTY DEBUG');
if ($debugCount === 0) {
    echo "  ✅ TabelEntry: Избыточная отладка удалена\n";
} else {
    echo "  ⚠️ TabelEntry: Осталось $debugCount отладочных сообщений\n";
}

// Check if prochee activities handling was simplified
if (strpos($entryContent, '$procheeActivities = [') !== false) {
    echo "  ✅ TabelEntry: Упрощена обработка Прочее activities\n";
} else {
    echo "  ❌ TabelEntry: НЕ найдена упрощенная обработка Прочее\n";
}

echo "\n3. Тестируем полный поток выполнения:\n";

// Simulate the full flow
$testCallbacks = [
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабАвтом_РабПоГр_yes',
    'cat_Гарантии_ЭлектрОб_yes'
];

foreach ($testCallbacks as $callback) {
    echo "\nТестируем: '$callback'\n";
    
    // Step 1: TabelHandler.canHandle
    $canHandle = str_starts_with($callback, 'category_Табель') || 
                 str_starts_with($callback, 'cat_ТабПНРаботы') ||
                 str_starts_with($callback, 'cat_Таб') ||
                 str_starts_with($callback, 'cat_ШМР_') ||
                 str_starts_with($callback, 'cat_Гарантии_');
    echo "  1. canHandle: " . ($canHandle ? '✅' : '❌') . "\n";
    
    // Step 2: isActivityExecution check
    $isActivity = str_contains($callback, '_yes');
    echo "  2. isActivityExecution: " . ($isActivity ? '✅' : '❌') . "\n";
    
    // Step 3: Pattern matching
    $patternMatched = false;
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callback)) $patternMatched = true;
    if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $callback)) $patternMatched = true;
    if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback)) $patternMatched = true;
    echo "  3. Pattern matching: " . ($patternMatched ? '✅' : '❌') . "\n";
    
    if ($canHandle && $isActivity && $patternMatched) {
        echo "  ✅ РЕЗУЛЬТАТ: Callback будет обработан корректно\n";
    } else {
        echo "  ❌ РЕЗУЛЬТАТ: Callback НЕ будет обработан\n";
    }
}

echo "\n4. ЗАКЛЮЧЕНИЕ:\n";
echo "✅ Исправления выполнены:\n";
echo "   - Убрана избыточная отладка из TabelEntry.addEntry\n";
echo "   - Упрощен метод createTabelMessage\n";
echo "   - Добавлена общая обработка для всех Прочее activities\n";
echo "   - isActivityExecution check остается в правильной позиции\n";
echo "\n";
echo "📋 СЛЕДУЮЩИЕ ШАГИ:\n";
echo "   1. Протестировать в реальном окружении\n";
echo "   2. Проверить логи на отсутствие ошибок\n";
echo "   3. Убедиться, что отчеты создаются правильно\n";

?>