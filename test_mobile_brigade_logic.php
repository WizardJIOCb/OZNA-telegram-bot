<?php
// Test script to verify the mobile brigade logic in handleOtherWorkTypes

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== Mobile Brigade Logic Test ===\n\n";

// Simulate the handleOtherWorkTypes method logic
$category = 'Табель_Мобильная_Бригада';

echo "Testing handleOtherWorkTypes logic:\n";
echo "================================\n";
echo "Category: '{$category}'\n\n";

// Check the conditions in handleOtherWorkTypes
echo "Checking conditions:\n";

// Commissioning condition
if ($category === 'Табель_Пусконаладка') {
    echo "1. Commissioning condition: MATCH\n";
} else {
    echo "1. Commissioning condition: NO MATCH\n";
}

// Supervision condition
if ($category === 'Табель_Шеф_монтаж') {
    echo "2. Supervision condition: MATCH\n";
} else {
    echo "2. Supervision condition: NO MATCH\n";
}

// Mobile Brigade condition
if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
    echo "3. Mobile Brigade condition: MATCH ✅\n";
    echo "   -> Should call handleMobileBrigadeDateSelection()\n";
} else {
    echo "3. Mobile Brigade condition: NO MATCH ❌\n";
}

// Current Activity condition
if ($category === 'Табель_Текущая') {
    echo "4. Current Activity condition: MATCH\n";
} else {
    echo "4. Current Activity condition: NO MATCH\n";
}

echo "\n";

// Test the activity types check
$activityTypes = [
    'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
    'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
    'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
    'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
    'МедОсмотр', 'Аттестация', 'Общая',
    'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет'
];

$strippedCategory = str_replace('Табель_', '', $category);
echo "Stripped category: '{$strippedCategory}'\n";

if (in_array($strippedCategory, $activityTypes)) {
    echo "5. Activity types check: MATCH\n";
    echo "   -> Would treat as activity confirmation\n";
} else {
    echo "5. Activity types check: NO MATCH\n";
}

echo "\n=== Test Complete ===\n";
echo "Conclusion: The mobile brigade condition should match and call handleMobileBrigadeDateSelection()\n";

?>