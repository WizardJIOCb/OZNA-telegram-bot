<?php

/**
 * Тестовый скрипт для проверки функциональности админки
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

echo "Тестирование функциональности админки...\n";

try {
    // Создаем сервисы
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "✅ Сервисы успешно созданы\n";
    
    // Проверяем методы AdminService
    $projects = $adminService->getAllProjects();
    echo "✅ Получен список проектов: " . count($projects) . " проектов\n";
    
    $requests = $adminService->getAllAccessRequests();
    echo "✅ Получен список заявок на доступ: " . count($requests) . " заявок\n";
    
    // Проверяем, является ли пользователь супер-админом (замените на свой ID)
    $userId = 230379668; // Ваш ID
    $isSuperAdmin = $adminService->isSuperAdmin($userId);
    echo "✅ Проверка супер-админа для пользователя {$userId}: " . ($isSuperAdmin ? 'Да' : 'Нет') . "\n";
    
    // Проверяем уровень доступа пользователя
    $accessLevel = $adminService->getUserAccessLevel($userId);
    echo "✅ Уровень доступа пользователя {$userId}: {$accessLevel}\n";
    
    echo "✅ Все тесты пройдены успешно!\n";
    echo "Теперь можно протестировать бота в Telegram\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка при тестировании: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}