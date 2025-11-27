<?php

// Test MessageService sendMessageWithReplyKeyboard method
echo "Testing MessageService sendMessageWithReplyKeyboard Method\n";
echo "================================================\n";

// Simulate the sendMessageWithReplyKeyboard method
function sendMessageWithReplyKeyboard($chatId, $text, $replyKeyboard = null) {
    echo "Sending message with reply keyboard to chat {$chatId}\n";
    echo "Message text:\n{$text}\n";
    
    if ($replyKeyboard) {
        echo "Reply keyboard:\n";
        foreach ($replyKeyboard as $rowIndex => $row) {
            echo "  Row {$rowIndex}: ";
            foreach ($row as $buttonIndex => $button) {
                echo "'" . $button['text'] . "'";
                if ($buttonIndex < count($row) - 1) echo ", ";
            }
            echo "\n";
        }
    } else {
        echo "No reply keyboard\n";
    }
    
    return "MESSAGE_SENT_WITH_REPLY_KEYBOARD";
}

// Test the method
$text = "<b>🏠 Главное меню</b>\n\n(для вызова этого меню в любой момент отправьте /start)";
$replyKeyboard = [
    [
        ['text' => '📊 Табель'], 
        ['text' => '❓ Помощь']
    ],
    [
        ['text' => '👤 Профиль'], 
        ['text' => '📁 Проекты']
    ],
    [
        ['text' => '⚙️ Админка']
    ],
    [
        ['text' => '🛠️ Управление проектом']
    ]
];

$result = sendMessageWithReplyKeyboard("123456789", $text, $replyKeyboard);
echo "\nResult: {$result}\n";

echo "\nTest completed!\n";