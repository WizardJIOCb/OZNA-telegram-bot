<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Promote command
 *
 * Allows admins to promote users to higher access levels.
 */

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

class PromoteCommand extends AdminCommand
{
    /**
     * @var string
     */
    protected $name = 'promote';

    /**
     * @var string
     */
    protected $description = 'Promote user to higher access level';

    /**
     * @var string
     */
    protected $usage = '/promote <user_id>';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws \Exception
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $args = $this->getMessage()->getText(true);

        // Initialize services
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);

        // Check if user is admin
        $userAccessLevel = $adminService->getUserAccessLevel($user_id);
        
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            return $messageService->sendMessage(
                $chat_id,
                "❌ У вас недостаточно прав для использования этой команды."
            );
        }

        // Check if user ID is provided
        if (empty($args)) {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Пожалуйста, укажите ID пользователя для повышения уровня доступа.\n\n" .
                "Использование: /promote <user_id>"
            );
        }

        // Validate user ID
        $targetUserId = (int) $args;
        if ($targetUserId <= 0) {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Неверный ID пользователя."
            );
        }

        // Check if target user has completed their profile (FIO and functional direction)
        $databaseService = new DatabaseService();
        $targetUserData = $databaseService->getUserData($targetUserId);
        $targetUserFio = $targetUserData['fio'] ?? '';
        $targetUserFunctionalDirection = $targetUserData['functional_direction'] ?? '';

        // Check if required profile fields are filled
        if (empty($targetUserFio) || $targetUserFio === 'Без ФИО') {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Пользователь не может быть повышен, так как не заполнено ФИО.\n\n" .
                "Пользователь должен заполнить ФИО в профиле перед повышением уровня доступа."
            );
        }

        if (empty($targetUserFunctionalDirection) || $targetUserFunctionalDirection === 'Не указано') {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Пользователь не может быть повышен, так как не выбрано функциональное направление.\n\n" .
                "Пользователь должен выбрать функциональное направление в профиле перед повышением уровня доступа."
            );
        }

        // Get current access level of target user
        $targetUserLevel = $adminService->getUserAccessLevel($targetUserId);
        $targetLevelName = $adminService->getAccessLevelName($targetUserLevel);

        // Check if target user can be promoted
        if ($targetUserLevel >= $userAccessLevel) {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Вы не можете повышать уровень доступа пользователя с равным или более высоким уровнем."
            );
        }

        // Determine new level
        $newLevel = $targetUserLevel + 1;
        $newLevelName = $adminService->getAccessLevelName($newLevel);

        // Check restrictions for promoting to admin
        if ($newLevel === DatabaseService::ACCESS_LEVEL_ADMIN && $userAccessLevel !== DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Только Суперадминистратор может назначать Администраторов."
            );
        }

        // Check restrictions for promoting to supervisor
        if ($newLevel === DatabaseService::ACCESS_LEVEL_SUPERVISOR && $userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Только Администратор может назначать Супервайзеров."
            );
        }

        // Update user access level
        if ($adminService->updateUserAccessLevel($targetUserId, $newLevel)) {
            // Notify target user
            try {
                $messageService->sendMessage(
                    $targetUserId,
                    "✅ <b>Повышение уровня доступа</b>\n\n" .
                    "Ваш уровень доступа был повышен до: {$newLevelName}"
                );
            } catch (\Exception $e) {
                // Ignore notification errors
            }

            return $messageService->sendMessage(
                $chat_id,
                "✅ Уровень доступа пользователя {$targetUserId} успешно повышен:\n" .
                "Было: {$targetLevelName} ({$targetUserLevel})\n" .
                "Стало: {$newLevelName} ({$newLevel})"
            );
        } else {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Ошибка при повышении уровня доступа. Попробуйте позже."
            );
        }
    }
}