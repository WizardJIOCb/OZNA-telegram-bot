<?php
/**
 * Test the exact callback pattern matching
 */

echo "🧪 TESTING CALLBACK PATTERN MATCHING\n";
echo "===================================\n\n";

$testCallback = 'cat_ШМР_СдачаОтч_yes';
echo "Testing callback: '{$testCallback}'\n\n";

// Test the pattern from handleActivityExecution
$pattern = '/^cat_ШМР_(.+)_yes$/u';
echo "Pattern: {$pattern}\n";

if (preg_match($pattern, $testCallback, $matches)) {
    echo "✅ PATTERN MATCHES!\n";
    echo "Full match: '{$matches[0]}'\n";
    echo "Captured group: '{$matches[1]}'\n";
    
    $shortToFullMap = [
        'РабПоГр' => 'ШМ_РабПоГрафику',
        'ПодгПоезд' => 'ШМ_ПодготовкаКПоездке',
        'Дорога' => 'ШМ_Дорога',
        'Инстр' => 'ШМ_Инструктаж',
        'СдачаОтч' => 'ШМ_СдачаАвансовогоОтчета'
    ];
    
    $shortActivityKey = $matches[1];
    echo "\nLooking up '{$shortActivityKey}' in mapping...\n";
    
    if (isset($shortToFullMap[$shortActivityKey])) {
        $fullActivityType = $shortToFullMap[$shortActivityKey];
        echo "✅ MAPPING FOUND: '{$shortActivityKey}' → '{$fullActivityType}'\n";
        
        echo "\nThis should result in automation format with:\n";
        echo "- Activity text: '💰 Сдача авансового отчета'\n";
        echo "- Format: '✅27.09.2025, Проект ОИ837: Добавлена запись в табель: 💰 Сдача авансового отчета'\n";
    } else {
        echo "❌ MAPPING NOT FOUND for '{$shortActivityKey}'\n";
        echo "Available mappings:\n";
        foreach ($shortToFullMap as $key => $value) {
            echo "  '{$key}' → '{$value}'\n";
        }
    }
} else {
    echo "❌ PATTERN DOES NOT MATCH\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "CONCLUSION:\n";
echo "The pattern and mapping should work correctly.\n";
echo "If the user is still getting the old format, the issue might be:\n";
echo "1. The callback is not reaching handleActivityExecution\n";
echo "2. Another pattern is matching first\n";
echo "3. The activityType is being overwritten later\n";
echo "4. There's an error in the execution flow\n";
?>