<?php
/**
 * Test the canHandle method functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Can Handle ===\n\n";

// Function to simulate AdminHandler::canHandle
function canHandle(string $callbackData): bool {
    echo "canHandle called with callbackData: '{$callbackData}'\n";
    
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    
    $startsWithCategoryAdmin = strpos($callbackData, 'category_Админка') === 0;
    $startsWithCategoryAdm = strpos($callbackData, 'category_Адм_') === 0;
    $equalsCategoryMain = $callbackData === 'category_Главное';
    $startsWithAdmReq = strpos($callbackData, 'adm_req_') === 0;
    
    // Add support for adm_req_* callbacks which don't have the category_ prefix
    $isAdmReqCallback = $startsWithAdmReq;
    
    // Exclude acc_lvl_* callbacks as they should be handled by AccessLevelHandler
    $isAccessLevelCallback = strpos($callbackData, 'acc_lvl_') === 0;
    
    // NOTE: We exclude 'category_Главное' from this handler to let MainMenuHandler handle it
    // This ensures consistent main menu handling for all users regardless of access level
    $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
    
    echo "startsWithCategoryAdmin: " . ($startsWithCategoryAdmin ? 'true' : 'false') . "\n";
    echo "startsWithCategoryAdm: " . ($startsWithCategoryAdm ? 'true' : 'false') . "\n";
    echo "equalsCategoryMain: " . ($equalsCategoryMain ? 'true' : 'false') . "\n";
    echo "startsWithAdmReq: " . ($startsWithAdmReq ? 'true' : 'false') . "\n";
    echo "isAdmReqCallback: " . ($isAdmReqCallback ? 'true' : 'false') . "\n";
    echo "isAccessLevelCallback: " . ($isAccessLevelCallback ? 'true' : 'false') . "\n";
    echo "canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";
    
    return $canHandle;
}

// Test with the callback data that should be sent by "Заявки на доступ" button
$callbackData = 'category_Админка_Заявки';
echo "Testing canHandle with: '{$callbackData}'\n";
$result = canHandle($callbackData);
echo "Result: " . ($result ? 'YES' : 'NO') . "\n\n";

// Test with other possible callback data
$testCases = [
    'category_Админка',
    'category_Админка_Проекты',
    'category_Админка_ЗапросДоступ',
    'category_Главное',
    'adm_req_view_123456789',
    'acc_lvl_promote_123456789'
];

foreach ($testCases as $testCase) {
    echo "Testing canHandle with: '{$testCase}'\n";
    $result = canHandle($testCase);
    echo "Result: " . ($result ? 'YES' : 'NO') . "\n\n";
}

echo "=== Test Complete ===\n";

// Test ProjectManagementHandler canHandle method
echo "Testing ProjectManagementHandler canHandle Method\n";
echo "=============================================\n";

// Simulate the canHandle method
function testCanHandle($callbackData) {
    $result = str_starts_with($callbackData, 'category_УправлениеПроектом');
    echo "Testing callback: '{$callbackData}'\n";
    echo "Starts with 'category_УправлениеПроектом': " . ($result ? 'YES' : 'NO') . "\n";
    
    // Show detailed comparison
    $prefix = 'category_УправлениеПроектом';
    echo "Prefix: '{$prefix}'\n";
    echo "Prefix length: " . strlen($prefix) . "\n";
    echo "Callback substring: '" . substr($callbackData, 0, strlen($prefix)) . "'\n";
    echo "Equal: " . ($prefix === substr($callbackData, 0, strlen($prefix)) ? 'YES' : 'NO') . "\n";
    
    return $result;
}

// Test cases
$testCases = [
    'category_УправлениеПроектом',
    'category_УправлениеПроектом_Персонал',
    'category_Админка',
    'personnel_main',
    'category_УправлениеПроектом123'
];

foreach ($testCases as $index => $testCase) {
    echo "\n--- Test Case " . ($index + 1) . " ---\n";
    $result = testCanHandle($testCase);
    echo "Result: " . ($result ? 'CAN HANDLE' : 'CANNOT HANDLE') . "\n";
}

echo "\nTest completed!\n";
