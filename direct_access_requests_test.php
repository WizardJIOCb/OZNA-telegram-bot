<?php
/**
 * Direct test of access requests functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Direct Access Requests Test ===\n\n";

// Function to get all access requests from file
function getAllAccessRequests(): array {
    $accessRequestsFile = __DIR__ . '/data/access_requests.json';
    if (file_exists($accessRequestsFile)) {
        $jsonData = file_get_contents($accessRequestsFile);
        if ($jsonData !== false) {
            $requests = json_decode($jsonData, true);
            if (is_array($requests)) {
                return $requests;
            }
        }
    }
    return [];
}

// Function to get access request by user ID from file
function getAccessRequestByUserId(int $userId): ?array {
    $accessRequestsFile = __DIR__ . '/data/access_requests.json';
    if (file_exists($accessRequestsFile)) {
        $jsonData = file_get_contents($accessRequestsFile);
        if ($jsonData !== false) {
            $requests = json_decode($jsonData, true);
            if (is_array($requests)) {
                foreach ($requests as $request) {
                    if (isset($request['user_id']) && (int)$request['user_id'] === $userId) {
                        return $request;
                    }
                }
            }
        }
    }
    return null;
}

// Function to show access requests list (similar to AdminHandler)
function showAccessRequestsList(): array {
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
    
    return $result;
}

// Test the function
echo "Testing showAccessRequestsList...\n";
$result = showAccessRequestsList();

echo "Text:\n";
echo $result['text'] . "\n";

echo "Keyboard:\n";
foreach ($result['keyboard'] as $row) {
    foreach ($row as $button) {
        echo "  Button: " . $button['text'] . " -> " . $button['callback_data'] . "\n";
    }
}

echo "\n=== Test Complete ===\n";