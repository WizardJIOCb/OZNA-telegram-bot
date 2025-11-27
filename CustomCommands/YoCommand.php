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

class YoCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'yo';

    /**
     * @var string
     */
    protected $description = 'Yo command';

    /**
     * @var string
     */
    protected $usage = '/yo';

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

        return $this->replyToChat(
            'YO' . PHP_EOL .
            'Как оно йопта? Сегодня уже ' . date('d.m.Y, H:i:s', time() + 3600 * 2).' по Окту, опять работать?..'
        );
    }
}

?>