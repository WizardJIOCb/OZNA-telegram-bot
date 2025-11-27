<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class StartCommand extends SystemCommand
{
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.2.0';
    protected $private_only = false;

    // Меню
    public static $categories = [
        'Табель'  => 'Здесь будет табель',
        'Помощь' => 'Здесь будет помощь',
        'Профиль' => 'Просмотр и управление профилем',
        'Админка' => 'Административные функции'
    ];

    public function execute(): ServerResponse
    {
        // Лог выполнения команды
        file_put_contents(__DIR__ . '/../start_debug.log', date('Y-m-d H:i:s') . " - StartCommand executed\n", FILE_APPEND);
        
        // Основное меню
        $keyboard_buttons = [];
        foreach (self::$categories as $key => $value) {
            // Добавляем эмодзи для админки
            $displayText = $key;
            if ($key === 'Админка') {
                $displayText = '⚙️ ' . $key;
            }
            
            $keyboard_buttons[] = new InlineKeyboardButton([
                'text'          => $displayText,
                'callback_data' => 'category_' . $key,
            ]);
        }
        
        $data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => "<b>Главное меню</b>\r\n(для вызова этого меню в любой момент отправьте /start)",
            'parse_mode' => 'HTML',
            'reply_markup' => new InlineKeyboard($keyboard_buttons),
        ];

        file_put_contents(__DIR__ . '/../start_debug.log', date('Y-m-d H:i:s') . " - About to send message\n", FILE_APPEND);
        
        $result = Request::sendMessage($data);
        
        file_put_contents(__DIR__ . '/../start_debug.log', date('Y-m-d H:i:s') . " - Message sent\n", FILE_APPEND);
        
        return $result;
    }
}
?>