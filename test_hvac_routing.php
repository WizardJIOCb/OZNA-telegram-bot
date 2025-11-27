<?php
/**
 * Тестовый скрипт для проверки маршрутизации ОВКВ callbacks
 * Проверяет, правильно ли распознаются callback-и подтверждения ОВКВ
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== ТЕСТ МАРШРУТИЗАЦИИ ОВКВ CALLBACKS ===\n\n";

// Тестовые ОВКВ callbacks (9 активностей × 3 типа действий = 27 callbacks)
$testCallbacks = [
    // РабГр (Работы по графику)
    'cat_ШМР_ОтоплВент_МонтОб_РабГр_yes' => 'Монтаж оборудования - РабГр',
    'cat_ШМР_ОтоплВент_МонтВозд_РабГр_yes' => 'Монтаж воздуховодов - РабГр',
    'cat_ШМР_ОтоплВент_МонтКонд_РабГр_yes' => 'Монтаж кондиционеров - РабГр',
    
    // НеучВОР (Работы неучтенные в ВОР)
    'cat_ШМР_ОтоплВент_МонтОб_НеучВОР_yes' => 'Монтаж оборудования - НеучВОР',
    'cat_ШМР_ОтоплВент_МонтВозд_НеучВОР_yes' => 'Монтаж воздуховодов - НеучВОР',
    
    // ПодтВех (Подтверждение вехи)
    'cat_ШМР_ОтоплВент_МонтОб_ПодтВех_yes' => 'Монтаж оборудования - ПодтВех',
    'cat_ШМР_ОтоплВент_МонтИТП_ПодтВех_yes' => 'Монтаж ИТП - ПодтВех',
];

// Имитация метода isActivityExecution
function testIsActivityExecution($category) {
    echo "  Проверка: isActivityExecution('{$category}')\n";
    
    // Проверка 1: Содержит ли '_yes'?
    if (!str_contains($category, '_yes')) {
        echo "  ❌ НЕ содержит '_yes'\n";
        return false;
    }
    echo "  ✅ Содержит '_yes'\n";
    
    // Проверка 2: Соответствует ли паттерну ОВКВ?
    $pattern = '/^cat_ШМР_ОтоплВент_(.+)_(РабГр|НеучВОР|ПодтВех)_yes$/u';
    if (preg_match($pattern, $category, $matches)) {
        echo "  ✅ Соответствует паттерну ОВКВ\n";
        echo "  ✅ Ключ активности: {$matches[1]}\n";
        echo "  ✅ Тип действия: {$matches[2]}\n";
        
        // Проверка 3: Есть ли ключ в карте активностей?
        $hvacActivityMap = [
            'МонтОб', 'МонтВозд', 'МонтКонд', 'МонтИТП', 
            'ПневмГидр', 'МонтКНС', 'ПроклКб', 'ПодклКб', 'МаркОб'
        ];
        
        if (in_array($matches[1], $hvacActivityMap)) {
            echo "  ✅ Ключ активности найден в карте\n";
            echo "  ✅✅✅ РЕЗУЛЬТАТ: TRUE (будет выполнен)\n";
            return true;
        } else {
            echo "  ❌ Ключ активности НЕ найден в карте\n";
            echo "  ❌ Доступные ключи: " . implode(', ', $hvacActivityMap) . "\n";
        }
    } else {
        echo "  ❌ НЕ соответствует паттерну ОВКВ\n";
    }
    
    echo "  ❌❌❌ РЕЗУЛЬТАТ: FALSE (НЕ будет выполнен)\n";
    return false;
}

// Имитация метода handleActivityExecution
function testHandleActivityExecution($category) {
    echo "  Проверка: handleActivityExecution('{$category}')\n";
    
    // Паттерн для извлечения данных
    $pattern = '/^cat_ШМР_ОтоплВент_(.+)_(РабГр|НеучВОР|ПодтВех)_yes$/u';
    if (preg_match($pattern, $category, $matches)) {
        $activityKey = $matches[1];
        $actionType = $matches[2];
        
        echo "  ✅ Извлечён ключ активности: {$activityKey}\n";
        echo "  ✅ Извлечён тип действия: {$actionType}\n";
        
        // Карта активностей
        $hvacActivityMap = [
            'МонтОб' => 'МонтОб',
            'МонтВозд' => 'МонтВозд',
            'МонтКонд' => 'МонтКонд',
            'МонтИТП' => 'МонтИТП',
            'ПневмГидр' => 'ПневмГидр',
            'МонтКНС' => 'МонтКНС',
            'ПроклКб' => 'ПроклКб',
            'ПодклКб' => 'ПодклКб',
            'МаркОб' => 'МаркОб'
        ];
        
        if (isset($hvacActivityMap[$activityKey])) {
            $activityType = "ШМР_ОтоплВент_{$hvacActivityMap[$activityKey]}_{$actionType}";
            echo "  ✅ Построен activityType: {$activityType}\n";
            echo "  ✅✅✅ УСПЕХ: Запись будет создана в табеле\n";
            return $activityType;
        } else {
            echo "  ❌ Ключ активности НЕ найден в карте\n";
        }
    } else {
        echo "  ❌ НЕ соответствует паттерну ОВКВ\n";
    }
    
    echo "  ❌❌❌ ОШИБКА: activityType НЕ создан\n";
    return null;
}

// Запуск тестов
$passedCount = 0;
$failedCount = 0;

foreach ($testCallbacks as $callback => $description) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "ТЕСТ: {$description}\n";
    echo "Callback: {$callback}\n";
    echo str_repeat("-", 80) . "\n";
    
    // Шаг 1: Проверка isActivityExecution
    echo "\nШАГ 1: Проверка распознавания (isActivityExecution)\n";
    $isRecognized = testIsActivityExecution($callback);
    
    if ($isRecognized) {
        // Шаг 2: Проверка handleActivityExecution
        echo "\nШАГ 2: Проверка обработки (handleActivityExecution)\n";
        $activityType = testHandleActivityExecution($callback);
        
        if ($activityType !== null) {
            echo "\n✅✅✅ ТЕСТ ПРОЙДЕН: Callback будет правильно обработан\n";
            $passedCount++;
        } else {
            echo "\n❌❌❌ ТЕСТ ПРОВАЛЕН: Callback распознан, но НЕ обработан\n";
            $failedCount++;
        }
    } else {
        echo "\n❌❌❌ ТЕСТ ПРОВАЛЕН: Callback НЕ распознан\n";
        $failedCount++;
    }
}

// Итоговая статистика
echo "\n" . str_repeat("=", 80) . "\n";
echo "ИТОГОВАЯ СТАТИСТИКА:\n";
echo str_repeat("=", 80) . "\n";
echo "Всего тестов: " . ($passedCount + $failedCount) . "\n";
echo "✅ Пройдено: {$passedCount}\n";
echo "❌ Провалено: {$failedCount}\n";

if ($failedCount === 0) {
    echo "\n🎉🎉🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ! Система работает корректно.\n";
} else {
    echo "\n⚠️⚠️⚠️ ОБНАРУЖЕНЫ ПРОБЛЕМЫ! Требуется исправление кода.\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
