<?php
// Test script to verify AccessLevelHandler canHandle logic

echo "Testing AccessLevelHandler canHandle logic...\n";

// Simulate the callback data that would be sent by the "Управление пользователями" button
$callback_data = 'acc_lvl_manage_users';

echo "Callback data: " . $callback_data . "\n";

// Test the AccessLevelHandler canHandle method logic
function accessLevelHandlerCanHandle($callbackData) {
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    
    // Check for category-based access level callbacks
    $isCategoryAccessLevel = strpos($callbackData, 'category_УровеньДоступа') === 0;
    
    // Also check for corrupted UTF-8 version and fix if needed
    if (!$isCategoryAccessLevel) {
        $isCategoryAccessLevel = strpos($callbackData, 'category_РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°') === 0;
        // If we found the corrupted version, we need to fix it
        if ($isCategoryAccessLevel) {
            // In a real implementation, we would fix the encoding here
            // For this test, we'll just check if it would be fixed
            $isCategoryAccessLevel = strpos($callbackData, 'category_УровеньДоступа') === 0;
        }
    }
    
    // Check for new access level management callbacks
    $isAccessLevelManagement = (
        $callbackData === 'acc_lvl_users' ||
        $callbackData === 'acc_lvl_manage_users' ||
        strpos($callbackData, 'acc_lvl_user_details_') === 0 ||
        strpos($callbackData, 'acc_lvl_set_level_') === 0
    );
    
    $canHandle = $isCategoryAccessLevel || $isAccessLevelManagement;
    
    echo "isCategoryAccessLevel: " . ($isCategoryAccessLevel ? 'true' : 'false') . "\n";
    echo "isAccessLevelManagement: " . ($isAccessLevelManagement ? 'true' : 'false') . "\n";
    
    return $canHandle;
}

$result = accessLevelHandlerCanHandle($callback_data);

if ($result) {
    echo "SUCCESS: AccessLevelHandler can handle this callback\n";
} else {
    echo "ERROR: AccessLevelHandler cannot handle this callback\n";
}

echo "\nTest completed.\n";
?>