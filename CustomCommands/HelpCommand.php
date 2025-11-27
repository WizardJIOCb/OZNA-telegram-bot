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
 * Help command
 *
 * Command to show help information.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Conversation;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;
// Use our custom services with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class HelpCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'help';

    /**
     * @var string
     */
    protected $description = 'Help command';

    /**
     * @var string
     */
    protected $usage = '/help';

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
        // If you use deep-linking, get the parameter like this:
        // $deep_linking_parameter = $this->getMessage()->getText(true);

        $message = $this->getMessage();

        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        // Initialize services for access level checking
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);
        
        // Check user access level - Guests (level 0) should have limited access to help
        $userAccessLevel = $adminService->getUserAccessLevel($user_id);
        if ($userAccessLevel == 0) { // ACCESS_LEVEL_GUEST
            return $this->replyToChat(
                'Команды:' . PHP_EOL .
                '/profile - просмотр профиля' . PHP_EOL .
                '/help - помощь'
            );
        }

        $conversation = new Conversation($user->getId(), $chat->getId(), "/start"); // $this->name as /start
        $conversation->notes['some-data'] += 1;
        $conversation->update();        

        /*$data = [
            'chat_id'      => $chat_id,
            // Remove any keyboard by default
            'reply_markup' => Keyboard::remove(['selective' => true]),
        ];

        $result = Request::emptyResponse();

        $data['reply_markup'] = (new Keyboard(['/GetCardszz', '/AddCard', '/DeleteCard']))
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(true);

        //$data['text'] = 'Select your gender:';        

        $result = Request::sendMessage($data);

        return $result;*/

        /*$keyboard_buttons = [];
        $keyboard_buttons[] = new InlineKeyboardButton(
            [
                'text'          => 'test1'.$text,
                'callback_data' => '/Longman',
            ]
        );
        $keyboard_buttons[] = new InlineKeyboardButton(
            [
                'text'          => 'test2'.$text,
                'callback_data' => '/test2',
            ]
        );
        $data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => 'Выберите действие',
            'reply_markup' => new InlineKeyboard($keyboard_buttons)
        ];

        return Request::sendMessage($data);*/

        /*$data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => 'Выберите действие 1<a href="www.ya.ru">YOOO</a>',
            'parse_mode' => 'HTML'
        ];

        return Request::sendMessage($data);*/

        
        if (
            $user_id == 230379668 || // Я
            $user_id == 5261465966 || // Боря
            $user_id == 1090118386 || // Близи
            $user_id == 444234398 // Олег olegpysh
        ){ 
            return $this->replyToChat(
                'Команды:' . PHP_EOL .
                '/GetCards - показать все документы' . PHP_EOL .
                '/AddCard - добавить документ' . PHP_EOL .
                '/DeleteCard - удалить документ' . PHP_EOL .
                '/GetTabelPNR - получить табель ПНР' . PHP_EOL .
                '/GetTabelSMR - получить табель ШМР (тест)' 
            );
        }else{
            return $this->replyToChat(
                'Команды:' . PHP_EOL .
                '/GetCards - показать все документы' . PHP_EOL .
                '/AddCard - добавить документ' . PHP_EOL .
                '/DeleteCard - удалить документ'
            );
        }        
    }
}

?>