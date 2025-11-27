<?php

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Создаем тестовый объект
$messageService = new MessageService();

// Тест создания клавиатуры главного меню
echo "Testing main menu keyboard creation:\n";
$keyboard = $messageService->createMainMenuReplyKeyboard();
echo "Main menu keyboard:\n";
print_r($keyboard);

echo "\n" . str_repeat("-", 50) . "\n";

// Тест создания клавиатуры для гостей
echo "Testing guest menu keyboard creation:\n";
$guestKeyboard = [
    [
        ['text' => '👤 Профиль'], 
        ['text' => '❓ Помощь']
    ]
];
echo "Guest menu keyboard:\n";
print_r($guestKeyboard);

echo "\n" . str_repeat("-", 50) . "\n";

// Проверим форматирование JSON для reply-клавиатуры
echo "Testing JSON formatting for reply keyboard:\n";
$replyMarkup = json_encode(['keyboard' => $keyboard, 'resize_keyboard' => true], JSON_UNESCAPED_UNICODE);
echo "Reply markup JSON: {$replyMarkup}\n";