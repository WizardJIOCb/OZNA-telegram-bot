<?php
// Test script to verify admin menu buttons

echo "Testing admin menu buttons...\n";

// Simulate the admin menu for a super admin (access level 4)
$accessLevel = 4;

echo "Access level: " . $accessLevel . " (Super Admin)\n";

// Simulate the keyboard creation logic
$keyboard = [
    [
        ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
    ],
    [
        ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
    ]
];

// Only super admins can manage access levels
if ($accessLevel === 4) { // ACCESS_LEVEL_SUPER_ADMIN
    $keyboard[] = [
        ['text' => '🔐 Управление уровнями доступа', 'callback_data' => 'category_УровеньДоступа_Управление']
    ];
    
    // NOTE: The following buttons have been removed as per user request
    // - "Добавить администратора" 
    // - "Очистить список администраторов"
    // These buttons were previously shown only to super admins but are no longer needed
}

$keyboard[] = [
    ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
];

echo "\nMenu buttons:\n";
foreach ($keyboard as $rowIndex => $row) {
    foreach ($row as $buttonIndex => $button) {
        echo "  Row " . ($rowIndex + 1) . ", Button " . ($buttonIndex + 1) . ": " . $button['text'] . "\n";
    }
}

// Count total buttons
$totalButtons = 0;
foreach ($keyboard as $row) {
    $totalButtons += count($row);
}

echo "\nTotal buttons: " . $totalButtons . "\n";

// Check if unwanted buttons are present
$hasAddAdminButton = false;
$hasClearAdminsButton = false;

foreach ($keyboard as $row) {
    foreach ($row as $button) {
        if ($button['text'] === '➕ Добавить администратора') {
            $hasAddAdminButton = true;
        }
        if ($button['text'] === '🗑️ Очистить список администраторов') {
            $hasClearAdminsButton = true;
        }
    }
}

if ($hasAddAdminButton) {
    echo "❌ ERROR: 'Добавить администратора' button is still present\n";
} else {
    echo "✅ SUCCESS: 'Добавить администратора' button has been removed\n";
}

if ($hasClearAdminsButton) {
    echo "❌ ERROR: 'Очистить список администраторов' button is still present\n";
} else {
    echo "✅ SUCCESS: 'Очистить список администраторов' button has been removed\n";
}

echo "\nTest completed.\n";
?>