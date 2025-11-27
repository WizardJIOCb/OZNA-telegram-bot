<?php

// Debug milestone execution flow
echo "Debugging milestone confirmation execution...\n\n";

// Load the TabelHandler to check the actual isActivityExecution method
require_once 'CustomCommands/Handlers/TabelHandler.php';

// Test the exact callback that should be generated
$testCallback = 'cat_ШМР_ПодтВех_yes';
echo "Testing callback: '{$testCallback}'\n";

// Check what routeTabelCallback does - it removes 'cat_' prefix
$cleanCallback = str_replace('cat_', '', $testCallback);
echo "Clean callback (routeTabelCallback processing): '{$cleanCallback}'\n";

// Test the isActivityExecution pattern - from line ~1596 in TabelHandler.php
$pattern = '/^ШМР_(.+)_yes$/u';
echo "isActivityExecution pattern: '{$pattern}'\n";

if (preg_match($pattern, $cleanCallback, $matches)) {
    echo "✅ Pattern matches in isActivityExecution!\n";
    echo "Extracted code: '{$matches[1]}'\n";
    
    // Check the simpleSupervisionMap from the actual TabelHandler
    // This should be around line 1600-1610
    $simpleSupervisionMap = [
        'АналФунд' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    $extractedCode = $matches[1];
    if (isset($simpleSupervisionMap[$extractedCode])) {
        echo "✅ Found mapping: '{$extractedCode}' => '{$simpleSupervisionMap[$extractedCode]}'\n";
        echo "✅ Should call handleSupervisionActivityExecution with activityType: '{$simpleSupervisionMap[$extractedCode]}'\n";
    } else {
        echo "❌ No mapping found for: '{$extractedCode}'\n";
        echo "Available mappings:\n";
        foreach ($simpleSupervisionMap as $key => $value) {
            echo "  '{$key}' => '{$value}'\n";
        }
    }
} else {
    echo "❌ Pattern does NOT match!\n";
    echo "This means isActivityExecution will return false!\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Testing basic pattern matching only...\n";

$testActivityType = 'ПодтверждениеВехи_ШМР';
echo "Activity type to test: '{$testActivityType}'\n";

// Test if this would be in commissioning activities
$commissioningActivities = [
    'КомплексноеОпробование', 'РежимнаяНаладка', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
    'Инструктаж', 'Дорога', 'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование', 'Метрология',
    'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
    'ПодтверждениеВехи_ШМР', 'ПроверкаМонтажнойПлощадки', 'ПодписаниеАктаШМР'
];

if (in_array($testActivityType, $commissioningActivities)) {
    echo "✅ Activity is in commissioning activities array\n";
} else {
    echo "❌ Activity is NOT in commissioning activities array\n";
}

echo "\nDebugging complete!\n";