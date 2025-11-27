<?php
// Полный тест процесса обработки заявок на доступ

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

echo "Начинаем полный тест процесса обработки заявок на доступ...\n";

try {
    // Создаем экземпляры сервисов
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    // Проверяем загрузку заявок
    echo "\n1. Проверяем загрузку заявок из файла:\n";
    $requests = $adminService->getAllAccessRequests();
    echo "Загружено заявок: " . count($requests) . "\n";
    
    if (!empty($requests)) {
        foreach ($requests as $index => $request) {
            echo "Заявка #" . ($index + 1) . ":\n";
            foreach ($request as $key => $value) {
                echo "  {$key}: " . print_r($value, true) . "\n";
            }
        }
    }
    
    // Проверяем содержимое файла
    echo "\n2. Проверяем содержимое файла access_requests.json:\n";
    $filePath = __DIR__ . '/data/access_requests.json';
    if (file_exists($filePath)) {
        $fileContent = file_get_contents($filePath);
        echo "Содержимое файла:\n";
        echo $fileContent . "\n";
        
        $decoded = json_decode($fileContent, true);
        echo "Декодированный JSON:\n";
        print_r($decoded);
    } else {
        echo "Файл не существует!\n";
    }
    
    // Тестируем метод showAccessRequestsList
    echo "\n3. Тестируем метод showAccessRequestsList:\n";
    $adminHandler = new AdminHandler($adminService, $messageService);
    
    // Используем отражение для вызова приватного метода
    $reflection = new ReflectionClass($adminHandler);
    $method = $reflection->getMethod('showAccessRequestsList');
    $method->setAccessible(true);
    
    // Тестируем метод
    $chatId = '5261465966'; // Ваш ID пользователя
    echo "Вызываем showAccessRequestsList для chat_id: {$chatId}\n";
    
    $result = $method->invoke($adminHandler, $chatId);
    
    echo "Результат выполнения метода:\n";
    print_r($result);
    
    echo "\nПолный тест завершен!\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>