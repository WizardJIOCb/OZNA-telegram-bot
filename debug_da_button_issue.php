<?php

echo "=== ДИАГНОСТИКА ПРОБЛЕМЫ С КНОПКОЙ 'ДА' ===\n";

// Simulate the issue by testing pattern matching
$testCallbacks = [
    'cat_ТабЭлОб_РабПоГр_yes',
    'cat_ТабПожСигн_РабПоГр_yes', 
    'cat_ТабАвтом_РабПоГр_yes',
    'cat_ТабВентОтопл_РабПоГр_yes',
    'cat_ТабТехОб_РабПоГр_yes',
    'cat_ТабМетрол_РабПоГр_yes',
    'cat_Гарантии_ЭлектрОб_yes',
    'cat_ШМР_РабПоГр_yes'
];

echo "\n1. Тестируем какие callback'и распознаются как Activity Execution:\n";

foreach ($testCallbacks as $callback) {
    echo "\nТестируем: '$callback'\n";
    
    if (str_contains($callback, '_yes')) {
        echo "  ✅ Содержит '_yes'\n";
        
        // Test electrical equipment pattern
        if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $callback, $matches)) {
            echo "  ✅ Совпадает с электрооборудованием: {$matches[1]}\n";
            continue;
        }
        
        // Test fire alarm equipment pattern  
        if (preg_match('/^cat_ТабПожСигн_(.+)_yes$/u', $callback, $matches)) {
            echo "  ✅ Совпадает с пожарной сигнализацией: {$matches[1]}\n";
            continue;
        }
        
        // Test automation equipment pattern
        if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $callback, $matches)) {
            echo "  ✅ Совпадает с автоматизацией: {$matches[1]}\n";
            continue;
        }
        
        // Test heating and ventilation equipment pattern
        if (preg_match('/^cat_ТабВентОтопл_(.+)_yes$/u', $callback, $matches)) {
            echo "  ✅ Совпадает с вентиляцией и отоплением: {$matches[1]}\n";
            continue;
        }
        
        // Test technological equipment pattern
        if (preg_match('/^cat_ТабТехОб_(.+)_yes$/u', $callback, $matches)) {
            echo "  ✅ Совпадает с технологическим оборудованием: {$matches[1]}\n";
            continue;
        }
        
        // Test metrology equipment pattern
        if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $callback, $matches)) {
            echo "  ✅ Совпадает с метрологией: {$matches[1]}\n";
            continue;
        }
        
        // Test warranty equipment pattern
        if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $callback, $matches)) {
            echo "  ✅ Совпадает с гарантийным оборудованием: {$matches[1]}\n";
            continue;
        }
        
        // Test supervision installation works pattern
        if (preg_match('/^cat_ШМР_(.+)_yes$/u', $callback, $matches)) {
            echo "  ✅ Совпадает с шеф-монтажными работами: {$matches[1]}\n";
            continue;
        }
        
        echo "  ❌ НЕ СОВПАДАЕТ ни с одним паттерном!\n";
    } else {
        echo "  ❌ НЕ содержит '_yes'\n";
    }
}

echo "\n2. Проверяем изменения в отчетах TabelEntry.php:\n";

// Check if report format changes affected callback recognition
$reportFile = __DIR__ . '/CustomCommands/Services/TabelEntry.php';
if (file_exists($reportFile)) {
    $content = file_get_contents($reportFile);
    
    // Check if supervision installation works activities are present  
    $supervisionActivities = [
        'ШМ_РабПоГрафику',
        'ШМ_ПодготовкаКПоездке', 
        'ШМ_Дорога',
        'ШМ_Инструктаж',
        'ШМ_СдачаАвансовогоОтчета'
    ];
    
    echo "Проверяем наличие supervision installation works в activityTypeMappings:\n";
    foreach ($supervisionActivities as $activity) {
        if (strpos($content, "'$activity'") !== false) {
            echo "  ✅ Найден: $activity\n";
        } else {
            echo "  ❌ НЕ найден: $activity\n";
        }
    }
} else {
    echo "❌ Файл TabelEntry.php не найден!\n";
}

echo "\n3. ЗАКЛЮЧЕНИЕ:\n";
echo "Если все callback'и распознаются правильно, но кнопки 'Да' не работают,\n";
echo "то проблема скорее всего в:\n";
echo "- Неправильной маршрутизации в routeTabelCallback\n";
echo "- Проблемах с handleActivityExecution\n";
echo "- Проблемах с созданием отчетов в TabelEntry\n";

?>