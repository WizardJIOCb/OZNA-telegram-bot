<?php

echo "Quick menu test\n";
echo "===============\n\n";

// Подключаем необходимые файлы
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

try {
    // Создаем сервис сообщений
    $messageService = new MessageService();
    
    echo "1. Testing main menu creation...\n";
    $mainMenu = $messageService->createMainMenuReplyKeyboard();
    echo "✓ Main menu created successfully\n";
    echo "Menu structure:\n";
    print_r($mainMenu);
    
    echo "\n2. Testing guest menu creation...\n";
    $guestMenu = [
        [
            ['text' => '👤 Профиль'], 
            ['text' => '❓ Помощь']
        ]
    ];
    echo "✓ Guest menu created successfully\n";
    echo "Menu structure:\n";
    print_r($guestMenu);
    
    echo "\n3. Testing reply markup formatting...\n";
    $replyMarkup = [
        'keyboard' => $mainMenu,
        'resize_keyboard' => true,
        'one_time_keyboard' => false
    ];
    $json = json_encode($replyMarkup, JSON_UNESCAPED_UNICODE);
    echo "✓ Reply markup formatted successfully\n";
    echo "JSON:\n";
    echo $json . "\n";
    
    echo "\n4. Testing message sending simulation...\n";
    $testData = [
        'chat_id' => 123456789,
        'text' => "<b>🏠 Главное меню</b>\r\n\r\n🔄 Для вызова этого меню отправьте /start",
        'reply_markup' => $replyMarkup,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    echo "✓ Message data prepared successfully\n";
    echo "Message data:\n";
    print_r($testData);
    
    echo "\nQuick menu test completed successfully!\n";
    echo "The menu should display correctly in the bot.\n";
    
} catch (Exception $e) {
    echo "Error during quick menu test: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}