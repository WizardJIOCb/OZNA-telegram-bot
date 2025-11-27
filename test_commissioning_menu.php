<?php
// Test script to check the commissioning works menu functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Testing Commissioning Works Menu ===\n";

// Test creating the commissioning works keyboard
$messageService = new MessageService();
$keyboard = $messageService->createCommissioningWorksKeyboard();

echo "Commissioning works keyboard created:\n";
echo "Number of rows: " . count($keyboard) . "\n";

// Display each row and button
foreach ($keyboard as $rowIndex => $row) {
    echo "Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $buttonIndex => $button) {
        echo "  Button " . ($buttonIndex + 1) . ": " . $button['text'] . "\n";
        if (isset($button['callback_data'])) {
            echo "    Callback: " . $button['callback_data'] . "\n";
        }
    }
}

// Test the handleCommissioningWorksMenu function simulation
function handleCommissioningWorksMenu($chatId) {
    global $messageService;
    
    echo "In handleCommissioningWorksMenu for chatId: $chatId\n";
    
    $keyboard = $messageService->createCommissioningWorksKeyboard();
    $text = '<b>⚙️ Пусконаладочные работы</b>\n\nВыберите тип оборудования:';
    
    echo "Message text: $text\n";
    echo "Keyboard rows: " . count($keyboard) . "\n";
    
    // Simulate sending message
    echo "Would send message to chat $chatId with " . count($keyboard) . " rows in keyboard\n";
    
    return "Success";
}

// Test the function
$result = handleCommissioningWorksMenu("test_chat_id");
echo "Function result: $result\n";

echo "\nTest completed!\n";
?>