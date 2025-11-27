<?php
/**
 * Test script to simulate showAccessRequestsList function (fixed version)
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test Show Access Requests List (Fixed) ===\n\n";

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
    
    // Инициализируем клавиатуру
    $keyboard = [];
    
    if (empty($requests)) {
        $result['text'] .= "Нет новых заявок на доступ.";
        $keyboard = [
            [
                ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
            ]
        ];
    } else {
        $result['text'] .= "Выберите заявку для просмотра:\n\n";
        
        // Создаем клавиатуру с кнопками для каждой заявки
        $keyboard = [];
        foreach ($requests as $index => $request) {
            $userName = $request['user_name'] ?? 'Неизвестный пользователь';
            $userId = $request['user_id'];
            
            // ВАЖНО: Используем сокращенный callback_data чтобы не превысить лимит Telegram (64 байта)
            // Формат: adm_req_view_{userId}
            $shortCallbackData = "adm_req_view_{$userId}";
            $keyboard[] = [
                ['text' => $userName, 'callback_data' => $shortCallbackData]
            ];
            
            // Ограничиваем количество заявок в списке (для предотвращения превышения лимита Telegram)
            if ($index >= 20) {
                $result['text'] .= "\n⚠️ Показаны только первые 20 заявок. Остальные скрыты.";
                break;
            }
        }
        
        // Добавляем кнопку "Назад"
        $keyboard[] = [
            [
                ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
            ]
        ];
    }
    
    $result['keyboard'] = $keyboard;
    
    echo "showAccessRequestsList returning:\n";
    echo "  Text length: " . strlen($result['text']) . " characters\n";
    echo "  Keyboard rows: " . count($result['keyboard']) . "\n";
    
    // Count total buttons
    $totalButtons = 0;
    foreach ($result['keyboard'] as $row) {
        $totalButtons += count($row);
    }
    echo "  Total buttons: " . $totalButtons . "\n";
    
    return $result;
}

// Test the function
$result = showAccessRequestsList();

echo "\nResult text:\n";
echo $result['text'] . "\n";

echo "\nResult keyboard:\n";
foreach ($result['keyboard'] as $rowIndex => $row) {
    echo "  Row {$rowIndex}:\n";
    foreach ($row as $buttonIndex => $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            echo "    Button {$buttonIndex}: " . $button['text'] . " -> " . $button['callback_data'] . "\n";
        } else {
            echo "    Invalid button at [{$rowIndex}][{$buttonIndex}]\n";
            print_r($button);
        }
    }
}

// Validate the result
echo "\nValidation:\n";
$isValid = true;

if (empty($result['text'])) {
    echo "  ❌ Text is empty\n";
    $isValid = false;
} else {
    echo "  ✅ Text is not empty\n";
}

if (empty($result['keyboard'])) {
    echo "  ❌ Keyboard is empty\n";
    $isValid = false;
} else {
    echo "  ✅ Keyboard is not empty\n";
}

// Check if all buttons have text and callback_data
$buttonCount = 0;
$invalidButtonCount = 0;
foreach ($result['keyboard'] as $rowIndex => $row) {
    foreach ($row as $buttonIndex => $button) {
        $buttonCount++;
        if (!isset($button['text']) || !isset($button['callback_data'])) {
            echo "  ❌ Button [{$rowIndex}][{$buttonIndex}] is invalid\n";
            $invalidButtonCount++;
            $isValid = false;
        }
    }
}

echo "  Total buttons checked: {$buttonCount}\n";
echo "  Invalid buttons: {$invalidButtonCount}\n";

if ($isValid) {
    echo "\n✅ All validation checks passed!\n";
} else {
    echo "\n❌ Some validation checks failed!\n";
}

echo "\n=== Test Complete ===\n";