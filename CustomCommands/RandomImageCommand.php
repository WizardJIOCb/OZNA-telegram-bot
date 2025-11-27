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
 * User "/image" command
 *
 * Randomly fetch any uploaded image from the Uploads path and send it to the user.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class RandomImageCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'randomimage';

    /**
     * @var string
     */
    protected $description = 'Randomly fetch any uploaded image';

    /**
     * @var string
     */
    protected $usage = '/randomimage';

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

        try{
            $message = $this->getMessage();

            // Use any extra parameters as the caption text.
            $caption = trim($message->getText(true));

            // Make sure the Upload path has been defined and exists.
            $upload_path = $this->telegram->getUploadPath();
            if (!is_dir($upload_path)) {
                return $this->replyToChat('Upload path has not been defined or does not exist.');
            }

                    
            // Get a random picture from the Upload path.
            try{
                $random_image = $this->getRandomImagePath($upload_path);
                //$this->replyToChat('Upload path! '.$upload_path);
                //$this->replyToChat('Random image! '.$random_image);
                if ('' === $random_image) {
                    return $this->replyToChat('No image found! '.$upload_path);
                }
            }catch(Exception $e){
                return $this->replyToChat('Excpeption '.$e->getMessage());
            }

            //$this->replyToChat('Go');

            // If no caption is set, use the filename.
            //$this->replyToChat('Random image caption! before '.$caption);
            if ('' === $caption) {
                $caption = basename($random_image);
                //$this->replyToChat('Random image caption! random '.$caption);
            }

            //$this->replyToChat('All done!');
            

            //$this->replyToChat('chat_id: '.$message->getFrom()->getId().PHP_EOL.'caption: '.$caption.PHP_EOL.'photo: '.$random_image,);

            return Request::sendPhoto([
                'chat_id' => $message->getFrom()->getId(),
                'caption' => $caption,
                'photo'   => $random_image,
            ]);        
        }catch(Exceptopn $e){
            return $this->replyToChat('Exception '.$e->getMessage());
        }
    }

    /**
     * Return the path to a random image in the passed directory.
     *
     * @param string $dir
     *
     * @return string
     */
    private function getRandomImagePath($dir): string
    {
        if (!is_dir($dir)) {
            return '';
        }

        // Filter the file list to only return images.
        $image_list = array_filter(scandir($dir), function ($file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            return in_array($extension, ['png', 'jpg', 'jpeg', 'gif']);
        });
        if (!empty($image_list)) {
            shuffle($image_list);
            return $dir . '/' . $image_list[0];
        }        

        return '';
    }
}