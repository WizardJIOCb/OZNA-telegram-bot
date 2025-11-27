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
 * Handler for admin related callback queries
 * This is the FIXED AdminHandler file (might not be used)
 */
class AdminHandler implements CallbackHandlerInterface
{
    private AdminService $adminService;
    private MessageService $messageService;

    public function __construct(AdminService $adminService, MessageService $messageService)
    {
        error_log("AdminHandler FIXED constructor called");
        $this->adminService = $adminService;
        $this->messageService = $messageService;
    }

    public function canHandle(string $callbackData): bool
    {
        error_log("AdminHandler canHandle called with callbackData: '{$callbackData}'");
        // Fix encoding issues with callback data
        $callbackData = $this->ensureUtf8Encoding($callbackData);
        error_log("AdminHandler checking if '{$callbackData}' starts with 'category_Админка': " . (str_starts_with($callbackData, 'category_Админка') ? 'true' : 'false'));
        error_log("AdminHandler checking if '{$callbackData}' starts with 'category_Адм_': " . (str_starts_with($callbackData, 'category_Адм_') ? 'true' : 'false'));
        error_log("AdminHandler checking if '{$callbackData}' equals 'category_Главное': " . ($callbackData === 'category_Главное' ? 'true' : 'false'));
        error_log("AdminHandler checking if '{$callbackData}' starts with 'adm_req_': " . (str_starts_with($callbackData, 'adm_req_') ? 'true' : 'false'));
        
        $startsWithCategoryAdmin = str_starts_with($callbackData, 'category_Админка');
        $startsWithCategoryAdm = str_starts_with($callbackData, 'category_Адм_');
        $equalsCategoryMain = $callbackData === 'category_Главное';
        $startsWithAdmReq = str_starts_with($callbackData, 'adm_req_');
        
        error_log("AdminHandler startsWithCategoryAdmin: " . ($startsWithCategoryAdmin ? 'true' : 'false'));
        error_log("AdminHandler startsWithCategoryAdm: " . ($startsWithCategoryAdm ? 'true' : 'false'));
        error_log("AdminHandler equalsCategoryMain: " . ($equalsCategoryMain ? 'true' : 'false'));
        error_log("AdminHandler startsWithAdmReq: " . ($startsWithAdmReq ? 'true' : 'false'));
        
        $canHandle = $startsWithCategoryAdmin || $startsWithCategoryAdm || $equalsCategoryMain || $startsWithAdmReq;
        error_log("AdminHandler canHandle check for '{$callbackData}': " . ($canHandle ? 'true' : 'false'));
        
        // Дополнительная проверка для заявок на доступ
        if ($startsWithCategoryAdmin) {
            error_log("AdminHandler canHandle: callbackData '{$callbackData}' starts with 'category_Админка'");
        }
        
        if ($startsWithAdmReq) {
            error_log("AdminHandler canHandle: callbackData '{$callbackData}' starts with 'adm_req_'");
        }
        
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("AdminHandler handling callback for user: {$userId}, category: '{$category}', chatId: {$chatId}");

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);

        // Кнопка "Главное" доступна всем
        if ($category === 'Главное') {
            error_log("AdminHandler handling 'Главное'");
            return $this->showMainMenu($chatId);
        }

        // Special case: Allow access request even for non-admins
        if ($category === 'Админка_ЗапросДоступ') {
            error_log("AdminHandler handling 'Админка_ЗапросДоступ'");
            return $this->handleAccessRequest($callbackQuery, $conversation);
        }

        // Проверяем уровень доступа пользователя
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        error_log("AdminHandler user {$userId} access level: {$userAccessLevel}");
        
        // Проверяем, имеет ли пользователь доступ к админке (уровень Администратор или выше)
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            error_log("AdminHandler user {$userId} doesn't have admin access, showing access request menu");
            // Показываем меню с кнопкой "Запросить доступ"
            return $this->showAccessRequestMenu($chatId);
        }

        // Роутинг для администраторов и суперадминов
        error_log("AdminHandler routing admin callback for category: '{$category}'");
        $result = $this->routeAdminCallback($callbackQuery, $category, $conversation);
        error_log("AdminHandler routeAdminCallback result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'success' : 'failed') : 'unknown'));
        return $result;
    }

    public function getPriority(): int
    {
        return 30; // Higher priority than AccessLevelHandler
    }

    private function showAccessRequestMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->adminService->createAccessRequestKeyboard();
        $text = "🔐 <b>Админка</b>\n\nДля получения доступа к административным функциям подайте заявку.";
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function routeAdminCallback(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("AdminHandler routeAdminCallback called with category: '{$category}' for user: {$userId}");

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);

        // Возврат в главное меню
        if ($category === 'Главное') {
            error_log("AdminHandler routeAdminCallback handling 'Главное'");
            return $this->showMainMenu($chatId);
        }

        // Главное меню админки
        if ($category === 'Админка') {
            error_log("AdminHandler routeAdminCallback handling 'Админка'");
            return $this->showAdminMainMenu($chatId, $userId);
        }

        // Обработка новых форматов callback-ов для заявок на доступ
        // Проверяем, если это очистка всех заявок (новый формат)
        if ($category === 'adm_req_clear') {
            error_log("AdminHandler routeAdminCallback handling 'adm_req_clear'");
            return $this->clearAllAccessRequests($callbackQuery, $chatId);
        }
        
        // Проверяем, если это просмотр конкретной заявки (новый формат)
        if (str_starts_with($category, 'adm_req_view_')) {
            $requestUserId = str_replace('adm_req_view_', '', $category);
            error_log("AdminHandler routeAdminCallback handling 'adm_req_view_' for user: {$requestUserId}");
            return $this->showAccessRequestDetails($callbackQuery, $requestUserId, $chatId);
        }
        
        // Проверяем, если это одобрение заявки (новый формат)
        if (str_starts_with($category, 'adm_req_approve_')) {
            $requestUserId = str_replace('adm_req_approve_', '', $category);
            error_log("AdminHandler routeAdminCallback handling 'adm_req_approve_' for user: {$requestUserId}");
            return $this->approveAccessRequest($callbackQuery, $requestUserId, $chatId);
        }
        
        // Проверяем, если это отклонение заявки (новый формат)
        if (str_starts_with($category, 'adm_req_reject_')) {
            $requestUserId = str_replace('adm_req_reject_', '', $category);
            error_log("AdminHandler routeAdminCallback handling 'adm_req_reject_' for user: {$requestUserId}");
            return $this->rejectAccessRequest($callbackQuery, $requestUserId, $chatId);
        }

        // Удаление конкретного проекта (сокращенный формат)
        if (str_starts_with($category, 'Адм_УдПр_')) {
            error_log("AdminHandler routeAdminCallback handling 'Адм_УдПр_'");
            return $this->handleDeleteSpecificProject($callbackQuery, $category, $conversation);
        }
        
        // Удаление конкретного проекта (старый формат для совместимости)
        if (str_starts_with($category, 'Админка_УдалитьПроект_')) {
            error_log("AdminHandler routeAdminCallback handling 'Админка_УдалитьПроект_'");
            return $this->handleDeleteSpecificProject($callbackQuery, $category, $conversation);
        }

        // Подтверждение удаления проекта (сокращенный callback)
        if (str_starts_with($category, 'Админка_ПодтвУдал_')) {
            error_log("AdminHandler routeAdminCallback handling 'Админка_ПодтвУдал_'");
            return $this->handleConfirmDeleteProject($callbackQuery, $category, $conversation);
        }

        // Управление проектами
        if (str_starts_with($category, 'Админка_Проекты')) {
            error_log("AdminHandler routeAdminCallback handling 'Админка_Проекты'");
            return $this->handleProjectManagement($callbackQuery, $category, $conversation);
        }

        // Заявки на доступ
        error_log("AdminHandler routeAdminCallback checking category: '{$category}' for 'Админка_Заявки'");
        if ($category === 'Админка_Заявки') {
            error_log("AdminHandler routeAdminCallback handling 'Админка_Заявки' (exact match)");
            return $this->handleAccessRequests($callbackQuery, $category, $conversation);
        }
        
        // Проверяем префикс для заявок на доступ
        if (str_starts_with($category, 'Админка_Заявки')) {
            error_log("AdminHandler routeAdminCallback handling 'Админка_Заявки' (prefix match)");
            return $this->handleAccessRequests($callbackQuery, $category, $conversation);
        }
        
        error_log("AdminHandler routeAdminCallback category '{$category}' does not match 'Админка_Заявки'");
        
        // Запрос доступа (для обычных пользователей)
        if ($category === 'Админка_ЗапросДоступ') {
            error_log("AdminHandler routeAdminCallback handling 'Админка_ЗапросДоступ'");
            return $this->handleAccessRequest($callbackQuery, $conversation);
        }

        error_log("AdminHandler routeAdminCallback unknown category: '{$category}'");
        $text = 'Неизвестная команда админки';
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text);
    }

    private function showAdminMainMenu(string $chatId, int $userId): ServerResponse
    {
        // Определяем уровень доступа пользователя
        $accessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($accessLevel);
        
        // Ensure UTF-8 encoding
        $levelName = $this->ensureUtf8Encoding($levelName);
        
        $keyboard = [
            [
                ['text' => '📋 Управление проектами', 'callback_data' => 'category_Админка_Проекты']
            ],
            [
                ['text' => '👥 Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']
            ]
        ];
        
        // Только суперадмины могут управлять уровнями доступа
        if ($accessLevel === DatabaseService::ACCESS_LEVEL_SUPER_ADMIN) {
            $keyboard[] = [
                ['text' => '🔐 Управление уровнями доступа', 'callback_data' => 'category_УровеньДоступа_Управление']
            ];
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
        ];

        $text = "⚙️ <b>Панель администратора</b>\n\n" .
            "Ваш уровень доступа: {$levelName}\n\n" .
            "Выберите действие:";
        $text = $this->ensureUtf8Encoding($text);
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Показать главное меню
     */
    private function showMainMenu(string $chatId): ServerResponse
    {
        // Копируем логику из StartCommand
        $categories = [
            'Табель'  => 'Здесь будет табель',
            'Помощь' => 'Здесь будет помощь',
            'Профиль' => 'Просмотр и управление профилем',
            'Админка' => 'Административные функции'
        ];

        $keyboard = [];
        foreach ($categories as $key => $value) {
            $keyboard[] = [
                ['text' => $key, 'callback_data' => 'category_' . $key]
            ];
        }

        $text = "<b>Главное меню</b>\n\n(для вызова этого меню в любой момент отправьте /start)";
        $text = $this->ensureUtf8Encoding($text);
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function handleProjectManagement(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);


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
        $text = "📋 <b>Управление проектами</b>\n\nВыберите действие:";
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function showProjectList(string $chatId): ServerResponse
    {
        $projects = $this->adminService->getAllProjects();
        $text = "📋 <b>Список проектов:</b>\n\n";
        
        if (empty($projects)) {
            $text .= "Проекты не найдены.";
        } else {
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
        
        // Устанавливаем состояние ожидания ввода названия проекта
        $conversation->notes['admin_action'] = 'add_project';
        $conversation->update();

        $text = "✏️ <b>Добавление проекта</b>\n\nВведите код проекта (например: ОИ834, БКНС115):";
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text);
    }

    private function showDeleteProjectMenu(string $chatId): ServerResponse
    {
        $projects = $this->adminService->getAllProjects();
        
        if (empty($projects)) {
            $keyboard = $this->adminService->createBackToProjectsKeyboard();
            $text = '❌ Нет проектов для удаления';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }

        $keyboard = $this->adminService->createDeleteProjectKeyboard($projects);
        
        $text = "🗑️ <b>Удаление проекта</b>\n\nВыберите проект для удаления:";
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function handleAccessRequests(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("AdminHandler handleAccessRequests called with category: '{$category}' for user: {$userId}");
        
        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        
        // Получаем все заявки на доступ
        $allRequests = $this->adminService->getAllAccessRequests();
        error_log("AdminHandler handleAccessRequests total requests: " . count($allRequests));
        
        // Проверяем подкатегорию
        if ($category === 'Админка_Заявки') {
            // Показываем список заявок
            error_log("AdminHandler handleAccessRequests showing access requests list");
            return $this->showAccessRequestsList($chatId);
        }
        
        // Проверяем, если это очистка всех заявок (новый формат)
        if ($category === 'adm_req_clear') {
            error_log("AdminHandler handleAccessRequests clearing all access requests");
            return $this->clearAllAccessRequests($callbackQuery, $chatId);
        }
        
        // Проверяем, если это просмотр конкретной заявки (новый формат)
        if (str_starts_with($category, 'adm_req_view_')) {
            $userId = str_replace('adm_req_view_', '', $category);
            error_log("AdminHandler handleAccessRequests showing details for user: {$userId}");
            return $this->showAccessRequestDetails($callbackQuery, $userId, $chatId);
        }
        
        // Проверяем, если это одобрение заявки (новый формат)
        if (str_starts_with($category, 'adm_req_approve_')) {
            $userId = str_replace('adm_req_approve_', '', $category);
            error_log("AdminHandler handleAccessRequests approving request for user: {$userId}");
            return $this->approveAccessRequest($callbackQuery, $userId, $chatId);
        }
        
        // Проверяем, если это отклонение заявки (новый формат)
        if (str_starts_with($category, 'adm_req_reject_')) {
            $userId = str_replace('adm_req_reject_', '', $category);
            error_log("AdminHandler handleAccessRequests rejecting request for user: {$userId}");
            return $this->rejectAccessRequest($callbackQuery, $userId, $chatId);
        }
        
        // Поддержка старого формата для обратной совместимости
        // Проверяем, если это очистка всех заявок (старый формат)
        if ($category === 'Админка_Заявки_Очистить') {
            error_log("AdminHandler handleAccessRequests clearing all access requests (old format)");
            return $this->clearAllAccessRequests($callbackQuery, $chatId);
        }
        
        // Проверяем, если это просмотр конкретной заявки (старый формат)
        if (str_starts_with($category, 'Админка_Заявки_Просмотр_')) {
            $userId = str_replace('Админка_Заявки_Просмотр_', '', $category);
            error_log("AdminHandler handleAccessRequests showing details for user: {$userId} (old format)");
            return $this->showAccessRequestDetails($callbackQuery, $userId, $chatId);
        }
        
        // Проверяем, если это одобрение заявки (старый формат)
        if (str_starts_with($category, 'Админка_Заявки_Одобрить_')) {
            $userId = str_replace('Админка_Заявки_Одобрить_', '', $category);
            error_log("AdminHandler handleAccessRequests approving request for user: {$userId} (old format)");
            return $this->approveAccessRequest($callbackQuery, $userId, $chatId);
        }
        
        // Проверяем, если это отклонение заявки (старый формат)
        if (str_starts_with($category, 'Админка_Заявки_Отклонить_')) {
            $userId = str_replace('Админка_Заявки_Отклонить_', '', $category);
            error_log("AdminHandler handleAccessRequests rejecting request for user: {$userId} (old format)");
            return $this->rejectAccessRequest($callbackQuery, $userId, $chatId);
        }
        
        error_log("AdminHandler handleAccessRequests unknown category: '{$category}'");
        $text = 'Неизвестная команда управления заявками';
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text);
    }

    /**
     * Показать список заявок на доступ
     */
    private function showAccessRequestsList(string $chatId): ServerResponse
    {
        error_log("AdminHandler showAccessRequestsList START for chatId: {$chatId}");
        
        try {
            $requests = $this->adminService->getAllAccessRequests();
            
            error_log("AdminHandler showAccessRequestsList found " . count($requests) . " requests");
            
            // Создаем текст сообщения с правильной кодировкой UTF-8
            $text = "👥 <b>Заявки на доступ к админке</b>\n\n";
            $text .= "Всего заявок: " . count($requests) . "\n\n";
            
            // Инициализируем клавиатуру
            $keyboard = [];
            
            if (empty($requests)) {
                error_log("AdminHandler showAccessRequestsList no requests found");
                $text .= "Нет новых заявок на доступ.";
                $keyboard = [
                    [
                        ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                    ]
                ];
            } else {
                error_log("AdminHandler showAccessRequestsList processing " . count($requests) . " requests");
                $text .= "Выберите заявку для просмотра:\n\n";
                
                // Создаем клавиатуру с кнопками для каждой заявки
                $keyboard = [];
                foreach ($requests as $index => $request) {
                    $userName = $request['user_name'] ?? 'Неизвестный пользователь';
                    $userId = $request['user_id'];
                    
                    error_log("AdminHandler showAccessRequestsList processing request #{$index}: user_id={$userId}, user_name={$userName}");
                    
                    // Ограничиваем длину имени пользователя для кнопки и добавляем многоточия
                    if (mb_strlen($userName) > 20) {
                        $userName = mb_substr($userName, 0, 17) . '...';
                    }
                    
                    // Убеждаемся, что текст в кнопке в правильной кодировке UTF-8
                    $userName = $this->ensureUtf8Encoding($userName);
                    
                    // ВАЖНО: Используем сокращенный callback_data чтобы не превысить лимит Telegram (64 байта)
                    // Формат: adm_req_view_{userId}
                    $shortCallbackData = "adm_req_view_{$userId}";
                    $keyboard[] = [
                        ['text' => $userName, 'callback_data' => $shortCallbackData]
                    ];
                }
                
                // Ограничиваем количество кнопок (максимум 20)
                if (count($keyboard) > 20) {
                    $keyboard = array_slice($keyboard, 0, 20);
                    $text .= "⚠️ Отображены только первые 20 заявок\n\n";
                }
                
                // Добавляем кнопку для очистки всех заявок
                // Формат: adm_req_clear
                $keyboard[] = [
                    ['text' => '🗑️ Очистить все заявки', 'callback_data' => 'adm_req_clear']
                ];
                
                $keyboard[] = [
                    ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                ];
            }
            
            // Убеждаемся, что текст сообщения в правильной кодировке UTF-8
            $text = $this->ensureUtf8Encoding($text);
            
            error_log("AdminHandler showAccessRequestsList sending message to chatId: {$chatId}");
            error_log("AdminHandler showAccessRequestsList message text: " . mb_substr($text, 0, 100) . "...");
            error_log("AdminHandler showAccessRequestsList keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE));
            
            $result = $this->messageService->sendMessage($chatId, $text, $keyboard);
            
            error_log("AdminHandler showAccessRequestsList message send result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'success' : 'failed') : 'unknown'));
            
            if (is_object($result) && method_exists($result, 'isOk') && !$result->isOk()) {
                error_log("AdminHandler showAccessRequestsList error: " . $result->getDescription());
                error_log("AdminHandler showAccessRequestsList error code: " . $result->getErrorCode());
            }
            
            error_log("AdminHandler showAccessRequestsList END");
            return $result;
            
        } catch (Exception $e) {
            error_log("AdminHandler showAccessRequestsList EXCEPTION: " . $e->getMessage());
            error_log("AdminHandler showAccessRequestsList EXCEPTION trace: " . $e->getTraceAsString());
            throw $e;
        } catch (Error $e) {
            error_log("AdminHandler showAccessRequestsList ERROR: " . $e->getMessage());
            error_log("AdminHandler showAccessRequestsList ERROR trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Показать детали конкретной заявки на доступ
     */
    private function showAccessRequestDetails(CallbackQuery $callbackQuery, string $userId, string $adminChatId): ServerResponse
    {
        $requests = $this->adminService->getAllAccessRequests();
        $request = null;
        
        // Ищем заявку по ID пользователя
        foreach ($requests as $req) {
            // Ensure both values are the same type for comparison
            if ((string)$req['user_id'] === (string)$userId) {
                $request = $req;
                break;
            }
        }
        
        if ($request === null) {
            $text = '❌ Заявка не найдена';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($adminChatId, $text);
        }
        
        $userName = $request['user_name'] ?? 'Неизвестный пользователь';
        $userFio = $request['user_fio'] ?? '';
        $userTelegramName = $request['user_telegram_name'] ?? '';
        $timestamp = date('d.m.Y H:i', $request['timestamp']);
        
        // Создаем текст сообщения с деталями заявки с правильной кодировкой UTF-8
        $text = "👤 <b>Заявка на доступ к админке</b>\n\n";
        $text .= "Имя: {$userName}\n";
        if (!empty($userFio)) {
            $text .= "ФИО из профиля: {$userFio}\n";
        }
        if (!empty($userTelegramName) && $userTelegramName !== $userName) {
            $text .= "Имя в Telegram: {$userTelegramName}\n";
        }
        $text .= "User ID: {$userId}\n";
        $text .= "Дата подачи: {$timestamp}\n\n";
        $text .= "Выберите действие:";
        
        // Убеждаемся, что текст сообщения в правильной кодировке UTF-8
        $text = $this->ensureUtf8Encoding($text);
        
        // Создаем клавиатуру с кнопками для одобрения/отклонения
        $keyboard = [
            [
                ['text' => '✅ Одобрить', 'callback_data' => "adm_req_approve_{$userId}"]
            ],
            [
                ['text' => '❌ Отклонить', 'callback_data' => "adm_req_reject_{$userId}"]
            ],
            [
                ['text' => '⬅️ Назад к списку заявок', 'callback_data' => 'category_Админка_Заявки']
            ]
        ];
        
        return $this->messageService->sendMessage($adminChatId, $text, $keyboard);
    }

    /**
     * Очистить все заявки на доступ
     */
    private function clearAllAccessRequests(CallbackQuery $callbackQuery, string $adminChatId): ServerResponse
    {
        // Очищаем все заявки
        $this->adminService->clearAllAccessRequests();
        
        // Показываем обновленный список заявок (который теперь пуст)
        return $this->showAccessRequestsList($adminChatId);
    }

    /**
     * Одобрить заявку на доступ
     */
    private function approveAccessRequest(CallbackQuery $callbackQuery, string $userId, string $adminChatId): ServerResponse
    {
        $requests = $this->adminService->getAllAccessRequests();
        $request = null;
        
        // Ищем заявку по ID пользователя
        foreach ($requests as $req) {
            // Ensure both values are the same type for comparison
            if ((string)$req['user_id'] === (string)$userId) {
                $request = $req;
                break;
            }
        }
        
        if ($request === null) {
            $text = '❌ Заявка не найдена';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($adminChatId, $text);
        }
        
        $userName = $request['user_name'];
        $userChatId = $request['chat_id'];
        
        // Одобряем заявку
        $this->adminService->approveAccessRequest($userId, $userName, $userChatId);
        
        // Показываем обновленный список заявок
        return $this->showAccessRequestsList($adminChatId);
    }

    /**
     * Отклонить заявку на доступ
     */
    private function rejectAccessRequest(CallbackQuery $callbackQuery, string $userId, string $adminChatId): ServerResponse
    {
        $requests = $this->adminService->getAllAccessRequests();
        $request = null;
        
        // Ищем заявку по ID пользователя
        foreach ($requests as $req) {
            // Ensure both values are the same type for comparison
            if ((string)$req['user_id'] === (string)$userId) {
                $request = $req;
                break;
            }
        }
        
        if ($request === null) {
            $text = '❌ Заявка не найдена';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($adminChatId, $text);
        }
        
        $userName = $request['user_name'];
        $userChatId = $request['chat_id'];
        
        // Отклоняем заявку
        $this->adminService->rejectAccessRequest($userId, $userName, $userChatId);
        
        // Показываем обновленный список заявок
        return $this->showAccessRequestsList($adminChatId);
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