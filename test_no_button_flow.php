<?php

// Test the flow for the "No" button in confirmation dialogs

echo "=== Testing 'No' Button Flow ===\n\n";

// 1. Simulate the callback data from the "No" button
$callbackData = 'category_Табель';
echo "1. Callback data from 'No' button: '{$callbackData}'\n";

// 2. Simulate category extraction (like in CallbackHandlerFactory)
$category = substr($callbackData, strlen('category_'));
echo "2. Extracted category: '{$category}'\n";

// 3. Test handler priorities and canHandle methods
echo "3. Handler priority check:\n";

// Simulate handler priorities
$handlers = [
    'MainMenuHandler' => 50,
    'AdminHandler' => 25,
    'TabelHandler' => 20
];

echo "   Sorted by priority:\n";
uasort($handlers, function($a, $b) { return $b - $a; });
foreach ($handlers as $handler => $priority) {
    echo "     {$priority}: {$handler}\n";
}

// 4. Test canHandle for each handler
echo "\n4. canHandle results:\n";

// MainMenuHandler canHandle
$mainMenuCanHandle = $callbackData === 'category_Главное';
echo "   MainMenuHandler canHandle('{$callbackData}'): " . ($mainMenuCanHandle ? 'true' : 'false') . "\n";

// AdminHandler canHandle
$startsWithCategoryAdmin = strpos($callbackData, 'category_Админка') === 0;
$startsWithCategoryAdm = strpos($callbackData, 'category_Адм_') === 0;
$equalsCategoryMain = $callbackData === 'category_Главное';
$startsWithAdmReq = strpos($callbackData, 'adm_req_') === 0;
$isAdmReqCallback = $startsWithAdmReq;
$isAccessLevelCallback = strpos($callbackData, 'acc_lvl_') === 0;
$adminCanHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
echo "   AdminHandler canHandle('{$callbackData}'): " . ($adminCanHandle ? 'true' : 'false') . "\n";

// TabelHandler canHandle
$tabelCanHandle = strpos($callbackData, 'category_Табель') === 0 || strpos($callbackData, 'cat_Таб') === 0;
echo "   TabelHandler canHandle('{$callbackData}'): " . ($tabelCanHandle ? 'true' : 'false') . "\n";

// 5. Determine which handler will process the callback
echo "\n5. Handler selection:\n";
$selectedHandler = '';
foreach ($handlers as $handler => $priority) {
    $canHandle = false;
    switch ($handler) {
        case 'MainMenuHandler':
            $canHandle = $callbackData === 'category_Главное';
            break;
        case 'AdminHandler':
            $startsWithCategoryAdmin = strpos($callbackData, 'category_Админка') === 0;
            $startsWithCategoryAdm = strpos($callbackData, 'category_Адм_') === 0;
            $equalsCategoryMain = $callbackData === 'category_Главное';
            $startsWithAdmReq = strpos($callbackData, 'adm_req_') === 0;
            $isAdmReqCallback = $startsWithAdmReq;
            $isAccessLevelCallback = strpos($callbackData, 'acc_lvl_') === 0;
            $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
            break;
        case 'TabelHandler':
            $canHandle = strpos($callbackData, 'category_Табель') === 0 || strpos($callbackData, 'cat_Таб') === 0;
            break;
    }
    
    echo "   Checking {$handler}: " . ($canHandle ? 'CAN HANDLE' : 'cannot handle') . "\n";
    if ($canHandle && $selectedHandler === '') {
        $selectedHandler = $handler;
        echo "   -> SELECTED {$handler} to handle the callback\n";
    }
}

echo "\n6. Expected behavior:\n";
echo "   - {$selectedHandler} should handle the callback\n";
echo "   - It should show the main tabel menu\n";

echo "\n=== Test Complete ===\n";