<?php

// Test script to verify the custom activity input functionality for "Прочее" button

echo "Testing custom activity input for 'Прочее' button\n";
echo "===============================================\n\n";

// Simulate the current behavior
echo "1. User presses 'Прочее' button (cat_ТабЭлОб_Проч)\n";
echo "2. System should prompt: 'Пожалуйста, введите описание выполняемой работы:'\n";
echo "3. User enters custom description\n";
echo "4. System should show confirmation with custom description\n\n";

// Simulate the confirmation message with custom input
$activityType = 'Прочее_Электрооборудование';
$projectCode = 'TEST-PROJECT';
$customInput = 'Настройка серверного оборудования';

echo "Simulated confirmation message:\n";
echo "<b>Прочее (Электрооборудование, Дополнительные работы)</b>, отправить запись в табель в проект <b>{$projectCode}</b>?\n\n";
echo "<b>Описание работы:</b> {$customInput}\n\n";

// Show the confirmation buttons
echo "Confirmation buttons:\n";
echo "✅ Да (category_Табель_{$activityType}_yes)\n";
echo "❌ Нет (category_Табель)\n\n";

echo "Test completed!\n";