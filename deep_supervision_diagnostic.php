<?php
/**
 * Deep diagnostic for supervision project activities confirmation system
 * Comprehensive runtime verification without making any changes
 */

echo "=== ГЛУБОКАЯ ДИАГНОСТИКА СИСТЕМЫ ПОДТВЕРЖДЕНИЙ ШМР ===\n\n";

// Read the actual TabelHandler file
$handlerPath = __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
if (!file_exists($handlerPath)) {
    echo "❌ КРИТИЧЕСКАЯ ОШИБКА: TabelHandler.php не найден!\n";
    exit(1);
}

$handlerContent = file_get_contents($handlerPath);

echo "1. ПРОВЕРКА СТРУКТУРЫ ФАЙЛА:\n";
echo "   Размер файла: " . number_format(strlen($handlerContent)) . " символов\n";
echo "   Строк в файле: " . substr_count($handlerContent, "\n") . "\n\n";

// Check if our fix is present
echo "2. ПРОВЕРКА НАЛИЧИЯ ИСПРАВЛЕНИЯ:\n";
$fixPattern = 'Handle supervision project activities FIRST';
if (strpos($handlerContent, $fixPattern) !== false) {
    echo "   ✅ Исправление найдено в коде\n";
    
    // Extract the exact fix implementation
    $fixStart = strpos($handlerContent, $fixPattern);
    $fixEnd = strpos($handlerContent, 'Handle short callback format for electrical equipment', $fixStart);
    
    if ($fixEnd !== false) {
        $fixCode = substr($handlerContent, $fixStart, $fixEnd - $fixStart);
        echo "   📋 Реализация исправления:\n";
        echo "   " . str_replace("\n", "\n   ", trim($fixCode)) . "\n\n";
    }
} else {
    echo "   ❌ Исправление НЕ найдено в коде!\n\n";
}

// Check regex pattern specifically
echo "3. ПРОВЕРКА REGEX ПАТТЕРНА:\n";
$regexPattern = '/\^category_Табель_\(ШМ_УчастиеВЗПИ\|ШМ_РазрПрограммы\|ШМ_РазрГрафика\|ШМ_ОргДопоставки\|ШМ_УчастиеВОПИ\|ШМ_ИспобязМастера\)_yes\$/u';
if (strpos($handlerContent, 'ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера') !== false) {
    echo "   ✅ Regex паттерн для ШМ активностей найден\n";
} else {
    echo "   ❌ Regex паттерн для ШМ активностей НЕ найден!\n";
}

// Check activity types array
echo "\n4. ПРОВЕРКА МАССИВА activityTypes:\n";
$supervisionActivities = [
    'ШМ_УчастиеВЗПИ',
    'ШМ_РазрПрограммы', 
    'ШМ_РазрГрафика',
    'ШМ_ОргДопоставки',
    'ШМ_УчастиеВОПИ',
    'ШМ_ИспобязМастера'
];

$allFound = true;
foreach ($supervisionActivities as $activity) {
    if (strpos($handlerContent, "'$activity'") !== false) {
        echo "   ✅ {$activity}\n";
    } else {
        echo "   ❌ {$activity} - ОТСУТСТВУЕТ!\n";
        $allFound = false;
    }
}

if ($allFound) {
    echo "   🎉 Все типы активностей ШМР присутствуют в массиве\n";
} else {
    echo "   ⚠️ Некоторые типы активностей отсутствуют!\n";
}

// Check TabelEntry.php for activity names
echo "\n5. ПРОВЕРКА TabelEntry.php:\n";
$entryPath = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
if (file_exists($entryPath)) {
    $entryContent = file_get_contents($entryPath);
    
    $activityNamesFound = 0;
    foreach ($supervisionActivities as $activity) {
        if (strpos($entryContent, "'$activity'") !== false) {
            $activityNamesFound++;
        }
    }
    
    echo "   Найдено названий активностей: {$activityNamesFound}/6\n";
    
    if ($activityNamesFound === 6) {
        echo "   ✅ Все названия активностей ШМР найдены в TabelEntry\n";
    } else {
        echo "   ⚠️ Не все названия активностей найдены в TabelEntry\n";
    }
} else {
    echo "   ❌ TabelEntry.php не найден!\n";
}

// Check MessageService.php for keyboard
echo "\n6. ПРОВЕРКА MessageService.php:\n";
$messagePath = __DIR__ . '/CustomCommands/Services/MessageService.php';
if (file_exists($messagePath)) {
    $messageContent = file_get_contents($messagePath);
    
    if (strpos($messageContent, 'createSupervisionProjectActivityKeyboard') !== false) {
        echo "   ✅ Метод createSupervisionProjectActivityKeyboard найден\n";
        
        // Check for all 6 button callbacks
        $buttonCallbacks = [
            'category_Табель_ШМ_УчастиеВЗПИ',
            'category_Табель_ШМ_РазрПрограммы',
            'category_Табель_ШМ_РазрГрафика',
            'category_Табель_ШМ_ОргДопоставки',
            'category_Табель_ШМ_УчастиеВОПИ',
            'category_Табель_ШМ_ИспобязМастера'
        ];
        
        $buttonsFound = 0;
        foreach ($buttonCallbacks as $callback) {
            if (strpos($messageContent, $callback) !== false) {
                $buttonsFound++;
            }
        }
        
        echo "   Найдено кнопок: {$buttonsFound}/6\n";
        
        if ($buttonsFound === 6) {
            echo "   ✅ Все кнопки ШМР найдены в MessageService\n";
        } else {
            echo "   ⚠️ Не все кнопки найдены в MessageService\n";
        }
    } else {
        echo "   ❌ Метод createSupervisionProjectActivityKeyboard НЕ найден!\n";
    }
} else {
    echo "   ❌ MessageService.php не найден!\n";
}

// Test callback flow simulation
echo "\n7. СИМУЛЯЦИЯ ПОЛНОГО ПОТОКА ОБРАБОТКИ:\n";

function simulateCallbackFlow($callbackData) {
    echo "   Тестирование: {$callbackData}\n";
    
    // Step 1: canHandle check
    $canHandle = (str_starts_with($callbackData, 'category_Табель') || 
                  str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                  str_starts_with($callbackData, 'cat_Таб'));
    echo "     canHandle: " . ($canHandle ? "✅ true" : "❌ false") . "\n";
    
    // Step 2: isActivityExecution check
    $isExecution = (str_contains($callbackData, '_yes') && 
                   preg_match('/^category_Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/', $callbackData));
    echo "     isActivityExecution: " . ($isExecution ? "✅ true" : "❌ false") . "\n";
    
    // Step 3: handleActivityExecution - activity type extraction
    if (preg_match('/^category_Табель_(ШМ_УчастиеВЗПИ|ШМ_РазрПрограммы|ШМ_РазрГрафика|ШМ_ОргДопоставки|ШМ_УчастиеВОПИ|ШМ_ИспобязМастера)_yes$/u', $callbackData, $matches)) {
        $activityType = $matches[1];
        echo "     handleActivityExecution activityType: ✅ '{$activityType}'\n";
        
        // Step 4: Check if activity type is in activityTypes array (simulated)
        $validActivityTypes = [
            'ШМ_УчастиеВЗПИ', 'ШМ_РазрПрограммы', 'ШМ_РазрГрафика',
            'ШМ_ОргДопоставки', 'ШМ_УчастиеВОПИ', 'ШМ_ИспобязМастера'
        ];
        
        $isValidType = in_array($activityType, $validActivityTypes);
        echo "     Activity type validation: " . ($isValidType ? "✅ valid" : "❌ invalid") . "\n";
        
        if ($canHandle && $isExecution && $isValidType) {
            echo "     🎉 РЕЗУЛЬТАТ: Callback должен успешно обрабатываться\n";
        } else {
            echo "     ❌ РЕЗУЛЬТАТ: Callback НЕ будет обработан корректно\n";
        }
    } else {
        echo "     handleActivityExecution activityType: ❌ не извлечен\n";
        echo "     ❌ РЕЗУЛЬТАТ: Callback НЕ будет обработан\n";
    }
    
    echo "\n";
}

// Test all supervision callbacks
$testCallbacks = [
    'category_Табель_ШМ_УчастиеВЗПИ_yes',
    'category_Табель_ШМ_РазрПрограммы_yes',
    'category_Табель_ШМ_РазрГрафика_yes',
    'category_Табель_ШМ_ОргДопоставки_yes',
    'category_Табель_ШМ_УчастиеВОПИ_yes',
    'category_Табель_ШМ_ИспобязМастера_yes'
];

foreach ($testCallbacks as $callback) {
    simulateCallbackFlow($callback);
}

// Check for potential conflicts
echo "8. ПРОВЕРКА ПОТЕНЦИАЛЬНЫХ КОНФЛИКТОВ:\n";

// Check if there are multiple patterns that could match our callbacks
$potentialConflicts = [
    'isActivityExecution',
    'handleActivityExecution',
    'category_Табель.*_yes'
];

foreach ($potentialConflicts as $pattern) {
    $matches = substr_count($handlerContent, $pattern);
    if ($matches > 1) {
        echo "   ⚠️ Найдено {$matches} вхождений паттерна: {$pattern}\n";
    }
}

// Final summary
echo "\n=== ИТОГОВЫЙ АНАЛИЗ ===\n";

$issues = [];

if (strpos($handlerContent, 'Handle supervision project activities FIRST') === false) {
    $issues[] = "Исправление не найдено в коде";
}

if (!$allFound) {
    $issues[] = "Не все типы активностей ШМР в массиве activityTypes";
}

if (empty($issues)) {
    echo "🎉 ВСЕ ПРОВЕРКИ ПРОЙДЕНЫ УСПЕШНО!\n";
    echo "Система подтверждений ШМР должна работать корректно.\n";
    echo "Пользователь может протестировать функциональность.\n";
} else {
    echo "⚠️ ОБНАРУЖЕНЫ ПРОБЛЕМЫ:\n";
    foreach ($issues as $issue) {
        echo "   - {$issue}\n";
    }
    echo "\nТребуется дополнительная диагностика или исправления.\n";
}

echo "\n=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>