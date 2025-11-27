<?php
// Тестовый скрипт для проверки отправки сообщения

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем необходимые файлы
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Начинаем тест отправки сообщения...\n";

try {
    // Создаем экземпляр сервиса сообщений
    $messageService = new MessageService();
    
    // Тестовое сообщение
    $chatId = '5261465966'; // Ваш ID пользователя
    $text = "👥 <b>Тестовое сообщение</b>\n\nПроверка отправки сообщения с заявками на доступ к админке.";
    
    echo "Отправляем сообщение в чат: {$chatId}\n";
    echo "Текст сообщения:\n{$text}\n\n";
    
    // Отправляем сообщение
    $response = $messageService->sendMessage($chatId, $text);
    
    echo "Ответ от Telegram API:\n";
    print_r($response);
    
    echo "Тест завершен!\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>