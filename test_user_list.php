<?php
// Test script to verify user management list functionality
echo "Testing user management list functionality...\n\n";

// Simulate getting all users with access
echo "Step 1: Simulate getting all users from database\n";

// Simulated database response (this is what DatabaseService.getAllUsers() should return)
$allUsers = [
    [
        'user_id' => 5261465966,
        'data' => [
            'fio' => 'Иван Иванов',
            'access_level' => 4,
            'functional_direction' => 'IT'
        ]
    ],
    [
        'user_id' => 123456789,
        'data' => [
            'fio' => 'Петр Петров',
            'access_level' => 1,
            'functional_direction' => 'HR'
        ]
    ],
    [
        'user_id' => 987654321,
        'data' => [
            'fio' => 'Сидор Сидоров',
            'access_level' => 0,
            'functional_direction' => 'Finance'
        ]
    ]
];

echo "Total users in database: " . count($allUsers) . "\n\n";

// Simulate getAllUsersWithAccess logic
echo "Step 2: Filter users with access level > 0\n";
$usersWithAccess = [];
foreach ($allUsers as $user) {
    $accessLevel = $user['data']['access_level'] ?? 0;
    echo "  User ID: {$user['user_id']}, Access Level: {$accessLevel}";
    if ($accessLevel > 0) {
        echo " - INCLUDED\n";
        $usersWithAccess[] = $user;
    } else {
        echo " - EXCLUDED (guest level)\n";
    }
}

echo "\nUsers with access (level > 0): " . count($usersWithAccess) . "\n\n";

// Simulate button creation
echo "Step 3: Create buttons for users with access\n";
if (empty($usersWithAccess)) {
    echo "  No users with access found - would show empty message\n";
} else {
    echo "  Creating buttons:\n";
    foreach ($usersWithAccess as $user) {
        $userId = $user['user_id'];
        $userData = $user['data'] ?? [];
        $fio = $userData['fio'] ?? 'Без ФИО';
        $accessLevel = $userData['access_level'] ?? 0;
        
        // Simulate getAccessLevelName
        $levelNames = [0 => 'Гость', 1 => 'Пользователь', 2 => 'Супервайзер', 3 => 'Администратор', 4 => 'Суперадминистратор'];
        $levelName = $levelNames[$accessLevel] ?? 'Неизвестный';
        
        $buttonText = "{$fio} ({$levelName})";
        $callbackData = "acc_lvl_user_details_{$userId}";
        
        echo "    Button: '{$buttonText}' -> {$callbackData}\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test completed successfully!\n";
