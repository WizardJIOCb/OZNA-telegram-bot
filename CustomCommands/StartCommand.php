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
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 *
 * When using deep-linking, the parameter can be accessed by getting the command text.
 *
 * @see https://core.telegram.org/bots#deep-linking
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
// Use our custom services with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class StartCommand extends SystemCommand
{
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.2.0';
    protected $private_only = false;

    public function execute(): ServerResponse
    {
        // Get user info
        $message = $this->getMessage();
        $user_id = $message->getFrom()->getId();
        $chat_id = $message->getChat()->getId();
        
        // Initialize services
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);
        
        // Check user access level
        $userAccessLevel = $adminService->getUserAccessLevel($user_id);
        
        // Create main menu reply keyboard based on user access level
        if ($userAccessLevel == 0) { // Guest level
            // Limited menu for guests - use the service method to ensure consistency
            $keyboard = $messageService->createGuestMainMenuReplyKeyboard();
        } else {
            // Full menu for other users - pass access level to determine if Adminка button should be shown
            $keyboard = $messageService->createMainMenuReplyKeyboard($userAccessLevel);
        }
        
        // Send message with reply keyboard (persistent menu)
        return $messageService->sendMessageWithReplyKeyboard(
            $chat_id,
            "<b>🏠 Главное меню</b>\r\n\r\n🔄 Для вызова этого меню отправьте /start",
            $keyboard
        );
    }
}