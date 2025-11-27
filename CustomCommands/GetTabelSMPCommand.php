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
 * Get Tabel SMP command
 *
 * Command to get SMP tabel data.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Spatie\Emoji\Emoji;
// Use our custom services with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class GetTabelSMPCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'gettabelsmp';

    /**
     * @var string
     */
    protected $description = 'Get Tabel command';

    /**
     * @var string
     */
    protected $usage = '/gettabelsmr';

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
        
        // Check user access level - Guests (level 0) should not have access to tabel functionality
        $userAccessLevel = $adminService->getUserAccessLevel($user_id);
        if ($userAccessLevel == 0) { // ACCESS_LEVEL_GUEST
            return $this->replyToChat(
                "⚠️ <b>Доступ ограничен</b>\n\nУ вас уровень доступа \"Гость\". Обратитесь к администратору для получения доступа к функциональности табеля.",
                ['parse_mode' => 'HTML']
            );
        }

        // Только для конкретных юзеров
        if (
            $user_id == 230379668 || // Я
            $user_id == 5261465966 || // Боря
            $user_id == 1090118386 || // Близи
            $user_id == 444234398 || // Олег olegpysh
            $user_id == 1278162877 // Шарафутдинов Тимур Мунирович            
            ){ 

            // Make sure the Download path has been defined and exists
            $download_path = $this->telegram->getDownloadPath();
            $upload_path = $this->telegram->getUploadPath();

            // Connect DB
            $databaseHost = '82.146.42.213';
            $databaseUsername = 'WizardJIOCb';
            $databasePassword = '7qQ#28051961';
            $databaseName = 'tg_bot';

            // Fetch user associative array
            $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName); 
            $sqlResult = mysqli_query($mysqli, "select user_id, date, text from message m where m.chat_id = -1001865074576 order by `date` DESC;");    
            $rows = [];
            $output = fopen('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/tabel_smr.csv', 'w');
            while ($rowr = $sqlResult->fetch_assoc()) {
                $rows[] = $rowr;
                fputcsv($output, $rowr);
            }
            fclose($output);

            return $result = Request::sendDocument([
                'chat_id' => $chat_id,
                //'photo'   => Request::encodeFile('https://optim.tildacdn.com/tild6466-3737-4465-b437-633661333236/-/resize/180x/-/format/webp/noroot.png.webp'),
                'document'   => Request::encodeFile('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/tabel_smr.csv'),
            ]);
        
        }else{
            return $this->replyToChat("Нет прав для выполнения команды");
        }

        /*Request::sendDocument(array(
            'document' =>  new CURLFile('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/tabel.csv', finfo_file(finfo_open(FILEINFO_MIME_TYPE), '/var/www/api.ladyzenko.pro/tg/ozna/Uploads/tabel.csv'))
        ));*/

        //return $this->replyToChat("done");
        /*return $this->replyToChat(            
            'callback: '.print_r($callback_query, true). PHP_EOL .
            'text: '.$this->getMessage()->getText(true) . PHP_EOL .
            'chat_id: '. $chat_id . PHP_EOL .
            'user_id: '. $user_id . PHP_EOL .
            'download_path: '. $download_path . PHP_EOL .
            'upload_path: '. $upload_path . PHP_EOL .
            'message: '. $message . PHP_EOL .
            'db_user: '. print_r($db_user, true) . PHP_EOL .
            'chat: '. $chat . PHP_EOL             
        );*/
    }
}

?>