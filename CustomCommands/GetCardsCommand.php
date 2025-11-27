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
 * Get Cards command
 *
 * Command to get user cards.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
// Use our custom services with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class GetCardsCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'Get cards';

    /**
     * @var string
     */
    protected $description = 'Get all cards';

    /**
     * @var string
     */
    protected $usage = '/getcards';

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
        $chat_id = $chat->getId();
        $user_id = $message->getFrom()->getId();

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

        // Make sure the Download path has been defined and exists
        $download_path = $this->telegram->getDownloadPath();
        $upload_path = $this->telegram->getUploadPath();

        // Connect DB
        $databaseHost = '82.146.42.213';
        $databaseUsername = 'WizardJIOCb';
        $databasePassword = '7qQ#28051961';
        $databaseName = 'tg_bot';

        // Получаем карты все
        $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName); 
        $sqlResult = mysqli_query($mysqli, "SELECT * FROM user_card where user_id = ".$user_id." ORDER BY id DESC;");    
        $cards = [];        
        while ($card = $sqlResult->fetch_assoc()) {
            $cards[] = $card;
        }

        $message = 'Всего карт: ' . count($cards) . PHP_EOL. PHP_EOL;
        foreach ($cards as $index => $card) {
            $message .= 'Номер карты: '.$card['card_number'] . PHP_EOL;
            $message .= 'ФИО: '.$card['fio']. PHP_EOL;
            $message .= 'Срок (ДО): '.date('d.m.Y', strtotime($card['expiration_dt'])) . PHP_EOL. PHP_EOL;
            //$message .= 'Срок (ДО): '.timetostr($card['expiration_dt']) . PHP_EOL. PHP_EOL;
        }        

        return $this->replyToChat(            
            $message
        );
    }
}

?>