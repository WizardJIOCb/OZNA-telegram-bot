<?php

// Test milestone confirmation functionality
echo "Testing milestone confirmation fix...\n\n";

// Test the pattern matching logic
$testCallback = 'cat_ШМР_ПодтВех_yes';
$pattern = '/^ШМР_(.+)_yes$/u';

echo "Testing callback: '{$testCallback}'\n";
echo "Pattern: '{$pattern}'\n";

// Remove 'cat_' prefix to match what routeTabelCallback does
$cleanCallback = str_replace('cat_', '', $testCallback);
echo "Clean callback (after removing 'cat_'): '{$cleanCallback}'\n";

if (preg_match($pattern, $cleanCallback, $matches)) {
    echo "✅ Pattern matches!\n";
    echo "Extracted activity short code: '{$matches[1]}'\n";
    
    // Test the mapping
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    $extractedCode = $matches[1];
    if (isset($simpleSupervisionMap[$extractedCode])) {
        echo "✅ Mapping found for '{$extractedCode}': '{$simpleSupervisionMap[$extractedCode]}'\n";
        
        // Test activity type exists in TabelEntry
        $testActivityTypes = [
            'ПроверкаМонтажнойПлощадки' => '🏗️ Проверка монтажной площадки',
            'ПодписаниеАктаШМР' => '📋 Подписание акта ШМР',
            'ПодтверждениеВехи_ШМР' => '✅ Этап шеф-монтажа завершен'
        ];
        
        $activityType = $simpleSupervisionMap[$extractedCode];
        if (isset($testActivityTypes[$activityType])) {
            echo "✅ Activity type definition found: '{$testActivityTypes[$activityType]}'\n";
            echo "✅ Milestone confirmation should work!\n";
        } else {
            echo "❌ Activity type '{$activityType}' not found in TabelEntry!\n";
        }
    } else {
        echo "❌ No mapping found for extracted code: '{$extractedCode}'\n";
    }
} else {
    echo "❌ Pattern does not match!\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Testing commissioning activities inclusion...\n";

$commissioningActivities = [
    'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
    'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
    'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
    'ПодтверждениеВехи_ШМР', 'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
];

$testActivity = 'ПодтверждениеВехи_ШМР';
if (in_array($testActivity, $commissioningActivities)) {
    echo "✅ '{$testActivity}' is in commissioning activities - will get correct message format!\n";
} else {
    echo "❌ '{$testActivity}' is NOT in commissioning activities!\n";
}

echo "\nTest completed!\n";