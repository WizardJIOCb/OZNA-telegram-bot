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
 * Delete Card command
 *
 * Command to delete a user card.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
// Use our custom services with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class DeleteCardCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'Delete card';

    /**
     * @var string
     */
    protected $description = 'Delete card';

    /**
     * @var string
     */
    protected $usage = '/deletecard';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * @var bool
     */
    protected $private_only = false;


    public function mssql_escape($data) {
        if(is_numeric($data))
            return $data;
        $unpacked = unpack('H*hex', $data);
        return '0x' . $unpacked['hex'];
    }

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        // Получаем данные из сообщения
        $message = $this->getMessage();
        $chat    = $message->getChat();
        $chat_id = $chat->getId();
        $user_id = $message->getFrom()->getId();        
        $message_text = $this->getMessage()->getText(true);
        $card_number = $message_text;

        // Initialize services for access level checking
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);
        
        // Check user access level - Guests (level 0) should not have access to card functionality
        $userAccessLevel = $adminService->getUserAccessLevel($user_id);
        if ($userAccessLevel == 0) { // ACCESS_LEVEL_GUEST
            return $this->replyToChat(
                "⚠️ <b>Доступ ограничен</b>\n\nУ вас уровень доступа \"Гость\". Обратитесь к администратору для получения доступа к функциональности карт.",
                ['parse_mode' => 'HTML']
            );
        }

        if (!$card_number){
            return $this->replyToChat('Укажите номер карты для удаления, например:'. PHP_EOL . '/deletecard 123456');
        }

        // Подключаемся к базе данных
        $databaseHost = '82.146.42.213';
        $databaseUsername = 'WizardJIOCb';
        $databasePassword = '7qQ#28051961';
        $databaseName = 'tg_bot';

        // Получаем карту
        $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName); 
        $sqlResult = mysqli_query($mysqli, "SELECT * FROM user_card where user_id = ".$user_id." and card_number = ".$card_number." ORDER BY id DESC;");    
        $card = $sqlResult->fetch_assoc();
        if ($card != null) {
            if ($card["user_id"] == $user_id){
                $sqlResult = mysqli_query($mysqli, "DELETE FROM tg_bot.user_card WHERE id = ".$card["id"]);    
                return $this->replyToChat('Карта '.$card_number.' удалена!');
            }else{
                return $this->replyToChat('Карта '.$card_number.' не ваша!');
            }
        }
        else {
            return $this->replyToChat('Карта '.$card_number.' не найдена!');
        }

    }
}

?>