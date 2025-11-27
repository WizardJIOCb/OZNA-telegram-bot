<?php

// Test script to verify activity confirmation patterns

$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
];

$testCategories = [
    'category_Табель_Теплоизоляция',
    'category_Табель_Дорога',
    'category_Табель_Простой',
    'category_Табель_ШМ_Простой',
    'category_Табель_НесуществующийТип',
];

echo "Testing activity confirmation patterns:\n";
foreach ($testCategories as $category) {
    echo "  Testing: {$category}\n";
    
    // Check standard activity types
    $isConfirmation = false;
    foreach ($activityTypes as $activity) {
        if ($category === "category_Табель_{$activity}") {
            $isConfirmation = true;
            echo "    ✅ Matches activity type: {$activity}\n";
            break;
        }
    }
    
    // Check idle menu final confirmations
    if (!$isConfirmation && str_contains($category, '_yes') && 
        (str_contains($category, 'Табель_Простой') || str_contains($category, 'Табель_ШМ_Простой'))) {
        $isConfirmation = true;
        echo "    ✅ Matches idle menu confirmation\n";
    }
    
    if (!$isConfirmation) {
        echo "    ❌ No match found\n";
    }
}

echo "Test completed.\n";