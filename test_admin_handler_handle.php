<?php
/**
 * Test the AdminHandler::handle method functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Admin Handler Handle ===\n\n";

// Function to get user access level (mock)
function getUserAccessLevel(int $userId): int {
    // For testing, assume user is admin
    return 3; // ACCESS_LEVEL_ADMIN
}

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

// Function to simulate AdminHandler::handle
function handle(string $category): array {
    $userId = 5261465966; // Test user ID (should be admin)
    
    echo "handle called with category: '{$category}' for user: {$userId}\n";
    
    // Special case: Allow access request even for non-admins
    if ($category === 'Админка_ЗапросДоступ') {
        echo "handling access request for user: {$userId}\n";
        return [
            'text' => "Запрос доступа отправлен",
            'keyboard' => []
        ];
    }

    // Special case: Allow adm_req_* callbacks to be processed regardless of access level
    // These are access request management callbacks that should be available to admins
    if (strpos($category, 'adm_req_') === 0) {
        echo "handling adm_req callback: '{$category}' for user: {$userId}\n";
        // In a real implementation, we would handle this here
        return [
            'text' => "Обработка заявки: {$category}",
            'keyboard' => []
        ];
    }

    // Special case: "Главное" category should be handled by MainMenuHandler
    if ($category === 'Главное') {
        echo "redirecting to main menu for user: {$userId}\n";
        return [
            'text' => "Главное меню",
            'keyboard' => []
        ];
    }

    // Check user access level
    $userAccessLevel = getUserAccessLevel($userId);
    echo "user access level: {$userAccessLevel} for user: {$userId}\n";
    
    // Check if user has access to admin panel (admin level or higher)
    if ($userAccessLevel < 3) { // ACCESS_LEVEL_ADMIN
        echo "user {$userId} does not have admin access, showing access request menu\n";
        // Show menu with "Request Access" button
        return [
            'text' => "Для получения доступа к административным функциям подайте заявку.",
            'keyboard' => [
                [
                    ['text' => '📝 Запросить доступ', 'callback_data' => 'category_Админка_ЗапросДоступ']
                ]
            ]
        ];
    }

    // Route for admins and super admins
    echo "routing admin callback for user: {$userId}\n";
    return routeAdminCallback($category, $userId);
}

// Function to simulate AdminHandler::routeAdminCallback
function routeAdminCallback(string $category, int $userId): array {
    echo "routeAdminCallback called with category: '{$category}' for user: {$userId}\n";

    // Return to main menu
    if ($category === 'Главное') {
        echo "routing to main menu for user: {$userId}\n";
        return [
            'text' => "Главное меню",
            'keyboard' => []
        ];
    }

    // Admin main menu
    if ($category === 'Админка') {
        echo "showing admin main menu for user: {$userId}\n";
        return [
            'text' => "Панель администратора",
            'keyboard' => [
                [
                    ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
                ],
                [
                    ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
                ]
            ]
        ];
    }
    
    // Access requests
    if ($category === 'Админка_Заявки') {
        echo "handling access requests for user: {$userId}\n";
        return showAccessRequestsList();
    }
    
    // Check prefix for access requests
    if (strpos($category, 'Админка_Заявки') === 0) {
        echo "handling access requests with prefix for user: {$userId}\n";
        return showAccessRequestsList();
    }
    
    // Access request (for regular users)
    if ($category === 'Админка_ЗапросДоступ') {
        echo "handling access request for user: {$userId}\n";
        return [
            'text' => "Запрос доступа отправлен",
            'keyboard' => []
        ];
    }

    $text = 'Неизвестная команда админки';
    echo "unknown admin command: '{$category}' for user: {$userId}\n";
    return [
        'text' => $text,
        'keyboard' => []
    ];
}

// Test with the category that should be triggered by "Заявки на доступ" button
$callbackData = 'Админка_Заявки'; // This is what gets extracted from 'category_Админка_Заявки'
echo "Testing handle with: '{$callbackData}'\n";
$result = handle($callbackData);

echo "Result text:\n";
echo $result['text'] . "\n";

if (!empty($result['keyboard'])) {
    echo "Result keyboard:\n";
    foreach ($result['keyboard'] as $row) {
        foreach ($row as $button) {
            echo "  Button: " . $button['text'] . " -> " . $button['callback_data'] . "\n";
        }
    }
}

echo "\n=== Test Complete ===\n";