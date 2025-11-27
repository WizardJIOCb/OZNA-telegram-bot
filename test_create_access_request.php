<?php

/**
 * Тестовый скрипт для создания тестовой заявки на доступ
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes since they're not autoloaded
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/CustomCommands/Services/CallbackHandlerFactory.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/ProjectSelector.php';
require_once __DIR__ . '/CustomCommands/Services/TabelEntry.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/FunctionalDirectionHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/ProfileHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/HelpHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AdminHandler.php';
require_once __DIR__ . '/CustomCommands/Handlers/AccessLevelHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Создание тестовой заявки на доступ...\n";

try {
    // Создаем сервисы
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "✅ Сервисы успешно созданы\n";
    
    // Создаем тестовую заявку на доступ
    $userId = 123456789;
    $userName = "Тестовый Пользователь";
    $chatId = "123456789";
    
    // Отправляем заявку на доступ
    $success = $adminService->sendAccessRequest($userId, $userName, $chatId);
    
    if ($success) {
        echo "✅ Тестовая заявка на доступ успешно создана\n";
    } else {
        echo "❌ Ошибка при создании тестовой заявки на доступ\n";
    }
    
    // Проверяем список заявок
    $requests = $adminService->getAllAccessRequests();
    echo "✅ Всего заявок на доступ: " . count($requests) . "\n";
    
    foreach ($requests as $index => $request) {
        echo "  Заявка #{$index}:\n";
        echo "    User ID: " . $request['user_id'] . "\n";
        echo "    User Name: " . $request['user_name'] . "\n";
        echo "    Chat ID: " . $request['chat_id'] . "\n";
        echo "    Timestamp: " . date('Y-m-d H:i:s', $request['timestamp']) . "\n";
    }
    
    echo "✅ Тест завершен успешно!\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}