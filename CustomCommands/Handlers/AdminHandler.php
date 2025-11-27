<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for admin related callback queries
 * This is the MAIN AdminHandler file being used
 */
class AdminHandler implements CallbackHandlerInterface
{
    private AdminService $adminService;
    private MessageService $messageService;

    public function __construct(AdminService $adminService, MessageService $messageService)
    {
        error_log("AdminHandler MAIN constructor called");
        $this->adminService = $adminService;
        $this->messageService = $messageService;
    }

    public function canHandle(string $callbackData): bool
    {
        // Add detailed logging
        error_log("AdminHandler: canHandle called with callbackData: '{$callbackData}'");
        
        // Check specifically for electrical equipment callback data
        if (strpos($callbackData, 'Электрооборудование') !== false) {
            error_log("AdminHandler: canHandle - detected электрооборудование in callback data, returning false");
            // AdminHandler should not handle electrical equipment callbacks
            return false;
        }
        
        $startsWithCategoryAdmin = str_starts_with($callbackData, 'category_Админка');
        $startsWithCategoryAdm = str_starts_with($callbackData, 'category_Адм_');
        $equalsCategoryMain = $callbackData === 'category_Главное';
        $startsWithAdmReq = str_starts_with($callbackData, 'adm_req_');
        
        // Add support for adm_req_* callbacks which don't have the category_ prefix
        $isAdmReqCallback = $startsWithAdmReq;
        
        // Exclude acc_lvl_* callbacks as they should be handled by AccessLevelHandler
        $isAccessLevelCallback = str_starts_with($callbackData, 'acc_lvl_');
        
        // NOTE: We exclude 'category_Главное' from this handler to let MainMenuHandler handle it
        // This ensures consistent main menu handling for all users regardless of access level
        $canHandle = !$isAccessLevelCallback && !$equalsCategoryMain && ($startsWithCategoryAdmin || $startsWithCategoryAdm || $startsWithAdmReq || $isAdmReqCallback);
        
        // Special case: Allow 'category_Админка_ЗапросДоступ' to be handled by AdminHandler
        // This ensures that access requests from profile are handled correctly
        if ($callbackData === 'category_Админка_ЗапросДоступ') {
            $canHandle = true; // AdminHandler will handle this
            error_log("AdminHandler: explicitly handling 'category_Админка_ЗапросДоступ'");
        }
        
        error_log("AdminHandler: canHandle result for '{$callbackData}': " . ($canHandle ? 'true' : 'false'));
        error_log("AdminHandler: startsWithCategoryAdmin: " . ($startsWithCategoryAdmin ? 'true' : 'false'));
        error_log("AdminHandler: startsWithCategoryAdm: " . ($startsWithCategoryAdm ? 'true' : 'false'));
        error_log("AdminHandler: equalsCategoryMain: " . ($equalsCategoryMain ? 'true' : 'false'));
        error_log("AdminHandler: startsWithAdmReq: " . ($startsWithAdmReq ? 'true' : 'false'));
        error_log("AdminHandler: isAdmReqCallback: " . ($isAdmReqCallback ? 'true' : 'false'));
        error_log("AdminHandler: isAccessLevelCallback: " . ($isAccessLevelCallback ? 'true' : 'false'));
        
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $callbackData = $callbackQuery->getData();
        
        error_log("AdminHandler: handle called with category: '{$category}' for user: {$userId}");
        error_log("AdminHandler: handle called with original callback data: '{$callbackData}'");
        
        // Special case: Allow access request even for non-admins
        // Moved this condition to the top to ensure it's handled before access level check
        if ($category === 'Админка_ЗапросДоступ') {
            error_log("AdminHandler: handling access request for user: {$userId}");
            return $this->handleAccessRequest($callbackQuery);
        }

        // Special handling for adm_req_* callbacks (no category_ prefix)
        // These are access request management callbacks that should be available to admins
        if (str_starts_with($callbackData, 'adm_req_')) {
            error_log("AdminHandler: handling adm_req callback from raw data: '{$callbackData}' for user: {$userId}");
            // Проверяем уровень доступа пользователя before handling adm_req callbacks
            $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
            error_log("AdminHandler: user access level for adm_req: {$userAccessLevel} for user: {$userId}");
            
            // Проверяем, имеет ли пользователь доступ к админке (уровень Администратор или выше)
            if ($userAccessLevel < 3) { // ACCESS_LEVEL_ADMIN
                error_log("AdminHandler: user {$userId} does not have admin access for adm_req, showing access denied");
                $text = '❌ У вас нет доступа к управлению заявками на доступ';
                $text = $this->ensureUtf8Encoding($text);
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // For adm_req callbacks, we need to pass the original callback data, not the category
            // because the category extraction doesn't work for adm_req callbacks (no category_ prefix)
            return $this->handleAccessRequests($callbackQuery, $callbackData, $conversation);
        }

        // Special case: "Главное" category should be handled by MainMenuHandler
        // This ensures consistent main menu handling for all users regardless of access level
        if ($category === 'Главное') {
            error_log("AdminHandler: redirecting to main menu for user: {$userId}");
            return $this->showMainMenu($chatId, $userId);
        }

        // Проверяем уровень доступа пользователя
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        error_log("AdminHandler: user access level: {$userAccessLevel} for user: {$userId}");
        
        // UPDATED: Супервайзеры (уровень 2) теперь имеют доступ к админке,
        // но с ограниченным функционалом (только модерация проектов)
        // Только пользователи с уровнем < 2 (Гость и Пользователь) получают меню запроса доступа
        if ($userAccessLevel < 2) { // ACCESS_LEVEL_SUPERVISOR
            error_log("AdminHandler: user {$userId} does not have supervisor access, showing access request menu");
            // Показываем меню с кнопкой "Запросить доступ"
            return $this->showAccessRequestMenu($chatId);
        }

        // Роутинг для администраторов и суперадминов
        error_log("AdminHandler: routing admin callback for user: {$userId}");
        return $this->routeAdminCallback($callbackQuery, $category, $conversation);
    }

    public function getPriority(): int
    {
        return 25; // Same priority as AccessLevelHandler and SupervisorHandler
    }

    private function showAccessRequestMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->adminService->createAccessRequestKeyboard();
        $text = "🔐 <b>Админка</b> Для получения доступа к административным функциям подайте заявку.";
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function routeAdminCallback(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("AdminHandler: routeAdminCallback called with category: '{$category}' for user: {$userId}");

        // Возврат в главное меню
        if ($category === 'Главное') {
            error_log("AdminHandler: routing to main menu for user: {$userId}");
            return $this->showMainMenu($chatId, $userId);
        }

        // Главное меню админки
        if ($category === 'Админка') {
            error_log("AdminHandler: showing admin main menu for user: {$userId}");
            return $this->showAdminMainMenu($chatId, $userId);
        }
        
        // Удаление конкретного проекта (сокращенный формат)
        if (str_starts_with($category, 'Адм_УдПр_')) {
            error_log("AdminHandler: handling delete specific project for user: {$userId}");
            return $this->handleDeleteSpecificProject($callbackQuery, $category, $conversation);
        }
    
        // Удаление конкретного проекта (старый формат для совместимости)
        if (str_starts_with($category, 'Админка_УдалитьПроект_')) {
            error_log("AdminHandler: handling delete specific project (old format) for user: {$userId}");
            return $this->handleDeleteSpecificProject($callbackQuery, $category, $conversation);
        }

        // Подтверждение удаления проекта (сокращенный callback)
        if (str_starts_with($category, 'Админка_ПодтвУдал_')) {
            error_log("AdminHandler: handling confirm delete project for user: {$userId}");
            return $this->handleConfirmDeleteProject($callbackQuery, $category, $conversation);
        }

        // Управление проектами
        if (str_starts_with($category, 'Админка_Проекты')) {
            error_log("AdminHandler: handling project management for user: {$userId}");
            return $this->handleProjectManagement($callbackQuery, $category, $conversation);
        }

        // Заявки на доступ
        if ($category === 'Админка_Заявки') {
            error_log("AdminHandler: handling access requests for user: {$userId}");
            return $this->handleAccessRequests($callbackQuery, $category, $conversation);
        }
        
        // Проверяем префикс для заявок на доступ
        if (str_starts_with($category, 'Админка_Заявки')) {
            error_log("AdminHandler: handling access requests with prefix for user: {$userId}");
            return $this->handleAccessRequests($callbackQuery, $category, $conversation);
        }
        
        // Модерация проектов - redirect to ProjectModerationHandler
        if ($category === 'Админка_МодерацияПроектов' || str_starts_with($category, 'Админка_МодерацияПроектов')) {
            error_log("AdminHandler: redirecting to ProjectModerationHandler for user: {$userId}");
            // Create ProjectModerationHandler and delegate
            $databaseService = $this->adminService->getDatabaseService();
            $projectModerationHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectModerationHandler(
                $databaseService,
                $this->messageService,
                $this->adminService
            );
            // Change the callback data to use the proj_mod_ prefix that ProjectModerationHandler expects
            $modCallbackData = 'category_Админка_МодерацияПроектов';
            error_log("AdminHandler: forwarding to ProjectModerationHandler with callback: {$modCallbackData}");
            return $projectModerationHandler->handle($callbackQuery, $modCallbackData, $conversation);
        }
        
        // Запрос доступа (для обычных пользователей)
        if ($category === 'Админка_ЗапросДоступ') {
            error_log("AdminHandler: handling access request for user: {$userId}");
            return $this->handleAccessRequest($callbackQuery);
        }
        
        // AI-помощник (только для администраторов и выше)
        if ($category === 'Админка_AIПомощник' || str_starts_with($category, 'Админка_AI')) {
            error_log("AdminHandler: redirecting to Grok AI for user: {$userId}");
            // Redirect to GrokHandler
            $grokHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\GrokHandler(
                $this->adminService->getDatabaseService(),
                $this->messageService,
                $this->adminService
            );
            return $grokHandler->handle($callbackQuery, 'Grok_Main', $conversation);
        }

        $text = 'Неизвестная команда админки';
        error_log("AdminHandler: unknown admin command: '{$category}' for user: {$userId}");
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text);
    }

    private function showAdminMainMenu(string $chatId, int $userId): ServerResponse
    {
        // Определяем уровень доступа пользователя
        $accessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($accessLevel);
        
        $keyboard = [];
        
        // Супервайзеры (уровень 2) - нет специального меню, используют "Управление проектом"
        if ($accessLevel === 2) { // ACCESS_LEVEL_SUPERVISOR
            // Направляем в "Управление проектом"
            return $this->messageService->sendMessage(
                $chatId,
                "ℹ️ <b>Панель супервайзера</b>\n\nВаш уровень доступа: {$levelName}\n\nИспользуйте меню <b>🛠️ Управление проектом</b> для доступа к функциям.",
                [
                    [
                        ['text' => '🛠️ Перейти в Управление проектом', 'callback_data' => 'category_УправлениеПроектом']
                    ]
                ]
            );
        }
        // Администраторы и Суперадмины видят полное меню
        else if ($accessLevel >= 3) { // ACCESS_LEVEL_ADMIN or higher
            $keyboard[] = [
                ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
            ];
            
            // Добавляем кнопку заявок на доступ с счетчиком
            try {
                $databaseService = $this->adminService->getDatabaseService();
                $accessRequestsCount = count($databaseService->getPendingAccessRequests());
                
                $accessButtonText = '👥 Заявки на доступ';
                if ($accessRequestsCount > 0) {
                    $accessButtonText .= " ({$accessRequestsCount})";
                }
                
                $keyboard[] = [
                    ['text' => $accessButtonText, 'callback_data' => 'category_Админка_Заявки']
                ];
            } catch (\Exception $e) {
                error_log("AdminHandler::showAdminMainMenu - Error adding access requests button: " . $e->getMessage());
                // Fallback без счетчика если ошибка
                $keyboard[] = [
                    ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
                ];
            }
            
            // Управление уровнями доступа - для администраторов и суперадминов
            $keyboard[] = [
                ['text' => '🔐 Управление уровнями доступа', 'callback_data' => 'category_УровеньДоступа_Управление']
            ];
            
            // AI-помощник - ВРЕМЕННО ОТКЛЮЧЕН (Grok API не доступен из России)
            // $keyboard[] = [
            //     ['text' => '🤖 AI-помощник', 'callback_data' => 'category_Админка_AIПомощник']
            // ];
            
            // NOTE: The following buttons have been removed as per user request
            // - "Добавить администратора" 
            // - "Очистить список администраторов"
            // - "🤖 AI-помощник" (Grok API не работает из России из-за санкций)

            $text = "⚙️ <b>Панель администратора</b> " .
                "Ваш уровень доступа: {$levelName}\n\n" .
                "Выберите действие:";
        }
        
        $text = $this->ensureUtf8Encoding($text);
        
        error_log("AdminHandler::showAdminMainMenu - Sending message with keyboard count: " . count($keyboard));
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Показать главное меню
     */
    private function showMainMenu(string $chatId, int $userId): ServerResponse
    {
        // Get user access level
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        // For guests (level 0), show limited menu
        if ($userAccessLevel == 0) {
            $keyboard = [
                [
                    ['text' => '👤 Профиль', 'callback_data' => 'category_Профиль']
                ],
                [
                    ['text' => '❓ Помощь', 'callback_data' => 'category_Помощь']
                ]
            ];
            
            $text = "<b>🏠 Главное меню</b> (для вызова этого меню в любой момент отправьте /start) ⚠️ У вас уровень доступа \"Гость\". Обратитесь к администратору для получения расширенного доступа.";
            
            // Also update the reply keyboard (persistent menu) for guests
            $replyKeyboard = $this->messageService->createGuestMainMenuReplyKeyboard();
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessageWithReplyKeyboard($chatId, $text, $replyKeyboard);
        } else {
            // For other users, show full menu
            $keyboard = [
                [
                    ['text' => '📊 Табель', 'callback_data' => 'category_Табель']
                ],
                [
                    ['text' => '👤 Профиль', 'callback_data' => 'category_Профиль']
                ],
                [
                    ['text' => '⚙️ Админка', 'callback_data' => 'category_Админка']
                ],
                [
                    ['text' => '❓ Помощь', 'callback_data' => 'category_Помощь']
                ]
            ];
            
            $text = "<b>🏠 Главное меню</b> (для вызова этого меню в любой момент отправьте /start)";
            
            // Also update the reply keyboard (persistent menu) for registered users
            $replyKeyboard = $this->messageService->createMainMenuReplyKeyboard($userAccessLevel);
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessageWithReplyKeyboard($chatId, $text, $replyKeyboard);
        }
    }

    private function handleProjectManagement(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Проверка: управление проектами доступно только Администраторам и Суперадминам
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        if ($userAccessLevel < 3) { // ACCESS_LEVEL_ADMIN
            $text = '❌ <b>Доступ ограничен</b>\n\nУправление проектами доступно только Администраторам и Суперадминам.';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }

        if ($category === 'Админка_Проекты') {
            return $this->showProjectManagementMenu($chatId);
        }

        if ($category === 'Админка_Проекты_Список') {
            return $this->showProjectList($chatId);
        }

        if ($category === 'Админка_Проекты_Добавить') {
            return $this->startAddProject($callbackQuery, $conversation);
        }

        if ($category === 'Админка_Проекты_Удалить') {
            return $this->showDeleteProjectMenu($chatId);
        }

        $text = 'Функция в разработке';
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text);
    }

    private function showProjectManagementMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->adminService->createProjectManagementKeyboard();
        $text = "📋 <b>Управление проектами</b> Выберите действие:";
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function showProjectList(string $chatId): ServerResponse
    {
        // Use ProjectSelector to get all projects including pending ones
        $databaseService = $this->adminService->getDatabaseService();
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(
            $databaseService
        );
        $projects = $projectSelector->getAllProjectsIncludingPending();
        $text = "📋 <b>Список проектов:</b>\n\n";
        
        if (empty($projects)) {
            $text .= "Проекты не найдены.";
        } else {
            $text .= "<i>Показаны все проекты (включая ожидающие модерацию)</i>\n\n";
            foreach ($projects as $project) {
                $text .= "• {$project}\n";
            }
        }

        $keyboard = $this->adminService->createBackToProjectsKeyboard();
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function startAddProject(CallbackQuery $callbackQuery, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Устанавливаем состояние ожидания ввода названия проекта
        $conversation->notes['admin_action'] = 'add_project';
        $conversation->update();

        $text = "✏️ <b>Добавление проекта</b> Введите код проекта (например: ОИ834, БКНС115):";
        $text = $this->ensureUtf8Encoding($text);
        
        return $this->messageService->sendMessage($chatId, $text);
    }

    private function showDeleteProjectMenu(string $chatId): ServerResponse
    {
        // Use ProjectSelector to get all projects including pending ones
        $databaseService = $this->adminService->getDatabaseService();
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(
            $databaseService
        );
        $projects = $projectSelector->getAllProjectsIncludingPending();
        
        if (empty($projects)) {
            $keyboard = $this->adminService->createBackToProjectsKeyboard();
            $text = '❌ Нет проектов для удаления';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }

        $keyboard = $this->adminService->createDeleteProjectKeyboard($projects);
        
        $text = "🗑️ <b>Удаление проекта</b> Выберите проект для удаления:";
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function handleAccessRequests(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        try {
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
            $userId = $callbackQuery->getFrom()->getId();
            
            error_log("AdminHandler: handleAccessRequests called with category: '{$category}' for user: {$userId}");
            error_log("AdminHandler: handleAccessRequests callback data: '" . $callbackQuery->getData() . "'");
            
            // Проверка: заявки на доступ доступны только Администраторам и Суперадминам
            $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
            if ($userAccessLevel < 3) { // ACCESS_LEVEL_ADMIN
                $text = '❌ <b>Доступ ограничен</b>\n\nУправление заявками на доступ доступно только Администраторам и Суперадминам.';
                $text = $this->ensureUtf8Encoding($text);
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // Проверяем подкатегорию
            if ($category === 'Админка_Заявки') {
                error_log("AdminHandler: showing access requests list for user: {$userId}");
                // Показываем список заявок
                return $this->showAccessRequestsList($chatId);
            }

            
            // Проверяем, если это просмотр конкретной заявки (новый формат)
            if ($this->stringStartsWith($category, 'adm_req_view_')) {
                $requestUserId = str_replace('adm_req_view_', '', $category);
                error_log("AdminHandler: showing access request details for request user: {$requestUserId} for user: {$userId}");
                return $this->showAccessRequestDetails($callbackQuery, $requestUserId, $chatId);
            }
            
            // Проверяем, если это одобрение заявки (новый формат)
            if ($this->stringStartsWith($category, 'adm_req_approve_')) {
                $requestUserId = str_replace('adm_req_approve_', '', $category);
                error_log("AdminHandler: approving access request for request user: {$requestUserId} by user: {$userId}");
                return $this->approveAccessRequest($callbackQuery, $requestUserId, $chatId, $userId);
            }
            
            // Проверяем, если это отклонение заявки (новый формат)
            if ($this->stringStartsWith($category, 'adm_req_reject_')) {
                $requestUserId = str_replace('adm_req_reject_', '', $category);
                error_log("AdminHandler: rejecting access request for request user: {$requestUserId} by user: {$userId}");
                return $this->rejectAccessRequest($callbackQuery, $requestUserId, $chatId, $userId);
            }

            
            // Проверяем, если это просмотр конкретной заявки (старый формат)
            if ($this->stringStartsWith($category, 'Админка_Заявки_Просмотр_')) {
                $requestUserId = str_replace('Админка_Заявки_Просмотр_', '', $category);
                error_log("AdminHandler: showing access request details (old format) for request user: {$requestUserId} for user: {$userId}");
                return $this->showAccessRequestDetails($callbackQuery, $requestUserId, $chatId);
            }
            
            // Проверяем, если это одобрение заявки (старый формат)
            if ($this->stringStartsWith($category, 'Админка_Заявки_Одобрить_')) {
                $requestUserId = str_replace('Админка_Заявки_Одобрить_', '', $category);
                error_log("AdminHandler: approving access request (old format) for request user: {$requestUserId} by user: {$userId}");
                return $this->approveAccessRequest($callbackQuery, $requestUserId, $chatId, $userId);
            }
            
            // Проверяем, если это отклонение заявки (старый формат)
            if ($this->stringStartsWith($category, 'Админка_Заявки_Отклонить_')) {
                $requestUserId = str_replace('Админка_Заявки_Отклонить_', '', $category);
                error_log("AdminHandler: rejecting access request (old format) for request user: {$requestUserId} by user: {$userId}");
                return $this->rejectAccessRequest($callbackQuery, $requestUserId, $chatId, $userId);
            }
            
            $text = 'Неизвестная команда управления заявками';
            error_log("AdminHandler: unknown access requests command: '{$category}' for user: {$userId}");
            error_log("AdminHandler: full callback data was: '" . $callbackQuery->getData() . "'");
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        } catch (\Exception $e) {
            error_log("AdminHandler: Error handling access requests: " . $e->getMessage());
            $chatId = $callbackQuery->getMessage()->getChat()->getId();
            $text = "❌ <b>Ошибка при обработке заявок на доступ</b> Попробуйте еще раз позже.";
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
    }

    /**
     * Показать список заявок на доступ
     */
    private function showAccessRequestsList(string $chatId): ServerResponse
    {
        try {
            $requests = $this->adminService->getAllAccessRequests();
            
            // Создаем текст сообщения
            $text = "👥 <b>Заявки на доступ к боту</b>\n\n";
            $text .= "Всего заявок: " . count($requests) . "\n\n";
            
            // Инициализируем клавиатуру
            $keyboard = [];
            
            if (empty($requests)) {
                $text .= "Нет новых заявок на доступ.";
                $keyboard = [
                    [
                        ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                    ]
                ];
            } else {
                $text .= "Выберите заявку для просмотра:\n\n";
                
                // Создаем клавиатуру с кнопками для каждой заявки
                $keyboard = [];
                
                // Используем массив для отслеживания уже добавленных пользователей
                $addedUsers = [];
                
                foreach ($requests as $index => $request) {
                    $userId = $request['user_id'];
                    $userName = $request['user_name'] ?? 'Неизвестный пользователь';
                    
                    // Пропускаем дубликаты - добавляем только первого пользователя с каждым ID
                    if (in_array($userId, $addedUsers)) {
                        continue;
                    }
                    
                    $addedUsers[] = $userId;
                    
                    // ВАЖНО: Используем сокращенный callback_data чтобы не превысить лимит Telegram (64 байта)
                    // Формат: adm_req_view_{userId}
                    $shortCallbackData = "adm_req_view_{$userId}";
                    $keyboard[] = [
                        ['text' => $userName, 'callback_data' => $shortCallbackData]
                    ];
                    
                    // Ограничиваем количество заявок в списке (для предотвращения превышения лимита Telegram)
                    if (count($addedUsers) >= 20) {
                        $text .= "\n⚠️ Показаны только первые 20 заявок. Остальные скрыты.";
                        break;
                    }
                }
                
                // Добавляем кнопку "Назад" - правильно структурированную
                $keyboard[] = [
                    ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                ];
            }
            
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        } catch (\Exception $e) {
            error_log("AdminHandler: Error showing access requests list: " . $e->getMessage());
            $text = "❌ <b>Ошибка при получении списка заявок</b> Попробуйте еще раз позже.";
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
    }

    /**
     * Показать детали заявки на доступ
     */
    private function showAccessRequestDetails(CallbackQuery $callbackQuery, string $requestUserId, string $chatId): ServerResponse
    {
        // Get the access request from the database
        $request = $this->adminService->getAccessRequestByUserId((int)$requestUserId);
        
        if (!$request) {
            $text = '❌ Заявка не найдена';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        $userName = $request['user_name'] ?? 'Неизвестный пользователь';
        $userTelegramName = $request['user_telegram_name'] ?? '';
        $userFio = $request['user_fio'] ?? '';
        $requestDate = date('d.m.Y H:i', $request['timestamp'] ?? time());
        $currentLevel = 'Гость'; // All requests are from guests
        $requestedLevel = 'Пользователь'; // All requests are for user level
        
        // Get user's functional direction from database
        $userData = $this->adminService->getDatabaseService()->getUserData((int)$requestUserId);
        $userFunctionalDirection = $userData['functional_direction'] ?? 'Не указано';
        
        $text = "👤 <b>Заявка на доступ</b>\n\n";
        $text .= "Пользователь: {$userName}\n";
        if (!empty($userTelegramName) && $userTelegramName !== $userName) {
            $text .= "Telegram: {$userTelegramName}\n";
        }
        $text .= "ID: {$requestUserId}\n";
        $text .= "Дата подачи: {$requestDate}\n";
        $text .= "Текущий уровень: {$currentLevel}\n";
        $text .= "Запрашиваемый уровень: {$requestedLevel}\n";
        if (!empty($userFio)) {
            //$text .= "ФИО: {$userFio}\n";
        }
        $text .= "Функциональное направление: {$userFunctionalDirection}\n\n";
        $text .= "Выберите действие:";
        
        // Создаем клавиатуру с кнопками одобрения и отклонения
        // ВАЖНО: Используем сокращенный callback_data чтобы не превысить лимит Telegram (64 байта)
        $keyboard = [
            [
                ['text' => '✅ Одобрить', 'callback_data' => "adm_req_approve_{$requestUserId}"],
                ['text' => '❌ Отклонить', 'callback_data' => "adm_req_reject_{$requestUserId}"]
            ],
            [
                ['text' => '⬅️ Назад к заявкам', 'callback_data' => 'category_Админка_Заявки']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Одобрить заявку на доступ
     */
    private function approveAccessRequest(CallbackQuery $callbackQuery, string $requestUserId, string $chatId, int $adminUserId): ServerResponse
    {
        // Get the access request to obtain the required information
        $request = $this->adminService->getAccessRequestByUserId((int)$requestUserId);
        
        if (!$request) {
            $text = "❌ <b>Ошибка при одобрении заявки</b> Заявка не найдена.";
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Extract required information from the request
        $userName = $request['user_name'] ?? 'Неизвестный пользователь';
        $userChatId = $request['chat_id'] ?? $requestUserId; // Fallback to userId if chat_id not available
        
        // Call the service method with all required parameters
        $success = $this->adminService->approveAccessRequest((int)$requestUserId, $userName, $userChatId, $adminUserId);
        
        if ($success) {
            $text = "✅ <b>Заявка одобрена</b> Пользователь получил запрашиваемый уровень доступа.";
        } else {
            $text = "❌ <b>Ошибка при одобрении заявки</b> Попробуйте еще раз.";
        }
        
        // После одобрения возвращаемся к списку заявок
        $keyboard = [
            [
                ['text' => '⬅️ Назад к заявкам', 'callback_data' => 'category_Админка_Заявки']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Отклонить заявку на доступ
     */
    private function rejectAccessRequest(CallbackQuery $callbackQuery, string $requestUserId, string $chatId, int $adminUserId): ServerResponse
    {
        // Get the access request to obtain the required information
        $request = $this->adminService->getAccessRequestByUserId((int)$requestUserId);
        
        if (!$request) {
            $text = "❌ <b>Ошибка при отклонении заявки</b> Заявка не найдена.";
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Extract required information from the request
        $userName = $request['user_name'] ?? 'Неизвестный пользователь';
        $userChatId = $request['chat_id'] ?? $requestUserId; // Fallback to userId if chat_id not available
        
        // Call the service method with all required parameters
        $success = $this->adminService->rejectAccessRequest((int)$requestUserId, $userName, $userChatId, $adminUserId);
        
        if ($success) {
            $text = "❌ <b>Заявка отклонена</b> Пользователь уведомлен об отказе.";
        } else {
            $text = "❌ <b>Ошибка при отклонении заявки</b> Попробуйте еще раз.";
        }
        
        // После отклонения возвращаемся к списку заявок
        $keyboard = [
            [
                ['text' => '⬅️ Назад к заявкам', 'callback_data' => 'category_Админка_Заявки']
            ]
        ];
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Handle access request from guest user
     */
    private function handleAccessRequest(CallbackQuery $callbackQuery): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $userName = $callbackQuery->getFrom()->getFirstName() . ' ' . $callbackQuery->getFrom()->getLastName();
        
        // Get user access level
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($userAccessLevel);
        
        // Check if user already has a pending access request
        if ($this->adminService->hasPendingAccessRequest($userId)) {
            $text = "📝 <b>Запрос доступа</b>";
            $text .= "У вас уже есть активный запрос на получение доступа.\n";
            $text .= "Пожалуйста, дождитесь рассмотрения вашего запроса администратором.";
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // For guests (level 0), we'll request User level (1)
        if ($userAccessLevel == 0) {
            // Send access request
            $success = $this->adminService->sendAccessRequest($userId, $userName, $chatId);
            
            if ($success) {
                $text = "📝 <b>Запрос доступа отправлен</b>";
                $text .= "Ваш текущий уровень: {$levelName}\n";
                $text .= "Запрашиваемый уровень: Пользователь\n\n";
                $text .= "Администраторы получили уведомление о вашем запросе.\n";
                $text .= "Ожидайте подтверждения.";
            } else {
                $text = "❌ <b>Ошибка при отправке запроса</b> Попробуйте еще раз позже.";
            }
        } else {
            $text = "🔐 <b>Запрос доступа</b>\n\nУ вас уже есть уровень доступа выше гостя: {$levelName}";
        }
        
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text);
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
    
    /**
     * Handle deletion of a specific project
     */
    private function handleDeleteSpecificProject(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("AdminHandler: handleDeleteSpecificProject called with category: '{$category}' for user: {$userId}");
        
        // Extract project code from category
        // Handle both formats:
        // 1. New format: Адм_УдПр_{projectCode}
        // 2. Old format: Админка_УдалитьПроект_{projectCode}
        $projectCode = '';
        if (str_starts_with($category, 'Адм_УдПр_')) {
            $projectCode = str_replace('Адм_УдПр_', '', $category);
        } elseif (str_starts_with($category, 'Админка_УдалитьПроект_')) {
            $projectCode = str_replace('Админка_УдалитьПроект_', '', $category);
        }
        
        if (empty($projectCode)) {
            $text = '❌ Не удалось определить код проекта для удаления';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Check if project exists (in JSON file OR database)
        $databaseService = $this->adminService->getDatabaseService();
        $existsInJson = $this->adminService->projectExists($projectCode);
        $existsInDatabase = $databaseService->projectExists($projectCode);
        
        if (!$existsInJson && !$existsInDatabase) {
            $text = "❌ Проект <b>{$projectCode}</b> не найден";
            $text = $this->ensureUtf8Encoding($text);
            $keyboard = $this->adminService->createBackToProjectsKeyboard();
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Show confirmation message with buttons
        $text = "🗑️ <b>Подтверждение удаления проекта</b>\n\n";
        $text .= "Вы действительно хотите удалить проект <b>{$projectCode}</b>?\n\n";
        $text .= "Все данные, связанные с этим проектом, будут удалены без возможности восстановления.";
        $text = $this->ensureUtf8Encoding($text);
        
        // Create confirmation keyboard
        $keyboard = [
            [
                ['text' => '✅ Да, удалить', 'callback_data' => "category_Админка_ПодтвУдал_{$projectCode}"],
                ['text' => '❌ Отмена', 'callback_data' => 'category_Админка_Проекты_Удалить']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Handle confirmation of project deletion
     */
    private function handleConfirmDeleteProject(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("AdminHandler: handleConfirmDeleteProject called with category: '{$category}' for user: {$userId}");
        
        // Extract project code from category (Админка_ПодтвУдал_{projectCode})
        $projectCode = str_replace('Админка_ПодтвУдал_', '', $category);
        
        if (empty($projectCode)) {
            $text = '❌ Не удалось определить код проекта для удаления';
            $text = $this->ensureUtf8Encoding($text);
            $keyboard = $this->adminService->createBackToProjectsKeyboard();
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Try to delete from both database and JSON file
        $databaseService = $this->adminService->getDatabaseService();
        $deletedFromDatabase = $databaseService->deleteProjectFromDatabase($projectCode);
        $deletedFromJson = $this->adminService->deleteProject($projectCode);
        
        if ($deletedFromDatabase || $deletedFromJson) {
            $text = "✅ <b>Проект удален</b>\n\n";
            $text .= "Проект <b>{$projectCode}</b> успешно удален из системы.";
            $text = $this->ensureUtf8Encoding($text);
        } else {
            $text = "❌ <b>Ошибка при удалении проекта</b>\n\n";
            $text .= "Не удалось удалить проект <b>{$projectCode}</b>. Попробуйте позже.";
            $text = $this->ensureUtf8Encoding($text);
        }
        
        $keyboard = $this->adminService->createBackToProjectsKeyboard();
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Check if string starts with prefix (custom implementation for PHP < 8.0 compatibility)
     */
    private function stringStartsWith(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === 0;
    }
}