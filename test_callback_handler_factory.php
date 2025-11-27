<?php
/**
 * Test the CallbackHandlerFactory functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Callback Handler Factory ===\n\n";

// Function to simulate AdminHandler::canHandle
function adminHandlerCanHandle(string $callbackData): bool {
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
    
    return $canHandle;
}

// Function to simulate AdminHandler::handle
function adminHandlerHandle(string $category): array {
    // For testing, we'll just return a simple response
    if ($category === 'Админка_Заявки') {
        return [
            'text' => "Список заявок на доступ (тест)",
            'keyboard' => [
                [
                    ['text' => 'Тестовая заявка 1', 'callback_data' => 'adm_req_view_123456789']
                ],
                [
                    ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                ]
            ]
        ];
    }
    
    return [
        'text' => "Обработано: {$category}",
        'keyboard' => []
    ];
}

// Function to simulate CallbackHandlerFactory::handleCallback
function handleCallback(string $callbackData): array {
    echo "handleCallback called with callbackData: '{$callbackData}'\n";
    
    // Fix encoding issues with callback data
    $callbackData = mb_convert_encoding($callbackData, 'UTF-8', 'auto');
    
    // Extract category from callback data
    $category = extractCategory($callbackData);
    
    echo "handling callback data: '{$callbackData}', extracted category: '{$category}'\n";
    
    // Check if AdminHandler can handle this callback
    if (adminHandlerCanHandle($callbackData)) {
        echo "found handler: AdminHandler\n";
        // Also fix encoding for category
        $encodedCategory = mb_convert_encoding($category, 'UTF-8', 'auto');
        $result = adminHandlerHandle($encodedCategory);
        echo "handler result: success\n";
        return $result;
    } else {
        echo "handler AdminHandler cannot handle callback: '{$callbackData}'\n";
    }
    
    echo "no handler found for callback data: '{$callbackData}'\n";
    return [
        'text' => 'Не выбран пункт меню',
        'keyboard' => []
    ];
}

// Function to simulate CallbackHandlerFactory::extractCategory
function extractCategory(string $callbackData): string {
    // Handle new short callback_data formats
    if (strpos($callbackData, 'adm_req_') === 0) {
        // For admin request callbacks, return the full callbackData as category
        $category = $callbackData;
        return $category;
    }
    
    if (strpos($callbackData, 'category_') === 0) {
        $category = substr($callbackData, strlen('category_'));
        // Fix encoding for category
        $category = mb_convert_encoding($category, 'UTF-8', 'auto');
        return $category;
    }
    
    return $callbackData;
}

// Test with the callback data that should be sent by "Заявки на доступ" button
$callbackData = 'category_Админка_Заявки';
echo "Testing handleCallback with: '{$callbackData}'\n";
$result = handleCallback($callbackData);

echo "Result text:\n";
echo $result['text'] . "\n";

if (!empty($result['keyboard'])) {
    echo "Result keyboard:\n";
    foreach ($result['keyboard'] as $rowIndex => $row) {
        foreach ($row as $buttonIndex => $button) {
            if (isset($button['text']) && isset($button['callback_data'])) {
                echo "  Button: " . $button['text'] . " -> " . $button['callback_data'] . "\n";
            } else {
                echo "  Invalid button at [{$rowIndex}][{$buttonIndex}]\n";
            }
        }
    }
}

echo "\n=== Test Complete ===\n";