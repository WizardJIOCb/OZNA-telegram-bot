<?php
/**
 * Test to check how CallbackqueryCommand processes the callback
 */

echo "=== CallbackqueryCommand Test ===\n\n";

// Simulate the CallbackqueryCommand logic
function processCallback($callback_data) {
    echo "Processing callback_data: '{$callback_data}'\n";
    
    // Fix encoding issues with callback data
    $callback_data = mb_convert_encoding($callback_data, 'UTF-8', 'auto');
    
    echo "After encoding fix: '{$callback_data}'\n";
    
    // Early exit if no category specified and not adm_req callback
    if (strpos($callback_data, 'category_') !== 0 && strpos($callback_data, 'adm_req_') !== 0 && strpos($callback_data, 'acc_lvl_') !== 0) {
        echo "Early exit - no category specified\n";
        return 'early_exit';
    }
    
    echo "Passed early exit check\n";
    
    // Simulate handler factory logic
    echo "Checking handlers...\n";
    
    // Test AdminHandler canHandle
    $canHandle = testAdminHandlerCanHandle($callback_data);
    echo "AdminHandler canHandle result: " . ($canHandle ? 'YES' : 'NO') . "\n";
    
    if ($canHandle) {
        echo "Would call AdminHandler->handle\n";
        return 'admin_handler';
    }
    
    echo "No handler found\n";
    return 'no_handler';
}

function testAdminHandlerCanHandle($callbackData) {
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    
    $startsWithCategoryAdmin = str_starts_with($callbackData, 'category_Админка');
    $startsWithCategoryAdm = str_starts_with($callbackData, 'category_Адм_');
    $equalsCategoryMain = $callbackData === 'category_Главное';
    $startsWithAdmReq = str_starts_with($callbackData, 'adm_req_');
    
    // Add support for adm_req_* callbacks which don't have the category_ prefix
    $isAdmReqCallback = $startsWithAdmReq;
    
    // Exclude acc_lvl_* callbacks as they should be handled by AccessLevelHandler
    $isAccessLevelCallback = str_starts_with($callbackData, 'acc_lvl_');
    
    // NOTE: We exclude 'category_Главное' from this handler to let MainMenuHandler handle it
    // This ensures consistent main menu handling for all users regardless of access level
    $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
    
    return $canHandle;
}

// Test with the actual callback_data
$testCallbackData = 'category_Админка_Заявки';
$result = processCallback($testCallbackData);

echo "\nFinal result: {$result}\n";
echo "Expected: admin_handler\n";
echo "Match: " . ($result === 'admin_handler' ? 'YES' : 'NO') . "\n\n";

// Test with potential corrupted data
$corruptedCallbackData = 'category_РђРґРјРёРЅРєР°_Р—Р°СЏРІРєРё';
echo "Testing with corrupted callback_data: '{$corruptedCallbackData}'\n";
$corruptedResult = processCallback($corruptedCallbackData);
echo "Corrupted data result: {$corruptedResult}\n\n";

// Test with various other callback_data formats
$otherTestCases = [
    'category_Админка',
    'category_Админка_Проекты',
    'adm_req_view_12345',
    'category_Главное',
    'invalid_callback'
];

foreach ($otherTestCases as $testCase) {
    echo "Testing: '{$testCase}'\n";
    $testResult = processCallback($testCase);
    echo "Result: {$testResult}\n\n";
}

echo "=== Test Complete ===\n";
