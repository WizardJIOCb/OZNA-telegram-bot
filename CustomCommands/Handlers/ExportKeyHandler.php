<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ExportSecurityService;
use Longman\TelegramBot\Conversation;

/**
 * Export Key Management Handler
 * Manages user export keys for CSV protection
 */
class ExportKeyHandler
{
    private MessageService $messageService;
    private DatabaseService $databaseService;
    private ExportSecurityService $exportSecurityService;
    
    public function __construct(
        MessageService $messageService,
        DatabaseService $databaseService,
        ExportSecurityService $exportSecurityService
    ) {
        $this->messageService = $messageService;
        $this->databaseService = $databaseService;
        $this->exportSecurityService = $exportSecurityService;
    }
    
    /**
     * Show export key management menu
     */
    public function showExportKeyMenu(string $chatId, int $userId): ServerResponse
    {
        $hasKey = $this->exportSecurityService->hasExportKey($userId);
        
        if ($hasKey) {
            $text = "🔐 <b>Кодовое слово</b>\n\n";
            $text .= "У вас установлено кодовое слово.\n\n";
            $text .= "Это кодовое слово защищает ваши выгрузки отчётов и заметок.\n\n";
            $text .= "⚠️ Если забыли - запросите сброс у суперадминистратора.\n\n";
            $text .= "Выберите действие:";
            
            $keyboard = [
                [
                    ['text' => '🔄 Запросить сброс', 'callback_data' => 'export_key_request_reset']
                ],
                [
                    ['text' => '⬅️ Назад к профилю', 'callback_data' => 'category_Профиль']
                ]
            ];
        } else {
            $text = "🔐 <b>Кодовое слово</b>\n\n";
            $text .= "⚠️ У вас не установлено кодовое слово.\n\n";
            $text .= "Кодовое слово необходимо для защиты выгрузок отчётов и заметок.\n\n";
            $text .= "Без кодового слова вы не сможете выгружать данные.";
            
            $keyboard = [
                [
                    ['text' => '✅ Установить кодовое слово', 'callback_data' => 'export_key_setup']
                ],
                [
                    ['text' => '⬅️ Назад к профилю', 'callback_data' => 'category_Профиль']
                ]
            ];
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Start export key setup process
     */
    public function startExportKeySetup(string $chatId, int $userId, $conversation): ServerResponse
    {
        $suggestedKey = $this->exportSecurityService->generateSecureKey(8);
        
        $text = "🔐 <b>Установка кодового слова</b>\n\n";
        $text .= "Введите кодовое слово для защиты ваших выгрузок.\n\n";
        $text .= "<b>Требования:</b>\n";
        $text .= "• Минимум 4 символа\n";
        $text .= "• Можно использовать буквы и цифры\n\n";
        $text .= "💡 <b>Предложенное слово:</b> <code>{$suggestedKey}</code>\n\n";
        $text .= "Вы можете использовать предложенное слово или ввести своё.";
        
        // Set conversation state
        $conversation->notes['awaiting_export_key'] = true;
        $conversation->update();
        
        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => 'export_key_menu']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle export key input
     */
    public function handleExportKeyInput(string $chatId, int $userId, string $exportKey, $conversation): ServerResponse
    {
        // Validate key
        if (strlen($exportKey) < 4) {
            $text = "⚠️ Кодовое слово слишком короткое.\n\nМинимум 4 символа. Попробуйте снова:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        if (strlen($exportKey) > 50) {
            $text = "⚠️ Кодовое слово слишком длинное.\n\nМаксимум 50 символов. Попробуйте снова:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Set export key
        $success = $this->exportSecurityService->setExportKey($userId, $exportKey);
        
        if ($success) {
            // Clear conversation state
            $conversation->notes['awaiting_export_key'] = false;
            $conversation->update();
            
            $text = "✅ <b>Кодовое слово установлено</b>\n\n";
            $text .= "Ваше кодовое слово: <code>{$exportKey}</code>\n\n";
            $text .= "⚠️ <b>Важно!</b>\n";
            $text .= "• Запомните это слово\n";
            $text .= "• Не сообщайте его никому\n";
            $text .= "• Вводите его при каждой выгрузке данных\n\n";
            $text .= "Если забудете - обратитесь к суперадминистратору.";
            
            $keyboard = [
                [
                    ['text' => '✅ Понятно', 'callback_data' => 'export_key_menu']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        } else {
            $text = "❌ Ошибка при установке кодового слова.\n\nПопробуйте снова или обратитесь к администратору.";
            
            $keyboard = [
                [
                    ['text' => '🔄 Попробовать снова', 'callback_data' => 'export_key_setup']
                ],
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'export_key_menu']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
    }
    
    /**
     * Show export history (all users, paginated)
     */
    public function showExportHistory(string $chatId, int $userId, int $page = 1): ServerResponse
    {
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Get all export logs with pagination
        $logs = $this->exportSecurityService->getAllExportLogs($perPage, $offset);
        $text = $this->exportSecurityService->formatExportHistoryWithUsers($logs);
        
        // Add pagination info
        $totalLogs = $this->databaseService->getTotalExportLogsCount();
        $totalPages = max(1, ceil($totalLogs / $perPage));
        
        if ($totalPages > 1) {
            $text .= "\n\n📄 Страница {$page} из {$totalPages}\n";
            $text .= "Всего выгрузок: {$totalLogs}";
        }
        
        // Build keyboard with pagination
        $keyboard = [];
        
        // Pagination buttons
        if ($totalPages > 1) {
            $paginationRow = [];
            
            if ($page > 1) {
                $paginationRow[] = ['text' => '⬅️ Пред.', 'callback_data' => "export_history_page_" . ($page - 1)];
            }
            
            if ($page < $totalPages) {
                $paginationRow[] = ['text' => 'След. ➡️', 'callback_data' => "export_history_page_" . ($page + 1)];
            }
            
            if (!empty($paginationRow)) {
                $keyboard[] = $paginationRow;
            }
        }
        
        // Back button
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Cancel export key reset request
     */
    public function cancelExportKeyResetRequest(string $chatId, int $userId): ServerResponse
    {
        error_log("ExportKeyHandler: cancelExportKeyResetRequest called for user: {$userId}");
        
        $userData = $this->databaseService->getUserData($userId);
        $userData['export_key_reset_requests'] = ['pending' => false];
        $this->databaseService->updateUserData($userId, $userData);
        
        $text = "✅ <b>Запрос отменён</b>\n\n";
        $text .= "Ваш запрос на сброс кодового слова отменён.";
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'export_key_menu']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Request export key reset
     */
    public function requestExportKeyReset(string $chatId, int $userId): ServerResponse
    {
        error_log("ExportKeyHandler: requestExportKeyReset called for user: {$userId}");
        
        // Create reset request
        $userData = $this->databaseService->getUserData($userId);
        $resetRequests = $userData['export_key_reset_requests'] ?? [];
        
        error_log("ExportKeyHandler: Current reset requests: " . json_encode($resetRequests));
        
        // Check if already has pending request
        if (isset($resetRequests['pending']) && $resetRequests['pending']) {
            error_log("ExportKeyHandler: User {$userId} already has pending request. Re-notifying admins for testing.");
            
            // TEMPORARY: Re-notify admins even if request already exists (for testing)
            $this->notifySuperAdminsAboutResetRequest($userId);
            
            $text = "⚠️ <b>Запрос уже отправлен</b>\n\n";
            $text .= "Вы уже отправили запрос на сброс кодового слова.\n\n";
            $text .= "Пожалуйста, дождитесь рассмотрения суперадминистратором.\n\n";
            $text .= "(Уведомление суперадминам отправлено повторно для проверки)";
            
            $keyboard = [
                [
                    ['text' => '❌ Отменить запрос', 'callback_data' => 'export_key_cancel_reset']
                ],
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'export_key_menu']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Create new reset request
        $resetRequests['pending'] = true;
        $resetRequests['requested_at'] = date('Y-m-d H:i:s');
        $userData['export_key_reset_requests'] = $resetRequests;
        
        $this->databaseService->updateUserData($userId, $userData);
        
        // Notify user
        $text = "✅ <b>Запрос отправлен</b>\n\n";
        $text .= "Ваш запрос на сброс кодового слова отправлен суперадминистратору.\n\n";
        $text .= "Пожалуйста, дождитесь рассмотрения.";
        
        $keyboard = [
            [
                ['text' => '✅ Понятно', 'callback_data' => 'export_key_menu']
            ]
        ];
        
        // Notify all super admins
        error_log("ExportKeyHandler: About to call notifySuperAdminsAboutResetRequest for user {$userId}");
        $this->notifySuperAdminsAboutResetRequest($userId);
        error_log("ExportKeyHandler: Finished calling notifySuperAdminsAboutResetRequest for user {$userId}");
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Notify super admins (level 4 only) about reset request
     */
    private function notifySuperAdminsAboutResetRequest(int $userId): void
    {
        $userData = $this->databaseService->getUserData($userId);
        $userFio = $userData['fio'] ?? 'Unknown';
        
        // Get only super admins (level 4)
        $superAdmins = $this->databaseService->getAllSuperAdmins();
        
        error_log("ExportKeyHandler: Notifying super admins about reset request. User: {$userId}, Super Admins count: " . count($superAdmins));
        error_log("ExportKeyHandler: Super Admin IDs: " . implode(', ', $superAdmins));
        
        if (empty($superAdmins)) {
            error_log("ExportKeyHandler: WARNING - No super admins found!");
            return;
        }
        
        $text = "🔔 <b>Запрос на сброс кодового слова</b>\n\n";
        $text .= "Пользователь: {$userFio}\n";
        $text .= "User ID: {$userId}\n\n";
        $text .= "Запросил сброс кодового слова.\n\n";
        $text .= "Для сброса перейдите:\n";
        $text .= "Админка → Управление уровнями доступа → Запросы на сброс";
        
        foreach ($superAdmins as $adminId) {
            error_log("ExportKeyHandler: Sending notification to super admin: {$adminId}");
            $result = $this->messageService->sendMessage($adminId, $text);
            error_log("ExportKeyHandler: Notification sent to {$adminId}. Result: " . ($result->isOk() ? 'OK' : 'FAILED'));
        }
    }
    
    /**
     * Admin: Reset user's export key
     */
    public function adminResetExportKey(string $chatId, int $adminUserId, int $targetUserId): ServerResponse
    {
        // Verify admin is super admin
        $adminAccessLevel = $this->databaseService->getUserAccessLevel($adminUserId);
        
        if ($adminAccessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            $text = "⚠️ <b>Доступ запрещён</b>\n\nТолько суперадминистраторы могут сбрасывать кодовые слова.";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Generate new key
        $newKey = $this->exportSecurityService->generateSecureKey(8);
        
        // Set new key
        $success = $this->exportSecurityService->setExportKey($targetUserId, $newKey);
        
        if ($success) {
            $targetUserData = $this->databaseService->getUserData($targetUserId);
            $targetFio = $targetUserData['fio'] ?? 'Unknown';
            
            $text = "✅ <b>Кодовое слово сброшено</b>\n\n";
            $text .= "Пользователь: {$targetFio}\n";
            $text .= "User ID: {$targetUserId}\n\n";
            $text .= "Новое кодовое слово: <code>{$newKey}</code>\n\n";
            $text .= "Сообщите пользователю новое кодовое слово.";
            
            // Notify user
            $userText = "🔐 <b>Кодовое слово изменено</b>\n\n";
            $userText .= "Ваше кодовое слово было сброшено администратором.\n\n";
            $userText .= "Новое кодовое слово: <code>{$newKey}</code>\n\n";
            $userText .= "Запомните его для выгрузки данных.";
            
            $this->messageService->sendMessage($targetUserId, $userText);
            
            return $this->messageService->sendMessage($chatId, $text);
        } else {
            $text = "❌ Ошибка при сбросе кодового слова.";
            return $this->messageService->sendMessage($chatId, $text);
        }
    }
}
