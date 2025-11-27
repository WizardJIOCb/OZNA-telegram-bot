<?php

echo "=== ТЕСТ ПРОЦЕССА ВЫПОЛНЕНИЯ КНОПКИ 'ДА' ===\n";

// Load the TabelHandler file and check for errors
$handlerFile = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (!file_exists($handlerFile)) {
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: TabelHandler.php не найден!\n";
    exit(1);
}

// Check for syntax errors
$syntaxCheck = shell_exec("php -l \"$handlerFile\" 2>&1");
if (strpos($syntaxCheck, 'No syntax errors') === false) {
    echo "❌ СИНТАКСИЧЕСКИЕ ОШИБКИ в TabelHandler.php:\n";
    echo $syntaxCheck . "\n";
} else {
    echo "✅ TabelHandler.php: Синтаксис корректен\n";
}

// Load the TabelEntry file and check for errors
$entryFile = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
if (!file_exists($entryFile)) {
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: TabelEntry.php не найден!\n";
    exit(1);
}

// Check for syntax errors
$syntaxCheck = shell_exec("php -l \"$entryFile\" 2>&1");
if (strpos($syntaxCheck, 'No syntax errors') === false) {
    echo "❌ СИНТАКСИЧЕСКИЕ ОШИБКИ в TabelEntry.php:\n";
    echo $syntaxCheck . "\n";
} else {
    echo "✅ TabelEntry.php: Синтаксис корректен\n";
}

echo "\n=== ПРОВЕРКА МЕТОДОВ В TabelHandler.php ===\n";

$handlerContent = file_get_contents($handlerFile);

// Check for key methods
$keyMethods = [
    'routeTabelCallback',
    'isActivityExecution', 
    'handleActivityExecution'
];

foreach ($keyMethods as $method) {
    if (strpos($handlerContent, "function $method") !== false) {
        echo "✅ Найден метод: $method\n";
    } else {
        echo "❌ НЕ найден метод: $method\n";
    }
}

echo "\n=== ПРОВЕРКА КЛЮЧЕВЫХ ИЗМЕНЕНИЙ ===\n";

// Check if the critical isActivityExecution check is present before handleOtherWorkTypes
$routeMethodStart = strpos($handlerContent, 'private function routeTabelCallback');
$routeMethodEnd = strpos($handlerContent, 'private function extractCategoryFromCallback', $routeMethodStart);

if ($routeMethodStart !== false && $routeMethodEnd !== false) {
    $routeMethod = substr($handlerContent, $routeMethodStart, $routeMethodEnd - $routeMethodStart);
    
    // Check for the critical isActivityExecution check
    if (strpos($routeMethod, 'if ($this->isActivityExecution($category))') !== false) {
        echo "✅ НАЙДЕН: isActivityExecution check в routeTabelCallback\n";
        
        // Check if it's positioned correctly (before handleOtherWorkTypes)
        $isActivityPos = strpos($routeMethod, 'if ($this->isActivityExecution($category))');
        $handleOtherPos = strpos($routeMethod, 'handleOtherWorkTypes');
        
        if ($isActivityPos !== false && $handleOtherPos !== false && $isActivityPos < $handleOtherPos) {
            echo "✅ ПРАВИЛЬНАЯ ПОЗИЦИЯ: isActivityExecution проверяется ДО handleOtherWorkTypes\n";
        } else {
            echo "❌ НЕПРАВИЛЬНАЯ ПОЗИЦИЯ: isActivityExecution проверяется ПОСЛЕ handleOtherWorkTypes\n";
        }
    } else {
        echo "❌ НЕ НАЙДЕН: isActivityExecution check в routeTabelCallback\n";
        echo "    ЭТО ОСНОВНАЯ ПРИЧИНА ПРОБЛЕМЫ!\n";
    }
} else {
    echo "❌ НЕ УДАЛОСЬ найти метод routeTabelCallback\n";
}

echo "\n=== ПРОВЕРКА МЕТОДОВ В TabelEntry.php ===\n";

$entryContent = file_get_contents($entryFile);

// Check for key methods in TabelEntry
$keyEntryMethods = [
    'addEntry',
    'createTabelMessage'
];

foreach ($keyEntryMethods as $method) {
    if (strpos($entryContent, "function $method") !== false) {
        echo "✅ Найден метод: $method\n";
    } else {
        echo "❌ НЕ найден метод: $method\n";
    }
}

echo "\n=== ОКОНЧАТЕЛЬНЫЙ ДИАГНОЗ ===\n";
echo "Основные причины проблемы с кнопкой 'Да':\n";
echo "1. Отсутствует isActivityExecution check в routeTabelCallback\n";
echo "2. isActivityExecution check находится в неправильной позиции\n";
echo "3. Синтаксические ошибки в критических файлах\n";
echo "4. Проблемы с созданием отчетов в TabelEntry\n";

?>