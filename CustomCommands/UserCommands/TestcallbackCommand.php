<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Request;

/**
 * Test callback functionality
 */
class TestcallbackCommand extends UserCommand
{
    protected $name = 'testcallback';
    protected $description = 'Test callback functionality';
    protected $usage = '/testcallback';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $chatId = $this->getMessage()->getChat()->getId();
        
        // Create a test button that simulates the Master Duties callback
        $keyboard = new InlineKeyboard([
            [
                new InlineKeyboardButton([
                    'text' => '🧪 Test Master Duties Callback',
                    'callback_data' => 'cat_ТабИсполнОбяз'
                ])
            ],
            [
                new InlineKeyboardButton([
                    'text' => '🧪 Test Simple Callback',
                    'callback_data' => 'test_simple'
                ])
            ]
        ]);
        
        $text = "🧪 **CALLBACK TEST**\n\n";
        $text .= "Click the buttons below to test callback handling:\n\n";
        $text .= "**First button**: Uses exact Master Duties callback\n";
        $text .= "**Second button**: Uses simple test callback\n\n";
        $text .= "If buttons don't respond:\n";
        $text .= "→ Callback handling is broken\n\n";
        $text .= "If buttons respond:\n";
        $text .= "→ Master Duties routing issue\n";
        
        return Request::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard,
        ]);
    }
}