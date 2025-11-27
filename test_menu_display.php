<?php

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Создаем сервис сообщений
$messageService = new MessageService();

// Тест создания клавиатуры главного меню
echo "Testing main menu keyboard creation:\n";
try {
    $keyboard = $messageService->createMainMenuReplyKeyboard();
    echo "Main menu keyboard created successfully:\n";
    print_r($keyboard);
    
    // Проверяем форматирование JSON
    $replyMarkup = [
        'keyboard' => $keyboard,
        'resize_keyboard' => true,
        'one_time_keyboard' => false
    ];
    $json = json_encode($replyMarkup, JSON_UNESCAPED_UNICODE);
    echo "\nJSON formatted keyboard:\n";
    echo $json . "\n";
    
} catch (Exception $e) {
    echo "Error creating main menu keyboard: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n";

// Тест создания гостевой клавиатуры
echo "Testing guest menu keyboard creation:\n";
$guestKeyboard = [
    [
        ['text' => '👤 Профиль'], 
        ['text' => '❓ Помощь']
    ]
];
echo "Guest menu keyboard:\n";
print_r($guestKeyboard);

// Проверяем форматирование JSON для гостевой клавиатуры
$guestReplyMarkup = [
    'keyboard' => $guestKeyboard,
    'resize_keyboard' => true,
    'one_time_keyboard' => false
];
$guestJson = json_encode($guestReplyMarkup, JSON_UNESCAPED_UNICODE);
echo "\nJSON formatted guest keyboard:\n";
echo $guestJson . "\n";