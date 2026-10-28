<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for functional direction related callback queries
 */
class FunctionalDirectionHandler implements CallbackHandlerInterface
{
    private MessageService $messageService;
    private AdminService $adminService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        // Create AdminService for access level checking
        $this->adminService = new AdminService($messageService);
    }

    public function canHandle(string $callbackData): bool
    {
        // Fix encoding issues with callback data
        $callbackData = $this->ensureUtf8Encoding($callbackData);
        $canHandle = str_starts_with($callbackData, 'category_Профиль_ФН');
        error_log("FunctionalDirectionHandler canHandle check for '{$callbackData}': " . ($canHandle ? 'true' : 'false'));
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("FunctionalDirectionHandler handling callback for user: {$userId}, category: '{$category}'");

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);

        // Remove access restriction for guests to set functional direction
        // Guests need to be able to set their functional direction to request access

        switch ($category) {
            case 'Профиль_ФН':
                // Show the functional direction selection menu
                error_log("FunctionalDirectionHandler showing functional direction menu for user: {$userId}");
                return $this->showFunctionalDirectionMenu($chatId);
                
            case 'Профиль_ФН_МобильнаяБригада':
                error_log("FunctionalDirectionHandler handling mobile brigade selection for user: {$userId}");
                return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Мобильная бригада');

            case 'Профиль_ФН_Пусконаладка':
                error_log("FunctionalDirectionHandler handling commissioning selection for user: {$userId}");
                return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Пусконаладка');

            case 'Профиль_ФН_ШефМонтаж':
                error_log("FunctionalDirectionHandler handling supervision selection for user: {$userId}");
                return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Шеф-монтаж');
                
            case 'Профиль_ФН_Менеджер':
                error_log("FunctionalDirectionHandler handling manager selection for user: {$userId}");
                return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Менеджер');
                
            case 'Профиль_ФН_Тестировщик':
                error_log("FunctionalDirectionHandler handling tester selection for user: {$userId}");
                // Check if user has admin access (level 3+)
                $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
                if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
                    return $this->messageService->sendMessage(
                        $chatId,
                        "❌ <b>Доступ запрещен</b>\n\n" .
                        "Функциональное направление 'Тестировщик' доступно только администраторам и суперадминистраторам."
                    );
                }
                return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Тестировщик');
                
            default:
                error_log("FunctionalDirectionHandler unknown category: '{$category}'");
                // Fix encoding for error message
                $category = $this->ensureUtf8Encoding($category);
                return $this->messageService->sendMessage($chatId, 'Неизвестная команда функционального направления');
        }
    }

    public function getPriority(): int
    {
        return 10; // Same as before
    }

    private function handleFunctionalDirectionUpdate(string $chatId, int $userId, string $direction): ServerResponse
    {
        // Ensure UTF-8 encoding
        $direction = mb_convert_encoding($direction, 'UTF-8', 'auto');
        
        // Обновляем ФН в базе данных
        $success = \pDB::updateUserFunctionalDirection($userId, $direction);
        
        if ($success) {
            $text = "<b>✅ Функциональное направление обновлено на:</b> {$direction}" . PHP_EOL . PHP_EOL .
                '📝 Используйте /profile для просмотра профиля';
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            
            return $this->messageService->sendMessage($chatId, $text);
        } else {
            $text = '<b>❌ Ошибка при обновлении функционального направления</b>';
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            
            return $this->messageService->sendMessage($chatId, $text);
        }
    }
    
    private function showFunctionalDirectionMenu(string $chatId): ServerResponse
    {
        // Get user access level from chatId (which should be the user ID in this context)
        $userId = (int)$chatId;
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
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
        
        // Add Тестировщик option only for admins and super admins
        if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
            $keyboard[] = [
                ['text' => '🧪 Тестировщик', 'callback_data' => 'category_Профиль_ФН_Тестировщик']
            ];
        }
        
        $text = '<b>🎯 Выберите новое функциональное направление:</b>' . PHP_EOL . PHP_EOL .
            '• Мобильная бригада' . PHP_EOL .
            '• Пусконаладка' . PHP_EOL .
            '• Шеф-монтаж' . PHP_EOL .
            '• Менеджер';
            
        // Add Тестировщик in text for admins
        if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
            $text .= PHP_EOL . '• Тестировщик (только для администраторов)';
        }
            
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Ensure UTF-8 encoding for text
     */
    private function ensureUtf8Encoding($text)
    {
        // If mbstring is available, use it for encoding conversion
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // If mbstring is not available, return as is
        return $text;
    }
}