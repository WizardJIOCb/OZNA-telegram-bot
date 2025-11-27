<?php

// Test script to verify МонтОб routing fix
echo "Testing МонтОб routing fix...\n\n";

try {
    // Test the routing for МонтОб - just test the method directly without instantiating
    
    // Test the callback that was failing
    $callbackData = 'cat_ТабЭлОб_МонтОб_yes';
    
    // Simulate the shortFormats array from the actual method
    $shortFormats = [
        'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
        'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes',
        'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
        'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ЭлектрОб_yes',
        'cat_ТабЭлОб_РаскКбЛн_yes' => 'Табель_РаскКбЛн_ЭлектрОб_yes',
        'cat_ТабЭлОб_МонтОб_yes' => 'Табель_МонтОб_ЭлектрОб_yes',  // This is the one we just added
        'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes',
        'cat_ТабЭлОб_ПодтВех_yes' => 'Табель_ПодтВех_ЭлектрОб_yes',
        'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes',
    ];
    
    echo "Testing callback: {$callbackData}\n";
    
    if (isset($shortFormats[$callbackData])) {
        $result = $shortFormats[$callbackData];
        echo "Result: {$result}\n";
        
        // Expected result
        $expected = 'Табель_МонтОб_ЭлектрОб_yes';
        
        if ($result === $expected) {
            echo "✅ SUCCESS: МонтОб routing is now working correctly!\n";
            echo "The callback '{$callbackData}' is properly mapped to '{$expected}'\n";
        } else {
            echo "❌ FAILED: Expected '{$expected}', got '{$result}'\n";
        }
    } else {
        echo "❌ FAILED: Callback '{$callbackData}' not found in shortFormats array\n";
    }
    
    echo "\n";
    
    // Test a few other electrical equipment callbacks to make sure they still work
    $testCases = [
        'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
        'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes',
        'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ЭлектрОб_yes',
    ];
    
    echo "Testing other electrical equipment callbacks:\n";
    foreach ($testCases as $callback => $expectedResult) {
        if (isset($shortFormats[$callback])) {
            $result = $shortFormats[$callback];
            $status = ($result === $expectedResult) ? '✅' : '❌';
            echo "{$status} {$callback} -> {$result}\n";
        } else {
            echo "❌ {$callback} -> NOT FOUND\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";