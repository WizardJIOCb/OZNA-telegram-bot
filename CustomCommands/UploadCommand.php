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
 * User "/upload" command
 *
 * A command that allows users to upload files to your bot, saving them to the bot's "Download" folder.
 *
 * IMPORTANT NOTICE
 * This is a "demo", do NOT use this as-is in your bot!
 * Know the security implications of allowing users to upload arbitrary files to your server!
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class UploadCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'upload';

    /**
     * @var string
     */
    protected $description = 'Upload and save files';

    /**
     * @var string
     */
    protected $usage = '/upload';

    /**
     * @var string
     */
    protected $version = '0.2.0';

    /**
     * @var bool
     */
    protected $need_mysql = false;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        // Получаем сообщение, чат, chat_id и user_id
        $message = $this->getMessage();
        $chat    = $message->getChat();
        $chat_id = $chat->getId();
        $user_id = $message->getFrom()->getId();                

        // Формируем меню
        $keyboards[] = new Keyboard('Табель', 'Отчёты');        
        $keyboard = end($keyboards)
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(true);
        /*$this->replyToChat('Меню', [
            'reply_markup' => $keyboard,
        ]);*/
        
        // Получаем все данные сообщения
        $cMessage = json_decode($message, true);

        // Переводим в нижний регистр но сохраняем оригинал
        $originalMessage = $cMessage["text"];
        $cMessage["text"] = mb_strtolower($cMessage["text"]);        

        // Клавиатура кнопок
        $keyboards[] = new Keyboard('Табель', 'Профиль', 'Отчёты');        
        $keyboard = end($keyboards)
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(true);

        return Request::sendMessage([
                    'chat_id'      => $this->getMessage()->getChat()->getId(),
                    'text'         => "Тест",
                    'reply_markup' => $keyboard
                ]);

        /*$result = Request::sendMessage([
            'chat_id'      => $user_id,
            'text'         => 'Меню',
            'reply_markup' => $keyboard
        ]);*/

        // Переключатель по командам (тексту в сообщение)
        switch($cMessage['text']){

            // Меню
            case("меню"):
                Request::sendMessage([
                    'chat_id'      => $this->getMessage()->getChat()->getId(),
                    'text'         => 'Меню',
                    'reply_markup' => $keyboard
                ]);                

                return Request::sendPhoto([
                    'chat_id' => $user_id,
                    'photo'   => Request::encodeFile('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/bot_menu.png')
                ]);
            break;

            // Привет
            case("привет"):
                return Request::sendMessage([
                    'chat_id'      => $this->getMessage()->getChat()->getId(),
                    'text'         => 'И тебе привет!',
                    'reply_markup' => $keyboard
                ]);
            break;

            // Отчёты
            case("отчёты"):
            case("отчеты"):

                // Только для админов (конкретных юзеров)
                if (
                    $user_id == 230379668 || // Я
                    $user_id == 5261465966 || // Боря
                    $user_id == 1090118386 || // Близи
                    $user_id == 444234398 // Олег olegpysh
                ){ 
                    $data = [
                        'chat_id'      => $this->getMessage()->getChat()->getId(),
                        'text'         => '<b>Отчёты</b>',
                        'disable_web_page_preview' => true,
                        'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                        'reply_markup' => json_encode(array(
                            'inline_keyboard' => array(
                                array(
                                    array(
                                        'text' => 'Табель ПНР',
                                        'callback_data' => '/GetTabelPNR',
                                    ),
                                    array(
                                        'text' => 'Табель ШМР',
                                        'callback_data' => '/GetTabelSMR',
                                    )/*,
                                    array(
                                        'text' => 'Вернуться к меню',
                                        'callback_data' => '/Menu',
                                    )*/
                                )
                            ),
                        )),
                    ];                                        

                    return Request::sendMessage($data);                    
                    
                }else{
                    return Request::sendMessage([
                        'chat_id'      => $this->getMessage()->getChat()->getId(),
                        'text'         => 'Вам недоступны отчёты',
                        'reply_markup' => $keyboard                 
                    ]);
                }
            break;

            // Табель
            case("табель"):

                $data = [
                    'chat_id'      => $this->getMessage()->getChat()->getId(),
                    'text'         => '<b>🔥 НОВОЕ МЕНЮ - Выберите вид работ 🔥</b>',
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML', //<b>, <a>, <i>, <code>, <pre>
                    'reply_markup' => json_encode(array(
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Подготовка к служебной поездке',
                                    'callback_data' => '/TabelReady',
                                )                                                                                                                 
                            ),
                            array(
                                array(
                                    'text' => 'Дорога',
                                    'callback_data' => '/TabelMobile',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Инструктаж',
                                    'callback_data' => '/TabelInstruct',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Сдача отчетных документов',
                                    'callback_data' => '/TabelDocs',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Ожидание',
                                    'callback_data' => '/TabelWaiting',
                                ),
                                array(
                                    'text' => 'Гарантия',
                                    'callback_data' => '/TabelGuarantee',
                                )  
                            ),
                            array(
                                array(
                                    'text' => 'Электромонтажные работы',
                                    'callback_data' => '/TabelElectro',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Сварочные работы',
                                    'callback_data' => '/TabelWeld',
                                )
                            ),
                            array(
                                array(
                                    'text' => 'Слесарные работы',
                                    'callback_data' => '/TabelLocksmith',
                                )
                            )
                        ),
                    )),
                ];  
                                
                $works = Request::sendMessage($data);  

                return Request::sendMessage([
                    'chat_id'      => $this->getMessage()->getChat()->getId(),
                    'parse_mode' => 'HTML',
                    'text'         => '<b>Либо выберите пунк из онсновного меню</b>',
                    'reply_markup' => $keyboard                 
                ]);
                
                //return $result;
                /*return Request::sendMessage([
                    'chat_id'      => $this->getMessage()->getChat()->getId(),
                    'text'         => 'Здесь скоро будет табель',
                    'reply_markup' => $keyboard                   
                ]);*/
            break;

            // Команда не найдена
            default:
                $result = Request::sendPhoto([
                    'chat_id' => $chat_id,
                    'photo'   => Request::encodeFile('/var/www/api.ladyzenko.pro/tg/ozna/Uploads/bot_menu.png')
                ]);
                return Request::sendMessage([
                    'chat_id'      => $this->getMessage()->getChat()->getId(),
                    'text'         => 'Команда '.$originalMessage." не найдена, используйте дотсупные пункты меню как на картинке выше",
                    'reply_markup' => $keyboard
                ]);                
            break;
        }



        //$data['text'] = 'prop '.$cMessage['text'];
        //$this->replyToChat('procesed 1 | '.$cMessage);
        //return $this->replyToChat($cMessage["text"]); //Work
        /*
        switch($data['text']){
            case('Табель'):
                return Request::sendMessage('на табель');
            break;
            default: 
                return Request::sendMessage("Команда ".$data['text'].' не найдена');
            break;
        }
        */
        //return Request::sendMessage("Команда ".$cMessage['text'].' не найдена');
        //return Request::sendMessage("Команда не найдена");
        

        //$message_type = $message->getType();
        //if (in_array($message_type, ['audio', 'document', 'photo', 'video', 'voice'], true)) {
            /*$this->replyToChat('start upload');
            $doc = $message->{'get' . ucfirst($message_type)}();
            $this->replyToChat('get done');
            // For photos, get the best quality!
            ($message_type === 'photo') && $doc = end($doc);
            $this->replyToChat('go on 1');
            $file_id = $doc->getFileId();
            $this->replyToChat('go on 2');
            $file    = Request::getFile(['file_id' => $file_id]);
            $this->replyToChat('file getted '.$file->getResult()->getFilePath()." | ". print_r($file, true));
            if ($file->isOk() && Request::downloadFile($file->getResult())) {
                $data['text'] = $message_type . ' file is located at: ' . $download_path . '/' . $file->getResult()->getFilePath();
            } else {
                $data['text'] = 'Failed to download.';
            }
            $this->replyToChat('uploaded');
            $conversation->notes['file_id'] = $file_id;
            $conversation->update();
            $conversation->stop();*/
            //$this->replyToChat('document');
        //} else {
            //$data['text'] = 'Please upload the file now | '.$message_type;
        //}

        //return Request::sendMessage($data);
    }
}