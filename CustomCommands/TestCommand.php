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
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Spatie\Emoji\Emoji;

class TestCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'test';

    /**
     * @var string
     */
    protected $description = 'Test command';

    /**
     * @var string
     */
    protected $usage = '/test';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * @var bool
     */
    protected $private_only = false;

    public static $categories = [
        'Табель'  => 'Здесь будет табель',
        'Помощь' => 'Здесь будет помощь'
    ];

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {

        $message = $this->getMessage();
        $chat    = $message->getChat();
        $chat_id = $chat->getId();
        $user_id = $message->getFrom()->getId();
        $user    = $message->getFrom();  


        //$mes = \pDB::getUserData($user_id);
        //$mes[1] = '123';
        //\pDB::setUserData($user_id, $mes);

        $mes = "lol7";
        return $this->replyToChat(
            print_r($mes, true)
        );
        

        /*$keyboard_buttons = [];
        foreach (self::$categories as $key => $value) {
            $keyboard_buttons[] = new InlineKeyboardButton([
                'text'          => ucfirst($key),
                'callback_data' => 'category_' . $key,
            ]);
        }
        $data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => 'Choose a category:',
            'reply_markup' => new InlineKeyboard($keyboard_buttons),
        ];

        return Request::sendMessage($data);*/

        // If you use deep-linking, get the parameter like this:
        // $deep_linking_parameter = $this->getMessage()->getText(true);

        /*$data = [
            'chat_id'      => -1002722176147,
            'text'         => '✅ Запись в табель от добавлена!!'.print_r,
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>            
        ];  
                        
        return $works = Request::sendMessage($data);*/


        $keyboard_buttons = [];
        foreach (self::$categories as $key => $value) {
            $keyboard_buttons[] = new InlineKeyboardButton([
                'text'          => ucfirst($key),
                'callback_data' => 'category_' . $key,
            ]);
        }
        $data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => 'Выберите пункт меню',
            'reply_markup' => new InlineKeyboard($keyboard_buttons),
        ];

        return Request::sendMessage($data);

        $message = $this->getMessage();
        $chat    = $message->getChat();
        $chat_id = $chat->getId();
        $user_id = $message->getFrom()->getId();
        $user    = $message->getFrom();        

        $edited_message = $this->getUpdate()->getEditedMessage();
        $callback_query = $this->getUpdate()->getCallbackQuery();
        
        $conversation = new Conversation($user->getId(), $chat->getId(), "/start");
        $conversation->notes['some-data'] += 1;
        $conversation->update();        

        return $this->replyToChat(
            'return grok answer'
        );


        /** @var Keyboard[] $keyboards */
        $keyboards = [];

        // Simple digits
        /*$keyboards[] = new Keyboard(
            ['7', '8', '9'],
            ['4', '5', '6'],
            ['1', '2', '3'],
            [' ', '0', ' ']
        );*/

        // Digits with operations
        /*$keyboards[] = new Keyboard(
            ['7', '8', '9', '+'],
            ['4', '5', '6', '-'],
            ['1', '2', '3', '*'],
            [' ', '0', ' ', '/']
        );*/

        // Short version with 1 button per row
        //$keyboards[] = new Keyboard('A', 'B', 'C');

        // Some different ways of creating rows and buttons
        $keyboards[] = new Keyboard(
            ['text' => 'A'],
            'B',
            ['C', 'D']
        );

        // Buttons to perform Contact or Location sharing
        /*$keyboards[] = new Keyboard([
            ['text' => 'Send my contact', 'request_contact' => true],
            ['text' => 'Send my location', 'request_location' => true],
        ]);*/

        // Shuffle our example keyboards and return a random one
        /*$keyboards[] = new Keyboard('Табель', 'Профиль', 'Отчёты');        
        $keyboard = end($keyboards)
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(true);

        return $this->replyToChat('Выберите пункт меню', [
            'reply_markup' => $keyboard,
        ]);*/

        //$callbackquery_data = $this->getUpdate()->getCallbackQuery()->getData();

         /*$data = [
            'chat_id'                  => $chat_id,
            'text'                     => 'yo12'.print_r($edited_message, true).print_r($callback_query, true).print_r($callbackquery_data, true),
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup'             => new InlineKeyboard(
                [
                    new InlineKeyboardButton(
                        [
                            'text'                => 'piuz123',
                            'switch_inline_query' => 'piu'
                        ]
                    ),
                ]
            ),
        ];
        return Request::sendMessage($data);*/

        /*
        return $this->replyToChat(
            'callback: test'. PHP_EOL.
            'edited: '.print_r($edited_message, true). PHP_EOL.
            'callback: '.print_r($callback_query, true)
            //'test: '.Emoji::wavingHand()
        );

        $callback_query = $this->getCallbackQuery();*/

        // Отправить событие в чат (например печатает..)
        /*Request::sendChatAction([
            'chat_id' => $chat_id,
            'action'  => Longman\TelegramBot\ChatAction::TYPING,
        ]);*/

        // Отправить сообщение всем кроме каналов, потому что 'channels' => false, в группы улетает нормально в котором бот админ
        /*$results = Request::sendToActiveChats(
            'sendMessage', // Callback function to execute (see Request.php methods)
            ['text' => 'Продолжаем дорабатывать бота, скоро будет красивое меню!'], // Param to evaluate the request
            [
                'groups'      => true,
                'supergroups' => true,
                'channels'    => false,
                'users'       => true,
            ]
        );*/

        // Make sure the Download path has been defined and exists
        $download_path = $this->telegram->getDownloadPath();
        $upload_path = $this->telegram->getUploadPath();

        // Connect DB
        /*$databaseHost = '82.146.42.213';
        $databaseUsername = 'WizardJIOCb';
        $databasePassword = '7qQ#28051961';
        $databaseName = 'tg_bot';


        // Fetch user associative array
        $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName); 
        $sqlResult = mysqli_query($mysqli, "SELECT * FROM users ORDER BY id DESC");    
        $rows = [];*/
        /*while ($rowr = $sqlResult->fetch_assoc()) {
            $rows[] = $rowr;
        }

        $db_user = $sqlResult->fetch_assoc();*/
        
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

        /*$result = Request::sendDocument([
            'chat_id' => $chat_id,
            //'photo'   => Request::encodeFile('https://optim.tildacdn.com/tild6466-3737-4465-b437-633661333236/-/resize/180x/-/format/webp/noroot.png.webp'),
            'document'   => Request::encodeFile('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/1.png'),
        ]);*/
        
        $data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => 'Ссылка <b>жирная</b> <a href="www.ya.ru">YO!</a>',
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
            'reply_markup' => json_encode(array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => 'Button 1',
                            'callback_data' => '/help',
                        ),

                        array(
                            'text' => 'Button 2',
                            'callback_data' => '/image',
                        ),
                    )
                ),
            )),
        ];

        Request::sendMessage($data);

        /*Request::sendDocument(array(
            'document' =>  new CURLFile('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/1.png', finfo_file(finfo_open(FILEINFO_MIME_TYPE), '/var/www/api.ladyzenko.pro/tg/ozna/Uploads/1.png'))
        ));*/

        return $this->replyToChat(            
            'callback: '.print_r($callback_query, true). PHP_EOL .
            'text: '.$this->getMessage()->getText(true) . PHP_EOL .
            'chat_id: '. $chat_id . PHP_EOL .
            'user_id: '. $user_id . PHP_EOL .
            'download_path: '. $download_path . PHP_EOL .
            'upload_path: '. $upload_path . PHP_EOL .
            'message: '. $message . PHP_EOL .
            'chat: '. $chat . PHP_EOL 
            //'db_login: '. $db_user[0] . PHP_EOL .
            //'db_user: '. print_r($db_user, true) . PHP_EOL .
            //'rows: '. print_r($rows, true)
        );
    }
}

?>