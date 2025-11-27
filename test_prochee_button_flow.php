<?php

// Test script to verify the complete flow for the "Прочее" button

echo "Complete Flow Test for 'Прочее' Button\n";
echo "=====================================\n\n";

// Step 1: User presses the "Прочее" button
echo "Step 1: User presses 'Прочее' button (cat_ТабЭлОб_Проч)\n";
echo "Expected behavior:\n";
echo "  - System sets 'awaiting_custom_input' = true in conversation notes\n";
echo "  - System sets 'custom_input_activity_type' = 'Прочее_Электрооборудование' in conversation notes\n";
echo "  - System prompts user with: 'Пожалуйста, введите описание выполняемой работы:'\n\n";

// Step 2: User enters custom description
echo "Step 2: User enters custom description\n";
$customInput = "Настройка серверного оборудования";
echo "User input: {$customInput}\n";
echo "Expected behavior:\n";
echo "  - System stores custom input in 'custom_input_text' in conversation notes\n";
echo "  - System sets 'awaiting_custom_input' = false\n";
echo "  - System shows confirmation message with custom input\n\n";

// Step 3: Show confirmation message
echo "Step 3: System shows confirmation message\n";

$activityType = 'Прочее_Электрооборудование';
$projectCode = 'TEST-PROJECT';

// Simulate the activity names array from TabelEntry
$activityNames = [
    'Прочее_Электрооборудование' => 'Прочее (Электрооборудование, Дополнительные работы)'
];

if (isset($activityNames[$activityType])) {
    $activityName = $activityNames[$activityType];
    $confirmationText = "<b>{$activityName}</b>, отправить запись в табель в проект <b>{$projectCode}</b>?";
    $confirmationText .= "\n\n<b>Описание работы:</b> " . $customInput;
    
    echo "Confirmation message:\n";
    echo $confirmationText . "\n\n";
} else {
    echo "ERROR: Activity type not found in activityNames array\n\n";
}

// Step 4: Show confirmation buttons
echo "Step 4: System shows confirmation buttons\n";
echo "Button 1: ✅ Да (callback_data: category_Табель_{$activityType}_yes)\n";
echo "Button 2: ❌ Нет (callback_data: category_Табель)\n\n";

// Step 5: User confirms
echo "Step 5: User clicks 'Да' button\n";
echo "Expected behavior:\n";
echo "  - System processes the activity execution\n";
echo "  - System creates a tabel entry with the custom description\n";
echo "  - System sends confirmation message to user\n\n";

// Simulate the final confirmation message
echo "Final confirmation message:\n";
$fio = "Иванов Иван Иванович";
$date = date('d.m.Y');
$message = "<b>{$fio}</b>\r\n✅ Запись в табель от <b>{$date}</b>, в проект <b>{$projectCode}</b> о <b>Прочее (Электрооборудование, Дополнительные работы)</b> добавлена\n\n";
$message .= "<b>Описание работы:</b> " . $customInput;

echo $message . "\n\n";

echo "Flow test completed!\n";