<?php

// Test ProjectManagementHandler showProjectManagementMenu method
echo "Testing ProjectManagementHandler showProjectManagementMenu Method\n";
echo "==========================================================\n";

// Simulate the showProjectManagementMenu method
function showProjectManagementMenu($chatId, $userId) {
    echo "Showing project management menu for chat {$chatId} and user {$userId}\n";
    
    $text = "🛠️ <b>Управление проектом</b>\n\n";
    $text .= "Добро пожаловать в раздел управления проектами!\n";
    $text .= "Выберите нужный пункт меню:\n";
    
    $keyboard = [
        [
            ['text' => '👥 Персонал', 'callback_data' => 'category_УправлениеПроектом_Персонал']
        ],
        [
            ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
        ]
    ];
    
    echo "Menu text:\n{$text}\n";
    echo "Keyboard:\n";
    foreach ($keyboard as $rowIndex => $row) {
        echo "  Row {$rowIndex}: ";
        foreach ($row as $buttonIndex => $button) {
            echo "'" . $button['text'] . "' (" . $button['callback_data'] . ")";
            if ($buttonIndex < count($row) - 1) echo ", ";
        }
        echo "\n";
    }
    
    return "MENU_SHOWN";
}

// Test the method
$result = showProjectManagementMenu("123456789", "5261465966");
echo "\nResult: {$result}\n";

echo "\nTest completed!\n";