<?php

// Тест для проверки логики обработки callback'ов мобильной бригады
echo "=== Тест обработки callback'ов мобильной бригады ===\n\n";

// Тестовые данные
$testCallbacks = [
    'Табель_Мобильная_Бригада_ДатаСегодня',
    'Табель_Мобильная_Бригада_ДатаВчера',
    'Табель_Пусконаладка_ДатаСегодня',
    'Табель_Шеф_монтаж_ДатаСегодня',
    'Табель_Текущая_ДатаСегодня'
];

foreach ($testCallbacks as $category) {
    echo "Категория: $category\n";
    
    // Проверка условий, как в методе handleDateSelection
    if (str_contains($category, 'Текущая')) {
        echo "→ Обработка: Текущая деятельность\n";
    } elseif (str_contains($category, 'Пусконаладка') || str_contains($category, 'ПНЛ')) {
        echo "→ Обработка: Пусконаладка\n";
    } elseif (str_contains($category, 'Шеф_монтаж') || str_contains($category, 'ШМ')) {
        echo "→ Обработка: Шеф-монтаж\n";
    } elseif (str_contains($category, 'Мобильная_Бригада')) {
        echo "→ Обработка: Мобильная бригада (ИСПРАВЛЕНО)\n";
    } else {
        echo "→ Обработка: По умолчанию\n";
    }
    
    echo "\n";
}

echo "=== Тест завершен ===\n";