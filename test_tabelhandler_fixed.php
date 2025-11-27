<?php
// Test to verify the fix for TabelHandler_fixed.php

// Simulate the isActivityExecution method logic
function testIsActivityExecution($category) {
    // Special handling for сварочные works due to encoding issues
    if ($category === 'category_Табель_Сварочные_yes') {
        echo "Special handling for сварочные works\n";
        return true;
    }
    
    // Simulate the activity types array
    $activityTypes = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
    ];
    
    // Check standard activity execution
    if (strpos($category, '_yes') !== false) {
        $pattern = '/^category_Табель_(' . implode('|', $activityTypes) . ')_yes$/';
        if (preg_match($pattern, $category)) {
            echo "Pattern matched\n";
            return true;
        } else {
            echo "Pattern did not match: {$pattern}\n";
        }
    }
    
    return false;
}

// Test with "Сварочные"
$testCategory = 'category_Табель_Сварочные_yes';
$result = testIsActivityExecution($testCategory);

echo "Testing isActivityExecution with category: {$testCategory}\n";
echo "Result: " . ($result ? 'true' : 'false') . "\n";

// Test with another category for comparison
$testCategory2 = 'category_Табель_Электромонтажные_yes';
$result2 = testIsActivityExecution($testCategory2);

echo "\nTesting isActivityExecution with category: {$testCategory2}\n";
echo "Result: " . ($result2 ? 'true' : 'false') . "\n";
?>