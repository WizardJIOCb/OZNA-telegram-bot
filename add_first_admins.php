<?php
require_once __DIR__ . '/Custome/DB.php';
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Добавление первых администраторов ===\n\n";

// Инициализация сервисов
$messageService = new MessageService();
$adminService = new AdminService($messageService);

// Список первых администраторов (замените на реальные ID пользователей)
$firstAdmins = [
    // [user_id, user_name, chat_id]
    [123456789, "Иванов Иван", "987654321"],
    [111111111, "Петров Петр", "222222222"],
    // Добавьте сюда реальных пользователей
];

echo "Добавление администраторов:\n";
echo str_repeat("-", 50) . "\n";

foreach ($firstAdmins as $admin) {
    $userId = $admin[0];
    $userName = $admin[1];
    $chatId = $admin[2];
    
    echo "Добавление администратора: $userName (ID: $userId)\n";
    
    // Обновляем уровень доступа до администратора
    $result = $adminService->updateUserAccessLevel($userId, DatabaseService::ACCESS_LEVEL_ADMIN);
    
    if ($result) {
        echo "✅ Уровень доступа успешно обновлен до Администратора\n";
        
        // Отправляем уведомление пользователю
        $message = "✅ <b>Доступ предоставлен</b>\n\n";
        $message .= "Вам предоставлен доступ к административным функциям бота.\n";
        $message .= "Теперь вы можете использовать все функции админки.";
        
        try {
            $messageService->sendMessage($chatId, $message);
            echo "✅ Уведомление отправлено пользователю\n";
        } catch (Exception $e) {
            echo "⚠️  Не удалось отправить уведомление: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Ошибка при обновлении уровня доступа\n";
    }
    
    echo "\n";
}

echo "=== Добавление администраторов завершено ===\n";

// Проверка текущих администраторов
echo "\nТекущие администраторы:\n";
echo str_repeat("-", 50) . "\n";

foreach ($firstAdmins as $admin) {
    $userId = $admin[0];
    $userName = $admin[1];
    
    $accessLevel = $adminService->getUserAccessLevel($userId);
    $levelName = $adminService->getAccessLevelName($accessLevel);
    
    echo "$userName (ID: $userId) - $levelName ($accessLevel)\n";
}
?>