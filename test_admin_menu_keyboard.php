<?php
/**
 * Test to check how the admin menu keyboard is created
 */

echo "=== Admin Menu Keyboard Test ===\n\n";

// Simulate the showAdminMainMenu method logic
function showAdminMainMenu($userId) {
    echo "Creating admin menu for user ID: {$userId}\n";
    
    // Simulate access level (4 = super admin)
    $accessLevel = 4;
    $levelName = 'Суперадмин';
    
    echo "User access level: {$accessLevel} ({$levelName})\n\n";
    
    // Create keyboard
    $keyboard = [
        [
            ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
        ],
        [
            ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
        ]
    ];
    
    // Only superadmins can manage access levels
    if ($accessLevel === 4) { // ACCESS_LEVEL_SUPER_ADMIN
        $keyboard[] = [
            ['text' => '🔐 Управление уровнями доступа', 'callback_data' => 'category_УровеньДоступа_Управление']
        ];
    }
    
    echo "Generated keyboard:\n";
    foreach ($keyboard as $rowIndex => $row) {
        echo "  Row {$rowIndex}:\n";
        foreach ($row as $buttonIndex => $button) {
            echo "    Button {$buttonIndex}: '{$button['text']}' -> '{$button['callback_data']}'\n";
        }
    }
    
    echo "\n";
    
    // Check if the "Заявки на доступ" button exists
    $found = false;
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            if ($button['text'] === '👥 Заявки на доступ' && $button['callback_data'] === 'category_Админка_Заявки') {
                $found = true;
                break 2;
            }
        }
    }
    
    echo "Button '👥 Заявки на доступ' found: " . ($found ? 'YES' : 'NO') . "\n";
    
    return $keyboard;
}

// Test with a super admin user
$testUserId = 5261465966;
$keyboard = showAdminMainMenu($testUserId);

echo "\n=== Test Complete ===\n";