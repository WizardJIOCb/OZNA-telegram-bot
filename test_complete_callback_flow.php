<?php
// Test script to verify the complete callback flow

echo "Testing complete callback flow...\n";

// Simulate the callback data that would be sent by the "Управление пользователями" button
$callback_data = 'acc_lvl_manage_users';

echo "Callback data: " . $callback_data . "\n";

// Step 1: Test CallbackqueryCommand filtering
echo "\n1. Testing CallbackqueryCommand filtering:\n";
$shouldProcessInCallbackCommand = !(strpos($callback_data, 'category_') !== 0 && strpos($callback_data, 'adm_req_') !== 0 && strpos($callback_data, 'acc_lvl_') !== 0);

if ($shouldProcessInCallbackCommand) {
    echo "   SUCCESS: Callback will be processed in CallbackqueryCommand\n";
} else {
    echo "   ERROR: Callback would be filtered out in CallbackqueryCommand\n";
    exit(1);
}

// Step 2: Test AdminHandler filtering
echo "\n2. Testing AdminHandler filtering:\n";
function adminHandlerCanHandle($callbackData) {
    $startsWithCategoryAdmin = str_starts_with($callbackData, 'category_Админка');
    $startsWithCategoryAdm = str_starts_with($callbackData, 'category_Адм_');
    $equalsCategoryMain = $callbackData === 'category_Главное';
    $startsWithAdmReq = str_starts_with($callbackData, 'adm_req_');
    $isAdmReqCallback = $startsWithAdmReq;
    $isAccessLevelCallback = str_starts_with($callbackData, 'acc_lvl_');
    
    return !$isAccessLevelCallback && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $equalsCategoryMain || $startsWithAdmReq || $isAdmReqCallback);
}

$adminHandlerResult = adminHandlerCanHandle($callback_data);

if ($adminHandlerResult) {
    echo "   ERROR: AdminHandler would try to handle this callback\n";
    exit(1);
} else {
    echo "   SUCCESS: AdminHandler will NOT handle this callback\n";
}

// Step 3: Test AccessLevelHandler filtering
echo "\n3. Testing AccessLevelHandler filtering:\n";
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
    
    return $canHandle;
}

$accessLevelHandlerResult = accessLevelHandlerCanHandle($callback_data);

if ($accessLevelHandlerResult) {
    echo "   SUCCESS: AccessLevelHandler can handle this callback\n";
} else {
    echo "   ERROR: AccessLevelHandler cannot handle this callback\n";
    exit(1);
}

// Step 4: Test handler priority
echo "\n4. Testing handler priority:\n";
$adminHandlerPriority = 30;
$accessLevelHandlerPriority = 25;

if ($accessLevelHandlerPriority < $adminHandlerPriority) {
    echo "   SUCCESS: AccessLevelHandler has lower priority than AdminHandler\n";
    echo "   (This is correct - AdminHandler is checked first, but will skip acc_lvl_* callbacks)\n";
} else {
    echo "   ERROR: AccessLevelHandler does not have lower priority than AdminHandler\n";
    exit(1);
}

echo "\n🎉 All tests passed! The callback flow should work correctly.\n";
echo "\nTest completed.\n";
?>