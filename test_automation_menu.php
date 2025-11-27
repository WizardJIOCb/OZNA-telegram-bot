<?php
// Test script to check the automation equipment menu functionality
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Testing Automation Equipment Menu ===\n";

// Test creating the automation equipment keyboard
$messageService = new MessageService();
$keyboard = $messageService->createAutomationEquipmentKeyboard();

echo "Automation equipment keyboard created:\n";
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

// Test the handleAutomationEquipmentMenu function simulation
function handleAutomationEquipmentMenu($chatId) {
    global $messageService;
    
    echo "In handleAutomationEquipmentMenu for chatId: $chatId\n";
    
    $keyboard = $messageService->createAutomationEquipmentKeyboard();
    $text = '<b>🤖 Автоматизация</b> Выберите тип работ:';
    
    echo "Message text: $text\n";
    echo "Keyboard rows: " . count($keyboard) . "\n";
    
    // Simulate sending message
    echo "Would send message to chat $chatId with " . count($keyboard) . " rows in keyboard\n";
    
    return "Success";
}

// Test the function
$result = handleAutomationEquipmentMenu("test_chat_id");
echo "Function result: $result\n";

echo "\nTest completed!\n";
?>