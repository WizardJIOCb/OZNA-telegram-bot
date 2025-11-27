<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
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
        $callbackData = $this->ensureUtf8Encoding($callbackData);
        
        // Check for category-based access level callbacks
        $isCategoryAccessLevel = strpos($callbackData, 'category_УровеньДоступа') === 0;
        
        // Check for new access level management callbacks
        $isAccessLevelManagement = (
            $callbackData === 'acc_lvl_users' ||
            $callbackData === 'acc_lvl_manage_users' ||
            strpos($callbackData, 'acc_lvl_user_details_') === 0 ||
            strpos($callbackData, 'acc_lvl_set_level_') === 0
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
        
        error_log("AccessLevelHandler handling callback for user: {$userId}, category: '{$category}'");
        error_log("AccessLevelHandler full callbackQuery: " . print_r($callbackQuery, true));

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);

        // Проверяем уровень доступа пользователя
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        error_log("AccessLevelHandler user {$userId} access level: {$userAccessLevel}");

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

        // Только администраторы и суперадмины могут управлять уровнями доступа
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            error_log("AccessLevelHandler user {$userId} doesn't have admin access, returning error message");
            return $this->messageService->sendMessage(
                $chatId,
                "❌ У вас недостаточно прав для управления уровнями доступа."
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

        // Показать меню управления уровнями доступа (новый формат)
        if ($category === 'acc_lvl_users') {
            error_log("AccessLevelHandler showing access level management menu for user: {$userId} (new format)");
            return $this->showAccessLevelManagementMenu($chatId);
        }

        // Показать список пользователей с доступом
        if ($category === 'acc_lvl_manage_users') {
            error_log("AccessLevelHandler showing users with access for user: {$userId}");
            return $this->showUsersWithAccess($chatId, $userId, $userAccessLevel);
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

        // Показать меню управления уровнями доступа (старый формат для обратной совместимости)
        if ($category === 'УровеньДоступа_Управление') {
            error_log("AccessLevelHandler showing access level management menu for user: {$userId} (old format)");
            return $this->showAccessLevelManagementMenu($chatId);
        }

        error_log("AccessLevelHandler unknown category: '{$category}'");
        // Fix encoding for error message
        $category = $this->ensureUtf8Encoding($category);
        return $this->messageService->sendMessage($chatId, 'Неизвестная команда управления уровнями доступа');
    }

    public function getPriority(): int
    {
        return 25; // Lower priority than AdminHandler (30)
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
    private function showAccessLevelManagementMenu(string $chatId): ServerResponse
    {
        error_log("AccessLevelHandler showAccessLevelManagementMenu called");
        
        $keyboard = [
            [
                ['text' => '👥 Управление пользователями', 'callback_data' => 'acc_lvl_manage_users']
            ],
            [
                ['text' => '⬅️ Вернуться в админку', 'callback_data' => 'category_Админка']
            ]
        ];

        error_log("AccessLevelHandler sending access level management menu");
        $message = "🔐 <b>Управление уровнями доступа</b>\n\nВыберите действие:";
        $message = $this->ensureUtf8Encoding($message);
        
        return $this->messageService->sendMessage(
            $chatId,
            $message,
            $keyboard
        );
    }

    /**
     * Показать список пользователей с любым уровнем доступа выше гостя
     */
    private function showUsersWithAccess(string $chatId, int $currentUserId, int $currentUserAccessLevel): ServerResponse
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
            $message .= "Выберите пользователя для просмотра деталей:\n\n";
            
            // Создаем кнопки по 2 в ряд
            $row = [];
            foreach ($usersWithAccess as $index => $user) {
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
                if (count($row) == 2 || $index == count($usersWithAccess) - 1) {
                    $keyboard[] = $row;
                    $row = [];
                }
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