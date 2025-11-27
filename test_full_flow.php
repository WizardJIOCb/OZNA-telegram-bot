<?php

// Test script to verify the full flow of tabel operations

echo "Testing full flow of tabel operations...\n";

// Test different callback patterns
$testCallbacks = [
    'category_Табель' => 'Main menu',
    'category_Табель_Теплоизоляция' => 'Activity confirmation',
    'category_Табель_Теплоизоляция_yes' => 'Activity execution',
    'category_Табель_Дорога' => 'Activity confirmation',
    'category_Табель_Дорога_yes' => 'Activity execution',
    'category_Табель_Простой' => 'Idle menu',
    'category_Табель_Простой_yes' => 'Idle execution',
];

echo "Testing callback patterns:\n";
foreach ($testCallbacks as $callback => $description) {
    echo "  {$callback} -> {$description}\n";
    
    // Test if it matches activity execution pattern
    $activityTypes = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
    ];
    
    // Check standard activity execution
    if (str_contains($callback, '_yes') && 
        preg_match('/^category_Табель_(' . implode('|', $activityTypes) . ')_yes$/', $callback)) {
        echo "    ✅ Matches activity execution pattern\n";
    } else {
        echo "    ❌ Does not match activity execution pattern\n";
    }
}

echo "Test completed.\n";