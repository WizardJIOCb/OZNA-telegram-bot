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
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Conversation;

class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Handle the callback query';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    // Отправить сообщение в группу мобильной бригады в чат Табель по умолчанию thread_id 2 !TODO нужно переименовать метод
    public function sendToHistoryChat($message, $history_thread_id = 2){
        $data = [
            'chat_id'      => '-1002722176147',
            "message_thread_id" => 2,
            'text'         => $message,
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>            
        ];  
        return $works = Request::sendMessage($data);
    }

    // Добавить запись в табель юзера
    public function addUserTabel($user_id, $tabel_message){

        // Удлаяем пустые юзернеймы (если вдруг не дали прав, обычно там юзернейм)
        $tabel_message = str_replace("@ ", "", $tabel_message);

        // Добавляем запись в базу
        \pDB::insert("INSERT INTO tg_bot.ozna_tabel (user_id, message, dt) VALUES(".$user_id.", '".$tabel_message."', current_timestamp());");
    }

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws \Exception
     */    
    public function execute(): ServerResponse
    {

        // Получае данные callback'a и сообщения
        $update         = $this->getUpdate();
        $callback_query = $update->getCallbackQuery();
        $callback_data  = $callback_query->getData();
        $user_id = $callback_query->getFrom()->getId();

        // Данные какие то храним в диалоге
        $conversation = new Conversation($callback_query->getMessage()->getChat()->getId(), $callback_query->getMessage()->getChat()->getId(), "/callback");

        // Сохраняем какие то данные для следующей обработки
        $conversation->notes['some-data'] = json_encode($callback_data);
        //$conversation->notes['some-data'] += 1;
        $conversation->update();    

        // Ничего не делаем если не указана категория меню
        if (strpos($callback_data, 'category_') !== 0 && strpos($callback_data, '/') !== 0) {
            return Request::emptyResponse();
        }

        // Преобразуем старые callback'ы в новый формат
        if (strpos($callback_data, '/TabelMobile') === 0) {
            $callback_data = 'category_Табель_Дорога';
        }

        // Получаем данные юзера глобальные
        $user_data = \pDB::getUserData($user_id);

        // Получаем категорию из данных callback (отрезаем category_ от category_Табель например)
        $category = substr($callback_data, strlen('category_'));

        // Проверяем есть ли уже запись за сегодня если отвечаем на ТАБЕЛЬ yes
        if (str_contains($category, "_yes") && str_contains($category, "Табель_")){
            // Проверяем является ли пользователь суперадмином
            $superAdmins = [
                230379668,  // Ваш ID
                5261465966, // Боря
                1090118386, // Близи
                444234398   // Олег olegpysh
            ];
            
            $isSuperAdmin = in_array($user_id, $superAdmins);
            
            // Суперадмины могут отправлять сообщения без ограничения по времени
            if (!$isSuperAdmin) {
                $databaseHost = '82.146.42.213';
                $databaseUsername = 'WizardJIOCb';
                $databasePassword = '7qQ#28051961';
                $databaseName = 'tg_bot';
                $mysqlir = new \mysqli($databaseHost, $databaseUsername, $databasePassword, $databaseName);
                $queryr = "select count(*) as \"count\", ot.dt from ozna_tabel ot where ot.user_id = ".$user_id." and ot.dt > DATE_SUB(NOW(), INTERVAL 600 SECOND);";
                $resultu = $mysqlir->query($queryr);
                $already = $resultu->fetch_assoc();        
                if ($already != null && $already["count"] > 0){
                    $data = [
                        'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                        //'text'         => '❌ За последние 8 часов вы уже добавляли запись в табель',
                        'text'         => '❌ За последние <b>10 минут</b> вы уже добавляли запись в табель [<b>'.date("H:i:s d.m.Y", strtotime($already["dt"]) + 3600 * 2).'</b>]',
                        'disable_web_page_preview' => true,
                        'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>                
                    ];                              
                    return $works = Request::sendMessage($data);
                }
            }
        }        

        // Проверяем что заполнено ФИО
        if ((@$user_data["fio"] == null || @$user_data["fio"] == "Без ФИО") && strpos($category, "Табель") !== -1){
            $data = [
                'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                'text'         => '<b>Заполните ФИО (один раз) командой /fio Фамилия Имя Отчество</b>',
                'disable_web_page_preview' => true,
                'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>                
            ];  
                            
            return $works = Request::sendMessage($data);
        }

        // Переключатель по кнопке нажатой (категории)
        switch($category){

            // Профиль
            case("Профиль"):

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Профиль ('.$user_data["fio"].')</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Отчёты',
                                    'callback_data' => 'category_Профиль_Отчёты',
                                ),
                                array(
                                    'text' => 'Обновить ФИО',
                                    'callback_data' => 'category_Профиль_ФИО',
                                )                                
                            ),
                            array(
                                array(
                                    'text' => 'Последние 10 отметок в табеле',
                                    'callback_data' => 'category_Профиль_Табель_Последние10',
                                )
                            )
                        ),                        
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            case("Профиль_Табель_Последние10"):

                // Connect DB
                $databaseHost = '82.146.42.213';
                $databaseUsername = 'WizardJIOCb';
                $databasePassword = '7qQ#28051961';
                $databaseName = 'tg_bot';


                // Получаем последние 10 записей табеля юзера
                $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName);                 
                $sqlResult = mysqli_query($mysqli, '
                    select dt + INTERVAL 2 HOUR as "dt", message from ozna_tabel ot where ot.user_id = '.$callback_query->getMessage()->getChat()->getId().'
                    order by dt DESC 
                    LIMIT 10;'
                );  
                $tabelMessage = "<b>Последние 10 отметок в табеле:</b>\r\n\r\n";
                $tabelLines = [];                
                while ($rowr = $sqlResult->fetch_assoc()) {
                    $tabelLines[] = $rowr;                    
                }
                $tabelLines = array_reverse($tabelLines);
                foreach($tabelLines as $line){
                    $tabelMessage .= $line["message"]."\r\n\r\n";                    
                }

                return Request::sendMessage([
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'parse_mode' => 'HTML',
                    'text'         => $tabelMessage
                ]);


            break;

            // Профиль - ввести фио
            case("Профиль_ФИО"):

                Request::sendMessage([
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => 'Для обновления ФИО введите /fio Фамилия Имя Отчество'
                ]);
                
                return Request::emptyResponse();

            break;

            // Профиль - Отчёты
            case("Профиль_Отчёты"):

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Профиль - Отчёты</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'График отметок в табеле',
                                    'callback_data' => 'category_Профиль_Отчёты_Табель',
                                )                                                                                                                 
                            )                            
                        ),                        
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Профиль - Отчёты
            case("Профиль_Отчёты_Табель"):

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Ваш график отметок</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Вернуться в профиль',
                                    'callback_data' => 'category_Профиль',
                                )                                                                                                                 
                            )
                        ),                        
                    )),
                ];  
                Request::sendMessage($data);

                // Connect DB
                $databaseHost = '82.146.42.213';
                $databaseUsername = 'WizardJIOCb';
                $databasePassword = '7qQ#28051961';
                $databaseName = 'tg_bot';


                // Fetch user associative array
                $mysqli = mysqli_connect($databaseHost, $databaseUsername, $databasePassword, $databaseName); 
                /*$sqlResult = mysqli_query($mysqli, "
                    select m.`date`, m.`text` from message m 
                    inner join user u on u.id = m.user_id
                    where (m.`text`  like '%добавлена%' and  m.`text` like '%Запись в табель%') and m.chat_id = ".$callback_query->getMessage()->getChat()->getId()."
                    order by `date` ASC;
                ");*/
                $sqlResult = mysqli_query($mysqli, "
                    select dt + INTERVAL 2 HOUR as \"dt\", message from ozna_tabel ot where ot.user_id = ".$callback_query->getMessage()->getChat()->getId()."
                    order by dt ASC;
                ");
                $output = fopen('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/tabel_profile_'.$callback_query->getMessage()->getChat()->getId().'.csv', 'w');
                fputcsv($output, array("Дата", "Событие"));
                while ($rowr = $sqlResult->fetch_assoc()) {
                    fputcsv($output, $rowr);
                }
                fclose($output);

                return $result = Request::sendDocument([
                    'chat_id' => $callback_query->getMessage()->getChat()->getId(),                
                    'document'   => Request::encodeFile('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/tabel_profile_'.$callback_query->getMessage()->getChat()->getId().'.csv'),
                ]);                
                                
                return $works;

            break;

            

            // Табель - Подготовка
            case("Табель_Подготовка"):                
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Подготовка к служебной поездке</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Подготовка_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Подготовка - добавить запись
            case("Табель_Подготовка_yes"):

                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о <b>Подготовке к служебной поездке добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;
            

            // Табель - Мобилизация            
            case("Табель_Мобилизация"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Дорога</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Мобилизация_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Мобилизация - добавить запись
            case("Табель_Мобилизация_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о <b>Дороге добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Инструктаж            
            case("Табель_Инструктаж"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Инструктаж</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Инструктаж_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Инструктаж - добавить запись
            case("Табель_Инструктаж_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о <b>Инструктаже добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Ожидание 
            case("Табель_Ожидание"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Ожидание</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Ожидание_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;
            
            // Табель - Ожидание - Добавить запись
            case("Табель_Ожидание_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> об <b>Ожидании добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Гарантия 
            case("Табель_Гарантия"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Гарантия</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Гарантия_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;
            
            // Табель - Гарантия - Добавить запись
            case("Табель_Гарантия_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о <b>Гарантии добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Электромонтажные работы 
            case("Табель_Электромонтажные"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Электромонтажные работы</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Электромонтажные_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;
            
            // Табель - Электромонтажные работы - Добавить запись
            case("Табель_Электромонтажные_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о <b>Электромонтажных работах добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Сварочные
            case("Табель_Сварочные"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Сварочные работы</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Сварочные_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;
            
            // Табель - Сварочные работы - Добавить запись
            case("Табель_Сварочные_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о <b>Сварочных работах добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Слесарные
            case("Табель_Слесарные"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Слесарные работы</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Слесарные_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;            
            
            // Табель - Слесарные работы - Добавить запись
            case("Табель_Слесарные_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о <b>Слесарных работах добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;            

            // Табель - Теплоизоляиця
            case("Табель_Теплоизоляция"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Теплоизоляция</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Теплоизоляция_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель - Теплоизоляиця - Добавить запись
            case("Табель_Теплоизоляция_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о <b>Теплоизоляции добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break; 

            // Табель - Документы
            case("Табель_Документы"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Отчётные документы</b>, отправить запись в табель в проект <b>'.$conversation->notes['project_code'].'</b>?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Документы_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;
            
            // Табель - Слесарные работы - Добавить запись
            case("Табель_Документы_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b>, в проект <b>'.$conversation->notes['project_code'].'</b> о работе с <b>отчётными документами добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    /*'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(                            
                            array(
                                array(
                                    'text' => 'Ок (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),*/
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Табель (выбора даты первый этап должен быть) // Рабочий!
            case("Табель"):               
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    //'text'         => '<b>Табель за '.$conversation->notes['tabel_data'].', для '.$project_code.'</b>',
                    'text'         => '<b>Выберите вид работ</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Мобильная бригада',
                                    'callback_data' => 'category_Табель_Мобильная_Бригада_Дата',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Шеф-монтаж',
                                    'callback_data' => 'category_Табель_Шеф-монтаж',
                                )
                            ),                            
                            array(
                                array(
                                    'text' => 'Пусконаладка',
                                    'callback_data' => 'category_Табель_Пусконаладка',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Текущая деятельность',
                                    'callback_data' => 'category_Табель_Текущая',
                                )
                            ),
                        ),                        
                    )),
                ];
                return $works = Request::sendMessage($data);  
            break;

            // Шеф-монтаж
            case("Табель_Мобильная_Бригада_Дата"):                

                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    //'text'         => '<b>Табель за '.$conversation->notes['tabel_data'].', для '.$project_code.'</b>',
                    'text'         => '<b>Выберите дату</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Сегодня '.date("d.m.Y"),
                                    'callback_data' => 'category_Табель_ДатаСегодня',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Вчера '.date("d.m.Y", time() - 86400),
                                    'callback_data' => 'category_Табель_ДатаВчера',
                                )
                            ),                            
                        ),                        
                    )),
                ];
                return $works = Request::sendMessage($data);

            break;
                                
                /*return $works = Request::sendMessage($data);

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Выберите дату отметки в табеле</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Сегодня '.date("d.m.Y"),
                                    'callback_data' => 'category_Табель_ДатаСегодня',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Вчера '.date("d.m.Y", time() - 86400),
                                    'callback_data' => 'category_Табель_ДатаВчера',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;*/

            // Табель после выбора даты - выбор проекта
            case("Табель_ДатаСегодня"):
            case("Табель_ДатаВчера"):
                
                // === Выбор проекта если чё закоментить и будет всё ок
                $tabel_date = $category == "Табель_ДатаСегодня" ? date("d.m.Y")  : date("d.m.Y", time() - 86400);
                $conversation->notes['tabel_data'] = $tabel_date;
                $conversation->update();

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Табель за '.$tabel_date.', выбор проекта</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'ОИ834',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ834',
                                ),array(
                                    'text' => 'ОИ651',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ651',
                                ),
                                array(
                                    'text' => 'ОИ696',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ696',
                                ),
                                array(
                                    'text' => 'ОИ795',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ795',
                                )
                            ),                                                        
                            array(
                                array(
                                    'text' => 'ОИ810',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ810',
                                ),
                                array(
                                    'text' => 'ОИ768',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ768',
                                ),
                                array(
                                    'text' => 'ОИ837',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ837',
                                ),
                                array(
                                    'text' => 'ОИ562',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ562',
                                )
                            ),             
                            array(                                                                
                                array(
                                    'text' => 'ОИ788',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ788',
                                ),
                                array(
                                    'text' => 'ОИ860',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ860',
                                ),
                                array(
                                    'text' => 'ОИ862',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ862',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'БКНС115',
                                    'callback_data' => 'category_Табель_ШифрПроекта_БКНС115',
                                ),
                                array(
                                    'text' => 'БКНС120',
                                    'callback_data' => 'category_Табель_ШифрПроекта_БКНС120',
                                )
                            )
                        ),                        
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);                
                
            break;

            case("Табель_ШифрПроекта_ОИ834"):
            case("Табель_ШифрПроекта_ОИ651"):
            case("Табель_ШифрПроекта_ОИ696"):
            case("Табель_ШифрПроекта_ОИ795"):
            case("Табель_ШифрПроекта_БКНС115"):
            case("Табель_ШифрПроекта_БКНС120"):
            case("Табель_ШифрПроекта_ОИ788"):
            case("Табель_ШифрПроекта_ОИ860"):
            case("Табель_ШифрПроекта_ОИ862"):
            case("Табель_ШифрПроекта_ОИ768"):
            case("Табель_ШифрПроекта_ОИ810"):
            case("Табель_ШифрПроекта_ОИ837"):
            case("Табель_ШифрПроекта_ОИ562"):
                
                // Получаем код проекта по категории
                $project_code = explode("_", $category);
                $project_code = $project_code[count($project_code)-1];
                $conversation->notes['project_code'] = $project_code;
                //$conversation->update();               
                
                $conversation->notes['current_category'] = $category;
                $conversation->update();

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>🔥 НОВОЕ МЕНЮ - Табель - Мобильная бригада для проекта '.$project_code.' 🔥</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Подготовка к служебной поездке',
                                    'callback_data' => 'category_Табель_Подготовка',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Дорога',
                                    'callback_data' => 'category_Табель_Мобилизация',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Инструктаж',
                                    'callback_data' => 'category_Табель_Инструктаж',
                                )
                            ),                            
                            array(
                                array(
                                    'text' => 'Ожидание',
                                    'callback_data' => 'category_Табель_Ожидание',
                                ),
                                array(
                                    'text' => 'Гарантия',
                                    'callback_data' => 'category_Табель_Гарантия',
                                )  
                            ),
                            array(
                                array(
                                    'text' => 'Электромонтажные работы',
                                    'callback_data' => 'category_Табель_Электромонтажные',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Сварочные работы',
                                    'callback_data' => 'category_Табель_Сварочные',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Теплоизоляция',
                                    'callback_data' => 'category_Табель_Теплоизоляция',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Слесарные работы',
                                    'callback_data' => 'category_Табель_Слесарные',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Сдача отчетных документов',
                                    'callback_data' => 'category_Табель_Документы',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Вернуться в табель',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

                /*$data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Табель за '.$conversation->notes['tabel_data'].', для '.$project_code.'</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Мобильная бригада',
                                    'callback_data' => 'category_Табель_МобильнаяБригада_Дата',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Шеф-монтаж',
                                    'callback_data' => 'category_Табель_Шеф-монтаж',
                                )
                            ),                            
                            array(
                                array(
                                    'text' => 'Пусконаладка',
                                    'callback_data' => 'category_Табель_Пусконаладка',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Текущая деятельность',
                                    'callback_data' => 'category_Табель_Текущая-деятельность',
                                )
                            )
                        ),                        
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);*/

            break;        

            

            case("Табель_ШифрПроекта"):
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Табель за '.($conversation->notes['tabel_data'] ?? date('d.m.Y')).'</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'ОИ834',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ834',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'ОИ651',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ651',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ696',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ696',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ795',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ795',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'БКНС115',
                                    'callback_data' => 'category_Табель_ШифрПроекта_БКНС115',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ788',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ788',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ860',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ860',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ862',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ862',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ768',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ768',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ810',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ810',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ837',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ837',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'ОИ562',
                                    'callback_data' => 'category_Табель_ШифрПроекта_ОИ562',
                                )
                            )
                                
                        ),                        
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);
            break;
            

            // Мобильная бригада
            case("Табель_Мобильная_бригада"):                
                
                $conversation->notes['current_category'] = $category;
                $conversation->update();

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Табель - Мобильная бригада</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Подготовка к служебной поездке',
                                    'callback_data' => 'category_Табель_Подготовка',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Дорога',
                                    'callback_data' => 'category_Табель_Мобилизация',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Инструктаж',
                                    'callback_data' => 'category_Табель_Инструктаж',
                                )
                            ),                            
                            array(
                                array(
                                    'text' => 'Ожидание',
                                    'callback_data' => 'category_Табель_Ожидание',
                                ),
                                array(
                                    'text' => 'Гарантия',
                                    'callback_data' => 'category_Табель_Гарантия',
                                )  
                            ),
                            array(
                                array(
                                    'text' => 'Электромонтажные работы',
                                    'callback_data' => 'category_Табель_Электромонтажные',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Сварочные работы',
                                    'callback_data' => 'category_Табель_Сварочные',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Теплоизоляция',
                                    'callback_data' => 'category_Табель_Теплоизоляция',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Слесарные работы',
                                    'callback_data' => 'category_Табель_Слесарные',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Сдача отчетных документов',
                                    'callback_data' => 'category_Табель_Документы',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Вернуться в табель',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Шеф-монтаж
            case("Табель_Шеф-монтаж"):                

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'parse_mode' => 'HTML',
                    'text'         => 'Табель <b>Шеф-монтаж</b> в разработке, пока недоступно',
                ];
                return $works = Request::sendMessage($data);

            break;            

            // Пусконаладк
            case("Табель_Пусконаладка"):                

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'parse_mode' => 'HTML',
                    'text'         => 'Табель <b>Пусконаладка</b> в разработке, пока недоступно',
                ];
                return $works = Request::sendMessage($data);

            break;
            

            case("Помощь"):                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'parse_mode' => 'HTML',
                    'text'         => '<b>Пункт меню помощь</b>, скоро здесь будет что то полезное :)',
                ];

                return Request::sendMessage($data);
            break;
            default: 
                /*return Request::editMessageText([
                    'chat_id'    => $callback_query->getMessage()->getChat()->getId(),
                    'message_id' => $callback_query->getMessage()->getMessageId(),
                    'text'       => StartCommand::$categories[$category],
                ]);*/
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => 'Не выбран пункт меню',
                ];

                return Request::sendMessage($data);
            break;

            // Текущая деятельность
            case("Табель_Текущая"):                

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    //'text'         => '<b>Табель за '.$conversation->notes['tabel_data'].', для '.$project_code.'</b>',
                    'text'         => '<b>Выберите дату текущей деятельности</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Сегодня '.date("d.m.Y"),
                                    'callback_data' => 'category_Табель_Текущая_ДатаСегодня',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Вчера '.date("d.m.Y", time() - 86400),
                                    'callback_data' => 'category_Табель_Текущая_ДатаВчера',
                                )
                            ),                            
                        ),                        
                    )),
                ];
                return $works = Request::sendMessage($data);

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'parse_mode' => 'HTML',
                    'text'         => 'Табель <b>Текущая деятельность</b> в разработке, пока недоступно',
                ];

                

                return $works = Request::sendMessage($data);

            break;

            // Табель после выбора даты - текущая деятельность (без проекта)
            case("Табель_Текущая_ДатаСегодня"):
            case("Табель_Текущая_ДатаВчера"):
                
                // Текущая деятельность не привязана к проектам - сразу показываем виды деятельности
                $tabel_date = $category == "Табель_Текущая_ДатаСегодня" ? date("d.m.Y")  : date("d.m.Y", time() - 86400);
                $conversation->notes['tabel_data'] = $tabel_date;
                $conversation->notes['project_code'] = 'Текущая деятельность'; // Не привязано к проектам
                $conversation->update();

                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>💼 Табель - Текущая деятельность за '.$tabel_date.'</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => '🩺 Медицинский осмотр',
                                    'callback_data' => 'category_Табель_МедОсмотр',
                                )
                            ),
                            array(
                                array(
                                    'text' => '🎯 Аттестация',
                                    'callback_data' => 'category_Табель_Аттестация',
                                )
                            ),
                            array(
                                array(
                                    'text' => '📋 Подготовка материала к ТКП',
                                    'callback_data' => 'category_Табель_ПодготовкаТКП',
                                )
                            ),
                            array(
                                array(
                                    'text' => '💼 Общая деятельность',
                                    'callback_data' => 'category_Табель_Общая',
                                )
                            ),
                            array(
                                array(
                                    'text' => '⬅️ Вернуться в табель',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),                        
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);                
                
            break;
            
            // Новые типы текущей деятельности
            
            // Медицинский осмотр
            case("Табель_МедОсмотр"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>МедОсмотр</b>, отправить запись в табель о текущей деятельности?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_МедОсмотр_yes',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Медицинский осмотр - добавить запись
            case("Табель_МедОсмотр_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b> о <b>Текущей деятельности (МедОсмотр) добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                ];  

                // Сохраняем запись в табель
                $this->addUserTabel($user_id, $tabel_message);

                // Отправляем в общий чат
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Аттестация
            case("Табель_Аттестация"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>Аттестация</b>, отправить запись в табель о текущей деятельности?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Аттестация_yes',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Аттестация - добавить запись
            case("Табель_Аттестация_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b> о <b>Текущей деятельности (Аттестация) добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                ];  

                $this->addUserTabel($user_id, $tabel_message);
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Подготовка материала к ТКП
            case("Табель_ПодготовкаТКП"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>ПодготовкаТКП</b>, отправить запись в табель о текущей деятельности?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_ПодготовкаТКП_yes',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Подготовка материала к ТКП - добавить запись
            case("Табель_ПодготовкаТКП_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b> о <b>Текущей деятельности (ПодготовкаТКП) добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                ];  

                $this->addUserTabel($user_id, $tabel_message);
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);

            break;

            // Общая
            case("Табель_Общая"):
                
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => '<b>💼 Общая деятельность</b>, отправить запись в табель о текущей деятельности?',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Да',
                                    'callback_data' => 'category_Табель_Общая_yes',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Нет/удалить (вернуться в табель)',
                                    'callback_data' => 'category_Табель',
                                )
                            )
                        ),
                    )),
                ];  
                                
                return $works = Request::sendMessage($data);

            break;

            // Общая - добавить запись
            case("Табель_Общая_yes"):
                
                $tabel_message = "<b>".$user_data["fio"]."</b>"."\r\n".'✅ Запись в табель от <b>'.$conversation->notes['tabel_data'].'</b> о <b>Текущей деятельности (Общая деятельность) добавлена</b>';
                $data = [
                    'chat_id'      => $callback_query->getMessage()->getChat()->getId(),
                    'text'         => $tabel_message,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                ];  

                $this->addUserTabel($user_id, $tabel_message);
                $this->sendToHistoryChat($data["text"]);
                                
                return $works = Request::sendMessage($data);
            
            
            
        }        
        

        // Callback query data can be fetched and handled accordingly.        
        $callback_query = $this->getCallbackQuery();
        $callback_data  = $callback_query->getData();
        $user_id = $callback_query->getFrom()->getId();

        // DB params
        $databaseHost = '82.146.42.213';
        $databaseUsername = 'WizardJIOCb';
        $databasePassword = '7qQ#28051961';
        $databaseName = 'tg_bot';

        // Убираем надоедливое сообщение с callback data - исправлено для красивого вида
        return Request::emptyResponse();        
    }

    public function rendmerMenu($callback_data, &$response_data){

    }
}