<?php

/**
 * Text handler command for processing Reply keyboard menu selections
 * Fixed version that works without database connectivity
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
     * Обработка выбора "Админка" - УПРОЩЕННАЯ ВЕРСИЯ
     */
    private function handleAdminMenu(string $chatId, int $userId): ServerResponse
    {
        // Debug: Log that we're handling the admin menu
        error_log("TextmenuCommand handleAdminMenu called for user: {$userId}");
        
        // Список известных супер-админов (хардкод для обхода проблемы с базой данных)
        $superAdmins = [
            230379668,  // Ваш ID
            5261465966  // Боря
        ];
        
        // Проверяем, является ли пользователь супер-админом
        $isSuperAdmin = in_array($userId, $superAdmins);
        error_log("User {$userId} is super admin (hardcoded check): " . ($isSuperAdmin ? 'true' : 'false'));
        
        // Если пользователь является супер-админом
        if ($isSuperAdmin) {
            // Debug: Log that user has admin access
            error_log("User {$userId} has admin access (hardcoded), showing admin panel");
            
            // Показываем главное меню админки
            $levelName = "Суперадминистратор";
            
            $keyboard = [
                [
                    ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
                ],
                [
                    ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
                ],
                [
                    ['text' => '🔐 Управление уровнями доступа', 'callback_data' => 'category_УровеньДоступа_Управление']
                ],
                [
                    ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
                ]
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