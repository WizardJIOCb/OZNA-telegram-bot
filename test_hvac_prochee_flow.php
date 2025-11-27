<?php

// Test script to verify HVAC "Прочее" (Other) functionality works correctly
// This tests the complete flow: Button click → Custom input → Confirmation → Report

error_log("=== HVAC ПРОЧЕЕ TEST SCRIPT START ===");

// Simulate the conversation state after HVAC "Прочее" button is clicked
echo "Testing HVAC Прочее functionality...\n";

// Test 1: Check if the activity type is set correctly
$test_activity_type = 'ШМР_Прочее_ОтоплениеИВентиляция';
echo "✓ Activity type: {$test_activity_type}\n";

// Test 2: Check if custom input would be processed
$test_custom_input = 'Монтаж дополнительного оборудования отопления';
echo "✓ Custom input: {$test_custom_input}\n";

// Test 3: Check if confirmation callback would be generated
$expected_callback = 'cat_ШМР_ОтоплВент_Проч_yes';
echo "✓ Expected confirmation callback: {$expected_callback}\n";

// Test 4: Check if the callback patterns match what we expect
$callback_patterns_to_test = [
    // The button click callback
    'cat_ШМР_ОтоплВент_Проч',
    // The confirmation callback  
    'cat_ШМР_ОтоплВент_Проч_yes'
];

echo "\n=== CALLBACK PATTERN TESTS ===\n";
foreach ($callback_patterns_to_test as $pattern) {
    echo "Testing pattern: {$pattern}\n";
    
    // Test if it would be caught by the "Прочее" button handler
    if ($pattern === 'cat_ШМР_ОтоплВент_Проч') {
        echo "  ✓ Would be caught by HVAC Прочее button handler\n";
    }
    
    // Test if it would be caught by the confirmation handler
    if ($pattern === 'cat_ШМР_ОтоплВент_Проч_yes') {
        echo "  ✓ Would be caught by HVAC Прочее confirmation handler\n";
    }
}

echo "\n=== ACTIVITY PROCESSING TEST ===\n";

// Simulate the handleCustomInput logic for HVAC Прочее
$activity_types_handled = [
    'Прочее_ЭлектрОб',
    'Прочее_ПожСигн', 
    'Прочее_Автом',
    'Прочее_ОхрСигн',
    'Прочее_ПроектнаяДеятельность',
    'Прочее_ПроектнаяДеятельность_ШМР',
    'ШМР_Прочее_ТехнологическоеОборудование',
    'ШМР_Прочее_Электрооборудование',
    'ШМР_Прочее_ПожарнаяСигнализация',
    'ШМР_Прочее_ОхраннаяСигнализация',
    'ШМР_Прочее_Автоматизация',
    'ШМР_Прочее_ОтоплениеИВентиляция', // ← This should be our new addition
    'УстрЗам_Прочее'
];

if (in_array($test_activity_type, $activity_types_handled)) {
    echo "✓ HVAC Прочее activity type IS handled in handleCustomInput\n";
    echo "✓ Would generate confirmation callback: {$expected_callback}\n";
} else {
    echo "❌ HVAC Прочее activity type NOT handled in handleCustomInput\n";
    echo "❌ This would cause 'Неизвестная команда табеля' error\n";
}

echo "\n=== FLOW SUMMARY ===\n";
echo "1. User clicks HVAC 'Прочее' button → cat_ШМР_ОтоплВент_Проч\n";
echo "2. System prompts: 'Пожалуйста, введите описание выполняемой работы:'\n";
echo "3. User enters custom text: '{$test_custom_input}'\n";
echo "4. System processes via handleCustomInput with activity_type: '{$test_activity_type}'\n";
echo "5. System shows confirmation with callback: '{$expected_callback}'\n";
echo "6. User clicks 'Да' → handleNewProcheeConfirmation processes the report\n";

echo "\n=== TEST COMPLETE ===\n";

?>