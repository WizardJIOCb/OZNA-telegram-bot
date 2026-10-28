<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ExportSecurityService;
use Longman\TelegramBot\Commands\SystemCommands\Services\FunctionalDirectionService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ExportKeyHandler;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for access level related callback queries
 */
class AccessLevelHandler implements CallbackHandlerInterface
{
    private AdminService $adminService;
    private MessageService $messageService;

    public function __construct(AdminService $adminService, MessageService $messageService)
    {
        $this->adminService = $adminService;
        $this->messageService = $messageService;
    }

    public function canHandle(string $callbackData): bool
    {
        // Fix encoding issues with callback data
        $callbackData = $this->fixEncoding($callbackData);
        
        // Check for category-based access level callbacks
        $isCategoryAccessLevel = strpos($callbackData, 'category_УровеньДоступа') === 0;
        
        // Also check for corrupted UTF-8 version and fix if needed
        if (!$isCategoryAccessLevel) {
            $isCategoryAccessLevel = strpos($callbackData, 'category_РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°') === 0;
            // If we found the corrupted version, we need to fix it
            if ($isCategoryAccessLevel) {
                $callbackData = $this->fixKnownCyrillicPatterns($callbackData);
                $isCategoryAccessLevel = strpos($callbackData, 'category_УровеньДоступа') === 0;
            }
        }
        
        // Check for new access level management callbacks
        $isAccessLevelManagement = (
            $callbackData === 'acc_lvl_users' ||
            $callbackData === 'acc_lvl_manage_users' ||
            $callbackData === 'acc_lvl_export_key_resets' ||
            strpos($callbackData, 'acc_lvl_manage_users_page_') === 0 ||
            strpos($callbackData, 'acc_lvl_user_details_') === 0 ||
            strpos($callbackData, 'acc_lvl_set_level_') === 0 ||
            strpos($callbackData, 'acc_lvl_reset_key_') === 0 ||
            strpos($callbackData, 'acc_lvl_change_fn_') === 0 ||
            strpos($callbackData, 'acc_lvl_set_fn_') === 0
        );
        
        $canHandle = $isCategoryAccessLevel || $isAccessLevelManagement;
        
        error_log("AccessLevelHandler canHandle check for '{$callbackData}': " . ($canHandle ? 'true' : 'false'));
        error_log("AccessLevelHandler isCategoryAccessLevel: " . ($isCategoryAccessLevel ? 'true' : 'false'));
        error_log("AccessLevelHandler isAccessLevelManagement: " . ($isAccessLevelManagement ? 'true' : 'false'));
        
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $callbackData = $callbackQuery->getData();
        
        error_log("AccessLevelHandler handling callback for user: {$userId}, category: '{$category}', callbackData: '{$callbackData}'");
        error_log("AccessLevelHandler full callbackQuery: " . print_r($callbackQuery, true));

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);

        // Если категория пустая, используем оригинальные данные callback
        if (empty($category) && !empty($callbackData)) {
            $category = $callbackData;
            error_log("AccessLevelHandler using callbackData as category: '{$category}'");
        }

        // Все пользователи могут просматривать свой уровень доступа
        if ($category === 'УровеньДоступа_Просмотр') {
            error_log("AccessLevelHandler handling 'УровеньДоступа_Просмотр'");
            return $this->showUserAccessLevel($chatId, $userId);
        }

        // Обработка запроса на повышение уровня доступа от гостя
        if ($category === 'УровеньДоступа_ЗапросПовышения') {
            error_log("AccessLevelHandler handling 'УровеньДоступа_ЗапросПовышения'");
            return $this->handleRequestAccessUpgrade($callbackQuery);
        }

        // Показать меню управления уровнями доступа (новый формат)
        if ($category === 'acc_lvl_users') {
            error_log("AccessLevelHandler showing access level management menu for user: {$userId} (new format)");
            return $this->showAccessLevelManagementMenu($chatId, $userId);
        }

        // Показать список пользователей с доступом
        if ($category === 'acc_lvl_manage_users' || strpos($category, 'acc_lvl_manage_users_page_') === 0) {
            error_log("AccessLevelHandler showing users with access for user: {$userId}");
            
            // Извлекаем номер страницы из callback_data
            $page = 1;
            if (strpos($category, 'acc_lvl_manage_users_page_') === 0) {
                $page = (int)str_replace('acc_lvl_manage_users_page_', '', $category);
            }
            
            // Check access level before showing users
            $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
            error_log("AccessLevelHandler user {$userId} access level: {$userAccessLevel}, page: {$page}");
            if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
                error_log("AccessLevelHandler user {$userId} doesn't have admin access for manage users");
                return $this->messageService->sendMessage(
                    $chatId,
                    "❌ У вас недостаточно прав для управления пользователями.\n\n" .
                    "Для использования этой функции вам нужен уровень доступа Администратор или выше.\n" .
                    "Ваш текущий уровень доступа: " . $this->adminService->getAccessLevelName($userAccessLevel) . " (" . $userAccessLevel . ")"
                );
            }
            return $this->showUsersWithAccess($chatId, $userId, $userAccessLevel, $page);
        }

        // Проверяем уровень доступа пользователя for other operations
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        error_log("AccessLevelHandler user {$userId} access level: {$userAccessLevel}");

        // Только администраторы и суперадмины могут управлять уровнями доступа
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            error_log("AccessLevelHandler user {$userId} doesn't have admin access, returning error message");
            return $this->messageService->sendMessage(
                $chatId,
                "❌ У вас недостаточно прав для управления уровнями доступа.\n\n" .
                "Для использования этой функции вам нужен уровень доступа Администратор или выше.\n" .
                "Ваш текущий уровень доступа: " . $this->adminService->getAccessLevelName($userAccessLevel) . " (" . $userAccessLevel . ")"
            );
        }

        // Обработка запросов на повышение уровня доступа
        if (strpos($category, 'УровеньДоступа_Повысить_') === 0) {
            error_log("AccessLevelHandler handling 'УровеньДоступа_Повысить_'");
            $targetUserId = str_replace('УровеньДоступа_Повысить_', '', $category);
            return $this->handleAccessLevelUpgrade($callbackQuery, $targetUserId, $userAccessLevel);
        }

        // Обработка запросов на понижение уровня доступа
        if (strpos($category, 'УровеньДоступа_Понизить_') === 0) {
            error_log("AccessLevelHandler handling 'УровеньДоступа_Понизить_'");
            $targetUserId = str_replace('УровеньДоступа_Понизить_', '', $category);
            return $this->handleAccessLevelDowngrade($callbackQuery, $targetUserId, $userAccessLevel);
        }

        // Показать детали пользователя
        if (strpos($category, 'acc_lvl_user_details_') === 0) {
            $targetUserId = str_replace('acc_lvl_user_details_', '', $category);
            error_log("AccessLevelHandler showing user details for user: {$targetUserId}");
            return $this->showUserDetails($chatId, $userId, (int)$targetUserId, $userAccessLevel);
        }

        // Установить уровень доступа пользователя
        if (strpos($category, 'acc_lvl_set_level_') === 0) {
            // Format: acc_lvl_set_level_{targetUserId}_{level}
            $parts = explode('_', str_replace('acc_lvl_set_level_', '', $category));
            if (count($parts) >= 2) {
                $targetUserId = $parts[0];
                $newLevel = (int)$parts[1];
                error_log("AccessLevelHandler setting access level {$newLevel} for user: {$targetUserId}");
                return $this->setUserAccessLevel($callbackQuery, $userId, (int)$targetUserId, $newLevel, $userAccessLevel);
            }
        }

        // Показать запросы на сброс кодовых слов
        if ($category === 'acc_lvl_export_key_resets') {
            error_log("AccessLevelHandler showing export key reset requests");
            return $this->showExportKeyResetRequests($chatId, $userId);
        }
        
        // Сбросить кодовое слово пользователя
        if (strpos($category, 'acc_lvl_reset_key_') === 0) {
            $targetUserId = (int)str_replace('acc_lvl_reset_key_', '', $category);
            error_log("AccessLevelHandler resetting export key for user: {$targetUserId}");
            return $this->resetUserExportKey($chatId, $userId, $targetUserId);
        }
        
        // Показать меню выбора ФН для пользователя (только для суперадминов)
        if (strpos($category, 'acc_lvl_change_fn_') === 0) {
            $targetUserId = (int)str_replace('acc_lvl_change_fn_', '', $category);
            error_log("AccessLevelHandler showing FN selection for user: {$targetUserId}");
            return $this->showFunctionalDirectionSelection($chatId, $userId, $targetUserId, $userAccessLevel);
        }
        
        // Установить ФН пользователя (только для суперадминов)
        if (strpos($category, 'acc_lvl_set_fn_') === 0) {
            // Format: acc_lvl_set_fn_{targetUserId}_{fnShortCode}
            $parts = explode('_', str_replace('acc_lvl_set_fn_', '', $category));
            if (count($parts) >= 2) {
                $targetUserId = (int)$parts[0];
                $fnShortCode = $parts[1];
                error_log("AccessLevelHandler setting FN '{$fnShortCode}' for user: {$targetUserId}");
                return $this->setUserFunctionalDirection($callbackQuery, $userId, $targetUserId, $fnShortCode, $userAccessLevel);
            }
        }
        
        // Показать меню управления уровнями доступа (старый формат для обратной совместимости)
        if ($category === 'УровеньДоступа_Управление') {
            error_log("AccessLevelHandler showing access level management menu for user: {$userId} (old format)");
            return $this->showAccessLevelManagementMenu($chatId, $userId);
        }

        error_log("AccessLevelHandler unknown category: '{$category}'");
        // Fix encoding for error message
        $category = $this->ensureUtf8Encoding($category);
        return $this->messageService->sendMessage($chatId, 'Неизвестная команда управления уровнями доступа');
    }

    public function getPriority(): int
    {
        return 25; // Same as before, lower priority than AdminHandler (30)
    }

    /**
     * Показать текущий уровень доступа пользователя
     */
    private function showUserAccessLevel(string $chatId, int $userId): ServerResponse
    {
        $accessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($accessLevel);

        // Ensure UTF-8 encoding
        $levelName = $this->ensureUtf8Encoding($levelName);

        $message = "🔐 <b>Ваш уровень доступа</b>\n\n";
        $message .= "Уровень: {$levelName}\n";
        $message .= "ID уровня: {$accessLevel}\n\n";

        // Добавляем информацию о возможностях в зависимости от уровня
        switch ($accessLevel) {
            case DatabaseService::ACCESS_LEVEL_GUEST:
                $message .= "Права:\n";
                $message .= "• Просмотр профиля\n";
                $message .= "• Заполнение профиля\n";
                $message .= "• Запрос повышения до Пользователя\n";
                break;
                
            case DatabaseService::ACCESS_LEVEL_USER:
                $message .= "Права:\n";
                $message .= "• Все возможности Гостя\n";
                $message .= "• Использование всех основных функций бота\n";
                $message .= "• Работа с табелем\n";
                break;
                
            case DatabaseService::ACCESS_LEVEL_ADMIN:
                $message .= "Права:\n";
                $message .= "• Все возможности Пользователя\n";
                $message .= "• Доступ к админке\n";
                $message .= "• Управление проектами\n";
                $message .= "• Подтверждение заявок от Гостей\n";
                $message .= "• Повышение Гостей до Пользователей\n";
                break;
                
            case DatabaseService::ACCESS_LEVEL_SUPER_ADMIN:
                $message .= "Права:\n";
                $message .= "• Все возможности Администратора\n";
                $message .= "• Назначение Администраторов\n";
                $message .= "• Полный доступ ко всем функциям бота\n";
                break;
        }

        // Для гостя добавляем кнопку запроса повышения
        $keyboard = [];
        if ($accessLevel === DatabaseService::ACCESS_LEVEL_GUEST) {
            $keyboard = [
                [
                    ['text' => '📝 Запросить повышение', 'callback_data' => 'category_УровеньДоступа_ЗапросПовышения']
                ]
            ];
        }

        // Ensure UTF-8 encoding for message
        $message = $this->ensureUtf8Encoding($message);
        
        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Обработка запроса на повышение уровня доступа от гостя
     */
    private function handleRequestAccessUpgrade(CallbackQuery $callbackQuery): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $userName = $callbackQuery->getFrom()->getFirstName() . ' ' . $callbackQuery->getFrom()->getLastName();

        // Ensure UTF-8 encoding
        $userName = $this->ensureUtf8Encoding($userName);

        // Проверяем текущий уровень доступа пользователя
        $currentLevel = $this->adminService->getUserAccessLevel($userId);

        // Если пользователь уже имеет уровень выше гостя, то не обрабатываем запрос
        if ($currentLevel > DatabaseService::ACCESS_LEVEL_GUEST) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ Вы уже имеете уровень доступа выше Гостя."
            );
        }

        // Отправляем уведомление всем администраторам
        $admins = $this->adminService->getSuperAdmins(); // Получаем список суперадминов
        
        // Также нужно получить список обычных администраторов из базы данных
        // Для простоты сейчас отправляем только суперадминам
        
        $message = "🔔 <b>Запрос на повышение уровня доступа</b>\n\n";
        $message .= "Пользователь: {$userName}\n";
        $message .= "User ID: {$userId}\n";
        $message .= "Текущий уровень: Гость\n";
        $message .= "Запрашиваемый уровень: Пользователь\n\n";
        $message .= "Для обработки запроса используйте:\n";
        $message .= "/promote {$userId} - для повышения\n";
        $message .= "/reject {$userId} - для отклонения";

        foreach ($admins as $adminId) {
            try {
                $this->messageService->sendMessage($adminId, $message);
            } catch (\Exception $e) {
                // Игнорируем ошибки отправки
            }
        }

        return $this->messageService->sendMessage(
            $chatId,
            "✅ <b>Запрос отправлен</b>\n\n" .
            "Ваш запрос на повышение уровня доступа отправлен администраторам.\n" .
            "Ожидайте решения."
        );
    }

    /**
     * Показать меню управления уровнями доступа
     */
    private function showAccessLevelManagementMenu(string $chatId, int $userId = null): ServerResponse
    {
        error_log("AccessLevelHandler showAccessLevelManagementMenu called");
        error_log("AccessLevelHandler chatId: {$chatId}, userId: " . ($userId ?? 'null'));
        
        // Get user access level if userId is provided
        $userAccessLevel = 0;
        if ($userId !== null) {
            $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
            error_log("AccessLevelHandler userAccessLevel: {$userAccessLevel}");
        }
        
        // Create button text based on user access level
        $manageUsersButtonText = '👥 Управление пользователями';
        if ($userId !== null && $userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            $manageUsersButtonText .= ' (Требуется уровень Администратор)';
        }
        
        $keyboard = [
            [
                ['text' => $manageUsersButtonText, 'callback_data' => 'acc_lvl_manage_users']
            ]
            // Убираем кнопку "Персонал" из этого меню, так как она теперь в меню "Управление проектом"
        ];
        
        // Add export key reset requests button only for Super Admins (level 4)
        if ($userId !== null && $userAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            $keyboard[] = [
                ['text' => '🔐 Запросы на сброс кодовых слов', 'callback_data' => 'acc_lvl_export_key_resets']
            ];
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Вернуться в админку', 'callback_data' => 'category_Админка']
        ];

        $text = "🔐 <b>Управление уровнями доступа</b>\n\n";
        $text .= "Выберите действие:";
        $text = $this->ensureUtf8Encoding($text);
        
        error_log("AccessLevelHandler: Sending access level management menu with " . count($keyboard) . " rows");
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Показать список пользователей с любым уровнем доступа выше гостя
     */
    private function showUsersWithAccess(string $chatId, int $currentUserId, int $currentUserAccessLevel, int $page = 1): ServerResponse
    {
        // Получаем всех пользователей с уровнем доступа выше гостя
        $usersWithAccess = $this->adminService->getAllUsersWithAccess();
        
        $message = "👥 <b>Пользователи с доступом</b>\n\n";
        
        // Создаем клавиатуру с кнопками для каждого пользователя
        $keyboard = [];
        
        if (empty($usersWithAccess)) {
            $message .= "Нет пользователей с уровнем доступа выше гостя.";
            $keyboard[] = [
                ['text' => '⬅️ Вернуться в меню', 'callback_data' => 'acc_lvl_users']
            ];
        } else {
            // ПАГИНАЦИЯ: 20 пользователей на страницу
            $perPage = 20;
            $totalUsers = count($usersWithAccess);
            $totalPages = (int)ceil($totalUsers / $perPage);
            
            // Проверяем корректность номера страницы
            if ($page < 1) $page = 1;
            if ($page > $totalPages) $page = $totalPages;
            
            // Вычисляем офсет для текущей страницы
            $offset = ($page - 1) * $perPage;
            $usersOnPage = array_slice($usersWithAccess, $offset, $perPage);
            
            $message .= "Выберите пользователя для просмотра деталей:\n";
            $message .= "Страница {$page} из {$totalPages} (Всего: {$totalUsers} польз.)\n\n";
            
            // Создаем кнопки по 2 в ряд
            $row = [];
            foreach ($usersOnPage as $index => $user) {
                $userId = $user['user_id'];
                $userData = $user['data'] ?? [];
                $fio = $userData['fio'] ?? 'Без ФИО';
                $accessLevel = $userData['access_level'] ?? 0;
                $levelName = $this->adminService->getAccessLevelName($accessLevel);
                
                // Создаем текст кнопки с ФИО и уровнем доступа
                $buttonText = "{$fio} ({$levelName})";
                
                // Ограничиваем длину текста кнопки (Telegram ограничение)
                if (mb_strlen($buttonText) > 30) {
                    $buttonText = mb_substr($buttonText, 0, 27) . '...';
                }
                
                $row[] = ['text' => $buttonText, 'callback_data' => "acc_lvl_user_details_{$userId}"];
                
                // Каждые 2 кнопки создаем новый ряд
                if (count($row) == 2 || $index == count($usersOnPage) - 1) {
                    $keyboard[] = $row;
                    $row = [];
                }
            }
            
            // Добавляем кнопки навигации по страницам
            if ($totalPages > 1) {
                $navRow = [];
                
                if ($page > 1) {
                    $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => "acc_lvl_manage_users_page_" . ($page - 1)];
                }
                
                $navRow[] = ['text' => "📖 {$page}/{$totalPages}", 'callback_data' => 'acc_lvl_manage_users_page_' . $page];
                
                if ($page < $totalPages) {
                    $navRow[] = ['text' => '➡️ Вперед', 'callback_data' => "acc_lvl_manage_users_page_" . ($page + 1)];
                }
                
                $keyboard[] = $navRow;
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Вернуться в меню', 'callback_data' => 'acc_lvl_users']
            ];
        }
        
        $message = $this->ensureUtf8Encoding($message);
        
        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Показать детали пользователя
     */
    private function showUserDetails(string $chatId, int $currentUserId, int $targetUserId, int $currentUserAccessLevel): ServerResponse
    {
        // Получаем данные целевого пользователя
        $databaseService = new DatabaseService();
        $userData = $databaseService->getUserData($targetUserId);
        
        // Проверяем, является ли целевой пользователь администратором или выше
        $targetUserAccessLevel = $userData['access_level'] ?? 0;
        
        // Администратор не может изменить уровень другого администратора
        if ($currentUserAccessLevel === DatabaseService::ACCESS_LEVEL_ADMIN && 
            $targetUserAccessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Ограничение доступа</b>\n\n" .
                "Администратор не может изменять уровень доступа другим администраторам или суперадминистраторам."
            );
        }
        
        $fio = $userData['fio'] ?? 'Без ФИО';
        $functionalDirection = $userData['functional_direction'] ?? 'Не указано';
        $levelName = $this->adminService->getAccessLevelName($targetUserAccessLevel);
        
        $message = "👤 <b>Профиль пользователя</b>\n\n";
        $message .= "ФИО: {$fio}\n";
        $message .= "Функциональное направление: {$functionalDirection}\n";
        $message .= "Уровень доступа: {$levelName} ({$targetUserAccessLevel})\n";
        $message .= "User ID: {$targetUserId}\n\n";
        
        // Создаем клавиатуру с кнопками выбора уровня доступа
        $keyboard = [];
        
        // Определяем максимальный уровень, который может установить текущий пользователь
        $maxLevel = ($currentUserAccessLevel === DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) ? 3 : 2;
        
        // Создаем кнопки для каждого уровня доступа
        $levelRow = [];
        for ($level = 0; $level <= $maxLevel; $level++) {
            $levelName = $this->adminService->getAccessLevelName($level);
            $levelRow[] = ['text' => $levelName, 'callback_data' => "acc_lvl_set_level_{$targetUserId}_{$level}"];
        }
        
        if (!empty($levelRow)) {
            $keyboard[] = $levelRow;
        }
        
        // Добавляем кнопку изменения ФН только для Суперадминистраторов
        if ($currentUserAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            $keyboard[] = [
                ['text' => '🎯 Изменить ФН', 'callback_data' => "acc_lvl_change_fn_{$targetUserId}"]
            ];
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Вернуться к списку', 'callback_data' => 'acc_lvl_manage_users']
        ];
        
        $message = $this->ensureUtf8Encoding($message);
        
        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Установить уровень доступа пользователя
     */
    private function setUserAccessLevel(CallbackQuery $callbackQuery, int $currentUserId, int $targetUserId, int $newLevel, int $currentUserAccessLevel): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Получаем данные целевого пользователя
        $databaseService = new DatabaseService();
        $userData = $databaseService->getUserData($targetUserId);
        $targetUserAccessLevel = $userData['access_level'] ?? 0;
        
        // Администратор не может изменить уровень другого администратора
        if ($currentUserAccessLevel === DatabaseService::ACCESS_LEVEL_ADMIN && 
            $targetUserAccessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Ограничение доступа</b>\n\n" .
                "Администратор не может изменять уровень доступа другим администраторам или суперадминистраторам."
            );
        }
        
        // Проверяем ограничения для назначения администраторов
        if ($newLevel === DatabaseService::ACCESS_LEVEL_ADMIN && 
            $currentUserAccessLevel !== DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Ограничение доступа</b>\n\n" .
                "Только Суперадминистратор может назначать Администраторов."
            );
        }
        
        // Обновляем уровень доступа
        if ($this->adminService->updateUserAccessLevel($targetUserId, $newLevel)) {
            $levelName = $this->adminService->getAccessLevelName($newLevel);
            
            // Отправляем уведомление целевому пользователю
            try {
                $this->messageService->sendMessage(
                    $targetUserId,
                    "✅ <b>Изменение уровня доступа</b>\n\n" .
                    "Ваш уровень доступа был изменен на: {$levelName}"
                );
            } catch (\Exception $e) {
                // Игнорируем ошибки отправки уведомления
            }
            
            // Показываем обновленные детали пользователя
            return $this->showUserDetails($chatId, $currentUserId, $targetUserId, $currentUserAccessLevel);
        } else {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ Ошибка при изменении уровня доступа. Попробуйте позже."
            );
        }
    }

    /**
     * Обработка запроса на повышение уровня доступа
     */
    private function handleAccessLevelUpgrade(CallbackQuery $callbackQuery, string $targetUserId, int $userAccessLevel): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();

        // Проверяем, что пользователь имеет право повышать уровень
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ У вас недостаточно прав для повышения уровня доступа."
            );
        }

        // Получаем текущий уровень целевого пользователя
        $targetUserLevel = $this->adminService->getUserAccessLevel((int)$targetUserId);

        // Проверяем, можно ли повысить уровень
        if ($targetUserLevel >= $userAccessLevel) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ Вы не можете повышать уровень доступа пользователя с равным или более высоким уровнем."
            );
        }

        // Определяем новый уровень
        $newLevel = $targetUserLevel + 1;

        // Проверяем ограничения для назначения администраторов
        if ($newLevel === DatabaseService::ACCESS_LEVEL_ADMIN && $userAccessLevel !== DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ Только Суперадминистратор может назначать Администраторов."
            );
        }

        // Обновляем уровень доступа
        if ($this->adminService->updateUserAccessLevel((int)$targetUserId, $newLevel)) {
            $levelName = $this->adminService->getAccessLevelName($newLevel);
            
            // Ensure UTF-8 encoding
            $levelName = $this->ensureUtf8Encoding($levelName);
            
            // Отправляем уведомление целевому пользователю
            try {
                $this->messageService->sendMessage(
                    $targetUserId,
                    "✅ <b>Повышение уровня доступа</b>\n\n" .
                    "Ваш уровень доступа был повышен до: {$levelName}"
                );
            } catch (\Exception $e) {
                // Игнорируем ошибки отправки уведомления
            }

            return $this->messageService->sendMessage(
                $chatId,
                "✅ Уровень доступа пользователя успешно повышен до: {$levelName}"
            );
        } else {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ Ошибка при повышении уровня доступа. Попробуйте позже."
            );
        }
    }

    /**
     * Обработка запроса на понижение уровня доступа
     */
    private function handleAccessLevelDowngrade(CallbackQuery $callbackQuery, string $targetUserId, int $userAccessLevel): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();

        // Проверяем, что пользователь имеет право понижать уровень
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ У вас недостаточно прав для понижения уровня доступа."
            );
        }

        // Получаем текущий уровень целевого пользователя
        $targetUserLevel = $this->adminService->getUserAccessLevel((int)$targetUserId);

        // Проверяем, можно ли понизить уровень
        if ($targetUserLevel >= $userAccessLevel) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ Вы не можете понижать уровень доступа пользователя с равным или более высоким уровнем."
            );
        }

        // Определяем новый уровень (минимум Гость)
        $newLevel = max($targetUserLevel - 1, DatabaseService::ACCESS_LEVEL_GUEST);

        // Обновляем уровень доступа
        if ($this->adminService->updateUserAccessLevel((int)$targetUserId, $newLevel)) {
            $levelName = $this->adminService->getAccessLevelName($newLevel);
            
            // Ensure UTF-8 encoding
            $levelName = $this->ensureUtf8Encoding($levelName);
            
            // Отправляем уведомление целевому пользователю
            try {
                $this->messageService->sendMessage(
                    $targetUserId,
                    "⚠️ <b>Понижение уровня доступа</b>\n\n" .
                    "Ваш уровень доступа был понижен до: {$levelName}"
                );
            } catch (\Exception $e) {
                // Игнорируем ошибки отправки уведомления
            }

            return $this->messageService->sendMessage(
                $chatId,
                "✅ Уровень доступа пользователя успешно понижен до: {$levelName}"
            );
        } else {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ Ошибка при понижении уровня доступа. Попробуйте позже."
            );
        }
    }

    /**
     * Show export key reset requests
     */
    private function showExportKeyResetRequests(string $chatId, int $userId): ServerResponse
    {
        error_log("AccessLevelHandler showExportKeyResetRequests called for user: {$userId}");
        
        // Check access level - only Super Admins (level 4) can view reset requests
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ У вас недостаточно прав для просмотра запросов.\n\n" .
                "Требуется уровень Суперадминистратор."
            );
        }
        
        // Get all users with pending reset requests
        $databaseService = new DatabaseService();
        $query = "SELECT user_id, data FROM ozna_data WHERE JSON_EXTRACT(data, '$.export_key_reset_requests.pending') = true";
        $result = $databaseService->getConnection()->query($query);
        
        $resetRequests = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $userData = json_decode($row['data'], true);
                if (isset($userData['export_key_reset_requests']['pending']) && $userData['export_key_reset_requests']['pending']) {
                    $resetRequests[] = [
                        'user_id' => $row['user_id'],
                        'fio' => $userData['fio'] ?? 'Unknown',
                        'requested_at' => $userData['export_key_reset_requests']['requested_at'] ?? 'Unknown'
                    ];
                }
            }
        }
        
        if (empty($resetRequests)) {
            $message = "✅ <b>Запросы на сброс кодовых слов</b>\n\n";
            $message .= "Нет активных запросов.";
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_УровеньДоступа_Управление']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $message, $keyboard);
        }
        
        $message = "🔔 <b>Запросы на сброс кодовых слов</b>\n\n";
        $message .= "Найдено запросов: " . count($resetRequests) . "\n\n";
        
        $keyboard = [];
        foreach ($resetRequests as $request) {
            $message .= "👤 {$request['fio']}\n";
            $message .= "ID: {$request['user_id']}\n";
            $message .= "📅 Запрошено: {$request['requested_at']}\n\n";
            
            $keyboard[] = [
                ['text' => "🔐 Сбросить: {$request['fio']}", 'callback_data' => "acc_lvl_reset_key_{$request['user_id']}"]
            ];
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'category_УровеньДоступа_Управление']
        ];
        
        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }
    
    /**
     * Reset user export key
     */
    private function resetUserExportKey(string $chatId, int $adminUserId, int $targetUserId): ServerResponse
    {
        error_log("AccessLevelHandler resetUserExportKey called for user: {$targetUserId} by admin: {$adminUserId}");
        
        // Check access level - only Super Admins (level 4) can reset keys
        $adminAccessLevel = $this->adminService->getUserAccessLevel($adminUserId);
        if ($adminAccessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ У вас недостаточно прав для сброса кодовых слов.\n\n" .
                "Требуется уровень Суперадминистратор."
            );
        }
        
        // Use ExportKeyHandler to reset the key
        $databaseService = new DatabaseService();
        $exportSecurityService = new ExportSecurityService($databaseService);
        $exportKeyHandler = new ExportKeyHandler(
            $this->messageService,
            $databaseService,
            $exportSecurityService
        );
        
        // Get user data
        $userData = $databaseService->getUserData($targetUserId);
        $targetFio = $userData['fio'] ?? 'Unknown';
        
        // Remove export key (reset to null)
        unset($userData['export_key']);
        unset($userData['export_key_set_at']);
        
        // Clear the reset request
        $userData['export_key_reset_requests'] = ['pending' => false];
        
        $success = $databaseService->updateUserData($targetUserId, $userData);
        
        if ($success) {
            $text = "✅ <b>Кодовое слово сброшено</b>\n\n";
            $text .= "Пользователь: {$targetFio}\n";
            $text .= "User ID: {$targetUserId}\n\n";
            $text .= "Кодовое слово удалено.\n\n";
            $text .= "Пользователь должен установить новое через:\n";
            $text .= "Профиль → Кодовое слово → Установить кодовое слово";
            
            // Notify user
            $userText = "🔐 <b>Кодовое слово сброшено</b>\n\n";
            $userText .= "Ваше кодовое слово было сброшено суперадминистратором.\n\n";
            $userText .= "Для выгрузки данных установите новое кодовое слово через:\n";
            $userText .= "Профиль → Кодовое слово → Установить кодовое слово";
            
            $this->messageService->sendMessage($targetUserId, $userText);
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад к списку запросов', 'callback_data' => 'acc_lvl_export_key_resets']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        } else {
            $text = "❌ Ошибка при сбросе кодового слова.";
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'acc_lvl_export_key_resets']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
    }

    /**
     * Показать меню выбора функционального направления для пользователя
     */
    private function showFunctionalDirectionSelection(string $chatId, int $currentUserId, int $targetUserId, int $currentUserAccessLevel): ServerResponse
    {
        // Только Суперадминистраторы могут менять ФН пользователей
        if ($currentUserAccessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Ограничение доступа</b>\n\n" .
                "Только Суперадминистратор может изменять функциональное направление пользователей."
            );
        }
        
        // Получаем данные целевого пользователя
        $databaseService = new DatabaseService();
        $userData = $databaseService->getUserData($targetUserId);
        
        $fio = $userData['fio'] ?? 'Без ФИО';
        $currentFn = $userData['functional_direction'] ?? 'Не указано';
        
        $message = "🎯 <b>Изменение функционального направления</b>\n\n";
        $message .= "Пользователь: {$fio}\n";
        $message .= "User ID: {$targetUserId}\n";
        $message .= "Текущее ФН: {$currentFn}\n\n";
        $message .= "Выберите новое функциональное направление:";
        
        // Создаем клавиатуру с кнопками выбора ФН
        // Суперадминистратор может установить любое направление, включая Тестировщик
        $keyboard = FunctionalDirectionService::createSelectionKeyboard('acc_lvl_set_fn_', $targetUserId, true);
        
        // Добавляем кнопку "Назад"
        $keyboard[] = [
            ['text' => '⬅️ Назад к профилю', 'callback_data' => "acc_lvl_user_details_{$targetUserId}"]
        ];
        
        $message = $this->ensureUtf8Encoding($message);
        
        return $this->messageService->sendMessage($chatId, $message, $keyboard);
    }

    /**
     * Установить функциональное направление пользователя
     */
    private function setUserFunctionalDirection(CallbackQuery $callbackQuery, int $currentUserId, int $targetUserId, string $fnShortCode, int $currentUserAccessLevel): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Только Суперадминистраторы могут менять ФН пользователей
        if ($currentUserAccessLevel < DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Ограничение доступа</b>\n\n" .
                "Только Суперадминистратор может изменять функциональное направление пользователей."
            );
        }
        
        // Получаем полное название ФН из короткого кода
        $fnName = FunctionalDirectionService::getNameFromShortCode($fnShortCode);
        
        if ($fnName === null) {
            return $this->messageService->sendMessage(
                $chatId,
                "❌ Неизвестное функциональное направление."
            );
        }
        
        // Получаем данные целевого пользователя
        $databaseService = new DatabaseService();
        $userData = $databaseService->getUserData($targetUserId);
        $targetFio = $userData['fio'] ?? 'Без ФИО';
        $oldFn = $userData['functional_direction'] ?? 'Не указано';
        
        // Обновляем ФН пользователя
        $success = $databaseService->setUserFunctionalDirection($targetUserId, $fnName);
        
        if ($success) {
            $text = "✅ <b>Функциональное направление изменено</b>\n\n";
            $text .= "Пользователь: {$targetFio}\n";
            $text .= "User ID: {$targetUserId}\n\n";
            $text .= "Старое ФН: {$oldFn}\n";
            $text .= "Новое ФН: {$fnName}";
            
            // Уведомляем пользователя об изменении ФН
            try {
                $userText = "🎯 <b>Изменение функционального направления</b>\n\n";
                $userText .= "Ваше функциональное направление было изменено суперадминистратором.\n\n";
                $userText .= "Новое ФН: {$fnName}";
                $this->messageService->sendMessage($targetUserId, $userText);
            } catch (\Exception $e) {
                // Игнорируем ошибки отправки уведомления
                error_log("AccessLevelHandler: Error sending FN change notification to user {$targetUserId}: " . $e->getMessage());
            }
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад к профилю', 'callback_data' => "acc_lvl_user_details_{$targetUserId}"]
                ],
                [
                    ['text' => '⬅️ Назад к списку', 'callback_data' => 'acc_lvl_manage_users']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        } else {
            $text = "❌ Ошибка при изменении функционального направления.";
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => "acc_lvl_change_fn_{$targetUserId}"]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
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
    
    /**
     * Fix double-encoded UTF-8 strings
     */
    private function fixDoubleEncodedUtf8($str)
    {
        // Try to detect and fix double-encoded UTF-8
        // This happens when UTF-8 bytes are interpreted as Windows-1251 and then encoded as UTF-8 again
        
        // First, try to convert from Windows-1251 to UTF-8
        $fixed = @mb_convert_encoding($str, 'UTF-8', 'Windows-1251');
        
        // If that doesn't look right, try other approaches
        if ($fixed === false || strlen($fixed) > strlen($str) * 2) {
            // Try to decode as UTF-8 and then re-encode
            $fixed = @mb_convert_encoding($str, 'UTF-8', 'UTF-8');
        }
        
        return $fixed ?: $str;
    }
    
    /**
     * Fix encoding issues in strings
     */
    private function fixEncoding($str)
    {
        // Ensure UTF-8 encoding
        $str = mb_convert_encoding($str, 'UTF-8', 'auto');
        return $str;
    }
    
    /**
     * Fix known Cyrillic pattern corruptions
     */
    private function fixKnownCyrillicPatterns($str)
    {
        // Replace the known corrupted patterns
        $replacements = [
            'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
            'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
            'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
            'Р”Р°С‚Р°' => 'Дата',
            'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
            'Р’С‡РµСЂР°' => 'Вчера',
            'Р�СЂРѕС„РёР»СЊ' => 'Профиль',
            'Р�СЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
            'Р¤РРћ' => 'ФИО',
            'Р¤Рќ' => 'ФН',
            'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
            'Р�СѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
            'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
            'РўР°Р±РµР»СЊ' => 'Табель',
            'Р—Р°РїСЂРѕСЃР�РѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
            'РџРѕРІС‹СЃРёС�СЊ' => 'Повысить',
            'РџРѕРЅРёР·РёС�СЊ' => 'Понизить'
        ];
        
        foreach ($replacements as $corrupted => $fixed) {
            $str = str_replace($corrupted, $fixed, $str);
        }
        
        return $str;
    }

}