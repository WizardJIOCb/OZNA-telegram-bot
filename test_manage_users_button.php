<?php
// Специальный тест для кнопки "Управление пользователями"
require_once __DIR__ . '/CustomCommands/Services/DatabaseService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== ТЕСТ КНОПКИ 'УПРАВЛЕНИЕ ПОЛЬЗОВАТЕЛЯМИ' ===\n\n";

// Данные для теста
$chatId = 5261465966; // Ваш Chat ID
$userId = 5261465966; // Ваш User ID

try {
    $messageService = new MessageService();
    $adminService = new AdminService($messageService);
    
    echo "Шаг 1: Проверка уровня доступа\n";
    echo str_repeat("-", 60) . "\n";
    $accessLevel = $adminService->getUserAccessLevel($userId);
    $levelName = $adminService->getAccessLevelName($accessLevel);
    echo "User ID: {$userId}\n";
    echo "Уровень доступа: {$levelName} ({$accessLevel})\n";
    echo "Требуется: Администратор (3) или выше\n";
    
    if ($accessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
        echo "✅ Уровень доступа достаточный\n\n";
    } else {
        echo "❌ Недостаточный уровень доступа!\n\n";
        exit;
    }
    
    echo "Шаг 2: Создание кнопки\n";
    echo str_repeat("-", 60) . "\n";
    
    $manageUsersButtonText = '👥 Управление пользователями';
    $callbackData = 'acc_lvl_manage_users';
    
    echo "Текст кнопки: '{$manageUsersButtonText}'\n";
    echo "Callback data: '{$callbackData}'\n\n";
    
    echo "Шаг 3: Отправка тестового сообщения с кнопкой\n";
    echo str_repeat("-", 60) . "\n";
    
    $keyboard = [
        [
            ['text' => $manageUsersButtonText, 'callback_data' => $callbackData]
        ],
        [
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Админка']
        ]
    ];
    
    $message = "🔐 <b>ТЕСТОВОЕ СООБЩЕНИЕ</b>\n\n";
    $message .= "Это тестовое сообщение с кнопкой 'Управление пользователями'.\n\n";
    $message .= "Попробуйте нажать на кнопку ниже:";
    
    echo "Отправляю сообщение в чат {$chatId}...\n";
    
    $result = $messageService->sendMessage($chatId, $message, $keyboard);
    
    if ($result->isOk()) {
        echo "✅ Сообщение успешно отправлено!\n\n";
        echo "ПРОВЕРЬТЕ БОТА:\n";
        echo "1. Откройте бота в Telegram\n";
        echo "2. Вы должны увидеть тестовое сообщение с кнопкой\n";
        echo "3. Нажмите на кнопку '👥 Управление пользователями'\n\n";
        echo "Если кнопка работает - вы должны увидеть список пользователей.\n";
        echo "Если кнопка не работает (ничего не происходит) - сообщите мне.\n";
    } else {
        echo "❌ Ошибка при отправке сообщения!\n";
        echo "Описание: " . $result->getDescription() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
    echo "\nСтек вызовов:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Тест завершен\n";
