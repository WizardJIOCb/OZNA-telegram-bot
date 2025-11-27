<?php
require_once __DIR__ . '/Custome/DB.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Тестирование механизма одобрения заявок на доступ ===\n\n";

// Инициализация сервисов
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Тестовый пользователь (замените на реальный ID пользователя, который подал заявку)
$testUserId = 123456789; // ID из файла access_requests.json
$testUserName = "Тестовый пользователь";
$testUserChatId = "987654321";

echo "Тестирование одобрения заявки для пользователя:\n";
echo "User ID: $testUserId\n";
echo "Имя: $testUserName\n";
echo "Chat ID: $testUserChatId\n\n";

// Проверка текущего уровня доступа пользователя
$currentLevel = $adminService->getUserAccessLevel($testUserId);
$currentLevelName = $adminService->getAccessLevelName($currentLevel);
echo "Текущий уровень доступа: $currentLevelName ($currentLevel)\n";

// Одобрение заявки на доступ
echo "\nПопытка одобрения заявки...\n";
$result = $adminService->approveAccessRequest($testUserId, $testUserName, $testUserChatId);

if ($result) {
    echo "✅ Заявка успешно одобрена!\n";
    
    // Проверка нового уровня доступа пользователя
    $newLevel = $adminService->getUserAccessLevel($testUserId);
    $newLevelName = $adminService->getAccessLevelName($newLevel);
    echo "Новый уровень доступа: $newLevelName ($newLevel)\n";
    
    // Проверка, что заявка удалена
    $accessRequests = $adminService->getAllAccessRequests();
    $requestFound = false;
    foreach ($accessRequests as $request) {
        if ($request['user_id'] == $testUserId) {
            $requestFound = true;
            break;
        }
    }
    
    if ($requestFound) {
        echo "⚠️  Заявка не была удалена из списка!\n";
    } else {
        echo "✅ Заявка успешно удалена из списка.\n";
    }
} else {
    echo "❌ Ошибка при одобрении заявки.\n";
}

echo "\n=== Тестирование завершено ===\n";
?>