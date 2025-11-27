<?php
// Тестовый скрипт для проверки метода showAccessRequestsList

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем необходимые файлы
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;

echo "Начинаем тест метода showAccessRequestsList...\n";

try {
    // Создаем экземпляры сервисов
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Создаем фиктивный AdminHandler (нам нужен только метод showAccessRequestsList)
    $adminHandler = new AdminHandler($adminService, $messageService);
    
    // Используем отражение для вызова приватного метода
    $reflection = new ReflectionClass($adminHandler);
    $method = $reflection->getMethod('showAccessRequestsList');
    $method->setAccessible(true);
    
    // Тестируем метод с вашим chat_id
    $chatId = '5261465966'; // Ваш ID пользователя
    echo "Вызываем showAccessRequestsList для chat_id: {$chatId}\n";
    
    $result = $method->invoke($adminHandler, $chatId);
    
    echo "Результат выполнения метода:\n";
    print_r($result);
    
    echo "Тест завершен!\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>