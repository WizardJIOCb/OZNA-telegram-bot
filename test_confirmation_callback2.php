<?php

// Test script to verify isConfirmationCallback method

$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
];

$testCategories = [
    'category_Табель_Теплоизоляция' => 'Activity confirmation',
    'category_Табель_Теплоизоляция_yes' => 'Activity execution',
    'category_Табель_Дорога' => 'Activity confirmation',
    'category_Табель_Дорога_yes' => 'Activity execution',
    'category_Табель_Простой' => 'Idle menu',
    'category_Табель_Простой_yes' => 'Idle execution',
    'category_Табель_ШМ_Простой_yes' => 'Idle execution',
];

echo "Testing isConfirmationCallback logic:\n";
foreach ($testCategories as $category => $description) {
    echo "  {$category} -> {$description}\n";
    
    // Check if it's an activity execution callback
    $isActivityExecution = false;
    if (str_contains($category, '_yes') && 
        preg_match('/^category_Табель_(' . implode('|', $activityTypes) . ')_yes$/', $category)) {
        $isActivityExecution = true;
    }
    
    // Check idle menu execution
    if (!$isActivityExecution && str_contains($category, '_yes') && 
        (str_contains($category, 'Табель_Простой_yes') || str_contains($category, 'Табель_ШМ_Простой_yes'))) {
        $isActivityExecution = true;
    }
    
    // Check if it's a confirmation callback (our new logic)
    $isConfirmation = false;
    if (!$isActivityExecution && str_contains($category, '_yes') && str_contains($category, 'Табель_')) {
        $isConfirmation = true;
    }
    
    echo "    Is activity execution: " . ($isActivityExecution ? '✅' : '❌') . "\n";
    echo "    Is confirmation: " . ($isConfirmation ? '✅' : '❌') . "\n";
    
    // Original logic for comparison
    $originalConfirmation = str_contains($category, '_yes') && str_contains($category, 'Табель_');
    echo "    Original logic would treat as confirmation: " . ($originalConfirmation ? '✅' : '❌') . "\n";
    
    echo "    Difference: " . (($originalConfirmation && !$isConfirmation) ? '✅ FIXED' : '❌') . "\n";
    echo "\n";
}

echo "Test completed.\n";