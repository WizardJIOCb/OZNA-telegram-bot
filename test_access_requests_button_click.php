<?php
/**
 * Test script to simulate clicking the "Заявки на доступ" button
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Access Requests Button Click ===\n\n";

// Function to simulate the callback data that would be sent by the button
$callbackData = 'category_Админка_Заявки';

echo "Simulating button click with callback data: '{$callbackData}'\n";

// Function to simulate AdminHandler::canHandle
function adminHandlerCanHandle(string $callbackData): bool {
    echo "AdminHandler::canHandle called with: '{$callbackData}'\n";
    
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
    
    echo "  startsWithCategoryAdmin: " . ($startsWithCategoryAdmin ? 'true' : 'false') . "\n";
    echo "  startsWithCategoryAdm: " . ($startsWithCategoryAdm ? 'true' : 'false') . "\n";
    echo "  equalsCategoryMain: " . ($equalsCategoryMain ? 'true' : 'false') . "\n";
    echo "  startsWithAdmReq: " . ($startsWithAdmReq ? 'true' : 'false') . "\n";
    echo "  isAdmReqCallback: " . ($isAdmReqCallback ? 'true' : 'false') . "\n";
    echo "  isAccessLevelCallback: " . ($isAccessLevelCallback ? 'true' : 'false') . "\n";
    echo "  canHandle result: " . ($canHandle ? 'true' : 'false') . "\n";
    
    return $canHandle;
}

// Function to get all access requests from file (similar to AdminService)
function getAllAccessRequests(): array {
    $accessRequestsFile = __DIR__ . '/data/access_requests.json';
    echo "Reading access requests from file: {$accessRequestsFile}\n";
    
    if (file_exists($accessRequestsFile)) {
        echo "File exists\n";
        $jsonData = file_get_contents($accessRequestsFile);
        if ($jsonData !== false) {
            echo "File content read successfully\n";
            $requests = json_decode($jsonData, true);
            if (is_array($requests)) {
                echo "JSON decoded successfully, found " . count($requests) . " requests\n";
                return $requests;
            } else {
                echo "Failed to decode JSON\n";
            }
        } else {
            echo "Failed to read file content\n";
        }
    } else {
        echo "File does not exist\n";
    }
    
    return [];
}

// Function to simulate showAccessRequestsList (similar to AdminHandler)
function showAccessRequestsList(): array {
    echo "showAccessRequestsList called\n";
    
    $requests = getAllAccessRequests();
    
    $result = [
        'text' => '',
        'keyboard' => []
    ];
    
    // Create text message
    $result['text'] = "👥 <b>Заявки на доступ к боту</b>\n\n";
    $result['text'] .= "Всего заявок: " . count($requests) . "\n\n";
    
    if (empty($requests)) {
        $result['text'] .= "Нет новых заявок на доступ.";
        $result['keyboard'] = [
            [
                ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
            ]
        ];
    } else {
        $result['text'] .= "Выберите заявку для просмотра:\n\n";
        
        // Create keyboard with buttons for each request
        $keyboard = [];
        foreach ($requests as $index => $request) {
            $userName = $request['user_name'] ?? 'Неизвестный пользователь';
            $userId = $request['user_id'];
            
            // Short callback_data to avoid Telegram limit (64 bytes)
            $shortCallbackData = "adm_req_view_{$userId}";
            $keyboard[] = [
                ['text' => $userName, 'callback_data' => $shortCallbackData]
            ];
            
            // Limit number of requests in list
            if ($index >= 20) {
                $result['text'] .= "\n⚠️ Показаны только первые 20 заявок. Остальные скрыты.";
                break;
            }
        }
        
        // Add back button
        $keyboard[] = [
            [
                ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
            ]
        ];
        
        $result['keyboard'] = $keyboard;
    }
    
    echo "showAccessRequestsList returning text with " . count($result['keyboard']) . " keyboard rows\n";
    return $result;
}

// Function to simulate AdminHandler::handle with category 'Админка_Заявки'
function handleAdminRequests(): array {
    echo "handleAdminRequests called\n";
    
    // Simulate user ID (should be an admin)
    $userId = 5261465966;
    echo "User ID: {$userId}\n";
    
    // Check if user has admin access (for testing, assume they do)
    $userHasAdminAccess = true;
    echo "User has admin access: " . ($userHasAdminAccess ? 'true' : 'false') . "\n";
    
    if ($userHasAdminAccess) {
        echo "Routing to showAccessRequestsList\n";
        return showAccessRequestsList();
    } else {
        echo "User does not have admin access\n";
        return [
            'text' => "У вас нет доступа к этой функции",
            'keyboard' => []
        ];
    }
}

// Test if AdminHandler can handle this callback
$canHandle = adminHandlerCanHandle($callbackData);
echo "\nAdminHandler can handle this callback: " . ($canHandle ? 'YES' : 'NO') . "\n";

if ($canHandle) {
    echo "\nProcessing the callback...\n";
    $result = handleAdminRequests();
    
    echo "\nResult text:\n";
    echo $result['text'] . "\n";
    
    echo "\nResult keyboard:\n";
    foreach ($result['keyboard'] as $rowIndex => $row) {
        foreach ($row as $buttonIndex => $button) {
            if (isset($button['text']) && isset($button['callback_data'])) {
                echo "  Button: " . $button['text'] . " -> " . $button['callback_data'] . "\n";
            } else {
                echo "  Invalid button at [{$rowIndex}][{$buttonIndex}]\n";
            }
        }
    }
} else {
    echo "\nAdminHandler cannot handle this callback\n";
}

echo "\n=== Test Complete ===\n";