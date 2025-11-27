<?php

// Test MainMenuHandler showMainMenu method
echo "Testing MainMenuHandler showMainMenu Method\n";
echo "====================================\n";

// Simulate the showMainMenu method
function showMainMenu($chatId, $userId, $userAccessLevel) {
    echo "Showing main menu for chat {$chatId}, user {$userId}, access level {$userAccessLevel}\n";
    
    // Create main menu keyboard
    $keyboard = [
        [
            ['text' => '📊 Табель', 'callback_data' => 'category_Табель']
        ],
        [
            ['text' => '📋 Супервайзер', 'callback_data' => 'category_Супервайзер']
        ]
    ];
    
    // Add admin menu for supervisors and above (level 2+)
    if ($userAccessLevel >= 2) {
        $keyboard[] = [
            ['text' => '⚙️ Админка', 'callback_data' => 'category_Админка']
        ];
    }
    
    // Add project management button for super admins only (level 4)
    if ($userAccessLevel >= 4) {
        $keyboard[] = [
            ['text' => '🛠️ Управление проектом', 'callback_data' => 'category_УправлениеПроектом']
        ];
    }
    
    $keyboard[] = [
        ['text' => '❓ Помощь', 'callback_data' => 'category_Помощь']
    ];
    
    $text = "<b>🏠 Главное меню</b>\n\n(для вызова этого меню в любой момент отправьте /start)";
    
    echo "Menu text:\n{$text}\n";
    echo "Inline keyboard:\n";
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
    
    // Create reply keyboard (persistent menu)
    $replyKeyboard = [
        [
            ['text' => '📊 Табель'], 
            ['text' => '❓ Помощь']
        ],
        [
            ['text' => '👤 Профиль'], 
            ['text' => '📁 Проекты']
        ]
    ];
    
    // Add Adminка button only for users with access level 2 or higher (Admin and above)
    if ($userAccessLevel >= 2) {
        $replyKeyboard[] = [
            ['text' => '⚙️ Админка']
        ];
    }
    
    // Add Project Management button only for super admins (access level 4)
    if ($userAccessLevel >= 4) {
        $replyKeyboard[] = [
            ['text' => '🛠️ Управление проектом']
        ];
    }
    
    echo "\nReply keyboard:\n";
    foreach ($replyKeyboard as $rowIndex => $row) {
        echo "  Row {$rowIndex}: ";
        foreach ($row as $buttonIndex => $button) {
            echo "'" . $button['text'] . "'";
            if ($buttonIndex < count($row) - 1) echo ", ";
        }
        echo "\n";
    }
    
    return "MAIN_MENU_SHOWN";
}

// Test for super admin (access level 4)
$result = showMainMenu("123456789", "5261465966", 4);
echo "\nResult: {$result}\n";

echo "\nTest completed!\n";