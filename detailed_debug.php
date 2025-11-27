<?php

// Detailed debug script to trace mobile brigade callback processing
echo "=== Detailed Debug Mobile Brigade Callback Processing ===\n\n";

// Test the exact callback data
$callbackData = 'category_Табель_Мобильная_Бригада_Дата';
echo "1. Testing callback data: {$callbackData}\n";

// Simulate category extraction (like in CallbackHandlerFactory)
$category = substr($callbackData, strlen('category_'));
echo "2. Extracted category: {$category}\n";

// Test the condition in routeTabelCallback
echo "3. Testing routeTabelCallback condition:\n";
echo "   Category === 'Табель_Мобильная_Бригада_Дата': " . ($category === 'Табель_Мобильная_Бригада_Дата' ? 'true' : 'false') . "\n";

// Test the condition in handleOtherWorkTypes
echo "4. Testing handleOtherWorkTypes condition:\n";
echo "   Category === 'Табель_Мобильная_Бригада_Дата': " . ($category === 'Табель_Мобильная_Бригада_Дата' ? 'true' : 'false') . "\n";

// Test what should happen next
echo "5. Expected behavior:\n";
echo "   - Should call handleDateSelectionMenu()\n";
echo "   - Should show date selection keyboard\n";

// Simulate what happens when user selects a date
echo "\n=== Simulating Date Selection ===\n";
$dateCallback = 'category_Табель_Мобильная_Бригада_ДатаСегодня';
echo "6. Date selection callback: {$dateCallback}\n";

$dateCategory = substr($dateCallback, strlen('category_'));
echo "7. Extracted date category: {$dateCategory}\n";

// Test if this is recognized as a date selection
$isDateSelection = (strpos($dateCategory, 'ДатаСегодня') !== false || strpos($dateCategory, 'ДатаВчера') !== false);
echo "8. Is date selection: " . ($isDateSelection ? 'true' : 'false') . "\n";

// Test the condition in handleDateSelection
echo "9. Testing handleDateSelection conditions:\n";
echo "   Contains 'Текущая': " . (strpos($dateCategory, 'Текущая') !== false ? 'true' : 'false') . "\n";
echo "   Contains 'Пусконаладка' or 'ПНЛ': " . (strpos($dateCategory, 'Пусконаладка') !== false || strpos($dateCategory, 'ПНЛ') !== false ? 'true' : 'false') . "\n";
echo "   Contains 'Шеф-монтаж' or 'ШМ': " . (strpos($dateCategory, 'Шеф-монтаж') !== false || strpos($dateCategory, 'ШМ') !== false ? 'true' : 'false') . "\n";
echo "   Contains 'Мобильная_Бригада': " . (strpos($dateCategory, 'Мобильная_Бригада') !== false ? 'true' : 'false') . "\n";

// Simulate what happens when user selects a project
echo "\n=== Simulating Project Selection ===\n";
$projectCallback = 'category_Табель_ШифрПроекта_ОИ834';
echo "10. Project selection callback: {$projectCallback}\n";

$projectCategory = substr($projectCallback, strlen('category_'));
echo "11. Extracted project category: {$projectCategory}\n";

// Test if this is recognized as a project selection
$isProjectSelection = (strpos($projectCategory, 'ШифрПроекта_') !== false);
echo "12. Is project selection: " . ($isProjectSelection ? 'true' : 'false') . "\n";

echo "\n=== Analysis ===\n";
echo "The flow should be:\n";
echo "1. Mobile brigade button click -> handleDateSelectionMenu()\n";
echo "2. Date selection -> handleDateSelection() -> show project selection\n";
echo "3. Project selection -> handleProjectSelection() -> show mobile brigade activities\n";

echo "\nIf the button is not working, it might be because:\n";
echo "- The callback data is not exactly 'category_Табель_Мобильная_Бригада_Дата'\n";
echo "- There's an encoding issue with the Cyrillic characters\n";
echo "- The TabelHandler is not being called at all\n";
echo "- There's an error in the handleDateSelectionMenu() method\n";

echo "\n=== Test Completed ===\n";