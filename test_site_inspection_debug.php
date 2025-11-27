<?php
/**
 * Test to verify why the site inspection button shows "Неизвестная команда табеля"
 */

echo "=== Site Inspection Debug Test ===\n\n";

// Test the new callback pattern
$testCallback = 'cat_ШМР_ПровМонт_yes';
echo "Testing callback: {$testCallback}\n";

// Extract category (as done in TabelHandler)
$category = str_replace('cat_', '', $testCallback);
echo "Extracted category: {$category}\n";

// Test isActivityExecution pattern
if (preg_match('/^ШМР_(.+)_yes$/u', $category, $matches)) {
    echo "✅ Pattern matches simple supervision format\n";
    $shortActivityKey = $matches[1];
    echo "Extracted key: {$shortActivityKey}\n";
    
    // Test mapping
    $simpleSupervisionMap = [
        'ПровМонт' => 'ПроверкаМонтажнойПлощадки',
        'ПодпАкт' => 'ПодписаниеАктаШМР',
        'ПодтВех' => 'ПодтверждениеВехи_ШМР'
    ];
    
    if (isset($simpleSupervisionMap[$shortActivityKey])) {
        $activityType = $simpleSupervisionMap[$shortActivityKey];
        echo "✅ Mapping found: {$shortActivityKey} → {$activityType}\n";
        
        // Test if activity type is in activityTypes array
        $activityTypes = [
            'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
            'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
            'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
            'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
            'МедОсмотр', 'Аттестация', 'Общая',
            'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет',
            'Мобильная бригада',
            // ... electrical equipment types ...
            'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб',
            'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'Прочее_ЭлектрОб',
            'Электрооборудование',
            // ... other activity types ...
            'УстрЗам_ЭС', 'УстрЗам_АТХ', 'УстрЗам_ОВКВ', 'УстрЗам_ПСОС', 'УстрЗам_ТХ', 'УстрЗам_Прочее',
            'РабГарантии_ЭлектрОб', 'РабГарантии_СрИзм', 'РабГарантии_НасОб',
            'РабГарантии_ТрубАрм', 'РабГарантии_ИспМех', 'РабГарантии_КонтрОб', 'РабГарантии_Прочее',
            'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета',
            'ШМР_ВходнойКонтроль_РабПоГр', 'ШМР_ВходнойКонтроль_ПодтВех',
            'ШМР_ЗавозОборудования_РабПоГр', 'ШМР_ЗавозОборудования_ПодтВех',
            'ШМР_ПроверкаМонтажнойПлощадки',
            'ПроверкаМонтажнойПлощадки',  // THIS IS THE KEY LINE!
            'ПодписаниеАктаШМР',
            'ПусконаладочныеРаботы'
        ];
        
        if (in_array($activityType, $activityTypes)) {
            echo "✅ Activity type '{$activityType}' found in activityTypes array\n";
            echo "✅ isActivityExecution should return TRUE\n";
            echo "✅ Button should work correctly!\n";
        } else {
            echo "❌ Activity type '{$activityType}' NOT found in activityTypes array\n";
            echo "❌ This causes 'Неизвестная команда табеля'\n";
            echo "❌ SOLUTION: Add '{$activityType}' to activityTypes array\n";
        }
    } else {
        echo "❌ No mapping found for key: {$shortActivityKey}\n";
    }
} else {
    echo "❌ Pattern does not match\n";
}

echo "\n=== Analysis ===\n";
echo "The issue is that after removing АналФунд and replacing with ПровМонт,\n";
echo "the system properly maps ПровМонт → ПроверкаМонтажнойПлощадки\n";
echo "but ПроверкаМонтажнойПлощадки must be in the activityTypes array\n";
echo "for isActivityExecution to return true.\n\n";

echo "If the test shows the activity type is in the array but still fails,\n";
echo "then the issue is in the validation logic in isActivityExecution method.\n";

echo "\n=== Test Complete ===\n";
?>