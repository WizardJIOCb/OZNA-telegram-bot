<?php
/**
 * Диагностика подтверждений проектной деятельности ШМР
 */

echo "=== ДИАГНОСТИКА ПОДТВЕРЖДЕНИЙ ШМР ===\n\n";

// Читаем актуальный код TabelHandler
$handlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Проверим, есть ли наш паттерн в isActivityExecution
echo "1. ПРОВЕРКА ПАТТЕРНА В isActivityExecution:\n";
if (strpos($handlerContent, 'ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера') !== false) {
    echo "   ✅ Паттерн найден в isActivityExecution\n";
} else {
    echo "   ❌ Паттерн отсутствует в isActivityExecution\n";
}

// Проверим актуальные activityTypes
echo "\n2. ПРОВЕРКА activityTypes МАССИВА:\n";
$supervisionTypes = [
    'ШМ_УчастиеВЗПИ',
    'ШМ_РазрПрограммы', 
    'ШМ_РазрГрафика',
    'ШМ_ОргДопоставки',
    'ШМ_УчастиеВОПИ',
    'ШМ_ИспобязМастера'
];

foreach ($supervisionTypes as $type) {
    if (strpos($handlerContent, "'$type'") !== false) {
        echo "   ✅ $type найден в activityTypes\n";
    } else {
        echo "   ❌ $type отсутствует в activityTypes\n";
    }
}

// Проверим метод handleActivityExecution 
echo "\n3. ПРОВЕРКА handleActivityExecution:\n";
if (strpos($handlerContent, 'handleActivityExecution') !== false) {
    echo "   ✅ Метод handleActivityExecution существует\n";
    
    // Найдем начало метода
    $methodStart = strpos($handlerContent, 'private function handleActivityExecution');
    if ($methodStart !== false) {
        // Найдем следующие 50 строк метода
        $methodCode = substr($handlerContent, $methodStart, 2000);
        
        // Проверим, обрабатывает ли он ШМ активности
        if (strpos($methodCode, 'ШМ_') !== false) {
            echo "   ✅ handleActivityExecution содержит обработку ШМ активностей\n";
        } else {
            echo "   ❌ handleActivityExecution НЕ содержит обработку ШМ активностей\n";
        }
    }
} else {
    echo "   ❌ Метод handleActivityExecution не найден\n";
}

// Проверим TabelEntry
echo "\n4. ПРОВЕРКА TabelEntry:\n";
$entryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

foreach ($supervisionTypes as $type) {
    if (strpos($entryContent, "'$type'") !== false) {
        echo "   ✅ $type найден в TabelEntry\n";
    } else {
        echo "   ❌ $type отсутствует в TabelEntry\n";
    }
}

// Эмуляция реального потока
echo "\n5. ЭМУЛЯЦИЯ РЕАЛЬНОГО ПОТОКА:\n";
echo "   Тестовый callback: category_Табель_ШМ_УчастиеВЗПИ_yes\n";

// Проверим общий паттерн activityTypes
$allActivityTypesPattern = '';
$activityTypesStart = strpos($handlerContent, 'private array $activityTypes = [');
if ($activityTypesStart !== false) {
    $activityTypesEnd = strpos($handlerContent, '];', $activityTypesStart);
    $activityTypesSection = substr($handlerContent, $activityTypesStart, $activityTypesEnd - $activityTypesStart);
    
    if (strpos($activityTypesSection, 'ШМ_УчастиеВЗПИ') !== false) {
        echo "   ✅ ШМ_УчастиеВЗПИ в основном массиве activityTypes\n";
    } else {
        echo "   ❌ ШМ_УчастиеВЗПИ НЕ в основном массиве activityTypes\n";
    }
}

// Симуляция isActivityExecution 
function simulateIsActivityExecution($category) {
    // Основная логика
    if (str_contains($category, '_yes')) {
        // Проверяем специальный паттерн для ШМ проектных активностей
        if (preg_match('/^category_Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/', $category)) {
            return true;
        }
    }
    return false;
}

$testCallback = 'category_Табель_ШМ_УчастиеВЗПИ_yes';
$result = simulateIsActivityExecution($testCallback);
echo "   Результат isActivityExecution: " . ($result ? "✅ TRUE" : "❌ FALSE") . "\n";

echo "\n6. ВОЗМОЖНЫЕ ПРОБЛЕМЫ:\n";
echo "   - Callback не достигает handleActivityExecution\n";
echo "   - Проблема в extractCategoryFromCallback\n";
echo "   - Проблема в handleActivityExecution\n";
echo "   - Проблема в TabelEntry создании сообщения\n";

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>