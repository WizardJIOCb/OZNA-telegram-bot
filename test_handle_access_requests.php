<?php
/**
 * Test the handleAccessRequests method functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Handle Access Requests ===\n\n";

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

// Function to simulate handleAccessRequests method
function handleAccessRequests(string $category): array {
    echo "Handling access requests with category: '{$category}'\n";
    
    // Get all requests
    $allRequests = getAllAccessRequests();
    echo "Retrieved " . count($allRequests) . " access requests\n";
    
    // Check subcategory
    if ($category === 'Админка_Заявки') {
        echo "Showing access requests list\n";
        // Show list of requests
        return showAccessRequestsList();
    }
    
    // Check if this is viewing a specific request (new format)
    if (strpos($category, 'adm_req_view_') === 0) {
        $requestUserId = str_replace('adm_req_view_', '', $category);
        echo "Showing access request details for user: {$requestUserId}\n";
        // In a real implementation, we would show details here
        return [
            'text' => "Детали заявки пользователя ID: {$requestUserId}",
            'keyboard' => [
                [
                    ['text' => '✅ Одобрить', 'callback_data' => "adm_req_approve_{$requestUserId}"],
                    ['text' => '❌ Отклонить', 'callback_data' => "adm_req_reject_{$requestUserId}"]
                ],
                [
                    ['text' => '⬅️ Назад к заявкам', 'callback_data' => 'category_Админка_Заявки']
                ]
            ]
        ];
    }
    
    // Check if this is approving a request (new format)
    if (strpos($category, 'adm_req_approve_') === 0) {
        $requestUserId = str_replace('adm_req_approve_', '', $category);
        echo "Approving access request for user: {$requestUserId}\n";
        // In a real implementation, we would approve here
        return [
            'text' => "✅ Заявка пользователя ID: {$requestUserId} одобрена",
            'keyboard' => [
                [
                    ['text' => '⬅️ Назад к заявкам', 'callback_data' => 'category_Админка_Заявки']
                ]
            ]
        ];
    }
    
    // Check if this is rejecting a request (new format)
    if (strpos($category, 'adm_req_reject_') === 0) {
        $requestUserId = str_replace('adm_req_reject_', '', $category);
        echo "Rejecting access request for user: {$requestUserId}\n";
        // In a real implementation, we would reject here
        return [
            'text' => "❌ Заявка пользователя ID: {$requestUserId} отклонена",
            'keyboard' => [
                [
                    ['text' => '⬅️ Назад к заявкам', 'callback_data' => 'category_Админка_Заявки']
                ]
            ]
        ];
    }
    
    $text = 'Неизвестная команда управления заявками';
    echo "Unknown access requests command: '{$category}'\n";
    return [
        'text' => $text,
        'keyboard' => []
    ];
}

// Test the function with the category that should be triggered by "Заявки на доступ" button
echo "Testing handleAccessRequests with 'Админка_Заявки'...\n";
$result = handleAccessRequests('Админка_Заявки');

echo "Result text:\n";
echo $result['text'] . "\n";

echo "Result keyboard:\n";
foreach ($result['keyboard'] as $row) {
    foreach ($row as $button) {
        echo "  Button: " . $button['text'] . " -> " . $button['callback_data'] . "\n";
    }
}

echo "\n=== Test Complete ===\n";