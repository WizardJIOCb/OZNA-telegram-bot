<?php
// Test script to verify the access requests keyboard structure

// Read the access requests file directly
$filePath = __DIR__ . '/data/access_requests.json';
$content = file_get_contents($filePath);
$requests = json_decode($content, true);

echo "Total requests found: " . count($requests) . "\n";

// Simulate the showAccessRequestsList method keyboard generation
function simulateShowAccessRequestsList($requests) {
    $keyboard = [];
    
    if (empty($requests)) {
        $keyboard = [
            [
                ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
            ]
        ];
    } else {
        // Используем массив для отслеживания уже добавленных пользователей
        $addedUsers = [];
        
        foreach ($requests as $index => $request) {
            $userId = $request['user_id'];
            $userName = $request['user_name'] ?? 'Неизвестный пользователь';
            
            // Пропускаем дубликаты - добавляем только первого пользователя с каждым ID
            if (in_array($userId, $addedUsers)) {
                continue;
            }
            
            $addedUsers[] = $userId;
            
            // ВАЖНО: Используем сокращенный callback_data чтобы не превысить лимит Telegram (64 байта)
            // Формат: adm_req_view_{userId}
            $shortCallbackData = "adm_req_view_{$userId}";
            $keyboard[] = [
                ['text' => $userName, 'callback_data' => $shortCallbackData]
            ];
            
            // Ограничиваем количество заявок в списке (для предотвращения превышения лимита Telegram)
            if (count($addedUsers) >= 20) {
                break;
            }
        }
        
        // Добавляем кнопку "Назад" - правильно структурированную
        $keyboard[] = [
            ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
        ];
    }
    
    return $keyboard;
}

// Test the keyboard structure
$keyboard = simulateShowAccessRequestsList($requests);

echo "Generated keyboard structure:\n";
echo json_encode($keyboard, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Validate keyboard structure
echo "\nValidating keyboard structure...\n";
$isValid = true;

foreach ($keyboard as $rowIndex => $row) {
    if (!is_array($row)) {
        echo "ERROR: Row $rowIndex is not an array\n";
        $isValid = false;
        continue;
    }
    
    foreach ($row as $buttonIndex => $button) {
        if (!is_array($button)) {
            echo "ERROR: Button at row $rowIndex, position $buttonIndex is not an array\n";
            $isValid = false;
            continue;
        }
        
        if (!isset($button['text']) || !isset($button['callback_data'])) {
            echo "ERROR: Button at row $rowIndex, position $buttonIndex is missing required fields\n";
            $isValid = false;
            continue;
        }
    }
}

if ($isValid) {
    echo "SUCCESS: Keyboard structure is valid\n";
    
    // Test JSON encoding for Telegram API
    $replyMarkup = ['inline_keyboard' => $keyboard];
    $json = json_encode($replyMarkup, JSON_UNESCAPED_UNICODE);
    
    echo "\nJSON for Telegram API:\n";
    echo $json . "\n";
    
    // Validate JSON
    $decoded = json_decode($json, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\nJSON validation: SUCCESS\n";
    } else {
        echo "\nJSON validation: ERROR - " . json_last_error_msg() . "\n";
    }
} else {
    echo "ERROR: Keyboard structure is invalid\n";
}

echo "\nTest completed.\n";