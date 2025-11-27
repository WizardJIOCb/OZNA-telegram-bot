<?php
// Debug script to trace the exact issue with the "Работы по графику" button

echo "=== Debugging Schedule Button Issue ===\n\n";

// Simulate the callback data that would be received
$callbackData = 'cat_ТабЭлОб_РабПоГр';
echo "1. Received callbackData: '{$callbackData}'\n";

// Test the mapping in extractCategoryFromCallback
$shortFormats = [
    'cat_ТабЭлОб' => 'Табель_Электрооборудование',
    'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
    'cat_ТабЭлОб_РабПоГр' => 'Табель_РаботыПоГрафику_Электрооборудование',
    'cat_ТабЭлОб_ФормИД' => 'Табель_ФормированиеИД_Электрооборудование',
    'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзменениеСхемПодключения_Электрооборудование',
    'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтажКабельныхЛиний_Электрооборудование',
    'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РасключениеКабельныхЛиний_Электрооборудование',
    'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
    'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтвердитьДостижениеВехи_Электрооборудование',
    'cat_ТабЭлОб_Проч' => 'Табель_Прочее_Электрооборудование'
];

echo "2. Checking if callbackData exists in shortFormats...\n";
if (isset($shortFormats[$callbackData])) {
    $mappedCategory = $shortFormats[$callbackData];
    echo "   SUCCESS: Found mapping - '{$callbackData}' -> '{$mappedCategory}'\n";
    
    // Test the specific condition that should handle this category
    $isScheduleWorks = ($mappedCategory === 'Табель_РаботыПоГрафику_Электрооборудование');
    echo "3. Is this the schedule works category? " . ($isScheduleWorks ? 'YES' : 'NO') . "\n";
    
    if ($isScheduleWorks) {
        echo "4. This should be handled by the first condition in handleOtherWorkTypes\n";
        echo "   Condition: if (\$category === 'Табель_РаботыПоГрафику_Электрооборудование')\n";
        echo "   Since mappedCategory is '{$mappedCategory}', this condition SHOULD match.\n";
        
        // Test what would happen in handleOtherWorkTypes
        $category = $mappedCategory;
        echo "5. Simulating handleOtherWorkTypes with category: '{$category}'\n";
        
        // Check the first condition
        if ($category === 'Табель_РаботыПоГрафику_Электрооборудование') {
            echo "   ✅ FIRST CONDITION MATCHES!\n";
            echo "   This should directly execute the activity without confirmation.\n";
            
            // Show what would happen next
            $strippedCategory = 'РаботыПоГрафику_Электрооборудование';
            echo "   Stripped category would be: '{$strippedCategory}'\n";
            
            // Check if this is in activityTypes
            $activityTypes = [
                'РаботыПоГрафику_Электрооборудование', // This should be present
                // ... other activity types
            ];
            
            if (in_array($strippedCategory, $activityTypes)) {
                echo "   ✅ Activity type found in activityTypes array\n";
                echo "   ✅ Should create callback: 'category_Табель_{$strippedCategory}_yes'\n";
                echo "   ✅ Should call handleActivityExecution with this callback\n";
            } else {
                echo "   ❌ Activity type NOT found in activityTypes array\n";
            }
        } else {
            echo "   ❌ FIRST CONDITION DOES NOT MATCH!\n";
            echo "   This is the bug - the condition is not working as expected.\n";
        }
    } else {
        echo "   This is not the schedule works category.\n";
    }
} else {
    echo "   ERROR: CallbackData '{$callbackData}' not found in shortFormats mapping\n";
}

echo "\n=== Debug Complete ===\n";

// Let's also test the exact string comparison that might be failing
echo "\n=== String Comparison Test ===\n";
$expected = 'Табель_РаботыПоГрафику_Электрооборудование';
$actual = 'Табель_РаботыПоГрафику_Электрооборудование';

echo "Expected: '{$expected}'\n";
echo "Actual:   '{$actual}'\n";
echo "Length of expected: " . strlen($expected) . "\n";
echo "Length of actual:   " . strlen($actual) . "\n";
echo "Are they equal? " . ($expected === $actual ? 'YES' : 'NO') . "\n";

// Let's also check for hidden characters
echo "Expected hex: " . bin2hex($expected) . "\n";
echo "Actual hex:   " . bin2hex($actual) . "\n";
?>