<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

/**
 * Команда для отображения списка пользователей с доступом
 */
class UsersCommand extends UserCommand
{
    protected $name = 'users';
    protected $description = 'Показать список пользователей с доступом';
    protected $usage = '/users';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();

        error_log("UsersCommand executed by user: {$userId}");

        try {
            $messageService = new MessageService();
            $adminService = new AdminService($messageService);
            
            // Проверяем уровень доступа
            $userAccessLevel = $adminService->getUserAccessLevel($userId);
            
            error_log("UsersCommand user {$userId} access level: {$userAccessLevel}");
            
            if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
                return $this->replyToChat(
                    "❌ У вас недостаточно прав для просмотра списка пользователей.\n\n" .
                    "Требуется уровень доступа: Администратор или выше\n" .
                    "Ваш уровень: " . $adminService->getAccessLevelName($userAccessLevel)
                );
            }
            
            // Получаем всех пользователей с доступом
            $usersWithAccess = $adminService->getAllUsersWithAccess();
            
            error_log("UsersCommand found " . count($usersWithAccess) . " users with access");
            
            if (empty($usersWithAccess)) {
                return $this->replyToChat(
                    "ℹ️ Нет пользователей с уровнем доступа выше Гостя."
                );
            }
            
            // Формируем сообщение со списком пользователей
            $text = "👥 <b>Пользователи с доступом</b>\n\n";
            $text .= "Всего пользователей: " . count($usersWithAccess) . "\n\n";
            
            // Показываем первых 20 пользователей
            $count = min(20, count($usersWithAccess));
            for ($i = 0; $i < $count; $i++) {
                $user = $usersWithAccess[$i];
                $uId = $user['user_id'];
                $userData = $user['data'] ?? [];
                $fio = $userData['fio'] ?? 'Без ФИО';
                $accessLevel = $userData['access_level'] ?? 0;
                $levelName = $adminService->getAccessLevelName($accessLevel);
                
                $text .= ($i + 1) . ". <b>{$fio}</b>\n";
                $text .= "   ID: {$uId}\n";
                $text .= "   Уровень: {$levelName}\n\n";
            }
            
            if (count($usersWithAccess) > 20) {
                $text .= "\n... и ещё " . (count($usersWithAccess) - 20) . " пользователей";
            }
            
            // Создаем кнопку для возврата
            $keyboard = [
                [
                    ['text' => '⬅️ Вернуться в админку', 'callback_data' => 'category_Админка']
                ]
            ];
            
            return $messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("UsersCommand error: " . $e->getMessage());
            return $this->replyToChat(
                "❌ Ошибка при получении списка пользователей: " . $e->getMessage()
            );
        }
    }
}
