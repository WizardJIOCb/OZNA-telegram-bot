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
 * Request access command
 *
 * Allows users to request access level upgrade.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

class RequestAccessCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'requestaccess';

    /**
     * @var string
     */
    protected $description = 'Request access level upgrade';

    /**
     * @var string
     */
    protected $usage = '/requestaccess';

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
        $user_name = $message->getFrom()->getFirstName() . ' ' . $message->getFrom()->getLastName();

        // Initialize services
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);

        // Check current access level
        $currentLevel = $adminService->getUserAccessLevel($user_id);
        $levelName = $adminService->getAccessLevelName($currentLevel);

        // If user is already Super Admin, no need to request upgrade
        if ($currentLevel === DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $messageService->sendMessage(
                $chat_id,
                "🔐 <b>Управление уровнями доступа</b>\n\n" .
                "Ваш текущий уровень: {$levelName}\n" .
                "Вы уже имеете самый высокий уровень доступа!"
            );
        }

        // For guests, show option to request upgrade to User level
        if ($currentLevel === DatabaseService::ACCESS_LEVEL_GUEST) {
            $keyboard = [
                [
                    ['text' => '📝 Запросить повышение до Пользователя', 'callback_data' => 'category_УровеньДоступа_ЗапросПовышения']
                ],
                [
                    ['text' => '⬅️ Вернуться в профиль', 'callback_data' => 'category_Профиль']
                ]
            ];

            return $messageService->sendMessage(
                $chat_id,
                "🔐 <b>Запрос повышения уровня доступа</b>\n\n" .
                "Ваш текущий уровень: {$levelName}\n\n" .
                "Вы можете запросить повышение до уровня Пользователь, " .
                "что позволит вам использовать все основные функции бота.",
                $keyboard
            );
        }

        // For users, show option to request upgrade to Supervisor level
        if ($currentLevel === DatabaseService::ACCESS_LEVEL_USER) {
            $keyboard = [
                [
                    ['text' => '📝 Запросить повышение до Супервайзера', 'callback_data' => 'category_УровеньДоступа_ЗапросПовышения']
                ],
                [
                    ['text' => '⬅️ Вернуться в профиль', 'callback_data' => 'category_Профиль']
                ]
            ];

            return $messageService->sendMessage(
                $chat_id,
                "🔐 <b>Запрос повышения уровня доступа</b>\n\n" .
                "Ваш текущий уровень: {$levelName}\n\n" .
                "Вы можете запросить повышение до уровня Супервайзер, " .
                "что позволит вам просматривать отчеты и другие функции.",
                $keyboard
            );
        }

        // For supervisors, show option to request upgrade to Admin level
        if ($currentLevel === DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            $keyboard = [
                [
                    ['text' => '📝 Запросить повышение до Администратора', 'callback_data' => 'category_УровеньДоступа_ЗапросПовышения']
                ],
                [
                    ['text' => '⬅️ Вернуться в профиль', 'callback_data' => 'category_Профиль']
                ]
            ];

            return $messageService->sendMessage(
                $chat_id,
                "🔐 <b>Запрос повышения уровня доступа</b>\n\n" .
                "Ваш текущий уровень: {$levelName}\n\n" .
                "Вы можете запросить повышение до уровня Администратор, " .
                "что позволит вам управлять проектами и подтверждать заявки.",
                $keyboard
            );
        }

        // For admins, show option to request upgrade to Super Admin level
        // Special case: Only Super Admins can promote to Super Admin level
        if ($currentLevel === DatabaseService::ACCESS_LEVEL_ADMIN) {
            return $messageService->sendMessage(
                $chat_id,
                "🔐 <b>Запрос повышения уровня доступа</b>\n\n" .
                "Ваш текущий уровень: {$levelName}\n" .
                "Повышение до уровня Суперадминистратор может быть выполнено только Суперадминистратором.\n\n" .
                "Обратитесь к Суперадминистратору для получения соответствующих прав."
            );
        }

        // Default response (should not happen with correct logic)
        $nextLevel = $currentLevel + 1;
        $nextLevelName = $adminService->getAccessLevelName($nextLevel);

        $keyboard = [
            [
                ['text' => "📝 Запросить повышение до {$nextLevelName}", 'callback_data' => 'category_УровеньДоступа_ЗапросПовышения']
            ],
            [
                ['text' => '⬅️ Вернуться в профиль', 'callback_data' => 'category_Профиль']
            ]
        ];

        return $messageService->sendMessage(
            $chat_id,
            "🔐 <b>Запрос повышения уровня доступа</b>\n\n" .
            "Ваш текущий уровень: {$levelName}\n" .
            "Следующий уровень: {$nextLevelName}\n\n" .
            "Вы можете запросить повышение до следующего уровня доступа.",
            $keyboard
        );
    }
}