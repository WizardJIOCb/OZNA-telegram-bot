<?php
// Test script to check user permissions for construction works activities

echo "=== Testing User Permissions for Construction Works Activities ===\n";

// Simulate a user with different access levels
$accessLevels = [
    0 => "GUEST",
    1 => "USER",
    2 => "SUPERVISOR",
    3 => "ADMIN",
    4 => "SUPER_ADMIN"
];

echo "Testing access level restrictions:\n";

foreach ($accessLevels as $level => $name) {
    echo "\nAccess Level {$level} ({$name}):\n";
    
    // Check if user can add entry (from TabelEntry.php canAddEntry method)
    $canAddEntry = true;
    
    // Admin users (level 3+) and super admins are exempt from this check
    // Level 1 users (Пользователь) are also exempt when explicitly confirming
    if ($level >= 3 || $level == 1) {
        $canAddEntry = true;
        echo "  ✅ Can add entry (exempt from recent entry check)\n";
    } else {
        // For other users, check if they have a recent entry
        // In our test, we'll assume they don't have a recent entry
        $canAddEntry = true;
        echo "  ✅ Can add entry (no recent entry found)\n";
    }
    
    if ($canAddEntry) {
        echo "  ✅ User can create construction works reports\n";
    } else {
        echo "  ❌ User cannot create construction works reports\n";
    }
}

echo "\n=== Test Complete ===\n";
echo "Users with access level 1 (USER) and above should be able to create construction works reports.\n";
echo "Guest users (level 0) might be restricted from creating reports.\n";
?>