<?php

/**
 * Test command to check if text messages are being received
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class TestMessageCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage'; // Use the same name as GenericmessageCommand to test priority

    /**
     * @var string
     */
    protected $description = 'Test command for debugging text message handling';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Main command execution
     */
    public function execute(): ServerResponse
    {
        error_log("TEST: TestMessageCommand received a message");
        
        $message = $this->getMessage();
        $text = trim($message->getText(true));
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();
        
        error_log("TEST: Text='{$text}', ChatID={$chatId}, UserID={$userId}");
        
        // Send a simple response to confirm the command is working
        $data = [
            'chat_id' => $chatId,
            'text'    => "ТЕСТ: Получено сообщение '{$text}'",
        ];
        
        return Request::sendMessage($data);
    }
}