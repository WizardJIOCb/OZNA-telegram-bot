<?php

// Test script to verify regex patterns for activity execution

$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы'
];

$category = 'category_Табель_Теплоизоляция_yes';

echo "Testing category: {$category}\n";

// Check standard activity execution
if (str_contains($category, '_yes') && 
    preg_match('/^category_Табель_(' . implode('|', $activityTypes) . ')_yes$/', $category, $matches)) {
    echo "✅ Match found!\n";
    print_r($matches);
} else {
    echo "❌ No match found\n";
}

// Let's also test the pattern without anchors
if (str_contains($category, '_yes') && 
    preg_match('/category_Табель_(' . implode('|', $activityTypes) . ')_yes/', $category, $matches)) {
    echo "✅ Match found without anchors!\n";
    print_r($matches);
} else {
    echo "❌ No match found without anchors\n";
}

echo "Activity types:\n";
foreach ($activityTypes as $type) {
    echo "  - {$type}\n";
}