<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Conversation;

/**
 * Handler for project moderation functionality
 * Manages user-submitted projects and admin moderation
 */
class ProjectModerationHandler implements CallbackHandlerInterface
{
    private DatabaseService $databaseService;
    private MessageService $messageService;
    private AdminService $adminService;

    public function __construct(
        DatabaseService $databaseService,
        MessageService $messageService,
        AdminService $adminService
    ) {
        $this->databaseService = $databaseService;
        $this->messageService = $messageService;
        $this->adminService = $adminService;
    }

    public function canHandle(string $callbackData): bool
    {
        return str_starts_with($callbackData, 'proj_mod_') ||
               $callbackData === 'category_Админка_МодерацияПроектов';
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);

        error_log("ProjectModerationHandler: Handling callback '{$category}' for user {$userId}");

        // ===== USER PART: Add new project =====
        if ($category === 'proj_mod_add_new') {
            return $this->startAddingNewProject($callbackQuery, $conversation);
        }

        // User confirmed adding new project
        if ($category === 'proj_mod_confirm_add') {
            return $this->confirmAddingNewProject($callbackQuery, $conversation);
        }

        // ===== ADMIN PART: Moderation =====
        if ($category === 'category_Админка_МодерацияПроектов') {
            // Check access level (supervisor+)
            if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $text = '⚠️ <b>Доступ ограничен</b>

Модерация проектов доступна только для Супервайзеров, Администраторов и Суперадминистраторов.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            return $this->showModerationList($chatId);
        }

        // View project details
        if (str_starts_with($category, 'proj_mod_view_')) {
            $projectId = (int)str_replace('proj_mod_view_', '', $category);
            return $this->showProjectDetails($chatId, $projectId, $userAccessLevel);
        }

        // Approve project
        if (str_starts_with($category, 'proj_mod_approve_')) {
            $projectId = (int)str_replace('proj_mod_approve_', '', $category);
            return $this->approveProject($chatId, $userId, $projectId, $userAccessLevel);
        }

        // Start rename
        if (str_starts_with($category, 'proj_mod_rename_start_')) {
            $projectId = (int)str_replace('proj_mod_rename_start_', '', $category);
            return $this->startRenameProject($callbackQuery, $conversation, $projectId, $userAccessLevel);
        }

        // Start reject
        if (str_starts_with($category, 'proj_mod_reject_start_')) {
            $projectId = (int)str_replace('proj_mod_reject_start_', '', $category);
            return $this->startRejectProject($callbackQuery, $conversation, $projectId, $userAccessLevel);
        }

        $text = 'Неизвестная команда модерации проектов';
        return $this->messageService->sendMessage($chatId, $text);
    }

    public function getPriority(): int
    {
        return 25; // High priority
    }

    /**
     * Start adding new project (user action)
     */
    private function startAddingNewProject(CallbackQuery $callbackQuery, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();

        // Check access level (only registered users, not guests)
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_USER) {
            $text = '⚠️ <b>Доступ ограничен</b>

Добавление проектов доступно только зарегистрированным пользователям. Обратитесь к администратору для получения доступа.';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $text = '🆕 <b>Добавление нового проекта</b>

Хотите добавить новый проект в систему?

📝 <b>Что произойдет:</b>
• Вы введете название проекта
• Проект сразу будет доступен для вашей работы
• Администраторы проверят его в ближайшее время

❗️ <b>Требования:</b>
• От 3 до 50 символов
• Буквы, цифры, дефис';

        $keyboard = [
            [
                ['text' => '✅ Продолжить', 'callback_data' => 'proj_mod_confirm_add']
            ],
            [
                ['text' => '❌ Отмена', 'callback_data' => 'category_Проекты_МоиПроекты']
            ]
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Confirm and proceed with adding new project
     */
    private function confirmAddingNewProject(CallbackQuery $callbackQuery, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();

        // Clear any old states first
        $conversation->notes['state'] = null;
        $conversation->notes['awaiting_custom_input'] = null;
        $conversation->notes['awaiting_date_input'] = null;
        $conversation->notes['awaiting_fio_input'] = null;
        $conversation->notes['admin_action'] = null;
        
        // Set conversation state for project moderation
        $conversation->notes['project_mod_action'] = 'add_new';
        $conversation->update();
        
        error_log("ProjectModerationHandler: Set state 'add_new' for user {$userId}");

        $text = '🆕 <b>Добавление нового проекта</b>

Введите название проекта (например: ОИ834, БКНС115):

❗️ <b>Требования к названию:</b>
• От 3 до 50 символов
• Буквы, цифры, дефис
• Название будет автоматически приведено к верхнему регистру';

        return $this->messageService->sendMessage($chatId, $text);
    }

    /**
     * Process new project name from user input
     */
    public function processNewProjectName(string $chatId, int $userId, string $projectName): ServerResponse
    {
        error_log("ProjectModerationHandler: Processing new project '{$projectName}' from user {$userId}");
        
        // Validate project name
        $projectName = trim($projectName);
        
        if (mb_strlen($projectName) < 3 || mb_strlen($projectName) > 50) {
            $text = '❌ <b>Ошибка</b>

Название проекта должно быть от 3 до 50 символов. Попробуйте еще раз.';
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // CRITICAL FIX: Normalize project name BEFORE checking for duplicates
        // This ensures case-insensitive comparison
        $normalizedName = trim(strtoupper($projectName));
        error_log("ProjectModerationHandler: Checking if project '{$normalizedName}' already exists");
        
        // Check if project already exists (checks all statuses: pending, active, renamed)
        if ($this->databaseService->projectExists($normalizedName)) {
            error_log("ProjectModerationHandler: Project '{$normalizedName}' already exists! Rejecting duplicate.");
            $text = '❌ <b>Проект уже существует</b>

Проект с таким названием уже есть в системе. Выберите другое название.';
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        error_log("ProjectModerationHandler: Project '{$normalizedName}' does not exist, proceeding to add");
        
        // Add project to moderation
        try {
            error_log("ProjectModerationHandler: Adding project '{$normalizedName}' to database");
            $projectId = $this->databaseService->addPendingProject($normalizedName, $userId);
            
            if (!$projectId) {
                throw new \Exception('Database returned false');
            }
            
            error_log("ProjectModerationHandler: Project added successfully with ID: {$projectId}");
        } catch (\Exception $e) {
            error_log("ProjectModerationHandler: Failed to add project: " . $e->getMessage());
            
            $text = '❌ <b>Ошибка</b>

Не удалось добавить проект. Попробуйте позже.';
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Success message to user
        $text = "✅ <b>Проект добавлен!</b>

Проект: <b>{$normalizedName}</b>

Проект сразу доступен для вашей работы. Администраторы получат уведомление и проверят его в ближайшее время.";

        // CRITICAL FIX: Send response to user FIRST, then notify administrators
        // This prevents blocking while sending notifications to multiple admins
        $response = $this->messageService->sendMessage($chatId, $text);
        
        // Notify administrators in background (after user gets response)
        try {
            $this->notifyAdministratorsAboutNewProject($userId, $normalizedName);
        } catch (\Exception $e) {
            error_log("ProjectModerationHandler: Failed to notify administrators: " . $e->getMessage());
            // Don't fail if notifications fail
        }

        return $response;

    }

    /**
     * Notify administrators about new project
     */
    private function notifyAdministratorsAboutNewProject(int $userId, string $projectName): void
    {
        $userData = $this->databaseService->getUserData($userId);
        $userFio = $userData['fio'] ?? 'Неизвестно';
        
        $message = "🔔 <b>Новый проект на согласование</b>\n\n";
        $message .= "👤 Пользователь: {$userFio}\n";
        $message .= "🆔 User ID: {$userId}\n";
        $message .= "📁 Проект: <b>{$projectName}</b>\n";
        $message .= "📅 Дата добавления: " . date('d.m.Y H:i');

        // Получаем актуальное количество проектов на согласование
        $pendingCount = $this->databaseService->getPendingProjectsCount();
        $countMessage = "\n\n📢 Всего проектов в ожидании: {$pendingCount}";
        $message .= $countMessage;

        // Создаем клавиатуру с кнопкой быстрого доступа к заявкам
        $keyboard = [
            [
                ['text' => '👁️ Посмотреть заявки', 'callback_data' => 'category_Админка_МодерацияПроектов']
            ]
        ];

        // Send to all supervisors and above
        $allUsers = $this->databaseService->getAllUsers();
        foreach ($allUsers as $user) {
            $accessLevel = $user['data']['access_level'] ?? 0;
            if ($accessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                try {
                    $this->messageService->sendMessage($user['user_id'], $message, $keyboard);
                } catch (\Exception $e) {
                    error_log("ProjectModerationHandler: Failed to notify admin {$user['user_id']}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Show moderation list (admin action)
     */
    private function showModerationList(string $chatId): ServerResponse
    {
        $pendingProjects = $this->databaseService->getPendingProjects();

        if (empty($pendingProjects)) {
            $text = '🔔 <b>Проекты на согласование</b>

Нет проектов, ожидающих проверки.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_УправлениеПроектом']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }

        $text = '🔔 <b>Проекты на согласование</b>

У вас ' . count($pendingProjects) . ' проект(ов) ожидают проверки:

';

        $keyboard = [];
        foreach ($pendingProjects as $index => $project) {
            $num = $index + 1;
            $projectName = $project['current_name'];
            $userFio = $project['user_fio'] ?? 'Неизвестно';
            $addedAt = date('d.m.Y H:i', strtotime($project['added_at']));
            $tabelCount = $project['tabel_count'] ?? 0;

            $text .= "━━━━━━━━━━━━━━━━━━━━━
{$num}️⃣ <b>{$projectName}</b>
👤 {$userFio}
📅 {$addedAt}
📊 Записей табеля: {$tabelCount}

";

            $keyboard[] = [
                ['text' => "🔍 {$projectName}", 'callback_data' => "proj_mod_view_{$project['id']}"]
            ];
        }

        $keyboard[] = [
            ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show project details (admin action)
     */
    private function showProjectDetails(string $chatId, int $projectId, int $userAccessLevel): ServerResponse
    {
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            $text = '⚠️ <b>Доступ ограничен</b>';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $project = $this->databaseService->getModerationProjectById($projectId);

        if (!$project) {
            $text = '❌ Проект не найден';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $projectName = $project['current_name'];
        $userFio = $project['user_fio'] ?? 'Неизвестно';
        $addedAt = date('d.m.Y H:i', strtotime($project['added_at']));
        $tabelCount = $project['tabel_count'] ?? 0;

        $text = "🔍 <b>Детали проекта</b>

📁 Проект: <b>{$projectName}</b>
👤 Добавил: {$userFio}
📅 Дата: {$addedAt}
📊 Записей табеля: {$tabelCount}

Выберите действие:";

        $keyboard = [
            [
                ['text' => '✅ Одобрить', 'callback_data' => "proj_mod_approve_{$projectId}"],
                ['text' => '✏️ Переименовать', 'callback_data' => "proj_mod_rename_start_{$projectId}"]
            ],
            [
                ['text' => '❌ Отклонить', 'callback_data' => "proj_mod_reject_start_{$projectId}"]
            ],
            [
                ['text' => '⬅️ Назад к списку', 'callback_data' => 'category_Админка_МодерацияПроектов']
            ]
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Approve project (admin action)
     */
    private function approveProject(string $chatId, int $moderatorId, int $projectId, int $userAccessLevel): ServerResponse
    {
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            $text = '⚠️ <b>Доступ ограничен</b>';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $project = $this->databaseService->getModerationProjectById($projectId);

        if (!$project) {
            $text = '❌ Проект не найден';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $success = $this->databaseService->approveProject($projectId, $moderatorId);

        if (!$success) {
            $text = '❌ <b>Ошибка</b>

Не удалось одобрить проект. Попробуйте позже.';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $projectName = $project['current_name'];
        $addedBy = $project['added_by'];

        // Success message to moderator
        $text = "✅ <b>Проект одобрен</b>

Проект: <b>{$projectName}</b>

Теперь проект доступен всем пользователям системы.";

        $this->messageService->sendMessage($chatId, $text);

        // Notify user
        $moderatorData = $this->databaseService->getUserData($moderatorId);
        $moderatorFio = $moderatorData['fio'] ?? 'Администратор';
        
        $userMessage = "✅ <b>Ваш проект одобрен</b>

Проект: <b>{$projectName}</b>
Модератор: {$moderatorFio}
Дата: " . date('d.m.Y H:i') . "

Теперь проект доступен всем пользователям системы.";

        try {
            $this->messageService->sendMessage($addedBy, $userMessage);
        } catch (\Exception $e) {
            error_log("ProjectModerationHandler: Failed to notify user {$addedBy}: " . $e->getMessage());
        }

        // Return to moderation list
        return $this->showModerationList($chatId);
    }

    /**
     * Start renaming project (admin action)
     */
    private function startRenameProject(CallbackQuery $callbackQuery, Conversation $conversation, int $projectId, int $userAccessLevel): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();

        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            $text = '⚠️ <b>Доступ ограничен</b>';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $project = $this->databaseService->getModerationProjectById($projectId);

        if (!$project) {
            $text = '❌ Проект не найден';
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Clear any old states first
        $conversation->notes['state'] = null;
        $conversation->notes['awaiting_custom_input'] = null;
        $conversation->notes['awaiting_date_input'] = null;
        $conversation->notes['awaiting_fio_input'] = null;
        $conversation->notes['admin_action'] = null;
        
        // Set conversation state for project renaming
        $conversation->notes['project_mod_action'] = 'rename';
        $conversation->notes['project_mod_id'] = $projectId;
        $conversation->update();
        
        error_log("ProjectModerationHandler: Set state 'rename' for project {$projectId}, cleared old states");

        $projectName = $project['current_name'];
        $tabelCount = $project['tabel_count'] ?? 0;

        $text = "✏️ <b>Переименование проекта</b>

Текущее название: <b>{$projectName}</b>
Записей табеля: {$tabelCount}

Введите новое название проекта:

❗️ Все записи табеля будут автоматически обновлены!";

        return $this->messageService->sendMessage($chatId, $text);
    }

    /**
     * Process rename project from admin input
     */
    public function processRenameProject(string $chatId, int $moderatorId, int $projectId, string $newName, string $comment = ''): ServerResponse
    {
        // Validate new name
        $newName = trim($newName);
        
        if (mb_strlen($newName) < 3 || mb_strlen($newName) > 50) {
            $text = '❌ <b>Ошибка</b>

Название проекта должно быть от 3 до 50 символов. Попробуйте еще раз.';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $project = $this->databaseService->getModerationProjectById($projectId);

        if (!$project) {
            $text = '❌ Проект не найден';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $oldName = $project['current_name'];
        $addedBy = $project['added_by'];

        // Rename project
        $success = $this->databaseService->renameProject($projectId, $newName, $moderatorId, $comment);

        if (!$success) {
            $text = '❌ <b>Ошибка</b>

Не удалось переименовать проект. Попробуйте позже.';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $normalizedNewName = trim(strtoupper($newName));

        // Success message to moderator
        $text = "✏️ <b>Проект переименован</b>

Было: <b>{$oldName}</b>
Стало: <b>{$normalizedNewName}</b>

Все записи табеля автоматически обновлены.";

        $this->messageService->sendMessage($chatId, $text);

        // Notify user
        $moderatorData = $this->databaseService->getUserData($moderatorId);
        $moderatorFio = $moderatorData['fio'] ?? 'Администратор';
        
        $userMessage = "✏️ <b>Ваш проект переименован</b>

Было: <b>{$oldName}</b>
Стало: <b>{$normalizedNewName}</b>
Модератор: {$moderatorFio}
Дата: " . date('d.m.Y H:i');

        if (!empty($comment)) {
            $userMessage .= "
Комментарий: {$comment}";
        }

        $userMessage .= "

Все ваши записи табеля автоматически обновлены.";

        try {
            $this->messageService->sendMessage($addedBy, $userMessage);
        } catch (\Exception $e) {
            error_log("ProjectModerationHandler: Failed to notify user {$addedBy}: " . $e->getMessage());
        }

        // Return to moderation list
        return $this->showModerationList($chatId);
    }

    /**
     * Start rejecting project (admin action)
     */
    private function startRejectProject(CallbackQuery $callbackQuery, Conversation $conversation, int $projectId, int $userAccessLevel): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();

        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            $text = '⚠️ <b>Доступ ограничен</b>';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $project = $this->databaseService->getModerationProjectById($projectId);

        if (!$project) {
            $text = '❌ Проект не найден';
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Clear any old states first
        $conversation->notes['state'] = null;
        $conversation->notes['awaiting_custom_input'] = null;
        $conversation->notes['awaiting_date_input'] = null;
        $conversation->notes['awaiting_fio_input'] = null;
        $conversation->notes['admin_action'] = null;
        
        // Set conversation state for project rejection
        $conversation->notes['project_mod_action'] = 'reject';
        $conversation->notes['project_mod_id'] = $projectId;
        $conversation->update();
        
        error_log("ProjectModerationHandler: Set state 'reject' for project {$projectId}, cleared old states");

        $projectName = $project['current_name'];

        $text = "❌ <b>Отклонение проекта</b>

Проект: <b>{$projectName}</b>

Введите причину отклонения:

❗️ Пользователь увидит эту причину и сможет добавить проект заново с правильным названием.";

        return $this->messageService->sendMessage($chatId, $text);
    }

    /**
     * Process reject project from admin input
     */
    public function processRejectProject(string $chatId, int $moderatorId, int $projectId, string $reason): ServerResponse
    {
        $project = $this->databaseService->getModerationProjectById($projectId);

        if (!$project) {
            $text = '❌ Проект не найден';
            return $this->messageService->sendMessage($chatId, $text);
        }

        $projectName = $project['current_name'];
        $addedBy = $project['added_by'];

        // Reject project
        $success = $this->databaseService->rejectProject($projectId, $moderatorId, $reason);

        if (!$success) {
            $text = '❌ <b>Ошибка</b>

Не удалось отклонить проект. Попробуйте позже.';
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Success message to moderator
        $text = "❌ <b>Проект отклонён</b>

Проект: <b>{$projectName}</b>
Причина: {$reason}

Проект скрыт из списка доступных.";

        $this->messageService->sendMessage($chatId, $text);

        // Notify user
        $moderatorData = $this->databaseService->getUserData($moderatorId);
        $moderatorFio = $moderatorData['fio'] ?? 'Администратор';
        
        $userMessage = "❌ <b>Ваш проект отклонён</b>

Проект: <b>{$projectName}</b>
Модератор: {$moderatorFio}
Дата: " . date('d.m.Y H:i') . "
Причина: {$reason}

Вы можете добавить проект заново с правильным названием.";

        try {
            $this->messageService->sendMessage($addedBy, $userMessage);
        } catch (\Exception $e) {
            error_log("ProjectModerationHandler: Failed to notify user {$addedBy}: " . $e->getMessage());
        }

        // Return to moderation list
        return $this->showModerationList($chatId);
    }
}
