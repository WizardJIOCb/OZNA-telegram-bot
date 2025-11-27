<?php
require_once __DIR__ . '/Custome/DB.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Проверка текущих уровней доступа пользователей ===\n\n";

// Инициализация сервисов
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Список тестовых пользователей (включая суперадминов)
$testUsers = [
    230379668,  // Главный админ
    5261465966, // Боря
    1090118386, // Близи
    444234398,  // Олег olegpysh
    // Добавьте сюда ID пользователей, которые подавали заявки
];

echo "Проверка уровней доступа для пользователей:\n";
echo str_repeat("-", 50) . "\n";

foreach ($testUsers as $userId) {
    $accessLevel = $adminService->getUserAccessLevel($userId);
    $levelName = $adminService->getAccessLevelName($accessLevel);
    $isSuperAdmin = $adminService->isSuperAdmin($userId) ? "Да" : "Нет";
    
    echo "User ID: " . str_pad($userId, 12) . 
         "Уровень: " . str_pad($levelName . " ($accessLevel)", 25) . 
         "Суперадмин: $isSuperAdmin\n";
}

echo "\n" . str_repeat("-", 50) . "\n";
echo "Проверка заявок на доступ:\n";
echo str_repeat("-", 50) . "\n";

$accessRequests = $adminService->getAllAccessRequests();
if (empty($accessRequests)) {
    echo "Нет активных заявок на доступ.\n";
} else {
    echo "Активные заявки на доступ:\n";
    foreach ($accessRequests as $request) {
        echo "User ID: " . $request['user_id'] . 
             ", Имя: " . $request['user_name'] . 
             ", Дата: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
    }
}

echo "\n=== Проверка завершена ===\n";
?>