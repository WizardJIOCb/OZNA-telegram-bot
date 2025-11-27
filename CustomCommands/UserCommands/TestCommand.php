<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

/**
 * Simple test command to verify bot is working
 */
class TestCommand extends UserCommand
{
    protected $name = 'test';
    protected $description = 'Simple test command';
    protected $usage = '/test';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $chatId = $this->getMessage()->getChat()->getId();
        
        $text = "🤖 **BOT STATUS TEST**\n\n";
        $text .= "✅ Bot is receiving commands\n";
        $text .= "✅ Command processing works\n";
        $text .= "✅ Message sending works\n\n";
        
        $text .= "**Master Duties Issue:**\n";
        $text .= "If you see this message, the bot works.\n";
        $text .= "The Master Duties button issue is likely:\n";
        $text .= "• Callback handling problem\n";
        $text .= "• TabelHandler not receiving callbacks\n";
        $text .= "• Silent error in callback processing\n\n";
        
        $text .= "**Next steps:**\n";
        $text .= "1. Try the Master Duties button again\n";
        $text .= "2. Check if other buttons work\n";
        $text .= "3. Report exact behavior\n";
        
        return Request::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }
}