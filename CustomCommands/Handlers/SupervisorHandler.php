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
 * Supervisor handler
 *
 * Handles supervisor-specific callback queries.
 */

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

class SupervisorHandler implements CallbackHandlerInterface
{
    private MessageService $messageService;
    private AdminService $adminService;
    
    public function __construct(MessageService $messageService, AdminService $adminService)
    {
        $this->messageService = $messageService;
        $this->adminService = $adminService;
    }
    
    /**
     * Check if this handler can handle the callback data
     */
    public function canHandle(string $callbackData): bool
    {
        // Add detailed logging
        error_log("SupervisorHandler: canHandle called with callbackData: '{$callbackData}'");
        
        // Be more specific to avoid conflicts with other handlers
        $result = str_starts_with($callbackData, 'category_Супервайзер_');
        
        error_log("SupervisorHandler: canHandle result for '{$callbackData}': " . ($result ? 'true' : 'false'));
        error_log("SupervisorHandler: starts with category_Супервайзер_: " . (str_starts_with($callbackData, 'category_Супервайзер_') ? 'true' : 'false'));
        return $result;
    }
    
    /**
     * Handle the callback data
     */
    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $userFirstName = $callbackQuery->getFrom()->getFirstName();
        
        // Check if user has supervisor access
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ У вас недостаточно прав для использования этой функции."
            );
        }
        
        // Parse callback data
        $parts = explode('_', $category);
        
        // Main supervisor menu
        if ($category === 'category_Супервайзер') {
            return $this->showSupervisorMenu($chatId);
        }
        
        // Reports submenu
        if ($category === 'category_Супервайзер_Отчеты') {
            return $this->showReportsMenu($chatId);
        }
        
        // View all reports
        if ($category === 'category_Супервайзер_Отчеты_ВсеОтчеты') {
            return $this->showAllReports($chatId);
        }
        
        // View user activity
        if ($category === 'category_Супервайзер_Отчеты_Активность') {
            return $this->showUserActivity($chatId);
        }
        
        // Back to supervisor menu
        if ($category === 'category_Супервайзер_Назад') {
            return $this->showSupervisorMenu($chatId);
        }
        
        // Back to main menu
        if ($category === 'category_Супервайзер_Главное') {
            $keyboard = $this->messageService->createMainMenuKeyboard($userId);
            return $this->messageService->sendMessage(
                $chatId,
                "📋 <b>Главное меню</b>",
                $keyboard
            );
        }
        
        // Default response
        return $this->messageService->sendMessage(
            $chatId,
            "❌ Неизвестная команда. Пожалуйста, используйте меню для навигации."
        );
    }
    
    /**
     * Get handler priority
     */
    public function getPriority(): int
    {
        return 25; // Same as before, medium priority
    }
    
    /**
     * Show supervisor main menu
     */
    private function showSupervisorMenu(int $chatId): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '📊 Отчеты', 'callback_data' => 'category_Супервайзер_Отчеты']
            ],
            [
                ['text' => '👥 Пользователи', 'callback_data' => 'category_Супервайзер_Пользователи']
            ],

        ];
        
        return $this->messageService->sendMessage(
            $chatId,
            "📋 <b>Меню супервайзера</b>\n\n" .
            "Доступные функции:\n" .
            "• Просмотр отчетов\n" .
            "• Управление пользователями",
            $keyboard
        );
    }
    
    /**
     * Show reports menu
     */
    private function showReportsMenu(int $chatId): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '📄 Все отчеты', 'callback_data' => 'category_Супервайзер_Отчеты_ВсеОтчеты']
            ],
            [
                ['text' => '📈 Активность пользователей', 'callback_data' => 'category_Супервайзер_Отчеты_Активность']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Супервайзер']
            ]
        ];
        
        return $this->messageService->sendMessage(
            $chatId,
            "📊 <b>Меню отчетов</b>\n\n" .
            "Выберите тип отчета для просмотра:",
            $keyboard
        );
    }
    
    /**
     * Show all reports
     */
    private function showAllReports(int $chatId): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Супервайзер_Отчеты']
            ]
        ];
        
        return $this->messageService->sendMessage(
            $chatId,
            "📄 <b>Все отчеты</b>\n\n" .
            "Здесь будет отображаться список всех отчетов.\n\n" .
            "<i>Функция находится в разработке.</i>",
            $keyboard
        );
    }
    
    /**
     * Show user activity
     */
    private function showUserActivity(int $chatId): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Супервайзер_Отчеты']
            ]
        ];
        
        return $this->messageService->sendMessage(
            $chatId,
            "📈 <b>Активность пользователей</b>\n\n" .
            "Здесь будет отображаться активность пользователей.\n\n" .
            "<i>Функция находится в разработке.</i>",
            $keyboard
        );
    }
}