<?php
// Test script to verify callback handling logic

echo "Testing callback handling logic...\n";

// Simulate the callback data that would be sent by the "Управление пользователями" button
$callback_data = 'acc_lvl_manage_users';

echo "Callback data: " . $callback_data . "\n";

// Test the condition in CallbackqueryCommand (updated)
$shouldProcessInCallbackCommand = !(strpos($callback_data, 'category_') !== 0 && strpos($callback_data, 'adm_req_') !== 0 && strpos($callback_data, 'acc_lvl_') !== 0);

if ($shouldProcessInCallbackCommand) {
    echo "SUCCESS: Callback will be processed in CallbackqueryCommand\n";
} else {
    echo "ERROR: Callback would be filtered out in CallbackqueryCommand\n";
}

// Test the condition in AdminHandler (updated)
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
    echo "ERROR: AdminHandler would try to handle this callback\n";
} else {
    echo "SUCCESS: AdminHandler will NOT handle this callback (correctly routed to AccessLevelHandler)\n";
}

// Test with a regular admin callback
$admin_callback_data = 'category_Админка_Заявки';
echo "\nTesting with admin callback: " . $admin_callback_data . "\n";

$shouldProcessAdminInCallbackCommand = !(strpos($admin_callback_data, 'category_') !== 0 && strpos($admin_callback_data, 'adm_req_') !== 0 && strpos($admin_callback_data, 'acc_lvl_') !== 0);

if ($shouldProcessAdminInCallbackCommand) {
    echo "SUCCESS: Admin callback will be processed in CallbackqueryCommand\n";
} else {
    echo "ERROR: Admin callback would be filtered out in CallbackqueryCommand\n";
}

$adminHandlerResult2 = adminHandlerCanHandle($admin_callback_data);

if ($adminHandlerResult2) {
    echo "SUCCESS: AdminHandler will handle this callback\n";
} else {
    echo "ERROR: AdminHandler will NOT handle this callback\n";
}

echo "\nTest completed.\n";
?>