<?php

// Test ProjectManagementHandler handle method
echo "Testing ProjectManagementHandler Handle Method\n";
echo "===========================================\n";

// Simulate the handle method logic
function handle($userId, $category) {
    echo "Handling callback for user {$userId} with category '{$category}'\n";
    
    // Check if user is super admin (mock)
    $isSuperAdmin = ($userId == 5261465966);
    $userAccessLevel = $isSuperAdmin ? 4 : 3; // 4 for super admin, 3 for regular admin
    
    echo "User access level: {$userAccessLevel}\n";
    
    // Check access level
    if ($userAccessLevel < 4) { // ACCESS_LEVEL_SUPER_ADMIN
        echo "❌ Access denied - only super admins can manage projects\n";
        return "ACCESS_DENIED";
    }
    
    // Handle different subcategories
    if ($category === 'УправлениеПроектом') {
        echo "Showing project management menu\n";
        return "SHOW_PROJECT_MENU";
    } elseif ($category === 'УправлениеПроектом_Персонал') {
        echo "Showing personnel menu\n";
        return "SHOW_PERSONNEL_MENU";
    }
    
    // Show project management menu by default
    echo "Showing project management menu (default)\n";
    return "SHOW_PROJECT_MENU";
}

// Test cases
$testCases = [
    // Super admin with main category
    [5261465966, 'УправлениеПроектом'],
    
    // Super admin with personnel category
    [5261465966, 'УправлениеПроектом_Персонал'],
    
    // Regular admin with main category (should be denied)
    [123456789, 'УправлениеПроектом'],
    
    // Super admin with unknown category
    [5261465966, 'УправлениеПроектом_Неизвестно']
];

foreach ($testCases as $index => $testCase) {
    $userId = $testCase[0];
    $category = $testCase[1];
    
    echo "\n--- Test Case " . ($index + 1) . " ---\n";
    $result = handle($userId, $category);
    echo "Result: {$result}\n";
}

echo "\nTest completed!\n";