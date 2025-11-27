<?php

// Test reply keyboard creation
echo "Testing Reply Keyboard Creation\n";
echo "=============================\n";

// Simulate the createMainMenuReplyKeyboard method
function createMainMenuReplyKeyboard($userAccessLevel = 99) {
    $keyboard = [
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
        $keyboard[] = [
            ['text' => '⚙️ Админка']
        ];
    }
    
    // Add Project Management button only for super admins (access level 4)
    if ($userAccessLevel >= 4) {
        $keyboard[] = [
            ['text' => '🛠️ Управление проектом']
        ];
    }
    
    return $keyboard;
}

// Test for different access levels
$testLevels = [0, 1, 2, 3, 4, 5];

foreach ($testLevels as $level) {
    echo "\n--- Access Level {$level} ---\n";
    $keyboard = createMainMenuReplyKeyboard($level);
    
    echo "Keyboard rows: " . count($keyboard) . "\n";
    foreach ($keyboard as $rowIndex => $row) {
        echo "  Row {$rowIndex}: ";
        foreach ($row as $buttonIndex => $button) {
            echo "'" . $button['text'] . "'";
            if ($buttonIndex < count($row) - 1) echo ", ";
        }
        echo "\n";
    }
}

echo "\nTest completed!\n";