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

class TestCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'addcard';

    /**
     * @var string
     */
    protected $description = 'Add card';

    /**
     * @var string
     */
    protected $usage = '/addcard';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * @var bool
     */
    protected $private_only = true;

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

        // Make sure the Download path has been defined and exists
        $download_path = $this->telegram->getDownloadPath();
        $upload_path = $this->telegram->getUploadPath();

        // Connect DB
        $databaseHost = '127.0.0.1';
        $databaseUsername = 'WizardJIOCb';
        $databasePassword = '7qQ#28051961';
        $databaseName = 'tg_bot';

        // Fetch user associative array
        /*$mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName); 
        $sqlResult = mysqli_query($mysqli, "SELECT * FROM users ORDER BY id DESC");    
        $rows = [];*/
        /*while ($rowr = $resultr->fetch_assoc()) {
            $rows[] = $rowr;
        }*/

        //$db_user = $sqlResult->fetch_assoc();
        
        /*
        $mysqlir = new mysqli($databaseHost, $databaseUsername, $databasePassword, $databaseName);        
        $queryr = "SELECT * FROM users ORDER BY id DESC";                
        $resultr = $mysqlir->query($queryr);
        while ($rowr = $resultr->fetch_assoc()) {
            $rows[] = $rowr;
        }
        */

        $result = Request::sendPhoto([
            'chat_id' => $chat_id,
            //'photo'   => Request::encodeFile('https://optim.tildacdn.com/tild6466-3737-4465-b437-633661333236/-/resize/180x/-/format/webp/noroot.png.webp'),
            'photo'   => Request::encodeFile('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/1.png'),
        ]);
        

        return $this->replyToChat(            
            'text: '.$this->getMessage()->getText(true) . PHP_EOL .
            'chat_id: '. $chat_id . PHP_EOL .
            'user_id: '. $user_id . PHP_EOL .
            'download_path: '. $download_path . PHP_EOL .
            'upload_path: '. $upload_path . PHP_EOL .
            'message: '. $message . PHP_EOL .
            'chat: '. $chat . PHP_EOL .
            'db_login: '. $db_user[0] . PHP_EOL .
            'db_user: '. print_r($db_user, true) . PHP_EOL .
            'rows: '. print_r($rows, true) . PHP_EOL
        );
    }
}

?>