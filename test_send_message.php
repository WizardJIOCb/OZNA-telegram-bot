<?php

// Test MessageService sendMessage method
echo "Testing MessageService sendMessage Method\n";
echo "====================================\n";

// Simulate the sendMessage method
function sendMessage($chatId, $text, $keyboard = null) {
    echo "Sending message to chat {$chatId}\n";
    echo "Message text:\n{$text}\n";
    
    if ($keyboard) {
        echo "Keyboard:\n";
        foreach ($keyboard as $rowIndex => $row) {
            echo "  Row {$rowIndex}: ";
            foreach ($row as $buttonIndex => $button) {
                echo "'" . $button['text'] . "'";
                if (isset($button['callback_data'])) {
                    echo " (" . $button['callback_data'] . ")";
                }
                if ($buttonIndex < count($row) - 1) echo ", ";
            }
            echo "\n";
        }
    } else {
        echo "No keyboard\n";
    }
    
    return "MESSAGE_SENT";
}

// Test the method
$text = "🛠️ <b>Управление проектом</b>\n\nДобро пожаловать в раздел управления проектами!\nВыберите нужный пункт меню:";
$keyboard = [
    [
        ['text' => '👥 Персонал', 'callback_data' => 'category_УправлениеПроектом_Персонал']
    ],
    [
        ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
    ]
];

$result = sendMessage("123456789", $text, $keyboard);
echo "\nResult: {$result}\n";

echo "\nTest completed!\n";