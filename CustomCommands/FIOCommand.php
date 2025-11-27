<?php

/**
 * User /fio command
 *
 * Command to update user's FIO.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
// Use our custom services with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class FioCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'fio';

    /**
     * @var string
     */
    protected $description = 'Изменить ФИО пользователя';

    /**
     * @var string
     */
    protected $usage = '/fio [Ваше ФИО] - установить ФИО';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $text = trim($message->getText(true));

        // Initialize services for access level checking
        $messageService = new MessageService();
        $adminService = new AdminService($messageService);
        
        // Check user access level - Guests (level 0) should be allowed to set FIO for access requests
        $userAccessLevel = $adminService->getUserAccessLevel($user_id);
        // Remove the restriction for guests to set FIO
        // Guests need to be able to set their FIO to request access

        if (empty($text)) {
            // Получаем текущее ФИО
            $userData = \pDB::getUserData($user_id);
            $currentFio = $userData['fio'] ?? 'Без ФИО';
            
            return $this->replyToChat(
                '<b>👤 Изменение ФИО</b>' . PHP_EOL . PHP_EOL .
                '<b>Текущее ФИО:</b> ' . $currentFio . PHP_EOL . PHP_EOL .
                '📝 Для изменения ФИО напишите:' . PHP_EOL .
                '<code>/fio ваше_новое_фио</code>' . PHP_EOL . PHP_EOL .
                '<b>Пример:</b>' . PHP_EOL .
                '<code>/fio Иванов Иван Иванович</code>',
                ['parse_mode' => 'HTML']
            );
        }

        // Обрабатываем новое ФИО
        return $this->handleFioUpdate($text, $user_id, $chat_id);
    }

    /**
     * Handle FIO update
     */
    private function handleFioUpdate(string $text, int $user_id, $chat_id): ServerResponse
    {
        // Проверяем минимальную длину
        if (strlen($text) < 3) {
            return $this->replyToChat(
                '<b>❌ ФИО слишком короткое!</b>' . PHP_EOL .
                'Минимум 3 символа.' . PHP_EOL . PHP_EOL .
                '<b>Пример:</b> /fio Иванов Иван Иванович',
                ['parse_mode' => 'HTML']
            );
        }

        // Обновляем ФИО
        $success = \pDB::updateUserFio($user_id, $text);
        
        if ($success) {
            return $this->replyToChat(
                "<b>✅ ФИО обновлено:</b> {$text}" . PHP_EOL . PHP_EOL .
                '📝 Используйте /profile для просмотра полного профиля',
                ['parse_mode' => 'HTML']
            );
        } else {
            return $this->replyToChat(
                '<b>❌ Ошибка при обновлении ФИО</b>',
                ['parse_mode' => 'HTML']
            );
        }
    }
}