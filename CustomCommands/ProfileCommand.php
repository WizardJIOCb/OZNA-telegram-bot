<?php

/**
 * User /profile command
 *
 * Command to view and manage user profile.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
// Use our custom services with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

class ProfileCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'profile';

    /**
     * @var string
     */
    protected $description = 'Просмотр и управление профилем пользователя';

    /**
     * @var string
     */
    protected $usage = '/profile - просмотр профиля';

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
        
        // Check user access level - Guests (level 0) should not have access to profile functionality
        // Note: Profile functionality is an exception and should be available to guests to see their access level
        // But they shouldn't be able to modify it
        $userAccessLevel = $adminService->getUserAccessLevel($user_id);
        if ($userAccessLevel == 0) { // ACCESS_LEVEL_GUEST
            // Show profile but with limited functionality
            $userData = \pDB::getUserData($user_id);
            $currentFio = $userData['fio'] ?? 'Без ФИО';
            $currentFn = $userData['functional_direction'] ?? 'Не указано';
            $accessLevel = $adminService->getUserAccessLevel($user_id);
            $levelName = $adminService->getAccessLevelName($accessLevel);
            
            return $this->replyToChat(
                '<b>👤 Ваш профиль</b>' . PHP_EOL . PHP_EOL .
                '<b>ФИО:</b> ' . $currentFio . PHP_EOL .
                '<b>Функциональное направление:</b> ' . $currentFn . PHP_EOL .
                '<b>Уровень доступа:</b> ' . $levelName . PHP_EOL . PHP_EOL .
                '⚠️ Уровень доступа "Гость" - Обратитесь к администратору для получения расширенного доступа.',
                ['parse_mode' => 'HTML']
            );
        }

        // Получаем данные пользователя
        $userData = \pDB::getUserData($user_id);
        $currentFio = $userData['fio'] ?? 'Без ФИО';
        $currentFn = $userData['functional_direction'] ?? 'Не указано';

        if (!empty($text)) {
            // Если есть текст, показываем сообщение о том, что нужно использовать кнопку в меню профиля
            return $this->replyToChat(
                '<b>📝 Для изменения ФИО перейдите в меню Профиль → Изменить ФИО</b>' . PHP_EOL . PHP_EOL .
                'Затем введите ваше ФИО в текстовом формате.' . PHP_EOL . PHP_EOL .
                '<b>Пример:</b> Иванов Иван Иванович',
                ['parse_mode' => 'HTML']
            );
        }

        // Показываем профиль и меню управления
        $keyboard = [
            [
                ['text' => '👤 Изменить ФИО', 'callback_data' => 'category_Профиль_ИзменитьФИО']
            ],
            [
                ['text' => '🎯 Изменить функциональное направление', 'callback_data' => 'category_Профиль_ИзменитьФН']
            ]
        ];
        
        $accessLevel = $adminService->getUserAccessLevel($user_id);
        $levelName = $adminService->getAccessLevelName($accessLevel);
        
        return $this->replyToChat(
            '<b>👤 Ваш профиль</b>' . PHP_EOL . PHP_EOL .
            '<b>ФИО:</b> ' . $currentFio . PHP_EOL .
            '<b>Функциональное направление:</b> ' . $currentFn . PHP_EOL .
            '<b>Уровень доступа:</b> ' . $levelName . PHP_EOL . PHP_EOL .
            '<b>Выберите что хотите изменить:</b>',
            [
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
            ]
        );
    }
}