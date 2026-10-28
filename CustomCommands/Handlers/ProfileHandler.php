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
        
        // Проверяем callback'и, связанные с внешним подключением (PWA)
        if (!$canHandle) {
            $canHandle = strpos($callbackData, 'pwa_') === 0;
        }
        
        // Проверяем callback'и, связанные со сбросом пароля пользователя (суперадмин)
        if (!$canHandle) {
            $canHandle = strpos($callbackData, 'admin_rst_pwd') === 0;
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
        
        if ($category === 'Профиль_ФН_Менеджер' || $callbackData === 'category_Профиль_ФН_Менеджер') {
            error_log("ProfileHandler handling manager selection for user: {$userId}");
            return $this->handleFunctionalDirectionUpdate($chatId, $userId, 'Менеджер');
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
        
        // PWA / Внешнее подключение callbacks
        if ($callbackData === 'pwa_menu') {
            return $this->showPwaMenu($chatId, $userId);
        }
        if ($callbackData === 'pwa_set_password') {
            $conversation->notes['awaiting_web_password_input'] = true;
            $conversation->update();
            return $this->messageService->sendMessage(
                $chatId,
                '<b>🔑 Установка пароля для веб-доступа</b>' . PHP_EOL . PHP_EOL .
                'Введите новый пароль (минимум 6 символов):' . PHP_EOL .
                'Пароль будет использоваться для входа в веб-приложение.'
            );
        }
        if ($callbackData === 'pwa_set_login') {
            $conversation->notes['awaiting_web_login_input'] = true;
            $conversation->update();
            return $this->messageService->sendMessage(
                $chatId,
                '<b>✏️ Изменение логина для веб-доступа</b>' . PHP_EOL . PHP_EOL .
                'Введите новый логин:' . PHP_EOL .
                'Логин будет использоваться для входа в веб-приложение.'
            );
        }
        if ($callbackData === 'pwa_reset_password') {
            return $this->handleResetPassword($chatId, $userId);
        }

        // Сброс пароля пользователя (суперадмин)
        if ($callbackData === 'admin_rst_pwd') {
            return $this->showAdminResetPasswordUserList($chatId, $userId, 0);
        }
        if (strpos($callbackData, 'admin_rst_pwd_p_') === 0) {
            $page = (int) substr($callbackData, strlen('admin_rst_pwd_p_'));
            return $this->showAdminResetPasswordUserList($chatId, $userId, $page);
        }
        if (strpos($callbackData, 'admin_rst_pwd_u_') === 0) {
            $targetTgId = (int) substr($callbackData, strlen('admin_rst_pwd_u_'));
            return $this->showAdminResetPasswordUserDetail($chatId, $userId, $targetTgId);
        }
        if (strpos($callbackData, 'admin_rst_pwd_gen_') === 0) {
            $targetTgId = (int) substr($callbackData, strlen('admin_rst_pwd_gen_'));
            return $this->adminGeneratePassword($chatId, $userId, $targetTgId);
        }
        if (strpos($callbackData, 'admin_rst_pwd_inp_') === 0) {
            $targetTgId = (int) substr($callbackData, strlen('admin_rst_pwd_inp_'));
            return $this->adminPromptCustomPassword($chatId, $userId, $targetTgId, $conversation);
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
        
        // Кнопка внешнего подключения (PWA) для всех уровней доступа выше гостя
        if ($accessLevel >= 1) {
            $keyboard[] = [
                ['text' => '🌐 Внешнее подключение', 'callback_data' => 'pwa_menu']
            ];
        }
        
        // Кнопка сброса пароля пользователя - только для суперадминистраторов (уровень 4)
        if ($accessLevel >= DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            $keyboard[] = [
                ['text' => '🔑 Сброс пароля пользователя', 'callback_data' => 'admin_rst_pwd']
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
            // Sync username in pwa_users to match FIO
            try {
                $db = $this->databaseService->getConnection();
                $newFio = trim($text);
                $stmt = $db->prepare("UPDATE pwa_users SET username = ?, fio = ? WHERE linked_telegram_id = ?");
                if ($stmt) {
                    $stmt->bind_param('ssi', $newFio, $newFio, $userId);
                    $stmt->execute();
                    $stmt->close();
                }
            } catch (\Throwable $e) {
                // Non-critical, don't block FIO update
            }

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
            ],
            [
                ['text' => '📊 Менеджер', 'callback_data' => 'category_Профиль_ФН_Менеджер']
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
            '• Шеф-монтаж' . PHP_EOL .
            '• Менеджер';
            
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
     * Show PWA / Внешнее подключение menu
     */
    private function showPwaMenu(string $chatId, int $userId): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $fio = $userData['fio'] ?? 'Без ФИО';

        // Check if user has a PWA account (table may not exist yet)
        $pwaUser = null;
        try {
            $db = $this->databaseService->getConnection();
            $stmt = $db->prepare("SELECT id, username FROM pwa_users WHERE linked_telegram_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $pwaUser = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }
        } catch (\Throwable $e) {
            error_log("ProfileHandler::showPwaMenu: pwa_users query failed: " . $e->getMessage());
        }

        if ($pwaUser) {
            $text = '<b>🌐 Внешнее подключение</b>' . PHP_EOL . PHP_EOL .
                '<b>Статус:</b> ✅ Настроено' . PHP_EOL .
                '<b>Логин:</b> <code>' . htmlspecialchars($pwaUser['username']) . '</code>' . PHP_EOL .
                '<b>Пароль:</b> установлен' . PHP_EOL . PHP_EOL .
                'Используйте эти данные для входа в веб-приложение.';
        } else {
            $text = '<b>🌐 Внешнее подключение</b>' . PHP_EOL . PHP_EOL .
                '<b>Статус:</b> ⚠️ Не настроено' . PHP_EOL .
                '<b>Логин будет:</b> <code>' . htmlspecialchars($fio) . '</code>' . PHP_EOL . PHP_EOL .
                'Установите пароль для активации веб-доступа.';
        }

        $keyboard = [];

        if ($pwaUser) {
            $keyboard[] = [
                ['text' => '🔑 Изменить пароль', 'callback_data' => 'pwa_set_password']
            ];
            $keyboard[] = [
                ['text' => '🔄 Сбросить пароль (показать новый)', 'callback_data' => 'pwa_reset_password']
            ];
        } else {
            $keyboard[] = [
                ['text' => '🔑 Установить пароль', 'callback_data' => 'pwa_set_password']
            ];
        }

        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Профиль']
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Handle web password input from user text message
     */
    public function handleWebPasswordUpdate(string $password, int $userId, string $chatId): ServerResponse
    {
        if (mb_strlen($password) < 6) {
            return $this->messageService->sendMessage(
                $chatId,
                '<b>❌ Пароль слишком короткий!</b>' . PHP_EOL .
                'Минимум 6 символов. Попробуйте ещё раз:'
            );
        }

        $userData = $this->databaseService->getUserData($userId);
        $fio = $userData['fio'] ?? '';
        $fn  = $userData['functional_direction'] ?? 'Не указано';
        $accessLevel = (int) ($userData['access_level'] ?? 0);

        if (trim($fio) === '') {
            return $this->messageService->sendMessage(
                $chatId,
                '<b>❌ Сначала установите ФИО в профиле!</b>' . PHP_EOL .
                'Перейдите в "📝 Изменить ФИО" и укажите ваше имя.'
            );
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $db = $this->databaseService->getConnection();

        // Ensure pwa_users table exists
        $db->query("
            CREATE TABLE IF NOT EXISTS pwa_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                linked_telegram_id BIGINT NULL UNIQUE,
                access_level TINYINT DEFAULT 0,
                fio VARCHAR(255),
                functional_direction VARCHAR(100),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_telegram_id (linked_telegram_id),
                INDEX idx_username (username)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Check if pwa_users record exists
        $stmt = $db->prepare("SELECT id, username FROM pwa_users WHERE linked_telegram_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            // Update password
            $stmt2 = $db->prepare("UPDATE pwa_users SET password_hash = ?, fio = ?, functional_direction = ?, access_level = ? WHERE id = ?");
            $stmt2->bind_param('sssii', $hash, $fio, $fn, $accessLevel, $existing['id']);
            $stmt2->execute();
            $stmt2->close();
            $username = $existing['username'];
        } else {
            // Create new record (login = FIO)
            $username = $fio;

            // Check for duplicate usernames and append suffix if needed
            $checkStmt = $db->prepare("SELECT COUNT(*) as cnt FROM pwa_users WHERE LOWER(username) = LOWER(?)");
            $checkStmt->bind_param('s', $username);
            $checkStmt->execute();
            $cnt = $checkStmt->get_result()->fetch_assoc()['cnt'];
            $checkStmt->close();
            if ($cnt > 0) {
                $username = $fio . '_' . $userId;
            }

            $isActive = 1;
            $stmt2 = $db->prepare(
                "INSERT INTO pwa_users (username, password_hash, linked_telegram_id, access_level, fio, functional_direction, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt2->bind_param('ssisssi', $username, $hash, $userId, $accessLevel, $fio, $fn, $isActive);
            $stmt2->execute();
            $stmt2->close();
        }

        $text = '<b>✅ Пароль для веб-доступа установлен!</b>' . PHP_EOL . PHP_EOL .
            '<b>Логин:</b> <code>' . htmlspecialchars($username) . '</code>' . PHP_EOL .
            '<b>Пароль:</b> <code>' . htmlspecialchars($password) . '</code>' . PHP_EOL . PHP_EOL .
            '⚠️ Запомните или запишите данные! Пароль показывается только один раз.' . PHP_EOL .
            '🌐 Теперь вы можете войти в веб-приложение.';

        $keyboard = [
            [
                ['text' => '⬅️ Назад к профилю', 'callback_data' => 'category_Профиль']
            ]
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Handle login (username) change from text input
     */
    public function handleWebLoginUpdate(string $newLogin, int $userId, string $chatId): ServerResponse
    {
        $newLogin = trim($newLogin);

        if (mb_strlen($newLogin) < 2) {
            return $this->messageService->sendMessage(
                $chatId,
                '<b>❌ Логин слишком короткий!</b>' . PHP_EOL .
                'Минимум 2 символа. Попробуйте ещё раз:'
            );
        }

        $db = $this->databaseService->getConnection();

        // Check uniqueness
        $checkStmt = $db->prepare("SELECT id FROM pwa_users WHERE LOWER(username) = LOWER(?) AND linked_telegram_id != ?");
        $checkStmt->bind_param('si', $newLogin, $userId);
        $checkStmt->execute();
        $dup = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($dup) {
            return $this->messageService->sendMessage(
                $chatId,
                '<b>❌ Такой логин уже занят!</b>' . PHP_EOL .
                'Попробуйте другой:'
            );
        }

        $stmt = $db->prepare("UPDATE pwa_users SET username = ? WHERE linked_telegram_id = ?");
        $stmt->bind_param('si', $newLogin, $userId);
        $stmt->execute();
        $stmt->close();

        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pwa_menu']
            ]
        ];

        return $this->messageService->sendMessage(
            $chatId,
            '<b>✅ Логин изменён!</b>' . PHP_EOL . PHP_EOL .
            '<b>Новый логин:</b> <code>' . htmlspecialchars($newLogin) . '</code>',
            $keyboard
        );
    }

    /**
     * Reset password: generate a new random one and show it to the user
     */
    private function handleResetPassword(string $chatId, int $userId): ServerResponse
    {
        $db = $this->databaseService->getConnection();

        // Generate strong random password
        $upper  = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower  = 'abcdefghjkmnpqrstuvwxyz';
        $digits = '23456789';
        $all    = $upper . $lower . $digits;
        $pwd  = $upper[random_int(0, strlen($upper) - 1)];
        $pwd .= $lower[random_int(0, strlen($lower) - 1)];
        $pwd .= $digits[random_int(0, strlen($digits) - 1)];
        for ($i = 3; $i < 10; $i++) {
            $pwd .= $all[random_int(0, strlen($all) - 1)];
        }
        $pwd = str_shuffle($pwd);

        $hash = password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $db->prepare("UPDATE pwa_users SET password_hash = ? WHERE linked_telegram_id = ?");
        $stmt->bind_param('si', $hash, $userId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            return $this->messageService->sendMessage(
                $chatId,
                '<b>❌ Аккаунт не найден.</b>' . PHP_EOL .
                'Сначала установите пароль через кнопку "Установить пароль".'
            );
        }

        // Get username for display
        $stmt2 = $db->prepare("SELECT username FROM pwa_users WHERE linked_telegram_id = ?");
        $stmt2->bind_param('i', $userId);
        $stmt2->execute();
        $row = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
        $username = $row['username'] ?? '?';

        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pwa_menu']
            ]
        ];

        return $this->messageService->sendMessage(
            $chatId,
            '<b>🔄 Пароль сброшен!</b>' . PHP_EOL . PHP_EOL .
            '<b>Логин:</b> <code>' . htmlspecialchars($username) . '</code>' . PHP_EOL .
            '<b>Новый пароль:</b> <code>' . $pwd . '</code>' . PHP_EOL . PHP_EOL .
            '⚠️ Запомните или запишите пароль! Он показывается только один раз.',
            $keyboard
        );
    }

    // ==========================================
    // Сброс пароля пользователя (Суперадмин)
    // ==========================================

    /**
     * Show list of PWA users for password reset (superadmin only)
     */
    private function showAdminResetPasswordUserList(string $chatId, int $adminUserId, int $page = 0): ServerResponse
    {
        // Verify superadmin access
        $accessLevel = $this->adminService->getUserAccessLevel($adminUserId);
        if ($accessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Доступ запрещен.</b> Только для суперадминистраторов.');
        }

        $db = $this->databaseService->getConnection();
        $perPage = 8;
        $offset = $page * $perPage;

        // Count total
        $countResult = $db->query("SELECT COUNT(*) as cnt FROM pwa_users WHERE is_active = 1");
        $total = $countResult ? (int) $countResult->fetch_assoc()['cnt'] : 0;

        if ($total === 0) {
            $keyboard = [
                [['text' => '⬅️ Назад', 'callback_data' => 'category_Профиль']]
            ];
            return $this->messageService->sendMessage(
                $chatId,
                '<b>🔑 Сброс пароля пользователя</b>' . PHP_EOL . PHP_EOL .
                'Нет пользователей с веб-доступом.',
                $keyboard
            );
        }

        $stmt = $db->prepare(
            "SELECT id, username, fio, linked_telegram_id FROM pwa_users WHERE is_active = 1 ORDER BY fio ASC, username ASC LIMIT ? OFFSET ?"
        );
        $stmt->bind_param('ii', $perPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();

        $totalPages = (int) ceil($total / $perPage);
        $text = '<b>🔑 Сброс пароля пользователя</b>' . PHP_EOL . PHP_EOL .
            'Выберите пользователя для сброса пароля:' . PHP_EOL .
            '<i>Страница ' . ($page + 1) . ' из ' . $totalPages . ' (всего: ' . $total . ')</i>';

        $keyboard = [];
        foreach ($users as $u) {
            $displayName = $u['fio'] ?: $u['username'];
            $tgId = (int) $u['linked_telegram_id'];
            $keyboard[] = [
                ['text' => $displayName, 'callback_data' => 'admin_rst_pwd_u_' . $tgId]
            ];
        }

        // Pagination
        $navRow = [];
        if ($page > 0) {
            $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => 'admin_rst_pwd_p_' . ($page - 1)];
        }
        if ($page < $totalPages - 1) {
            $navRow[] = ['text' => '➡️ Далее', 'callback_data' => 'admin_rst_pwd_p_' . ($page + 1)];
        }
        if (!empty($navRow)) {
            $keyboard[] = $navRow;
        }

        $keyboard[] = [
            ['text' => '⬅️ К профилю', 'callback_data' => 'category_Профиль']
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show user detail before password reset (superadmin only)
     */
    private function showAdminResetPasswordUserDetail(string $chatId, int $adminUserId, int $targetTgId): ServerResponse
    {
        $accessLevel = $this->adminService->getUserAccessLevel($adminUserId);
        if ($accessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Доступ запрещен.</b>');
        }

        $db = $this->databaseService->getConnection();
        $stmt = $db->prepare("SELECT username, fio FROM pwa_users WHERE linked_telegram_id = ? AND is_active = 1");
        $stmt->bind_param('i', $targetTgId);
        $stmt->execute();
        $pwaUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$pwaUser) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Пользователь не найден.</b>');
        }

        $text = '<b>🔑 Сброс пароля</b>' . PHP_EOL . PHP_EOL .
            '<b>Пользователь:</b> ' . htmlspecialchars($pwaUser['fio'] ?: $pwaUser['username']) . PHP_EOL .
            '<b>Логин:</b> <code>' . htmlspecialchars($pwaUser['username']) . '</code>' . PHP_EOL . PHP_EOL .
            'Выберите способ сброса пароля:';

        $keyboard = [
            [
                ['text' => '🎲 Сгенерировать временный пароль', 'callback_data' => 'admin_rst_pwd_gen_' . $targetTgId]
            ],
            [
                ['text' => '✏️ Ввести пароль вручную', 'callback_data' => 'admin_rst_pwd_inp_' . $targetTgId]
            ],
            [
                ['text' => '⬅️ К списку', 'callback_data' => 'admin_rst_pwd']
            ]
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Generate a random temp password for user (superadmin only)
     */
    private function adminGeneratePassword(string $chatId, int $adminUserId, int $targetTgId): ServerResponse
    {
        $accessLevel = $this->adminService->getUserAccessLevel($adminUserId);
        if ($accessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Доступ запрещен.</b>');
        }

        $db = $this->databaseService->getConnection();

        // Get user info
        $stmt = $db->prepare("SELECT username, fio FROM pwa_users WHERE linked_telegram_id = ? AND is_active = 1");
        $stmt->bind_param('i', $targetTgId);
        $stmt->execute();
        $pwaUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$pwaUser) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Пользователь не найден.</b>');
        }

        // Generate temp password
        $upper  = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower  = 'abcdefghjkmnpqrstuvwxyz';
        $digits = '23456789';
        $all    = $upper . $lower . $digits;
        $pwd  = $upper[random_int(0, strlen($upper) - 1)];
        $pwd .= $lower[random_int(0, strlen($lower) - 1)];
        $pwd .= $digits[random_int(0, strlen($digits) - 1)];
        for ($i = 3; $i < 10; $i++) {
            $pwd .= $all[random_int(0, strlen($all) - 1)];
        }
        $pwd = str_shuffle($pwd);

        $hash = password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt2 = $db->prepare("UPDATE pwa_users SET password_hash = ? WHERE linked_telegram_id = ?");
        $stmt2->bind_param('si', $hash, $targetTgId);
        $stmt2->execute();
        $affected = $stmt2->affected_rows;
        $stmt2->close();

        if ($affected === 0) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Ошибка при сбросе пароля.</b>');
        }

        error_log("Admin {$adminUserId} reset password for user TG:{$targetTgId} ({$pwaUser['fio']})");

        $keyboard = [
            [['text' => '⬅️ К списку пользователей', 'callback_data' => 'admin_rst_pwd']],
            [['text' => '⬅️ К профилю', 'callback_data' => 'category_Профиль']]
        ];

        return $this->messageService->sendMessage(
            $chatId,
            '<b>✅ Пароль сброшен!</b>' . PHP_EOL . PHP_EOL .
            '<b>Пользователь:</b> ' . htmlspecialchars($pwaUser['fio'] ?: $pwaUser['username']) . PHP_EOL .
            '<b>Логин:</b> <code>' . htmlspecialchars($pwaUser['username']) . '</code>' . PHP_EOL .
            '<b>Временный пароль:</b> <code>' . $pwd . '</code>' . PHP_EOL . PHP_EOL .
            '⚠️ Передайте эти данные пользователю. Пароль показывается только один раз.' . PHP_EOL .
            'Рекомендуйте пользователю сменить пароль при первом входе.',
            $keyboard
        );
    }

    /**
     * Prompt superadmin to enter custom password for user
     */
    private function adminPromptCustomPassword(string $chatId, int $adminUserId, int $targetTgId, Conversation $conversation): ServerResponse
    {
        $accessLevel = $this->adminService->getUserAccessLevel($adminUserId);
        if ($accessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Доступ запрещен.</b>');
        }

        $conversation->notes['awaiting_admin_reset_pwd'] = true;
        $conversation->notes['admin_reset_pwd_target'] = $targetTgId;
        $conversation->update();

        return $this->messageService->sendMessage(
            $chatId,
            '<b>✏️ Введите временный пароль</b>' . PHP_EOL . PHP_EOL .
            'Введите новый временный пароль для пользователя (минимум 6 символов):'
        );
    }

    /**
     * Handle custom password input from superadmin
     */
    public function handleAdminResetPasswordInput(string $password, int $adminUserId, int $targetTgId, string $chatId): ServerResponse
    {
        $accessLevel = $this->adminService->getUserAccessLevel($adminUserId);
        if ($accessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Доступ запрещен.</b>');
        }

        if (mb_strlen($password) < 6) {
            return $this->messageService->sendMessage(
                $chatId,
                '<b>❌ Пароль слишком короткий!</b>' . PHP_EOL .
                'Минимум 6 символов. Попробуйте ещё раз:'
            );
        }

        $db = $this->databaseService->getConnection();

        $stmt = $db->prepare("SELECT username, fio FROM pwa_users WHERE linked_telegram_id = ? AND is_active = 1");
        $stmt->bind_param('i', $targetTgId);
        $stmt->execute();
        $pwaUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$pwaUser) {
            return $this->messageService->sendMessage($chatId, '<b>❌ Пользователь не найден.</b>');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt2 = $db->prepare("UPDATE pwa_users SET password_hash = ? WHERE linked_telegram_id = ?");
        $stmt2->bind_param('si', $hash, $targetTgId);
        $stmt2->execute();
        $stmt2->close();

        error_log("Admin {$adminUserId} set custom password for user TG:{$targetTgId} ({$pwaUser['fio']})");

        $keyboard = [
            [['text' => '⬅️ К списку пользователей', 'callback_data' => 'admin_rst_pwd']],
            [['text' => '⬅️ К профилю', 'callback_data' => 'category_Профиль']]
        ];

        return $this->messageService->sendMessage(
            $chatId,
            '<b>✅ Пароль установлен!</b>' . PHP_EOL . PHP_EOL .
            '<b>Пользователь:</b> ' . htmlspecialchars($pwaUser['fio'] ?: $pwaUser['username']) . PHP_EOL .
            '<b>Логин:</b> <code>' . htmlspecialchars($pwaUser['username']) . '</code>' . PHP_EOL .
            '<b>Пароль:</b> <code>' . htmlspecialchars($password) . '</code>' . PHP_EOL . PHP_EOL .
            '⚠️ Передайте эти данные пользователю. Пароль показывается только один раз.' . PHP_EOL .
            'Рекомендуйте пользователю сменить пароль при первом входе.',
            $keyboard
        );
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