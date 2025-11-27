<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ExportSecurityService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ExportKeyHandler;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for profile related callback queries
 */
class ProfileHandler implements CallbackHandlerInterface
{
    private MessageService $messageService;
    private AdminService $adminService;
    private DatabaseService $databaseService;
    private ExportSecurityService $exportSecurityService;
    private ExportKeyHandler $exportKeyHandler;

    public function __construct(MessageService $messageService)
    {
        error_log("ProfileHandler: Constructor called - STARTING INITIALIZATION");
        
        // Create services
        $this->messageService = $messageService;
        $this->adminService = new AdminService($messageService);
        $this->databaseService = new DatabaseService();
        $this->exportSecurityService = new ExportSecurityService($this->databaseService);
        $this->exportKeyHandler = new ExportKeyHandler(
            $this->messageService,
            $this->databaseService,
            $this->exportSecurityService
        );
        
        error_log("ProfileHandler: Constructor completed - INITIALIZATION SUCCESSFUL");
    }

    public function canHandle(string $callbackData): bool
    {
        // Add detailed logging
        error_log("ProfileHandler: canHandle called with callbackData: '{$callbackData}'");
        
        // Fix encoding issues with callback data
        $callbackData = $this->ensureUtf8Encoding($callbackData);
        
        // Дополнительная отладка
        error_log("ProfileHandler callbackData length: " . strlen($callbackData));
        error_log("ProfileHandler callbackData mb_strlen: " . mb_strlen($callbackData, 'UTF-8'));
        error_log("ProfileHandler callbackData hex: " . bin2hex($callbackData));
        error_log("ProfileHandler callbackData mb_check: " . (mb_check_encoding($callbackData, 'UTF-8') ? 'valid' : 'invalid'));
        
        // Проверяем, начинается ли callbackData с префикса категории профиля
        $canHandle = strpos($callbackData, 'category_Профиль') === 0;
        
        // Дополнительная проверка с использованием mb_strpos для корректной работы с UTF-8
        if (!$canHandle) {
            $canHandle = mb_strpos($callbackData, 'category_Профиль', 0, 'UTF-8') === 0;
        }
        
        // Проверяем callback'и, связанные с export key
        if (!$canHandle) {
            $canHandle = strpos($callbackData, 'export_key_') === 0;
        }
        
        error_log("ProfileHandler: canHandle result for '{$callbackData}': " . ($canHandle ? 'true' : 'false'));
        error_log("ProfileHandler: starts with category_Профиль: " . (strpos($callbackData, 'category_Профиль') === 0 ? 'true' : 'false'));
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("ProfileHandler handling callback for user: {$userId}, category: '{$category}'");
        // Removed file_put_contents for debugging to avoid permission issues

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        
        // Дополнительная отладка для понимания проблемы с кодировкой
        error_log("ProfileHandler category length: " . strlen($category));
        error_log("ProfileHandler category mb_strlen: " . mb_strlen($category, 'UTF-8'));
        error_log("ProfileHandler category hex: " . bin2hex($category));
        error_log("ProfileHandler category mb_check: " . (mb_check_encoding($category, 'UTF-8') ? 'valid' : 'invalid'));
        
        // Получаем callback_data напрямую из callbackQuery для дополнительной проверки
        $callbackData = $callbackQuery->getData();
        $callbackData = $this->ensureUtf8Encoding($callbackData);
        error_log("ProfileHandler direct callbackData: '{$callbackData}'");

        // Обрабатываем различные категории профиля
        if ($category === 'Профиль_Просмотр' || $callbackData === 'category_Профиль_Просмотр') {
            error_log("ProfileHandler showing profile for user: {$userId}");
            // Removed file_put_contents for debugging to avoid permission issues
            return $this->showProfile($chatId, $userId);
        }
        
        if ($category === 'Профиль_ФИО' || $callbackData === 'category_Профиль_ФИО') {
            error_log("ProfileHandler setting FIO input state for user: {$userId}");
            return $this->setFioInputState($chatId, $userId, $conversation);
        }
        
        if ($category === 'Профиль_ФН' || $callbackData === 'category_Профиль_ФН') {
            error_log("ProfileHandler showing functional direction menu for user: {$userId}");
            return $this->showFunctionalDirectionMenu($chatId);
        }
        
        // Обработка подкатегорий функционального направления
        if ($category === 'Профиль_ФН_МобильнаяБригада' || $callbackData === 'category_Профиль_ФН_МобильнаяБригада') {
            error_log("ProfileHandler handling mobile brigade selection for user: {$userId}");
            return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Мобильная бригада');
        }
        
        if ($category === 'Профиль_ФН_Пусконаладка' || $callbackData === 'category_Профиль_ФН_Пусконаладка') {
            error_log("ProfileHandler handling commissioning selection for user: {$userId}");
            return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Пусконаладка');
        }
        
        if ($category === 'Профиль_ФН_ШефМонтаж' || $callbackData === 'category_Профиль_ФН_ШефМонтаж') {
            error_log("ProfileHandler handling supervision selection for user: {$userId}");
            return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Шеф-монтаж');
        }
        
        if ($category === 'Профиль_ФН_Тестировщик' || $callbackData === 'category_Профиль_ФН_Тестировщик') {
            error_log("ProfileHandler handling tester selection for user: {$userId}");
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
        }
        
        // Handle export key management callbacks
        if ($callbackData === 'export_key_menu') {
            error_log("ProfileHandler showing export key menu for user: {$userId}");
            return $this->exportKeyHandler->showExportKeyMenu($chatId, $userId);
        }
        
        if ($callbackData === 'export_key_setup' || $callbackData === 'export_key_change') {
            error_log("ProfileHandler starting export key setup for user: {$userId}");
            return $this->exportKeyHandler->startExportKeySetup($chatId, $userId, $conversation);
        }
        
        if ($callbackData === 'export_key_history') {
            error_log("ProfileHandler showing export history for user: {$userId}");
            return $this->exportKeyHandler->showExportHistory($chatId, $userId);
        }
        
        if ($callbackData === 'export_key_request_reset') {
            error_log("ProfileHandler requesting export key reset for user: {$userId}");
            return $this->exportKeyHandler->requestExportKeyReset($chatId, $userId);
        }
        
        if ($callbackData === 'export_key_cancel_reset') {
            error_log("ProfileHandler canceling export key reset for user: {$userId}");
            return $this->exportKeyHandler->cancelExportKeyResetRequest($chatId, $userId);
        }
        
        // Обработка основной категории Профиль
        if ($category === 'Профиль' || $callbackData === 'category_Профиль') {
            error_log("ProfileHandler showing profile for user: {$userId}");
            return $this->showProfileMenu($chatId, $userId);
        }

        error_log("ProfileHandler unknown category: '{$category}', callbackData: '{$callbackData}'");
        // Fix encoding for error message
        $category = $this->ensureUtf8Encoding($category);
        return $this->messageService->sendMessage($chatId, 'Неизвестная команда профиля: ' . $category);
    }

    public function getPriority(): int
    {
        return 15; // Same as before, higher than FunctionalDirectionHandler (10)
    }

    /**
     * Show profile menu with options
     */
    private function showProfileMenu(string $chatId, int $userId): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $currentFio = $userData['fio'] ?? 'Без ФИО';
        $currentFn = $userData['functional_direction'] ?? 'Не указано';
        $accessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($accessLevel);
        
        $text = '<b>👤 Ваш профиль</b>' . PHP_EOL . PHP_EOL .
            '<b>ФИО:</b> ' . $currentFio . PHP_EOL .
            '<b>Функциональное направление:</b> ' . $currentFn . PHP_EOL .
            '<b>Уровень доступа:</b> ' . $levelName;
        
        // Add export key status only for supervisors and above
        if ($accessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            $hasExportKey = $this->exportSecurityService->hasExportKey($userId);
            $exportKeyStatus = $hasExportKey ? '✅ Установлено' : '⚠️ Не установлено';
            $text .= PHP_EOL . '<b>Кодовое слово:</b> ' . $exportKeyStatus;
        }
        
        $keyboard = [
            [
                ['text' => '📝 Изменить ФИО', 'callback_data' => 'category_Профиль_ФИО']
            ],
            [
                ['text' => '🏢 Изменить направление', 'callback_data' => 'category_Профиль_ФН']
            ]
        ];
        
        // Добавляем кнопку кодового слова только для супервайзеров, админов и суперадминов (уровень 2+)
        if ($accessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            $keyboard[] = [
                ['text' => '🔐 Кодовое слово', 'callback_data' => 'export_key_menu']
            ];
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Главное меню', 'callback_data' => 'category_Главное']
        ];
        
        if ($accessLevel == 0) {
            $text .= PHP_EOL . PHP_EOL .
                '⚠️ <b>Уровень доступа "Гость"</b>' . PHP_EOL .
                'Для получения расширенного доступа нажмите "🔐 Запросить доступ" в главном меню.';
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show profile information (view-only)
     */
    private function showProfile(string $chatId, int $userId): ServerResponse
    {
        // Получаем данные пользователя
        $userData = \pDB::getUserData($userId);
        error_log("ProfileHandler: User data for user {$userId}: " . print_r($userData, true));
        $currentFio = $userData['fio'] ?? 'Без ФИО';
        $currentFn = $userData['functional_direction'] ?? 'Не указано';
        
        // Получаем уровень доступа пользователя
        $accessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($accessLevel);
        
        // Ensure UTF-8 encoding for all text
        $currentFio = mb_convert_encoding($currentFio, 'UTF-8', 'auto');
        $currentFn = mb_convert_encoding($currentFn, 'UTF-8', 'auto');
        $levelName = mb_convert_encoding($levelName, 'UTF-8', 'auto');
        
        // Для гостевых пользователей показываем профиль без кнопки "Запросить доступ" (кнопка теперь в главном меню)
        if ($accessLevel == 0) { // Guest level
            $text = '<b>👤 Ваш профиль</b>' . PHP_EOL . PHP_EOL .
                '<b>ФИО:</b> ' . $currentFio . PHP_EOL .
                '<b>Функциональное направление:</b> ' . $currentFn . PHP_EOL .
                '<b>Уровень доступа:</b> ' . $levelName . PHP_EOL . PHP_EOL .
                '⚠️ Уровень доступа "Гость" - Для получения расширенного доступа нажмите кнопку "🔐 Запросить доступ" в главном меню.';
                
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            
            return $this->messageService->sendMessage($chatId, $text);
        } else {
            // Для пользователей с уровнем доступа выше гостя показываем полную информацию
            $text = '<b>👤 Ваш профиль</b>' . PHP_EOL . PHP_EOL .
                '<b>ФИО:</b> ' . $currentFio . PHP_EOL .
                '<b>Функциональное направление:</b> ' . $currentFn . PHP_EOL .
                '<b>Уровень доступа:</b> ' . $levelName . PHP_EOL .
                '<b>ID уровня:</b> ' . $accessLevel;
                
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            
            return $this->messageService->sendMessage($chatId, $text);
        }
    }

    private function showFioChangeInstructions(string $chatId): ServerResponse
    {
        $text = '<b>👤 Изменение ФИО</b>' . PHP_EOL . PHP_EOL .
            '📝 Для изменения ФИО нажмите кнопку "✏️ Изменить ФИО" в меню профиля, ' .
            'затем введите ваше ФИО в текстовом формате.' . PHP_EOL . PHP_EOL .
            '<b>Пример:</b>' . PHP_EOL .
            'Иванов Иван Иванович';
            
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        
        return $this->messageService->sendMessage($chatId, $text);
    }

    private function setFioInputState(string $chatId, int $userId, Conversation $conversation): ServerResponse
    {
        // Set the conversation state to expect FIO input
        $conversation->notes['awaiting_fio_input'] = true;
        $conversation->update();
        
        $text = '<b>👤 Введите ваше ФИО</b>' . PHP_EOL . PHP_EOL .
            'Пожалуйста, введите ваше полное ФИО в формате:' . PHP_EOL .
            'Фамилия Имя Отчество' . PHP_EOL . PHP_EOL .
            '<b>Пример:</b>' . PHP_EOL .
            'Иванов Иван Иванович';
            
        $text = $this->ensureUtf8Encoding($text);
        
        return $this->messageService->sendMessage($chatId, $text);
    }
    
    /**
     * Handle FIO update from text input
     */
    public function handleFioUpdate(string $text, int $userId, string $chatId): ServerResponse
    {
        // Validate FIO length
        if (strlen(trim($text)) < 3) {
            $responseText = '<b>❌ ФИО слишком короткое!</b>' . PHP_EOL .
                'Минимум 3 символа.' . PHP_EOL . PHP_EOL .
                'Пожалуйста, введите ваше полное ФИО в формате:' . PHP_EOL .
                'Фамилия Имя Отчество' . PHP_EOL . PHP_EOL .
                '<b>Пример:</b>' . PHP_EOL .
                'Иванов Иван Иванович';
                
            $responseText = $this->ensureUtf8Encoding($responseText);
            
            return $this->messageService->sendMessage($chatId, $responseText);
        }

        // Update FIO in database
        $success = \pDB::updateUserFio($userId, trim($text));
        
        if ($success) {
            $responseText = "<b>✅ ФИО обновлено:</b> " . trim($text) . PHP_EOL . PHP_EOL .
                '📝 Используйте кнопку "👤 Просмотр профиля" для проверки изменений';
                
            $responseText = $this->ensureUtf8Encoding($responseText);
            
            return $this->messageService->sendMessage($chatId, $responseText);
        } else {
            $responseText = '<b>❌ Ошибка при обновлении ФИО</b>';
            $responseText = $this->ensureUtf8Encoding($responseText);
            
            return $this->messageService->sendMessage($chatId, $responseText);
        }
    }
    
    private function showFunctionalDirectionMenu(string $chatId): ServerResponse
    {
        // Get user access level - we need to create AdminService to check it
        $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($this->messageService);
        $userId = (int)$chatId;
        $userAccessLevel = $adminService->getUserAccessLevel($userId);
        
        $keyboard = [
            [
                ['text' => '👷‍♂️ Мобильная бригада', 'callback_data' => 'category_Профиль_ФН_МобильнаяБригада']
            ],
            [
                ['text' => '⚙️ Пусконаладка', 'callback_data' => 'category_Профиль_ФН_Пусконаладка']
            ],
            [
                ['text' => '🔧 Шеф-монтаж', 'callback_data' => 'category_Профиль_ФН_ШефМонтаж']
            ]
        ];
        
        // Add Тестировщик option only for admins and super admins
        if ($userAccessLevel >= \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_ADMIN) {
            $keyboard[] = [
                ['text' => '🧪 Тестировщик', 'callback_data' => 'category_Профиль_ФН_Тестировщик']
            ];
        }
        
        $text = '<b>🎯 Выберите новое функциональное направление:</b>' . PHP_EOL . PHP_EOL .
            '• Мобильная бригада' . PHP_EOL .
            '• Пусконаладка' . PHP_EOL .
            '• Шеф-монтаж';
            
        // Add Тестировщик in text for admins
        if ($userAccessLevel >= \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService::ACCESS_LEVEL_ADMIN) {
            $text .= PHP_EOL . '• Тестировщик (только для администраторов)';
        }
            
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function handleFunctionalDirectionUpdate(string $chatId, int $userId, string $functionalDirection): ServerResponse
    {
        // Ensure UTF-8 encoding
        $functionalDirection = mb_convert_encoding($functionalDirection, 'UTF-8', 'auto');
        
        // Обновляем функциональное направление в базе данных
        $success = \pDB::updateUserFunctionalDirection($userId, $functionalDirection);
        
        if ($success) {
            $text = '<b>✅ Функциональное направление успешно обновлено!</b>' . PHP_EOL . PHP_EOL .
                '<b>Новое ФН:</b> ' . $functionalDirection;
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            
            return $this->messageService->sendMessage($chatId, $text);
        } else {
            $text = '<b>❌ Ошибка при обновлении функционального направления</b>' . PHP_EOL .
                'Попробуйте еще раз позже.';
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            
            return $this->messageService->sendMessage($chatId, $text);
        }
    }

    /**
     * Ensure UTF-8 encoding for text
     */
    private function ensureUtf8Encoding($text)
    {
        // If already UTF-8, return as is
        if (mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }
        
        // Try to convert from Windows-1251 to UTF-8
        $converted = mb_convert_encoding($text, 'UTF-8', 'Windows-1251');
        if (mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }
        
        // Fallback to auto-detection
        return mb_convert_encoding($text, 'UTF-8');
    }
}