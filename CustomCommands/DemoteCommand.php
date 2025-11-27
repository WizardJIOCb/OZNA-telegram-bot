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
 * Demote command
 *
 * Allows admins to demote users to lower access levels.
 */

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

class DemoteCommand extends AdminCommand
{
    /**
     * @var string
     */
    protected $name = 'demote';

    /**
     * @var string
     */
    protected $description = 'Demote user to lower access level';

    /**
     * @var string
     */
    protected $usage = '/demote <user_id>';

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
                "❌ Пожалуйста, укажите ID пользователя для понижения уровня доступа.\n\n" .
                "Использование: /demote <user_id>"
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

        // Get current access level of target user
        $targetUserLevel = $adminService->getUserAccessLevel($targetUserId);
        $targetLevelName = $adminService->getAccessLevelName($targetUserLevel);

        // Check if target user can be demoted
        if ($targetUserLevel >= $userAccessLevel) {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Вы не можете понижать уровень доступа пользователя с равным или более высоким уровнем."
            );
        }

        // Determine new level (minimum Guest)
        $newLevel = max($targetUserLevel - 1, DatabaseService::ACCESS_LEVEL_GUEST);
        $newLevelName = $adminService->getAccessLevelName($newLevel);

        // Update user access level
        if ($adminService->updateUserAccessLevel($targetUserId, $newLevel)) {
            // Notify target user
            try {
                $messageService->sendMessage(
                    $targetUserId,
                    "⚠️ <b>Понижение уровня доступа</b>\n\n" .
                    "Ваш уровень доступа был понижен до: {$newLevelName}"
                );
            } catch (\Exception $e) {
                // Ignore notification errors
            }

            return $messageService->sendMessage(
                $chat_id,
                "✅ Уровень доступа пользователя {$targetUserId} успешно понижен:\n" .
                "Было: {$targetLevelName} ({$targetUserLevel})\n" .
                "Стало: {$newLevelName} ({$newLevel})"
            );
        } else {
            return $messageService->sendMessage(
                $chat_id,
                "❌ Ошибка при понижении уровня доступа. Попробуйте позже."
            );
        }
    }
}