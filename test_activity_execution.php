<?php

// Test the isActivityExecution method logic
function testIsActivityExecution($category) {
    $activityTypes = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
    ];
    
    // Check standard activity execution
    if (str_contains($category, '_yes')) {
        // Extract the activity type from the category
        $pattern = '/^Табель_(.+)_yes$/';
        if (preg_match($pattern, $category, $matches)) {
            $activityType = $matches[1];
            // Check if this activity type is in our list
            return in_array($activityType, $activityTypes);
        }
    }
    
    return false;
}

// Test the isActivityConfirmation method logic
function testIsActivityConfirmation($category) {
    $activityTypes = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
    ];
    
    // Check standard activity types
    foreach ($activityTypes as $activity) {
        if ($category === "Табель_{$activity}") {
            return true;
        }
    }
    
    return false;
}

// Test cases
$testCases = [
    'Табель_Сварочные_yes',
    'Табель_Дорога_yes',
    'Табель_Электромонтажные_yes',
    'Табель_Слесарные_yes',
    'Табель_Теплоизоляция_yes',
    'Табель_Документы_yes',
    'Табель_Сварочные',
    'Табель_Дорога',
    'Табель_Электромонтажные'
];

echo "Testing isActivityExecution:\n";
foreach ($testCases as $testCase) {
    $result = testIsActivityExecution($testCase) ? 'YES' : 'NO';
    echo "  $testCase: $result\n";
}

echo "\nTesting isActivityConfirmation:\n";
foreach ($testCases as $testCase) {
    // Only test the non-_yes versions for confirmation
    if (!str_contains($testCase, '_yes')) {
        $result = testIsActivityConfirmation($testCase) ? 'YES' : 'NO';
        echo "  $testCase: $result\n";
    }
}

?>