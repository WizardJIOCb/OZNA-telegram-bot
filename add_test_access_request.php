<?php
// Скрипт для добавления тестовой заявки на доступ

require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Создаем экземпляры сервисов
$databaseService = new DatabaseService();
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Добавляем тестовую заявку
$testRequest = [
    'user_id' => 123456789,
    'user_name' => 'Тестовый пользователь',
    'user_telegram_name' => 'Test User',
    'user_fio' => 'Иванов Иван Иванович',
    'chat_id' => '987654321',
    'timestamp' => time()
];

// Получаем текущие заявки
$accessRequests = $adminService->getAllAccessRequests();

// Добавляем тестовую заявку
$accessRequests[] = $testRequest;

// Сохраняем обновленный список заявок в файл
$filePath = __DIR__ . '/data/access_requests.json';
$jsonData = json_encode($accessRequests, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($filePath, $jsonData);

echo "Тестовая заявка успешно добавлена!\n";
echo "Проверьте работу кнопки 'Заявки на доступ' в админке.\n";
?>