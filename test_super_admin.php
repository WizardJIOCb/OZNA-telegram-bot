<?php

// Test super admin functionality
echo "Testing Super Admin Functionality\n";
echo "===============================\n";

// Simulate the isSuperAdmin method
function isSuperAdmin($userId) {
    // For now, we'll use a simple approach to check super admin status
    // In a real implementation, you might want to store this in a file or database
    $superAdmins = [5261465966]; // Example super admin ID
    $result = in_array($userId, $superAdmins);
    echo "Checking if user {$userId} is super admin: " . ($result ? 'YES' : 'NO') . "\n";
    echo "Super admin list: " . json_encode($superAdmins) . "\n";
    return $result;
}

// Simulate the getUserAccessLevel method
function getUserAccessLevel($userId) {
    // First check if user is super admin
    if (isSuperAdmin($userId)) {
        echo "User {$userId} is super admin, returning level 4\n";
        return 4; // ACCESS_LEVEL_SUPER_ADMIN
    }
    
    // Get access level from database (mock)
    $mockLevels = [
        5261465966 => 1, // This should be overridden by super admin check
        123456789 => 3,  // Regular admin
        987654321 => 1   // Regular user
    ];
    
    $level = $mockLevels[$userId] ?? 0;
    echo "User {$userId} access level from database: {$level}\n";
    return $level;
}

// Test different user IDs
$testUsers = [
    5261465966, // Super admin
    123456789,  // Regular admin
    987654321,  // Regular user
    111111111   // Guest
];

foreach ($testUsers as $userId) {
    echo "\n--- Testing User {$userId} ---\n";
    $accessLevel = getUserAccessLevel($userId);
    echo "Final access level: {$accessLevel}\n";
}

echo "\nTest completed!\n";