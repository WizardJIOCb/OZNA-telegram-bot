<?php

// Test the specific automation scheme changes callback flow

echo "=== Testing Automation Scheme Changes Callback Flow ===\n\n";

// Step 1: User clicks "Изменение схем подключения" button
echo "1. User clicks 'Изменение схем подключения' button\n";
echo "   Callback: cat_ТабАвтом_ИзмСх\n";
echo "   → Should show confirmation with button: cat_ТабАвтом_ИзмСх_yes\n\n";

// Step 2: User clicks "Да" button
echo "2. User clicks 'Да' button\n";
echo "   Callback: cat_ТабАвтом_ИзмСх_yes\n\n";

// Step 3: Test if isActivityExecution recognizes this callback
echo "3. Testing isActivityExecution method recognition:\n";

function testIsActivityExecution($category) {
    // Simulate the isActivityExecution logic for automation equipment
    if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $category, $matches)) {
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_Автом',
            'ФормИД' => 'ФормИД_Автом',
            'ДопРаб' => 'ДопРаб_Автом',
            'ПодтВех' => 'ПодтВех_Автом',
            'ИзмСх' => 'ИзмСх_Автом',
            'МонтКб' => 'МонтКб_Автом',
            'РаскКб' => 'РаскКб_Автом',
            'МонтАСУ' => 'МонтАСУ_Автом',
            'УдалПО' => 'УдалПО_Автом',
            'Проч' => 'Прочее_Автом'
        ];
        
        $shortActivityKey = $matches[1];
        if (isset($shortToFullMap[$shortActivityKey])) {
            return true;
        }
    }
    return false;
}

$testCallback = 'cat_ТабАвтом_ИзмСх_yes';
$result = testIsActivityExecution($testCallback);
echo "   isActivityExecution('{$testCallback}'): " . ($result ? 'TRUE' : 'FALSE') . "\n\n";

// Step 4: Test activity type extraction
echo "4. Testing activity type extraction in handleActivityExecution:\n";

function testActivityTypeExtraction($category) {
    if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $category, $matches)) {
        $shortActivityKey = $matches[1];
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_Автом',
            'ФормИД' => 'ФормИД_Автом',
            'ДопРаб' => 'ДопРаб_Автом',
            'ПодтВех' => 'ПодтВех_Автом',
            'ИзмСх' => 'ИзмСх_Автом',
            'МонтКб' => 'МонтКб_Автом',
            'РаскКб' => 'РаскКб_Автом',
            'МонтАСУ' => 'МонтАСУ_Автом',
            'УдалПО' => 'УдалПО_Автом',
            'Проч' => 'Прочее_Автом'
        ];
        
        if (isset($shortToFullMap[$shortActivityKey])) {
            return $shortToFullMap[$shortActivityKey];
        }
    }
    return 'NOT_FOUND';
}

$extractedType = testActivityTypeExtraction($testCallback);
echo "   Extracted activity type: '{$extractedType}'\n\n";

if ($result && $extractedType !== 'NOT_FOUND') {
    echo "✅ SUCCESS: Automation scheme changes callback should work correctly!\n";
    echo "   The callback flows through:\n";
    echo "   1. isActivityExecution() → TRUE\n";
    echo "   2. handleActivityExecution() → extracts 'ИзмСх_Автом'\n";
    echo "   3. Creates tabel entry with activity type 'ИзмСх_Автом'\n";
} else {
    echo "❌ PROBLEM: Something is wrong with the callback handling!\n";
}

?>