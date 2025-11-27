<?php
/**
 * Test to check how the routeAdminCallback method processes the callback
 */

echo "=== Route Admin Callback Test ===\n\n";

// Simulate the routeAdminCallback method logic
function routeAdminCallback($category) {
    echo "Routing admin callback with category: '{$category}'\n";
    
    // Возврат в главное меню
    if ($category === 'Главное') {
        echo "  Would call showMainMenu\n";
        return 'showMainMenu';
    }

    // Главное меню админки
    if ($category === 'Админка') {
        echo "  Would call showAdminMainMenu\n";
        return 'showAdminMainMenu';
    }
    
    // Удаление конкретного проекта (сокращенный формат)
    if (str_starts_with($category, 'Адм_УдПр_')) {
        echo "  Would call handleDeleteSpecificProject\n";
        return 'handleDeleteSpecificProject';
    }

    // Удаление конкретного проекта (старый формат для совместимости)
    if (str_starts_with($category, 'Админка_УдалитьПроект_')) {
        echo "  Would call handleDeleteSpecificProject (old format)\n";
        return 'handleDeleteSpecificProject';
    }

    // Подтверждение удаления проекта (сокращенный callback)
    if (str_starts_with($category, 'Админка_ПодтвУдал_')) {
        echo "  Would call handleConfirmDeleteProject\n";
        return 'handleConfirmDeleteProject';
    }

    // Управление проектами
    if (str_starts_with($category, 'Админка_Проекты')) {
        echo "  Would call handleProjectManagement\n";
        return 'handleProjectManagement';
    }

    // Заявки на доступ
    if ($category === 'Админка_Заявки') {
        echo "  Would call handleAccessRequests\n";
        return 'handleAccessRequests';
    }
    
    // Проверяем префикс для заявок на доступ
    if (str_starts_with($category, 'Админка_Заявки')) {
        echo "  Would call handleAccessRequests (with prefix)\n";
        return 'handleAccessRequests';
    }
    
    // Запрос доступа (для обычных пользователей)
    if ($category === 'Админка_ЗапросДоступ') {
        echo "  Would call handleAccessRequest\n";
        return 'handleAccessRequest';
    }
    
    echo "  Unknown category - would show error message\n";
    return 'unknown';
}

// Test with the specific category we're interested in
$category = 'Админка_Заявки';
$result = routeAdminCallback($category);

echo "\nResult: {$result}\n";
echo "Expected: handleAccessRequests\n";
echo "Match: " . ($result === 'handleAccessRequests' ? 'YES' : 'NO') . "\n\n";

// Test the handleAccessRequests method logic
function handleAccessRequests($category) {
    echo "Handling access requests with category: '{$category}'\n";
    
    // Проверяем подкатегорию
    if ($category === 'Админка_Заявки') {
        echo "  Would call showAccessRequestsList\n";
        return 'showAccessRequestsList';
    }
    
    echo "  Would handle other access request categories\n";
    return 'other';
}

// Test handleAccessRequests
$requestsResult = handleAccessRequests($category);
echo "handleAccessRequests result: {$requestsResult}\n";
echo "Expected: showAccessRequestsList\n";
echo "Match: " . ($requestsResult === 'showAccessRequestsList' ? 'YES' : 'NO') . "\n\n";

// Test the showAccessRequestsList method logic
function showAccessRequestsList() {
    echo "Showing access requests list\n";
    
    // Simulate reading from access_requests.json
    $accessRequestsFile = __DIR__ . '/data/access_requests.json';
    
    if (file_exists($accessRequestsFile)) {
        $content = file_get_contents($accessRequestsFile);
        $requests = json_decode($content, true);
        
        if (is_array($requests)) {
            echo "  Found " . count($requests) . " access requests\n";
            
            if (!empty($requests)) {
                echo "  Would create keyboard with buttons for each request:\n";
                foreach ($requests as $index => $request) {
                    $userName = $request['user_name'] ?? 'Неизвестный пользователь';
                    $userId = $request['user_id'];
                    $shortCallbackData = "adm_req_view_{$userId}";
                    echo "    Button: '{$userName}' -> '{$shortCallbackData}'\n";
                    
                    // Limit to first 3 for display
                    if ($index >= 2) {
                        echo "    ...\n";
                        break;
                    }
                }
            } else {
                echo "  No requests found\n";
            }
        } else {
            echo "  Error parsing access requests file\n";
        }
    } else {
        echo "  Access requests file not found\n";
    }
    
    echo "  Would add 'Назад в админку' button\n";
    echo "  Would send message with keyboard\n";
    
    return 'message_sent';
}

// Test showAccessRequestsList
$listResult = showAccessRequestsList();
echo "\nshowAccessRequestsList result: {$listResult}\n";

echo "\n=== Test Complete ===\n";