<?php

/**
 * User /fn command
 *
 * Command to update user's functional direction.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class FnCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'fn';

    /**
     * @var string
     */
    protected $description = 'Установить функциональное направление';

    /**
     * @var string
     */
    protected $usage = '/fn [Мобильная бригада|Пусконаладка|Шеф-монтаж]';

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

        if (empty($text)) {
            // Если нет параметров, показываем клавиатуру с выбором ФН
            // Проверяем уровень доступа пользователя
            $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
            $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
            $userAccessLevel = $adminService->getUserAccessLevel($user_id);
            
            $keyboard = [
                [
                    ['text' => '👷‍♂️ Мобильная бригада', 'callback_data' => 'category_Профиль_ФН_МобильнаяБригада']
                ],
                [
                    ['text' => '⚙️ Пусконаладка', 'callback_data' => 'category_Профиль_ФН_Пусконаладка']
                ],
                [
                    ['text' => '🔧 Шеф-монтаж', 'callback_data' => 'category_Профиль_ФН_ШефМонтаж']
                ],
                [
                    ['text' => '📊 Менеджер', 'callback_data' => 'category_Профиль_ФН_Менеджер']
                ]
            ];
            
            $textOptions = "• Мобильная бригада" . PHP_EOL .
                          "• Пусконаладка" . PHP_EOL .
                          "• Шеф-монтаж" . PHP_EOL .
                          "• Менеджер";
            
            // Добавляем Тестировщик для админов
            if ($userAccessLevel >= \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_ADMIN) {
                $keyboard[] = [
                    ['text' => '🧪 Тестировщик', 'callback_data' => 'category_Профиль_ФН_Тестировщик']
                ];
                $textOptions .= PHP_EOL . "• Тестировщик (только для администраторов)";
            }
            
            // Получаем текущее ФН
            $userData = \pDB::getUserData($user_id);
            $currentFn = $userData['functional_direction'] ?? 'Не указано';
            
            return $this->replyToChat(
                '<b>🎯 Выберите функциональное направление:</b>' . PHP_EOL . PHP_EOL .
                '<b>Текущее ФН:</b> ' . $currentFn . PHP_EOL . PHP_EOL .
                '<b>Доступные направления:</b>' . PHP_EOL .
                $textOptions . PHP_EOL . PHP_EOL .
                '<b>Пример:</b> /fn Мобильная бригада',
                [
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
                ]
            );
        }

        // Обрабатываем текстовый ввод как функциональное направление
        return $this->handleFunctionalDirectionInput($text, $user_id, $chat_id);
    }

    /**
     * Handle functional direction input
     */
    private function handleFunctionalDirectionInput(string $text, int $user_id, $chat_id): ServerResponse
    {
        // Список допустимых функциональных направлений
        $allowedDirections = [
            'Мобильная бригада',
            'Пусконаладка', 
            'Шеф-монтаж',
            'Менеджер'
        ];
        
        // Проверяем, если это тестировщик, то нужно проверить права
        if (strcasecmp($text, 'Тестировщик') === 0) {
            // Получаем уровень доступа пользователя
            $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
            $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
            $userAccessLevel = $adminService->getUserAccessLevel($user_id);
            
            if ($userAccessLevel < \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_ADMIN) {
                return $this->replyToChat(
                    "❌ <b>Доступ запрещен</b>" . PHP_EOL . PHP_EOL .
                    "Функциональное направление 'Тестировщик' доступно только администраторам и суперадминистраторам.",
                    ['parse_mode' => 'HTML']
                );
            }
            
            // Для админов разрешаем установку тестировщика
            $success = \pDB::updateUserFunctionalDirection($user_id, 'Тестировщик');
            
            if ($success) {
                return $this->replyToChat(
                    "<b>✅ Функциональное направление обновлено:</b> Тестировщик" . PHP_EOL,
                    ['parse_mode' => 'HTML']
                );
            } else {
                return $this->replyToChat(
                    '<b>❌ Ошибка при обновлении функционального направления</b>',
                    ['parse_mode' => 'HTML']
                );
            }
        }

        // Проверяем, является ли это функциональным направлением
        foreach ($allowedDirections as $direction) {
            if (strcasecmp($text, $direction) === 0) {
                $success = \pDB::updateUserFunctionalDirection($user_id, $direction);
                
                if ($success) {
                    return $this->replyToChat(
                        "<b>✅ Функциональное направление обновлено:</b> {$direction}" . PHP_EOL,
                        ['parse_mode' => 'HTML']
                    );
                } else {
                    return $this->replyToChat(
                        '<b>❌ Ошибка при обновлении функционального направления</b>',
                        ['parse_mode' => 'HTML']
                    );
                }
            }
        }

        // Если не найдено подходящее ФН
        return $this->replyToChat(
            '<b>❌ Неверное функциональное направление!</b>' . PHP_EOL . PHP_EOL .
            '<b>Доступные направления:</b>' . PHP_EOL .
            '• Мобильная бригада' . PHP_EOL .
            '• Пусконаладка' . PHP_EOL .
            '• Шеф-монтаж' . PHP_EOL .
            '• Менеджер' . PHP_EOL . PHP_EOL .
            '<b>Пример:</b> /fn Мобильная бригада',
            ['parse_mode' => 'HTML']
        );
    }
}