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
 * Add Card command
 *
 * Command to add a user card.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
// Use our custom services with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class AddCardCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'Add card';

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

        // Проверяем, что сообщение не пустое
        if ($message_text == null || $message == ''){
            return $this->replyToChat(            
                'Для того чтобы добавить карту введите:' . PHP_EOL .
                '/addcard [номер карты], [ФИО владельца], [дата истечения]' . PHP_EOL .
                'Пример:' . PHP_EOL .
                '/addcard 123456, Калимуллин Родион Данирович, 31.12.2028'
            );
        }

        // Разбиваем сообщение на поля
        $params = explode(',', $message_text);

        // Проверяем каждое поле
        if (count($params) == 3){
            foreach($params as $key => $value) {
                if (!$value){
                    return $this->replyToChat('Одно из полей пустое или не заполнено (возможно есть лишнии запятые)');
                }
                $params[$key] = trim($value);
            }
        }else{
            return $this->replyToChat('Кол-во полей через запятую не равно 3');
        }

        // Подключаемся к базе данных и добавляем карту
        $databaseHost = '82.146.42.213';
        $databaseUsername = 'WizardJIOCb';
        $databasePassword = '7qQ#28051961';
        $databaseName = 'tg_bot';
        //$conn = new mysqli($databaseHost, $databaseUsername, $databasePassword, $databaseName);

        // Подготавливаем запрос и вставляем карту
        try{      
            $expDate = explode('.', $params[2]);
            $expDate = $expDate[2].'-'.$expDate[1].'-'.$expDate[0];
            $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName);                  
            $sqlResult = mysqli_query($mysqli, "INSERT INTO user_card (card_number, fio, expiration_dt, user_id) VALUES ('".$params[0]."', '".$params[1]."', '".$expDate."', ".$user_id.")");  

        }catch (\Exception $e) {
            return $this->replyToChat('Ошибка добавления карты: '.$e->getMessage());
        }

        // Карта успешно добавлена
        return $this->replyToChat('Карта для '.$params[1].' ('.$params[0].' до '.$params[2].') успешно добавлена!');

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

        /*
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
            'params: '. print_r($params, true) . PHP_EOL .
            'rows: '. print_r($rows, true) . PHP_EOL
        );*/
    }
}

?>