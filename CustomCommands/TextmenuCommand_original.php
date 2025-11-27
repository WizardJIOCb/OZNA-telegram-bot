<?php

/**
 * Text handler command for processing Reply keyboard menu selections
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;

class TextmenuCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';  // Возвращаем правильное имя команды

    /**
     * @var string
     */
    protected $description = 'Handle Reply keyboard menu selections';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    private MessageService $messageService;
    private AdminService $adminService;

    /**
     * Initialize services
     */
    private function initializeServices(): void
    {
        if (isset($this->messageService) && isset($this->adminService)) {
            return; // Already initialized
        }

        $this->messageService = new MessageService();
        $this->adminService = new AdminService($this->messageService);
    }

    /**
     * Main command execution
     */
    public function execute(): ServerResponse
    {
        $this->initializeServices();

        $message = $this->getMessage();
        $text = trim($message->getText());
        $chatId = $message->getChat()->getId();
        $userId = $message->getFrom()->getId();

        // Debug: Log which command is handling the message
        error_log("TextmenuCommand handling text: '{$text}' from user: {$userId} in chat: {$chatId}");

        // Обрабатываем команды из Reply меню
        switch ($text) {
            case '📊 Табель':
            case 'Табель':
                return $this->handleTabelMenu($chatId);
                
            case '❓ Помощь':
            case 'Помощь':
                return $this->handleHelpMenu($chatId);
                
            case '👤 Профиль':
            case 'Профиль':
                return $this->handleProfileMenu($chatId);
                
            case '⚙️ Админка':
            case 'Админка':
                // Debug: Log that we're handling the admin button
                error_log("TextmenuCommand handling admin button for user: {$userId}");
                return $this->handleAdminMenu($chatId, $userId);
                
            default:
                // Debug: Log unrecognized text
                error_log("TextmenuCommand received unrecognized text: '{$text}' from user: {$userId}");
                
                // Если текст не соответствует меню - игнорируем
                return Request::emptyResponse();
        }
    }

    /**
     * Обработка выбора "Табель"
     */
    private function handleTabelMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createWorkTypeKeyboard();
        return $this->messageService->sendMessage(
            $chatId,
            '📊 <b>Табель учета рабочего времени</b>

Выберите тип работ:',
            $keyboard
        );
    }

    /**
     * Обработка выбора "Помощь"
     */
    private function handleHelpMenu(string $chatId): ServerResponse
    {
        return $this->messageService->sendMessage(
            $chatId,
            '<b>💁 Помощь</b>

Здесь будет полезная информация о работе с ботом.'
        );
    }

    /**
     * Обработка выбора "Профиль"
     */
    private function handleProfileMenu(string $chatId): ServerResponse
    {
        error_log("TextmenuCommand handleProfileMenu called");
        $keyboard = [
            [
                ['text' => '👤 Просмотр профиля', 'callback_data' => 'category_Профиль_Просмотр']
            ],
            [
                ['text' => '✏️ Изменить ФИО', 'callback_data' => 'category_Профиль_ФИО']
            ],
            [
                ['text' => '🏢 Функциональное направление', 'callback_data' => 'category_Профиль_ФН']
            ]
        ];
        
        error_log("TextmenuCommand sending profile menu with keyboard: " . print_r($keyboard, true));
        return $this->messageService->sendMessage(
            $chatId,
            '👤 <b>Профиль пользователя</b>

Выберите действие:',
            $keyboard
        );
    }

    /**
     * Обработка выбора "Админка"
     */
    private function handleAdminMenu(string $chatId, int $userId): ServerResponse
    {
        // Debug: Log that we're handling the admin menu
        error_log("TextmenuCommand handleAdminMenu called for user: {$userId}");
        
        // Проверяем уровень доступа пользователя
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        // Debug: Log the user's access level
        error_log("User {$userId} access level: {$userAccessLevel}");
        
        // Дополнительная отладка для суперадминов
        $isSuperAdmin = $this->adminService->isSuperAdmin($userId);
        error_log("User {$userId} is super admin: " . ($isSuperAdmin ? 'true' : 'false'));
        
        // Если пользователь имеет доступ к админке (уровень Администратор или выше)
        if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
            // Debug: Log that user has admin access
            error_log("User {$userId} has admin access, showing admin panel");
            
            // Показываем главное меню админки
            $levelName = $this->adminService->getAccessLevelName($userAccessLevel);
            
            $keyboard = [
                [
                    ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
                ],
                [
                    ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
                ]
            ];
            
            // Только суперадмины могут управлять уровнями доступа
            if ($userAccessLevel === DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
                error_log("User {$userId} is super admin, adding access level management button");
                $keyboard[] = [
                    ['text' => '🔐 Управление уровнями доступа', 'callback_data' => 'category_УровеньДоступа_Управление']
                ];
                error_log("Added access level management button with callback_data: category_УровеньДоступа_Управление");
            } else {
                error_log("User {$userId} is not super admin (access level: {$userAccessLevel}), not adding access level management button");
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
            ];

            return $this->messageService->sendMessage(
                $chatId,
                "⚙️ <b>Панель администратора</b>

Ваш уровень доступа: {$levelName}

Выберите действие:",
                $keyboard
            );
        } else {
            // Debug: Log that user doesn't have admin access
            error_log("User {$userId} doesn't have admin access, showing request access prompt");
            
            // Показываем меню с кнопкой "Запросить доступ"
            $keyboard = $this->adminService->createAccessRequestKeyboard();
            return $this->messageService->sendMessage(
                $chatId,
                "🔐 <b>Админка</b>

Для получения доступа к административным функциям подайте заявку.",
                $keyboard
            );
        }
    }
}