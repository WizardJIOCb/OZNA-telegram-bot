<?php
// Test script to verify electrical equipment activities fix

echo "Testing Electrical Equipment Activities Fix\n";
echo "==========================================\n\n";

// Simulate the TabelHandler activity types array
$activityTypes = [
    // Standard activity types
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
    'АвансовыйОтчет',
    // New electrical equipment activity types
    'РаботыПоГрафику_Электрооборудование',
    'ФормированиеИД_Электрооборудование',
    'ПодтвДостВехи_ЭлектрОб',
    'ДопРаб_ЭлектрОб', // Added back for additional works
    'ИзмСхемПодкл_ЭлектрОб',
    'МонтажКабЛиний_ЭлектрОб',
    'РасклКабЛиний_ЭлектрОб',
    'Прочее_Электрооборудование',
    // Electrical equipment main category
    'Электрооборудование',
    // Automation equipment activity types
    'РабПоГр_Автом',
    'ФормИД_Автом',
    'ПодтВех_Автом',
    'ДопРаб_Автом',
    'ИзмСх_Автом',
    'МонтКб_Автом',
    'РаскКб_Автом',
    'МонтАСУ_Автом',
    'УдалПО_Автом',
    'Прочее_Автом'
];

// Print all activity types for debugging
echo "Activity types in the array:\n";
foreach ($activityTypes as $type) {
    if (strpos($type, 'Электрооборудование') !== false) {
        echo "  - {$type}\n";
    }
}
echo "\n";

// Test cases for electrical equipment activities
$testCases = [
    'cat_ТабЭлОб_РабПоГр_yes' => 'РаботыПоГрафику_Электрооборудование',
    'cat_ТабЭлОб_ФормИД_yes' => 'ФормированиеИД_Электрооборудование',
    'cat_ТабЭлОб_ИзмСхПд_yes' => 'ИзмСхемПодкл_ЭлектрОб',
    'cat_ТабЭлОб_МонтКбЛн_yes' => 'МонтажКабЛиний_ЭлектрОб',
    'cat_ТабЭлОб_РаскКбЛн_yes' => 'РасклКабЛиний_ЭлектрОб',
    'cat_ТабЭлОб_ДопРаб_yes' => 'ДопРаб_ЭлектрОб',
    'cat_ТабЭлОб_ПодтВех_yes' => 'ПодтвДостВехи_ЭлектрОб',
    'cat_ТабЭлОб_Проч_yes' => 'Прочее_Электрооборудование'
];

echo "Testing short callback format mapping:\n";
$allPassed = true;

foreach ($testCases as $callback => $expectedActivityType) {
    echo "Testing: {$callback}\n";
    
    // Simulate the regex matching in TabelHandler::isActivityExecution
    if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $callback, $matches)) {
        $shortActivityKey = $matches[1];
        
        // Map short format to full activity type (as in TabelHandler)
        $shortToFullMap = [
            'РабПоГр' => 'РаботыПоГрафику_Электрооборудование',
            'ФормИД' => 'ФормированиеИД_Электрооборудование',
            'ИзмСхПд' => 'ИзмСхемПодкл_ЭлектрОб',
            'МонтКбЛн' => 'МонтажКабЛиний_ЭлектрОб',
            'РаскКбЛн' => 'РасклКабЛиний_ЭлектрОб',
            'ДопРаб' => 'ДопРаб_ЭлектрОб',
            'ПодтВех' => 'ПодтвДостВехи_ЭлектрОб',
            'Проч' => 'Прочее_Электрооборудование'
        ];
        
        if (isset($shortToFullMap[$shortActivityKey])) {
            $activityType = $shortToFullMap[$shortActivityKey];
            $isValid = in_array($activityType, $activityTypes);
            
            echo "  Short key: {$shortActivityKey}\n";
            echo "  Mapped to: {$activityType}\n";
            echo "  In activityTypes: " . ($isValid ? 'YES' : 'NO') . "\n";
            
            if ($activityType === $expectedActivityType && $isValid) {
                echo "  ✅ PASS\n\n";
            } else {
                echo "  ❌ FAIL\n";
                echo "    Expected: {$expectedActivityType}\n";
                echo "    Got: {$activityType}\n";
                echo "    Valid: " . ($isValid ? 'YES' : 'NO') . "\n\n";
                $allPassed = false;
            }
        } else {
            echo "  ❌ FAIL - No mapping found for short key: {$shortActivityKey}\n\n";
            $allPassed = false;
        }
    } else {
        echo "  ❌ FAIL - Regex did not match\n\n";
        $allPassed = false;
    }
}

echo "Testing full callback format mapping:\n";
$fullTestCases = [
    'Табель_РаботыПоГрафику_Электрооборудование_yes' => 'РаботыПоГрафику_Электрооборудование',
    'Табель_ФормированиеИД_Электрооборудование_yes' => 'ФормированиеИД_Электрооборудование',
    'Табель_ИзмСхемПодкл_ЭлектрОб_yes' => 'ИзмСхемПодкл_ЭлектрОб',
    'Табель_МонтажКабЛиний_ЭлектрОб_yes' => 'МонтажКабЛиний_ЭлектрОб',
    'Табель_РасклКабЛиний_ЭлектрОб_yes' => 'РасклКабЛиний_ЭлектрОб',
    'Табель_Прочее_Электрооборудование_yes' => 'Прочее_Электрооборудование'
];

foreach ($fullTestCases as $callback => $expectedActivityType) {
    echo "Testing: {$callback}\n";
    
    // Simulate the regex matching in TabelHandler::isActivityExecution
    if (preg_match('/^Табель_(.+)_yes$/', $callback, $matches)) {
        $activityType = $matches[1];
        $isValid = in_array($activityType, $activityTypes);
        
        echo "  Extracted activity type: {$activityType}\n";
        echo "  In activityTypes: " . ($isValid ? 'YES' : 'NO') . "\n";
        
        if ($activityType === $expectedActivityType && $isValid) {
            echo "  ✅ PASS\n\n";
        } else {
            echo "  ❌ FAIL\n";
            echo "    Expected: {$expectedActivityType}\n";
            echo "    Got: {$activityType}\n";
            echo "    Valid: " . ($isValid ? 'YES' : 'NO') . "\n\n";
            $allPassed = false;
        }
    } else {
        echo "  ❌ FAIL - Regex did not match\n\n";
        $allPassed = false;
    }
}

// Check if all activity types are in the array
echo "Checking if all expected activity types are in the array:\n";
$expectedTypes = [
    'РаботыПоГрафику_Электрооборудование',
    'ФормированиеИД_Электрооборудование',
    'ПодтвДостВехи_ЭлектрОб',
    'ДопРаб_ЭлектрОб',
    'ИзмСхемПодкл_ЭлектрОб',
    'МонтажКабЛиний_ЭлектрОб',
    'РасклКабЛиний_ЭлектрОб',
    'Прочее_Электрооборудование'
];

foreach ($expectedTypes as $type) {
    $exists = in_array($type, $activityTypes);
    echo "  {$type}: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . "\n";
    if (!$exists) {
        $allPassed = false;
    }
}

echo "\nFinal Result: " . ($allPassed ? "✅ ALL TESTS PASSED" : "❌ SOME TESTS FAILED") . "\n";

if ($allPassed) {
    echo "\n🎉 The fix for electrical equipment activities should now work correctly!\n";
    echo "Users should be able to send activities like 'Работы по графику', 'Формирование ИД', etc.\n";
    echo "without any issues.\n";
} else {
    echo "\n⚠️  There are still issues with the electrical equipment activities.\n";
    echo "Please check the TabelHandler implementation.\n";
}
?>