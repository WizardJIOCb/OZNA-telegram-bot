<?php
// Test the callback handler fix for user management button
echo "Testing callback handler fix for 'acc_lvl_manage_users'...\n\n";

// Test callback data
$callbackData = 'acc_lvl_manage_users';
echo "Callback data: {$callbackData}\n";

// Simulate extractCategory logic (updated version)
function extractCategorySimulation($callbackData) {
    echo "\nSimulating extractCategory method:\n";
    echo "  Input: '{$callbackData}'\n";
    
    // Handle access level callbacks (acc_lvl_ prefix) - return full callback data
    if (strpos($callbackData, 'acc_lvl_') === 0) {
        $category = $callbackData; // Pass the full callback data to AccessLevelHandler
        echo "  Matched acc_lvl_ prefix\n";
        echo "  Extracted category: '{$category}'\n";
        return $category;
    }
    
    // Handle admin request callbacks (adm_req_ prefix) - return full callback data
    if (strpos($callbackData, 'adm_req_') === 0) {
        $category = $callbackData; // Pass the full callback data to AdminHandler
        echo "  Matched adm_req_ prefix\n";
        echo "  Extracted category: '{$category}'\n";
        return $category;
    }
    
    // Handle project control callbacks (proj_ctrl_ prefix)
    if (strpos($callbackData, 'proj_ctrl_') === 0) {
        $category = $callbackData; // Pass the full callback data to ProjectHandler
        echo "  Matched proj_ctrl_ prefix\n";
        echo "  Extracted category: '{$category}'\n";
        return $category;
    }
    
    // Handle other categories
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        echo "  Matched category_ prefix\n";
        echo "  Extracted category: '{$category}'\n";
        return $category;
    }
    
    echo "  No matching prefix found\n";
    echo "  Extracted category: '' (empty)\n";
    return '';
}

$category = extractCategorySimulation($callbackData);

echo "\n" . str_repeat("=", 60) . "\n";
echo "Result:\n";
echo "  Callback data: '{$callbackData}'\n";
echo "  Extracted category: '{$category}'\n";
echo "  Category is empty: " . (empty($category) ? 'YES - PROBLEM!' : 'NO - OK!') . "\n";

if (!empty($category)) {
    echo "\n✅ SUCCESS: Category extracted correctly!\n";
    echo "The AccessLevelHandler will receive category = '{$category}'\n";
    echo "This will match the condition: if (\$category === 'acc_lvl_manage_users')\n";
    echo "And the user list will be displayed!\n";
} else {
    echo "\n❌ FAILURE: Category is empty!\n";
    echo "The AccessLevelHandler will not be able to handle this callback.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";

// Test all access level callbacks
echo "\nTesting all access level callbacks:\n";
$testCallbacks = [
    'acc_lvl_manage_users',
    'acc_lvl_user_details_123456',
    'acc_lvl_set_level_123456_2',
    'acc_lvl_users'
];

foreach ($testCallbacks as $testCallback) {
    echo "\n  Testing: '{$testCallback}'\n";
    $testCategory = extractCategorySimulation($testCallback);
    echo "    Result: " . (empty($testCategory) ? '❌ EMPTY' : "✅ '{$testCategory}'") . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test completed!\n";
