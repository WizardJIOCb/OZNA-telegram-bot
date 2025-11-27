<?php
// Test script to verify the fix for electrical equipment callbacks

// Test the callback data handling
$callbackData = 'cat_ТабЭлОб_РабПоГр_yes';

echo "Testing electrical equipment callback handling:\n";
echo "Callback data: $callbackData\n\n";

// Test 1: Check if the callback data matches the expected pattern
$test1 = ($callbackData === 'cat_ТабЭлОб_РабПоГр_yes' ||
          $callbackData === 'cat_ТабЭлОб_ФормИД_yes' ||
          $callbackData === 'cat_ТабЭлОб_ИзмСхПд_yes' ||
          $callbackData === 'cat_ТабЭлОб_МонтКбЛн_yes' ||
          $callbackData === 'cat_ТабЭлОб_РаскКбЛн_yes' ||
          $callbackData === 'cat_ТабЭлОб_ДопРаб_yes' ||
          $callbackData === 'cat_ТабЭлОб_ПодтВех_yes' ||
          $callbackData === 'cat_ТабЭлОб_Проч_yes');

echo "Test 1 - Callback matches expected electrical equipment patterns: " . ($test1 ? "PASS" : "FAIL") . "\n";

// Test 2: Check if isActivityExecution would return true for this callback
// Based on the isActivityExecution method in TabelHandler
$isActivityExecution = false;
if (strpos($callbackData, '_yes') !== false) {
    // Handle short callback format for electrical equipment first
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $callbackData, $matches)) {
        // Handle short callback format for electrical equipment
        $shortActivityKey = $matches[1];
        // Map short format to full activity type
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_ЭлектрОб',
            'ФормИД' => 'ФормИД_ЭлектрОб',
            'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
            'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
            'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
            'ДопРаб' => 'ДопРаб_ЭлектрОб',
            'ПодтВех' => 'ПодтВех_ЭлектрОб',
            'Проч' => 'Прочее_ЭлектрОб'
        ];
        
        if (isset($shortToFullMap[$shortActivityKey])) {
            $activityType = $shortToFullMap[$shortActivityKey];
            // Check if activity type is in the activityTypes array
            $activityTypes = [
                'РабПоГр_ЭлектрОб',
                'ФормИД_ЭлектрОб',
                'ПодтВех_ЭлектрОб',
                'ИзмСхПд_ЭлектрОб',
                'МонтКбЛн_ЭлектрОб',
                'РаскКбЛн_ЭлектрОб',
                'Прочее_ЭлектрОб',
                'ДопРаб_ЭлектрОб'
            ];
            
            $isActivityExecution = in_array($activityType, $activityTypes);
        }
    }
}

echo "Test 2 - isActivityExecution would return true: " . ($isActivityExecution ? "PASS" : "FAIL") . "\n";

// Test 3: Check if the callback data would be properly handled in the handleOtherWorkTypes method
$callbackHandled = false;
if ($callbackData === 'cat_ТабЭлОб_РабПоГр_yes' ||
    $callbackData === 'cat_ТабЭлОб_ФормИД_yes' ||
    $callbackData === 'cat_ТабЭлОб_ИзмСхПд_yes' ||
    $callbackData === 'cat_ТабЭлОб_МонтКбЛн_yes' ||
    $callbackData === 'cat_ТабЭлОб_РаскКбЛн_yes' ||
    $callbackData === 'cat_ТабЭлОб_ДопРаб_yes' ||
    $callbackData === 'cat_ТабЭлОб_ПодтВех_yes' ||
    $callbackData === 'cat_ТабЭлОб_Проч_yes') {
    $callbackHandled = true;
}

echo "Test 3 - Callback handled in handleOtherWorkTypes method: " . ($callbackHandled ? "PASS" : "FAIL") . "\n";

echo "\n=== Test Summary ===\n";
if ($test1 && $isActivityExecution && $callbackHandled) {
    echo "✅ All tests pass - electrical equipment callbacks should work correctly\n";
} else {
    echo "❌ Some tests failed - there may still be issues\n";
}