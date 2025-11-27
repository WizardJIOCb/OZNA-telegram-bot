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
 * Menu command
 *
 * Shows the main menu with inline keyboard buttons.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class MenuCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'menu';

    /**
     * @var string
     */
    protected $description = 'Menu command';

    /**
     * @var string
     */
    protected $usage = '/menu';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * @var bool
     */
    protected $private_only = false;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        // Get user info
        $message = $this->getMessage();
        $user_id = $message->getFrom()->getId();
        $chat_id = $message->getChat()->getId();
        
        // Initialize services
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);
        
        // Create main menu keyboard based on user access level
        $keyboard = $messageService->createMainMenuKeyboard($user_id);
        
        // Send message with inline keyboard
        return $messageService->sendMessage(
            $chat_id,
            "📋 <b>Главное меню</b>\n\n(для вызова этого меню в любой момент отправьте /start)",
            $keyboard
        );
    }
}
