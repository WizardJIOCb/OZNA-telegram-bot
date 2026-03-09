<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

// Explicitly include the interface to ensure it's available
require_once __DIR__ . '/../Services/CallbackHandlerInterface.php';
require_once __DIR__ . '/../Services/ManualInputStateManager.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager;
use Longman\TelegramBot\Commands\SystemCommands\Services\ExportSecurityService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for project management related callback queries
 * Handles all project-related functionality including personal project lists
 */
class ProjectHandler implements CallbackHandlerInterface
{
    private AdminService $adminService;
    private MessageService $messageService;
    private DatabaseService $databaseService;
    private ManualInputStateManager $stateManager;
    private ExportSecurityService $exportSecurityService;

    private static bool $schemaMigrated = false;

    public function __construct(AdminService $adminService, MessageService $messageService)
    {
        error_log("ProjectHandler constructor called");
        $this->adminService = $adminService;
        $this->messageService = $messageService;
        $this->databaseService = new DatabaseService();
        $this->stateManager = new ManualInputStateManager();
        $this->exportSecurityService = new ExportSecurityService($this->databaseService);
        $this->ensureSchemaMigrations();
    }

    private function ensureSchemaMigrations(): void
    {
        if (self::$schemaMigrated) {
            return;
        }
        self::$schemaMigrated = true;

        try {
            $conn = $this->databaseService->getConnection();
            // Add 'СМР' to work_type ENUM in project_milestones if missing
            $result = $conn->query("SHOW COLUMNS FROM project_milestones LIKE 'work_type'");
            if ($result && $row = $result->fetch_assoc()) {
                if (strpos($row['Type'], 'СМР') === false) {
                    $conn->query("ALTER TABLE project_milestones MODIFY COLUMN work_type ENUM('ШМР','ПНР','СМР') NOT NULL COMMENT 'Тип работ'");
                    error_log("ProjectHandler: migrated project_milestones.work_type ENUM to include СМР");
                }
            }
            // Add 'СМР' to work_type ENUM in milestone_progress_estimates if missing
            $result = $conn->query("SHOW COLUMNS FROM milestone_progress_estimates LIKE 'work_type'");
            if ($result && $row = $result->fetch_assoc()) {
                if (strpos($row['Type'], 'СМР') === false) {
                    $conn->query("ALTER TABLE milestone_progress_estimates MODIFY COLUMN work_type ENUM('ШМР','ПНР','СМР') NOT NULL COMMENT 'Тип работ'");
                    error_log("ProjectHandler: migrated milestone_progress_estimates.work_type ENUM to include СМР");
                }
            }
        } catch (\Exception $e) {
            error_log("ProjectHandler::ensureSchemaMigrations error: " . $e->getMessage());
        }
    }

    public function canHandle(string $callbackData): bool
    {
        error_log("ProjectHandler: canHandle called with callbackData: '{$callbackData}'");
        
        // EXCLUDE proj_mod_* callbacks - they should be handled by ProjectModerationHandler
        if (str_starts_with($callbackData, 'proj_mod_')) {
            error_log("ProjectHandler: rejecting proj_mod_ callback - should go to ProjectModerationHandler");
            return false;
        }
        
        // Handle all project-related callbacks
        $startsWithCategoryProjects = str_starts_with($callbackData, 'category_Проекты');
        
        // Handle project list management callbacks
        $startsWithProjectList = str_starts_with($callbackData, 'proj_list_');
        $startsWithProjectControl = str_starts_with($callbackData, 'proj_ctrl_');
        $startsWithProjectReport = str_starts_with($callbackData, 'proj_report_');
        $startsWithProjectNotes = str_starts_with($callbackData, 'proj_notes_');
        $startsWithProjectCard = str_starts_with($callbackData, 'proj_card_');
        $startsWithProjectConstructor = str_starts_with($callbackData, 'proj_constructor_');
        $startsWithProjectNomenclature = str_starts_with($callbackData, 'proj_nomenclature_');
        $startsWithProjectPM = str_starts_with($callbackData, 'proj_pm_');
        $startsWithProjectCustomer = str_starts_with($callbackData, 'proj_customer_');
        $startsWithProjectBU = str_starts_with($callbackData, 'proj_bu_');
        $startsWithProject1C = str_starts_with($callbackData, 'proj_1c_');
        $startsWithProjectPNR = str_starts_with($callbackData, 'proj_pnr_');
        $startsWithProjectSMR = str_starts_with($callbackData, 'proj_smr_');
        $startsWithProjectCMR = str_starts_with($callbackData, 'proj_cmr_');
        $isExportKeyHistory = $callbackData === 'export_key_history' || str_starts_with($callbackData, 'export_history_page_');
        $isRetryExport = $callbackData === 'retry_export';
        $isCancelExport = $callbackData === 'category_Проекты' && isset($conversation->notes['awaiting_export_key_verify']) && $conversation->notes['awaiting_export_key_verify'];
        
        $canHandle = $startsWithCategoryProjects || $startsWithProjectList || $startsWithProjectControl || $startsWithProjectReport || $startsWithProjectNotes || $startsWithProjectCard || $startsWithProjectConstructor || $startsWithProjectNomenclature || $startsWithProjectPM || $startsWithProjectCustomer || $startsWithProjectBU || $startsWithProject1C || $startsWithProjectPNR || $startsWithProjectSMR || $startsWithProjectCMR || $isExportKeyHistory || $isRetryExport || $isCancelExport;
        
        error_log("ProjectHandler: canHandle result for '{$callbackData}': " . ($canHandle ? 'true' : 'false'));
        error_log("ProjectHandler: startsWithCategoryProjects: " . ($startsWithCategoryProjects ? 'true' : 'false'));
        error_log("ProjectHandler: startsWithProjectList: " . ($startsWithProjectList ? 'true' : 'false'));
        error_log("ProjectHandler: startsWithProjectControl: " . ($startsWithProjectControl ? 'true' : 'false'));
        error_log("ProjectHandler: startsWithProjectReport: " . ($startsWithProjectReport ? 'true' : 'false'));
        error_log("ProjectHandler: startsWithProjectNotes: " . ($startsWithProjectNotes ? 'true' : 'false'));
        
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $callbackData = $callbackQuery->getData();
        
        error_log("ProjectHandler: handle called with category: '{$category}' for user: {$userId}");
        
        // Check user access level
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        // Basic access check - all registered users (level 1+) can access projects
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_USER) {
            $text = '⚠️ <b>Доступ ограничен</b>

У вас уровень доступа "Гость". Обратитесь к администратору для получения доступа к проектам.';
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Handle different project-related callbacks
        if ($category === 'Проекты') {
            // Check if we're cancelling export key verification
            if (isset($conversation->notes['awaiting_export_key_verify']) && $conversation->notes['awaiting_export_key_verify']) {
                // Clear export key verification state
                $conversation->notes['awaiting_export_key_verify'] = false;
                unset($conversation->notes['export_resume_callback']);
                unset($conversation->notes['export_pending_type']);
                $conversation->update();
                
                error_log("ProjectHandler: Cancelled export key verification for user {$userId}");
            }
            
            return $this->showProjectsMainMenu($chatId, $userId);
        }
        
        // Main project activities
        if ($category === 'Проекты_МоиПроекты') {
            return $this->showMyProjectsMenu($chatId, $userId);
        }
        
        if ($category === 'Проекты_МояАктивность') {
            return $this->showMyActivityMenu($chatId, $userId);
        }
        
        if ($category === 'Проекты_КарточкаПроекта') {
            return $this->showProjectCardsMenu($chatId, $userId);
        }
        
        if ($category === 'Проекты_ОтчётПоПроекту') {
            // Check access level for project reports (supervisor+)
            if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $text = '⚠️ <b>Доступ ограничен</b>

Функция "Отчёт по проекту" доступна только для Супервайзеров, Администраторов и Суперадминистраторов.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            return $this->showProjectReportMenu($chatId, $userId);
        }
        
        // My Projects submenu
        if ($category === 'Проекты_ПроектыДляОтчёта') {
            return $this->showReportProjectsMenu($chatId, $userId);
        }
        
        if ($category === 'Проекты_ПроектыНаКонтроле') {
            // Check access level for control projects (supervisor+)
            if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $text = '⚠️ <b>Доступ ограничен</b>

Функция "Проекты на контроле" доступна только для Супервайзеров, Администраторов и Суперадминистраторов.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            return $this->showControlProjectsMenu($chatId, $userId);
        }
        
        // Handle project list management callbacks (without category_ prefix)
        if (str_starts_with($callbackData, 'proj_list_')) {
            return $this->handleReportProjectsCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle project report callbacks
        if (str_starts_with($callbackData, 'proj_report_')) {
            return $this->handleProjectReportCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle project notes export callbacks
        if (str_starts_with($callbackData, 'proj_notes_')) {
            return $this->handleProjectReportCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle project card callbacks
        if (str_starts_with($callbackData, 'proj_card_')) {
            if ($callbackData === 'proj_card_menu') {
                return $this->showProjectCardsMenu($chatId, $userId);
            }
            if ($callbackData === 'proj_card_list') {
                return $this->showProjectCardsList($chatId, $userId, 1);
            }
            
            // Handle pagination for project cards list
            if (str_starts_with($callbackData, 'proj_card_list_page_')) {
                $page = (int)str_replace('proj_card_list_page_', '', $callbackData);
                return $this->showProjectCardsList($chatId, $userId, $page);
            }
            if ($callbackData === 'proj_card_add') {
                return $this->addNewProject($chatId, $userId, $conversation);
            }
            if ($callbackData === 'proj_card_cancel') {
                return $this->cancelProjectCreation($chatId, $userId, $conversation);
            }
            // Handle project card view with pagination: proj_card_view_{code} or proj_card_view_{code}_{page}
            if (str_starts_with($callbackData, 'proj_card_view_')) {
                $parts = explode('_', str_replace('proj_card_view_', '', $callbackData), 2);
                $projectCode = $parts[0];
                $page = $parts[1] ?? 'info'; // Default to 'info' page
                return $this->viewProjectCard($chatId, $userId, $projectCode, $page);
            }
            if (str_starts_with($callbackData, 'proj_card_delete_confirm_')) {
                $projectCode = str_replace('proj_card_delete_confirm_', '', $callbackData);
                return $this->confirmDeleteProject($chatId, $userId, $projectCode);
            }
            if (str_starts_with($callbackData, 'proj_card_delete_yes_')) {
                $projectCode = str_replace('proj_card_delete_yes_', '', $callbackData);
                return $this->deleteProject($chatId, $userId, $projectCode);
            }
            if (str_starts_with($callbackData, 'proj_card_delete_no_')) {
                $projectCode = str_replace('proj_card_delete_no_', '', $callbackData);
                return $this->viewProjectCard($chatId, $userId, $projectCode);
            }
            if (str_starts_with($callbackData, 'proj_card_edit_')) {
                $projectCode = str_replace('proj_card_edit_', '', $callbackData);
                return $this->showProjectConstructor($chatId, $userId, $projectCode);
            }
        }
        
        // Handle project constructor callbacks
        if (str_starts_with($callbackData, 'proj_constructor_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle project nomenclature callbacks
        if (str_starts_with($callbackData, 'proj_nomenclature_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle project manager callbacks
        if (str_starts_with($callbackData, 'proj_pm_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle customer callbacks
        if (str_starts_with($callbackData, 'proj_customer_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle business unit callbacks
        if (str_starts_with($callbackData, 'proj_bu_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle 1C name callbacks
        if (str_starts_with($callbackData, 'proj_1c_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle PNR milestones callbacks
        if (str_starts_with($callbackData, 'proj_pnr_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle SMR milestones callbacks
        if (str_starts_with($callbackData, 'proj_smr_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle CMR milestones callbacks
        if (str_starts_with($callbackData, 'proj_cmr_')) {
            return $this->handleConstructorCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle My Activity submenu callbacks
        if ($category === 'Проекты_ПоследниеЗаписи') {
            return $this->showLastEntries($chatId, $userId);
        }
        
        if ($category === 'Проекты_ДетальнаяСтатистика') {
            return $this->showDetailedStatistics($chatId, $userId);
        }
        
        if ($category === 'Проекты_МоиДостижения') {
            return $this->showAchievements($chatId, $userId);
        }
        
        if ($category === 'Проекты_ЭкспортАктивности') {
            return $this->exportUserActivity($chatId, $userId);
        }
        
        if (str_starts_with($callbackData, 'proj_ctrl_')) {
            // Check access level for control projects callbacks
            if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
                $text = '⚠️ <b>Доступ ограничен</b>

У вас нет доступа к управлению проектами на контроле.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            return $this->handleControlProjectsCallback($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle export history callback
        if ($callbackData === 'export_key_history' || str_starts_with($callbackData, 'export_history_page_')) {
            // Extract page number
            $page = 1;
            if (str_starts_with($callbackData, 'export_history_page_')) {
                $page = (int)str_replace('export_history_page_', '', $callbackData);
            }
            
            // Create ExportKeyHandler to show history
            $exportSecurityService = new \Longman\TelegramBot\Commands\SystemCommands\Services\ExportSecurityService($this->databaseService);
            $exportKeyHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ExportKeyHandler(
                $this->messageService,
                $this->databaseService,
                $exportSecurityService
            );
            return $exportKeyHandler->showExportHistory($chatId, $userId, $page);
        }
        
        // Handle retry export (wrong export key)
        if ($callbackData === 'retry_export') {
            // Just re-prompt for export key
            $conversation->notes['awaiting_export_key_verify'] = true;
            $conversation->update();
            
            $text = "🔐 <b>Введите кодовое слово</b>\n\nДля безопасности введите ваше кодовое слово:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Default case
        $text = 'Неизвестная команда управления проектами';
        return $this->messageService->sendMessage($chatId, $text);
    }

    public function getPriority(): int
    {
        return 30;
    }

    /**
     * Show main projects menu
     */
    private function showProjectsMainMenu(string $chatId, int $userId): ServerResponse
    {
        // Get user access level
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        $keyboard = [
            [
                ['text' => '📂 Мои проекты', 'callback_data' => 'category_Проекты_МоиПроекты']
            ],
            [
                ['text' => '📊 Моя активность', 'callback_data' => 'category_Проекты_МояАктивность']
            ]
        ];
        
        $keyboard[] = [
            ['text' => '⬅️ Вернуться в главное меню', 'callback_data' => 'category_Главное']
        ];
        
        $text = '📁 <b>Проекты</b>

Выберите действие:';
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show "My Projects" submenu
     */
    private function showMyProjectsMenu(string $chatId, int $userId): ServerResponse
    {
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        $keyboard = [
            [
                ['text' => '🆕 Добавить проект', 'callback_data' => 'proj_mod_add_new']
            ],
            [
                ['text' => '📋 Проекты для отчёта', 'callback_data' => 'category_Проекты_ПроектыДляОтчёта']
            ]
        ];
        
        $keyboard[] = [
            ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
        ];
        
        $text = '📂 <b>Мои проекты</b>

Выберите действие:';
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show "My Activity" dashboard with statistics and achievements foundation
     */
    private function showMyActivityMenu(string $chatId, int $userId): ServerResponse
    {
        // Simple version to ensure it works - we'll add complex stats later
        try {
            // Get basic user data
            $userData = $this->databaseService->getUserData($userId);
            $userName = $userData['fio'] ?? 'Пользователь';
            
            // Get simple entry count
            $entries = $this->databaseService->getUserLastTabelEntries($userId, 100);
            $totalEntries = count($entries);
            
            // Count recent entries (simple way)
            $recentEntries = 0;
            $milestones = 0;
            
            $thirtyDaysAgo = strtotime('-30 days');
            foreach ($entries as $entry) {
                $entryTime = strtotime($entry['dt']);
                if ($entryTime >= $thirtyDaysAgo) {
                    $recentEntries++;
                }
                
                // Simple milestone detection
                if (stripos($entry['message'], 'веха') !== false || 
                    stripos($entry['message'], 'этап') !== false || 
                    stripos($entry['message'], 'завершен') !== false) {
                    $milestones++;
                }
            }
            
            // Create simple, engaging text
            $text = "📊 <b>Моя активность</b>\n\n";
            
            $text .= "👋 Привет, <b>{$userName}!</b>\n\n";
            
            $text .= "📈 <b>Ваша статистика:</b>\n";
            $text .= "• 📝 Всего записей в табель: <b>{$totalEntries}</b>\n";
            $text .= "• 🕒 За последние 30 дней: <b>{$recentEntries}</b>\n";
            
            if ($milestones > 0) {
                $text .= "• 🎯 Подтверждено вех: <b>{$milestones}</b>\n";
            }
            
            $text .= "\n🏆 <b>Система достижений:</b>\n";
            
            // Simple achievement system
            if ($totalEntries >= 50) {
                $text .= "• 🥇 Мастер табеля! (50+ записей)\n";
            } elseif ($totalEntries >= 20) {
                $text .= "• 🥈 Опытный сотрудник (20+ записей)\n";
            } elseif ($totalEntries >= 10) {
                $text .= "• 🥉 Активный пользователь (10+ записей)\n";
            } else {
                $text .= "• 🌟 Начинающий (менее 10 записей)\n";
            }
            
            if ($milestones >= 5) {
                $text .= "• 🎆 Покоритель вех! (5+ вех)\n";
            } elseif ($milestones >= 1) {
                $text .= "• ⭐ Первые вехи достигнуты!\n";
            }
            
            $text .= "\n🚀 <b>Скоро будет доступно:</b>\n";
            $text .= "• 🏅 Эмодзи-значки рядом с именем\n";
            $text .= "• 🏆 Соревнования между сотрудниками\n";
            $text .= "• 📊 Детальная аналитика и графики\n";
            $text .= "• 🎁 Награды за достижения";
            
            // Create simple keyboard
            $keyboard = [
                [
                    ['text' => '📋 Последние записи', 'callback_data' => 'category_Проекты_ПоследниеЗаписи']
                ],
                [
                    ['text' => '🏆 Мои достижения', 'callback_data' => 'category_Проекты_МоиДостижения']
                ],
                [
                    ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            // Fallback in case of any error
            error_log("Error in showMyActivityMenu: " . $e->getMessage());
            
            $text = "📊 <b>Моя активность</b>\n\n";
            $text .= "🎉 <b>Добро пожаловать в новый раздел!</b>\n\n";
            $text .= "Здесь будет отображаться:\n";
            $text .= "• 📈 Ваша статистика активности\n";
            $text .= "• 🏆 Достижения и успехи\n";
            $text .= "• 📊 Анализ продуктивности\n";
            $text .= "• 🎯 Система рангов и наград\n\n";
            $text .= "⚙️ <i>Система загружается...</i>";
            
            $keyboard = [
                [
                    ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
    }

    /**
     * Show manage nomenclature groups menu
     */
    private function showManageNomenclatureGroups(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get all nomenclature groups
            $result = $conn->query("SELECT id, name FROM project_nomenclature_groups ORDER BY name");
            $groups = [];
            while ($row = $result->fetch_assoc()) {
                $groups[] = $row;
            }
            
            if (empty($groups)) {
                $text = '🗑️ <b>Управление группами</b>

Номенклатурных групп пока нет.';
                $keyboard = [
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_name_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            $text = '🗑️ <b>Управление группами</b>

Нажмите на группу для удаления:';
            
            $keyboard = [];
            foreach ($groups as $group) {
                $keyboard[] = [
                    ['text' => '🗑️ ' . $group['name'], 'callback_data' => 'proj_nomenclature_delete_' . $group['id'] . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_name_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showManageNomenclatureGroups error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm deletion of nomenclature group
     */
    private function confirmDeleteNomenclatureGroup(string $chatId, int $userId, string $projectCode, int $groupId): ServerResponse
    {
        error_log("confirmDeleteNomenclatureGroup called with: chatId={$chatId}, userId={$userId}, projectCode={$projectCode}, groupId={$groupId}");
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get group name
            $stmt = $conn->prepare("SELECT name FROM project_nomenclature_groups WHERE id = ?");
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $result = $stmt->get_result();
            $group = $result->fetch_assoc();
            
            error_log("Group query result: " . ($group ? "Found group '{$group['name']}'" : "Group NOT FOUND"));
            
            if (!$group) {
                error_log("Returning 'Group not found' message");
                return $this->messageService->sendMessage($chatId, '❌ Группа не найдена');
            }
            
            // Check if group is used by any projects
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM project WHERE name = ?");
            $stmt->bind_param('s', $group['name']);
            $stmt->execute();
            $result = $stmt->get_result();
            $usage = $result->fetch_assoc();
            $usageCount = $usage['count'] ?? 0;
            
            $text = '⚠️ <b>Подтвердите удаление</b>

Группа: <b>' . $group['name'] . '</b>

';
            
            if ($usageCount > 0) {
                $text .= '⚠️ Эта группа используется в <b>' . $usageCount . '</b> проект(ах).
При удалении названия проектов останутся неизменными.

';
            }
            
            $text .= 'Вы уверены?';
            
            $keyboard = [
                [
                    ['text' => '✅ Да, удалить', 'callback_data' => 'proj_nomenclature_delete_yes_' . $groupId . '_' . $projectCode]
                ],
                [
                    ['text' => '❌ Отмена', 'callback_data' => 'proj_nomenclature_delete_no_' . $groupId . '_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::confirmDeleteNomenclatureGroup error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete nomenclature group
     */
    private function deleteNomenclatureGroup(string $chatId, int $userId, string $projectCode, int $groupId): ServerResponse
    {
        error_log("deleteNomenclatureGroup called with: chatId={$chatId}, userId={$userId}, projectCode={$projectCode}, groupId={$groupId}");
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get group name before deletion
            $stmt = $conn->prepare("SELECT name FROM project_nomenclature_groups WHERE id = ?");
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $result = $stmt->get_result();
            $group = $result->fetch_assoc();
            
            error_log("Group query result before delete: " . ($group ? "Found group '{$group['name']}'" : "Group NOT FOUND"));
            
            if (!$group) {
                error_log("Returning 'Group not found' message from delete");
                return $this->messageService->sendMessage($chatId, '❌ Группа не найдена');
            }
            
            $groupName = $group['name'];
            
            // Delete the group
            $stmt = $conn->prepare("DELETE FROM project_nomenclature_groups WHERE id = ?");
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            
            $text = '✅ <b>Группа удалена</b>

Группа "' . $groupName . '" успешно удалена.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К управлению группами', 'callback_data' => 'proj_nomenclature_manage_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К выбору группы', 'callback_data' => 'proj_constructor_name_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::deleteNomenclatureGroup error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Show project managers selection menu
     */
    private function showProjectManagersMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Create table if not exists
            $conn->query("
                CREATE TABLE IF NOT EXISTS project_managers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    created_by BIGINT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Get all project managers
            $result = $conn->query("SELECT id, name FROM project_managers ORDER BY name");
            $managers = [];
            while ($row = $result->fetch_assoc()) {
                $managers[] = $row;
            }
            
            $text = '👤 <b>Руководитель проекта</b>

';
            $text .= 'Выберите руководителя проекта:';
            
            $keyboard = [];
            foreach ($managers as $manager) {
                $keyboard[] = [
                    ['text' => $manager['name'], 'callback_data' => 'proj_pm_select_' . $manager['id'] . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '➕ Добавить РП', 'callback_data' => 'proj_pm_add_' . $projectCode]
            ];
            $keyboard[] = [
                ['text' => '🗑️ Управление РП', 'callback_data' => 'proj_pm_manage_' . $projectCode]
            ];
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_pm_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showProjectManagersMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Select project manager for project
     */
    private function selectProjectManager(string $chatId, int $userId, string $projectCode, int $pmId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get manager name
            $stmt = $conn->prepare("SELECT name FROM project_managers WHERE id = ?");
            $stmt->bind_param('i', $pmId);
            $stmt->execute();
            $result = $stmt->get_result();
            $manager = $result->fetch_assoc();
            
            if (!$manager) {
                return $this->messageService->sendMessage($chatId, '❌ Руководитель не найден');
            }
            
            // Update project card with PM
            // First check if project_cards record exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardExists = $result->fetch_assoc();
            
            if ($cardExists) {
                // Update existing card
                $stmt = $conn->prepare("UPDATE project_cards SET project_manager_id = ? WHERE project_code = ?");
                $stmt->bind_param('is', $pmId, $projectCode);
            } else {
                // Create new card - need to get project name first
                $stmt = $conn->prepare("SELECT name FROM project WHERE code = ?");
                $stmt->bind_param('s', $projectCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $project = $result->fetch_assoc();
                $projectName = $project['name'] ?? $projectCode; // fallback to code if name not found
                
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, project_name, project_manager_id, created_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssii', $projectCode, $projectName, $pmId, $userId);
            }
            $stmt->execute();
            
            $text = '✅ <b>Руководитель проекта назначен</b>

';
            $text .= 'Проект: ' . $projectCode . '
';
            $text .= 'РП: ' . $manager['name'];
            
            $keyboard = [
                [
                    ['text' => '⬅️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::selectProjectManager error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to add new project manager
     */
    private function promptAddProjectManager(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_project_manager_name';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '➕ <b>Добавить руководителя проекта</b>

';
        $text .= 'Введите ФИО руководителя:';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_pm_back_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle project manager name input
     */
    public function handleProjectManagerNameInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
            }
            
            $managerName = trim($text);
            
            if (empty($managerName)) {
                $errorText = '❌ ФИО не может быть пустым.

Введите ФИО руководителя:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_pm_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Check if manager already exists
            $stmt = $conn->prepare("SELECT id FROM project_managers WHERE name = ?");
            $stmt->bind_param('s', $managerName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->fetch_assoc()) {
                $errorText = '❌ РП с таким ФИО уже существует.

Введите другое ФИО:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_pm_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Insert new manager
            $stmt = $conn->prepare("INSERT INTO project_managers (name, created_by) VALUES (?, ?)");
            $stmt->bind_param('si', $managerName, $userId);
            $stmt->execute();
            
            // Clear conversation
            $conversation->stop();
            
            $successText = '✅ <b>Руководитель проекта добавлен</b>

';
            $successText .= 'ФИО: ' . $managerName;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К выбору РП', 'callback_data' => 'proj_constructor_pm_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleProjectManagerNameInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка при добавлении РП: ' . $e->getMessage());
        }
    }
    
    /**
     * Show manage project managers menu
     */
    private function showManageProjectManagers(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get all project managers
            $result = $conn->query("SELECT id, name FROM project_managers ORDER BY name");
            $managers = [];
            while ($row = $result->fetch_assoc()) {
                $managers[] = $row;
            }
            
            if (empty($managers)) {
                $text = '📋 <b>Управление руководителями</b>

';
                $text .= 'Список пуст. Добавьте первого руководителя.';
                
                $keyboard = [
                    [
                        ['text' => '➕ Добавить РП', 'callback_data' => 'proj_pm_add_' . $projectCode]
                    ],
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_pm_' . $projectCode]
                    ]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            $text = '🗑️ <b>Управление руководителями</b>

';
            $text .= 'Выберите РП для удаления:';
            
            $keyboard = [];
            foreach ($managers as $manager) {
                $keyboard[] = [
                    ['text' => '🗑️ ' . $manager['name'], 'callback_data' => 'proj_pm_delete_' . $manager['id'] . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_pm_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showManageProjectManagers error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm deletion of project manager
     */
    private function confirmDeleteProjectManager(string $chatId, int $userId, string $projectCode, int $pmId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get manager name
            $stmt = $conn->prepare("SELECT name FROM project_managers WHERE id = ?");
            $stmt->bind_param('i', $pmId);
            $stmt->execute();
            $result = $stmt->get_result();
            $manager = $result->fetch_assoc();
            
            if (!$manager) {
                return $this->messageService->sendMessage($chatId, '❌ Руководитель не найден');
            }
            
            // Check if manager is used by any projects
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM project_cards WHERE project_manager_id = ?");
            $stmt->bind_param('i', $pmId);
            $stmt->execute();
            $result = $stmt->get_result();
            $usage = $result->fetch_assoc();
            $usageCount = $usage['count'] ?? 0;
            
            $text = '⚠️ <b>Подтвердите удаление</b>

РП: <b>' . $manager['name'] . '</b>

';
            
            if ($usageCount > 0) {
                $text .= '⚠️ Этот РП назначен на <b>' . $usageCount . '</b> проект(ов).
При удалении назначения будут сброшены.

';
            }
            
            $text .= 'Вы уверены?';
            
            $keyboard = [
                [
                    ['text' => '✅ Да, удалить', 'callback_data' => 'proj_pm_delete_yes_' . $pmId . '_' . $projectCode]
                ],
                [
                    ['text' => '❌ Отмена', 'callback_data' => 'proj_pm_delete_no_' . $pmId . '_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::confirmDeleteProjectManager error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete project manager
     */
    private function deleteProjectManager(string $chatId, int $userId, string $projectCode, int $pmId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get manager name before deletion
            $stmt = $conn->prepare("SELECT name FROM project_managers WHERE id = ?");
            $stmt->bind_param('i', $pmId);
            $stmt->execute();
            $result = $stmt->get_result();
            $manager = $result->fetch_assoc();
            
            if (!$manager) {
                return $this->messageService->sendMessage($chatId, '❌ Руководитель не найден');
            }
            
            $managerName = $manager['name'];
            
            // Clear PM from project cards
            $stmt = $conn->prepare("UPDATE project_cards SET project_manager_id = NULL WHERE project_manager_id = ?");
            $stmt->bind_param('i', $pmId);
            $stmt->execute();
            
            // Delete the manager
            $stmt = $conn->prepare("DELETE FROM project_managers WHERE id = ?");
            $stmt->bind_param('i', $pmId);
            $stmt->execute();
            
            $text = '✅ <b>РП удалён</b>

РП "' . $managerName . '" успешно удалён.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К управлению РП', 'callback_data' => 'proj_pm_manage_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К выбору РП', 'callback_data' => 'proj_constructor_pm_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::deleteProjectManager error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Show customers selection menu
     */
    private function showCustomersMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get all customers
            $result = $conn->query("SELECT id, name FROM customers ORDER BY name");
            $customers = [];
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
            
            $text = '🏢 <b>Заказчик</b>

';
            $text .= 'Выберите заказчика:';
            
            $keyboard = [];
            foreach ($customers as $customer) {
                $keyboard[] = [
                    ['text' => $customer['name'], 'callback_data' => 'proj_customer_select_' . $customer['id'] . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '➕ Добавить заказчика', 'callback_data' => 'proj_customer_add_' . $projectCode]
            ];
            $keyboard[] = [
                ['text' => '🗑️ Управление заказчиками', 'callback_data' => 'proj_customer_manage_' . $projectCode]
            ];
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_customer_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showCustomersMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Select customer for project
     */
    private function selectCustomer(string $chatId, int $userId, string $projectCode, int $customerId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get customer name
            $stmt = $conn->prepare("SELECT name FROM customers WHERE id = ?");
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();
            
            if (!$customer) {
                return $this->messageService->sendMessage($chatId, '❌ Заказчик не найден');
            }
            
            // Update project card with customer
            // First check if project_cards record exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardExists = $result->fetch_assoc();
            
            if ($cardExists) {
                // Update existing card
                $stmt = $conn->prepare("UPDATE project_cards SET customer_id = ? WHERE project_code = ?");
                $stmt->bind_param('is', $customerId, $projectCode);
            } else {
                // Create new card - need to get project name first
                $stmt = $conn->prepare("SELECT name FROM project WHERE code = ?");
                $stmt->bind_param('s', $projectCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $project = $result->fetch_assoc();
                $projectName = $project['name'] ?? $projectCode;
                
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, project_name, customer_id, created_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssii', $projectCode, $projectName, $customerId, $userId);
            }
            $stmt->execute();
            
            $text = '✅ <b>Заказчик назначен</b>

';
            $text .= 'Проект: ' . $projectCode . '
';
            $text .= 'Заказчик: ' . $customer['name'];
            
            $keyboard = [
                [
                    ['text' => '⬅️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::selectCustomer error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to add new customer
     */
    private function promptAddCustomer(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_customer_name';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '➕ <b>Добавить заказчика</b>

';
        $text .= 'Введите название заказчика:';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_customer_back_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle customer name input
     */
    public function handleCustomerNameInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
            }
            
            $customerName = trim($text);
            
            if (empty($customerName)) {
                $errorText = '❌ Название не может быть пустым.

Введите название заказчика:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_customer_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Check if customer already exists
            $stmt = $conn->prepare("SELECT id FROM customers WHERE name = ?");
            $stmt->bind_param('s', $customerName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->fetch_assoc()) {
                $errorText = '❌ Заказчик с таким названием уже существует.

Введите другое название:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_customer_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Insert new customer
            $stmt = $conn->prepare("INSERT INTO customers (name) VALUES (?)");
            $stmt->bind_param('s', $customerName);
            $stmt->execute();
            
            // Clear conversation
            $conversation->stop();
            
            $successText = '✅ <b>Заказчик добавлен</b>

';
            $successText .= 'Название: ' . $customerName;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К выбору заказчика', 'callback_data' => 'proj_constructor_customer_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleCustomerNameInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка при добавлении заказчика: ' . $e->getMessage());
        }
    }
    
    /**
     * Show manage customers menu
     */
    private function showManageCustomers(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get all customers
            $result = $conn->query("SELECT id, name FROM customers ORDER BY name");
            $customers = [];
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
            
            if (empty($customers)) {
                $text = '📋 <b>Управление заказчиками</b>

';
                $text .= 'Список пуст. Добавьте первого заказчика.';
                
                $keyboard = [
                    [
                        ['text' => '➕ Добавить заказчика', 'callback_data' => 'proj_customer_add_' . $projectCode]
                    ],
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_customer_' . $projectCode]
                    ]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            $text = '🗑️ <b>Управление заказчиками</b>

';
            $text .= 'Выберите заказчика для удаления:';
            
            $keyboard = [];
            foreach ($customers as $customer) {
                $keyboard[] = [
                    ['text' => '🗑️ ' . $customer['name'], 'callback_data' => 'proj_customer_delete_' . $customer['id'] . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_customer_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showManageCustomers error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm deletion of customer
     */
    private function confirmDeleteCustomer(string $chatId, int $userId, string $projectCode, int $customerId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get customer name
            $stmt = $conn->prepare("SELECT name FROM customers WHERE id = ?");
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();
            
            if (!$customer) {
                return $this->messageService->sendMessage($chatId, '❌ Заказчик не найден');
            }
            
            // Check if customer is used by any projects
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM project_cards WHERE customer_id = ?");
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $usage = $result->fetch_assoc();
            $usageCount = $usage['count'] ?? 0;
            
            $text = '⚠️ <b>Подтвердите удаление</b>

Заказчик: <b>' . $customer['name'] . '</b>

';
            
            if ($usageCount > 0) {
                $text .= '⚠️ Этот заказчик используется в <b>' . $usageCount . '</b> проект(ах).
При удалении назначения будут сброшены.

';
            }
            
            $text .= 'Вы уверены?';
            
            $keyboard = [
                [
                    ['text' => '✅ Да, удалить', 'callback_data' => 'proj_customer_delete_yes_' . $customerId . '_' . $projectCode]
                ],
                [
                    ['text' => '❌ Отмена', 'callback_data' => 'proj_customer_delete_no_' . $customerId . '_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::confirmDeleteCustomer error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete customer
     */
    private function deleteCustomer(string $chatId, int $userId, string $projectCode, int $customerId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get customer name before deletion
            $stmt = $conn->prepare("SELECT name FROM customers WHERE id = ?");
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $customer = $result->fetch_assoc();
            
            if (!$customer) {
                return $this->messageService->sendMessage($chatId, '❌ Заказчик не найден');
            }
            
            $customerName = $customer['name'];
            
            // Clear customer from project cards
            $stmt = $conn->prepare("UPDATE project_cards SET customer_id = NULL WHERE customer_id = ?");
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            
            // Delete the customer
            $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->bind_param('i', $customerId);
            $stmt->execute();
            
            $text = '✅ <b>Заказчик удалён</b>

Заказчик "' . $customerName . '" успешно удалён.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К управлению заказчиками', 'callback_data' => 'proj_customer_manage_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К выбору заказчика', 'callback_data' => 'proj_constructor_customer_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::deleteCustomer error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Show business units selection menu
     */
    private function showBusinessUnitsMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get all business units
            $result = $conn->query("SELECT id, name FROM business_units ORDER BY name");
            $businessUnits = [];
            while ($row = $result->fetch_assoc()) {
                $businessUnits[] = $row;
            }
            
            $text = '🏭 <b>Бизнес-направление</b>

';
            $text .= 'Выберите бизнес-направление:';
            
            $keyboard = [];
            foreach ($businessUnits as $bu) {
                $keyboard[] = [
                    ['text' => $bu['name'], 'callback_data' => 'proj_bu_select_' . $bu['id'] . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '➕ Добавить БН', 'callback_data' => 'proj_bu_add_' . $projectCode]
            ];
            $keyboard[] = [
                ['text' => '🗑️ Управление БН', 'callback_data' => 'proj_bu_manage_' . $projectCode]
            ];
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_bu_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showBusinessUnitsMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Select business unit for project
     */
    private function selectBusinessUnit(string $chatId, int $userId, string $projectCode, int $buId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get business unit name
            $stmt = $conn->prepare("SELECT name FROM business_units WHERE id = ?");
            $stmt->bind_param('i', $buId);
            $stmt->execute();
            $result = $stmt->get_result();
            $bu = $result->fetch_assoc();
            
            if (!$bu) {
                return $this->messageService->sendMessage($chatId, '❌ БН не найдено');
            }
            
            // Update project card with business unit
            // First check if project_cards record exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardExists = $result->fetch_assoc();
            
            if ($cardExists) {
                // Update existing card
                $stmt = $conn->prepare("UPDATE project_cards SET business_unit_id = ? WHERE project_code = ?");
                $stmt->bind_param('is', $buId, $projectCode);
            } else {
                // Create new card - need to get project name first
                $stmt = $conn->prepare("SELECT name FROM project WHERE code = ?");
                $stmt->bind_param('s', $projectCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $project = $result->fetch_assoc();
                $projectName = $project['name'] ?? $projectCode;
                
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, project_name, business_unit_id, created_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssii', $projectCode, $projectName, $buId, $userId);
            }
            $stmt->execute();
            
            $text = '✅ <b>БН назначено</b>

';
            $text .= 'Проект: ' . $projectCode . '
';
            $text .= 'БН: ' . $bu['name'];
            
            $keyboard = [
                [
                    ['text' => '⬅️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::selectBusinessUnit error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to add new business unit
     */
    private function promptAddBusinessUnit(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_business_unit_name';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '➕ <b>Добавить бизнес-направление</b>

';
        $text .= 'Введите название БН:';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_bu_back_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle business unit name input
     */
    public function handleBusinessUnitNameInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
            }
            
            $buName = trim($text);
            
            if (empty($buName)) {
                $errorText = '❌ Название не может быть пустым.

Введите название БН:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_bu_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Check if business unit already exists
            $stmt = $conn->prepare("SELECT id FROM business_units WHERE name = ?");
            $stmt->bind_param('s', $buName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->fetch_assoc()) {
                $errorText = '❌ БН с таким названием уже существует.

Введите другое название:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_bu_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Insert new business unit
            $stmt = $conn->prepare("INSERT INTO business_units (name) VALUES (?)");
            $stmt->bind_param('s', $buName);
            $stmt->execute();
            
            // Clear conversation
            $conversation->stop();
            
            $successText = '✅ <b>БН добавлено</b>

';
            $successText .= 'Название: ' . $buName;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К выбору БН', 'callback_data' => 'proj_constructor_bu_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleBusinessUnitNameInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка при добавлении БН: ' . $e->getMessage());
        }
    }
    
    /**
     * Show manage business units menu
     */
    private function showManageBusinessUnits(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get all business units
            $result = $conn->query("SELECT id, name FROM business_units ORDER BY name");
            $businessUnits = [];
            while ($row = $result->fetch_assoc()) {
                $businessUnits[] = $row;
            }
            
            if (empty($businessUnits)) {
                $text = '📋 <b>Управление БН</b>

';
                $text .= 'Список пуст. Добавьте первое БН.';
                
                $keyboard = [
                    [
                        ['text' => '➕ Добавить БН', 'callback_data' => 'proj_bu_add_' . $projectCode]
                    ],
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_bu_' . $projectCode]
                    ]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            $text = '🗑️ <b>Управление БН</b>

';
            $text .= 'Выберите БН для удаления:';
            
            $keyboard = [];
            foreach ($businessUnits as $bu) {
                $keyboard[] = [
                    ['text' => '🗑️ ' . $bu['name'], 'callback_data' => 'proj_bu_delete_' . $bu['id'] . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_bu_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showManageBusinessUnits error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm deletion of business unit
     */
    private function confirmDeleteBusinessUnit(string $chatId, int $userId, string $projectCode, int $buId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get business unit name
            $stmt = $conn->prepare("SELECT name FROM business_units WHERE id = ?");
            $stmt->bind_param('i', $buId);
            $stmt->execute();
            $result = $stmt->get_result();
            $bu = $result->fetch_assoc();
            
            if (!$bu) {
                return $this->messageService->sendMessage($chatId, '❌ БН не найдено');
            }
            
            // Check if business unit is used by any projects
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM project_cards WHERE business_unit_id = ?");
            $stmt->bind_param('i', $buId);
            $stmt->execute();
            $result = $stmt->get_result();
            $usage = $result->fetch_assoc();
            $usageCount = $usage['count'] ?? 0;
            
            $text = '⚠️ <b>Подтвердите удаление</b>

БН: <b>' . $bu['name'] . '</b>

';
            
            if ($usageCount > 0) {
                $text .= '⚠️ Это БН используется в <b>' . $usageCount . '</b> проект(ах).
При удалении назначения будут сброшены.

';
            }
            
            $text .= 'Вы уверены?';
            
            $keyboard = [
                [
                    ['text' => '✅ Да, удалить', 'callback_data' => 'proj_bu_delete_yes_' . $buId . '_' . $projectCode]
                ],
                [
                    ['text' => '❌ Отмена', 'callback_data' => 'proj_bu_delete_no_' . $buId . '_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::confirmDeleteBusinessUnit error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete business unit
     */
    private function deleteBusinessUnit(string $chatId, int $userId, string $projectCode, int $buId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get business unit name before deletion
            $stmt = $conn->prepare("SELECT name FROM business_units WHERE id = ?");
            $stmt->bind_param('i', $buId);
            $stmt->execute();
            $result = $stmt->get_result();
            $bu = $result->fetch_assoc();
            
            if (!$bu) {
                return $this->messageService->sendMessage($chatId, '❌ БН не найдено');
            }
            
            $buName = $bu['name'];
            
            // Clear business unit from project cards
            $stmt = $conn->prepare("UPDATE project_cards SET business_unit_id = NULL WHERE business_unit_id = ?");
            $stmt->bind_param('i', $buId);
            $stmt->execute();
            
            // Delete the business unit
            $stmt = $conn->prepare("DELETE FROM business_units WHERE id = ?");
            $stmt->bind_param('i', $buId);
            $stmt->execute();
            
            $text = '✅ <b>БН удалёно</b>

БН "' . $buName . '" успешно удалёно.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К управлению БН', 'callback_data' => 'proj_bu_manage_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К выбору БН', 'callback_data' => 'proj_constructor_bu_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::deleteBusinessUnit error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Show 1C name management menu
     */
    private function show1CNameMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get current 1C name from project_cards
            $stmt = $conn->prepare("SELECT name_1c FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $current1CName = ($cardData && !empty($cardData['name_1c'])) ? $cardData['name_1c'] : null;
            
            $text = '💼 <b>Наименование 1С</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            
            if ($current1CName) {
                $text .= '📄 <b>Текущее наименование:</b>
' . $current1CName . '

';
                $text .= 'Вы можете изменить или очистить наименование.';
            } else {
                $text .= 'ℹ️ Наименование не установлено.

';
                $text .= 'Укажите наименование проекта для импорта в 1С.';
            }
            
            $keyboard = [];
            
            if ($current1CName) {
                $keyboard[] = [
                    ['text' => '✏️ Изменить', 'callback_data' => 'proj_1c_set_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_1c_clear_' . $projectCode]
                ];
            } else {
                $keyboard[] = [
                    ['text' => '➕ Установить', 'callback_data' => 'proj_1c_set_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_1c_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::show1CNameMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to enter 1C name
     */
    private function prompt1CName(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_1c_name';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '✏️ <b>Наименование 1С</b>

';
        $text .= 'Введите наименование проекта, как оно указано в 1С:';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_1c_back_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle 1C name input
     */
    public function handle1CNameInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
            }
            
            $name1C = trim($text);
            
            if (empty($name1C)) {
                $errorText = '❌ Наименование не может быть пустым.

Введите наименование 1С:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_1c_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Update or create project_cards record
            // First check if project_cards record exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardExists = $result->fetch_assoc();
            
            if ($cardExists) {
                // Update existing card
                $stmt = $conn->prepare("UPDATE project_cards SET name_1c = ? WHERE project_code = ?");
                $stmt->bind_param('ss', $name1C, $projectCode);
            } else {
                // Create new card - need to get project name first
                $stmt = $conn->prepare("SELECT name FROM project WHERE code = ?");
                $stmt->bind_param('s', $projectCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $project = $result->fetch_assoc();
                $projectName = $project['name'] ?? $projectCode;
                
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, project_name, name_1c, created_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('sssi', $projectCode, $projectName, $name1C, $userId);
            }
            $stmt->execute();
            
            // Clear conversation
            $conversation->stop();
            
            $successText = '✅ <b>Наименование 1С установлено</b>

';
            $successText .= 'Проект: ' . $projectCode . '
';
            $successText .= 'Наименование 1С: ' . $name1C;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handle1CNameInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка при установке наименования: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear 1C name
     */
    private function clear1CName(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Clear 1C name from project_cards
            $stmt = $conn->prepare("UPDATE project_cards SET name_1c = NULL WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Наименование 1С очищено</b>

';
            $text .= 'Проект: ' . $projectCode;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clear1CName error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Get standard PNR milestone names
     */
    private function getPNRMilestoneNames(): array
    {
        return [
            'Электрооборудование',
            'Пожарная сигнализация',
            'Охранная сигнализация',
            'Автоматизация',
            'Вентиляция и отопление',
            'Технологическое оборудование',
            'Метрология',
            'ИИ на рабочей среде',
            'Комплексное опробование'
        ];
    }
    
    /**
     * Ensure project_cards record exists for project_code
     * Required before INSERT into project_milestones due to FK constraint
     */
    private function ensureProjectCardExists(string $projectCode, int $createdBy): bool
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Check if already exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ? LIMIT 1");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->fetch_assoc()) {
                return true; // Already exists
            }
            
            // Get project name from projects table
            $stmt = $conn->prepare("SELECT name FROM project WHERE code = ? LIMIT 1");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $project = $result->fetch_assoc();
            
            $projectName = $project['name'] ?? $projectCode;
            
            // Create minimal record
            $stmt = $conn->prepare("
                INSERT INTO project_cards (project_code, project_name, created_by)
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param('ssi', $projectCode, $projectName, $createdBy);
            $stmt->execute();
            
            error_log("ProjectHandler: Auto-created project_cards record for {$projectCode}");
            return true;
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::ensureProjectCardExists error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Show PNR milestones menu with visual indicators
     */
    private function showPNRMilestonesMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get project info
            $stmt = $conn->prepare("SELECT code, name FROM project WHERE code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $project = $result->fetch_assoc();
            
            if (!$project) {
                return $this->messageService->sendMessage($chatId, '❌ Проект не найден');
            }
            
            $milestoneNames = $this->getPNRMilestoneNames();
            
            // Get existing milestones from database
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ПНР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $existingMilestones = [];
            while ($row = $result->fetch_assoc()) {
                $existingMilestones[$row['milestone_name']] = $row;
            }
            
            $text = '⚙️ <b>ПНР: Плановые трудозатраты</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Настройте плановые трудозатраты для каждой вехи:

';
            
            $keyboard = [];
            foreach ($milestoneNames as $index => $milestoneName) {
                $milestone = $existingMilestones[$milestoneName] ?? null;
                
                // Determine icon and text based on status
                if ($milestone && $milestone['is_not_applicable']) {
                    $icon = '❌';
                    $buttonText = $icon . ' ' . $milestoneName . ' (не предусмотрено)';
                } elseif ($milestone && $milestone['planned_hours'] !== null) {
                    $icon = '📊';
                    $unit = ($milestone['unit'] === 'hours') ? 'ч.' : 'дн.';
                    $buttonText = $icon . ' ' . $milestoneName . ' (' . $milestone['planned_hours'] . ' ' . $unit . ')';
                } else {
                    $icon = '❓';
                    $buttonText = $icon . ' ' . $milestoneName;
                }
                
                $keyboard[] = [
                    ['text' => $buttonText, 'callback_data' => 'proj_pnr_milestone_' . $index . '_' . $projectCode]
                ];
            }
            
            // Get additional work data from project_cards
            $stmt = $conn->prepare("
                SELECT pnr_mobilization, pnr_project_activity
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            // Mobilization button
            $mobilization = ($cardData && $cardData['pnr_mobilization'] !== null) ? $cardData['pnr_mobilization'] : null;
            if ($mobilization !== null && $mobilization > 0) {
                $mobButtonText = '📊 Мобилизационные мероприятия (' . $mobilization . ' дн.)';
            } else {
                $mobButtonText = '❓ Мобилизационные мероприятия';
            }
            $keyboard[] = [
                ['text' => $mobButtonText, 'callback_data' => 'proj_pnr_mobilization_' . $projectCode]
            ];
            
            // Project activity button
            $projectActivity = ($cardData && $cardData['pnr_project_activity'] !== null) ? $cardData['pnr_project_activity'] : null;
            if ($projectActivity !== null && $projectActivity > 0) {
                $projButtonText = '📊 Проектная деятельность (' . $projectActivity . ' дн.)';
            } else {
                $projButtonText = '❓ Проектная деятельность';
            }
            $keyboard[] = [
                ['text' => $projButtonText, 'callback_data' => 'proj_pnr_project_activity_' . $projectCode]
            ];
            
            $keyboard[] = [
                ['text' => '⬅️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showPNRMilestonesMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show edit menu for specific PNR milestone
     */
    private function showEditPNRMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getPNRMilestoneNames();
            
            if (!isset($milestoneNames[$milestoneIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Веха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            
            // Get existing milestone data
            $stmt = $conn->prepare("
                SELECT planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $milestoneName);
            $stmt->execute();
            $result = $stmt->get_result();
            $milestone = $result->fetch_assoc();
            
            // Check if milestone is confirmed
            $stmt = $conn->prepare("
                SELECT COUNT(*) as confirmed_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE '%Этап наладки завершен%'
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                     OR ot.message LIKE '%[Конструктор]%')
            ");
            $stmt->bind_param('ss', $projectCode, $milestoneName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $isConfirmed = ($row['confirmed_count'] ?? 0) > 0;
            
            $text = '⚙️ <b>' . $milestoneName . '</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            
            if ($milestone && $milestone['is_not_applicable']) {
                $text .= '📌 <b>Статус:</b> Не предусмотрено проектом';
            } elseif ($milestone && $milestone['planned_hours'] !== null) {
                $unit = ($milestone['unit'] === 'hours') ? 'часов' : 'дней';
                $text .= '📌 <b>Текущее значение:</b> ' . $milestone['planned_hours'] . ' ' . $unit;
            } else {
                $text .= '⚠️ Трудозатраты не установлены';
            }
            
            if ($isConfirmed) {
                $text .= "\n✅ <b>Веха подтверждена</b>";
            }
            
            $keyboard = [];
            
            if ($milestone && $milestone['is_not_applicable']) {
                // Currently marked as "not applicable"
                $keyboard[] = [
                    ['text' => '✏️ Установить трудозатраты', 'callback_data' => 'proj_pnr_set_' . $milestoneIndex . '_' . $projectCode]
                ];
            } elseif ($milestone && $milestone['planned_hours'] !== null) {
                // Has value set
                $keyboard[] = [
                    ['text' => '✏️ Изменить трудозатраты', 'callback_data' => 'proj_pnr_set_' . $milestoneIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '❌ Не предусмотрено проектом', 'callback_data' => 'proj_pnr_notappl_' . $milestoneIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_pnr_clear_' . $milestoneIndex . '_' . $projectCode]
                ];
            } else {
                // Not set yet
                $keyboard[] = [
                    ['text' => '➕ Установить трудозатраты', 'callback_data' => 'proj_pnr_set_' . $milestoneIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '❌ Не предусмотрено проектом', 'callback_data' => 'proj_pnr_notappl_' . $milestoneIndex . '_' . $projectCode]
                ];
            }
            
            // Confirm / Unconfirm milestone button
            if ($isConfirmed) {
                $keyboard[] = [
                    ['text' => '↩️ Отменить подтверждение вехи', 'callback_data' => 'proj_pnr_unconf_' . $milestoneIndex . '_' . $projectCode]
                ];
            } else {
                $keyboard[] = [
                    ['text' => '✅ Подтвердить веху (100%)', 'callback_data' => 'proj_pnr_conf_' . $milestoneIndex . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_pnr_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditPNRMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm PNR milestone (mark as 100% completed) via constructor
     */
    private function confirmPNRMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getPNRMilestoneNames();
            
            if (!isset($milestoneNames[$milestoneIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Веха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            
            // Get current user's FIO from ozna_data
            $stmt = $conn->prepare("SELECT JSON_EXTRACT(data, '$.fio') as fio FROM ozna_data WHERE user_id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $fio = $userData ? trim($userData['fio'], '"') : 'Конструктор';
            
            // Insert confirmation record into ozna_tabel
            $message = "<b>{$fio}</b>\r\n✅" . date('d.m.Y') . ", Проект <b>{$projectCode}</b>: Этап наладки завершен ({$milestoneName}) [Конструктор]";
            
            $stmt = $conn->prepare("INSERT INTO ozna_tabel (user_id, message) VALUES (?, ?)");
            $stmt->bind_param('is', $userId, $message);
            $stmt->execute();
            
            // Also create/update milestone_progress_estimates to 100%
            $stmt = $conn->prepare("
                INSERT INTO milestone_progress_estimates (project_code, work_type, milestone_name, estimated_percent, estimator_user_id, estimator_fio, estimated_at)
                VALUES (?, 'ПНР', ?, 100, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE estimated_percent = 100, estimator_user_id = ?, estimator_fio = ?, estimated_at = NOW()
            ");
            $stmt->bind_param('ssisis', $projectCode, $milestoneName, $userId, $fio, $userId, $fio);
            $stmt->execute();
            
            $this->messageService->sendMessage($chatId, "✅ Веха «{$milestoneName}» подтверждена (100%)");
            
            return $this->showPNRMilestonesMenu($chatId, $userId, $projectCode);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::confirmPNRMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Unconfirm PNR milestone (remove confirmation) via constructor
     */
    private function unconfirmPNRMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getPNRMilestoneNames();
            
            if (!isset($milestoneNames[$milestoneIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Веха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            
            // Delete ALL confirmation records for this milestone on this project
            // Matches the same pattern used in the confirmation check query
            $stmt = $conn->prepare("
                DELETE ot FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE '%Этап наладки завершен%'
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                     OR ot.message LIKE '%[Конструктор]%')
            ");
            $stmt->bind_param('ss', $projectCode, $milestoneName);
            $stmt->execute();
            $deletedCount = $stmt->affected_rows;
            
            $this->messageService->sendMessage($chatId, "↩️ Подтверждение вехи «{$milestoneName}» отменено (удалено записей: {$deletedCount})");
            
            return $this->showPNRMilestonesMenu($chatId, $userId, $projectCode);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::unconfirmPNRMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to enter PNR milestone hours
     */
    private function promptPNRMilestoneHours(string $chatId, int $userId, string $projectCode, int $milestoneIndex, Conversation $conversation): ServerResponse
    {
        $milestoneNames = $this->getPNRMilestoneNames();
        
        if (!isset($milestoneNames[$milestoneIndex])) {
            return $this->messageService->sendMessage($chatId, '❌ Веха не найдена');
        }
        
        $milestoneName = $milestoneNames[$milestoneIndex];
        
        $conversation->notes['state'] = 'awaiting_pnr_milestone_hours';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->notes['milestone_index'] = $milestoneIndex;
        $conversation->notes['milestone_name'] = $milestoneName;
        $conversation->update();
        
        $text = '✏️ <b>Плановые трудозатраты</b>

';
        $text .= '<b>Веха:</b> ' . $milestoneName . '

';
        $text .= 'Введите плановые трудозатраты в <b>днях</b> (целое число):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_pnr_back_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle PNR milestone hours input
     */
    public function handlePNRMilestoneHoursInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            $milestoneIndex = $conversation->notes['milestone_index'] ?? null;
            $milestoneName = $conversation->notes['milestone_name'] ?? null;
            
            if (!$projectCode || $milestoneIndex === null || !$milestoneName) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: данные не найдены');
            }
            
            $hours = trim($text);
            
            // Validate input - must be positive integer
            if (!is_numeric($hours) || intval($hours) != $hours || intval($hours) < 0) {
                $errorText = '❌ Неверный формат

Введите целое положительное число (дни):';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_pnr_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            $plannedHours = intval($hours);
            
            // Check if milestone already exists
            $stmt = $conn->prepare("
                SELECT id FROM project_milestones
                WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $milestoneName);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                // Update existing
                $stmt = $conn->prepare("
                    UPDATE project_milestones
                    SET planned_hours = ?, is_not_applicable = 0, unit = 'days'
                    WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = ?
                ");
                $stmt->bind_param('iss', $plannedHours, $projectCode, $milestoneName);
            } else {
                // Ensure project_cards record exists (FK constraint)
                $this->ensureProjectCardExists($projectCode, $userId);
                
                // Insert new
                $stmt = $conn->prepare("
                    INSERT INTO project_milestones (project_code, work_type, milestone_name, planned_hours, is_not_applicable, unit, order_index)
                    VALUES (?, 'ПНР', ?, ?, 0, 'days', ?)
                ");
                $stmt->bind_param('ssii', $projectCode, $milestoneName, $plannedHours, $milestoneIndex);
            }
            $stmt->execute();
            
            // Clear conversation
            $conversation->stop();
            
            $successText = '✅ <b>Трудозатраты установлены</b>

';
            $successText .= '<b>Веха:</b> ' . $milestoneName . '
';
            $successText .= '<b>Плановые трудозатраты:</b> ' . $plannedHours . ' дн.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_pnr_back_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handlePNRMilestoneHoursInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Set PNR milestone as not applicable
     */
    private function setPNRMilestoneNotApplicable(string $chatId, int $userId, string $projectCode, int $milestoneIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getPNRMilestoneNames();
            
            if (!isset($milestoneNames[$milestoneIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Веха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            
            // Check if milestone already exists
            $stmt = $conn->prepare("
                SELECT id FROM project_milestones
                WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $milestoneName);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                // Update existing
                $stmt = $conn->prepare("
                    UPDATE project_milestones
                    SET planned_hours = NULL, is_not_applicable = 1
                    WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = ?
                ");
                $stmt->bind_param('ss', $projectCode, $milestoneName);
            } else {
                // Ensure project_cards record exists (FK constraint)
                $this->ensureProjectCardExists($projectCode, $userId);
                
                // Insert new
                $stmt = $conn->prepare("
                    INSERT INTO project_milestones (project_code, work_type, milestone_name, planned_hours, is_not_applicable, order_index)
                    VALUES (?, 'ПНР', ?, NULL, 1, ?)
                ");
                $stmt->bind_param('ssi', $projectCode, $milestoneName, $milestoneIndex);
            }
            $stmt->execute();
            
            $text = '✅ <b>Статус установлен</b>

';
            $text .= '<b>Веха:</b> ' . $milestoneName . '
';
            $text .= '📌 <b>Статус:</b> Не предусмотрено проектом';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_pnr_back_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::setPNRMilestoneNotApplicable error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear PNR milestone data
     */
    private function clearPNRMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getPNRMilestoneNames();
            
            if (!isset($milestoneNames[$milestoneIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Веха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            
            // Delete milestone
            $stmt = $conn->prepare("
                DELETE FROM project_milestones
                WHERE project_code = ? AND work_type = 'ПНР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $milestoneName);
            $stmt->execute();
            
            $text = '✅ <b>Данные очищены</b>

';
            $text .= '<b>Веха:</b> ' . $milestoneName . '

';
            $text .= 'Трудозатраты и статус удалены.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_pnr_back_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearPNRMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Show edit menu for PNR Mobilization
     */
    private function showEditPNRMobilization(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get existing data
            $stmt = $conn->prepare("
                SELECT pnr_mobilization
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $text = '🚗 <b>Мобилизационные мероприятия</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Дорога + Инструктаж

';
            
            if ($cardData && $cardData['pnr_mobilization'] !== null) {
                $text .= '📊 <b>Текущее значение:</b> ' . $cardData['pnr_mobilization'] . ' дней';
            } else {
                $text .= '⚠️ Трудозатраты не установлены';
            }
            
            $keyboard = [
                [
                    ['text' => '✏️ Установить трудозатраты', 'callback_data' => 'proj_pnr_mob_set_' . $projectCode]
                ]
            ];
            
            if ($cardData && $cardData['pnr_mobilization'] !== null) {
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_pnr_mob_clear_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditPNRMobilization error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to enter PNR Mobilization hours
     */
    private function promptPNRMobilization(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_pnr_mobilization';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '✏️ <b>Плановые трудозатраты</b>

';
        $text .= '<b>Мобилизационные мероприятия</b>

';
        $text .= 'Введите плановые трудозатраты в <b>днях</b> (целое число):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle PNR Mobilization input
     */
    public function handlePNRMobilizationInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: данные не найдены');
            }
            
            $days = trim($text);
            
            // Validate input - must be positive integer
            if (!is_numeric($days) || intval($days) != $days || intval($days) < 0) {
                $errorText = '❌ Неверный формат

Введите целое положительное число (дни):';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            $plannedDays = intval($days);
            
            // Check if project_cards record exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc();
            
            if ($exists) {
                // Update existing
                $stmt = $conn->prepare("
                    UPDATE project_cards
                    SET pnr_mobilization = ?
                    WHERE project_code = ?
                ");
                $stmt->bind_param('is', $plannedDays, $projectCode);
            } else {
                // Insert new
                $stmt = $conn->prepare("
                    INSERT INTO project_cards (project_code, pnr_mobilization)
                    VALUES (?, ?)
                ");
                $stmt->bind_param('si', $projectCode, $plannedDays);
            }
            $stmt->execute();
            
            // Clear conversation state
            $conversation->stop();
            
            $text = '✅ <b>Трудозатраты установлены</b>

';
            $text .= '<b>Мобилизационные мероприятия:</b> ' . $plannedDays . ' дн.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handlePNRMobilizationInput error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear PNR Mobilization data
     */
    private function clearPNRMobilization(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                UPDATE project_cards
                SET pnr_mobilization = NULL
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Данные очищены</b>

';
            $text .= '<b>Мобилизационные мероприятия:</b> данные удалены';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearPNRMobilization error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show edit menu for PNR Project Activity
     */
    private function showEditPNRProjectActivity(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get existing data
            $stmt = $conn->prepare("
                SELECT pnr_project_activity
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $text = '📝 <b>Проектная деятельность</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Разработка программ и графиков

';
            
            if ($cardData && $cardData['pnr_project_activity'] !== null) {
                $text .= '📊 <b>Текущее значение:</b> ' . $cardData['pnr_project_activity'] . ' дней';
            } else {
                $text .= '⚠️ Трудозатраты не установлены';
            }
            
            $keyboard = [
                [
                    ['text' => '✏️ Установить трудозатраты', 'callback_data' => 'proj_pnr_projact_set_' . $projectCode]
                ]
            ];
            
            if ($cardData && $cardData['pnr_project_activity'] !== null) {
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_pnr_projact_clear_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditPNRProjectActivity error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to enter PNR Project Activity hours
     */
    private function promptPNRProjectActivity(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_pnr_project_activity';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '✏️ <b>Плановые трудозатраты</b>

';
        $text .= '<b>Проектная деятельность</b>

';
        $text .= 'Введите плановые трудозатраты в <b>днях</b> (целое число):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle PNR Project Activity input
     */
    public function handlePNRProjectActivityInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: данные не найдены');
            }
            
            $days = trim($text);
            
            // Validate input - must be positive integer
            if (!is_numeric($days) || intval($days) != $days || intval($days) < 0) {
                $errorText = '❌ Неверный формат

Введите целое положительное число (дни):';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            $plannedDays = intval($days);
            
            // Check if project_cards record exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc();
            
            if ($exists) {
                // Update existing
                $stmt = $conn->prepare("
                    UPDATE project_cards
                    SET pnr_project_activity = ?
                    WHERE project_code = ?
                ");
                $stmt->bind_param('is', $plannedDays, $projectCode);
            } else {
                // Insert new
                $stmt = $conn->prepare("
                    INSERT INTO project_cards (project_code, pnr_project_activity)
                    VALUES (?, ?)
                ");
                $stmt->bind_param('si', $projectCode, $plannedDays);
            }
            $stmt->execute();
            
            // Clear conversation state
            $conversation->stop();
            
            $text = '✅ <b>Трудозатраты установлены</b>

';
            $text .= '<b>Проектная деятельность:</b> ' . $plannedDays . ' дн.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handlePNRProjectActivityInput error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear PNR Project Activity data
     */
    private function clearPNRProjectActivity(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                UPDATE project_cards
                SET pnr_project_activity = NULL
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Данные очищены</b>

';
            $text .= '<b>Проектная деятельность:</b> данные удалены';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearPNRProjectActivity error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    // ==================== SMR Constructor Methods ====================

    /**
     * Get sub-milestone names for a given top-level SMR milestone index
     */
    private function getSMRSubMilestoneNames(int $milestoneIndex): array
    {
        $milestoneNames = $this->getSMRMilestoneNames();
        if (!isset($milestoneNames[$milestoneIndex])) {
            return [];
        }
        $structure = $this->getSMRMilestoneStructure();
        $milestoneName = $milestoneNames[$milestoneIndex];
        if (!isset($structure[$milestoneName])) {
            return [];
        }
        return array_keys($structure[$milestoneName]);
    }

    /**
     * Show SMR milestones menu (top-level milestones with aggregated sub-milestone status)
     */
    private function showSMRMilestonesMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get project info
            $stmt = $conn->prepare("SELECT code, name FROM project WHERE code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $project = $result->fetch_assoc();
            
            if (!$project) {
                return $this->messageService->sendMessage($chatId, '❌ Проект не найден');
            }
            
            $milestoneNames = $this->getSMRMilestoneNames();
            
            // Get all existing SMR milestones from database
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $existingMilestones = [];
            while ($row = $result->fetch_assoc()) {
                $existingMilestones[$row['milestone_name']] = $row;
            }
            
            $text = '🔧 <b>ШМР: Плановые трудозатраты</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Настройте плановые трудозатраты для каждой вехи:

';
            
            $keyboard = [];
            foreach ($milestoneNames as $index => $milestoneName) {
                $subNames = $this->getSMRSubMilestoneNames($index);
                $totalSub = count($subNames);
                $configuredCount = 0;
                $totalDays = 0;
                
                foreach ($subNames as $subName) {
                    $sub = $existingMilestones[$subName] ?? null;
                    if ($sub) {
                        if ($sub['is_not_applicable']) {
                            $configuredCount++;
                        } elseif ($sub['planned_hours'] !== null) {
                            $configuredCount++;
                            $totalDays += (int)$sub['planned_hours'];
                        }
                    }
                }
                
                if ($configuredCount === $totalSub && $totalSub > 0) {
                    $icon = '📊';
                    $buttonText = $icon . ' ' . $milestoneName . ' (' . $totalDays . ' дн.)';
                } elseif ($configuredCount > 0) {
                    $icon = '⚠️';
                    $buttonText = $icon . ' ' . $milestoneName . ' (' . $totalDays . ' дн., ' . $configuredCount . '/' . $totalSub . ')';
                } else {
                    $icon = '❓';
                    $buttonText = $icon . ' ' . $milestoneName;
                }
                
                $keyboard[] = [
                    ['text' => $buttonText, 'callback_data' => 'proj_smr_milestone_' . $index . '_' . $projectCode]
                ];
            }
            
            // Get additional work data from project_cards
            $stmt = $conn->prepare("
                SELECT smr_mobilization, smr_project_activity, smr_master_duties
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            // Mobilization button
            $mobilization = ($cardData && $cardData['smr_mobilization'] !== null) ? $cardData['smr_mobilization'] : null;
            if ($mobilization !== null && $mobilization > 0) {
                $mobButtonText = '📊 Мобилизационные мероприятия (' . $mobilization . ' дн.)';
            } else {
                $mobButtonText = '❓ Мобилизационные мероприятия';
            }
            $keyboard[] = [
                ['text' => $mobButtonText, 'callback_data' => 'proj_smr_mobilization_' . $projectCode]
            ];
            
            // Project activity button
            $projectActivity = ($cardData && $cardData['smr_project_activity'] !== null) ? $cardData['smr_project_activity'] : null;
            if ($projectActivity !== null && $projectActivity > 0) {
                $projButtonText = '📊 Проектная деятельность (' . $projectActivity . ' дн.)';
            } else {
                $projButtonText = '❓ Проектная деятельность';
            }
            $keyboard[] = [
                ['text' => $projButtonText, 'callback_data' => 'proj_smr_project_activity_' . $projectCode]
            ];
            
            // Master duties button
            $masterDuties = ($cardData && $cardData['smr_master_duties'] !== null) ? $cardData['smr_master_duties'] : null;
            if ($masterDuties !== null && $masterDuties > 0) {
                $masterButtonText = '📊 Обязанности мастера (' . $masterDuties . ' дн.)';
            } else {
                $masterButtonText = '❓ Обязанности мастера';
            }
            $keyboard[] = [
                ['text' => $masterButtonText, 'callback_data' => 'proj_smr_master_duties_' . $projectCode]
            ];
            
            $keyboard[] = [
                ['text' => '⬅️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showSMRMilestonesMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show sub-milestones list for a top-level SMR milestone
     */
    private function showSMRSubMilestonesMenu(string $chatId, int $userId, string $projectCode, int $milestoneIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getSMRMilestoneNames();
            
            if (!isset($milestoneNames[$milestoneIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Веха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            $subNames = $this->getSMRSubMilestoneNames($milestoneIndex);
            
            // Get existing sub-milestones from database
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $existingMilestones = [];
            while ($row = $result->fetch_assoc()) {
                $existingMilestones[$row['milestone_name']] = $row;
            }
            
            $text = '🔧 <b>' . $milestoneName . '</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Настройте плановые трудозатраты для каждой подвехи:

';
            
            $keyboard = [];
            foreach ($subNames as $subIndex => $subName) {
                $sub = $existingMilestones[$subName] ?? null;
                
                if ($sub && $sub['is_not_applicable']) {
                    $icon = '❌';
                    $buttonText = $icon . ' ' . $subName . ' (не предусм.)';
                } elseif ($sub && $sub['planned_hours'] !== null) {
                    $icon = '📊';
                    $unit = ($sub['unit'] === 'hours') ? 'ч.' : 'дн.';
                    $buttonText = $icon . ' ' . $subName . ' (' . $sub['planned_hours'] . ' ' . $unit . ')';
                } else {
                    $icon = '❓';
                    $buttonText = $icon . ' ' . $subName;
                }
                
                $keyboard[] = [
                    ['text' => $buttonText, 'callback_data' => 'proj_smr_sub_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_smr_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showSMRSubMilestonesMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show edit menu for specific SMR sub-milestone
     */
    private function showEditSMRSubMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $subNames = $this->getSMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($subNames[$subIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $subName = $subNames[$subIndex];
            
            // Get existing milestone data
            $stmt = $conn->prepare("
                SELECT planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $subName);
            $stmt->execute();
            $result = $stmt->get_result();
            $milestone = $result->fetch_assoc();
            
            // Check if sub-milestone is confirmed
            $stmt = $conn->prepare("
                SELECT COUNT(*) as confirmed_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                     OR ot.message LIKE '%[Конструктор]%')
            ");
            $stmt->bind_param('ss', $projectCode, $subName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $isConfirmed = ($row['confirmed_count'] ?? 0) > 0;
            
            $text = '🔧 <b>' . $subName . '</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            
            if ($milestone && $milestone['is_not_applicable']) {
                $text .= '📌 <b>Статус:</b> Не предусмотрено проектом';
            } elseif ($milestone && $milestone['planned_hours'] !== null) {
                $unit = ($milestone['unit'] === 'hours') ? 'часов' : 'дней';
                $text .= '📌 <b>Текущее значение:</b> ' . $milestone['planned_hours'] . ' ' . $unit;
            } else {
                $text .= '⚠️ Трудозатраты не установлены';
            }
            
            if ($isConfirmed) {
                $text .= "\n✅ <b>Подвеха подтверждена</b>";
            }
            
            $keyboard = [];
            
            if ($milestone && $milestone['is_not_applicable']) {
                $keyboard[] = [
                    ['text' => '✏️ Установить трудозатраты', 'callback_data' => 'proj_smr_sset_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            } elseif ($milestone && $milestone['planned_hours'] !== null) {
                $keyboard[] = [
                    ['text' => '✏️ Изменить трудозатраты', 'callback_data' => 'proj_smr_sset_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '❌ Не предусмотрено проектом', 'callback_data' => 'proj_smr_sna_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_smr_sclr_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            } else {
                $keyboard[] = [
                    ['text' => '➕ Установить трудозатраты', 'callback_data' => 'proj_smr_sset_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '❌ Не предусмотрено проектом', 'callback_data' => 'proj_smr_sna_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            }
            
            // Confirm / Unconfirm sub-milestone button
            if ($isConfirmed) {
                $keyboard[] = [
                    ['text' => '↩️ Отменить подтверждение', 'callback_data' => 'proj_smr_unconf_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            } else {
                $keyboard[] = [
                    ['text' => '✅ Подтвердить подвеху (100%)', 'callback_data' => 'proj_smr_conf_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ К подвехам', 'callback_data' => 'proj_smr_milestone_' . $milestoneIndex . '_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditSMRSubMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm SMR sub-milestone (mark as 100% completed) via constructor
     */
    private function confirmSMRMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $subNames = $this->getSMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($subNames[$subIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $subName = $subNames[$subIndex];
            
            // Get current user's FIO
            $stmt = $conn->prepare("SELECT JSON_EXTRACT(data, '$.fio') as fio FROM ozna_data WHERE user_id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
            $fio = $userData ? trim($userData['fio'], '"') : 'Конструктор';
            
            // Insert confirmation record into ozna_tabel
            $message = "<b>{$fio}</b>\r\n✅" . date('d.m.Y') . ", Проект <b>{$projectCode}</b>: Этап шеф-монтажа завершен ({$subName}) [Конструктор]";
            
            $stmt = $conn->prepare("INSERT INTO ozna_tabel (user_id, message) VALUES (?, ?)");
            $stmt->bind_param('is', $userId, $message);
            $stmt->execute();
            
            $this->messageService->sendMessage($chatId, "✅ Подвеха «{$subName}» подтверждена (100%)");
            
            return $this->showSMRSubMilestonesMenu($chatId, $userId, $projectCode, $milestoneIndex);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::confirmSMRMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Unconfirm SMR sub-milestone (remove confirmation) via constructor
     */
    private function unconfirmSMRMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $subNames = $this->getSMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($subNames[$subIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $subName = $subNames[$subIndex];
            
            // Delete ALL confirmation records for this sub-milestone
            $stmt = $conn->prepare("
                DELETE ot FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                     OR ot.message LIKE '%[Конструктор]%')
            ");
            $stmt->bind_param('ss', $projectCode, $subName);
            $stmt->execute();
            $deletedCount = $stmt->affected_rows;
            
            $this->messageService->sendMessage($chatId, "↩️ Подтверждение подвехи «{$subName}» отменено (удалено записей: {$deletedCount})");
            
            return $this->showSMRSubMilestonesMenu($chatId, $userId, $projectCode, $milestoneIndex);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::unconfirmSMRMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt for SMR sub-milestone hours input
     */
    private function promptSMRMilestoneHours(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex, Conversation $conversation): ServerResponse
    {
        $subNames = $this->getSMRSubMilestoneNames($milestoneIndex);
        
        if (!isset($subNames[$subIndex])) {
            return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
        }
        
        $subName = $subNames[$subIndex];
        
        $conversation->notes['state'] = 'awaiting_smr_milestone_hours';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->notes['milestone_index'] = $milestoneIndex;
        $conversation->notes['sub_milestone_index'] = $subIndex;
        $conversation->notes['milestone_name'] = $subName;
        $conversation->update();
        
        $text = '✏️ <b>Плановые трудозатраты</b>

';
        $text .= '<b>Подвеха:</b> ' . $subName . '

';
        $text .= 'Введите плановые трудозатраты в <b>днях</b> (целое число):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_smr_milestone_' . $milestoneIndex . '_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle SMR sub-milestone hours input
     */
    public function handleSMRMilestoneHoursInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            $milestoneIndex = $conversation->notes['milestone_index'] ?? null;
            $subIndex = $conversation->notes['sub_milestone_index'] ?? null;
            $milestoneName = $conversation->notes['milestone_name'] ?? null;
            
            if (!$projectCode || $milestoneIndex === null || !$milestoneName) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: данные не найдены');
            }
            
            $hours = trim($text);
            
            // Validate input - must be positive integer
            if (!is_numeric($hours) || intval($hours) != $hours || intval($hours) < 0) {
                $errorText = '❌ Неверный формат

Введите целое положительное число (дни):';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_smr_milestone_' . $milestoneIndex . '_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            $plannedHours = intval($hours);
            
            // Check if milestone already exists
            $stmt = $conn->prepare("
                SELECT id FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $milestoneName);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                $stmt = $conn->prepare("
                    UPDATE project_milestones
                    SET planned_hours = ?, is_not_applicable = 0, unit = 'days'
                    WHERE project_code = ? AND work_type = 'ШМР' AND milestone_name = ?
                ");
                $stmt->bind_param('iss', $plannedHours, $projectCode, $milestoneName);
            } else {
                // Ensure project_cards record exists (FK constraint)
                $this->ensureProjectCardExists($projectCode, $userId);
                
                $stmt = $conn->prepare("
                    INSERT INTO project_milestones (project_code, work_type, milestone_name, planned_hours, is_not_applicable, unit, order_index)
                    VALUES (?, 'ШМР', ?, ?, 0, 'days', ?)
                ");
                $orderIdx = $milestoneIndex * 100 + ($subIndex ?? 0);
                $stmt->bind_param('ssii', $projectCode, $milestoneName, $plannedHours, $orderIdx);
            }
            $stmt->execute();
            
            // Clear conversation
            $conversation->stop();
            
            $successText = '✅ <b>Трудозатраты установлены</b>

';
            $successText .= '<b>Подвеха:</b> ' . $milestoneName . '
';
            $successText .= '<b>Плановые трудозатраты:</b> ' . $plannedHours . ' дн.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К подвехам', 'callback_data' => 'proj_smr_milestone_' . $milestoneIndex . '_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К списку вех', 'callback_data' => 'proj_smr_back_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleSMRMilestoneHoursInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Set SMR sub-milestone as not applicable
     */
    private function setSMRMilestoneNotApplicable(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $subNames = $this->getSMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($subNames[$subIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $subName = $subNames[$subIndex];
            
            $stmt = $conn->prepare("
                SELECT id FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $subName);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                $stmt = $conn->prepare("
                    UPDATE project_milestones
                    SET planned_hours = NULL, is_not_applicable = 1
                    WHERE project_code = ? AND work_type = 'ШМР' AND milestone_name = ?
                ");
                $stmt->bind_param('ss', $projectCode, $subName);
            } else {
                // Ensure project_cards record exists (FK constraint)
                $this->ensureProjectCardExists($projectCode, $userId);
                
                $stmt = $conn->prepare("
                    INSERT INTO project_milestones (project_code, work_type, milestone_name, planned_hours, is_not_applicable, order_index)
                    VALUES (?, 'ШМР', ?, NULL, 1, ?)
                ");
                $orderIdx = $milestoneIndex * 100 + $subIndex;
                $stmt->bind_param('ssi', $projectCode, $subName, $orderIdx);
            }
            $stmt->execute();
            
            $text = '✅ <b>Статус установлен</b>

';
            $text .= '<b>Подвеха:</b> ' . $subName . '
';
            $text .= '📌 <b>Статус:</b> Не предусмотрено проектом';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К подвехам', 'callback_data' => 'proj_smr_milestone_' . $milestoneIndex . '_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К списку вех', 'callback_data' => 'proj_smr_back_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::setSMRMilestoneNotApplicable error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear SMR sub-milestone data
     */
    private function clearSMRMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $subNames = $this->getSMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($subNames[$subIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $subName = $subNames[$subIndex];
            
            $stmt = $conn->prepare("
                DELETE FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $subName);
            $stmt->execute();
            
            $text = '✅ <b>Данные очищены</b>

';
            $text .= '<b>Подвеха:</b> ' . $subName . '

';
            $text .= 'Трудозатраты и статус удалены.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К подвехам', 'callback_data' => 'proj_smr_milestone_' . $milestoneIndex . '_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К списку вех', 'callback_data' => 'proj_smr_back_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearSMRMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Show edit menu for SMR Mobilization
     */
    private function showEditSMRMobilization(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                SELECT smr_mobilization
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $text = '🚗 <b>Мобилизационные мероприятия</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Дорога + Инструктаж

';
            
            if ($cardData && $cardData['smr_mobilization'] !== null) {
                $text .= '📊 <b>Текущее значение:</b> ' . $cardData['smr_mobilization'] . ' дней';
            } else {
                $text .= '⚠️ Трудозатраты не установлены';
            }
            
            $keyboard = [
                [
                    ['text' => '✏️ Установить трудозатраты', 'callback_data' => 'proj_smr_mob_set_' . $projectCode]
                ]
            ];
            
            if ($cardData && $cardData['smr_mobilization'] !== null) {
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_smr_mob_clear_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditSMRMobilization error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to enter SMR Mobilization hours
     */
    private function promptSMRMobilization(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_smr_mobilization';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '✏️ <b>Плановые трудозатраты</b>

';
        $text .= '<b>Мобилизационные мероприятия</b>

';
        $text .= 'Введите плановые трудозатраты в <b>днях</b> (целое число):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle SMR Mobilization input
     */
    public function handleSMRMobilizationInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: данные не найдены');
            }
            
            $days = trim($text);
            
            if (!is_numeric($days) || intval($days) != $days || intval($days) < 0) {
                $errorText = '❌ Неверный формат

Введите целое положительное число (дни):';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            $plannedDays = intval($days);
            
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc();
            
            if ($exists) {
                $stmt = $conn->prepare("UPDATE project_cards SET smr_mobilization = ? WHERE project_code = ?");
                $stmt->bind_param('is', $plannedDays, $projectCode);
            } else {
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, smr_mobilization) VALUES (?, ?)");
                $stmt->bind_param('si', $projectCode, $plannedDays);
            }
            $stmt->execute();
            
            $conversation->stop();
            
            $text = '✅ <b>Трудозатраты установлены</b>

';
            $text .= '<b>Мобилизационные мероприятия:</b> ' . $plannedDays . ' дн.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleSMRMobilizationInput error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear SMR Mobilization data
     */
    private function clearSMRMobilization(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("UPDATE project_cards SET smr_mobilization = NULL WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Данные очищены</b>

';
            $text .= '<b>Мобилизационные мероприятия:</b> данные удалены';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearSMRMobilization error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show edit menu for SMR Project Activity
     */
    private function showEditSMRProjectActivity(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                SELECT smr_project_activity
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $text = '📝 <b>Проектная деятельность</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Разработка программ и графиков

';
            
            if ($cardData && $cardData['smr_project_activity'] !== null) {
                $text .= '📊 <b>Текущее значение:</b> ' . $cardData['smr_project_activity'] . ' дней';
            } else {
                $text .= '⚠️ Трудозатраты не установлены';
            }
            
            $keyboard = [
                [
                    ['text' => '✏️ Установить трудозатраты', 'callback_data' => 'proj_smr_projact_set_' . $projectCode]
                ]
            ];
            
            if ($cardData && $cardData['smr_project_activity'] !== null) {
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_smr_projact_clear_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditSMRProjectActivity error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to enter SMR Project Activity hours
     */
    private function promptSMRProjectActivity(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_smr_project_activity';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '✏️ <b>Плановые трудозатраты</b>

';
        $text .= '<b>Проектная деятельность</b>

';
        $text .= 'Введите плановые трудозатраты в <b>днях</b> (целое число):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle SMR Project Activity input
     */
    public function handleSMRProjectActivityInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: данные не найдены');
            }
            
            $days = trim($text);
            
            if (!is_numeric($days) || intval($days) != $days || intval($days) < 0) {
                $errorText = '❌ Неверный формат

Введите целое положительное число (дни):';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            $plannedDays = intval($days);
            
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc();
            
            if ($exists) {
                $stmt = $conn->prepare("UPDATE project_cards SET smr_project_activity = ? WHERE project_code = ?");
                $stmt->bind_param('is', $plannedDays, $projectCode);
            } else {
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, smr_project_activity) VALUES (?, ?)");
                $stmt->bind_param('si', $projectCode, $plannedDays);
            }
            $stmt->execute();
            
            $conversation->stop();
            
            $text = '✅ <b>Трудозатраты установлены</b>

';
            $text .= '<b>Проектная деятельность:</b> ' . $plannedDays . ' дн.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleSMRProjectActivityInput error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear SMR Project Activity data
     */
    private function clearSMRProjectActivity(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("UPDATE project_cards SET smr_project_activity = NULL WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Данные очищены</b>

';
            $text .= '<b>Проектная деятельность:</b> данные удалены';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearSMRProjectActivity error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show edit menu for SMR Master Duties
     */
    private function showEditSMRMasterDuties(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("
                SELECT smr_master_duties
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $text = '👷 <b>Обязанности мастера</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            
            if ($cardData && $cardData['smr_master_duties'] !== null) {
                $text .= '📊 <b>Текущее значение:</b> ' . $cardData['smr_master_duties'] . ' дней';
            } else {
                $text .= '⚠️ Трудозатраты не установлены';
            }
            
            $keyboard = [
                [
                    ['text' => '✏️ Установить трудозатраты', 'callback_data' => 'proj_smr_master_set_' . $projectCode]
                ]
            ];
            
            if ($cardData && $cardData['smr_master_duties'] !== null) {
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_smr_master_clear_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditSMRMasterDuties error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to enter SMR Master Duties hours
     */
    private function promptSMRMasterDuties(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_smr_master_duties';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '✏️ <b>Плановые трудозатраты</b>

';
        $text .= '<b>Обязанности мастера</b>

';
        $text .= 'Введите плановые трудозатраты в <b>днях</b> (целое число):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle SMR Master Duties input
     */
    public function handleSMRMasterDutiesInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: данные не найдены');
            }
            
            $days = trim($text);
            
            if (!is_numeric($days) || intval($days) != $days || intval($days) < 0) {
                $errorText = '❌ Неверный формат

Введите целое положительное число (дни):';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            $plannedDays = intval($days);
            
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $exists = $result->fetch_assoc();
            
            if ($exists) {
                $stmt = $conn->prepare("UPDATE project_cards SET smr_master_duties = ? WHERE project_code = ?");
                $stmt->bind_param('is', $plannedDays, $projectCode);
            } else {
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, smr_master_duties) VALUES (?, ?)");
                $stmt->bind_param('si', $projectCode, $plannedDays);
            }
            $stmt->execute();
            
            $conversation->stop();
            
            $text = '✅ <b>Трудозатраты установлены</b>

';
            $text .= '<b>Обязанности мастера:</b> ' . $plannedDays . ' дн.';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleSMRMasterDutiesInput error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear SMR Master Duties data
     */
    private function clearSMRMasterDuties(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("UPDATE project_cards SET smr_master_duties = NULL WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Данные очищены</b>

';
            $text .= '<b>Обязанности мастера:</b> данные удалены';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🛠️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearSMRMasterDuties error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Show CMR milestones menu (top-level milestones with aggregated sub-milestone status)
     */
    private function showCMRMilestonesMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get project info
            $stmt = $conn->prepare("SELECT code, name FROM project WHERE code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $project = $result->fetch_assoc();
            
            if (!$project) {
                return $this->messageService->sendMessage($chatId, '❌ Проект не найден');
            }
            
            $milestoneNames = $this->getCMRMilestoneNames();
            
            // Get all existing CMR milestones from database
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $existingMilestones = [];
            while ($row = $result->fetch_assoc()) {
                $existingMilestones[$row['milestone_name']] = $row;
            }
            
            $text = '🏗️ <b>СМР: Плановые трудозатраты</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Настройте плановые трудозатраты для каждой вехи:

';
            
            $keyboard = [];
            foreach ($milestoneNames as $index => $milestoneName) {
                $subNames = $this->getCMRSubMilestoneNames($index);
                $totalSub = count($subNames);
                $configuredCount = 0;
                $totalDays = 0;
                
                foreach ($subNames as $subName) {
                    $fullSubName = $milestoneName . ' - ' . $subName;
                    $sub = $existingMilestones[$fullSubName] ?? null;
                    if ($sub) {
                        if ($sub['is_not_applicable']) {
                            $configuredCount++;
                        } elseif ($sub['planned_hours'] !== null) {
                            $configuredCount++;
                            $totalDays += (int)$sub['planned_hours'];
                        }
                    }
                }
                
                if ($configuredCount === $totalSub && $totalSub > 0) {
                    $icon = '📊';
                    $buttonText = $icon . ' ' . $milestoneName . ' (' . $totalDays . ' дн.)';
                } elseif ($configuredCount > 0) {
                    $icon = '⚠️';
                    $buttonText = $icon . ' ' . $milestoneName . ' (' . $totalDays . ' дн., ' . $configuredCount . '/' . $totalSub . ')';
                } else {
                    $icon = '❓';
                    $buttonText = $icon . ' ' . $milestoneName;
                }
                
                $keyboard[] = [
                    ['text' => $buttonText, 'callback_data' => 'proj_cmr_milestone_' . $index . '_' . $projectCode]
                ];
            }
            
            // Get additional work data from project_cards
            $stmt = $conn->prepare("
                SELECT cmr_mobilization, cmr_forecast_enabled
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            // Mobilization button
            $mobilization = ($cardData && $cardData['cmr_mobilization'] !== null) ? $cardData['cmr_mobilization'] : null;
            if ($mobilization !== null && $mobilization > 0) {
                $mobButtonText = '📊 Мобилизационные мероприятия (' . $mobilization . ' дн.)';
            } else {
                $mobButtonText = '❓ Мобилизационные мероприятия';
            }
            $keyboard[] = [
                ['text' => $mobButtonText, 'callback_data' => 'proj_cmr_mobilization_' . $projectCode]
            ];
            
            // Forecast toggle button
            $forecastEnabled = ($cardData && isset($cardData['cmr_forecast_enabled'])) ? (bool)$cardData['cmr_forecast_enabled'] : true;
            $forecastStatusText = $forecastEnabled ? '🔮 Прогноз эффективности: ВКЛ' : '🔮 Прогноз эффективности: ВЫКЛ';
            $keyboard[] = [
                ['text' => $forecastStatusText, 'callback_data' => 'proj_cmr_forecast_toggle_' . $projectCode]
            ];
            
            $keyboard[] = [
                ['text' => '⬅️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode . '_cmr']
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showCMRMilestonesMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show sub-milestones list for a top-level CMR milestone
     */
    private function showCMRSubMilestonesMenu(string $chatId, int $userId, string $projectCode, int $milestoneIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getCMRMilestoneNames();
            
            if (!isset($milestoneNames[$milestoneIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Веха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            $subNames = $this->getCMRSubMilestoneNames($milestoneIndex);
            
            // Get existing sub-milestones from database
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $existingMilestones = [];
            while ($row = $result->fetch_assoc()) {
                $existingMilestones[$row['milestone_name']] = $row;
            }
            
            $text = '🏗️ <b>' . $milestoneName . '</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            $text .= 'Настройте плановые трудозатраты для каждой подвехи:

';
            
            $keyboard = [];
            foreach ($subNames as $subIndex => $subName) {
                $fullSubName = $milestoneName . ' - ' . $subName;
                $sub = $existingMilestones[$fullSubName] ?? null;
                
                if ($sub && $sub['is_not_applicable']) {
                    $icon = '❌';
                    $buttonText = $icon . ' ' . $subName . ' (не предусм.)';
                } elseif ($sub && $sub['planned_hours'] !== null) {
                    $icon = '📊';
                    $unit = ($sub['unit'] === 'hours') ? 'ч.' : 'дн.';
                    $buttonText = $icon . ' ' . $subName . ' (' . $sub['planned_hours'] . ' ' . $unit . ')';
                } else {
                    $icon = '❓';
                    $buttonText = $icon . ' ' . $subName;
                }
                
                $keyboard[] = [
                    ['text' => $buttonText, 'callback_data' => 'proj_cmr_sub_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showCMRSubMilestonesMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show edit screen for a CMR sub-milestone
     */
    private function showEditCMRSubMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getCMRMilestoneNames();
            $subNames = $this->getCMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($milestoneNames[$milestoneIndex]) || !isset($subNames[$subIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            $subName = $subNames[$subIndex];
            $fullSubName = $milestoneName . ' - ' . $subName;
            
            // Get existing sub-milestone from database
            $stmt = $conn->prepare("
                SELECT planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $fullSubName);
            $stmt->execute();
            $result = $stmt->get_result();
            $sub = $result->fetch_assoc();
            
            $text = '🏗️ <b>' . $milestoneName . '</b>
<b>Подвеха:</b> ' . $subName . '

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            
            $keyboard = [];
            
            if ($sub) {
                if ($sub['is_not_applicable']) {
                    $text .= '❌ <b>Статус:</b> Не предусмотрено проектом';
                } elseif ($sub['planned_hours'] !== null) {
                    $unit = ($sub['unit'] === 'hours') ? 'часов' : 'дней';
                    $text .= '📊 <b>Текущий план:</b> ' . $sub['planned_hours'] . ' ' . $unit;
                } else {
                    $text .= '❓ <b>Статус:</b> Не настроено';
                }
                
                $keyboard[] = [
                    ['text' => '✏️ Изменить трудозатраты', 'callback_data' => 'proj_cmr_sset_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '❌ Не предусмотрено проектом', 'callback_data' => 'proj_cmr_sna_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_cmr_sclr_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            } else {
                $text .= '❓ <b>Статус:</b> Не настроено';
                $keyboard[] = [
                    ['text' => '➕ Установить трудозатраты', 'callback_data' => 'proj_cmr_sset_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '❌ Не предусмотрено проектом', 'callback_data' => 'proj_cmr_sna_' . $milestoneIndex . '_' . $subIndex . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ К подвехам', 'callback_data' => 'proj_cmr_milestone_' . $milestoneIndex . '_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditCMRSubMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user for CMR milestone hours input
     */
    private function promptCMRMilestoneHours(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex, Conversation $conversation): ServerResponse
    {
        $milestoneNames = $this->getCMRMilestoneNames();
        $subNames = $this->getCMRSubMilestoneNames($milestoneIndex);
        
        if (!isset($milestoneNames[$milestoneIndex]) || !isset($subNames[$subIndex])) {
            return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
        }
        
        $milestoneName = $milestoneNames[$milestoneIndex];
        $subName = $subNames[$subIndex];
        
        $conversation->notes['state'] = 'awaiting_cmr_milestone_hours';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->notes['cmr_milestone_index'] = $milestoneIndex;
        $conversation->notes['cmr_sub_index'] = $subIndex;
        $conversation->update();
        
        $text = '🏗️ <b>' . $milestoneName . '</b>
<b>Подвеха:</b> ' . $subName . '

';
        $text .= 'Введите плановые трудозатраты (в днях):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_cmr_milestone_' . $milestoneIndex . '_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle CMR milestone hours input
     */
    public function handleCMRMilestoneHoursInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            $milestoneIndex = $conversation->notes['cmr_milestone_index'] ?? null;
            $subIndex = $conversation->notes['cmr_sub_index'] ?? null;
            
            if (!$projectCode || $milestoneIndex === null || $subIndex === null) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: данные не найдены');
            }
            
            $milestoneNames = $this->getCMRMilestoneNames();
            $subNames = $this->getCMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($milestoneNames[$milestoneIndex]) || !isset($subNames[$subIndex])) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            $subName = $subNames[$subIndex];
            $fullSubName = $milestoneName . ' - ' . $subName;
            
            $hours = (int)trim($text);
            if ($hours <= 0) {
                $errorText = '❌ Введите положительное число дней.

Попробуйте ещё раз:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_cmr_milestone_' . $milestoneIndex . '_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Clear state
            $conversation->notes['state'] = null;
            $conversation->update();
            
            // Check if milestone exists
            $stmt = $conn->prepare("
                SELECT id FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $fullSubName);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                $stmt = $conn->prepare("
                    UPDATE project_milestones
                    SET planned_hours = ?, is_not_applicable = 0, unit = 'days'
                    WHERE project_code = ? AND work_type = 'СМР' AND milestone_name = ?
                ");
                $stmt->bind_param('iss', $hours, $projectCode, $fullSubName);
            } else {
                // Ensure project_cards record exists (FK constraint)
                $this->ensureProjectCardExists($projectCode, $userId);
                
                $stmt = $conn->prepare("
                    INSERT INTO project_milestones (project_code, work_type, milestone_name, planned_hours, is_not_applicable, unit)
                    VALUES (?, 'СМР', ?, ?, 0, 'days')
                ");
                $stmt->bind_param('ssi', $projectCode, $fullSubName, $hours);
            }
            $stmt->execute();
            
            $successText = '✅ <b>Трудозатраты установлены</b>

';
            $successText .= '<b>Веха:</b> ' . $milestoneName . '
';
            $successText .= '<b>Подвеха:</b> ' . $subName . '
';
            $successText .= '<b>План:</b> ' . $hours . ' дней';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К подвехам', 'callback_data' => 'proj_cmr_milestone_' . $milestoneIndex . '_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleCMRMilestoneHoursInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Set CMR milestone as not applicable
     */
    private function setCMRMilestoneNotApplicable(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getCMRMilestoneNames();
            $subNames = $this->getCMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($milestoneNames[$milestoneIndex]) || !isset($subNames[$subIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            $subName = $subNames[$subIndex];
            $fullSubName = $milestoneName . ' - ' . $subName;
            
            // Check if milestone exists
            $stmt = $conn->prepare("
                SELECT id FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $fullSubName);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                $stmt = $conn->prepare("
                    UPDATE project_milestones
                    SET planned_hours = NULL, is_not_applicable = 1
                    WHERE project_code = ? AND work_type = 'СМР' AND milestone_name = ?
                ");
                $stmt->bind_param('ss', $projectCode, $fullSubName);
            } else {
                // Ensure project_cards record exists (FK constraint)
                $this->ensureProjectCardExists($projectCode, $userId);
                
                $stmt = $conn->prepare("
                    INSERT INTO project_milestones (project_code, work_type, milestone_name, planned_hours, is_not_applicable, unit)
                    VALUES (?, 'СМР', ?, NULL, 1, 'days')
                ");
                $stmt->bind_param('ss', $projectCode, $fullSubName);
            }
            $stmt->execute();
            
            $successText = '✅ <b>Статус установлен</b>

';
            $successText .= '<b>Веха:</b> ' . $milestoneName . '
';
            $successText .= '<b>Подвеха:</b> ' . $subName . '
';
            $successText .= '<b>Статус:</b> Не предусмотрено проектом';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К подвехам', 'callback_data' => 'proj_cmr_milestone_' . $milestoneIndex . '_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::setCMRMilestoneNotApplicable error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear CMR milestone
     */
    private function clearCMRMilestone(string $chatId, int $userId, string $projectCode, int $milestoneIndex, int $subIndex): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $milestoneNames = $this->getCMRMilestoneNames();
            $subNames = $this->getCMRSubMilestoneNames($milestoneIndex);
            
            if (!isset($milestoneNames[$milestoneIndex]) || !isset($subNames[$subIndex])) {
                return $this->messageService->sendMessage($chatId, '❌ Подвеха не найдена');
            }
            
            $milestoneName = $milestoneNames[$milestoneIndex];
            $subName = $subNames[$subIndex];
            $fullSubName = $milestoneName . ' - ' . $subName;
            
            // Delete milestone record
            $stmt = $conn->prepare("
                DELETE FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР' AND milestone_name = ?
            ");
            $stmt->bind_param('ss', $projectCode, $fullSubName);
            $stmt->execute();
            
            $successText = '✅ <b>Данные очищены</b>

';
            $successText .= '<b>Веха:</b> ' . $milestoneName . '
';
            $successText .= '<b>Подвеха:</b> ' . $subName;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К подвехам', 'callback_data' => 'proj_cmr_milestone_' . $milestoneIndex . '_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearCMRMilestone error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show edit CMR mobilization screen
     */
    private function showEditCMRMobilization(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get current mobilization value
            $stmt = $conn->prepare("
                SELECT cmr_mobilization
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $text = '🚗 <b>Мобилизационные мероприятия (СМР)</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            
            if ($cardData && $cardData['cmr_mobilization'] !== null) {
                $text .= '📊 <b>Текущее значение:</b> ' . $cardData['cmr_mobilization'] . ' дней';
            } else {
                $text .= '❓ <b>Статус:</b> Не настроено';
            }
            
            $keyboard = [];
            
            if ($cardData && $cardData['cmr_mobilization'] !== null) {
                $keyboard[] = [
                    ['text' => '✏️ Изменить трудозатраты', 'callback_data' => 'proj_cmr_mob_set_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_cmr_mob_clear_' . $projectCode]
                ];
            } else {
                $keyboard[] = [
                    ['text' => '➕ Установить трудозатраты', 'callback_data' => 'proj_cmr_mob_set_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditCMRMobilization error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user for CMR mobilization input
     */
    private function promptCMRMobilization(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_cmr_mobilization';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '🚗 <b>Мобилизационные мероприятия (СМР)</b>

';
        $text .= '<b>Проект:</b> ' . $projectCode . '

';
        $text .= 'Введите плановые трудозатраты (в днях):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_cmr_mobilization_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle CMR mobilization input
     */
    public function handleCMRMobilizationInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
            }
            
            $days = (int)trim($text);
            if ($days <= 0) {
                $errorText = '❌ Введите положительное число дней.

Попробуйте ещё раз:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_cmr_mobilization_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Clear state
            $conversation->notes['state'] = null;
            $conversation->update();
            
            // Check if project_cards record exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                $stmt = $conn->prepare("UPDATE project_cards SET cmr_mobilization = ? WHERE project_code = ?");
                $stmt->bind_param('is', $days, $projectCode);
            } else {
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, cmr_mobilization) VALUES (?, ?)");
                $stmt->bind_param('si', $projectCode, $days);
            }
            $stmt->execute();
            
            $successText = '✅ <b>Мобилизация установлена</b>

';
            $successText .= '<b>Проект:</b> ' . $projectCode . '
';
            $successText .= '<b>План:</b> ' . $days . ' дней';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode . '_cmr']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleCMRMobilizationInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear CMR mobilization
     */
    private function clearCMRMobilization(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("UPDATE project_cards SET cmr_mobilization = NULL WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Мобилизация очищена</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode . '_cmr']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearCMRMobilization error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle CMR forecast enabled/disabled for a project
     */
    private function toggleCMRForecast(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Check if project_cards record exists
            $stmt = $conn->prepare("SELECT id, cmr_forecast_enabled FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                // Toggle: flip the current value
                $stmt = $conn->prepare("UPDATE project_cards SET cmr_forecast_enabled = NOT cmr_forecast_enabled WHERE project_code = ?");
                $stmt->bind_param('s', $projectCode);
            } else {
                // No record yet - create with forecast disabled (0), since default is enabled (1)
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, created_by, cmr_forecast_enabled) VALUES (?, ?, 0)");
                $stmt->bind_param('si', $projectCode, $userId);
            }
            $stmt->execute();
            
            // Return to CMR milestones menu to show updated state
            return $this->showCMRMilestonesMenu($chatId, $userId, $projectCode);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::toggleCMRForecast error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Show edit CMR project activity screen
     */
    private function showEditCMRProjectActivity(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get current project activity value
            $stmt = $conn->prepare("
                SELECT cmr_project_activity
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $text = '📂 <b>Доп. работы без плана (СМР)</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode . '

';
            
            if ($cardData && $cardData['cmr_project_activity'] !== null) {
                $text .= '📊 <b>Текущее значение:</b> ' . $cardData['cmr_project_activity'] . ' дней';
            } else {
                $text .= '❓ <b>Статус:</b> Не настроено';
            }
            
            $keyboard = [];
            
            if ($cardData && $cardData['cmr_project_activity'] !== null) {
                $keyboard[] = [
                    ['text' => '✏️ Изменить трудозатраты', 'callback_data' => 'proj_cmr_projact_set_' . $projectCode]
                ];
                $keyboard[] = [
                    ['text' => '🗑️ Очистить', 'callback_data' => 'proj_cmr_projact_clear_' . $projectCode]
                ];
            } else {
                $keyboard[] = [
                    ['text' => '➕ Установить трудозатраты', 'callback_data' => 'proj_cmr_projact_set_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showEditCMRProjectActivity error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user for CMR project activity input
     */
    private function promptCMRProjectActivity(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_cmr_project_activity';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '📂 <b>Доп. работы без плана (СМР)</b>

';
        $text .= '<b>Проект:</b> ' . $projectCode . '

';
        $text .= 'Введите плановые трудозатраты (в днях):';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_cmr_project_activity_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle CMR project activity input
     */
    public function handleCMRProjectActivityInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
            }
            
            $days = (int)trim($text);
            if ($days <= 0) {
                $errorText = '❌ Введите положительное число дней.

Попробуйте ещё раз:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_cmr_project_activity_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Clear state
            $conversation->notes['state'] = null;
            $conversation->update();
            
            // Check if project_cards record exists
            $stmt = $conn->prepare("SELECT id FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing) {
                $stmt = $conn->prepare("UPDATE project_cards SET cmr_project_activity = ? WHERE project_code = ?");
                $stmt->bind_param('is', $days, $projectCode);
            } else {
                $stmt = $conn->prepare("INSERT INTO project_cards (project_code, cmr_project_activity) VALUES (?, ?)");
                $stmt->bind_param('si', $projectCode, $days);
            }
            $stmt->execute();
            
            $successText = '✅ <b>Доп. работы установлены</b>

';
            $successText .= '<b>Проект:</b> ' . $projectCode . '
';
            $successText .= '<b>План:</b> ' . $days . ' дней';
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode . '_cmr']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleCMRProjectActivityInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Clear CMR project activity
     */
    private function clearCMRProjectActivity(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            $stmt = $conn->prepare("UPDATE project_cards SET cmr_project_activity = NULL WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Доп. работы очищены</b>

';
            $text .= '<b>Проект:</b> ' . $projectCode;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К списку вех', 'callback_data' => 'proj_cmr_back_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode . '_cmr']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::clearCMRProjectActivity error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Show development placeholder for modules under development
     */
    private function showDevelopmentPlaceholder(string $chatId, string $moduleName): ServerResponse
    {
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pm_menu']
            ]
        ];
        
        $icons = [
            'Моя активность' => '📊',
            'Карточка проекта' => '🗂️',
            'Отчёт по проекту' => '📋'
        ];
        
        $icon = $icons[$moduleName] ?? '📄';
        
        $text = "{$icon} <b>{$moduleName}</b>

🚧 Данный модуль находится в разработке.";
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Get user activity statistics for the activity dashboard
     */
    private function getUserActivityStatistics(int $userId): array
    {
        // Get user's timesheet entries
        $allEntries = $this->databaseService->getUserLastTabelEntries($userId, 1000); // Get many entries for analysis
        
        // Initialize statistics
        $stats = [
            'total_entries' => count($allEntries),
            'last_30_days' => 0,
            'this_week' => 0,
            'milestones' => 0,
            'unique_projects' => 0,
            'streak_days' => 0,
            'most_active_project' => null,
            'favorite_activity' => null
        ];
        
        if (empty($allEntries)) {
            return $stats;
        }
        
        // Calculate time-based statistics
        $now = time();
        $thirtyDaysAgo = $now - (30 * 24 * 60 * 60);
        $weekStart = strtotime('last Monday', $now);
        
        $projects = [];
        $activities = [];
        $dailyActivity = [];
        
        foreach ($allEntries as $entry) {
            $entryTime = strtotime($entry['dt']);
            $entryDate = date('Y-m-d', $entryTime);
            
            // Count entries in last 30 days
            if ($entryTime >= $thirtyDaysAgo) {
                $stats['last_30_days']++;
            }
            
            // Count entries this week
            if ($entryTime >= $weekStart) {
                $stats['this_week']++;
            }
            
            // Track daily activity for streak calculation
            $dailyActivity[$entryDate] = true;
            
            // Extract project and activity info from message
            $message = $entry['message'];
            
            // Count milestones (entries containing 'веха' or 'этап')
            if (stripos($message, 'вех') !== false || stripos($message, 'этап') !== false) {
                $stats['milestones']++;
            }
            
            // Extract project codes (assuming format like ОИ834, БКНС115)
            if (preg_match('/(ОИ\d+|БКНС\d+)/u', $message, $matches)) {
                $projectCode = $matches[1];
                if (!isset($projects[$projectCode])) {
                    $projects[$projectCode] = 0;
                }
                $projects[$projectCode]++;
            }
            
            // Extract activity types (simple pattern matching)
            if (preg_match('/о (.*?) добавлена/u', $message, $matches)) {
                $activityName = trim($matches[1]);
                if (!isset($activities[$activityName])) {
                    $activities[$activityName] = 0;
                }
                $activities[$activityName]++;
            }
        }
        
        // Calculate unique projects
        $stats['unique_projects'] = count($projects);
        
        // Find most active project
        if (!empty($projects)) {
            arsort($projects);
            $topProject = array_keys($projects)[0];
            $stats['most_active_project'] = [
                'code' => $topProject,
                'count' => $projects[$topProject]
            ];
        }
        
        // Find favorite activity
        if (!empty($activities)) {
            arsort($activities);
            $topActivity = array_keys($activities)[0];
            $stats['favorite_activity'] = [
                'name' => $topActivity,
                'count' => $activities[$topActivity]
            ];
        }
        
        // Calculate activity streak
        $stats['streak_days'] = $this->calculateActivityStreak($dailyActivity);
        
        return $stats;
    }
    
    /**
     * Calculate consecutive days with activity
     */
    private function calculateActivityStreak(array $dailyActivity): int
    {
        if (empty($dailyActivity)) {
            return 0;
        }
        
        $streak = 0;
        $currentDate = date('Y-m-d');
        
        // Count backwards from today
        for ($i = 0; $i < 365; $i++) { // Max 1 year lookback
            $checkDate = date('Y-m-d', strtotime("-{$i} days"));
            
            if (isset($dailyActivity[$checkDate])) {
                $streak++;
            } else {
                break; // Streak broken
            }
        }
        
        return $streak;
    }
    
    /**
     * Get emoji for milestone achievements
     */
    private function getMilestoneAchievementEmoji(int $milestones): string
    {
        if ($milestones >= 50) return '🏆'; // Trophy
        if ($milestones >= 25) return '🥇'; // Gold medal
        if ($milestones >= 10) return '🥈'; // Silver medal  
        if ($milestones >= 5) return '🥉';  // Bronze medal
        if ($milestones >= 1) return '⭐';      // Star
        return '🎁'; // Gift (for first milestone)
    }
    
    /**
     * Get emoji for project diversity
     */
    private function getProjectDiversityEmoji(int $projects): string
    {
        if ($projects >= 10) return '🌍'; // World
        if ($projects >= 5) return '🏢';  // Building  
        if ($projects >= 3) return '📁';  // Folder
        if ($projects >= 2) return '💼';  // Briefcase
        return '📄'; // Document
    }
    
    /**
     * Get emoji for activity streak
     */
    private function getStreakEmoji(int $days): string
    {
        if ($days >= 30) return '🔥'; // Fire
        if ($days >= 14) return '⚡';      // Lightning
        if ($days >= 7) return '🎢';  // Dart
        if ($days >= 3) return '🚀';  // Rocket
        return '🌟'; // Glowing star
    }
    
    /**
     * Show user's last entries with complete information
     */
    private function showLastEntries(string $chatId, int $userId): ServerResponse
    {
        $entries = $this->databaseService->getUserLastTabelEntries($userId, 15);
        
        $text = "📅 <b>Последние записи в табеле</b>\n\n";
        
        if (empty($entries)) {
            $text .= "📭 У вас пока нет записей в табеле.";
        } else {
            $text .= "📋 <b>Последние " . count($entries) . " записей:</b>\n\n";
            
            foreach ($entries as $index => $entry) {
                $date = date('d.m.Y', strtotime($entry['dt']));
                $time = date('H:i', strtotime($entry['dt']));
                
                // Get the complete message without truncation
                $message = $entry['message'];
                
                // Remove HTML tags for cleaner display but keep the content
                $cleanMessage = strip_tags($message);
                
                // Format entry with complete information
                $entryNumber = $index + 1;
                $text .= "<b>{$entryNumber}.</b> 🕐 <b>{$date} в {$time}</b>\n";
                $text .= "📝 {$cleanMessage}\n\n";
            }
            
            // Add helpful verification information
            $text .= "✅ <b>Как проверить правильность записи:</b>\n";
            $text .= "• 📅 Дата и время соответствуют фактической активности\n";
            $text .= "• 🏷️ Название проекта указано корректно\n";
            $text .= "• ⚙️ Вид выполненной активности соответствует реальности\n";
            $text .= "• ✅ Статус 'добавлена' означает успешное сохранение";
        }
        
        $keyboard = [
            [
                ['text' => '🔄 Обновить список', 'callback_data' => 'category_Проекты_ПоследниеЗаписи']
            ],
            [
                ['text' => '⬅️ Назад к активности', 'callback_data' => 'category_Проекты_МояАктивность']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show detailed statistics
     */
    private function showDetailedStatistics(string $chatId, int $userId): ServerResponse
    {
        $stats = $this->getUserActivityStatistics($userId);
        
        $text = "📈 <b>Детальная статистика</b>

";
        
        // Time period analysis
        $text .= "📅 <b>Анализ по периодам:</b>
";
        $text .= "• Всего записей: <b>{$stats['total_entries']}</b>
";
        $text .= "• За 30 дней: <b>{$stats['last_30_days']}</b>
";
        $text .= "• За неделю: <b>{$stats['this_week']}</b>
";
        
        // Activity metrics
        if ($stats['total_entries'] > 0) {
            $dailyAvg = round($stats['last_30_days'] / 30, 1);
            $text .= "• Среднее в день: <b>{$dailyAvg}</b>
";
        }
        
        $text .= "\n🏆 <b>Достижения:</b>
";
        $text .= "• Подтверждено вех: <b>{$stats['milestones']}</b>
";
        $text .= "• Проектов в работе: <b>{$stats['unique_projects']}</b>
";
        $text .= "• Дней подряд с активностью: <b>{$stats['streak_days']}</b>
";
        
        // Performance indicators  
        $text .= "\n📊 <b>Показатели эффективности:</b>
";
        
        // Calculate efficiency rating
        $efficiency = 0;
        if ($stats['total_entries'] > 50) $efficiency += 2;
        elseif ($stats['total_entries'] > 20) $efficiency += 1;
        
        if ($stats['milestones'] > 10) $efficiency += 2;
        elseif ($stats['milestones'] > 3) $efficiency += 1;
        
        if ($stats['streak_days'] > 7) $efficiency += 2;
        elseif ($stats['streak_days'] > 3) $efficiency += 1;
        
        $efficiencyText = match($efficiency) {
            0, 1 => '🌱 Новичок',
            2, 3 => '🚀 Развивающийся',
            4, 5 => '⭐ Опытный',
            default => '🏆 Мастер'
        };
        
        $text .= "• Уровень: {$efficiencyText} ({$efficiency}/6)
";
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к активности', 'callback_data' => 'category_Проекты_МояАктивность']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show achievements preview
     */
    private function showAchievements(string $chatId, int $userId): ServerResponse
    {
        $stats = $this->getUserActivityStatistics($userId);
        
        $text = "🏆 <b>Мои достижения</b>

";
        
        // Current achievements
        $text .= "✨ <b>Полученные достижения:</b>
";
        
        $achievements = [];
        
        // Entry count achievements
        if ($stats['total_entries'] >= 100) {
            $achievements[] = '🏆 Мастер табеля (100+ записей)';
        } elseif ($stats['total_entries'] >= 50) {
            $achievements[] = '🥇 Опытный сотрудник (50+ записей)';
        } elseif ($stats['total_entries'] >= 10) {
            $achievements[] = '🥈 Первые шаги (10+ записей)';
        }
        
        // Milestone achievements
        if ($stats['milestones'] >= 20) {
            $achievements[] = '🎆 Легенда вех (20+ вех)';
        } elseif ($stats['milestones'] >= 10) {
            $achievements[] = '🎉 Достижитель вех! (10+ вех)';
        } elseif ($stats['milestones'] >= 5) {
            $achievements[] = '⭐ Покоритель вех (5+ вех)';
        } elseif ($stats['milestones'] >= 1) {
            $achievements[] = '🎁 Первая веха!';
        }
        
        // Project diversity achievements
        if ($stats['unique_projects'] >= 10) {
            $achievements[] = '🌍 Мастер многозадачности (10+ проектов)';
        } elseif ($stats['unique_projects'] >= 5) {
            $achievements[] = '🏢 Многопрофильный (5+ проектов)';
        } elseif ($stats['unique_projects'] >= 3) {
            $achievements[] = '📁 Параллельные проекты (3+ проекта)';
        }
        
        // Streak achievements
        if ($stats['streak_days'] >= 30) {
            $achievements[] = '🔥 Неугасимое пламя (30+ дней)';
        } elseif ($stats['streak_days'] >= 14) {
            $achievements[] = '⚡ Молниеносная активность (14+ дней)';
        } elseif ($stats['streak_days'] >= 7) {
            $achievements[] = '🚀 Недельная активность';
        }
        
        if (empty($achievements)) {
            $text .= "🌱 Пока никаких достижений... Но вы только начинаете!
";
        } else {
            foreach ($achievements as $achievement) {
                $text .= "{$achievement}
";
            }
        }
        
        $text .= "\n🎨 <b>Будущие возможности:</b>
";
        $text .= "• Система рангов с видимыми значками
";
        $text .= "• Соревнования и лидерборды
";
        $text .= "• Специальные достижения за командную работу
";
        $text .= "• Подарки и поощрения";
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к активности', 'callback_data' => 'category_Проекты_МояАктивность']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Export user activity to text format
     */
    private function exportUserActivity(string $chatId, int $userId): ServerResponse
    {
        // Get all user entries
        $entries = $this->databaseService->getAllUserTabelEntries($userId);
        
        if (empty($entries)) {
            $text = "💭 У вас пока нет записей для экспорта.";
        } else {
            // Create export content
            $exportText = "ОТЧЁТ ПО АКТИВНОСТИ ПОЛЬЗОВАТЕЛЯ
";
            $exportText .= "Генерация отчёта: " . date('d.m.Y H:i') . "\n";
            $exportText .= "Всего записей: " . count($entries) . "\n\n";
            
            foreach ($entries as $entry) {
                $date = date('d.m.Y H:i', strtotime($entry['dt']));
                $message = strip_tags($entry['message']);
                $exportText .= "{$date}: {$message}\n";
            }
            
            // Send as a file (simplified version - just send as message for now)
            $text = "📋 <b>Экспорт активности</b>

";
            $text .= "Ваш отчёт готов! Содержит <b>" . count($entries) . "</b> записей.

";
            $text .= "Первые 5 записей:
";
            
            $previewEntries = array_slice($entries, 0, 5);
            foreach ($previewEntries as $entry) {
                $date = date('d.m.Y', strtotime($entry['dt']));
                $message = strip_tags($entry['message']);
                if (strlen($message) > 80) {
                    $message = substr($message, 0, 80) . '...';
                }
                $text .= "• {$date}: {$message}
";
            }
            
            if (count($entries) > 5) {
                $text .= "\n... и ещё " . (count($entries) - 5) . " записей";
            }
        }
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к активности', 'callback_data' => 'category_Проекты_МояАктивность']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show report projects management menu
     */
    private function showReportProjectsMenu(string $chatId, int $userId): ServerResponse
    {
        // Get user's personal report projects list
        $personalProjects = $this->getUserReportProjects($userId);
        
        // Debug logging
        error_log("ProjectHandler: showReportProjectsMenu for user {$userId}");
        error_log("ProjectHandler: user has " . count($personalProjects) . " report projects");
        error_log("ProjectHandler: user projects: " . json_encode($personalProjects));
        
        $text = '📋 <b>Проекты для отчёта</b>

Здесь вы можете составить личный список проектов, который будет отображаться при формировании отчётов.

';
        
        if (empty($personalProjects)) {
            $text .= 'У вас пока нет проектов в личном списке.

';
            error_log("ProjectHandler: No personal projects found for user {$userId}");
        } else {
            $text .= 'Ваши проекты для отчёта:
';
            foreach ($personalProjects as $project) {
                $text .= "• {$project}
";
                error_log("ProjectHandler: Added project to display: {$project}");
            }
            $text .= '
';
        }
        
        // Get all available projects from admin system
        $allProjects = $this->adminService->getAllProjects();
        
        $keyboard = [];
        
        if (!empty($allProjects)) {
            $keyboard[] = [
                ['text' => '➕ Добавить проект', 'callback_data' => 'proj_list_add']
            ];
        }
        
        if (!empty($personalProjects)) {
            $keyboard[] = [
                ['text' => '➖ Удалить проект', 'callback_data' => 'proj_list_remove']
            ];
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Назад к моим проектам', 'callback_data' => 'category_Проекты_МоиПроекты']
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show control projects management menu - FULL IMPLEMENTATION
     */
    private function showControlProjectsMenu(string $chatId, int $userId): ServerResponse
    {
        // UNIQUE DEBUG MARKER - 2025-09-17-FIXED
        error_log("ProjectHandler: FIXED VERSION 2025-09-17 showControlProjectsMenu called for user {$userId}");
        
        // Get user's control projects list with error handling
        try {
            $controlProjects = $this->getUserControlProjects($userId);
            error_log("ProjectHandler: Successfully got control projects via method call");
        } catch (\Error $e) {
            error_log("ProjectHandler: Error getting control projects: " . $e->getMessage());
            // Fallback: get control projects directly
            $userData = $this->databaseService->getUserData($userId);
            $controlProjects = $userData['control_projects'] ?? [];
            error_log("ProjectHandler: Using fallback method to get control projects");
        }
        
        // Debug logging
        error_log("ProjectHandler: showControlProjectsMenu for user {$userId}");
        error_log("ProjectHandler: user has " . count($controlProjects) . " projects on control");
        
        $text = '🔍 <b>Проекты на контроле</b>

Здесь вы можете настроить личный список проектов, копии отчётов по которым будут автоматически направлены в определённые группы и чаты Telegram.

';
        
        if (empty($controlProjects)) {
            $text .= 'У вас пока нет проектов на контроле.

';
        } else {
            $text .= 'Ваши проекты на контроле:
';
            foreach ($controlProjects as $projectCode => $settings) {
                $text .= "• <b>{$projectCode}</b>";
                if (isset($settings['group_id']) && !empty($settings['group_id'])) {
                    $text .= " → Группа: {$settings['group_id']}";
                }
                if (isset($settings['chat_id']) && !empty($settings['chat_id'])) {
                    $text .= " → Чат: {$settings['chat_id']}";
                }
                $text .= "\n";
            }
            $text .= "\n";
        }
        
        // Get all available projects from admin system
        $allProjects = $this->adminService->getAllProjects();
        
        $keyboard = [];
        
        // Короткие названия кнопок для лучшей совместимости
        if (!empty($allProjects)) {
            $keyboard[] = [
                ['text' => '➕ Добавить проект', 'callback_data' => 'proj_ctrl_add']
            ];
            error_log("ProjectHandler: added 'Add project' button");
        }
        
        if (!empty($controlProjects)) {
            $keyboard[] = [
                ['text' => '➖ Удалить проект', 'callback_data' => 'proj_ctrl_remove']
            ];
            $keyboard[] = [
                ['text' => '⚙️ Настройки', 'callback_data' => 'proj_ctrl_settings']
            ];
            error_log("ProjectHandler: added 'Remove' and 'Settings' buttons");
        } else {
            error_log("ProjectHandler: no control projects, skipping Remove/Settings buttons");
        }
        
        $keyboard[] = [
            ['text' => '📋 Список проектов', 'callback_data' => 'proj_ctrl_list']
        ];
        
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'pm_menu']
        ];
        
        error_log("ProjectHandler: keyboard has " . count($keyboard) . " rows");
        foreach ($keyboard as $rowIndex => $row) {
            foreach ($row as $button) {
                error_log("ProjectHandler: button '{$button['text']}' -> '{$button['callback_data']}'");
            }
        }
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Handle report projects callbacks (add/remove)
     */
    private function handleReportProjectsCallback(CallbackQuery $callbackQuery, string $callbackData, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        if ($callbackData === 'proj_list_add') {
            return $this->showAddReportProjectMenu($chatId, $userId, 1);
        }
        
        // Handle pagination for add report project menu
        if (str_starts_with($callbackData, 'proj_list_add_page_')) {
            $page = (int)str_replace('proj_list_add_page_', '', $callbackData);
            return $this->showAddReportProjectMenu($chatId, $userId, $page);
        }
        
        if ($callbackData === 'proj_list_remove') {
            return $this->showRemoveReportProjectMenu($chatId, $userId);
        }
        
        // Handle specific add/remove actions
        if (str_starts_with($callbackData, 'proj_list_add_')) {
            $projectCode = str_replace('proj_list_add_', '', $callbackData);
            return $this->addProjectToReportList($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_list_remove_')) {
            $projectCode = str_replace('proj_list_remove_', '', $callbackData);
            return $this->removeProjectFromReportList($chatId, $userId, $projectCode);
        }
        
        $text = 'Неизвестная команда управления списком проектов для отчёта';
        return $this->messageService->sendMessage($chatId, $text);
    }

    /**
     * Handle control projects callbacks - FULL IMPLEMENTATION
     */
    private function handleControlProjectsCallback(CallbackQuery $callbackQuery, string $callbackData, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        if ($callbackData === 'proj_ctrl_add') {
            return $this->showAddControlProjectMenu($chatId, $userId);
        }
        
        if ($callbackData === 'proj_ctrl_remove') {
            return $this->showRemoveControlProjectMenu($chatId, $userId);
        }
        
        if ($callbackData === 'proj_ctrl_list') {
            return $this->showControlProjectsList($chatId, $userId);
        }
        
        if ($callbackData === 'proj_ctrl_settings') {
            return $this->showControlProjectSettings($chatId, $userId);
        }
        
        // Handle settings for specific projects
        if (str_starts_with($callbackData, 'proj_ctrl_set_')) {
            $projectCode = str_replace('proj_ctrl_set_', '', $callbackData);
            return $this->showProjectControlSettings($chatId, $userId, $projectCode);
        }
        
        // Handle specific add/remove actions
        if (str_starts_with($callbackData, 'proj_ctrl_add_')) {
            $projectCode = str_replace('proj_ctrl_add_', '', $callbackData);
            return $this->addProjectToControlList($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_ctrl_remove_')) {
            $projectCode = str_replace('proj_ctrl_remove_', '', $callbackData);
            return $this->removeProjectFromControlList($chatId, $userId, $projectCode);
        }
        
        // Handle new configuration callbacks
        if (str_starts_with($callbackData, 'proj_ctrl_cfg_')) {
            return $this->handleConfigurationCallbacks($callbackQuery, $callbackData, $conversation);
        }
        
        // Handle test notifications
        if (str_starts_with($callbackData, 'proj_ctrl_test_')) {
            $projectCode = str_replace('proj_ctrl_test_', '', $callbackData);
            return $this->sendTestNotification($chatId, $userId, $projectCode);
        }
        
        // Handle group selection callbacks
        if (str_starts_with($callbackData, 'proj_ctrl_group_select_')) {
            $remaining = str_replace('proj_ctrl_group_select_', '', $callbackData);
            $parts = explode('_', $remaining, 2); // Split into max 2 parts
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $groupId = $parts[1];
                return $this->setProjectGroup($chatId, $userId, $projectCode, $groupId);
            }
        }
        
        // Handle chat selection callbacks  
        if (str_starts_with($callbackData, 'proj_ctrl_chat_select_')) {
            $remaining = str_replace('proj_ctrl_chat_select_', '', $callbackData);
            $parts = explode('_', $remaining, 2); // Split into max 2 parts
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $chatType = $parts[1];
                return $this->setProjectChat($chatId, $userId, $projectCode, $chatType);
            }
        }
        
        // Handle notification type toggle
        if (str_starts_with($callbackData, 'proj_ctrl_toggle_type_')) {
            $remaining = str_replace('proj_ctrl_toggle_type_', '', $callbackData);
            $parts = explode('_', $remaining, 2); // Split into max 2 parts
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $notificationType = $parts[1];
                return $this->toggleNotificationType($chatId, $userId, $projectCode, $notificationType);
            }
        }
        
        // Handle notification mode selection
        if (str_starts_with($callbackData, 'proj_ctrl_mode_select_')) {
            $remaining = str_replace('proj_ctrl_mode_select_', '', $callbackData);
            $parts = explode('_', $remaining, 2); // Split into max 2 parts
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $mode = $parts[1];
                return $this->setNotificationMode($chatId, $userId, $projectCode, $mode);
            }
        }
        
        // Handle manual group ID input
        if (str_starts_with($callbackData, 'proj_ctrl_group_manual_')) {
            $projectCode = str_replace('proj_ctrl_group_manual_', '', $callbackData);
            return $this->showManualGroupInput($chatId, $userId, $projectCode);
        }
        
        // Handle manual chat ID input
        if (str_starts_with($callbackData, 'proj_ctrl_chat_manual_')) {
            $projectCode = str_replace('proj_ctrl_chat_manual_', '', $callbackData);
            return $this->showManualChatInput($chatId, $userId, $projectCode);
        }

        $text = 'Неизвестная команда управления проектами на контроле';
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к проектам на контроле', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show menu to add project to report list
     */
    private function showAddReportProjectMenu(string $chatId, int $userId, int $page = 1): ServerResponse
    {
        // Create a FRESH DatabaseService to avoid stale cached data
        $freshDatabaseService = new DatabaseService();
        
        // Use ProjectSelector with fresh database connection
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(
            $freshDatabaseService
        );
        $allProjects = $projectSelector->getAllProjectsIncludingPending();
        $userProjects = $this->getUserReportProjects($userId);
        
        // Normalize arrays before diff to avoid encoding issues with Cyrillic text
        $allProjects = array_values(array_map('trim', $allProjects));
        $userProjects = array_values(array_map('trim', $userProjects));
        
        // Filter out projects already in user's list
        $availableProjects = array_diff($allProjects, $userProjects);
        // Re-index array after diff
        $availableProjects = array_values($availableProjects);
        
        // CRITICAL: Apply intelligent sorting after filtering
        // This ensures projects remain sorted even after array_diff operation
        $availableProjects = $freshDatabaseService->sortProjectsIntelligently($availableProjects);
        
        if (empty($availableProjects)) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад к проектам для отчёта', 'callback_data' => 'category_Проекты_ПроектыДляОтчёта']
                ]
            ];
            
            $text = '📋 <b>Добавить проект для отчёта</b>

Все доступные проекты уже добавлены в ваш список.';
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // PAGINATION: 33 projects per page
        $perPage = 33;
        $totalProjects = count($availableProjects);
        $totalPages = (int)ceil($totalProjects / $perPage);
        
        // Check if page is valid
        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;
        
        // Get projects for current page
        $offset = ($page - 1) * $perPage;
        $projectsOnPage = array_slice($availableProjects, $offset, $perPage);
        
        $text = '📋 <b>Добавить проект для отчёта</b>

Выберите проект для добавления в ваш личный список:';
        $text .= "\nСтраница {$page} из {$totalPages} (Всего: {$totalProjects} проектов)";
        
        $keyboard = [];
        
        // Create buttons for available projects (3 per row)
        $row = [];
        foreach ($projectsOnPage as $index => $project) {
            $row[] = ['text' => $project, 'callback_data' => "proj_list_add_{$project}"];
            
            if (count($row) == 3 || $index == count($projectsOnPage) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        // Add pagination navigation buttons
        if ($totalPages > 1) {
            $navRow = [];
            
            if ($page > 1) {
                $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => "proj_list_add_page_" . ($page - 1)];
            }
            
            $navRow[] = ['text' => "📖 {$page}/{$totalPages}", 'callback_data' => 'noop'];
            
            if ($page < $totalPages) {
                $navRow[] = ['text' => '➡️ Вперед', 'callback_data' => "proj_list_add_page_" . ($page + 1)];
            }
            
            $keyboard[] = $navRow;
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Назад к проектам для отчёта', 'callback_data' => 'category_Проекты_ПроектыДляОтчёта']
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show menu to remove project from report list
     */
    private function showRemoveReportProjectMenu(string $chatId, int $userId): ServerResponse
    {
        $userProjects = $this->getUserReportProjects($userId);
        
        if (empty($userProjects)) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад к проектам для отчёта', 'callback_data' => 'category_Проекты_ПроектыДляОтчёта']
                ]
            ];
            
            $text = '📋 <b>Удалить проект из отчёта</b>

У вас нет проектов в личном списке для удаления.';
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Apply intelligent sorting to user's projects
        $userProjects = $this->databaseService->sortProjectsIntelligently($userProjects);
        
        $keyboard = [];
        
        // Create buttons for user's projects (3 per row)
        $row = [];
        foreach ($userProjects as $index => $project) {
            $row[] = ['text' => $project, 'callback_data' => "proj_list_remove_{$project}"];
            
            if (count($row) == 3 || $index == count($userProjects) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Назад к проектам для отчёта', 'callback_data' => 'category_Проекты_ПроектыДляОтчёта']
        ];
        
        $text = '📋 <b>Удалить проект из отчёта</b>

Выберите проект для удаления из вашего личного списка:';
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Get user's personal report projects list
     */
    private function getUserReportProjects(int $userId): array
    {
        $userData = $this->databaseService->getUserData($userId);
        $reportProjects = $userData['report_projects'] ?? [];
        
        // Debug logging
        error_log("ProjectHandler: getUserReportProjects for user {$userId}");
        error_log("ProjectHandler: raw user data: " . json_encode($userData));
        error_log("ProjectHandler: extracted report_projects: " . json_encode($reportProjects));
        
        return $reportProjects;
    }

    /**
     * Add project to user's report list
     */
    private function addProjectToReportList(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $reportProjects = $userData['report_projects'] ?? [];
        
        if (!in_array($projectCode, $reportProjects)) {
            $reportProjects[] = $projectCode;
            $userData['report_projects'] = $reportProjects;
            $this->databaseService->updateUserData($userId, $userData);
            
            $text = "✅ <b>Проект добавлен</b>

Проект <b>{$projectCode}</b> успешно добавлен в ваш список проектов для отчёта.";
        } else {
            $text = "ℹ️ <b>Проект уже в списке</b>

Проект <b>{$projectCode}</b> уже находится в вашем списке проектов для отчёта.";
        }
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к проектам для отчёта', 'callback_data' => 'category_Проекты_ПроектыДляОтчёта']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Remove project from user's report list
     */
    private function removeProjectFromReportList(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $reportProjects = $userData['report_projects'] ?? [];
        
        $key = array_search($projectCode, $reportProjects);
        if ($key !== false) {
            array_splice($reportProjects, $key, 1);
            $userData['report_projects'] = $reportProjects;
            $this->databaseService->updateUserData($userId, $userData);
            
            $text = "✅ <b>Проект удалён</b>

Проект <b>{$projectCode}</b> успешно удалён из вашего списка проектов для отчёта.";
        } else {
            $text = "ℹ️ <b>Проект не найден</b>

Проект <b>{$projectCode}</b> не найден в вашем списке проектов для отчёта.";
        }
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к проектам для отчёта', 'callback_data' => 'category_Проекты_ПроектыДляОтчёта']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Handle project report callbacks
     */
    private function handleProjectReportCallback(CallbackQuery $callbackQuery, string $callbackData, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Handle simple report
        if ($callbackData === 'proj_report_simple') {
            // Check export permission first
            $conversation->notes['export_resume_callback'] = 'proj_report_simple_after_key';
            $conversation->update();
            
            $permissionCheck = $this->checkExportPermission($chatId, $userId, $conversation, 'report');
            if ($permissionCheck !== null) {
                return $permissionCheck;
            }
            return $this->showSimpleReportStartDateMenu($chatId, $userId, $conversation);
        }
        
        // Resume simple report after export key verification
        if ($callbackData === 'proj_report_simple_after_key') {
            return $this->showSimpleReportStartDateMenu($chatId, $userId, $conversation);
        }
        
        // Handle by-project report
        if ($callbackData === 'proj_report_byproject') {
            // Check export permission first
            $conversation->notes['export_resume_callback'] = 'proj_report_byproject_after_key';
            $conversation->update();
            
            $permissionCheck = $this->checkExportPermission($chatId, $userId, $conversation, 'report');
            if ($permissionCheck !== null) {
                return $permissionCheck;
            }
            return $this->showByProjectReportMenu($chatId, $userId, 1);
        }
        
        // Resume by-project report after export key verification
        if ($callbackData === 'proj_report_byproject_after_key') {
            return $this->showByProjectReportMenu($chatId, $userId, 1);
        }
        
        // Handle pagination for by-project report
        if (str_starts_with($callbackData, 'proj_report_byproject_page_')) {
            $page = (int)str_replace('proj_report_byproject_page_', '', $callbackData);
            return $this->showByProjectReportMenu($chatId, $userId, $page);
        }
        
        // Handle notes export
        if ($callbackData === 'proj_report_notes') {
            // Check export permission first
            $conversation->notes['export_resume_callback'] = 'proj_report_notes_after_key';
            $conversation->update();
            
            $permissionCheck = $this->checkExportPermission($chatId, $userId, $conversation, 'notes');
            if ($permissionCheck !== null) {
                return $permissionCheck;
            }
            return $this->showNotesExportTypeMenu($chatId, $userId);
        }
        
        // Resume notes export after export key verification
        if ($callbackData === 'proj_report_notes_after_key') {
            return $this->showNotesExportTypeMenu($chatId, $userId);
        }
        
        // Handle notes export type selection
        if ($callbackData === 'proj_notes_type_all') {
            return $this->showNotesExportStatusFilterMenu($chatId, $userId, null, $conversation);
        }
        
        if ($callbackData === 'proj_notes_type_byproject') {
            return $this->showNotesExportProjectMenu($chatId, $userId, 1);
        }
        
        // Handle pagination for notes export project menu
        if (str_starts_with($callbackData, 'proj_notes_project_page_')) {
            $page = (int)str_replace('proj_notes_project_page_', '', $callbackData);
            return $this->showNotesExportProjectMenu($chatId, $userId, $page);
        }
        
        // Handle filter type selection
        if ($callbackData === 'proj_report_filter_by_fn') {
            $startDate = $conversation->notes['simple_report_start_date'] ?? '';
            $endDate = $conversation->notes['simple_report_end_date'] ?? '';
            return $this->showSimpleReportFnMenu($chatId, $userId, $conversation, $startDate, $endDate);
        }
        
        // Handle quick filters menu
        if ($callbackData === 'proj_report_filter_quick') {
            return $this->showSimpleReportQuickFilterMenu($chatId, $userId, $conversation);
        }
        
        // Handle back from filter selection
        if ($callbackData === 'proj_report_filter_back') {
            $startDate = $conversation->notes['simple_report_start_date'] ?? '';
            $endDate = $conversation->notes['simple_report_end_date'] ?? '';
            return $this->showSimpleReportFilterMenu($chatId, $userId, $conversation, $startDate, $endDate);
        }
        
        // Handle quick filter selections
        if (str_starts_with($callbackData, 'proj_report_quick_')) {
            $filterType = str_replace('proj_report_quick_', '', $callbackData);
            $startDate = $conversation->notes['simple_report_start_date'] ?? '';
            $endDate = $conversation->notes['simple_report_end_date'] ?? '';
            
            return $this->generateSimpleReportWithQuickFilter($chatId, $userId, $startDate, $endDate, $filterType);
        }
        
        // Handle functional direction selection for simple report
        if (str_starts_with($callbackData, 'proj_report_simple_fn_')) {
            $fn = str_replace('proj_report_simple_fn_', '', $callbackData);
            $startDate = $conversation->notes['simple_report_start_date'] ?? '';
            $endDate = $conversation->notes['simple_report_end_date'] ?? '';
            
            return $this->generateSimpleReport($chatId, $userId, $startDate, $endDate, $fn);
        }
        
        // Handle project selection for by-project report
        if (str_starts_with($callbackData, 'proj_report_select_')) {
            $projectCode = str_replace('proj_report_select_', '', $callbackData);
            return $this->showReportDateRangeMenu($chatId, $userId, $projectCode);
        }
        
        // Handle period selection for by-project report (now shows filter menu)
        if (str_starts_with($callbackData, 'proj_report_period_')) {
            $remaining = str_replace('proj_report_period_', '', $callbackData);
            $parts = explode('_', $remaining, 2);
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $period = $parts[1];
                return $this->showProjectReportFilterMenu($chatId, $userId, $projectCode, $period);
            }
        }
        
        // Handle project report filter type selection
        if (str_starts_with($callbackData, 'proj_report_prj_filter_')) {
            $remaining = str_replace('proj_report_prj_filter_', '', $callbackData);
            $parts = explode('_', $remaining, 2);
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $period = $parts[1];
                return $this->showProjectReportQuickFilterMenu($chatId, $userId, $projectCode, $period);
            }
        }
        
        // Handle back to filter menu from quick filters
        if (str_starts_with($callbackData, 'proj_report_back_to_filter_')) {
            $remaining = str_replace('proj_report_back_to_filter_', '', $callbackData);
            $parts = explode('_', $remaining, 2);
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $period = $parts[1];
                return $this->showProjectReportFilterMenu($chatId, $userId, $projectCode, $period);
            }
        }
        
        // Handle project report without filter
        if (str_starts_with($callbackData, 'proj_report_prj_nofilter_')) {
            $remaining = str_replace('proj_report_prj_nofilter_', '', $callbackData);
            $parts = explode('_', $remaining, 2);
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $period = $parts[1];
                return $this->generateProjectReport($chatId, $userId, $projectCode, $period, null);
            }
        }
        
        // Handle project report quick filter selection
        if (str_starts_with($callbackData, 'proj_report_prj_quick_')) {
            $remaining = str_replace('proj_report_prj_quick_', '', $callbackData);
            $parts = explode('_', $remaining, 3);
            if (count($parts) >= 3) {
                $projectCode = $parts[0];
                $period = $parts[1];
                $filterType = $parts[2];
                return $this->generateProjectReport($chatId, $userId, $projectCode, $period, $filterType);
            }
        }
        
        // Handle custom filter menu (MUST be before proj_report_custom_ to avoid conflict)
        if (str_starts_with($callbackData, 'proj_report_custom_filter_')) {
            $projectCode = str_replace('proj_report_custom_filter_', '', $callbackData);
            $startDate = $conversation->notes['project_report_start_date'] ?? null;
            $endDate = $conversation->notes['project_report_end_date'] ?? null;
            return $this->showProjectReportCustomQuickFilterMenu($chatId, $userId, $conversation, $projectCode);
        }
        
        // Handle back to custom filter menu from quick filters
        if (str_starts_with($callbackData, 'proj_report_custom_back_to_filter_')) {
            $projectCode = str_replace('proj_report_custom_back_to_filter_', '', $callbackData);
            $startDate = $conversation->notes['project_report_start_date'] ?? null;
            $endDate = $conversation->notes['project_report_end_date'] ?? null;
            
            if ($startDate && $endDate) {
                return $this->showProjectReportCustomFilterMenu($chatId, $userId, $conversation, $projectCode, $startDate, $endDate);
            } else {
                // If dates are lost, restart the flow
                return $this->showProjectReportStartDateMenu($chatId, $userId, $conversation, $projectCode);
            }
        }
        
        // Handle custom no filter
        if (str_starts_with($callbackData, 'proj_report_custom_nofilter_')) {
            $projectCode = str_replace('proj_report_custom_nofilter_', '', $callbackData);
            return $this->generateProjectReportCustom($chatId, $userId, $conversation, $projectCode, null);
        }
        
        // Handle custom quick filter selection
        if (str_starts_with($callbackData, 'proj_report_custom_quick_')) {
            $remaining = str_replace('proj_report_custom_quick_', '', $callbackData);
            $parts = explode('_', $remaining, 2);
            if (count($parts) >= 2) {
                $projectCode = $parts[0];
                $filterType = $parts[1];
                return $this->generateProjectReportCustom($chatId, $userId, $conversation, $projectCode, $filterType);
            }
        }
        
        // Handle custom date selection (MUST be last as it's most generic)
        if (str_starts_with($callbackData, 'proj_report_custom_')) {
            $projectCode = str_replace('proj_report_custom_', '', $callbackData);
            return $this->showProjectReportStartDateMenu($chatId, $userId, $conversation, $projectCode);
        }
        
        // Handle notes export project selection
        if (str_starts_with($callbackData, 'proj_notes_project_')) {
            $projectCode = str_replace('proj_notes_project_', '', $callbackData);
            return $this->showNotesExportStatusFilterMenu($chatId, $userId, $projectCode, $conversation);
        }
        
        // Handle notes export status filter selection - generate immediately
        if (str_starts_with($callbackData, 'proj_notes_generate_')) {
            $remaining = str_replace('proj_notes_generate_', '', $callbackData);
            $parts = explode('_', $remaining, 2);
            $statusFilter = $parts[0]; // active, closed, all
            $projectCode = $parts[1] ?? null; // null for general journal
            $projectCode = ($projectCode === 'all') ? null : $projectCode;
            
            // Generate export immediately for all period
            return $this->generateNotesExportAll($chatId, $userId, $conversation, $projectCode, $statusFilter);
        }
        
        // Handle notes export date range selection
        if (str_starts_with($callbackData, 'proj_notes_period_')) {
            $remaining = str_replace('proj_notes_period_', '', $callbackData);
            $parts = explode('_', $remaining); // [statusFilter, projectCode, period]
            if (count($parts) >= 3) {
                $statusFilter = $parts[0];
                $projectCode = $parts[1] === 'all' ? null : $parts[1];
                $period = $parts[2];
                return $this->generateNotesExport($chatId, $userId, $conversation, $projectCode, $statusFilter, $period);
            }
        }
        
        // Handle back from date range to status filter
        if (str_starts_with($callbackData, 'proj_notes_filter_back_')) {
            $projectCode = str_replace('proj_notes_filter_back_', '', $callbackData);
            $projectCode = $projectCode === 'all' ? null : $projectCode;
            return $this->showNotesExportStatusFilterMenu($chatId, $userId, $projectCode, $conversation);
        }
        
        // Handle notes export custom date selection
        if (str_starts_with($callbackData, 'proj_notes_custom_')) {
            $remaining = str_replace('proj_notes_custom_', '', $callbackData);
            $parts = explode('_', $remaining, 2);
            if (count($parts) >= 2) {
                $statusFilter = $parts[0];
                $projectCode = $parts[1] === 'all' ? null : $parts[1];
                
                // Save to conversation
                $conversation->notes['notes_export_status_filter'] = $statusFilter;
                $conversation->notes['notes_export_project_code'] = $projectCode;
                $conversation->update();
                
                return $this->showNotesExportStartDateMenu($chatId, $userId, $conversation, $projectCode, $statusFilter);
            }
        }
        
        $text = 'Неизвестная команда отчёта по проекту';
        return $this->messageService->sendMessage($chatId, $text);
    }

    // ========================================
    // CONTROL PROJECTS METHODS - NEW
    // ========================================

    /**
     * Get user's personal control projects list
     */
    private function getUserControlProjects(int $userId): array
    {
        $userData = $this->databaseService->getUserData($userId);
        return $userData['control_projects'] ?? [];
    }

    /**
     * Show menu to add project to control list
     */
    private function showAddControlProjectMenu(string $chatId, int $userId): ServerResponse
    {
        // Use ProjectSelector to get all projects including pending ones
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(
            $this->databaseService
        );
        $allProjects = $projectSelector->getAllProjectsIncludingPending();
        $userControlProjects = $this->getUserControlProjects($userId);
        $availableProjects = array_diff($allProjects, array_keys($userControlProjects));
        
        // Apply intelligent sorting after filtering
        $availableProjects = $this->databaseService->sortProjectsIntelligently(array_values($availableProjects));
        
        if (empty($availableProjects)) {
            $text = '➕ <b>Добавить проект на контроль</b>

Все доступные проекты уже добавлены в ваш список контроля.';
            $keyboard = [[
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']
            ]];
        } else {
            $text = '➕ <b>Добавить проект на контроль</b>

Выберите проект:';
            $keyboard = [];
            $row = [];
            foreach ($availableProjects as $index => $project) {
                $row[] = ['text' => $project, 'callback_data' => "proj_ctrl_add_{$project}"];
                if (count($row) == 3 || $index == count($availableProjects) - 1) {
                    $keyboard[] = $row;
                    $row = [];
                }
            }
            $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']];
        }
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function showRemoveControlProjectMenu(string $chatId, int $userId): ServerResponse
    {
        $userControlProjects = $this->getUserControlProjects($userId);
        if (empty($userControlProjects)) {
            $text = '➖ <b>Удалить проект</b>

У вас нет проектов на контроле.';
        } else {
            $text = '➖ <b>Удалить проект с контроля</b>

Выберите проект:';
        }
        $keyboard = [];
        if (!empty($userControlProjects)) {
            $row = [];
            $projectCodes = array_keys($userControlProjects);
            // Apply intelligent sorting to project list
            $projectCodes = $this->databaseService->sortProjectsIntelligently($projectCodes);
            foreach ($projectCodes as $index => $project) {
                $row[] = ['text' => $project, 'callback_data' => "proj_ctrl_remove_{$project}"];
                if (count($row) == 3 || $index == count($projectCodes) - 1) {
                    $keyboard[] = $row;
                    $row = [];
                }
            }
        }
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']];
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function showControlProjectsList(string $chatId, int $userId): ServerResponse
    {
        $controlProjects = $this->getUserControlProjects($userId);
        $text = '📋 <b>Проекты на контроле</b>

';
        if (empty($controlProjects)) {
            $text .= 'У вас пока нет проектов на контроле.';
        } else {
            foreach ($controlProjects as $projectCode => $settings) {
                $text .= "• <b>{$projectCode}</b>\n";
                $text .= "  📂 Группа: " . ($settings['group_id'] ?? 'не настроена') . "\n";
                $text .= "  💬 Чат: " . ($settings['chat_id'] ?? 'не настроен') . "\n\n";
            }
        }
        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']
        ]];
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function showControlProjectSettings(string $chatId, int $userId): ServerResponse
    {
        $controlProjects = $this->getUserControlProjects($userId);
        if (empty($controlProjects)) {
            $text = '⚙️ <b>Настройки</b>

У вас нет проектов на контроле.';
            $keyboard = [];
        } else {
            $text = '⚙️ <b>Настройки уведомлений</b>

Выберите проект:';
            $keyboard = [];
            $row = [];
            $projectCodes = array_keys($controlProjects);
            // Apply intelligent sorting to project list
            $projectCodes = $this->databaseService->sortProjectsIntelligently($projectCodes);
            foreach ($projectCodes as $index => $project) {
                $row[] = ['text' => "⚙️ {$project}", 'callback_data' => "proj_ctrl_set_{$project}"];
                if (count($row) == 2 || $index == count($projectCodes) - 1) {
                    $keyboard[] = $row;
                    $row = [];
                }
            }
        }
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']];
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function addProjectToControlList(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = ['group_id' => '', 'chat_id' => '', 'created_at' => date('Y-m-d H:i:s')];
            $userData['control_projects'] = $controlProjects;
            $this->databaseService->updateUserData($userId, $userData);
            $text = "✅ <b>Проект добавлен</b>

<b>{$projectCode}</b> добавлен на контроль.

📝 Не забудьте настроить уведомления!";
        } else {
            $text = "ℹ️ <b>Проект уже на контроле</b>

<b>{$projectCode}</b> уже в списке.";
        }
        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']
        ]];
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function removeProjectFromControlList(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        if (isset($controlProjects[$projectCode])) {
            unset($controlProjects[$projectCode]);
            $userData['control_projects'] = $controlProjects;
            $this->databaseService->updateUserData($userId, $userData);
            $text = "✅ <b>Проект удалён</b>

<b>{$projectCode}</b> удалён с контроля.";
        } else {
            $text = "ℹ️ <b>Проект не найден</b>

<b>{$projectCode}</b> не найден в списке.";
        }
        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ПроектыНаКонтроле']
        ]];
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function showProjectControlSettings(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $controlProjects = $this->getUserControlProjects($userId);
        $projectSettings = $controlProjects[$projectCode] ?? [];
        
        $text = "⚙️ <b>Настройки проекта {$projectCode}</b>\n\n";
        
        // Display current settings
        $text .= "📊 <b>Текущие настройки:</b>\n";
        $text .= "📂 Группа уведомлений: " . ($projectSettings['group_id'] ? "✅ настроена" : "❌ не настроена") . "\n";
        $text .= "💬 Чат уведомлений: " . ($projectSettings['chat_id'] ? "✅ настроен" : "❌ не настроен") . "\n";
        $text .= "🔔 Типы уведомлений: " . ($projectSettings['notification_types'] ? implode(', ', $projectSettings['notification_types']) : "все") . "\n";
        $text .= "⏰ Режим уведомлений: " . ($projectSettings['notification_mode'] ?? "стандартный") . "\n\n";
        
        $text .= "Выберите параметр для настройки:";
        
        $keyboard = [
            [
                ['text' => '📂 Настроить группу', 'callback_data' => "proj_ctrl_cfg_group_{$projectCode}"],
                ['text' => '💬 Настроить чат', 'callback_data' => "proj_ctrl_cfg_chat_{$projectCode}"]
            ],
            [
                ['text' => '🔔 Типы уведомлений', 'callback_data' => "proj_ctrl_cfg_types_{$projectCode}"],
                ['text' => '⏰ Режим уведомлений', 'callback_data' => "proj_ctrl_cfg_mode_{$projectCode}"]
            ],
            [
                ['text' => '🧪 Тестовое уведомление', 'callback_data' => "proj_ctrl_test_{$projectCode}"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_ctrl_settings']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Configure notification group for project
     */
    private function showGroupConfiguration(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $text = "📂 <b>Настройка группы для проекта {$projectCode}</b>\n\n";
        $text .= "Выберите группу для получения уведомлений:\n\n";
        $text .= "💡 <b>Совет:</b> Добавьте бота в нужную группу и сделайте его администратором для отправки уведомлений.";
        
        // Get available groups (this would need to be implemented based on your bot's group management)
        $availableGroups = $this->getAvailableGroups($userId);
        
        $keyboard = [];
        foreach ($availableGroups as $groupId => $groupName) {
            $keyboard[] = [['text' => "📂 {$groupName}", 'callback_data' => "proj_ctrl_group_select_{$projectCode}_{$groupId}"]];
        }
        
        // Add manual group ID input option
        $keyboard[] = [['text' => '✏️ Ввести ID группы вручную', 'callback_data' => "proj_ctrl_group_manual_{$projectCode}"]];
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Configure notification chat for project
     */
    private function showChatConfiguration(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $text = "💬 <b>Настройка чата для проекта {$projectCode}</b>\n\n";
        $text .= "Выберите чат для получения уведомлений:\n\n";
        $text .= "💡 <b>Совет:</b> Можете выбрать личный чат с ботом или любой другой чат, где есть бот.";
        
        $keyboard = [
            [
                ['text' => '👤 Личный чат', 'callback_data' => "proj_ctrl_chat_select_{$projectCode}_personal"]
            ],
            [
                ['text' => '✏️ Ввести ID чата вручную', 'callback_data' => "proj_ctrl_chat_manual_{$projectCode}"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Configure notification types for project
     */
    private function showNotificationTypesConfiguration(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $controlProjects = $this->getUserControlProjects($userId);
        $currentTypes = $controlProjects[$projectCode]['notification_types'] ?? [];
        
        $text = "🔔 <b>Типы уведомлений для проекта {$projectCode}</b>\n\n";
        $text .= "Выберите типы уведомлений (можно выбрать несколько):\n\n";
        
        $notificationTypes = [
            'daily_reports' => '📊 Ежедневные отчёты',
            'milestone_updates' => '🎯 Обновления по вехам',
            'critical_issues' => '🚨 Критические проблемы',
            'team_updates' => '👥 Обновления команды',
            'schedule_changes' => '📅 Изменения в графике',
            'budget_alerts' => '💰 Финансовые уведомления'
        ];
        
        $keyboard = [];
        foreach ($notificationTypes as $type => $label) {
            $isSelected = in_array($type, $currentTypes);
            $prefix = $isSelected ? '✅ ' : '⬜ ';
            $keyboard[] = [['text' => $prefix . $label, 'callback_data' => "proj_ctrl_toggle_type_{$projectCode}_{$type}"]];
        }
        
        $keyboard[] = [['text' => '💾 Сохранить настройки', 'callback_data' => "proj_ctrl_save_types_{$projectCode}"]];
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Configure notification mode for project
     */
    private function showNotificationModeConfiguration(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $controlProjects = $this->getUserControlProjects($userId);
        $currentMode = $controlProjects[$projectCode]['notification_mode'] ?? 'standard';
        
        $text = "⏰ <b>Режим уведомлений для проекта {$projectCode}</b>\n\n";
        $text .= "Выберите режим уведомлений:\n\n";
        
        $modes = [
            'standard' => '🔔 Стандартный - уведомления в рабочее время',
            'immediate' => '⚡ Мгновенный - все уведомления сразу',
            'summary' => '📋 Сводка - итоги в конце дня',
            'critical_only' => '🚨 Только критические',
            'disabled' => '🔕 Отключены'
        ];
        
        $keyboard = [];
        foreach ($modes as $mode => $description) {
            $prefix = ($mode === $currentMode) ? '✅ ' : '⬜ ';
            $keyboard[] = [['text' => $prefix . $description, 'callback_data' => "proj_ctrl_mode_select_{$projectCode}_{$mode}"]];
        }
        
        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Send test notification using manual group/chat settings with proper thread handling
     */
    private function sendTestNotification(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $controlProjects = $this->getUserControlProjects($userId);
        $projectSettings = $controlProjects[$projectCode] ?? [];
        
        // Получаем данные пользователя для отображения уровня доступа и ФИО
        $userData = $this->databaseService->getUserData($userId);
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        // Определяем название уровня доступа
        $accessLevelNames = [
            1 => 'Пользователь',
            2 => 'Супервайзер',
            3 => 'Администратор',
            4 => 'Суперадминистратор'
        ];
        $accessLevelName = $accessLevelNames[$userAccessLevel] ?? 'Гость';
        
        // Получаем ФИО из профиля
        $userFio = $userData['fio'] ?? 'Не указано';
        
        $testMessage = "🧪 <b>Тестовое уведомление</b>\n\n";
        $testMessage .= "📂 Проект: {$projectCode}\n";
        $testMessage .= "⏰ Время: " . date('d.m.Y H:i:s') . "\n";
        $testMessage .= "👤 Отправитель: {$accessLevelName} - {$userFio}\n\n";
        $testMessage .= "✅ Система уведомлений работает корректно!";
        
        $testsSent = [];
        $errors = [];
        
        // Отправка в настроенную группу с корректным обработкой chat_id
        if (!empty($projectSettings['group_id'])) {
            try {
                // Подготовка параметров отправки
                $data = [
                    'chat_id' => $projectSettings['group_id'],
                    'text' => $testMessage,
                    'parse_mode' => 'HTML'
                ];
                
                // Добавляем message_thread_id если указан chat_id (как в системе отчетов)
                if (!empty($projectSettings['chat_id']) && $projectSettings['chat_id'] !== 'personal') {
                    $data['message_thread_id'] = $projectSettings['chat_id'];
                }
                
                $result = \Longman\TelegramBot\Request::sendMessage($data);
                
                if ($result->isOk()) {
                    if (!empty($projectSettings['chat_id']) && $projectSettings['chat_id'] !== 'personal') {
                        $testsSent[] = "группу {$projectSettings['group_id']} (чат {$projectSettings['chat_id']})";
                    } else {
                        $testsSent[] = "группу {$projectSettings['group_id']}";
                    }
                } else {
                    $errors[] = 'Группа: ' . $result->getDescription();
                }
            } catch (\Exception $e) {
                $errors[] = 'Группа: ' . $e->getMessage();
            }
        }
        
        // Отправка в личный чат (если указан personal)
        if (!empty($projectSettings['chat_id']) && $projectSettings['chat_id'] === 'personal') {
            try {
                $result = $this->messageService->sendMessage($userId, $testMessage);
                if ($result->isOk()) {
                    $testsSent[] = 'личный чат';
                } else {
                    $errors[] = 'Личный чат: ' . $result->getDescription();
                }
            } catch (\Exception $e) {
                $errors[] = 'Личный чат: ' . $e->getMessage();
            }
        }
        
        // Подготовка ответа
        $responseText = "🧪 <b>Тестовое уведомление</b>\n\n";
        
        if (empty($projectSettings['group_id']) && (empty($projectSettings['chat_id']) || $projectSettings['chat_id'] !== 'personal')) {
            $responseText .= "❌ Не настроены получатели уведомлений.\n\n";
            $responseText .= "Настройте группу и чат для получения уведомлений.";
        } else {
            if (!empty($testsSent)) {
                $responseText .= "✅ <b>Успешно отправлено в:</b>\n";
                foreach ($testsSent as $success) {
                    $responseText .= "• " . $success . "\n";
                }
            }
            
            if (!empty($errors)) {
                $responseText .= "\n❌ <b>Ошибки отправки:</b>\n";
                foreach ($errors as $error) {
                    $responseText .= "• " . $error . "\n";
                }
            }
            
            $responseText .= "\n📍 <b>Настройки проекта:</b>\n";
            $responseText .= "• Группа: " . ($projectSettings['group_id'] ?? 'Не настроена') . "\n";
            $responseText .= "• Чат (тема): " . ($projectSettings['chat_id'] ?? 'Не настроен') . "\n";
        }
        
        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $responseText, $keyboard);
    }
    
    /**
     * Get available groups for notifications
     */
    private function getAvailableGroups(int $userId): array
    {
        // This is a placeholder - you would implement actual group discovery
        // based on your bot's group management system
        return [
            '-1001234567890' => 'Группа разработки',
            '-1001234567891' => 'Группа менеджмента',
            '-1001234567892' => 'Группа контроля качества'
        ];
    }
    
    /**
     * Show manual group ID input interface
     */
    private function showManualGroupInput(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        // Set state using file-based manager
        require_once __DIR__ . '/../Services/ManualInputStateManager.php';
        $stateManager = new \Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager();
        $stateManager->setState($userId, 'group_id', $projectCode);
        
        $text = "✏️ <b>Ручной ввод ID группы</b>\n\n";
        $text .= "Для проекта: <b>{$projectCode}</b>\n\n";
        $text .= "📝 <b>Инструкции:</b>\n";
        $text .= "1. Добавьте бота в группу\n";
        $text .= "2. Сделайте бота администратором\n";
        $text .= "3. Найдите ID группы (начинается с -100)\n";
        $text .= "4. Отправьте ID следующим сообщением\n\n";
        $text .= "💡 Используйте @userinfobot в группе\n\n";
        $text .= "💬 <b>Ожидаю ввода ID группы...</b>";
        
        $keyboard = [[
            ['text' => '❌ Отмена', 'callback_data' => "proj_ctrl_cfg_group_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show manual chat ID input interface
     */
    private function showManualChatInput(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        // Set file-based state for GenericmessageCommand (primary method)
        $this->stateManager->setState($userId, 'chat_id', $projectCode);
        
        // Set conversation state as backup
        $conversation = new \Longman\TelegramBot\Conversation($userId, $chatId, '/callback');
        $conversation->notes['manual_input_type'] = 'chat_id';
        $conversation->notes['manual_input_project'] = $projectCode;
        $conversation->update();
        
        $text = "✏️ <b>Ручной ввод ID чата</b>\n\n";
        $text .= "Для проекта: <b>{$projectCode}</b>\n\n";
        $text .= "📝 <b>Инструкции:</b>\n";
        $text .= "1. Определите чат для уведомлений\n";
        $text .= "2. Убедитесь, что бот в чате\n";
        $text .= "3. Найдите ID чата\n";
        $text .= "4. Отправьте ID следующим сообщением\n\n";
        $text .= "💬 <b>Ожидаю ввода ID чата...</b>";
        
        $keyboard = [[
            ['text' => '❌ Отмена', 'callback_data' => "proj_ctrl_cfg_chat_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle configuration callbacks
     */
    private function handleConfigurationCallbacks(CallbackQuery $callbackQuery, string $callbackData, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Handle group configuration
        if (str_starts_with($callbackData, 'proj_ctrl_cfg_group_')) {
            $projectCode = str_replace('proj_ctrl_cfg_group_', '', $callbackData);
            return $this->showGroupConfiguration($chatId, $userId, $projectCode);
        }
        
        // Handle chat configuration
        if (str_starts_with($callbackData, 'proj_ctrl_cfg_chat_')) {
            $projectCode = str_replace('proj_ctrl_cfg_chat_', '', $callbackData);
            return $this->showChatConfiguration($chatId, $userId, $projectCode);
        }
        
        // Handle notification types configuration
        if (str_starts_with($callbackData, 'proj_ctrl_cfg_types_')) {
            $projectCode = str_replace('proj_ctrl_cfg_types_', '', $callbackData);
            return $this->showNotificationTypesConfiguration($chatId, $userId, $projectCode);
        }
        
        // Handle notification mode configuration
        if (str_starts_with($callbackData, 'proj_ctrl_cfg_mode_')) {
            $projectCode = str_replace('proj_ctrl_cfg_mode_', '', $callbackData);
            return $this->showNotificationModeConfiguration($chatId, $userId, $projectCode);
        }
        
        return $this->messageService->sendMessage($chatId, 'Неизвестная команда конфигурации');
    }
    
    /**
     * Set project group
     */
    private function setProjectGroup(string $chatId, int $userId, string $projectCode, string $groupId): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = [];
        }
        
        $controlProjects[$projectCode]['group_id'] = $groupId;
        $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
        
        $userData['control_projects'] = $controlProjects;
        $this->databaseService->updateUserData($userId, $userData);
        
        $text = "✅ <b>Группа настроена</b>\n\nГруппа для проекта {$projectCode} успешно настроена.";
        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Set project chat
     */
    private function setProjectChat(string $chatId, int $userId, string $projectCode, string $chatType): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = [];
        }
        
        $targetChatId = ($chatType === 'personal') ? $userId : $chatType;
        $controlProjects[$projectCode]['chat_id'] = $targetChatId;
        $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
        
        $userData['control_projects'] = $controlProjects;
        $this->databaseService->updateUserData($userId, $userData);
        
        $text = "✅ <b>Чат настроен</b>\n\nЧат для проекта {$projectCode} успешно настроен.";
        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Toggle notification type
     */
    private function toggleNotificationType(string $chatId, int $userId, string $projectCode, string $notificationType): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = [];
        }
        
        $currentTypes = $controlProjects[$projectCode]['notification_types'] ?? [];
        
        if (in_array($notificationType, $currentTypes)) {
            $currentTypes = array_diff($currentTypes, [$notificationType]);
        } else {
            $currentTypes[] = $notificationType;
        }
        
        $controlProjects[$projectCode]['notification_types'] = array_values($currentTypes);
        $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
        
        $userData['control_projects'] = $controlProjects;
        $this->databaseService->updateUserData($userId, $userData);
        
        // Return to the notification types configuration screen
        return $this->showNotificationTypesConfiguration($chatId, $userId, $projectCode);
    }
    
    /**
     * Set notification mode
     */
    private function setNotificationMode(string $chatId, int $userId, string $projectCode, string $mode): ServerResponse
    {
        $userData = $this->databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = [];
        }
        
        $controlProjects[$projectCode]['notification_mode'] = $mode;
        $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
        
        $userData['control_projects'] = $controlProjects;
        $this->databaseService->updateUserData($userId, $userData);
        
        $text = "✅ <b>Режим уведомлений сохранён</b>\n\nРежим уведомлений для проекта {$projectCode} успешно изменён.";
        $keyboard = [[
            ['text' => '⬅️ Назад', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    // ========================================
    // PROJECT REPORT METHODS - NEW
    // ========================================

    /**
     * Show project report menu
     */
    private function showProjectReportMenu(string $chatId, int $userId): ServerResponse
    {
        $text = '📋 <b>Отчёт по проекту</b>

Выберите тип отчёта:';

        $keyboard = [
            [
                ['text' => '📊 Простой отчёт', 'callback_data' => 'proj_report_simple']
            ],
            [
                ['text' => '📁 По-проектный отчёт', 'callback_data' => 'proj_report_byproject']
            ],
            [
                ['text' => '📌 Выгрузка заметок', 'callback_data' => 'proj_report_notes']
            ],
            [
                ['text' => '📋 История выгрузок', 'callback_data' => 'export_key_history']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pm_menu']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show date range selection for project report
     */
    private function showReportDateRangeMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        $text = "📋 <b>Отчёт по проекту {$projectCode}</b>

Выберите период для отчёта:";
        
        $today = date('d.m.Y');
        $yesterday = date('d.m.Y', strtotime('-1 day'));
        $week = date('d.m.Y', strtotime('-7 days'));
        $month = date('d.m.Y', strtotime('-30 days'));
        
        $keyboard = [
            [
                ['text' => '📅 Сегодня', 'callback_data' => "proj_report_period_{$projectCode}_today"]
            ],
            [
                ['text' => '📅 Вчера', 'callback_data' => "proj_report_period_{$projectCode}_yesterday"]
            ],
            [
                ['text' => '📅 Последние 7 дней', 'callback_data' => "proj_report_period_{$projectCode}_week"]
            ],
            [
                ['text' => '📅 Последние 30 дней', 'callback_data' => "proj_report_period_{$projectCode}_month"]
            ],
            [
                ['text' => '📅 Весь период', 'callback_data' => "proj_report_period_{$projectCode}_all"]
            ],
            [
                ['text' => '📆 Выбранный период', 'callback_data' => "proj_report_custom_{$projectCode}"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show filter selection for project report
     */
    private function showProjectReportFilterMenu(string $chatId, int $userId, string $projectCode, string $period): ServerResponse
    {
        $periodText = $this->getPeriodText($period);
        
        $text = "📋 <b>Отчёт по проекту {$projectCode}</b>

📅 Период: {$periodText}

Выберите <b>тип фильтра</b>:";
        
        $keyboard = [
            [
                ['text' => '⚡ Быстрые фильтры', 'callback_data' => "proj_report_prj_filter_{$projectCode}_{$period}"]
            ],
            [
                ['text' => '📊 Без фильтра (все данные)', 'callback_data' => "proj_report_prj_nofilter_{$projectCode}_{$period}"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => "proj_report_select_{$projectCode}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show quick filters for project report
     */
    private function showProjectReportQuickFilterMenu(string $chatId, int $userId, string $projectCode, string $period): ServerResponse
    {
        $periodText = $this->getPeriodText($period);
        
        $text = "⚡ <b>Быстрые фильтры</b>

📁 Проект: {$projectCode}
📅 Период: {$periodText}

Выберите нужный фильтр:";
        
        $keyboard = [
            [
                ['text' => '🔧 Гарантийные работы', 'callback_data' => "proj_report_prj_quick_{$projectCode}_{$period}_warranty"]
            ],
            [
                ['text' => '⚠️ Устранение замечаний РК', 'callback_data' => "proj_report_prj_quick_{$projectCode}_{$period}_rk"]
            ],
            [
                ['text' => '📝 Дополнительные работы', 'callback_data' => "proj_report_prj_quick_{$projectCode}_{$period}_additional"]
            ],
            [
                ['text' => '💤 Непроизводительное время', 'callback_data' => "proj_report_prj_quick_{$projectCode}_{$period}_idle"]
            ],
            [
                ['text' => '🚗 Командировочные расходы', 'callback_data' => "proj_report_prj_quick_{$projectCode}_{$period}_travel"]
            ],
            [
                ['text' => '📂 Документооборот', 'callback_data' => "proj_report_prj_quick_{$projectCode}_{$period}_docs"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => "proj_report_back_to_filter_{$projectCode}_{$period}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Get period text for display
     */
    private function getPeriodText(string $period): string
    {
        $periodTexts = [
            'today' => 'Сегодня',
            'yesterday' => 'Вчера',
            'week' => 'Последние 7 дней',
            'month' => 'Последние 30 дней',
            'all' => 'Весь период'
        ];
        
        return $periodTexts[$period] ?? $period;
    }

    /**
     * Show start date selection for project custom report
     */
    private function showProjectReportStartDateMenu(string $chatId, int $userId, Conversation $conversation, string $projectCode): ServerResponse
    {
        $text = "📋 <b>Отчёт по проекту {$projectCode}</b>\n\nВведите <b>дату начала</b> отчёта в формате ДД.ММ.ГГГГ\n\nПример: 01.10.2025";
        
        // Set conversation state
        $conversation->notes['awaiting_project_report_start_date'] = true;
        $conversation->notes['project_report_code'] = $projectCode;
        $conversation->update();
        
        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => "proj_report_select_{$projectCode}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show end date selection for project custom report
     */
    private function showProjectReportEndDateMenu(string $chatId, int $userId, Conversation $conversation, string $projectCode, string $startDate): ServerResponse
    {
        $text = "📋 <b>Отчёт по проекту {$projectCode}</b>\n\nДата начала: {$startDate}\n\nВведите <b>дату окончания</b> отчёта в формате ДД.ММ.ГГГГ\n\nПример: 13.10.2025";
        
        // Set conversation state
        $conversation->notes['project_report_start_date'] = $startDate;
        $conversation->notes['awaiting_project_report_end_date'] = true;
        $conversation->notes['awaiting_project_report_start_date'] = false;
        $conversation->update();
        
        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => "proj_report_select_{$projectCode}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Handle project report custom date input (called from GenericmessageCommand)
     */
    public function handleProjectReportDateInput(string $chatId, int $userId, string $userInput, Conversation $conversation): ?ServerResponse
    {
        $projectCode = $conversation->notes['project_report_code'] ?? null;
        
        if (!$projectCode) {
            return null;
        }
        
        // Check if we're waiting for start date
        if (isset($conversation->notes['awaiting_project_report_start_date']) && $conversation->notes['awaiting_project_report_start_date']) {
            // Validate date format
            if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $userInput)) {
                $text = '⚠️ Неверный формат даты. Используйте формат ДД.ММ.ГГГГ (например, 01.10.2025)';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // Validate date
            $dateParts = explode('.', $userInput);
            if (!checkdate((int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2])) {
                $text = '⚠️ Некорректная дата. Проверьте введённые данные.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            return $this->showProjectReportEndDateMenu($chatId, $userId, $conversation, $projectCode, $userInput);
        }
        
        // Check if we're waiting for end date
        if (isset($conversation->notes['awaiting_project_report_end_date']) && $conversation->notes['awaiting_project_report_end_date']) {
            // Validate date format
            if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $userInput)) {
                $text = '⚠️ Неверный формат даты. Используйте формат ДД.ММ.ГГГГ (например, 13.10.2025)';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // Validate date
            $dateParts = explode('.', $userInput);
            if (!checkdate((int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2])) {
                $text = '⚠️ Некорректная дата. Проверьте введённые данные.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            $startDate = $conversation->notes['project_report_start_date'];
            
            // Clear conversation state
            $conversation->notes['awaiting_project_report_end_date'] = false;
            $conversation->notes['project_report_start_date'] = $startDate;
            $conversation->notes['project_report_end_date'] = $userInput;
            $conversation->update();
            
            return $this->showProjectReportCustomFilterMenu($chatId, $userId, $conversation, $projectCode, $startDate, $userInput);
        }
        
        return null;
    }

    /**
     * Show filter menu for custom date project report
     */
    private function showProjectReportCustomFilterMenu(string $chatId, int $userId, Conversation $conversation, string $projectCode, string $startDate, string $endDate): ServerResponse
    {
        $text = "📋 <b>Отчёт по проекту {$projectCode}</b>\n\n📅 Период: {$startDate} - {$endDate}\n\nВыберите <b>тип фильтра</b>:";
        
        // Save dates to conversation
        $conversation->notes['project_report_start_date'] = $startDate;
        $conversation->notes['project_report_end_date'] = $endDate;
        $conversation->update();
        
        $keyboard = [
            [
                ['text' => '⚡ Быстрые фильтры', 'callback_data' => "proj_report_custom_filter_{$projectCode}"]
            ],
            [
                ['text' => '📊 Без фильтра (все данные)', 'callback_data' => "proj_report_custom_nofilter_{$projectCode}"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => "proj_report_select_{$projectCode}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show quick filters for custom date project report
     */
    private function showProjectReportCustomQuickFilterMenu(string $chatId, int $userId, Conversation $conversation, string $projectCode): ServerResponse
    {
        $startDate = $conversation->notes['project_report_start_date'] ?? '';
        $endDate = $conversation->notes['project_report_end_date'] ?? '';
        
        $text = "⚡ <b>Быстрые фильтры</b>\n\n📁 Проект: {$projectCode}\n📅 Период: {$startDate} - {$endDate}\n\nВыберите нужный фильтр:";
        
        $keyboard = [
            [
                ['text' => '🔧 Гарантийные работы', 'callback_data' => "proj_report_custom_quick_{$projectCode}_warranty"]
            ],
            [
                ['text' => '⚠️ Устранение замечаний РК', 'callback_data' => "proj_report_custom_quick_{$projectCode}_rk"]
            ],
            [
                ['text' => '📝 Дополнительные работы', 'callback_data' => "proj_report_custom_quick_{$projectCode}_additional"]
            ],
            [
                ['text' => '💤 Непроизводительное время', 'callback_data' => "proj_report_custom_quick_{$projectCode}_idle"]
            ],
            [
                ['text' => '🚗 Командировочные расходы', 'callback_data' => "proj_report_custom_quick_{$projectCode}_travel"]
            ],
            [
                ['text' => '📂 Документооборот', 'callback_data' => "proj_report_custom_quick_{$projectCode}_docs"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => "proj_report_custom_back_to_filter_{$projectCode}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Generate custom date project report
     */
    private function generateProjectReportCustom(string $chatId, int $userId, Conversation $conversation, string $projectCode, ?string $filterType = null): ServerResponse
    {
        $startDate = $conversation->notes['project_report_start_date'] ?? null;
        $endDate = $conversation->notes['project_report_end_date'] ?? null;
        
        if (!$startDate || !$endDate) {
            $text = '⚠️ Ошибка: даты не указаны';
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Convert dates to SQL format
        $startParts = explode('.', $startDate);
        $endParts = explode('.', $endDate);
        $startSql = "{$startParts[2]}-{$startParts[1]}-{$startParts[0]} 00:00:00";
        $endSql = "{$endParts[2]}-{$endParts[1]}-{$endParts[0]} 23:59:59";
        
        // Get tabel entries for the project
        $entries = $this->getProjectTabelEntries($projectCode, $startSql, $endSql);
        
        // Apply filter if specified
        if ($filterType !== null) {
            $entries = $this->applyQuickFilter($entries, $filterType);
        }
        
        if (empty($entries)) {
            $filterText = $filterType ? " для фильтра: " . $this->getQuickFilterName($filterType) : '';
            $text = "⚠️ <b>Нет данных</b>\n\nЗа период с {$startDate} по {$endDate}{$filterText} нет активностей по проекту {$projectCode}.";
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
                ]
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Create CSV file
        $timestamp = date('Y-m-d_H-i-s');
        $period = "{$startDate}_{$endDate}";
        $fileName = $filterType 
            ? $this->createFilteredProjectReportCsv($projectCode, $entries, $period, $filterType)
            : $this->createProjectReportCsv($projectCode, $entries, $period);
        
        // Send status message first
        $filterInfo = $filterType ? "\n🎯 Фильтр: " . $this->getQuickFilterName($filterType) : '';
        $text = "✅ <b>Отчёт сформирован</b>\n\n📁 Проект: {$projectCode}\n📅 Период: {$startDate} - {$endDate}{$filterInfo}\n📄 Записей: " . count($entries) . "\n\nФайл готов к скачиванию.";
        $this->messageService->sendMessage($chatId, $text);
        
        // Send the file
        $this->sendCsvFile($chatId, $fileName);
        
        // Log the export
        error_log("ProjectHandler: Logging export - userId: {$userId}, fileName: {$fileName}, entries: " . count($entries));
        $logResult = $this->exportSecurityService->logExport(
            $userId,
            'report',
            $filterType ? "По-проектный ({$this->getQuickFilterName($filterType)})" : 'По-проектный',
            count($entries),
            $fileName,
            $projectCode,
            $startDate,
            $endDate,
            $filterType
        );
        error_log("ProjectHandler: Export log result: " . ($logResult ? 'SUCCESS' : 'FAILED'));
        
        // Send final menu
        $keyboard = [
            [
                ['text' => '🔄 Сформировать ещё один отчёт', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ],
            [
                ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, "👍 Готово!", $keyboard);
    }

    /**
     * Generate and send project report
     */
    private function generateProjectReport(string $chatId, int $userId, string $projectCode, string $period, ?string $filterType = null): ServerResponse
    {
        // Calculate date range based on period
        $startDate = null;
        $endDate = date('Y-m-d 23:59:59');
        $periodText = $this->getPeriodText($period);
        
        switch ($period) {
            case 'today':
                $startDate = date('Y-m-d 00:00:00');
                break;
            case 'yesterday':
                $startDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $endDate = date('Y-m-d 23:59:59', strtotime('-1 day'));
                break;
            case 'week':
                $startDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
                $startDate = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case 'all':
                $startDate = null;
                break;
        }
        
        // Get tabel entries for the project
        $entries = $this->getProjectTabelEntries($projectCode, $startDate, $endDate);
        
        // Apply filter if specified
        if ($filterType !== null) {
            $entries = $this->applyQuickFilter($entries, $filterType);
        }
        
        if (empty($entries)) {
            $filterText = $filterType ? " для фильтра: " . $this->getQuickFilterName($filterType) : '';
            $text = "⚠️ <b>Нет данных</b>

За выбранный период ({$periodText}){$filterText} нет активностей по проекту {$projectCode}.";
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
                ]
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Create CSV file
        $fileName = $filterType 
            ? $this->createFilteredProjectReportCsv($projectCode, $entries, $period, $filterType)
            : $this->createProjectReportCsv($projectCode, $entries, $period);
        
        // Send status message first
        $filterInfo = $filterType ? "\n🎯 Фильтр: " . $this->getQuickFilterName($filterType) : '';
        $text = "✅ <b>Отчёт сформирован</b>

📁 Проект: {$projectCode}
📅 Период: {$periodText}{$filterInfo}
📄 Записей: " . count($entries) . "

Файл готов к скачиванию.";
        $this->messageService->sendMessage($chatId, $text);
        
        // Send the file
        $this->sendCsvFile($chatId, $fileName);
        
        // Log the export
        error_log("ProjectHandler: Logging export - userId: {$userId}, fileName: {$fileName}, entries: " . count($entries));
        $logResult = $this->exportSecurityService->logExport(
            $userId,
            'report',
            $filterType ? "По-проектный ({$this->getQuickFilterName($filterType)})" : 'По-проектный',
            count($entries),
            $fileName,
            $projectCode,
            $startDate,
            $endDate,
            $filterType
        );
        error_log("ProjectHandler: Export log result: " . ($logResult ? 'SUCCESS' : 'FAILED'));
        
        // Send final menu
        $keyboard = [
            [
                ['text' => '🔄 Сформировать ещё один отчёт', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ],
            [
                ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, "👍 Готово!", $keyboard);
    }

    /**
     * Get tabel entries for a specific project
     */
    private function getProjectTabelEntries(string $projectCode, ?string $startDate, string $endDate): array
    {
        // Get entries from database
        $rawEntries = $this->databaseService->getProjectTabelEntries($projectCode, $startDate, $endDate);
        
        // Get all users to map user IDs to FIOs and FN
        $allUsers = $this->databaseService->getAllUsers();
        $userMap = [];
        $userFnMap = [];
        
        // Create user ID to FIO and FN mapping
        foreach ($allUsers as $user) {
            $userMap[$user['user_id']] = $user['data']['fio'] ?? 'Неизвестно';
            $userFnMap[$user['user_id']] = $user['data']['functional_direction'] ?? 'Не указано';
        }
        
        // Enrich entries with FIO and FN, exclude testers
        $entries = [];
        foreach ($rawEntries as $row) {
            $userFn = $userFnMap[$row['user_id']] ?? 'Не указано';
            
            // CRITICAL: Exclude Тестировщик from all reports
            if ($userFn === 'Тестировщик') {
                continue;
            }
            
            $entries[] = [
                'user_id' => $row['user_id'],
                'fio' => $userMap[$row['user_id']] ?? 'Неизвестно',
                'fn' => $userFn,
                'dt' => $row['dt'],
                'message' => $row['message']
            ];
        }
        
        return $entries;
    }

    /**
     * Create CSV file for project report
     */
    private function createProjectReportCsv(string $projectCode, array $entries, string $period): string
    {
        $uploadsDir = __DIR__ . '/../../Uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $fileName = "project_report_{$projectCode}_{$period}_{$timestamp}.csv";
        $filePath = $uploadsDir . '/' . $fileName;
        
        $output = fopen($filePath, 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fprintf($output, "\xEF\xBB\xBF");
        
        // Write header - UNIFIED FORMAT matching menu structure
        fputcsv($output, ['Проект', 'Дата', 'Процесс (ФН)', 'Уровень 1', 'Уровень 2', 'Уровень 3', 'Уровень 4', 'Уровень 5', 'Фамилия']);
        
        // Write data
        foreach ($entries as $entry) {
            // Skip milestone entries (вехи)
            if ($this->isMilestoneEntry($entry['message'])) {
                continue;
            }
            
            // Determine functional direction from entry if available
            $fn = $entry['fn'] ?? 'Не указано';
            $parsed = $this->parseReportEntry($entry['message'], $fn);
            // NEW ORDER: Project, Date, Process, Level 1, Level 2, Level 3, Level 4, Level 5, Name
            // Apply sanitization to prevent CSV issues with long texts
            fputcsv($output, [
                $this->sanitizeForCsv($parsed['project']),
                $parsed['date'],  // Date doesn't need sanitization
                $this->sanitizeForCsv($parsed['process']),
                $this->sanitizeForCsv($parsed['osnovnaya']),  // Уровень 1
                $this->sanitizeForCsv($parsed['tip1']),        // Уровень 2
                $this->sanitizeForCsv($parsed['tip2']),        // Уровень 3
                $this->sanitizeForCsv($parsed['tip3']),        // Уровень 4
                $this->sanitizeForCsv($parsed['tip4'] ?? ''),  // Уровень 5 (если есть)
                $this->sanitizeForCsv($entry['fio'])
            ]);
        }
        
        fclose($output);
        
        return $fileName;
    }
    
    /**
     * Create filtered CSV file for project report
     */
    private function createFilteredProjectReportCsv(string $projectCode, array $entries, string $period, string $filterType): string
    {
        $uploadsDir = __DIR__ . '/../../Uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $fileName = "project_filtered_{$projectCode}_{$filterType}_{$period}_{$timestamp}.csv";
        $filePath = $uploadsDir . '/' . $fileName;
        
        $output = fopen($filePath, 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fprintf($output, "\xEF\xBB\xBF");
        
        // Write header - UNIFIED FORMAT matching menu structure
        fputcsv($output, ['Проект', 'Дата', 'Процесс (ФН)', 'Уровень 1', 'Уровень 2', 'Уровень 3', 'Уровень 4', 'Уровень 5', 'Фамилия']);
        
        // Write data
        foreach ($entries as $entry) {
            // Skip milestone entries (вехи)
            if ($this->isMilestoneEntry($entry['message'])) {
                continue;
            }
            
            // Determine functional direction from entry if available
            $fn = $entry['fn'] ?? 'Не указано';
            $parsed = $this->parseReportEntry($entry['message'], $fn);
            // NEW ORDER: Project, Date, Process, Level 1, Level 2, Level 3, Level 4, Level 5, Name
            // Apply sanitization to prevent CSV issues with long texts
            fputcsv($output, [
                $this->sanitizeForCsv($parsed['project']),
                $parsed['date'],  // Date doesn't need sanitization
                $this->sanitizeForCsv($parsed['process']),
                $this->sanitizeForCsv($parsed['osnovnaya']),  // Уровень 1
                $this->sanitizeForCsv($parsed['tip1']),        // Уровень 2
                $this->sanitizeForCsv($parsed['tip2']),        // Уровень 3
                $this->sanitizeForCsv($parsed['tip3']),        // Уровень 4
                $this->sanitizeForCsv($parsed['tip4'] ?? ''),  // Уровень 5 (если есть)
                $this->sanitizeForCsv($entry['fio'])
            ]);
        }
        
        fclose($output);
        
        return $fileName;
    }

    /**
     * Send CSV file to user
     */
    private function sendCsvFile(string $chatId, string $fileName, string $caption = '📄 Отчёт по проекту'): bool
    {
        $filePath = __DIR__ . '/../../Uploads/' . $fileName;
        
        if (!file_exists($filePath)) {
            error_log("ProjectHandler: CSV file not found: {$filePath}");
            return false;
        }
        
        try {
            $result = \Longman\TelegramBot\Request::sendDocument([
                'chat_id' => $chatId,
                'document' => \Longman\TelegramBot\Request::encodeFile($filePath),
                'caption' => $caption
            ]);
            
            // Clean up file after sending
            if ($result->isOk()) {
                unlink($filePath);
            }
            
            return $result->isOk();
        } catch (\Exception $e) {
            error_log("ProjectHandler: Error sending CSV file: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // SIMPLE REPORT METHODS - NEW
    // ========================================

    /**
     * Show by-project report menu (the old implementation)
     */
    private function showByProjectReportMenu(string $chatId, int $userId, int $page = 1): ServerResponse
    {
        $text = '📁 <b>По-проектный отчёт</b>

Отчёт по всем активностям всех сотрудников на выбранном проекте.

Выберите проект:';

        // Get user's report projects first
        $personalProjects = $this->getUserReportProjects($userId);
        
        // Get all available projects (including pending ones from database)
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(
            $this->databaseService
        );
        $allProjects = $projectSelector->getAllProjectsIncludingPending();
        
        // Combine projects: personal projects first, then others
        $displayProjects = array_unique(array_merge($personalProjects, $allProjects));
        
        // CRITICAL: Apply intelligent sorting to the combined project list
        $displayProjects = $this->databaseService->sortProjectsIntelligently($displayProjects);
        
        if (empty($displayProjects)) {
            $text .= '

⚠️ Проекты не найдены.';
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
                ]
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // PAGINATION: 33 projects per page
        $perPage = 33;
        $totalProjects = count($displayProjects);
        $totalPages = (int)ceil($totalProjects / $perPage);
        
        // Check if page is valid
        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;
        
        // Get projects for current page
        $offset = ($page - 1) * $perPage;
        $projectsOnPage = array_slice($displayProjects, $offset, $perPage);
        
        $text .= "\nСтраница {$page} из {$totalPages} (Всего: {$totalProjects} проектов)";
        
        $keyboard = [];
        
        // Create buttons for projects (3 per row)
        $row = [];
        foreach ($projectsOnPage as $index => $project) {
            $row[] = ['text' => $project, 'callback_data' => "proj_report_select_{$project}"];
            
            if (count($row) == 3 || $index == count($projectsOnPage) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        // Add pagination navigation buttons
        if ($totalPages > 1) {
            $navRow = [];
            
            if ($page > 1) {
                $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => "proj_report_byproject_page_" . ($page - 1)];
            }
            
            $navRow[] = ['text' => "📖 {$page}/{$totalPages}", 'callback_data' => "proj_report_byproject_page_{$page}"];
            
            if ($page < $totalPages) {
                $navRow[] = ['text' => '➡️ Вперед', 'callback_data' => "proj_report_byproject_page_" . ($page + 1)];
            }
            
            $keyboard[] = $navRow;
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show start date selection for simple report
     */
    private function showSimpleReportStartDateMenu(string $chatId, int $userId, Conversation $conversation): ServerResponse
    {
        $text = '📊 <b>Простой отчёт</b>

Введите <b>дату начала</b> отчёта в формате ДД.ММ.ГГГГ

Пример: 01.10.2025';
        
        // Set conversation state
        $conversation->notes['awaiting_simple_report_start_date'] = true;
        $conversation->update();
        
        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show end date selection for simple report
     */
    private function showSimpleReportEndDateMenu(string $chatId, int $userId, Conversation $conversation, string $startDate): ServerResponse
    {
        $text = "📊 <b>Простой отчёт</b>

Дата начала: {$startDate}

Введите <b>дату окончания</b> отчёта в формате ДД.ММ.ГГГГ

Пример: 13.10.2025";
        
        // Set conversation state
        $conversation->notes['simple_report_start_date'] = $startDate;
        $conversation->notes['awaiting_simple_report_end_date'] = true;
        $conversation->notes['awaiting_simple_report_start_date'] = false;
        $conversation->update();
        
        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show filter type selection for simple report
     */
    private function showSimpleReportFilterMenu(string $chatId, int $userId, Conversation $conversation, string $startDate, string $endDate): ServerResponse
    {
        $text = "📊 <b>Простой отчёт</b>

📅 Период: {$startDate} - {$endDate}

Выберите <b>тип фильтра</b>:";
        
        // Set conversation state
        $conversation->notes['simple_report_start_date'] = $startDate;
        $conversation->notes['simple_report_end_date'] = $endDate;
        $conversation->notes['awaiting_simple_report_end_date'] = false;
        $conversation->update();
        
        $keyboard = [
            [
                ['text' => '👥 По ФН (функциональное направление)', 'callback_data' => 'proj_report_filter_by_fn']
            ],
            [
                ['text' => '⚡ Быстрые фильтры', 'callback_data' => 'proj_report_filter_quick']
            ],
            [
                ['text' => '📊 Без фильтра (все данные)', 'callback_data' => 'proj_report_simple_fn_all']
            ],
            [
                ['text' => '⬅️ Отмена', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show functional direction selection for simple report
     */
    private function showSimpleReportFnMenu(string $chatId, int $userId, Conversation $conversation, string $startDate, string $endDate): ServerResponse
    {
        $text = "📊 <b>Простой отчёт</b>

📅 Период: {$startDate} - {$endDate}

Выберите <b>Функциональное направление</b>:";
        
        // Set conversation state
        $conversation->notes['simple_report_start_date'] = $startDate;
        $conversation->notes['simple_report_end_date'] = $endDate;
        $conversation->notes['awaiting_simple_report_end_date'] = false;
        $conversation->update();
        
        // Get user access level to conditionally show Тестировщик
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        
        $keyboard = [
            [
                ['text' => '📊 Все ФН', 'callback_data' => 'proj_report_simple_fn_all']
            ],
            [
                ['text' => '👷‍♂️ Мобильная бригада', 'callback_data' => 'proj_report_simple_fn_Мобильная бригада']
            ],
            [
                ['text' => '⚙️ Пусконаладка', 'callback_data' => 'proj_report_simple_fn_Пусконаладка']
            ],
            [
                ['text' => '🔧 Шеф-монтаж', 'callback_data' => 'proj_report_simple_fn_Шеф-монтаж']
            ]
        ];
        
        // Add Тестировщик option only for admins and super admins
        if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
            $keyboard[] = [
                ['text' => '🧪 Тестировщик', 'callback_data' => 'proj_report_simple_fn_Тестировщик']
            ];
        }
        
        // Add back button
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => 'proj_report_filter_back']
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Show quick filter selection for simple report
     */
    private function showSimpleReportQuickFilterMenu(string $chatId, int $userId, Conversation $conversation): ServerResponse
    {
        $startDate = $conversation->notes['simple_report_start_date'] ?? '';
        $endDate = $conversation->notes['simple_report_end_date'] ?? '';
        
        $text = "⚡ <b>Быстрые фильтры</b>

📅 Период: {$startDate} - {$endDate}

Выберите нужный фильтр:";
        
        $keyboard = [
            [
                ['text' => '🔧 Гарантийные работы', 'callback_data' => 'proj_report_quick_warranty']
            ],
            [
                ['text' => '⚠️ Устранение замечаний РК', 'callback_data' => 'proj_report_quick_rk']
            ],
            [
                ['text' => '📝 Дополнительные работы', 'callback_data' => 'proj_report_quick_additional']
            ],
            [
                ['text' => '💤 Непроизводительное время', 'callback_data' => 'proj_report_quick_idle']
            ],
            [
                ['text' => '🚗 Командировочные расходы', 'callback_data' => 'proj_report_quick_travel']
            ],
            [
                ['text' => '📂 Документооборот', 'callback_data' => 'proj_report_quick_docs']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_report_filter_back']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Handle simple report date input
     */
    public function handleSimpleReportDateInput(string $chatId, int $userId, string $userInput, Conversation $conversation): ?ServerResponse
    {
        // Check if we're waiting for start date
        if (isset($conversation->notes['awaiting_simple_report_start_date']) && $conversation->notes['awaiting_simple_report_start_date']) {
            // Validate date format
            if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $userInput)) {
                $text = '⚠️ Неверный формат даты. Используйте формат ДД.ММ.ГГГГ (например, 01.10.2025)';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // Validate date
            $dateParts = explode('.', $userInput);
            if (!checkdate((int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2])) {
                $text = '⚠️ Некорректная дата. Проверьте введённые данные.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            return $this->showSimpleReportEndDateMenu($chatId, $userId, $conversation, $userInput);
        }
        
        // Check if we're waiting for end date
        if (isset($conversation->notes['awaiting_simple_report_end_date']) && $conversation->notes['awaiting_simple_report_end_date']) {
            // Validate date format
            if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $userInput)) {
                $text = '⚠️ Неверный формат даты. Используйте формат ДД.ММ.ГГГГ (например, 13.10.2025)';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // Validate date
            $dateParts = explode('.', $userInput);
            if (!checkdate((int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2])) {
                $text = '⚠️ Некорректная дата. Проверьте введённые данные.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            $startDate = $conversation->notes['simple_report_start_date'];
            return $this->showSimpleReportFilterMenu($chatId, $userId, $conversation, $startDate, $userInput);
        }
        
        return null;
    }

    /**
     * Generate and send simple report for functional direction
     */
    private function generateSimpleReport(string $chatId, int $userId, string $startDate, string $endDate, string $fn): ServerResponse
    {
        // Convert dates to SQL format
        $startParts = explode('.', $startDate);
        $endParts = explode('.', $endDate);
        $startSql = "{$startParts[2]}-{$startParts[1]}-{$startParts[0]} 00:00:00";
        $endSql = "{$endParts[2]}-{$endParts[1]}-{$endParts[0]} 23:59:59";
        
        // Get entries based on functional direction
        if ($fn === 'all') {
            $entries = $this->getSimpleReportEntries($startSql, $endSql, null);
            $fnText = 'Все ФН';
        } else {
            $entries = $this->getSimpleReportEntries($startSql, $endSql, $fn);
            $fnText = $fn;
        }
        
        if (empty($entries)) {
            $text = "⚠️ <b>Нет данных</b>

За период с {$startDate} по {$endDate} нет активностей для ФН: {$fnText}.";
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
                ]
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Create CSV file
        $fileName = $this->createSimpleReportCsv($startDate, $endDate, $fn, $entries);
        
        // Send status message first
        $text = "✅ <b>Простой отчёт сформирован</b>

📅 Период: {$startDate} - {$endDate}
🎯 ФН: {$fnText}
📄 Записей: " . count($entries) . "

Файл готов к скачиванию.";
        $this->messageService->sendMessage($chatId, $text);
        
        // Send the file
        $this->sendCsvFile($chatId, $fileName);
        
        // Log the export
        error_log("ProjectHandler: Logging simple report export - userId: {$userId}, fileName: {$fileName}, entries: " . count($entries));
        $logResult = $this->exportSecurityService->logExport(
            $userId,
            'report',
            "Простой отчёт ({$fnText})",
            count($entries),
            $fileName,
            null,  // no project code for simple reports
            $startSql,
            $endSql,
            $fn
        );
        error_log("ProjectHandler: Export log result: " . ($logResult ? 'SUCCESS' : 'FAILED'));
        
        // Send final menu
        $keyboard = [
            [
                ['text' => '🔄 Сформировать ещё один отчёт', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ],
            [
                ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, "👍 Готово!", $keyboard);
    }

    /**
     * Get tabel entries for simple report
     */
    private function getSimpleReportEntries(string $startDate, string $endDate, ?string $functionalDirection): array
    {
        // CRITICAL FIX: Expand date range to catch entries added on different dates
        // Users can add entries on 02.10.2025 but mark them for 01.10.2025
        // So we need to query a wider range and filter by message date
        
        // Expand range by 7 days in both directions
        $startDateTime = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);
        $startDateTime->modify('-7 days');
        $endDateTime->modify('+7 days');
        $expandedStartSql = $startDateTime->format('Y-m-d H:i:s');
        $expandedEndSql = $endDateTime->format('Y-m-d H:i:s');
        
        // Get all entries for the EXPANDED date range
        $rawEntries = $this->databaseService->getSimpleReportEntries($expandedStartSql, $expandedEndSql);
        
        // Convert target date range to DD.MM.YYYY format for comparison
        $targetStartDate = (new \DateTime($startDate))->format('d.m.Y');
        $targetEndDate = (new \DateTime($endDate))->format('d.m.Y');
        
        // Get all users to get FIO and FN mapping
        $allUsers = $this->databaseService->getAllUsers();
        $userMap = [];
        $userFnMap = [];
        
        // Create user ID to FIO and FN mapping
        foreach ($allUsers as $user) {
            $userMap[$user['user_id']] = $user['data']['fio'] ?? 'Неизвестно';
            $userFnMap[$user['user_id']] = $user['data']['functional_direction'] ?? 'Не указано';
        }
        
        $entries = [];
        foreach ($rawEntries as $row) {
            $userFn = $userFnMap[$row['user_id']] ?? 'Не указано';
            
            // CRITICAL: Exclude Тестировщик from all reports
            if ($userFn === 'Тестировщик') {
                continue;
            }
            
            // Filter by functional direction if specified
            if ($functionalDirection !== null && $userFn !== $functionalDirection) {
                continue;
            }
            
            // CRITICAL FIX: Extract date from MESSAGE and filter by it
            $messageDate = $this->extractDateFromMessage($row['message']);
            if ($messageDate === null) {
                // Cannot extract date - skip this entry
                continue;
            }
            
            // Convert to timestamp for comparison
            $messageDateObj = \DateTime::createFromFormat('d.m.Y', $messageDate);
            $targetStartDateObj = \DateTime::createFromFormat('d.m.Y', $targetStartDate);
            $targetEndDateObj = \DateTime::createFromFormat('d.m.Y', $targetEndDate);
            
            if (!$messageDateObj || !$targetStartDateObj || !$targetEndDateObj) {
                // Date parsing failed - skip
                continue;
            }
            
            // Check if message date is within target range
            if ($messageDateObj >= $targetStartDateObj && $messageDateObj <= $targetEndDateObj) {
                $entries[] = [
                    'user_id' => $row['user_id'],
                    'fio' => $userMap[$row['user_id']] ?? 'Неизвестно',
                    'fn' => $userFn,
                    'dt' => $row['dt'],
                    'message' => $row['message']
                ];
            }
        }
        
        return $entries;
    }

    /**
     * Extract date from message text
     * Returns date in DD.MM.YYYY format or null if not found
     */
    private function extractDateFromMessage(string $message): ?string
    {
        // Try project-based format: "✅DD.MM.YYYY, Проект..." (with optional space/newline after emoji)
        if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $message, $matches)) {
            return $matches[1];
        }
        
        // Try incident format: "😨 ⚠️ DD.MM.YYYY, Проект..."
        if (preg_match('/😨\s*⚠️\s*(\d{2}\.\d{2}\.\d{4}),\s*Проект/', $message, $matches)) {
            return $matches[1];
        }
        
        // Try current activity format: "Запись в табель от <b>DD.MM.YYYY</b>"
        if (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>/', $message, $matches)) {
            return $matches[1];
        }
        
        // Try old RK format: "Запись в табель от <b>DD.MM.YYYY</b>, в проект..."
        if (preg_match('/от <b>(\d{2}\.\d{2}\.\d{4})<\/b>,\s*в проект/', $message, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Generate and send simple report with quick filter
     */
    private function generateSimpleReportWithQuickFilter(string $chatId, int $userId, string $startDate, string $endDate, string $filterType): ServerResponse
    {
        // Convert dates to SQL format
        $startParts = explode('.', $startDate);
        $endParts = explode('.', $endDate);
        $startSql = "{$startParts[2]}-{$startParts[1]}-{$startParts[0]} 00:00:00";
        $endSql = "{$endParts[2]}-{$endParts[1]}-{$endParts[0]} 23:59:59";
        
        // Get all entries for the date range
        $allEntries = $this->getSimpleReportEntries($startSql, $endSql, null);
        
        // Apply quick filter
        $filteredEntries = $this->applyQuickFilter($allEntries, $filterType);
        $filterName = $this->getQuickFilterName($filterType);
        
        if (empty($filteredEntries)) {
            $text = "⚠️ <b>Нет данных</b>

За период с {$startDate} по {$endDate} нет активностей для фильтра: {$filterName}.";
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
                ]
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Create CSV file with filter name
        $fileName = $this->createFilteredReportCsv($startDate, $endDate, $filterType, $filteredEntries);
        
        // Send status message first
        $text = "✅ <b>Отчёт сформирован</b>

📅 Период: {$startDate} - {$endDate}
🎯 Фильтр: {$filterName}
📄 Записей: " . count($filteredEntries) . "

Файл готов к скачиванию.";
        $this->messageService->sendMessage($chatId, $text);
        
        // Send the file
        $this->sendCsvFile($chatId, $fileName);
        
        // Log the export
        error_log("ProjectHandler: Logging simple report export - userId: {$userId}, fileName: {$fileName}, entries: " . count($filteredEntries));
        $logResult = $this->exportSecurityService->logExport(
            $userId,
            'report',
            "Простой отчёт ({$filterName})",
            count($filteredEntries),
            $fileName,
            null,  // no project code for simple reports
            $startSql,
            $endSql,
            $filterType
        );
        error_log("ProjectHandler: Export log result: " . ($logResult ? 'SUCCESS' : 'FAILED'));
        
        // Send final menu
        $keyboard = [
            [
                ['text' => '🔄 Сформировать ещё один отчёт', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ],
            [
                ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Проекты']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, "👍 Готово!", $keyboard);
    }
    
    /**
     * Apply quick filter to entries
     */
    private function applyQuickFilter(array $entries, string $filterType): array
    {
        $filtered = [];
        
        foreach ($entries as $entry) {
            $message = mb_strtolower($entry['message']);
            $shouldInclude = false;
            
            switch ($filterType) {
                case 'warranty':
                    // Гарантийные работы
                    if (strpos($message, 'гарант') !== false || 
                        strpos($message, 'рабгарантии') !== false ||
                        strpos($message, 'шм_рабгарантии') !== false) {
                        $shouldInclude = true;
                    }
                    break;
                    
                case 'rk':
                    // Устранение замечаний РК
                    if (strpos($message, 'устрзам') !== false ||
                        strpos($message, 'уз_рк') !== false ||
                        strpos($message, 'шм_уз_рк') !== false ||
                        strpos($message, 'замечани') !== false) {
                        $shouldInclude = true;
                    }
                    break;
                    
                case 'additional':
                    // Дополнительные работы (Прочее)
                    if (strpos($message, 'прочее') !== false ||
                        strpos($message, 'шмр_прочее') !== false ||
                        strpos($message, 'моббриг_прочее') !== false) {
                        $shouldInclude = true;
                    }
                    break;
                    
                case 'idle':
                    // Непроизводительное время
                    if (strpos($message, 'простой') !== false ||
                        strpos($message, 'ожидание') !== false ||
                        strpos($message, 'шм_простой') !== false) {
                        $shouldInclude = true;
                    }
                    break;
                    
                case 'travel':
                    // Командировочные расходы
                    if (strpos($message, 'дорога') !== false ||
                        strpos($message, 'подготовка к') !== false ||
                        strpos($message, 'подготовкакпоездке') !== false ||
                        strpos($message, 'моббриг_подготовкакпоездке') !== false ||
                        strpos($message, 'шмр_дорога') !== false) {
                        $shouldInclude = true;
                    }
                    break;
                    
                case 'docs':
                    // Документооборот
                    if (strpos($message, 'сдача') !== false ||
                        strpos($message, 'документ') !== false ||
                        strpos($message, 'авансов') !== false ||
                        strpos($message, 'отчёт') !== false) {
                        $shouldInclude = true;
                    }
                    break;
            }
            
            if ($shouldInclude) {
                $filtered[] = $entry;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Get quick filter display name
     */
    private function getQuickFilterName(string $filterType): string
    {
        $filterNames = [
            'warranty' => '🔧 Гарантийные работы',
            'rk' => '⚠️ Устранение замечаний РК',
            'additional' => '📝 Дополнительные работы',
            'idle' => '💤 Непроизводительное время',
            'travel' => '🚗 Командировочные расходы',
            'docs' => '📂 Документооборот'
        ];
        
        return $filterNames[$filterType] ?? 'Неизвестный фильтр';
    }
    
    /**
     * Parse description and extract main activity and subcategories
     */
    private function parseDescription(string $desc): array
    {
        $desc = trim($desc);
        
        // Remove emojis with comprehensive regex
        $desc = preg_replace('/[\x{1F600}-\x{1FFFF}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2702}-\x{27B0}\x{24C2}-\x{1F251}]+/u', '', $desc);
        $desc = trim($desc);
        
        // Special handling for INCIDENT format - remove pause emoji from main
        if (strpos($desc, 'Простой') !== false) {
            // Remove pause emoji (both with and without variation selector)
            $desc = preg_replace('/⏸[\x{FE0F}]?/u', '', $desc);
            $desc = preg_replace('/\s+/', ' ', $desc); // Normalize whitespace
            $desc = trim($desc);
        }
        
        // Find first parenthesis
        $firstParen = strpos($desc, '(');
        if ($firstParen !== false) {
            $mainPart = trim(substr($desc, 0, $firstParen));
            $subsString = trim(substr($desc, $firstParen));
        } else {
            $mainPart = $desc;
            $subsString = '';
        }
        
        $subs = $this->extractSubs($subsString);
        
        // If we have one group without nested parentheses, split by commas
        if (count($subs) === 1 && strpos($subs[0], '(') === false && strpos($subs[0], ',') !== false) {
            $subs = array_map('trim', explode(',', $subs[0]));
        }
        
        return ['main' => $mainPart, 'subs' => $subs];
    }
    
    /**
     * Extract subcategories from parentheses string
     */
    private function extractSubs(string $s): array
    {
        $subs = [];
        $s = trim($s);
        
        if (empty($s)) {
            return $subs;
        }
        
        // Split by top-level parentheses
        $i = 0;
        $len = strlen($s);
        
        while ($i < $len) {
            if ($s[$i] === '(') {
                $start = $i;
                $count = 1;
                $i++;
                
                while ($i < $len && $count > 0) {
                    if ($s[$i] === '(') {
                        $count++;
                    } elseif ($s[$i] === ')') {
                        $count--;
                    }
                    $i++;
                }
                
                if ($count === 0) {
                    $sub = trim(substr($s, $start + 1, $i - $start - 2));
                    $subs[] = $sub;
                    // Skip spaces after closing bracket
                    while ($i < $len && $s[$i] === ' ') {
                        $i++;
                    }
                    continue;
                }
            } else {
                $i++;
            }
        }
        
        return $subs;
    }
    
    /**
     * Get process activity name based on process type
     */
    private function getProcessActivity(string $process): string
    {
        if ($process === 'Шеф-монтаж') {
            return 'Шеф-монтажные работы';
        } elseif ($process === 'Пусконаладка') {
            return 'Пусконаладочные работы';
        } elseif ($process === 'Мобильная бригада') {
            return 'Мобильная бригада';
        }
        return '';
    }
    
    /**
     * Determine ШМР sub-main category based on activity name
     * Maps activities to their parent categories in the menu hierarchy
     */
    private function getSMRSubMainCategory(string $activity): string
    {
        // Подготовительные работы activities
        $preparatoryWorks = ['Входной контроль', 'Завоз оборудования на площадку', 'Проверка монтажной площадки'];
        if (in_array($activity, $preparatoryWorks)) {
            return 'Подготовительные работы';
        }
        
        // Монтаж блоков activities
        $blockInstallation = ['Расстановка блоков'];
        if (in_array($activity, $blockInstallation)) {
            return 'Монтаж блоков';
        }
        
        // Сборка здания activities
        $buildingAssembly = ['Монтаж площадок обслуживания', 'Монтаж элементов укрытия', 'Сборка кровли'];
        if (in_array($activity, $buildingAssembly)) {
            return 'Сборка здания';
        }
        
        // Внутриблочный монтаж - Технологическое оборудование activities
        $techEquipment = ['Монтаж трубопроводов', 'Гидро/пневмо испытания', 'Монтаж технологического оборудования', 'Монтаж ЗРА', 'Протяжка соединений', 'Монтаж теплоизоляции'];
        if (in_array($activity, $techEquipment)) {
            return 'Внутриблочный монтаж';
        }
        
        // Default to Внутриблочный монтаж for equipment-related activities with " - " separator
        return 'Внутриблочный монтаж';
    }
    
    /**
     * Parse tabel entry message into structured report data
     */
    private function parseReportEntry(string $message, string $functionalDirection): array
    {
        // Initialize default values
        $result = [
            'project' => '',
            'date' => '',
            'process' => $functionalDirection,
            'vid' => '',
            'osnovnaya' => '',
            'tip1' => '',
            'tip2' => '',
            'tip3' => '',
            'tip4' => ''
        ];
        
        // Try to parse project-based entry format:
        // "<b>ФИО</b>\r\n✅Дата, Проект <b>КОД</b>: Добавлена запись в табель: <b>ОПИСАНИЕ</b>"
        // Note: ✅ may have optional space after it
        if (preg_match('/✅\s*(\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: .*? <b>(.*?)<\/b>/', $message, $matches)) {
            $result['date'] = $matches[1];
            $result['project'] = $matches[2];
            $description = $matches[3];
        } 
        // Try to parse INCIDENT format (Простой entries):
        // "<b>ФИО</b>\r\n😨 ⚠️ Дата, Проект <b>КОД</b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>ОПИСАНИЕ</b>"
        elseif (preg_match('/😨 ⚠️ (\d{2}\.\d{2}\.\d{4}), Проект <b>(.*?)<\/b>: ИНЦИДЕНТ - Добавлена запись в табель: <b>.*?(Простой .*?)<\/b>/', $message, $matches)) {
            $result['date'] = $matches[1];
            $result['project'] = $matches[2];
            $description = $matches[3];
        } 
        // Try to parse current activity format (Текущая деятельность):
        // "✅Запись в табель от <b>ДД.ММ.ГГГГ</b>: Текущая деятельность (<b>ОПИСАНИЕ</b>) добавлена"
        elseif (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>: Текущая деятельность \(<b>(.*?)<\/b>\) добавлена/', $message, $matches)) {
            $result['date'] = $matches[1];
            $result['project'] = '';
            $description = $matches[2];
        }
        // Try to parse old RK format:
        // "✅ Запись в табель от <b>ДД.ММ.ГГГГ</b>, в проект <b>КОД</b> о <b>ОПИСАНИЕ добавлена</b>"
        elseif (preg_match('/Запись в табель от <b>(\d{2}\.\d{2}\.\d{4})<\/b>, в проект <b>(.*?)<\/b> о <b>(.*?) добавлена<\/b>/', $message, $matches)) {
            $result['date'] = $matches[1];
            $result['project'] = $matches[2];
            $description = $matches[3];
        } else {
            // Cannot parse - return defaults
            return $result;
        }
        
        // Parse description
        $parsed = $this->parseDescription($description);
        $main = $parsed['main'];
        $subs = $parsed['subs'];
        
        $vid = $main;
        $osnovnaya = '';
        $tip1 = '';
        $tip2 = '';
        $tip3 = '';
        $tip4 = '';
        
        // Logic based on main activity type
        if (strpos($main, 'Прочее') !== false && isset($subs[0]) && $result['process'] === 'Пусконаладка') {
            // ПНР Equipment "Prochee" with or without custom text:
            // Format 1 (with comment): "Прочее (Электрооборудование, Дополнительные работы) (Настройка реле защиты)"
            // Format 2 (no comment): "Прочее (Электрооборудование, Дополнительные работы)"
            // Structure:
            // Level 1: Пусконаладочные работы
            // Level 2: Equipment type (e.g., Электрооборудование)
            // Level 3: Activity category (e.g., Дополнительные работы)
            // Level 4: Прочее
            // Level 5: Custom text (if provided) or empty
            
            $osnovnaya = $this->getProcessActivity($result['process']);  // "Пусконаладочные работы"
            $vid = $osnovnaya;
            
            // Parse first sub: "Equipment, Category" or just "Equipment"
            $firstSub = $subs[0];
            if (strpos($firstSub, ',') !== false) {
                $parts = explode(',', $firstSub, 2);
                $tip1 = trim($parts[0]);  // Equipment type
                $tip2 = trim($parts[1]);  // Category (e.g., "Дополнительные работы")
            } else {
                $tip1 = $firstSub;  // Equipment type only
                $tip2 = 'Дополнительные работы';  // EXPLICIT category for Прочее
            }
            
            $tip3 = 'Прочее';           // Level 4: "Prochee"
            $tip4 = $subs[1] ?? '';  // Level 5: Custom text (if provided)
        } elseif ($main === 'Прочее') {
            if (count($subs) >= 2) {
                if ($subs[0] === 'Проектная деятельность') {
                    $vid = $subs[1];
                    $osnovnaya = $subs[0];
                } else {
                    $vid = $subs[1];
                    $osnovnaya = $this->getProcessActivity($result['process']);
                    $tip1 = $subs[0];
                }
                // Add additional sub levels
                if (isset($subs[2])) {
                    $tip2 = $subs[2];
                }
                if (isset($subs[3])) {
                    $tip3 = $subs[3];
                }
                if (isset($subs[4])) {
                    $tip4 = $subs[4];
                }
            }
        } elseif ($main === 'Простой') {
            $vid = '';
            $osnovnaya = $main;
            // Parse idle time structure: can be 2-level or 3-level
            // 2-level: Простой (Category (Details)) - use process as tip1
            // 3-level: Простой (Process) (Category (Details)) - use Process as tip1
            
            if (count($subs) === 1 && strpos($subs[0], '(') !== false) {
                // Two-level structure: Простой (Category (Details))
                // Use process name as tip1
                $tip1 = $result['process'];
                $subParsed = $this->parseDescription($subs[0]);
                $tip2 = $subParsed['main'];
                if (!empty($subParsed['subs'])) {
                    $tip3 = $subParsed['subs'][0];
                    if (isset($subParsed['subs'][1])) {
                        $tip4 = $subParsed['subs'][1];
                    }
                }
            } else {
                // Three-level structure or simple one-level
                if (count($subs) >= 1) {
                    $tip1 = $subs[0]; // First reason (e.g., "Ожидание материалов")
                }
                if (count($subs) >= 2) {
                    // Check if second sub has nested structure
                    $subParsed = $this->parseDescription($subs[1]);
                    $tip2 = $subParsed['main'];
                    if (!empty($subParsed['subs'])) {
                        $tip3 = $subParsed['subs'][0];
                        if (isset($subParsed['subs'][1])) {
                            $tip4 = $subParsed['subs'][1];
                        }
                    }
                }
                // Additional simple subs
                if (count($subs) >= 3) {
                    $tip3 = $subs[2];
                }
                if (count($subs) >= 4) {
                    $tip4 = $subs[3];
                }
            }
        } elseif (($main === 'Работы по графику' || $main === 'Подписание акта ШМР' || $main === 'Шеф-монтажные работы' || 
                   $main === 'Работы неучтенные в ВОР' || strpos($main, 'Этап шеф-монтажа') === 0) && 
                   $result['process'] === 'Шеф-монтаж') {
            // ШМР activities ONLY - check process to avoid conflict with ПНР
            // For ШМР activities, determine the structure based on subcategories
            // Structure varies by activity type:
            // Type 1 (with " - "): ШМР → Внутриблочный монтаж → Equipment → Activity → Action
            // Type 2 (no " - "): ШМР → Category → Activity → Action
            
            $osnovnaya = $this->getProcessActivity($result['process']);  // "Шеф-монтажные работы"
            
            // Handle combined format with " - " separator: "Activity - Equipment"
            if (isset($subs[0]) && strpos($subs[0], ' - ') !== false) {
                // Type 1: Внутриблочный монтаж / Технологическое оборудование
                // Example: "Подключение оборудования - Электрооборудование"
                $parts = explode(' - ', $subs[0], 2);
                $activityName = trim($parts[0]);  // e.g., "Подключение оборудования"
                $equipmentType = trim($parts[1]); // e.g., "Электрооборудование"
                
                // Determine sub-main category
                $tip1 = $this->getSMRSubMainCategory($activityName);  // Level 2: Внутриблочный монтаж
                $tip2 = $equipmentType;                               // Level 3: Equipment type
                $tip3 = $activityName;                                // Level 4: Activity type
                $tip4 = $main;                                        // Level 5: Final action
                $vid = $osnovnaya;
            } else {
                // Type 2: Other categories (Подготовительные работы, Монтаж блоков, Сборка здания)
                // Example: "Входной контроль"
                // UNIFIED STRUCTURE: Move final action to Level 5 for consistency
                if (isset($subs[0])) {
                    $activityName = $subs[0];
                    $tip1 = $this->getSMRSubMainCategory($activityName);  // Level 2: Category
                    $tip2 = $activityName;                                // Level 3: Activity
                    $tip3 = '';                                            // Level 4: (empty - unified structure)
                    $tip4 = $main;                                        // Level 5: Final action (MOVED HERE)
                    $vid = $osnovnaya;
                } else {
                    // Fallback for edge cases
                    $tip1 = '';
                    $tip2 = '';
                    $tip3 = '';
                    $tip4 = $main;
                    $vid = $osnovnaya;
                }
            }
        } elseif ($main === 'Участие в ЗПИ' || 
                   strpos($main, 'Разработка программы работ') !== false || 
                   strpos($main, 'Разработка графика работ') !== false ||
                   $main === 'Организация допоставки' ||
                   $main === 'Участие в ОПИ' ||
                   strpos($main, 'Исполнение обязанностей мастера') !== false ||
                   strpos($main, 'Шеф-наладочные работы') !== false) {
            // Проектная деятельность structure:
            // Level 1: Проектная деятельность
            // Level 2: Activity type (e.g., Разработка программы работ)
            // Level 3: Sub-activity (e.g., for ЗПИ: Проверка/наладка шкафов) - if present
            // Level 4-5: (empty)
            $osnovnaya = 'Проектная деятельность';
            $tip1 = $main;  // Level 2: Activity type
            // Level 3: ZPI sub-activity detail (if present)
            $tip2 = (!empty($subs) && $main === 'Участие в ЗПИ') ? $subs[0] : '';
            $tip3 = '';  // Level 4: (empty)
            $tip4 = '';  // Level 5: (empty)
            $vid = $osnovnaya;
        } elseif (strpos($main, 'Медицинский осмотр') !== false || 
                  strpos($main, 'Аттестация') !== false ||
                  strpos($main, 'Подготовка ТКП') !== false || 
                  strpos($main, 'Подготовка материала к ТКП') !== false ||
                  strpos($main, 'Общая деятельность') !== false) {
            // Текущая деятельность structure:
            // Level 1: Текущая деятельность (main process)
            // Level 2: Activity type (e.g., Медицинский осмотр, Подготовка ТКП)
            // Level 3: Sub-type if exists (e.g., БН ОПНВГ for ТКП)
            // Level 4: (empty)
            // Level 5: (empty)
            $osnovnaya = 'Текущая деятельность';
            $vid = $osnovnaya;
            
            // Check for ТКП sub-types in parentheses
            if (isset($subs[0])) {
                // Format: "Подготовка ТКП (БН ОПНВГ)"
                $tip1 = $main;     // Level 2: Main activity
                $tip2 = $subs[0];  // Level 3: Sub-type
                $tip3 = '';        // Level 4: (empty)
                $tip4 = '';        // Level 5: (empty)
            } else {
                // Simple format: "Медицинский осмотр"
                $tip1 = $main;     // Level 2: Activity
                $tip2 = '';        // Level 3: (empty)
                $tip3 = '';        // Level 4: (empty)
                $tip4 = '';        // Level 5: (empty)
            }
        } elseif (strpos($main, 'Прочее') !== false && isset($subs[0]) && $result['process'] === 'Пусконаладка') {
            // ПНР Equipment "Prochee" with or without custom text:
            // Format 1 (with comment): "Прочее (Электрооборудование, Дополнительные работы) (Настройка реле защиты)"
            // Format 2 (no comment): "Прочее (Электрооборудование, Дополнительные работы)"
            // Structure:
            // Level 1: Пусконаладочные работы
            // Level 2: Equipment type (e.g., Электрооборудование)
            // Level 3: Activity category (e.g., Дополнительные работы)
            // Level 4: Прочее
            // Level 5: Custom text (if provided) or empty
            
            $osnovnaya = $this->getProcessActivity($result['process']);  // "Пусконаладочные работы"
            $vid = $osnovnaya;
            
            // Parse first sub: "Equipment, Category" or just "Equipment"
            $firstSub = $subs[0];
            if (strpos($firstSub, ',') !== false) {
                $parts = explode(',', $firstSub, 2);
                $tip1 = trim($parts[0]);  // Equipment type
                $tip2 = trim($parts[1]);  // Category (e.g., "Дополнительные работы")
            } else {
                $tip1 = $firstSub;  // Equipment type only
                $tip2 = 'Дополнительные работы';  // EXPLICIT category for Прочее
            }
            
            $tip3 = 'Прочее';           // Level 4: "Prochee"
            $tip4 = $subs[1] ?? '';  // Level 5: Custom text (if provided)
        } elseif (in_array($main, ['Изменение схемы подключения', 'Прокладка кабеля', 'Подключение кабеля',
                          'Монтаж оборудования', 'Монтаж оборудования АСУ ТП', 'Удаленный ПНР ПО',
                          'Изменение конфигурации СИ', 'Калибровка/поверка оборудования', 'КМХ СИ', 'Коррекция характеристик СИ',
                          'Демонтаж/ревизия арматуры', 'Демонтаж/ревизия техн. оборудования']) && isset($subs[0]) && $result['process'] === 'Пусконаладка') {
            // ПНР Equipment specific activities (Dop. works sub-activities):
            // Format: "Изменение схемы подключения (Электрооборудование, Дополнительные работы)"
            // Structure:
            // Level 1: Пусконаладочные работы
            // Level 2: Equipment type (e.g., Электрооборудование)
            // Level 3: Дополнительные работы (ALWAYS for these activities)
            // Level 4: Specific activity (e.g., Изменение схемы подключения)
            // Level 5: (empty)
            
            $osnovnaya = $this->getProcessActivity($result['process']);  // "Пусконаладочные работы"
            $vid = $osnovnaya;
            
            // Parse first sub: "Equipment, Category" or just "Equipment"
            $firstSub = $subs[0];
            if (strpos($firstSub, ',') !== false) {
                $parts = explode(',', $firstSub, 2);
                $tip1 = trim($parts[0]);  // Equipment type
                $tip2 = trim($parts[1]);  // Category (should be "Дополнительные работы")
                $tip3 = $main;            // Level 4: Specific activity
                $tip4 = '';                // Level 5: (empty)
            } else {
                // No category in sub, but these activities are ALWAYS under "Дополнительные работы"
                $tip1 = $firstSub;                        // Equipment type only
                $tip2 = 'Дополнительные работы';  // Level 3: EXPLICIT category
                $tip3 = $main;                            // Level 4: Specific activity
                $tip4 = '';                                // Level 5: (empty)
            }
        } elseif ($main === 'Работы по гарантии' || strpos($main, 'Устранени') === 0) {
            // Handle: "Работы по гарантии (Электрооборудование)"
            // Handle: "Устранения замечаний РК (Раздел ТХ)"
            // Handle: "Устранение замечаний РК (Шеф-монтаж)"
            $vid = $main;
            $osnovnaya = $main;
            $tip1 = $subs[0] ?? '';
            // Check for nested details
            if (isset($subs[1])) {
                $tip2 = $subs[1];
            }
            if (isset($subs[2])) {
                $tip3 = $subs[2];
            }
            if (isset($subs[3])) {
                $tip4 = $subs[3];
            }
        } elseif (in_array($main, ['Работы по графику', 'Формирование ИД']) && isset($subs[0]) && $result['process'] === 'Пусконаладка') {
            // ПНР Equipment activities - IMPORTANT: check process to avoid conflict with ШМР
            // Format: "Работы по графику (Электрооборудование)"
            // Structure:
            // Level 1: Пусконаладочные работы
            // Level 2: Equipment type (e.g., Электрооборудование)
            // Level 3: (empty)
            // Level 4: (empty)
            // Level 5: Final action (e.g., Работы по графику)
            
            $osnovnaya = $this->getProcessActivity($result['process']);  // "Пусконаладочные работы"
            $vid = $osnovnaya;
            
            // Parse first sub: "Equipment, Category" or just "Equipment"
            $firstSub = $subs[0];
            if (strpos($firstSub, ',') !== false) {
                $parts = explode(',', $firstSub, 2);
                $tip1 = trim($parts[0]);  // Equipment type
                $tip2 = trim($parts[1]);  // Category (e.g., "Дополнительные работы")
                $tip3 = '';                // Level 4: (empty for uniformity)
                $tip4 = $main;            // Level 5: Final action
            } else {
                $tip1 = $firstSub;        // Equipment type only
                $tip2 = '';                // Level 3: (empty)
                $tip3 = '';                // Level 4: (empty)
                $tip4 = $main;            // Level 5: Final action
            }
        } elseif (in_array($main, ['Комплексное опробование', 'ИИ на рабочей среде', 'Подготовка к служебной поездке',
                          'Дорога', 'Инструктаж', 'Сдача авансового отчета']) && $result['process'] === 'Пусконаладка') {
            // ПНР Simple activities (no equipment) - format: "Комплексное опробование"
            // Structure:
            // Level 1: Пусконаладочные работы
            // Level 2: (empty)
            // Level 3: (empty)
            // Level 4: (empty)
            // Level 5: Activity name (e.g., Комплексное опробование)
            
            $osnovnaya = $this->getProcessActivity($result['process']);  // "Пусконаладочные работы"
            $vid = $osnovnaya;
            $tip1 = '';  // Level 2: (empty)
            $tip2 = '';  // Level 3: (empty)
            $tip3 = '';  // Level 4: (empty)
            $tip4 = $main;  // Level 5: Activity
        } elseif (in_array($main, ['Подтвердить достижение вехи']) && isset($subs[0]) && $result['process'] === 'Пусконаладка') {
            // ПНР Milestone confirmation - separate from ШМР
            $osnovnaya = $this->getProcessActivity($result['process']);
            $vid = $osnovnaya;
            $tip1 = $subs[0];  // Equipment type
            $tip2 = '';
            $tip3 = '';
            $tip4 = $main;     // Final action
        } elseif ($main === 'Пусконаладочные работы' && isset($subs[0]) && $result['process'] === 'Пусконаладка') {
            // Handle generic "Пусконаладочные работы" entries with equipment details
            // Format: "Пусконаладочные работы (Электрооборудование, Дополнительные работы)"
            // Structure:
            // Level 1: Пусконаладочные работы
            // Level 2: Equipment type (e.g., Электрооборудование)
            // Level 3: Category (e.g., Дополнительные работы)
            // Level 4: (empty)
            // Level 5: (empty)
            
            $osnovnaya = $main;  // "Пусконаладочные работы"
            $vid = $osnovnaya;
            
            // Parse first sub: "Equipment, Category" or just "Equipment"
            $firstSub = $subs[0];
            if (strpos($firstSub, ',') !== false) {
                $parts = explode(',', $firstSub, 2);
                $tip1 = trim($parts[0]);  // Equipment type
                $tip2 = trim($parts[1]);  // Category
                $tip3 = '';                // Level 4: (empty)
                $tip4 = '';                // Level 5: (empty)
            } else {
                $tip1 = $firstSub;        // Equipment type only
                $tip2 = '';                // Level 3: (empty)
                $tip3 = '';                // Level 4: (empty)
                $tip4 = '';                // Level 5: (empty)
            }
        } elseif (empty($subs)) {
            // No subcategories - main is the activity itself
            // This handles: "Общая деятельность", "Шеф-монтажные работы добавлена", etc.
            $vid = $main;
            $osnovnaya = $main;
            $tip1 = '';  // Level 2: (empty)
            $tip2 = '';  // Level 3: (empty)
            $tip3 = '';  // Level 4: (empty)
            $tip4 = '';  // Level 5: (empty)
            
            // Special handling for "Шеф-монтажные работы" without details
            if ($main === 'Шеф-монтажные работы') {
                $osnovnaya = $main;
            }
        } else {
            // Default logic based on process
            if ($result['process'] === 'Мобильная бригада') {
                // Mobile brigade: main activity is the primary work type
                // Example: "Электромонтажные работы (Досборка)"
                $osnovnaya = $main;       // Основной вид работ
                $tip1 = $subs[0] ?? '';   // Подтип работ
                $tip2 = $subs[1] ?? '';   // Детали
                $tip3 = $subs[2] ?? '';   // Примечание
                $tip4 = $subs[3] ?? '';   // Дополнительные детали
            } else {
                // Other processes (PNR, SMR, etc.)
                // IMPORTANT: Check if first sub contains comma-separated values (Equipment, Category)
                $osnovnaya = $main;       // Основной вид работ
                
                // Try to split first sub by comma if it exists
                if (isset($subs[0]) && strpos($subs[0], ',') !== false) {
                    $parts = explode(',', $subs[0], 2);
                    $tip1 = trim($parts[0]);  // Equipment type
                    $tip2 = trim($parts[1]);  // Category
                    $tip3 = $subs[1] ?? '';   // Примечание
                    $tip4 = $subs[2] ?? '';   // Дополнительные детали
                } else {
                    $tip1 = $subs[0] ?? '';   // Подтип работ
                    $tip2 = $subs[1] ?? '';   // Детали
                    $tip3 = $subs[2] ?? '';   // Примечание
                    $tip4 = $subs[3] ?? '';   // Дополнительные детали
                }
            }
        }
        
        $result['vid'] = $vid;
        $result['osnovnaya'] = $osnovnaya;
        $result['tip1'] = $tip1;
        $result['tip2'] = $tip2;
        $result['tip3'] = $tip3;
        $result['tip4'] = $tip4;
        
        return $result;
    }
    
    /**
     * Create CSV file for simple report
     */
    private function createSimpleReportCsv(string $startDate, string $endDate, string $fn, array $entries): string
    {
        $uploadsDir = __DIR__ . '/../../Uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $fnSafe = ($fn === 'all') ? 'all' : preg_replace('/[^a-zA-Z0-9_-]/', '_', $fn);
        $fileName = "simple_report_{$fnSafe}_{$timestamp}.csv";
        $filePath = $uploadsDir . '/' . $fileName;
        
        $output = fopen($filePath, 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fprintf($output, "\xEF\xBB\xBF");
        
        // Write header - UNIFIED FORMAT matching menu structure
        fputcsv($output, ['Проект', 'Дата', 'Процесс (ФН)', 'Уровень 1', 'Уровень 2', 'Уровень 3', 'Уровень 4', 'Уровень 5', 'Фамилия']);
        
        // Write data
        foreach ($entries as $entry) {
            // Skip milestone entries (вехи)
            if ($this->isMilestoneEntry($entry['message'])) {
                continue;
            }
            
            $parsed = $this->parseReportEntry($entry['message'], $entry['fn']);
            // NEW ORDER: Project, Date, Process, Level 1, Level 2, Level 3, Level 4, Level 5, Name
            // Apply sanitization to prevent CSV issues with long texts
            fputcsv($output, [
                $this->sanitizeForCsv($parsed['project']),
                $parsed['date'],  // Date doesn't need sanitization
                $this->sanitizeForCsv($parsed['process']),
                $this->sanitizeForCsv($parsed['osnovnaya']),  // Уровень 1
                $this->sanitizeForCsv($parsed['tip1']),        // Уровень 2
                $this->sanitizeForCsv($parsed['tip2']),        // Уровень 3
                $this->sanitizeForCsv($parsed['tip3']),        // Уровень 4
                $this->sanitizeForCsv($parsed['tip4'] ?? ''),  // Уровень 5 (если есть)
                $this->sanitizeForCsv($entry['fio'])
            ]);
        }
        
        fclose($output);
        
        return $fileName;
    }
    
    /**
     * Create CSV file for filtered report
     */
    private function createFilteredReportCsv(string $startDate, string $endDate, string $filterType, array $entries): string
    {
        $uploadsDir = __DIR__ . '/../../Uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $fileName = "filtered_report_{$filterType}_{$timestamp}.csv";
        $filePath = $uploadsDir . '/' . $fileName;
        
        $output = fopen($filePath, 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fprintf($output, "\xEF\xBB\xBF");
        
        // Write header - UNIFIED FORMAT matching menu structure
        fputcsv($output, ['Проект', 'Дата', 'Процесс (ФН)', 'Уровень 1', 'Уровень 2', 'Уровень 3', 'Уровень 4', 'Уровень 5', 'Фамилия']);
        
        // Write data
        foreach ($entries as $entry) {
            // Skip milestone entries (вехи)
            if ($this->isMilestoneEntry($entry['message'])) {
                continue;
            }
            
            $parsed = $this->parseReportEntry($entry['message'], $entry['fn']);
            // NEW ORDER: Project, Date, Process, Level 1, Level 2, Level 3, Level 4, Level 5, Name
            // Apply sanitization to prevent CSV issues with long texts
            fputcsv($output, [
                $this->sanitizeForCsv($parsed['project']),
                $parsed['date'],  // Date doesn't need sanitization
                $this->sanitizeForCsv($parsed['process']),
                $this->sanitizeForCsv($parsed['osnovnaya']),  // Уровень 1
                $this->sanitizeForCsv($parsed['tip1']),        // Уровень 2
                $this->sanitizeForCsv($parsed['tip2']),        // Уровень 3
                $this->sanitizeForCsv($parsed['tip3']),        // Уровень 4
                $this->sanitizeForCsv($parsed['tip4'] ?? ''),  // Уровень 5 (если есть)
                $this->sanitizeForCsv($entry['fio'])
            ]);
        }
        
        fclose($output);
        
        return $fileName;
    }
    
    /**
     * Check if a message entry is a milestone (веха) entry
     * Milestone entries should be excluded from CSV reports
     */
    private function isMilestoneEntry(string $message): bool
    {
        // Milestone patterns to detect:
        // 1. "✅ Этап шеф-монтажа завершен" (ШМР milestones)
        // 2. "✅ Этап наладки завершен" (ПНР milestones)
        // 3. "✅ Подтвердить достижение вехи" (general milestone confirmations)
        $milestonePatterns = [
            '/Этап шеф-монтажа завершен/u',
            '/Этап наладки завершен/u',
            '/Подтвердить достижение вехи/u'
        ];
        
        foreach ($milestonePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize text for CSV export (handle long texts and line breaks)
     * @param string $text Text to sanitize
     * @param int $maxLength Maximum length (default 2000 characters)
     * @return string Sanitized text
     */
    private function sanitizeForCsv(string $text, int $maxLength = 2000): string
    {
        if (empty($text)) {
            return $text;
        }
        
        // Replace line breaks with pipe separator
        $text = str_replace(["\r\n", "\r", "\n"], " | ", $text);
        
        // Trim excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Limit length
        if (mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength) . '...';
        }
        
        return $text;
    }
    
    /**
     * Check export permission and handle export key verification
     */
    private function checkExportPermission(string $chatId, int $userId, $conversation, string $exportType = 'notes'): ?ServerResponse
    {
        // Check 1: Can user export?
        if (!$this->exportSecurityService->canUserExport($userId)) {
            $message = $this->exportSecurityService->getExportPermissionMessage($userId);
            return $this->messageService->sendMessage($chatId, $message);
        }
        
        // Check 2: Does user have export key?
        if (!$this->exportSecurityService->hasExportKey($userId)) {
            $text = "⚠️ <b>Кодовое слово не установлено</b>\n\n";
            $text .= "Установите кодовое слово в профиле для выгрузки данных.\n\n";
            $text .= "Профиль → 🔐 Кодовое слово";
            
            $keyboard = [
                [['text' => '🔐 Установить сейчас', 'callback_data' => 'export_key_setup']],
                [['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты']]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Check 3: Prompt for export key verification
        $conversation->notes['awaiting_export_key_verify'] = true;
        $conversation->notes['export_pending_type'] = $exportType;
        $conversation->update();
        
        $text = "🔐 <b>Введите кодовое слово</b>\n\nДля безопасности введите ваше кодовое слово:";
        return $this->messageService->sendMessage($chatId, $text);
    }
    
    /**
     * Send ZIP file to user (for password-protected exports)
     */
    private function sendZipFile(string $chatId, string $fileName, string $caption = '📦 Защищённая выгрузка'): bool
    {
        $filePath = __DIR__ . '/../../Uploads/' . $fileName;
        
        if (!file_exists($filePath)) {
            error_log("ProjectHandler: ZIP file not found: {$filePath}");
            return false;
        }
        
        try {
            $result = \Longman\TelegramBot\Request::sendDocument([
                'chat_id' => $chatId,
                'document' => \Longman\TelegramBot\Request::encodeFile($filePath),
                'caption' => $caption
            ]);
            
            // Clean up file after sending
            if ($result->isOk()) {
                unlink($filePath);
            }
            
            return $result->isOk();
        } catch (\Exception $e) {
            error_log("ProjectHandler: Error sending ZIP file: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // NOTES EXPORT METHODS
    // ========================================
    
    /**
     * Show notes export type selection menu
     */
    private function showNotesExportTypeMenu(string $chatId, int $userId): ServerResponse
    {
        $text = "📌 <b>Выгрузка заметок</b>\n\nВыберите тип выгрузки:";
        
        $keyboard = [
            [
                ['text' => '📋 Общий журнал заметок', 'callback_data' => 'proj_notes_type_all']
            ],
            [
                ['text' => '📁 Заметки по проекту', 'callback_data' => 'proj_notes_type_byproject']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show project selection menu for notes export
     */
    private function showNotesExportProjectMenu(string $chatId, int $userId, int $page = 1): ServerResponse
    {
        // Get all projects
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector(
            $this->databaseService
        );
        $allProjects = $projectSelector->getAllProjectsIncludingPending();
        
        // Apply intelligent sorting
        $allProjects = $this->databaseService->sortProjectsIntelligently($allProjects);
        
        if (empty($allProjects)) {
            $text = '⚠️ <b>Нет доступных проектов</b>';
            $keyboard = [[
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_report_notes']
            ]];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // PAGINATION: 33 projects per page
        $perPage = 33;
        $totalProjects = count($allProjects);
        $totalPages = (int)ceil($totalProjects / $perPage);
        
        // Check if page is valid
        if ($page < 1) $page = 1;
        if ($page > $totalPages) $page = $totalPages;
        
        // Get projects for current page
        $offset = ($page - 1) * $perPage;
        $projectsOnPage = array_slice($allProjects, $offset, $perPage);
        
        $text = "📁 <b>Заметки по проекту</b>\n\nВыберите проект:";
        $text .= "\nСтраница {$page} из {$totalPages} (Всего: {$totalProjects} проектов)";
        
        $keyboard = [];
        
        // Create buttons for projects (3 per row)
        $row = [];
        foreach ($projectsOnPage as $index => $projectCode) {
            $row[] = ['text' => $projectCode, 'callback_data' => "proj_notes_project_{$projectCode}"];
            
            if (count($row) == 3 || $index == count($projectsOnPage) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        // Add pagination navigation buttons
        if ($totalPages > 1) {
            $navRow = [];
            
            if ($page > 1) {
                $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => "proj_notes_project_page_" . ($page - 1)];
            }
            
            $navRow[] = ['text' => "📖 {$page}/{$totalPages}", 'callback_data' => "proj_notes_project_page_{$page}"];
            
            if ($page < $totalPages) {
                $navRow[] = ['text' => '➡️ Вперед', 'callback_data' => "proj_notes_project_page_" . ($page + 1)];
            }
            
            $keyboard[] = $navRow;
        }
        
        // Add back button
        $keyboard[] = [[
            'text' => '⬅️ Назад',
            'callback_data' => 'proj_report_notes'
        ]];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show status filter menu for notes export
     */
    private function showNotesExportStatusFilterMenu(string $chatId, int $userId, ?string $projectCode, $conversation): ServerResponse
    {
        if ($projectCode) {
            $text = "📁 <b>Заметки по проекту: {$projectCode}</b>\n\nВыберите фильтр по статусу:";
            $backCallback = "proj_notes_type_byproject";
        } else {
            $text = "📋 <b>Общий журнал заметок</b>\n\nВыберите фильтр по статусу:";
            $backCallback = "proj_report_notes";
        }
        
        $projectParam = $projectCode ?? 'all';
        
        $keyboard = [
            [
                ['text' => '✅ Активные', 'callback_data' => "proj_notes_generate_active_{$projectParam}"]
            ],
            [
                ['text' => '❌ Закрытые', 'callback_data' => "proj_notes_generate_closed_{$projectParam}"]
            ],
            [
                ['text' => '📊 Все', 'callback_data' => "proj_notes_generate_all_{$projectParam}"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => $backCallback]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show date range selection for notes export
     */
    private function showNotesExportDateRangeMenu(string $chatId, int $userId, $conversation, ?string $projectCode, string $statusFilter): ServerResponse
    {
        // Save status filter to conversation
        $conversation->notes['notes_export_status_filter'] = $statusFilter;
        $conversation->notes['notes_export_project_code'] = $projectCode;
        $conversation->update();
        
        if ($projectCode && $projectCode !== 'all') {
            $text = "📁 <b>Заметки по проекту: {$projectCode}</b>\n";
        } else {
            $text = "📋 <b>Общий журнал заметок</b>\n";
        }
        
        $statusText = [
            'active' => '✅ Активные',
            'closed' => '❌ Закрытые',
            'all' => '📊 Все'
        ][$statusFilter];
        
        $text .= "Фильтр: {$statusText}\n\nВыберите период:";
        
        $projectParam = $projectCode ?? 'all';
        
        $keyboard = [
            [
                ['text' => '📅 Сегодня', 'callback_data' => "proj_notes_period_{$statusFilter}_{$projectParam}_today"]
            ],
            [
                ['text' => '📅 Вчера', 'callback_data' => "proj_notes_period_{$statusFilter}_{$projectParam}_yesterday"]
            ],
            [
                ['text' => '📅 Последние 7 дней', 'callback_data' => "proj_notes_period_{$statusFilter}_{$projectParam}_week"]
            ],
            [
                ['text' => '📅 Последние 30 дней', 'callback_data' => "proj_notes_period_{$statusFilter}_{$projectParam}_month"]
            ],
            [
                ['text' => '📅 Весь период', 'callback_data' => "proj_notes_period_{$statusFilter}_{$projectParam}_all"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => "proj_notes_filter_back_{$projectParam}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show start date selection for notes export custom period
     */
    private function showNotesExportStartDateMenu(string $chatId, int $userId, $conversation, ?string $projectCode, string $statusFilter): ServerResponse
    {
        if ($projectCode) {
            $text = "📁 <b>Заметки по проекту: {$projectCode}</b>\n\n";
        } else {
            $text = "📋 <b>Общий журнал заметок</b>\n\n";
        }
        
        $text .= "Введите <b>дату начала</b> в формате ДД.ММ.ГГГГ\n\nПример: 01.10.2025";
        
        // Set conversation state
        $conversation->notes['awaiting_notes_export_start_date'] = true;
        $conversation->notes['notes_export_project_code'] = $projectCode;
        $conversation->notes['notes_export_status_filter'] = $statusFilter;
        $conversation->update();
        
        $projectParam = $projectCode ?? 'all';
        
        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => "proj_notes_filter_{$statusFilter}_{$projectParam}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show end date selection for notes export custom period
     */
    private function showNotesExportEndDateMenu(string $chatId, int $userId, $conversation, ?string $projectCode, string $statusFilter, string $startDate): ServerResponse
    {
        if ($projectCode) {
            $text = "📁 <b>Заметки по проекту: {$projectCode}</b>\n";
        } else {
            $text = "📋 <b>Общий журнал заметок</b>\n";
        }
        
        $text .= "Дата начала: {$startDate}\n\nВведите <b>дату окончания</b> в формате ДД.ММ.ГГГГ\n\nПример: 13.10.2025";
        
        // Set conversation state
        $conversation->notes['notes_export_start_date'] = $startDate;
        $conversation->notes['awaiting_notes_export_end_date'] = true;
        $conversation->notes['awaiting_notes_export_start_date'] = false;
        $conversation->update();
        
        $projectParam = $projectCode ?? 'all';
        
        $keyboard = [
            [
                ['text' => '⬅️ Отмена', 'callback_data' => "proj_notes_filter_{$statusFilter}_{$projectParam}"]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle notes export custom date input (called from GenericmessageCommand)
     */
    public function handleNotesExportDateInput(string $chatId, int $userId, string $userInput, $conversation): ?ServerResponse
    {
        $projectCode = $conversation->notes['notes_export_project_code'] ?? null;
        $statusFilter = $conversation->notes['notes_export_status_filter'] ?? 'all';
        
        // Check if we're waiting for start date
        if (isset($conversation->notes['awaiting_notes_export_start_date']) && $conversation->notes['awaiting_notes_export_start_date']) {
            // Validate date format
            if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $userInput)) {
                $text = '⚠️ Неверный формат даты. Используйте формат ДД.ММ.ГГГГ (например, 01.10.2025)';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // Validate date
            $dateParts = explode('.', $userInput);
            if (!checkdate((int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2])) {
                $text = '⚠️ Некорректная дата. Проверьте введённые данные.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            return $this->showNotesExportEndDateMenu($chatId, $userId, $conversation, $projectCode, $statusFilter, $userInput);
        }
        
        // Check if we're waiting for end date
        if (isset($conversation->notes['awaiting_notes_export_end_date']) && $conversation->notes['awaiting_notes_export_end_date']) {
            // Validate date format
            if (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $userInput)) {
                $text = '⚠️ Неверный формат даты. Используйте формат ДД.ММ.ГГГГ (например, 13.10.2025)';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // Validate date
            $dateParts = explode('.', $userInput);
            if (!checkdate((int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2])) {
                $text = '⚠️ Некорректная дата. Проверьте введённые данные.';
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            $startDate = $conversation->notes['notes_export_start_date'];
            
            // Clear conversation state
            $conversation->notes['awaiting_notes_export_end_date'] = false;
            $conversation->notes['awaiting_notes_export_start_date'] = false;
            $conversation->update();
            
            // Generate notes export with custom date range
            return $this->generateNotesExportCustom($chatId, $userId, $conversation, $projectCode, $statusFilter, $startDate, $userInput);
        }
        
        return null;
    }
    
    /**
     * Generate notes export for all period (no date filtering)
     */
    private function generateNotesExportAll(string $chatId, int $userId, $conversation, ?string $projectCode, string $statusFilter): ServerResponse
    {
        // NOTE: Export permission already checked before calling this method
        // No need to check again here
        
        // Get all notes without date filtering
        $notes = $this->getProjectNotes($projectCode, null, date('Y-m-d 23:59:59'), $statusFilter);
        
        if (empty($notes)) {
            if ($projectCode) {
                $text = "⚠️ <b>Нет данных</b>\n\nНет заметок по проекту {$projectCode}.";
            } else {
                $text = "⚠️ <b>Нет данных</b>\n\nВ системе нет заметок.";
            }
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
                ]
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Create CSV file
        $fileName = $this->createNotesExportCsv($projectCode, $notes, 'all', $statusFilter);
        
        // Send status message
        $statusText = [
            'active' => '✅ Активные',
            'closed' => '❌ Закрытые',
            'all' => '📊 Все'
        ][$statusFilter];
        
        if ($projectCode) {
            $text = "✅ <b>Выгрузка заметок сформирована</b>\n\n📁 Проект: {$projectCode}\n📋 Фильтр: {$statusText}\n📌 Заметок: " . count($notes);
        } else {
            $text = "✅ <b>Выгрузка заметок сформирована</b>\n\n📋 Общий журнал\n📋 Фильтр: {$statusText}\n📌 Заметок: " . count($notes);
        }
        $this->messageService->sendMessage($chatId, $text);
        
        // Send CSV file with notes caption
        $this->sendCsvFile($chatId, $fileName, '📌 Журнал заметок');
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        return $this->messageService->sendMessage($chatId, '👆 Файл выше', $keyboard);
    }
    
    /**
     * Generate notes export for predefined period
     */
    private function generateNotesExport(string $chatId, int $userId, $conversation, ?string $projectCode, string $statusFilter, string $period): ServerResponse
    {
        // NOTE: Export permission already checked before calling this method
        // No need to check again here
        
        // Calculate date range based on period
        $endDate = date('Y-m-d 23:59:59');
        switch ($period) {
            case 'today':
                $startDate = date('Y-m-d 00:00:00');
                $periodText = 'Сегодня';
                break;
            case 'yesterday':
                $startDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
                $endDate = date('Y-m-d 23:59:59', strtotime('-1 day'));
                $periodText = 'Вчера';
                break;
            case 'week':
                $startDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
                $periodText = 'Последние 7 дней';
                break;
            case 'month':
                $startDate = date('Y-m-d 00:00:00', strtotime('-30 days'));
                $periodText = 'Последние 30 дней';
                break;
            case 'all':
                $startDate = null;
                $periodText = 'Весь период';
                break;
            default:
                $startDate = date('Y-m-d 00:00:00');
                $periodText = 'Сегодня';
        }
        
        // Get notes from database
        $notes = $this->getProjectNotes($projectCode, $startDate, $endDate, $statusFilter);
        
        if (empty($notes)) {
            if ($projectCode) {
                $text = "⚠️ <b>Нет данных</b>\n\nЗа период ({$periodText}) нет заметок по проекту {$projectCode}.";
            } else {
                $text = "⚠️ <b>Нет данных</b>\n\nЗа период ({$periodText}) нет заметок.";
            }
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
                ]
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Create CSV file
        $fileName = $this->createNotesExportCsv($projectCode, $notes, $period, $statusFilter);
        
        // Send status message
        $statusText = [
            'active' => '✅ Активные',
            'closed' => '❌ Закрытые',
            'all' => '📊 Все'
        ][$statusFilter];
        
        if ($projectCode) {
            $text = "✅ <b>Выгрузка заметок сформирована</b>\n\n📁 Проект: {$projectCode}\n📅 Период: {$periodText}\n📋 Фильтр: {$statusText}\n📌 Заметок: " . count($notes);
        } else {
            $text = "✅ <b>Выгрузка заметок сформирована</b>\n\n📋 Общий журнал\n📅 Период: {$periodText}\n📋 Фильтр: {$statusText}\n📌 Заметок: " . count($notes);
        }
        $this->messageService->sendMessage($chatId, $text);
        
        // Send CSV file with notes caption
        $this->sendCsvFile($chatId, $fileName, '📌 Журнал заметок');
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        return $this->messageService->sendMessage($chatId, '👆 Файл выше', $keyboard);
    }
    
    /**
     * Generate notes export for custom date range
     */
    private function generateNotesExportCustom(string $chatId, int $userId, $conversation, ?string $projectCode, string $statusFilter, string $startDate, string $endDate): ServerResponse
    {
        // NOTE: Export permission already checked before calling this method
        // No need to check again here
        
        // Convert dates to SQL format
        $startParts = explode('.', $startDate);
        $endParts = explode('.', $endDate);
        $startSql = "{$startParts[2]}-{$startParts[1]}-{$startParts[0]} 00:00:00";
        $endSql = "{$endParts[2]}-{$endParts[1]}-{$endParts[0]} 23:59:59";
        
        // Get notes from database
        $notes = $this->getProjectNotes($projectCode, $startSql, $endSql, $statusFilter);
        
        if (empty($notes)) {
            if ($projectCode) {
                $text = "⚠️ <b>Нет данных</b>\n\nЗа период с {$startDate} по {$endDate} нет заметок по проекту {$projectCode}.";
            } else {
                $text = "⚠️ <b>Нет данных</b>\n\nЗа период с {$startDate} по {$endDate} нет заметок.";
            }
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
                ]
            ];
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Create CSV file
        $period = "{$startDate}_{$endDate}";
        $fileName = $this->createNotesExportCsv($projectCode, $notes, $period, $statusFilter);
        
        // Send status message
        $statusText = [
            'active' => '✅ Активные',
            'closed' => '❌ Закрытые',
            'all' => '📊 Все'
        ][$statusFilter];
        
        if ($projectCode) {
            $text = "✅ <b>Выгрузка заметок сформирована</b>\n\n📁 Проект: {$projectCode}\n📅 Период: {$startDate} - {$endDate}\n📋 Фильтр: {$statusText}\n📌 Заметок: " . count($notes);
        } else {
            $text = "✅ <b>Выгрузка заметок сформирована</b>\n\n📋 Общий журнал\n📅 Период: {$startDate} - {$endDate}\n📋 Фильтр: {$statusText}\n📌 Заметок: " . count($notes);
        }
        $this->messageService->sendMessage($chatId, $text);
        
        // Send CSV file with notes caption
        $this->sendCsvFile($chatId, $fileName, '📌 Журнал заметок');
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_ОтчётПоПроекту']
            ]
        ];
        return $this->messageService->sendMessage($chatId, '👆 Файл выше', $keyboard);
    }
    
    /**
     * Get project notes from database
     */
    private function getProjectNotes(?string $projectCode, ?string $startDate, string $endDate, string $statusFilter = 'all'): array
    {
        try {
            // Use NotesService to get notes (it has PDO database access)
            require_once __DIR__ . '/../Services/NotesService.php';
            
            // Create PDO connection with hardcoded credentials (same as other parts of the system)
            // CRITICAL: Set charset to utf8mb4 AND execute SET NAMES to ensure proper encoding
            $dsn = "mysql:host=82.146.42.213;dbname=tg_bot;charset=utf8mb4";
            $pdo = new \PDO($dsn, 'WizardJIOCb', '7qQ#28051961');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // CRITICAL FIX: Force UTF-8 encoding for connection
            $pdo->exec("SET NAMES utf8mb4");
            $pdo->exec("SET CHARACTER SET utf8mb4");
            $pdo->exec("SET character_set_connection=utf8mb4");
            
            $notesService = new \Longman\TelegramBot\Commands\SystemCommands\Services\NotesService($pdo);
            
            // Get notes by project or all notes
            if ($projectCode) {
                // Get notes for specific project
                if ($statusFilter === 'all') {
                    $allNotes = $notesService->getNotesByProject($projectCode);
                } else {
                    $allNotes = $notesService->getNotesByProject($projectCode, $statusFilter);
                }
            } else {
                // Get all notes (general journal)
                if ($statusFilter === 'all') {
                    $sql = "SELECT * FROM project_notes ORDER BY created_at DESC";
                    $stmt = $pdo->query($sql);
                } else {
                    $sql = "SELECT * FROM project_notes WHERE status = :status ORDER BY created_at DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':status' => $statusFilter]);
                }
                $allNotes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            // Filter by date if specified
            if ($startDate) {
                $startTime = strtotime($startDate);
                $endTime = strtotime($endDate);
                
                $allNotes = array_filter($allNotes, function($note) use ($startTime, $endTime) {
                    $noteTime = strtotime($note['created_at']);
                    return $noteTime >= $startTime && $noteTime <= $endTime;
                });
            }
            
            return array_values($allNotes);
        } catch (\Exception $e) {
            error_log("ProjectHandler::getProjectNotes error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create CSV file for notes export
     */
    private function createNotesExportCsv(?string $projectCode, array $notes, string $period, string $statusFilter = 'all'): string
    {
        $uploadsDir = __DIR__ . '/../../Uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $periodSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $period);
        
        if ($projectCode) {
            $fileName = "notes_{$projectCode}_{$statusFilter}_{$periodSafe}_{$timestamp}.csv";
        } else {
            $fileName = "notes_all_{$statusFilter}_{$periodSafe}_{$timestamp}.csv";
        }
        
        $filePath = $uploadsDir . '/' . $fileName;
        
        $output = fopen($filePath, 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fprintf($output, "\xEF\xBB\xBF");
        
        // Write header
        fputcsv($output, ['№', 'Проект', 'Дата создания', 'Автор', 'Контекст', 'Этап', 'Текст заметки', 'Статус', 'Дата закрытия', 'Закрыл']);
        
        // Write data
        foreach ($notes as $note) {
            $status = $note['status'] === 'active' ? 'Активна' : 'Закрыта';
            $closedAt = $note['closed_at'] ? date('d.m.Y H:i', strtotime($note['closed_at'])) : '';
            $closedBy = $note['closed_by_fio'] ?? '';
            
            // After fix_notes_encoding.sql execution:
            // Table has correct UTF-8 encoding, so no conversion needed
            // Data comes from database in UTF-8 and goes to CSV as UTF-8
            $row = [
                $note['id'],
                $note['project_id'],
                date('d.m.Y H:i', strtotime($note['created_at'])),
                $note['user_fio'],
                $note['context_menu'],
                $note['stage'],
                $note['note_text'],
                $status,
                $closedAt,
                $closedBy
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        
        return $fileName;
    }
    
    /**
     * Show project cards menu
     */
    private function showProjectCardsMenu(string $chatId, int $userId): ServerResponse
    {
        $text = '🗂️ <b>Карточки проектов</b>

Просмотр информации о проектах:';

        $keyboard = [
            [
                ['text' => '📋 Список проектов', 'callback_data' => 'proj_card_list']
            ],
            [
                ['text' => '➕ Добавить проект', 'callback_data' => 'proj_card_add']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'pm_menu']
            ]
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Show list of projects for cards
     */
    private function showProjectCardsList(string $chatId, int $userId, int $page = 1): ServerResponse
    {
        try {
            // Get connection (mysqli)
            $conn = $this->databaseService->getConnection();
            
            // Query for active projects
            $query = "
                SELECT DISTINCT code as project_code, name as project_name 
                FROM project 
                WHERE code IS NOT NULL AND code != '' AND is_active = 1
                ORDER BY code
            ";
            
            $result = $conn->query($query);
            if (!$result) {
                throw new \Exception("Database query failed: " . $conn->error);
            }
            
            $projects = [];
            while ($row = $result->fetch_assoc()) {
                $projects[] = $row;
            }
            
            if (empty($projects)) {
                $text = '📋 <b>Список проектов</b>

Проекты пока не созданы.';
                
                $keyboard = [
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_КарточкаПроекта']
                    ]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            $text = '📋 <b>Список проектов</b>

Всего проектов: ' . count($projects) . '

Выберите проект для просмотра карточки:';
            
            $keyboard = [];
            foreach ($projects as $project) {
                // If name equals code, show only code
                $displayText = ($project['project_code'] === $project['project_name']) 
                    ? '🗂️ ' . $project['project_code']
                    : '🗂️ ' . $project['project_code'] . ' - ' . $project['project_name'];
                    
                $keyboard[] = [
                    ['text' => $displayText, 
                     'callback_data' => 'proj_card_view_' . $project['project_code']]
                ];
            }
            
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_КарточкаПроекта']
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showProjectCardsList error: " . $e->getMessage());
            error_log("ProjectHandler::showProjectCardsList trace: " . $e->getTraceAsString());
            return $this->messageService->sendMessage($chatId, 'Ошибка загрузки списка проектов: ' . $e->getMessage());
        }
    }
    
    /**
     * Create pagination navigation buttons for project card
     */
    private function createCardNavigationButtons(string $projectCode, string $currentPage): array
    {
        $pages = [
            'info' => '📋 Инфо',
            'smr' => '🔧 ШМР',
            'cmr' => '🏗️ СМР',
            'pnr' => '⚙️ ПНР'
        ];
        
        $buttons = [];
        foreach ($pages as $page => $label) {
            // Add indicator for current page
            if ($page === $currentPage) {
                $label = '📍 ' . $label;
            }
            $buttons[] = [
                'text' => $label,
                'callback_data' => 'proj_card_view_' . $projectCode . '_' . $page
            ];
        }
        
        return $buttons;
    }
    
    /**
     * View project card details with pagination
     * @param string $page Page to show: 'info', 'smr', 'cmr', 'pnr' (default: 'info')
     */
    private function viewProjectCard(string $chatId, int $userId, string $projectCode, string $page = 'info'): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get project from project table
            $stmt = $conn->prepare("SELECT code as project_code, name as project_name FROM project WHERE code = ? LIMIT 1");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $project = $result->fetch_assoc();
            
            if (!$project) {
                return $this->messageService->sendMessage($chatId, 'Проект не найден');
            }
            
            // Route to appropriate page
            switch ($page) {
                case 'info':
                    return $this->showProjectCardInfoPage($chatId, $userId, $projectCode, $project, $conn);
                case 'smr':
                    return $this->showProjectCardSMRPage($chatId, $userId, $projectCode, $project, $conn);
                case 'cmr':
                    return $this->showProjectCardCMRPage($chatId, $userId, $projectCode, $project, $conn);
                case 'pnr':
                    return $this->showProjectCardPNRPage($chatId, $userId, $projectCode, $project, $conn);
                default:
                    return $this->showProjectCardInfoPage($chatId, $userId, $projectCode, $project, $conn);
            }
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::viewProjectCard error: " . $e->getMessage());
            error_log("ProjectHandler::viewProjectCard trace: " . $e->getTraceAsString());
            return $this->messageService->sendMessage($chatId, 'Ошибка загрузки карточки: ' . $e->getMessage());
        }
    }
    
    /**
     * Show INFO page of project card
     */
    private function showProjectCardInfoPage(string $chatId, int $userId, string $projectCode, array $project, $conn): ServerResponse
    {
        // Get additional data from project_cards if exists
        $stmt = $conn->prepare("
            SELECT pc.*, c.name as customer_name, bu.name as business_unit_name, pm.name as project_manager_name
            FROM project_cards pc
            LEFT JOIN customers c ON pc.customer_id = c.id
            LEFT JOIN business_units bu ON pc.business_unit_id = bu.id
            LEFT JOIN project_managers pm ON pc.project_manager_id = pm.id
            WHERE pc.project_code = ?
        ");
        $stmt->bind_param('s', $projectCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $cardData = $result->fetch_assoc();
        
        // Get count of tabel entries for this project (excluding testers)
        $stmt = $conn->prepare("
            SELECT COUNT(*) as entry_count
            FROM ozna_tabel ot
            INNER JOIN ozna_data od ON ot.user_id = od.user_id
            WHERE ot.message LIKE CONCAT('%', ?, '%')
            AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
        ");
        $stmt->bind_param('s', $projectCode);
        $stmt->execute();
        $result = $stmt->get_result();
        $tabelData = $result->fetch_assoc();
        $entryCount = $tabelData['entry_count'] ?? 0;
        
        $text = '🗂️ <b>Карточка проекта</b>

';
        $text .= '<b>Шифр:</b> ' . $project['project_code'] . '
';
        $text .= '<b>Наименование:</b> ' . $project['project_name'] . '
';
        
        $projectManager = ($cardData && $cardData['project_manager_name']) ? $cardData['project_manager_name'] : '-';
        $text .= '<b>РП:</b> ' . $projectManager . '
';
        
        $customerName = ($cardData && $cardData['customer_name']) ? $cardData['customer_name'] : '-';
        $text .= '<b>Заказчик:</b> ' . $customerName . '
';
        
        $businessUnit = ($cardData && $cardData['business_unit_name']) ? $cardData['business_unit_name'] : '-';
        $text .= '<b>БН:</b> ' . $businessUnit . '
';
        
        $nomenclatureName = ($cardData && $cardData['name_1c']) ? $cardData['name_1c'] : '-';
        $text .= '<b>Наименование 1С:</b> ' . $nomenclatureName . '

';
        
        $text .= '📊 <b>Активность:</b>
';
        $text .= '  • Записей в табеле: ' . $entryCount . '

';
        
        // ============ EFFICIENCY SUMMARY SECTION ============
        $text .= "📈 <b>Эффективность этапов:</b>\n";
        
        // --- ШМР ---
        $smrEfficiencyText = $this->calculateSMREfficiencySummary($projectCode, $conn);
        $text .= $smrEfficiencyText;
        
        // --- СМР ---
        $cmrEfficiencyText = $this->calculateCMREfficiencySummary($projectCode, $conn);
        $text .= $cmrEfficiencyText;
        
        // --- ПНР ---
        $pnrEfficiencyText = $this->calculatePNREfficiencySummary($projectCode, $conn);
        $text .= $pnrEfficiencyText;
        
        // ============ COMBINED EFFICIENCY SECTION ============
        $combinedEfficiency = $this->calculateCombinedEfficiency($projectCode, $conn);
        if ($combinedEfficiency) {
            $text .= "\n📊 <b>Сводная эффективность:</b>\n";
            $text .= $combinedEfficiency;
        }
        $text .= "\n";
        
        // Navigation buttons
        $navButtons = $this->createCardNavigationButtons($projectCode, 'info');
        $keyboard = [
            $navButtons,
            [
                ['text' => '⚙️ Конструктор', 'callback_data' => 'proj_card_edit_' . $projectCode]
            ],
            [
                ['text' => '🗑️ Удалить проект', 'callback_data' => 'proj_card_delete_confirm_' . $projectCode]
            ],
            [
                ['text' => '⬅️ К списку', 'callback_data' => 'proj_card_list']
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Cancel project creation
     */
    private function cancelProjectCreation(string $chatId, int $userId, Conversation $conversation): ServerResponse
    {
        $conversation->stop();
        return $this->showProjectCardsMenu($chatId, $userId);
    }
    
    /**
     * Confirm project deletion
     */
    private function confirmDeleteProject(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get project info
            $stmt = $conn->prepare("
                SELECT code, name 
                FROM project 
                WHERE code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $project = $result->fetch_assoc();
            
            if (!$project) {
                return $this->messageService->sendMessage($chatId, '❌ Проект не найден');
            }
            
            // Count related data
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM ozna_tabel WHERE message LIKE CONCAT('%', ?, '%')");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $tabelCount = $result->fetch_assoc()['cnt'];
            
            $text = "⚠️ <b>Подтверждение удаления</b>\n\n";
            $text .= "🔹 Шифр: <b>{$project['code']}</b>\n";
            $text .= "🔹 Наименование: {$project['name']}\n\n";
            
            if ($tabelCount > 0) {
                $text .= "📄 В табеле: {$tabelCount} записей\n\n";
            }
            
            $text .= "⚠️ <b>Внимание!</b> Удаление проекта НЕ удалит записи из табеля.\n";
            $text .= "Проект будет помечен как неактивный.\n\n";
            $text .= "Вы уверены?";
            
            $keyboard = [
                [
                    ['text' => '✅ Да, удалить', 'callback_data' => 'proj_card_delete_yes_' . $projectCode],
                    ['text' => '❌ Отмена', 'callback_data' => 'proj_card_delete_no_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::confirmDeleteProject error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete project (mark as inactive)
     */
    private function deleteProject(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Mark project as inactive instead of deleting
            $stmt = $conn->prepare("
                UPDATE project 
                SET is_active = 0 
                WHERE code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $text = "✅ <b>Проект удалён</b>\n\n";
                $text .= "🔹 Шифр: {$projectCode}\n\n";
                $text .= "📄 Проект помечен как неактивный.\n";
                $text .= "Записи в табеле сохранены.";
                
                $keyboard = [
                    [
                        ['text' => '📋 Список проектов', 'callback_data' => 'proj_card_list']
                    ],
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_КарточкаПроекта']
                    ]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } else {
                return $this->messageService->sendMessage($chatId, '❌ Проект не найден');
            }
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::deleteProject error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка при удалении: ' . $e->getMessage());
        }
    }
    
    /**
     * Get standard SMR milestone names
     */
    private function getSMRMilestoneNames(): array
    {
        return [
            'Подготовительные работы',
            'Монтаж блоков',
            'Сборка здания',
            'Внутриблочный монтаж',
            'Подписание акта ШМР'
        ];
    }
    
    /**
     * Get sub-milestone structure for SMR milestones
     * Each top-level milestone contains sub-milestones with their activity mappings
     * Activities contain FULL text patterns to LIKE-match in ozna_tabel.message
     */
    private function getSMRMilestoneStructure(): array
    {
        return [
            'Подготовительные работы' => [
                'Входной контроль' => ['Работы по графику (Входной контроль)'],
                'Завоз оборудования на площадку' => ['Работы по графику (Завоз оборудования на площадку)'],
                'Проверка монтажной площадки' => ['Работы по графику (Проверка монтажной площадки)']
            ],
            'Монтаж блоков' => [
                'Расстановка блоков' => ['Работы по графику (Расстановка блоков)'],
                'Монтаж площадок обслуживания' => ['Работы по графику (Монтаж площадок обслуживания)']
            ],
            'Сборка здания' => [
                'Монтаж элементов укрытия' => ['Работы по графику (Монтаж элементов укрытия)'],
                'Сборка кровли' => ['Работы по графику (Сборка кровли)']
            ],
            'Внутриблочный монтаж' => [
                'Технологическое оборудование' => [
                    'Работы по графику (Монтаж трубопроводов)',
                    'Работы по графику (Гидро/пневмо испытания)',
                    'Работы по графику (Монтаж технологического оборудования)',
                    'Работы по графику (Монтаж ЗРА)',
                    'Работы по графику (Протяжка соединений)',
                    'Работы по графику (Монтаж теплоизоляции)',
                    'Прочее (Технологическое оборудование)'
                ],
                'Электрооборудование' => [
                    'Работы по графику (Монтаж оборудования - Электрооборудование)',
                    'Работы по графику (Монтаж кабеленесущих систем - Электрооборудование)',
                    'Работы по графику (Прокладка кабеля - Электрооборудование)',
                    'Работы по графику (Подключение оборудования - Электрооборудование)',
                    'Работы по графику (Маркировка оборудования/КЛ - Электрооборудование)',
                    'Прочее (Электрооборудование)'
                ],
                'Отопление и вентиляция' => [
                    'Работы по графику (Монтаж оборудования - Отопление и вентиляция)',
                    'Работы по графику (Монтаж воздуховодов/труб - Отопление и вентиляция)',
                    'Работы по графику (Монтаж кондиционеров - Отопление и вентиляция)',
                    'Работы по графику (Монтаж ИТП - Отопление и вентиляция)',
                    'Работы по графику (Проведение пневмо/гидро испытаний - Отопление и вентиляция)',
                    'Работы по графику (Монтаж КНС - Отопление и вентиляция)',
                    'Работы по графику (Прокладка кабеля - Отопление и вентиляция)',
                    'Работы по графику (Подключение кабеля - Отопление и вентиляция)',
                    'Работы по графику (Маркировка оборудования/КЛ - Отопление и вентиляция)',
                    'Прочее (Отопление и вентиляция)'
                ],
                'Пожарная сигнализация' => [
                    'Работы по графику (Монтаж оборудования - Пожарная сигнализация)',
                    'Работы по графику (Монтаж кабеленесущих систем - Пожарная сигнализация)',
                    'Работы по графику (Прокладка кабеля - Пожарная сигнализация)',
                    'Работы по графику (Подключение оборудования - Пожарная сигнализация)',
                    'Работы по графику (Маркировка оборудования/КЛ - Пожарная сигнализация)',
                    'Прочее (Пожарная сигнализация)'
                ],
                'Охранная сигнализация' => [
                    'Работы по графику (Монтаж оборудования - Охранная сигнализация)',
                    'Работы по графику (Монтаж кабеленесущих систем - Охранная сигнализация)',
                    'Работы по графику (Прокладка кабеля - Охранная сигнализация)',
                    'Работы по графику (Подключение оборудования - Охранная сигнализация)',
                    'Работы по графику (Маркировка оборудования/КЛ - Охранная сигнализация)',
                    'Прочее (Охранная сигнализация)'
                ],
                'Автоматизация' => [
                    'Работы по графику (Монтаж оборудования - Автоматизация)',
                    'Работы по графику (Монтаж кабеленесущих систем - Автоматизация)',
                    'Работы по графику (Прокладка кабеля - Автоматизация)',
                    'Работы по графику (Подключение оборудования - Автоматизация)',
                    'Работы по графику (Маркировка оборудования/КЛ - Автоматизация)',
                    'Прочее (Автоматизация)'
                ]
            ],
            'Подписание акта ШМР' => [
                'Подписание акта ШМР' => ['Подписание акта ШМР']
            ]
        ];
    }
    
    /**
     * Get standard CMR (СМР - строительно-монтажные работы) milestone names
     */
    private function getCMRMilestoneNames(): array
    {
        return [
            'Электромонтажные работы',
            'Слесарные работы',
            'Сварочные работы',
            'Работы по теплоизоляции',
            'Малярные работы'
        ];
    }
    
    /**
     * Get sub-milestone structure for CMR milestones
     * Each top-level milestone contains sub-milestones with their activity mappings
     * Activities contain FULL text patterns to LIKE-match in ozna_tabel.message
     */
    private function getCMRMilestoneStructure(): array
    {
        return [
            'Электромонтажные работы' => [
                'Досборка' => ['Электромонтажные работы (Досборка)', 'Электромонтажные работы добавлена'],
                'Реализация ТР' => ['Электромонтажные работы (Реализация ТР)'],
                'Устранение замечаний РК' => ['Электромонтажные работы (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Электромонтажные работы (Работы по гарантии)']
            ],
            'Слесарные работы' => [
                'Досборка' => ['Слесарные работы (Досборка)', 'Слесарные работы добавлена'],
                'Реализация ТР' => ['Слесарные работы (Реализация ТР)'],
                'Устранение замечаний РК' => ['Слесарные работы (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Слесарные работы (Работы по гарантии)']
            ],
            'Сварочные работы' => [
                'Досборка' => ['Сварочные работы (Досборка)', 'Сварочные работы добавлена'],
                'Реализация ТР' => ['Сварочные работы (Реализация ТР)'],
                'Устранение замечаний РК' => ['Сварочные работы (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Сварочные работы (Работы по гарантии)']
            ],
            'Работы по теплоизоляции' => [
                'Досборка' => ['Работы по теплоизоляции (Досборка)', 'Работы по теплоизоляции добавлена'],
                'Реализация ТР' => ['Работы по теплоизоляции (Реализация ТР)'],
                'Устранение замечаний РК' => ['Работы по теплоизоляции (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Работы по теплоизоляции (Работы по гарантии)']
            ],
            'Малярные работы' => [
                'Досборка' => ['Малярные работы (Досборка)', 'Малярные работы добавлена'],
                'Реализация ТР' => ['Малярные работы (Реализация ТР)'],
                'Устранение замечаний РК' => ['Малярные работы (Устранение замечаний РК)'],
                'Работы по гарантии' => ['Малярные работы (Работы по гарантии)']
            ]
        ];
    }
    
    /**
     * Get sub-milestone names for a CMR milestone by index
     */
    private function getCMRSubMilestoneNames(int $milestoneIndex): array
    {
        $milestoneNames = $this->getCMRMilestoneNames();
        $structure = $this->getCMRMilestoneStructure();
        
        if (!isset($milestoneNames[$milestoneIndex])) {
            return [];
        }
        
        $milestoneName = $milestoneNames[$milestoneIndex];
        if (!isset($structure[$milestoneName])) {
            return [];
        }
        return array_keys($structure[$milestoneName]);
    }
    
    /**
     * Get mapping of CMR work types to related SMR milestones (Level 2 under Внутриблочный монтаж)
     * Used to calculate CMR forecast based on SMR milestone progress
     * Note: Малярные работы are excluded - to be handled separately
     */
    private function getCMRToSMRMilestoneMapping(): array
    {
        return [
            'Электромонтажные работы' => ['Электрооборудование'],
            'Слесарные работы' => ['Технологическое оборудование', 'Отопление и вентиляция'],
            'Сварочные работы' => ['Технологическое оборудование'],
            'Работы по теплоизоляции' => ['Технологическое оборудование', 'Отопление и вентиляция'],
            // 'Малярные работы' => [] - excluded, will be handled separately
        ];
    }
    
    /**
     * Calculate weighted progress for CMR Досборка based on related SMR milestones
     * Progress = Σ(milestone_progress × milestone_plan) / Σ(milestone_plan)
     * 
     * @param string $projectCode Project code
     * @param string $cmrWorkType CMR work type (e.g., 'Электромонтажные работы')
     * @param $conn Database connection
     * @return array|null ['progress' => float 0-100, 'totalPlan' => int, 'details' => array] or null if no data
     */
    private function calculateCMRForecastFromSMR(string $projectCode, string $cmrWorkType, $conn): ?array
    {
        $mapping = $this->getCMRToSMRMilestoneMapping();
        
        // Skip Малярные работы - not in mapping
        if (!isset($mapping[$cmrWorkType])) {
            return null;
        }
        
        $relatedSMRMilestones = $mapping[$cmrWorkType];
        if (empty($relatedSMRMilestones)) {
            return null;
        }
        
        // Get SMR milestone plans from project_milestones
        $placeholders = implode(',', array_fill(0, count($relatedSMRMilestones), '?'));
        $sql = "
            SELECT milestone_name, planned_hours, is_not_applicable, unit
            FROM project_milestones
            WHERE project_code = ? 
            AND work_type = 'ШМР'
            AND milestone_name IN ({$placeholders})
        ";
        
        $stmt = $conn->prepare($sql);
        $types = 's' . str_repeat('s', count($relatedSMRMilestones));
        $params = array_merge([$projectCode], $relatedSMRMilestones);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $milestonePlans = [];
        while ($row = $result->fetch_assoc()) {
            if (!$row['is_not_applicable'] && $row['planned_hours'] > 0) {
                $planDays = $row['unit'] === 'hours' ? ceil($row['planned_hours'] / 8) : $row['planned_hours'];
                $milestonePlans[$row['milestone_name']] = $planDays;
            }
        }
        
        // If no plan data for related milestones, return null
        if (empty($milestonePlans)) {
            return null;
        }
        
        // Get SMR milestone progress estimates
        $milestoneProgress = [];
        foreach (array_keys($milestonePlans) as $milestoneName) {
            // Check if milestone is confirmed (100%)
            $confirmSql = "
                SELECT COUNT(*) as confirmed_count
                FROM ozna_tabel ot
                LEFT JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND ot.message LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message LIKE CONCAT('%', ?, '%')
                AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                     OR ot.message LIKE '%[Конструктор]%')
            ";
            $confirmStmt = $conn->prepare($confirmSql);
            $confirmStmt->bind_param('ss', $projectCode, $milestoneName);
            $confirmStmt->execute();
            $confirmResult = $confirmStmt->get_result();
            $confirmRow = $confirmResult->fetch_assoc();
            $isConfirmed = ($confirmRow['confirmed_count'] ?? 0) > 0;
            
            if ($isConfirmed) {
                $milestoneProgress[$milestoneName] = 100;
            } else {
                // Check for survey estimate
                $estimateSql = "
                    SELECT estimated_percent
                    FROM milestone_progress_estimates
                    WHERE project_code = ?
                    AND milestone_name = ?
                    AND work_type = 'ШМР'
                    ORDER BY created_at DESC
                    LIMIT 1
                ";
                $estimateStmt = $conn->prepare($estimateSql);
                $estimateStmt->bind_param('ss', $projectCode, $milestoneName);
                $estimateStmt->execute();
                $estimateResult = $estimateStmt->get_result();
                $estimateRow = $estimateResult->fetch_assoc();
                
                if ($estimateRow) {
                    $milestoneProgress[$milestoneName] = $estimateRow['estimated_percent'];
                } else {
                    // Fallback: use min(fact/plan, 1) * 100
                    // Get fact for this milestone
                    $milestoneProgress[$milestoneName] = 0; // Default to 0 if no data
                }
            }
        }
        
        // Calculate weighted progress
        $totalWeightedProgress = 0;
        $totalPlan = 0;
        $details = [];
        
        foreach ($milestonePlans as $milestoneName => $plan) {
            $progress = $milestoneProgress[$milestoneName] ?? 0;
            $totalWeightedProgress += $progress * $plan;
            $totalPlan += $plan;
            $details[$milestoneName] = [
                'plan' => $plan,
                'progress' => $progress
            ];
        }
        
        $weightedProgress = $totalPlan > 0 ? round($totalWeightedProgress / $totalPlan, 1) : 0;
        
        return [
            'progress' => $weightedProgress,
            'totalPlan' => $totalPlan,
            'details' => $details
        ];
    }
    
    /**
     * Calculate overall CMR Досборка forecast based on all work types
     * Returns weighted average progress across all CMR work types
     */
    private function calculateOverallCMRDosborkForecast(string $projectCode, $conn): ?array
    {
        $cmrWorkTypes = [
            'Электромонтажные работы',
            'Слесарные работы',
            'Сварочные работы',
            'Работы по теплоизоляции'
            // 'Малярные работы' - excluded
        ];
        
        $totalProgress = 0;
        $totalWeight = 0;
        $workTypeForecasts = [];
        
        foreach ($cmrWorkTypes as $workType) {
            $forecast = $this->calculateCMRForecastFromSMR($projectCode, $workType, $conn);
            if ($forecast && $forecast['totalPlan'] > 0) {
                $workTypeForecasts[$workType] = $forecast;
                $totalProgress += $forecast['progress'] * $forecast['totalPlan'];
                $totalWeight += $forecast['totalPlan'];
            }
        }
        
        if ($totalWeight == 0) {
            return null;
        }
        
        return [
            'overallProgress' => round($totalProgress / $totalWeight, 1),
            'totalPlan' => $totalWeight,
            'workTypes' => $workTypeForecasts
        ];
    }

    /**
     * Show ШМР page of project card
     */
    private function showProjectCardSMRPage(string $chatId, int $userId, string $projectCode, array $project, $conn): ServerResponse
    {
        try {
            // Get SMR milestones with plan from project_milestones
            $milestoneNames = $this->getSMRMilestoneNames();
            
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $smrMilestonesPlan = [];
            while ($row = $result->fetch_assoc()) {
                $smrMilestonesPlan[$row['milestone_name']] = [
                    'planned_hours' => $row['planned_hours'],
                    'is_not_applicable' => $row['is_not_applicable'],
                    'unit' => $row['unit']
                ];
            }
            
            // Get SMR milestone fact days from tabel (excluding testers and confirmations)
            // Using two-level structure: top-level milestones contain sub-milestones
            $milestoneStructure = $this->getSMRMilestoneStructure();
            $smrMilestonesFact = []; // Store fact for each top-level and sub-milestone
            $smrMilestonesConfirmed = [];
            $smrSubConfirmed = []; // Per sub-milestone confirmation status
            
            foreach ($milestoneNames as $topMilestoneName) {
                $subMilestones = $milestoneStructure[$topMilestoneName] ?? [];
                $topMilestoneFact = 0;
                $smrMilestonesFact[$topMilestoneName] = ['subs' => [], 'total' => 0];
                $allSubsConfirmed = true;
                $hasActiveSubs = false;
                
                foreach ($subMilestones as $subMilestoneName => $activities) {
                    // All activities now contain full text patterns to LIKE-match directly
                    $activityConditions = [];
                    foreach ($activities as $activity) {
                        $activityConditions[] = "ot.message LIKE '%" . $conn->real_escape_string($activity) . "%'";
                    }
                    $activityFilter = '(' . implode(' OR ', $activityConditions) . ')';
                    
                    $sql = "
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND {$activityFilter}
                        AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('s', $projectCode);
                    
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $subFact = $row['days_count'] ?? 0;
                    
                    $smrMilestonesFact[$topMilestoneName]['subs'][$subMilestoneName] = $subFact;
                    $topMilestoneFact += $subFact;
                    
                    // Count НеучВОР (works not in scope) per sub-milestone
                    // Derive NVOR patterns: replace "Работы по графику" with "Работы неучтенные в ВОР"
                    $nvorConditions = [];
                    foreach ($activities as $activity) {
                        if (strpos($activity, 'Работы по графику (') === 0) {
                            $nvorPattern = str_replace('Работы по графику (', 'Работы неучтенные в ВОР (', $activity);
                            // Handle spelling variations: "/" vs "-" (e.g. Гидро/пневмо vs Гидро-пневмо)
                            $nvorPattern = str_replace('/', '%', $nvorPattern);
                            $nvorConditions[] = "ot.message LIKE '%" . $conn->real_escape_string($nvorPattern) . "%'";
                        }
                    }
                    
                    $subNvor = 0;
                    if (!empty($nvorConditions)) {
                        $nvorFilter = '(' . implode(' OR ', $nvorConditions) . ')';
                        $nvorSql = "
                            SELECT COUNT(DISTINCT ot.id) as nvor_count
                            FROM ozna_tabel ot
                            INNER JOIN ozna_data od ON ot.user_id = od.user_id
                            WHERE ot.message LIKE CONCAT('%', ?, '%')
                            AND {$nvorFilter}
                            AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                            AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                        ";
                        $nvorStmt = $conn->prepare($nvorSql);
                        $nvorStmt->bind_param('s', $projectCode);
                        $nvorStmt->execute();
                        $nvorResult = $nvorStmt->get_result();
                        $nvorRow = $nvorResult->fetch_assoc();
                        $subNvor = $nvorRow['nvor_count'] ?? 0;
                    }
                    $smrMilestonesFact[$topMilestoneName]['subs_nvor'][$subMilestoneName] = $subNvor;
                    
                    // Check if this sub-milestone is confirmed
                    $confirmSql = "
                        SELECT COUNT(*) as confirmed_count
                        FROM ozna_tabel ot
                        LEFT JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND ot.message LIKE '%Этап шеф-монтажа завершен%'
                        AND ot.message LIKE CONCAT('%', ?, '%')
                        AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                             OR ot.message LIKE '%[Конструктор]%')
                    ";
                    $confirmStmt = $conn->prepare($confirmSql);
                    $confirmStmt->bind_param('ss', $projectCode, $subMilestoneName);
                    $confirmStmt->execute();
                    $confirmResult = $confirmStmt->get_result();
                    $confirmRow = $confirmResult->fetch_assoc();
                    $isSubConfirmed = ($confirmRow['confirmed_count'] ?? 0) > 0;
                    $smrSubConfirmed[$subMilestoneName] = $isSubConfirmed;
                    
                    $hasActiveSubs = true;
                    if (!$isSubConfirmed) {
                        $allSubsConfirmed = false;
                    }
                }
                
                $smrMilestonesFact[$topMilestoneName]['total'] = $topMilestoneFact;
                
                // Top milestone is confirmed if all active sub-milestones are confirmed
                $smrMilestonesConfirmed[$topMilestoneName] = $hasActiveSubs && $allSubsConfirmed;
            }
            
            // Get SMR additional work counts from tabel
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Исполнение обязанностей мастера%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as master_duties_smr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as rk_comments_smr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as warranty_smr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as downtime_smr,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Дорога%' 
                         OR ot.message LIKE '%Инструктаж%' 
                         OR ot.message LIKE '%Подготовка к служебной поездке%'
                         OR ot.message LIKE '%Сдача авансового отчета%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as mobilization_smr,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Разработка графика работ%'
                         OR ot.message LIKE '%Разработка программы работ%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as project_activity_smr
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('sssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $smrAdditional = $result->fetch_assoc();
            
            // Get TOTAL SMR fact count (excluding confirmations)
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT ot.id) as smr_total_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                AND ot.message NOT LIKE '%Этап наладки завершен%'
                AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $smrTotalRow = $result->fetch_assoc();
            $smrTotalFactFromTabel = $smrTotalRow['smr_total_count'] ?? 0;
            
            // Calculate total plan - only from valid sub-milestone names in structure
            // Get list of valid sub-milestone names
            $validSubMilestoneNames = [];
            foreach ($milestoneStructure as $topName => $subs) {
                foreach ($subs as $subName => $activities) {
                    $validSubMilestoneNames[] = $subName;
                }
            }
            
            $smrTotalPlan = 0;
            foreach ($smrMilestonesPlan as $name => $data) {
                // Only sum plans for valid sub-milestones (ignore orphaned records)
                if (!in_array($name, $validSubMilestoneNames)) {
                    continue;
                }
                if (!$data['is_not_applicable'] && $data['planned_hours'] !== null) {
                    if ($data['unit'] === 'hours') {
                        $smrTotalPlan += ceil($data['planned_hours'] / 8);
                    } else {
                        $smrTotalPlan += $data['planned_hours'];
                    }
                }
            }
            
            // Get additional work plan from project_cards
            $stmt = $conn->prepare("
                SELECT smr_mobilization, smr_project_activity, smr_master_duties
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            // Вспомогательные работы НЕ добавляем к плану в шапке - они учитываются в общей эффективности
            $smrMobilizationPlan = ($cardData && $cardData['smr_mobilization']) ? $cardData['smr_mobilization'] : 0;
            $smrProjectActivityPlan = ($cardData && $cardData['smr_project_activity']) ? $cardData['smr_project_activity'] : 0;
            $smrMasterDutiesPlan = ($cardData && $cardData['smr_master_duties']) ? $cardData['smr_master_duties'] : 0;
            $smrAuxPlan = $smrMobilizationPlan + $smrProjectActivityPlan + $smrMasterDutiesPlan;
            // $smrTotalPlan показывает только план по вехам, без вспомогательных работ
            
            // ============ CALCULATE EFFICIENCY METRICS ============
            // Get milestone progress estimates from survey data (ШМР)
            $milestoneEstimates = [];
            $stmt = $conn->prepare("
                SELECT milestone_name, estimated_percent, efficiency_at_estimate, 
                       estimator_fio, estimated_at
                FROM milestone_progress_estimates
                WHERE project_code = ? AND work_type = 'ШМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $milestoneEstimates[$row['milestone_name']] = $row;
            }
            
            // Get additional work facts early for calculations
            $mobFact = $smrAdditional['mobilization_smr'] ?? 0;
            $projActFact = $smrAdditional['project_activity_smr'] ?? 0;
            $masterDutiesFact = $smrAdditional['master_duties_smr'] ?? 0;
            $rkCommentsFact = $smrAdditional['rk_comments_smr'] ?? 0;
            $warrantyFact = $smrAdditional['warranty_smr'] ?? 0;
            $downtimeFact = $smrAdditional['downtime_smr'] ?? 0;
            
            // Calculate milestone totals and NVOR
            $milestonesTotalFact = 0;
            $totalNvorFact = 0;
            foreach ($smrMilestonesFact as $milestoneData) {
                $milestonesTotalFact += $milestoneData['total'] ?? 0;
                if (isset($milestoneData['subs_nvor'])) {
                    foreach ($milestoneData['subs_nvor'] as $nvorValue) {
                        $totalNvorFact += $nvorValue;
                    }
                }
            }
            
            // Calculate effective work per sub-milestone
            $milestonesEffectiveWork = 0;
            $forecastRemainingActualDays = 0;
            $milestonesPlanTotal = 0;
            
            foreach ($milestoneNames as $topMilestoneName) {
                $subMilestones = $milestoneStructure[$topMilestoneName] ?? [];
                $isTopConfirmed = $smrMilestonesConfirmed[$topMilestoneName] ?? false;
                
                foreach ($subMilestones as $subMilestoneName => $activities) {
                    $planData = $smrMilestonesPlan[$subMilestoneName] ?? null;
                    if ($planData && $planData['is_not_applicable']) continue;
                    
                    $subPlan = 0;
                    if ($planData && $planData['planned_hours'] !== null && $planData['planned_hours'] > 0) {
                        $subPlan = $planData['planned_hours'];
                        if ($planData['unit'] === 'hours') {
                            $subPlan = ceil($subPlan / 8);
                        }
                        $milestonesPlanTotal += $subPlan;
                    }
                    
                    $subFact = $smrMilestonesFact[$topMilestoneName]['subs'][$subMilestoneName] ?? 0;
                    $estimate = $milestoneEstimates[$subMilestoneName] ?? null;
                    $isSubConfirmedForCalc = $smrSubConfirmed[$subMilestoneName] ?? false;
                    
                    if ($subPlan > 0) {
                        if ($isTopConfirmed || $isSubConfirmedForCalc) {
                            // Sub-milestone confirmed = 100% completion
                            $milestonesEffectiveWork += $subPlan;
                        } elseif ($subFact > 0 && $estimate) {
                            // Sub-milestone has work done - use survey estimate
                            $effectiveDone = ($estimate['estimated_percent'] * $subPlan) / 100;
                            $milestonesEffectiveWork += $effectiveDone;
                            $remainingPlan = $subPlan - $effectiveDone;
                            if ($effectiveDone > 0 && $remainingPlan > 0) {
                                $milestoneEfficiency = $effectiveDone / $subFact;
                                $forecastRemainingActualDays += $remainingPlan / $milestoneEfficiency;
                            } elseif ($remainingPlan > 0) {
                                $forecastRemainingActualDays += $remainingPlan;
                            }
                        } elseif ($subFact > 0) {
                            // Has fact but no estimate - use min(fact, plan) as conservative estimate
                            $effectiveDone = min($subFact, $subPlan);
                            $milestonesEffectiveWork += $effectiveDone;
                            $remainingPlan = $subPlan - $effectiveDone;
                            if ($effectiveDone > 0 && $remainingPlan > 0) {
                                $milestoneEfficiency = $effectiveDone / $subFact;
                                $forecastRemainingActualDays += $remainingPlan / $milestoneEfficiency;
                            } elseif ($remainingPlan > 0) {
                                $forecastRemainingActualDays += $remainingPlan;
                            }
                        } else {
                            // Not started - assume 100% efficiency for forecast
                            $forecastRemainingActualDays += $subPlan;
                        }
                    }
                }
            }
            
            // Stage = milestones only (mob/projAct/masterDuties treated as auxiliary)
            $stagePlanTime = $milestonesPlanTotal;
            $stageFactTime = $milestonesTotalFact;
            $stageEffectiveWork = $milestonesEffectiveWork;
            
            // Overall plan includes auxiliary activities
            $overallPlanTime = $milestonesPlanTotal + $smrMobilizationPlan + $smrProjectActivityPlan + $smrMasterDutiesPlan;
            
            // Additional work (NVOR + RK + warranty + downtime + unaccounted) — reduces overall only
            $unaccountedWorksFact = $smrTotalFactFromTabel - ($milestonesTotalFact + $totalNvorFact + $mobFact + $projActFact + $masterDutiesFact + $rkCommentsFact + $warrantyFact + $downtimeFact);
            $unaccountedWorksFact = max(0, $unaccountedWorksFact);
            $auxiliaryFact = $mobFact + $projActFact + $masterDutiesFact;
            $additionalWorkFact = $totalNvorFact + $rkCommentsFact + $warrantyFact + $downtimeFact + $unaccountedWorksFact;
            $totalFactTime = $stageFactTime + $auxiliaryFact + $additionalWorkFact;
            
            $remainingPlanWork = max(0, $stagePlanTime - $stageEffectiveWork);
            
            // Build message
            $text = "🔧 <b>ШМР (Шеф-монтажные работы)</b>\n\n";
            $text .= "<b>Шифр:</b> " . $project['project_code'] . "\n";
            $text .= "<b>Наименование:</b> " . $project['project_name'] . "\n\n";
            
            // Overall metrics
            $text .= "📊 <b>План:</b> " . $smrTotalPlan . " дн. | <b>Факт:</b> " . $smrTotalFactFromTabel . " дн.\n";
            if ($stagePlanTime > 0 && $stageEffectiveWork >= $stagePlanTime) {
                $text .= "✅ Этап завершен\n";
            }
            $text .= "\n";
            
            // ============ CURRENT EFFICIENCY ============
            if ($stagePlanTime > 0) {
                $text .= "<b>📈 Текущая эффективность:</b>\n";
                
                // Current overall
                if ($totalFactTime > 0) {
                    $currentOverall = round(($overallPlanTime / $totalFactTime) * 100, 1);
                    $overallDelta = $totalFactTime - $overallPlanTime;
                    $overallDeltaSign = $overallDelta > 0 ? '⬆️' : '⬇️';
                    $overallDeltaAbs = abs($overallDelta);
                    $icon = $currentOverall >= 100 ? '🟢' : ($currentOverall >= 80 ? '🟡' : '🔴');
                    $text .= "{$icon} Общая: {$currentOverall}% ({$overallDeltaSign} {$overallDeltaAbs} дн.)\n";
                }
                
                // Current stage
                if ($stageFactTime > 0) {
                    $currentStage = round(($stagePlanTime / $stageFactTime) * 100, 1);
                    $stageDelta = $stageFactTime - $stagePlanTime;
                    $stageDeltaSign = $stageDelta > 0 ? '⬆️' : '⬇️';
                    $stageDeltaAbs = abs($stageDelta);
                    $icon = $currentStage >= 100 ? '🟢' : ($currentStage >= 80 ? '🟡' : '🔴');
                    $text .= "{$icon} Этап: {$currentStage}% ({$stageDeltaSign} {$stageDeltaAbs} дн.)\n";
                }
            } elseif ($smrTotalPlan == 0) {
                $text .= "⚠️ План не установлен\n";
            }
            
            // ============ FORECAST EFFICIENCY ============
            if ($stagePlanTime > 0 && $remainingPlanWork > 0) {
                $text .= "\n<b>🔮 Прогноз эффективности:</b>\n";
                
                // Forecast overall
                if ($totalFactTime > 0) {
                    $factForecastOverall = $totalFactTime + $forecastRemainingActualDays;
                    $forecastOverall = round(($overallPlanTime / $factForecastOverall) * 100, 1);
                    $forecastOverallDelta = round($factForecastOverall - $overallPlanTime, 1);
                    $forecastOverallDeltaStr = $forecastOverallDelta > 0 ? "⬆️ {$forecastOverallDelta}" : ($forecastOverallDelta < 0 ? "⬇️ " . abs($forecastOverallDelta) : "0");
                    $icon = $forecastOverall >= 100 ? '🟢' : ($forecastOverall >= 80 ? '🟡' : '🔴');
                    $text .= "{$icon} Общая: {$forecastOverall}% ({$forecastOverallDeltaStr} дн.)\n";
                }
                
                // Forecast stage
                if ($stageFactTime > 0) {
                    $factForecastStage = $stageFactTime + $forecastRemainingActualDays;
                    $forecastStage = round(($stagePlanTime / $factForecastStage) * 100, 1);
                    $forecastStageDelta = round($factForecastStage - $stagePlanTime, 1);
                    $forecastStageDeltaStr = $forecastStageDelta > 0 ? "⬆️ {$forecastStageDelta}" : ($forecastStageDelta < 0 ? "⬇️ " . abs($forecastStageDelta) : "0");
                    $icon = $forecastStage >= 100 ? '🟢' : ($forecastStage >= 80 ? '🟡' : '🔴');
                    $text .= "{$icon} Этап: {$forecastStage}% ({$forecastStageDeltaStr} дн.)\n";
                }
                
                // Completion date forecast
                $personnelCount = 0;
                $stmt = $conn->prepare("
                    SELECT COUNT(DISTINCT JSON_EXTRACT(od.data, '$.fio')) as unique_specialists
                    FROM ozna_tabel ot
                    INNER JOIN ozna_data od ON ot.user_id = od.user_id
                    WHERE ot.message LIKE CONCAT('%', ?, '%')
                    AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                    AND ot.dt >= DATE_SUB(NOW(), INTERVAL 3 DAY)
                ");
                $stmt->bind_param('s', $projectCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $personnelRow = $result->fetch_assoc();
                $personnelCount = $personnelRow['unique_specialists'] ?? 0;
                
                if ($personnelCount > 0) {
                    $daysRemaining = ceil($forecastRemainingActualDays / $personnelCount);
                    $completionDate = new \DateTime();
                    $completionDate->modify("+{$daysRemaining} days");
                    $completionDateStr = $completionDate->format('d.m.Y');
                    $text .= "📅 Завершение: ~{$completionDateStr} (при {$personnelCount} чел.)\n";
                    $text .= "   ⏱️ Осталось: {$remainingPlanWork} дн.\n";
                } else {
                    $text .= "📅 Нет данных о персонале за последние 3 дня\n";
                    $text .= "   ⏱️ Осталось: {$remainingPlanWork} дн.\n";
                }
            }
            $text .= "\n";
            
            // Milestones section - two-level display: top milestone with sub-milestones
            // Get plan data from project_milestones (stored per sub-milestone)
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $subMilestonePlanData = [];
            while ($row = $result->fetch_assoc()) {
                $subMilestonePlanData[$row['milestone_name']] = [
                    'planned_hours' => $row['planned_hours'],
                    'is_not_applicable' => $row['is_not_applicable'],
                    'unit' => $row['unit']
                ];
            }
            
            // Build display structure
            $hasAnyMilestones = false;
            $milestoneDisplayData = [];
            
            foreach ($milestoneNames as $topMilestoneName) {
                $subMilestones = $milestoneStructure[$topMilestoneName] ?? [];
                $topMilestonePlan = 0;
                $topMilestoneFact = $smrMilestonesFact[$topMilestoneName]['total'] ?? 0;
                $subMilestoneData = [];
                
                foreach ($subMilestones as $subMilestoneName => $activities) {
                    $planData = $subMilestonePlanData[$subMilestoneName] ?? null;
                    
                    // Skip if not applicable
                    if ($planData && $planData['is_not_applicable']) {
                        continue;
                    }
                    
                    // Get plan
                    $subPlan = 0;
                    if ($planData && $planData['planned_hours'] !== null) {
                        $subPlan = $planData['planned_hours'];
                        if ($planData['unit'] === 'hours') {
                            $subPlan = ceil($subPlan / 8);
                        }
                    }
                    
                    // Get fact
                    $subFact = $smrMilestonesFact[$topMilestoneName]['subs'][$subMilestoneName] ?? 0;
                    
                    // Get НеучВОР fact
                    $subNvor = $smrMilestonesFact[$topMilestoneName]['subs_nvor'][$subMilestoneName] ?? 0;
                    
                    // Skip if both plan and fact and nvor are 0
                    if ($subPlan == 0 && $subFact == 0 && $subNvor == 0) {
                        continue;
                    }
                    
                    $subMilestoneData[] = [
                        'name' => $subMilestoneName,
                        'plan' => $subPlan,
                        'fact' => $subFact,
                        'nvor' => $subNvor
                    ];
                    
                    $topMilestonePlan += $subPlan;
                }
                
                // Skip top milestone if no sub-milestones to display
                if (empty($subMilestoneData)) {
                    continue;
                }
                
                $hasAnyMilestones = true;
                $isConfirmed = $smrMilestonesConfirmed[$topMilestoneName] ?? false;
                
                $milestoneDisplayData[] = [
                    'name' => $topMilestoneName,
                    'plan' => $topMilestonePlan,
                    'fact' => $topMilestoneFact,
                    'confirmed' => $isConfirmed,
                    'subs' => $subMilestoneData
                ];
            }
            
            // Display milestones
            if ($hasAnyMilestones) {
                $text .= "<b>Вехи:</b>\n";
                $topCounter = 0;
                
                foreach ($milestoneDisplayData as $topMilestone) {
                    $topCounter++;
                    $confirmMark = $topMilestone['confirmed'] ? ' ✅' : '';
                    
                    // Top-level milestone header
                    $text .= "  {$topCounter}. <b>{$topMilestone['name']}</b>{$confirmMark}\n";
                    $text .= "     📊 План: {$topMilestone['plan']} дн. | Факт: {$topMilestone['fact']} дн.\n";
                    
                    // Efficiency for top-level milestone
                    if ($topMilestone['plan'] > 0 && $topMilestone['fact'] > 0) {
                        $efficiency = round(($topMilestone['plan'] / $topMilestone['fact']) * 100, 1);
                        $delta = $topMilestone['fact'] - $topMilestone['plan'];
                        $deltaSign = $delta > 0 ? '⬆️' : '⬇️';
                        $deltaAbs = abs($delta);
                        
                        if ($efficiency >= 90) {
                            $text .= "     🟢 Эффективность: {$efficiency}% ({$deltaSign} {$deltaAbs} дн.)\n";
                        } elseif ($efficiency >= 70) {
                            $text .= "     🟡 Эффективность: {$efficiency}% ({$deltaSign} {$deltaAbs} дн.)\n";
                        } else {
                            $text .= "     🔴 Эффективность: {$efficiency}% ({$deltaSign} {$deltaAbs} дн.)\n";
                        }
                    } elseif ($topMilestone['fact'] > 0) {
                        $text .= "     ⚠️ План не установлен (⬆️ {$topMilestone['fact']} дн.)\n";
                    }
                    
                    // Sub-milestones with efficiency analysis
                    foreach ($topMilestone['subs'] as $subMilestone) {
                        // Get survey estimate for this sub-milestone
                        $subEstimate = $milestoneEstimates[$subMilestone['name']] ?? null;
                        $isSubConfirmed = $smrSubConfirmed[$subMilestone['name']] ?? false;
                        $progressText = '';
                        $completionPercent = null;
                        
                        if ($topMilestone['confirmed'] || $isSubConfirmed) {
                            $completionPercent = 100;
                            $progressText = " ✅ [100%]";
                        } elseif ($subEstimate) {
                            $completionPercent = $subEstimate['estimated_percent'];
                            if ($completionPercent >= 100) {
                                $progressText = " ✅ [100%]";
                            } else {
                                $progressText = " [{$completionPercent}%]";
                            }
                        }
                        
                        $text .= "       • <b>{$subMilestone['name']}</b>{$progressText}\n";
                        $text .= "         📊 План: {$subMilestone['plan']} дн. | Факт: {$subMilestone['fact']} дн.\n";
                        
                        // НеучВОР for sub-milestone
                        $subNvorValue = $subMilestone['nvor'] ?? 0;
                        if ($subNvorValue > 0) {
                            $text .= "         📄 Неучтённые в ВОР: {$subNvorValue} дн.\n";
                        }
                        
                        // Efficiency analysis for sub-milestone
                        if ($subMilestone['plan'] > 0 && $subMilestone['fact'] > 0) {
                            if ($completionPercent !== null) {
                                // Real efficiency using survey estimate
                                $subEfficiency = round(($completionPercent * $subMilestone['plan']) / ($subMilestone['fact'] * 100) * 100, 1);
                            } else {
                                // Fallback: plan/fact ratio
                                $subEfficiency = round(($subMilestone['plan'] / $subMilestone['fact']) * 100, 1);
                            }
                            $subDelta = $subMilestone['fact'] - $subMilestone['plan'];
                            $subDeltaSign = $subDelta > 0 ? '⬆️' : '⬇️';
                            $subDeltaAbs = abs($subDelta);
                            
                            if ($subEfficiency >= 90) {
                                $text .= "         🟢 Эффективность: {$subEfficiency}% ({$subDeltaSign} {$subDeltaAbs} дн.)\n";
                            } elseif ($subEfficiency >= 70) {
                                $text .= "         🟡 Эффективность: {$subEfficiency}% ({$subDeltaSign} {$subDeltaAbs} дн.)\n";
                            } else {
                                $text .= "         🔴 Эффективность: {$subEfficiency}% ({$subDeltaSign} {$subDeltaAbs} дн.)\n";
                            }
                        } elseif ($subMilestone['fact'] > 0) {
                            $text .= "         ⚠️ План не установлен (⬆️ {$subMilestone['fact']} дн.)\n";
                        }
                    }
                    
                    $text .= "\n";
                }
                $text .= "\n";
            }
            
            // Mobilization with description (only if plan or fact > 0)
            $mobFact = $smrAdditional['mobilization_smr'] ?? 0;
            if ($smrMobilizationPlan > 0 || $mobFact > 0) {
                $text .= "🚗 <b>Мобилизационные мероприятия</b>\n";
                $text .= "(Дорога, Инструктаж, Подготовка к служебной поездке, Сдача авансового отчета)\n";
                $text .= "📊 План: {$smrMobilizationPlan} дн. | Факт: {$mobFact} дн.\n";
                if ($smrMobilizationPlan > 0 && $mobFact > 0) {
                    $mobDelta = $mobFact - $smrMobilizationPlan;
                    if ($mobDelta != 0) {
                        $mobDeltaSign = $mobDelta > 0 ? '⬆️' : '⬇️';
                        $text .= "⚠️ Отклонение: {$mobDeltaSign} " . abs($mobDelta) . " дн.\n";
                    }
                } elseif ($mobFact > 0) {
                    $text .= "⚠️ План не установлен (⬆️ {$mobFact} дн.)\n";
                }
                $text .= "\n";
            }
            
            // Project activity with description (only if plan or fact > 0)
            $projActFact = $smrAdditional['project_activity_smr'] ?? 0;
            if ($smrProjectActivityPlan > 0 || $projActFact > 0) {
                $text .= "📝 <b>Проектная деятельность</b>\n";
                $text .= "(Разработка графика работ, Разработка программы работ)\n";
                $text .= "📊 План: {$smrProjectActivityPlan} дн. | Факт: {$projActFact} дн.\n";
                if ($smrProjectActivityPlan > 0 && $projActFact > 0) {
                    $projDelta = $projActFact - $smrProjectActivityPlan;
                    if ($projDelta != 0) {
                        $projDeltaSign = $projDelta > 0 ? '⬆️' : '⬇️';
                        $text .= "⚠️ Отклонение: {$projDeltaSign} " . abs($projDelta) . " дн.\n";
                    }
                } elseif ($projActFact > 0) {
                    $text .= "⚠️ План не установлен (⬆️ {$projActFact} дн.)\n";
                }
                $text .= "\n";
            }
            
            // Дополнительные работы section
            if ($masterDutiesFact > 0 || $rkCommentsFact > 0 || $warrantyFact > 0 || $downtimeFact > 0 || $unaccountedWorksFact > 0) {
                $text .= "📋 <b>Дополнительные работы:</b>\n";
                
                // 1. Исполнение обязанностей мастера (with plan/fact analysis)
                if ($smrMasterDutiesPlan > 0 || $masterDutiesFact > 0) {
                    $text .= "  • <b>Исполнение обязанностей мастера</b>\n";
                    $text .= "    📊 План: {$smrMasterDutiesPlan} дн. | Факт: {$masterDutiesFact} дн.\n";
                    
                    if ($smrMasterDutiesPlan > 0 && $masterDutiesFact > 0) {
                        $masterEfficiency = round(($smrMasterDutiesPlan / $masterDutiesFact) * 100, 1);
                        $masterDelta = $masterDutiesFact - $smrMasterDutiesPlan;
                        $masterDeltaSign = $masterDelta > 0 ? '⬆️' : '⬇️';
                        $masterDeltaAbs = abs($masterDelta);
                        
                        if ($masterEfficiency >= 90) {
                            $text .= "    🟢 Эффективность: {$masterEfficiency}% ({$masterDeltaSign} {$masterDeltaAbs} дн.)\n";
                        } elseif ($masterEfficiency >= 70) {
                            $text .= "    🟡 Эффективность: {$masterEfficiency}% ({$masterDeltaSign} {$masterDeltaAbs} дн.)\n";
                        } else {
                            $text .= "    🔴 Эффективность: {$masterEfficiency}% ({$masterDeltaSign} {$masterDeltaAbs} дн.)\n";
                        }
                    } elseif ($masterDutiesFact > 0) {
                        $text .= "    ⚠️ План не установлен (⬆️ {$masterDutiesFact} дн.)\n";
                    }
                }
                
                // 3. Устранение замечаний РК (fact only)
                if ($rkCommentsFact > 0) {
                    $text .= "  • Устранение замечаний РК: {$rkCommentsFact} дн.\n";
                }
                
                // 4. Работы по гарантии (fact only)
                if ($warrantyFact > 0) {
                    $text .= "  • Работы по гарантии: {$warrantyFact} дн.\n";
                }
                
                // 5. Простой (fact only)
                if ($downtimeFact > 0) {
                    $text .= "  • Простой: {$downtimeFact} дн.\n";
                }
                
                // 6. Прочие неучтённые работы (everything else that doesn't fit other categories)
                if ($unaccountedWorksFact > 0) {
                    $text .= "  • Прочие неучтённые работы: {$unaccountedWorksFact} дн.\n";
                }
                
                $text .= "\n";
            }
            
            $navButtons = $this->createCardNavigationButtons($projectCode, 'smr');
            $keyboard = [
                $navButtons,
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_card_list']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showProjectCardSMRPage error: " . $e->getMessage());
            error_log("ProjectHandler::showProjectCardSMRPage trace: " . $e->getTraceAsString());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка загрузки ШМР: ' . $e->getMessage());
        }
    }
    
    /**
     * Show СМР page of project card
     */
    private function showProjectCardCMRPage(string $chatId, int $userId, string $projectCode, array $project, $conn): ServerResponse
    {
        try {
            // Get CMR milestones with plan from project_milestones
            $milestoneNames = $this->getCMRMilestoneNames();
            
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cmrMilestonesPlan = [];
            while ($row = $result->fetch_assoc()) {
                $cmrMilestonesPlan[$row['milestone_name']] = [
                    'planned_hours' => $row['planned_hours'],
                    'is_not_applicable' => $row['is_not_applicable'],
                    'unit' => $row['unit']
                ];
            }
            
            // Get CMR milestone fact days from tabel (excluding testers)
            // Using two-level structure: top-level milestones contain sub-milestones
            $milestoneStructure = $this->getCMRMilestoneStructure();
            $cmrMilestonesFact = []; // Store fact for each top-level and sub-milestone
            
            foreach ($milestoneNames as $topMilestoneName) {
                $subMilestones = $milestoneStructure[$topMilestoneName] ?? [];
                $topMilestoneFact = 0;
                $cmrMilestonesFact[$topMilestoneName] = ['subs' => [], 'total' => 0];
                
                foreach ($subMilestones as $subMilestoneName => $activities) {
                    // All activities contain full text patterns to LIKE-match directly
                    $activityConditions = [];
                    foreach ($activities as $activity) {
                        $activityConditions[] = "ot.message LIKE '%" . $conn->real_escape_string($activity) . "%'";
                    }
                    $activityFilter = '(' . implode(' OR ', $activityConditions) . ')';
                    
                    $sql = "
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND {$activityFilter}
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('s', $projectCode);
                    
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $subFact = $row['days_count'] ?? 0;
                    
                    $cmrMilestonesFact[$topMilestoneName]['subs'][$subMilestoneName] = $subFact;
                    $topMilestoneFact += $subFact;
                }
                
                $cmrMilestonesFact[$topMilestoneName]['total'] = $topMilestoneFact;
            }
            
            // Get CMR mobilization from tabel
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Дорога%' 
                         OR ot.message LIKE '%Инструктаж%' 
                         OR ot.message LIKE '%Подготовка к служебной поездке%'
                         OR ot.message LIKE '%Сдача авансового отчета%')
                         AND ot.message LIKE CONCAT('%', ?, '%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                         THEN ot.id END) as mobilization_cmr
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('ss', $projectCode, $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cmrAdditional = $result->fetch_assoc();
            
            // Get TOTAL CMR fact count
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT ot.id) as cmr_total_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cmrTotalRow = $result->fetch_assoc();
            $cmrTotalFactFromTabel = $cmrTotalRow['cmr_total_count'] ?? 0;
            
            // Calculate total plan from sub-milestones
            $cmrTotalPlan = 0;
            foreach ($cmrMilestonesPlan as $name => $data) {
                if (!$data['is_not_applicable'] && $data['planned_hours'] !== null) {
                    if ($data['unit'] === 'hours') {
                        $cmrTotalPlan += ceil($data['planned_hours'] / 8);
                    } else {
                        $cmrTotalPlan += $data['planned_hours'];
                    }
                }
            }
            
            // Get mobilization plan from project_cards
            $stmt = $conn->prepare("
                SELECT cmr_mobilization, cmr_forecast_enabled
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $cmrMobilizationPlan = ($cardData && $cardData['cmr_mobilization']) ? $cardData['cmr_mobilization'] : 0;
            $cmrTotalPlan += $cmrMobilizationPlan;
            
            // Check if forecast is enabled for this project
            $forecastEnabled = ($cardData && isset($cardData['cmr_forecast_enabled'])) ? (bool)$cardData['cmr_forecast_enabled'] : true;
            
            // Build message
            $text = "🏗️ <b>СМР (Мобильная бригада)</b>\n\n";
            $text .= "<b>Шифр:</b> " . $project['project_code'] . "\n";
            $text .= "<b>Наименование:</b> " . $project['project_name'] . "\n\n";
            
            // Overall metrics
            $text .= "📊 <b>План:</b> " . $cmrTotalPlan . " дн. | <b>Факт:</b> " . $cmrTotalFactFromTabel . " дн.\n";
            $text .= "\n";
            
            // ============ CURRENT EFFICIENCY ============
            if ($cmrTotalPlan > 0 && $cmrTotalFactFromTabel > 0) {
                $text .= "<b>📈 Текущая эффективность:</b>\n";
                
                $efficiency = round(($cmrTotalPlan / $cmrTotalFactFromTabel) * 100, 1);
                $delta = $cmrTotalFactFromTabel - $cmrTotalPlan;
                $deltaSign = $delta > 0 ? '⬆️' : '⬇️';
                $deltaAbs = abs($delta);
                $icon = $efficiency >= 100 ? '🟢' : ($efficiency >= 80 ? '🟡' : '🔴');
                $text .= "{$icon} Общая: {$efficiency}% ({$deltaSign} {$deltaAbs} дн.)\n";
                
                // Stage (milestone-only) efficiency
                $cmrStagePlan = 0;
                foreach ($cmrMilestonesPlan as $name => $data) {
                    if (!$data['is_not_applicable'] && $data['planned_hours'] !== null) {
                        if ($data['unit'] === 'hours') {
                            $cmrStagePlan += ceil($data['planned_hours'] / 8);
                        } else {
                            $cmrStagePlan += $data['planned_hours'];
                        }
                    }
                }
                $cmrStageFact = 0;
                foreach ($cmrMilestonesFact as $mData) {
                    $cmrStageFact += $mData['total'] ?? 0;
                }
                if ($cmrStagePlan > 0 && $cmrStageFact > 0) {
                    $stageEff = round(($cmrStagePlan / $cmrStageFact) * 100, 1);
                    $stageDelta = $cmrStageFact - $cmrStagePlan;
                    $stageDeltaSign = $stageDelta > 0 ? '⬆️' : '⬇️';
                    $stageDeltaAbs = abs($stageDelta);
                    $stageIcon = $stageEff >= 100 ? '🟢' : ($stageEff >= 80 ? '🟡' : '🔴');
                    $text .= "{$stageIcon} Этап: {$stageEff}% ({$stageDeltaSign} {$stageDeltaAbs} дн.)\n";
                }
            } elseif ($cmrTotalPlan == 0 && $cmrTotalFactFromTabel > 0) {
                $text .= "⚠️ План не установлен\n";
            }
            
            // ============ FORECAST EFFICIENCY ============
            if ($forecastEnabled) {
                $cmrForecast = $this->calculateOverallCMRDosborkForecast($projectCode, $conn);
                if ($cmrForecast && $cmrForecast['overallProgress'] > 0 && $cmrTotalPlan > 0 && $cmrTotalFactFromTabel > 0) {
                    $smrProgress = $cmrForecast['overallProgress'];
                    
                    if ($smrProgress >= 100) {
                        $forecastTotalFact = $cmrTotalFactFromTabel;
                        $forecastStageFact = $cmrStageFact ?? $cmrTotalFactFromTabel;
                    } else {
                        $forecastTotalFact = round($cmrTotalFactFromTabel / ($smrProgress / 100), 1);
                        $forecastStageFact = isset($cmrStageFact) && $cmrStageFact > 0 ? round($cmrStageFact / ($smrProgress / 100), 1) : 0;
                    }
                    
                    $text .= "\n<b>🔮 Прогноз эффективности:</b>\n";
                    $text .= "📊 Прогресс ШМР: {$smrProgress}%\n";
                    
                    // Forecast overall
                    if ($forecastTotalFact > 0) {
                        $forecastOverall = round(($cmrTotalPlan / $forecastTotalFact) * 100, 1);
                        $forecastOverallDelta = round($forecastTotalFact - $cmrTotalPlan, 1);
                        $forecastOverallDeltaStr = $forecastOverallDelta > 0 ? "⬆️ {$forecastOverallDelta}" : ($forecastOverallDelta < 0 ? "⬇️ " . abs($forecastOverallDelta) : "0");
                        $forecastOverallIcon = $forecastOverall >= 100 ? '🟢' : ($forecastOverall >= 80 ? '🟡' : '🔴');
                        $text .= "{$forecastOverallIcon} Общая: {$forecastOverall}% ({$forecastOverallDeltaStr} дн.)\n";
                    }
                    
                    // Forecast stage
                    if ($forecastStageFact > 0 && isset($cmrStagePlan) && $cmrStagePlan > 0) {
                        $forecastStage = round(($cmrStagePlan / $forecastStageFact) * 100, 1);
                        $forecastStageDelta = round($forecastStageFact - $cmrStagePlan, 1);
                        $forecastStageDeltaStr = $forecastStageDelta > 0 ? "⬆️ {$forecastStageDelta}" : ($forecastStageDelta < 0 ? "⬇️ " . abs($forecastStageDelta) : "0");
                        $forecastStageIcon = $forecastStage >= 100 ? '🟢' : ($forecastStage >= 80 ? '🟡' : '🔴');
                        $text .= "{$forecastStageIcon} Этап: {$forecastStage}% ({$forecastStageDeltaStr} дн.)\n";
                    }
                }
            }
            $text .= "\n";
            
            // Build display structure for milestones
            $hasAnyMilestones = false;
            $milestoneDisplayData = [];
            
            foreach ($milestoneNames as $topMilestoneName) {
                $subMilestones = $milestoneStructure[$topMilestoneName] ?? [];
                $topMilestonePlan = 0;
                $topMilestoneFact = $cmrMilestonesFact[$topMilestoneName]['total'] ?? 0;
                $subMilestoneData = [];
                
                foreach ($subMilestones as $subMilestoneName => $activities) {
                    // Full name as stored in DB: "Слесарные работы - Досборка"
                    $fullSubName = $topMilestoneName . ' - ' . $subMilestoneName;
                    $planData = $cmrMilestonesPlan[$fullSubName] ?? null;
                    
                    // Skip if not applicable
                    if ($planData && $planData['is_not_applicable']) {
                        continue;
                    }
                    
                    // Get plan
                    $subPlan = 0;
                    if ($planData && $planData['planned_hours'] !== null) {
                        $subPlan = $planData['planned_hours'];
                        if ($planData['unit'] === 'hours') {
                            $subPlan = ceil($subPlan / 8);
                        }
                    }
                    
                    // Get fact
                    $subFact = $cmrMilestonesFact[$topMilestoneName]['subs'][$subMilestoneName] ?? 0;
                    
                    // Skip if both plan and fact are 0
                    if ($subPlan == 0 && $subFact == 0) {
                        continue;
                    }
                    
                    $subMilestoneData[] = [
                        'name' => $subMilestoneName,
                        'plan' => $subPlan,
                        'fact' => $subFact
                    ];
                    
                    $topMilestonePlan += $subPlan;
                }
                
                // Skip top milestone if no sub-milestones to display
                if (empty($subMilestoneData)) {
                    continue;
                }
                
                $hasAnyMilestones = true;
                
                $milestoneDisplayData[] = [
                    'name' => $topMilestoneName,
                    'plan' => $topMilestonePlan,
                    'fact' => $topMilestoneFact,
                    'subs' => $subMilestoneData
                ];
            }
            
            // Display milestones
            if ($hasAnyMilestones) {
                $text .= "<b>Вехи:</b>\n";
                $topCounter = 0;
                $cmrMapping = $this->getCMRToSMRMilestoneMapping();
                
                foreach ($milestoneDisplayData as $topMilestone) {
                    $topCounter++;
                    
                    // Top-level milestone header
                    $text .= "  {$topCounter}. <b>{$topMilestone['name']}</b>\n";
                    $planText = $topMilestone['plan'];
                    $text .= "     📊 План: {$planText} дн. | Факт: {$topMilestone['fact']} дн.\n";
                    
                    // Current efficiency for top-level milestone
                    if ($topMilestone['plan'] > 0 && $topMilestone['fact'] > 0) {
                        $efficiency = round(($topMilestone['plan'] / $topMilestone['fact']) * 100, 1);
                        $delta = $topMilestone['fact'] - $topMilestone['plan'];
                        $deltaSign = $delta > 0 ? '⬆️' : '⬇️';
                        $deltaAbs = abs($delta);
                        $icon = $efficiency >= 100 ? '🟢' : ($efficiency >= 80 ? '🟡' : '🔴');
                        $text .= "     {$icon} Эффективность: {$efficiency}% ({$deltaSign} {$deltaAbs} дн.)\n";
                    } elseif ($topMilestone['fact'] > 0) {
                        $text .= "     ⚠️ План не установлен (⬆️ {$topMilestone['fact']} дн.)\n";
                    }
                    
                    // Per-milestone forecast based on related ШМР milestones
                    if ($forecastEnabled && $topMilestone['plan'] > 0 && $topMilestone['fact'] > 0) {
                        $milestoneForecast = $this->calculateCMRForecastFromSMR($projectCode, $topMilestone['name'], $conn);
                        if ($milestoneForecast && $milestoneForecast['progress'] > 0) {
                            $mProgress = $milestoneForecast['progress'];
                            if ($mProgress >= 100) {
                                $mForecastFact = $topMilestone['fact'];
                            } else {
                                $mForecastFact = round($topMilestone['fact'] / ($mProgress / 100), 1);
                            }
                            $mForecastEff = round(($topMilestone['plan'] / $mForecastFact) * 100, 1);
                            $mForecastDelta = round($mForecastFact - $topMilestone['plan'], 1);
                            $mForecastDeltaStr = $mForecastDelta > 0 ? "⬆️ {$mForecastDelta}" : ($mForecastDelta < 0 ? "⬇️ " . abs($mForecastDelta) : "0");
                            $mForecastIcon = $mForecastEff >= 100 ? '🟢' : ($mForecastEff >= 80 ? '🟡' : '🔴');
                            $text .= "     🔮 Прогноз: {$mForecastIcon} {$mForecastEff}% ({$mForecastDeltaStr} дн.) [{$mProgress}%]\n";
                        }
                    }
                    
                    // Sub-milestones with efficiency analysis
                    foreach ($topMilestone['subs'] as $subMilestone) {
                        $text .= "       • <b>{$subMilestone['name']}</b>\n";
                        $subPlanText = $subMilestone['plan'];
                        $text .= "         📊 План: {$subPlanText} дн. | Факт: {$subMilestone['fact']} дн.\n";
                        
                        // Efficiency analysis for sub-milestone
                        if ($subMilestone['plan'] > 0 && $subMilestone['fact'] > 0) {
                            $subEfficiency = round(($subMilestone['plan'] / $subMilestone['fact']) * 100, 1);
                            $subDelta = $subMilestone['fact'] - $subMilestone['plan'];
                            $subDeltaSign = $subDelta > 0 ? '⬆️' : '⬇️';
                            $subDeltaAbs = abs($subDelta);
                            $subIcon = $subEfficiency >= 100 ? '🟢' : ($subEfficiency >= 80 ? '🟡' : '🔴');
                            $text .= "         {$subIcon} Эффективность: {$subEfficiency}% ({$subDeltaSign} {$subDeltaAbs} дн.)\n";
                        } elseif ($subMilestone['fact'] > 0) {
                            $text .= "         ⚠️ План не установлен (⬆️ {$subMilestone['fact']} дн.)\n";
                        }
                    }
                    
                    $text .= "\n";
                }
            }
            
            // Mobilization with description (only if plan or fact > 0)
            $mobFact = $cmrAdditional['mobilization_cmr'] ?? 0;
            if ($cmrMobilizationPlan > 0 || $mobFact > 0) {
                $text .= "🚗 <b>Мобилизационные мероприятия</b>\n";
                $text .= "(Дорога, Инструктаж, Подготовка к служебной поездке, Сдача авансового отчета)\n";
                $text .= "📊 План: {$cmrMobilizationPlan} дн. | Факт: {$mobFact} дн.\n";
                if ($cmrMobilizationPlan > 0 && $mobFact > 0) {
                    $mobDelta = $mobFact - $cmrMobilizationPlan;
                    if ($mobDelta != 0) {
                        $mobDeltaSign = $mobDelta > 0 ? '⬆️' : '⬇️';
                        $text .= "⚠️ Отклонение: {$mobDeltaSign} " . abs($mobDelta) . " дн.\n";
                    }
                } elseif ($mobFact > 0) {
                    $text .= "⚠️ План не установлен (⬆️ {$mobFact} дн.)\n";
                }
                $text .= "\n";
            }
            
            $navButtons = $this->createCardNavigationButtons($projectCode, 'cmr');
            $keyboard = [
                $navButtons,
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_card_list']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showProjectCardCMRPage error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка загрузки СМР: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate SMR efficiency summary for info page
     * Returns a formatted string with efficiency status
     */
    private function calculateSMREfficiencySummary(string $projectCode, $conn): string
    {
        try {
            // Get SMR milestones with plan
            $milestoneNames = $this->getSMRMilestoneNames();
            $milestoneStructure = $this->getSMRMilestoneStructure();
            
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ШМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $smrMilestonesPlan = [];
            while ($row = $result->fetch_assoc()) {
                $smrMilestonesPlan[$row['milestone_name']] = [
                    'planned_hours' => $row['planned_hours'],
                    'is_not_applicable' => $row['is_not_applicable'],
                    'unit' => $row['unit']
                ];
            }
            
            // Get list of valid sub-milestone names
            $validSubMilestoneNames = [];
            foreach ($milestoneStructure as $topName => $subs) {
                foreach ($subs as $subName => $activities) {
                    $validSubMilestoneNames[] = $subName;
                }
            }
            
            // Check if there's any SMR milestone plan data (only for valid sub-milestones)
            $hasMilestonePlan = false;
            foreach ($smrMilestonesPlan as $name => $data) {
                if (!in_array($name, $validSubMilestoneNames)) {
                    continue; // Ignore orphaned records
                }
                if (!$data['is_not_applicable'] && $data['planned_hours'] > 0) {
                    $hasMilestonePlan = true;
                    break;
                }
            }
            
            // Get auxiliary plan from project_cards
            $stmt = $conn->prepare("
                SELECT smr_mobilization, smr_project_activity, smr_master_duties
                FROM project_cards WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            $smrMobilizationPlan = ($cardData && isset($cardData['smr_mobilization'])) ? (int)$cardData['smr_mobilization'] : 0;
            $smrProjectActivityPlan = ($cardData && isset($cardData['smr_project_activity'])) ? (int)$cardData['smr_project_activity'] : 0;
            $smrMasterDutiesPlan = ($cardData && isset($cardData['smr_master_duties'])) ? (int)$cardData['smr_master_duties'] : 0;
            $auxPlan = $smrMobilizationPlan + $smrProjectActivityPlan + $smrMasterDutiesPlan;
            
            // Get total fact from tabel (with same exclusion filters as showProjectCardSMRPage)
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT ot.id) as total_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                AND ot.message NOT LIKE '%Этап наладки завершен%'
                AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $totalFactFromTabel = $row['total_count'] ?? 0;
            
            // Calculate overall plan including auxiliary work
            $overallPlan = $auxPlan;
            
            // Return "нет данных" only if there's no plan AND no fact
            if (!$hasMilestonePlan && $overallPlan == 0 && $totalFactFromTabel == 0) {
                return "🔧 ШМР: — нет данных\n";
            }
            
            // If no milestone plan (for ШМР) but has auxiliary plan or fact, show simple efficiency
            if (!$hasMilestonePlan) {
                if ($totalFactFromTabel > 0 && $overallPlan > 0) {
                    $efficiency = round(($overallPlan / $totalFactFromTabel) * 100, 1);
                    $effIcon = $efficiency >= 100 ? '🟢' : ($efficiency >= 80 ? '🟡' : '🔴');
                    $delta = $totalFactFromTabel - $overallPlan;
                    $deltaStr = $delta > 0 ? "⬆️ {$delta}" : ($delta < 0 ? "⬇️ " . abs($delta) : "0");
                    return "🔧 ШМР: {$effIcon} {$efficiency}% ({$deltaStr} дн.)\n   План: {$overallPlan} дн. | Факт: {$totalFactFromTabel} дн.\n";
                } elseif ($totalFactFromTabel > 0) {
                    return "🔧 ШМР: ⚠️ План не установлен (Факт: {$totalFactFromTabel} дн.)\n";
                } else {
                    return "🔧 ШМР: 📋 План {$overallPlan} дн.\n";
                }
            }
            
            // Get milestone facts per sub-milestone and NVOR
            $smrMilestonesFact = [];
            $smrMilestonesConfirmed = [];
            $smrSubConfirmed = [];
            
            foreach ($milestoneNames as $topMilestoneName) {
                $subMilestones = $milestoneStructure[$topMilestoneName] ?? [];
                $topMilestoneFact = 0;
                $smrMilestonesFact[$topMilestoneName] = ['subs' => [], 'total' => 0];
                $allSubsConfirmed = true;
                $hasActiveSubs = false;
                
                foreach ($subMilestones as $subMilestoneName => $activities) {
                    $activityConditions = [];
                    foreach ($activities as $activity) {
                        $activityConditions[] = "ot.message LIKE '%" . $conn->real_escape_string($activity) . "%'";
                    }
                    $activityFilter = '(' . implode(' OR ', $activityConditions) . ')';
                    
                    $sql = "
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND {$activityFilter}
                        AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('s', $projectCode);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $subFact = $row['days_count'] ?? 0;
                    
                    $smrMilestonesFact[$topMilestoneName]['subs'][$subMilestoneName] = $subFact;
                    $topMilestoneFact += $subFact;
                    
                    // NVOR per sub-milestone
                    $nvorConditions = [];
                    foreach ($activities as $activity) {
                        if (strpos($activity, 'Работы по графику (') === 0) {
                            $nvorPattern = str_replace('Работы по графику (', 'Работы неучтенные в ВОР (', $activity);
                            $nvorPattern = str_replace('/', '%', $nvorPattern);
                            $nvorConditions[] = "ot.message LIKE '%" . $conn->real_escape_string($nvorPattern) . "%'";
                        }
                    }
                    
                    $subNvor = 0;
                    if (!empty($nvorConditions)) {
                        $nvorFilter = '(' . implode(' OR ', $nvorConditions) . ')';
                        $nvorSql = "
                            SELECT COUNT(DISTINCT ot.id) as nvor_count
                            FROM ozna_tabel ot
                            INNER JOIN ozna_data od ON ot.user_id = od.user_id
                            WHERE ot.message LIKE CONCAT('%', ?, '%')
                            AND {$nvorFilter}
                            AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                            AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                        ";
                        $nvorStmt = $conn->prepare($nvorSql);
                        $nvorStmt->bind_param('s', $projectCode);
                        $nvorStmt->execute();
                        $nvorResult = $nvorStmt->get_result();
                        $nvorRow = $nvorResult->fetch_assoc();
                        $subNvor = $nvorRow['nvor_count'] ?? 0;
                    }
                    $smrMilestonesFact[$topMilestoneName]['subs_nvor'][$subMilestoneName] = $subNvor;
                    
                    // Check if this sub-milestone is confirmed
                    $stmt = $conn->prepare("
                        SELECT COUNT(*) as confirmed_count
                        FROM ozna_tabel ot
                        LEFT JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND ot.message LIKE '%Этап шеф-монтажа завершен%'
                        AND ot.message LIKE CONCAT('%', ?, '%')
                        AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                             OR ot.message LIKE '%[Конструктор]%')
                    ");
                    $stmt->bind_param('ss', $projectCode, $subMilestoneName);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $isSubConfirmed = ($row['confirmed_count'] ?? 0) > 0;
                    $smrSubConfirmed[$subMilestoneName] = $isSubConfirmed;
                    
                    $hasActiveSubs = true;
                    if (!$isSubConfirmed) {
                        $allSubsConfirmed = false;
                    }
                }
                
                $smrMilestonesFact[$topMilestoneName]['total'] = $topMilestoneFact;
                
                // Top milestone confirmed if all active sub-milestones are confirmed
                $smrMilestonesConfirmed[$topMilestoneName] = $hasActiveSubs && $allSubsConfirmed;
            }
            
            // Get additional work facts
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Исполнение обязанностей мастера%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as master_duties_smr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as rk_comments_smr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as warranty_smr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as downtime_smr,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Дорога%' 
                         OR ot.message LIKE '%Инструктаж%' 
                         OR ot.message LIKE '%Подготовка к служебной поездке%'
                         OR ot.message LIKE '%Сдача авансового отчета%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as mobilization_smr,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Разработка графика работ%'
                         OR ot.message LIKE '%Разработка программы работ%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                         THEN ot.id END) as project_activity_smr
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('sssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $smrAdditional = $result->fetch_assoc();
            
            $mobFact = $smrAdditional['mobilization_smr'] ?? 0;
            $projActFact = $smrAdditional['project_activity_smr'] ?? 0;
            $masterDutiesFact = $smrAdditional['master_duties_smr'] ?? 0;
            $rkCommentsFact = $smrAdditional['rk_comments_smr'] ?? 0;
            $warrantyFact = $smrAdditional['warranty_smr'] ?? 0;
            $downtimeFact = $smrAdditional['downtime_smr'] ?? 0;
            
            // Get TOTAL SMR fact count
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT ot.id) as smr_total_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                AND ot.message NOT LIKE '%Этап наладки завершен%'
                AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $smrTotalRow = $result->fetch_assoc();
            $smrTotalFactFromTabel = $smrTotalRow['smr_total_count'] ?? 0;
            
            // Get additional work plan from project_cards
            $stmt = $conn->prepare("
                SELECT smr_mobilization, smr_project_activity, smr_master_duties
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $smrMobilizationPlan = ($cardData && $cardData['smr_mobilization']) ? $cardData['smr_mobilization'] : 0;
            $smrProjectActivityPlan = ($cardData && $cardData['smr_project_activity']) ? $cardData['smr_project_activity'] : 0;
            $smrMasterDutiesPlan = ($cardData && $cardData['smr_master_duties']) ? $cardData['smr_master_duties'] : 0;
            
            // Get milestone progress estimates from survey data (ШМР)
            $milestoneEstimates = [];
            $stmt = $conn->prepare("
                SELECT milestone_name, estimated_percent, efficiency_at_estimate, 
                       estimator_fio, estimated_at
                FROM milestone_progress_estimates
                WHERE project_code = ? AND work_type = 'ШМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $milestoneEstimates[$row['milestone_name']] = $row;
            }
            
            // Calculate milestone totals and NVOR
            $milestonesTotalFact = 0;
            $totalNvorFact = 0;
            foreach ($smrMilestonesFact as $milestoneData) {
                $milestonesTotalFact += $milestoneData['total'] ?? 0;
                if (isset($milestoneData['subs_nvor'])) {
                    foreach ($milestoneData['subs_nvor'] as $nvorValue) {
                        $totalNvorFact += $nvorValue;
                    }
                }
            }
            
            // Calculate effective work per sub-milestone
            $milestonesEffectiveWork = 0;
            $forecastRemainingActualDays = 0;
            $milestonesPlanTotal = 0;
            
            foreach ($milestoneNames as $topMilestoneName) {
                $subMilestones = $milestoneStructure[$topMilestoneName] ?? [];
                $isTopConfirmed = $smrMilestonesConfirmed[$topMilestoneName] ?? false;
                
                foreach ($subMilestones as $subMilestoneName => $activities) {
                    $planData = $smrMilestonesPlan[$subMilestoneName] ?? null;
                    if ($planData && $planData['is_not_applicable']) continue;
                    
                    $subPlan = 0;
                    if ($planData && $planData['planned_hours'] !== null && $planData['planned_hours'] > 0) {
                        $subPlan = $planData['planned_hours'];
                        if ($planData['unit'] === 'hours') {
                            $subPlan = ceil($subPlan / 8);
                        }
                        $milestonesPlanTotal += $subPlan;
                    }
                    
                    $subFact = $smrMilestonesFact[$topMilestoneName]['subs'][$subMilestoneName] ?? 0;
                    $estimate = $milestoneEstimates[$subMilestoneName] ?? null;
                    $isSubConfirmedForCalc = $smrSubConfirmed[$subMilestoneName] ?? false;
                    
                    if ($subPlan > 0) {
                        if ($isTopConfirmed || $isSubConfirmedForCalc) {
                            // Sub-milestone confirmed = 100% completion
                            $milestonesEffectiveWork += $subPlan;
                        } elseif ($subFact > 0 && $estimate) {
                            // Sub-milestone has work done - use survey estimate
                            $effectiveDone = ($estimate['estimated_percent'] * $subPlan) / 100;
                            $milestonesEffectiveWork += $effectiveDone;
                            $remainingPlan = $subPlan - $effectiveDone;
                            if ($effectiveDone > 0 && $remainingPlan > 0) {
                                $milestoneEfficiency = $effectiveDone / $subFact;
                                $forecastRemainingActualDays += $remainingPlan / $milestoneEfficiency;
                            } elseif ($remainingPlan > 0) {
                                $forecastRemainingActualDays += $remainingPlan;
                            }
                        } elseif ($subFact > 0) {
                            // Has fact but no estimate - use min(fact, plan) as conservative estimate
                            $effectiveDone = min($subFact, $subPlan);
                            $milestonesEffectiveWork += $effectiveDone;
                            $remainingPlan = $subPlan - $effectiveDone;
                            if ($effectiveDone > 0 && $remainingPlan > 0) {
                                $milestoneEfficiency = $effectiveDone / $subFact;
                                $forecastRemainingActualDays += $remainingPlan / $milestoneEfficiency;
                            } elseif ($remainingPlan > 0) {
                                $forecastRemainingActualDays += $remainingPlan;
                            }
                        } else {
                            // Not started - assume 100% efficiency for forecast
                            $forecastRemainingActualDays += $subPlan;
                        }
                    }
                }
            }
            
            // Stage = milestones only (mob/projAct/masterDuties treated as auxiliary)
            $stagePlanTime = $milestonesPlanTotal;
            $stageFactTime = $milestonesTotalFact;
            $stageEffectiveWork = $milestonesEffectiveWork;
            
            // Overall plan includes auxiliary activities
            $overallPlanTime = $milestonesPlanTotal + $smrMobilizationPlan + $smrProjectActivityPlan + $smrMasterDutiesPlan;
            
            // Additional work (NVOR + RK + warranty + downtime + unaccounted) — reduces overall only
            $unaccountedWorksFact = $smrTotalFactFromTabel - ($milestonesTotalFact + $totalNvorFact + $mobFact + $projActFact + $masterDutiesFact + $rkCommentsFact + $warrantyFact + $downtimeFact);
            $unaccountedWorksFact = max(0, $unaccountedWorksFact);
            $auxiliaryFact = $mobFact + $projActFact + $masterDutiesFact;
            $additionalWorkFact = $totalNvorFact + $rkCommentsFact + $warrantyFact + $downtimeFact + $unaccountedWorksFact;
            $totalFactTime = $stageFactTime + $auxiliaryFact + $additionalWorkFact;
            
            if ($stagePlanTime == 0) {
                return "🔧 ШМР: — план не установлен\n";
            }
            
            $remainingPlanWork = max(0, $stagePlanTime - $stageEffectiveWork);
            
            // Stage completed
            if ($remainingPlanWork <= 0) {
                $result = "🔧 ШМР: ✅ Завершен\n   Эффективность:";
                if ($totalFactTime > 0) {
                    $overallEff = round(($overallPlanTime / $totalFactTime) * 100, 1);
                    $overallIcon = $overallEff >= 100 ? '🟢' : ($overallEff >= 80 ? '🟡' : '🔴');
                    $result .= "\n   Общая: {$overallIcon} {$overallEff}%";
                }
                if ($stageFactTime > 0) {
                    $stageEff = round(($stagePlanTime / $stageFactTime) * 100, 1);
                    $stageIcon = $stageEff >= 100 ? '🟢' : ($stageEff >= 80 ? '🟡' : '🔴');
                    $result .= "\n   Этап: {$stageIcon} {$stageEff}%";
                }
                return $result . "\n";
            }
            
            // Stage in progress - show forecast
            $forecastText = "🔧 ШМР: 🔄 В работе\n   Эффективность:";
            
            // Forecast overall efficiency
            if ($totalFactTime > 0) {
                $factForecastOverall = $totalFactTime + $forecastRemainingActualDays;
                $forecastOverall = round(($overallPlanTime / $factForecastOverall) * 100, 1);
                $overallDelta = round($factForecastOverall - $overallPlanTime, 1);
                $overallDeltaStr = $overallDelta > 0 ? "⬆️ {$overallDelta}" : ($overallDelta < 0 ? "⬇️ " . abs($overallDelta) : "0");
                $overallIcon = $forecastOverall >= 100 ? '🟢' : ($forecastOverall >= 80 ? '🟡' : '🔴');
                $forecastText .= "\n   Общая: {$overallIcon} {$forecastOverall}% ({$overallDeltaStr} дн.)";
            }
            
            // Forecast stage efficiency
            if ($stageFactTime > 0) {
                $factForecastStage = $stageFactTime + $forecastRemainingActualDays;
                $forecastEfficiency = round(($stagePlanTime / $factForecastStage) * 100, 1);
                $forecastDelta = round($factForecastStage - $stagePlanTime, 1);
                $forecastDeltaStr = $forecastDelta > 0 ? "⬆️ {$forecastDelta}" : ($forecastDelta < 0 ? "⬇️ " . abs($forecastDelta) : "0");
                $colorIcon = $forecastEfficiency >= 100 ? '🟢' : ($forecastEfficiency >= 80 ? '🟡' : '🔴');
                $forecastText .= "\n   Этап: {$colorIcon} {$forecastEfficiency}% ({$forecastDeltaStr} дн.)";
            }
            
            // Get personnel count for completion date
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT JSON_EXTRACT(od.data, '$.fio')) as unique_specialists
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                AND ot.dt >= DATE_SUB(NOW(), INTERVAL 3 DAY)
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $personnelRow = $result->fetch_assoc();
            $personnelCount = $personnelRow['unique_specialists'] ?? 0;
            
            if ($personnelCount > 0) {
                $daysRemaining = ceil($forecastRemainingActualDays / $personnelCount);
                $completionDate = new \DateTime();
                $completionDate->modify("+{$daysRemaining} days");
                $completionDateStr = $completionDate->format('d.m.Y');
                $forecastText .= "\n   Прогноз завершения: ~{$completionDateStr}";
            }
            
            return $forecastText . "\n";
            
        } catch (\Exception $e) {
            error_log("calculateSMREfficiencySummary error: " . $e->getMessage());
            return "🔧 ШМР: — ошибка расчёта\n";
        }
    }
    
    /**
     * Calculate PNR efficiency summary for info page
     * Returns a formatted string with efficiency status
     */
    private function calculatePNREfficiencySummary(string $projectCode, $conn): string
    {
        try {
            // Get PNR milestones with plan
            $milestoneNames = $this->getPNRMilestoneNames();
            
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ПНР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $pnrMilestonesPlan = [];
            while ($row = $result->fetch_assoc()) {
                $pnrMilestonesPlan[$row['milestone_name']] = [
                    'planned_hours' => $row['planned_hours'],
                    'is_not_applicable' => $row['is_not_applicable'],
                    'unit' => $row['unit']
                ];
            }
            
            // Check if there's any PNR plan data
            $hasPlanData = false;
            foreach ($pnrMilestonesPlan as $data) {
                if (!$data['is_not_applicable'] && $data['planned_hours'] > 0) {
                    $hasPlanData = true;
                    break;
                }
            }
            
            // Get auxiliary plan from project_cards
            $stmt = $conn->prepare("SELECT pnr_mobilization, pnr_project_activity FROM project_cards WHERE project_code = ?");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            $pnrMobilizationPlan = ($cardData && isset($cardData['pnr_mobilization'])) ? (int)$cardData['pnr_mobilization'] : 0;
            $pnrProjectActivityPlan = ($cardData && isset($cardData['pnr_project_activity'])) ? (int)$cardData['pnr_project_activity'] : 0;
            $auxPlan = $pnrMobilizationPlan + $pnrProjectActivityPlan;
            
            // Get total fact from tabel
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT ot.id) as total_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                AND ot.message NOT LIKE '%Этап наладки завершен%'
                AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $totalFactFromTabel = $row['total_count'] ?? 0;
            
            // Calculate overall plan including auxiliary work
            $overallPlan = $auxPlan;
            
            // Return "нет данных" only if there's no plan AND no fact
            if (!$hasPlanData && $overallPlan == 0 && $totalFactFromTabel == 0) {
                return "⚙️ ПНР: — нет данных\n";
            }
            
            // If no milestone plan but has auxiliary plan or fact, show simple efficiency
            if (!$hasPlanData) {
                if ($totalFactFromTabel > 0 && $overallPlan > 0) {
                    $efficiency = round(($overallPlan / $totalFactFromTabel) * 100, 1);
                    $effIcon = $efficiency >= 100 ? '🟢' : ($efficiency >= 80 ? '🟡' : '🔴');
                    $delta = $totalFactFromTabel - $overallPlan;
                    $deltaStr = $delta > 0 ? "⬆️ {$delta}" : ($delta < 0 ? "⬇️ " . abs($delta) : "0");
                    return "⚙️ ПНР: {$effIcon} {$efficiency}% ({$deltaStr} дн.)\n   План: {$overallPlan} дн. | Факт: {$totalFactFromTabel} дн.\n";
                } elseif ($totalFactFromTabel > 0) {
                    return "⚙️ ПНР: ⚠️ План не установлен (Факт: {$totalFactFromTabel} дн.)\n";
                } else {
                    return "⚙️ ПНР: 📋 План {$overallPlan} дн.\n";
                }
            }
            
            // Milestones with standalone activities
            $standaloneActivityMilestones = ['ИИ на рабочей среде', 'Комплексное опробование'];
            
            // Get milestone facts and confirmation status
            $pnrMilestonesFact = [];
            $pnrMilestonesConfirmed = [];
            
            foreach ($milestoneNames as $name) {
                if (in_array($name, $standaloneActivityMilestones)) {
                    $stmt = $conn->prepare("
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND (ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                             OR ot.message LIKE CONCAT('%Формирование ИД%', ?, '%')
                             OR ot.message LIKE CONCAT('%', ?, '%'))
                        AND ot.message NOT LIKE '%Этап наладки завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ");
                    $stmt->bind_param('ssss', $projectCode, $name, $name, $name);
                } else {
                    $stmt = $conn->prepare("
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND (ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                             OR ot.message LIKE CONCAT('%Формирование ИД%', ?, '%'))
                        AND ot.message NOT LIKE '%Этап наладки завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ");
                    $stmt->bind_param('sss', $projectCode, $name, $name);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $pnrMilestonesFact[$name] = $row['days_count'] ?? 0;
                
                // Check confirmation
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as confirmed_count
                    FROM ozna_tabel ot
                    INNER JOIN ozna_data od ON ot.user_id = od.user_id
                    WHERE ot.message LIKE CONCAT('%', ?, '%')
                    AND ot.message LIKE '%Этап наладки завершен%'
                    AND ot.message LIKE CONCAT('%', ?, '%')
                    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         OR ot.message LIKE '%[Конструктор]%')
                ");
                $stmt->bind_param('ss', $projectCode, $name);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $pnrMilestonesConfirmed[$name] = ($row['confirmed_count'] ?? 0) > 0;
            }
            
            // Get milestone progress estimates
            $milestoneEstimates = [];
            $stmt = $conn->prepare("
                SELECT milestone_name, estimated_percent
                FROM milestone_progress_estimates
                WHERE project_code = ? AND work_type = 'ПНР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $milestoneEstimates[$row['milestone_name']] = $row;
            }
            
            // Get additional work plan
            $stmt = $conn->prepare("
                SELECT pnr_mobilization, pnr_project_activity
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $pnrMobilizationPlan = ($cardData && $cardData['pnr_mobilization']) ? $cardData['pnr_mobilization'] : 0;
            $pnrProjectActivityPlan = ($cardData && $cardData['pnr_project_activity']) ? $cardData['pnr_project_activity'] : 0;
            
            // Get additional work facts
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Дорога%' 
                         OR ot.message LIKE '%Инструктаж%' 
                         OR ot.message LIKE '%Подготовка к служебной поездке%'
                         OR ot.message LIKE '%Сдача авансового отчета%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as mobilization_pnr,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Разработка графика работ%'
                         OR ot.message LIKE '%Разработка программы работ%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as project_activity_pnr
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $pnrAdditional = $result->fetch_assoc();
            
            $mobFact = $pnrAdditional['mobilization_pnr'] ?? 0;
            $projActFact = $pnrAdditional['project_activity_pnr'] ?? 0;
            
            // Get total PNR fact and additional work for overall efficiency
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT CASE WHEN JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         AND ot.message NOT LIKE '%Этап наладки завершен%'
                         AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                         AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                         AND ot.message NOT LIKE '%Участие в ЗПИ%'
                         THEN ot.id END) as pnr_total,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as downtime_pnr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as rk_comments_pnr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as warranty_pnr
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('ssss', $projectCode, $projectCode, $projectCode, $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $pnrTotals = $result->fetch_assoc();
            
            $pnrTotalFactFromTabel = $pnrTotals['pnr_total'] ?? 0;
            $downtimeFact = $pnrTotals['downtime_pnr'] ?? 0;
            $rkCommentsFact = $pnrTotals['rk_comments_pnr'] ?? 0;
            $warrantyFact = $pnrTotals['warranty_pnr'] ?? 0;
            
            // Calculate totals
            $milestonesPlanTotal = 0;
            $milestonesFactTotal = 0;
            $milestonesEffectiveWork = 0;
            $forecastRemainingActualDays = 0;
            
            foreach ($milestoneNames as $name) {
                $planData = $pnrMilestonesPlan[$name] ?? null;
                $plan = 0;
                if ($planData && !$planData['is_not_applicable'] && $planData['planned_hours'] > 0) {
                    $plan = $planData['planned_hours'];
                    if ($planData['unit'] === 'hours') {
                        $plan = ceil($plan / 8);
                    }
                    $milestonesPlanTotal += $plan;
                }
                
                $fact = $pnrMilestonesFact[$name] ?? 0;
                $milestonesFactTotal += $fact;
                
                // Calculate effective work
                $estimate = $milestoneEstimates[$name] ?? null;
                $isConfirmed = $pnrMilestonesConfirmed[$name] ?? false;
                if ($plan > 0) {
                    if ($isConfirmed) {
                        $milestonesEffectiveWork += $plan;
                    } elseif ($fact > 0 && $estimate) {
                        $effectiveDone = ($estimate['estimated_percent'] * $plan) / 100;
                        $milestonesEffectiveWork += $effectiveDone;
                        $remainingPlan = $plan - $effectiveDone;
                        if ($effectiveDone > 0 && $remainingPlan > 0) {
                            $forecastRemainingActualDays += $remainingPlan / ($effectiveDone / $fact);
                        } elseif ($remainingPlan > 0) {
                            $forecastRemainingActualDays += $remainingPlan;
                        }
                    } elseif ($fact > 0) {
                        $effectiveDone = min($fact, $plan);
                        $milestonesEffectiveWork += $effectiveDone;
                        $remainingPlan = $plan - $effectiveDone;
                        if ($effectiveDone > 0 && $remainingPlan > 0) {
                            $forecastRemainingActualDays += $remainingPlan / ($effectiveDone / $fact);
                        } elseif ($remainingPlan > 0) {
                            $forecastRemainingActualDays += $remainingPlan;
                        }
                    } else {
                        $forecastRemainingActualDays += $plan;
                    }
                }
            }
            
            // Stage = milestones only (mob/projAct treated as auxiliary)
            $stagePlanTime = $milestonesPlanTotal;
            $stageFactTime = $milestonesFactTotal;
            $stageEffectiveWork = $milestonesEffectiveWork;
            
            // Overall plan includes auxiliary activities (mob + projAct)
            $overallPlanTime = $milestonesPlanTotal + $pnrMobilizationPlan + $pnrProjectActivityPlan;
            
            // Calculate additional/unaccounted work for overall efficiency
            $unaccountedWorksFact = $pnrTotalFactFromTabel - ($milestonesFactTotal + $mobFact + $projActFact + $downtimeFact + $rkCommentsFact + $warrantyFact);
            $unaccountedWorksFact = max(0, $unaccountedWorksFact);
            $auxiliaryFact = $mobFact + $projActFact;
            $additionalWorkFact = $downtimeFact + $warrantyFact + $rkCommentsFact + $unaccountedWorksFact;
            $totalFactTime = $stageFactTime + $auxiliaryFact + $additionalWorkFact;
            
            if ($stagePlanTime == 0) {
                return "⚙️ ПНР: — план не установлен\n";
            }
            
            $remainingPlanWork = max(0, $stagePlanTime - $stageEffectiveWork);
            
            // Stage completed
            if ($remainingPlanWork <= 0) {
                $result = "⚙️ ПНР: ✅ Завершен\n   Эффективность:";
                if ($totalFactTime > 0) {
                    $overallEff = round(($overallPlanTime / $totalFactTime) * 100, 1);
                    $overallIcon = $overallEff >= 100 ? '🟢' : ($overallEff >= 80 ? '🟡' : '🔴');
                    $result .= "\n   Общая: {$overallIcon} {$overallEff}%";
                }
                if ($stageFactTime > 0) {
                    $stageEff = round(($stagePlanTime / $stageFactTime) * 100, 1);
                    $stageIcon = $stageEff >= 100 ? '🟢' : ($stageEff >= 80 ? '🟡' : '🔴');
                    $result .= "\n   Этап: {$stageIcon} {$stageEff}%";
                }
                return $result . "\n";
            }
            
            // Stage in progress - show forecast
            $forecastText = "⚙️ ПНР: 🔄 В работе\n   Эффективность:";
            
            // Forecast overall efficiency (includes additional work)
            if ($totalFactTime > 0) {
                $factForecastOverall = $totalFactTime + $forecastRemainingActualDays;
                $forecastOverall = round(($overallPlanTime / $factForecastOverall) * 100, 1);
                $overallDelta = round($factForecastOverall - $overallPlanTime, 1);
                $overallDeltaStr = $overallDelta > 0 ? "⬆️ {$overallDelta}" : ($overallDelta < 0 ? "⬇️ " . abs($overallDelta) : "0");
                $overallIcon = $forecastOverall >= 100 ? '🟢' : ($forecastOverall >= 80 ? '🟡' : '🔴');
                $forecastText .= "\n   Общая: {$overallIcon} {$forecastOverall}% ({$overallDeltaStr} дн.)";
            }
            
            // Forecast stage efficiency
            if ($stageFactTime > 0) {
                $factForecastStage = $stageFactTime + $forecastRemainingActualDays;
                $forecastEfficiency = round(($stagePlanTime / $factForecastStage) * 100, 1);
                $forecastDelta = round($factForecastStage - $stagePlanTime, 1);
                $forecastDeltaStr = $forecastDelta > 0 ? "⬆️ {$forecastDelta}" : ($forecastDelta < 0 ? "⬇️ " . abs($forecastDelta) : "0");
                $colorIcon = $forecastEfficiency >= 100 ? '🟢' : ($forecastEfficiency >= 80 ? '🟡' : '🔴');
                
                $forecastText .= "\n   Этап: {$colorIcon} {$forecastEfficiency}% ({$forecastDeltaStr} дн.)";
            }
            
            // Get personnel count for completion date
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT JSON_EXTRACT(od.data, '$.fio')) as unique_specialists
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                AND ot.dt >= DATE_SUB(NOW(), INTERVAL 3 DAY)
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $personnelRow = $result->fetch_assoc();
            $personnelCount = $personnelRow['unique_specialists'] ?? 0;
            
            if ($personnelCount > 0) {
                $daysRemaining = ceil($forecastRemainingActualDays / $personnelCount);
                $completionDate = new \DateTime();
                $completionDate->modify("+{$daysRemaining} days");
                $completionDateStr = $completionDate->format('d.m.Y');
                $forecastText .= "\n   Прогноз завершения: ~{$completionDateStr}";
            }
            
            return $forecastText . "\n";
            
        } catch (\Exception $e) {
            error_log("calculatePNREfficiencySummary error: " . $e->getMessage());
            return "⚙️ ПНР: — ошибка расчёта\n";
        }
    }
    
    /**
     * Calculate CMR (СМР/Мобильная бригада) efficiency summary for info page
     * Returns a formatted string with efficiency status
     */
    private function calculateCMREfficiencySummary(string $projectCode, $conn): string
    {
        try {
            // Get CMR milestones with plan
            $milestoneNames = $this->getCMRMilestoneNames();
            
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'СМР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cmrMilestonesPlan = [];
            while ($row = $result->fetch_assoc()) {
                $cmrMilestonesPlan[$row['milestone_name']] = [
                    'planned_hours' => $row['planned_hours'],
                    'is_not_applicable' => $row['is_not_applicable'],
                    'unit' => $row['unit']
                ];
            }
            
            // Check if there's any CMR milestone plan data
            $hasMilestonePlan = false;
            foreach ($cmrMilestonesPlan as $data) {
                if (!$data['is_not_applicable'] && $data['planned_hours'] > 0) {
                    $hasMilestonePlan = true;
                    break;
                }
            }
            
            // Get auxiliary plan from project_cards
            $stmt = $conn->prepare("
                SELECT cmr_mobilization, cmr_project_activity, cmr_forecast_enabled
                FROM project_cards WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            $cmrMobilizationPlan = ($cardData && isset($cardData['cmr_mobilization'])) ? (int)$cardData['cmr_mobilization'] : 0;
            $cmrProjectActivityPlan = ($cardData && isset($cardData['cmr_project_activity'])) ? (int)$cardData['cmr_project_activity'] : 0;
            $auxPlan = $cmrMobilizationPlan + $cmrProjectActivityPlan;
            $forecastEnabled = ($cardData && isset($cardData['cmr_forecast_enabled'])) ? (bool)$cardData['cmr_forecast_enabled'] : true;
            
            // Get total fact from tabel (with exclusion filters for confirmations)
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT ot.id) as total_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                AND ot.message NOT LIKE '%Этап наладки завершен%'
                AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $totalFactFromTabel = $row['total_count'] ?? 0;
            
            // Calculate overall plan including auxiliary work
            $overallPlan = $auxPlan;
            
            // Return "нет данных" only if there's no plan AND no fact
            if (!$hasMilestonePlan && $overallPlan == 0 && $totalFactFromTabel == 0) {
                return "🏗️ СМР: — нет данных\n";
            }
            
            // If no milestone plan (for СМР) but has auxiliary plan or fact, show simple efficiency
            if (!$hasMilestonePlan) {
                if ($totalFactFromTabel > 0 && $overallPlan > 0) {
                    $efficiency = round(($overallPlan / $totalFactFromTabel) * 100, 1);
                    $effIcon = $efficiency >= 100 ? '🟢' : ($efficiency >= 80 ? '🟡' : '🔴');
                    $delta = $totalFactFromTabel - $overallPlan;
                    $deltaStr = $delta > 0 ? "⬆️ {$delta}" : ($delta < 0 ? "⬇️ " . abs($delta) : "0");
                    return "🏗️ СМР: {$effIcon} {$efficiency}% ({$deltaStr} дн.)\n   План: {$overallPlan} дн. | Факт: {$totalFactFromTabel} дн.\n";
                } elseif ($totalFactFromTabel > 0) {
                    return "🏗️ СМР: ⚠️ План не установлен (Факт: {$totalFactFromTabel} дн.)\n";
                } else {
                    return "🏗️ СМР: 📋 План {$overallPlan} дн.\n";
                }
            }
            
            // Calculate total plan (milestones only, excluding mobilization)
            $stagePlanTime = 0;
            foreach ($cmrMilestonesPlan as $name => $data) {
                if (!$data['is_not_applicable'] && $data['planned_hours'] !== null) {
                    if ($data['unit'] === 'hours') {
                        $stagePlanTime += ceil($data['planned_hours'] / 8);
                    } else {
                        $stagePlanTime += $data['planned_hours'];
                    }
                }
            }
            
            // Get mobilization and project activity plan from project_cards
            $stmt = $conn->prepare("
                SELECT cmr_mobilization, cmr_project_activity
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $mobilizationPlan = ($cardData && $cardData['cmr_mobilization']) ? $cardData['cmr_mobilization'] : 0;
            $projectActivityPlan = ($cardData && $cardData['cmr_project_activity']) ? $cardData['cmr_project_activity'] : 0;
            
            // Overall plan = stage + auxiliary
            $overallPlanTime = $stagePlanTime + $mobilizationPlan + $projectActivityPlan;
            
            // Get CMR milestone facts from tabel
            $milestoneStructure = $this->getCMRMilestoneStructure();
            $stageFactTime = 0;
            
            foreach ($milestoneNames as $topMilestoneName) {
                $subMilestones = $milestoneStructure[$topMilestoneName] ?? [];
                
                foreach ($subMilestones as $subMilestoneName => $activities) {
                    $activityConditions = [];
                    foreach ($activities as $activity) {
                        $activityConditions[] = "ot.message LIKE '%" . $conn->real_escape_string($activity) . "%'";
                    }
                    $activityFilter = '(' . implode(' OR ', $activityConditions) . ')';
                    
                    $sql = "
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND {$activityFilter}
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('s', $projectCode);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $stageFactTime += $row['days_count'] ?? 0;
                }
            }
            
            // Get mobilization and project activity facts
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Дорога%' 
                         OR ot.message LIKE '%Инструктаж%' 
                         OR ot.message LIKE '%Подготовка к служебной поездке%'
                         OR ot.message LIKE '%Сдача авансового отчета%')
                         AND ot.message LIKE CONCAT('%', ?, '%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                         THEN ot.id END) as mobilization_cmr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE '%Проектная деятельность%'
                         AND ot.message LIKE CONCAT('%', ?, '%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Мобильная бригада'
                         THEN ot.id END) as project_activity_cmr
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('sss', $projectCode, $projectCode, $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $auxRow = $result->fetch_assoc();
            
            $mobilizationFact = $auxRow['mobilization_cmr'] ?? 0;
            $projectActivityFact = $auxRow['project_activity_cmr'] ?? 0;
            
            // Total fact = stage + auxiliary
            $totalFactTime = $stageFactTime + $mobilizationFact + $projectActivityFact;
            
            // No work done yet
            if ($totalFactTime == 0) {
                return "🏗️ СМР: 📋 План {$overallPlanTime} дн.\n";
            }
            
            // Calculate current efficiency
            $stageEff = $stageFactTime > 0 ? round(($stagePlanTime / $stageFactTime) * 100, 1) : 0;
            $overallEff = $totalFactTime > 0 ? round(($overallPlanTime / $totalFactTime) * 100, 1) : 0;
            
            // СМР forecast based on related ШМР milestones progress (Досборка)
            if ($forecastEnabled) {
                $cmrForecast = $this->calculateOverallCMRDosborkForecast($projectCode, $conn);
                
                $forecastText = "🏗️ СМР: 🔄 В работе\n   Эффективность:";
                
                // Calculate forecast using ШМР milestone progress
                if ($cmrForecast && $cmrForecast['overallProgress'] > 0) {
                    // Progress from ШМР milestones
                    $smrProgress = $cmrForecast['overallProgress'];
                    
                    if ($smrProgress >= 100) {
                        $forecastStageFact = $stageFactTime;
                        $forecastTotalFact = $totalFactTime;
                    } else {
                        $forecastStageFact = $smrProgress > 0 ? round($stageFactTime / ($smrProgress / 100), 1) : $stageFactTime;
                        $forecastTotalFact = $smrProgress > 0 ? round($totalFactTime / ($smrProgress / 100), 1) : $totalFactTime;
                    }
                    
                    // Forecast overall efficiency
                    if ($forecastTotalFact > 0) {
                        $forecastOverall = round(($overallPlanTime / $forecastTotalFact) * 100, 1);
                        $overallDelta = round($forecastTotalFact - $overallPlanTime, 1);
                        $overallDeltaStr = $overallDelta > 0 ? "⬆️ {$overallDelta}" : ($overallDelta < 0 ? "⬇️ " . abs($overallDelta) : "0");
                        $overallIcon = $forecastOverall >= 100 ? '🟢' : ($forecastOverall >= 80 ? '🟡' : '🔴');
                        $forecastText .= "\n   Общая: {$overallIcon} {$forecastOverall}% ({$overallDeltaStr} дн.)";
                    }
                    
                    // Forecast stage efficiency
                    if ($forecastStageFact > 0) {
                        $forecastEfficiency = round(($stagePlanTime / $forecastStageFact) * 100, 1);
                        $forecastDelta = round($forecastStageFact - $stagePlanTime, 1);
                        $forecastDeltaStr = $forecastDelta > 0 ? "⬆️ {$forecastDelta}" : ($forecastDelta < 0 ? "⬇️ " . abs($forecastDelta) : "0");
                        $colorIcon = $forecastEfficiency >= 100 ? '🟢' : ($forecastEfficiency >= 80 ? '🟡' : '🔴');
                        $forecastText .= "\n   Этап: {$colorIcon} {$forecastEfficiency}% ({$forecastDeltaStr} дн.)";
                    }
                } else {
                    // Fallback: old logic using current efficiency trend
                    $remainingPlan = max(0, $stagePlanTime - $stageFactTime);
                    $currentStageEfficiency = $stageFactTime > 0 ? $stagePlanTime / $stageFactTime : 1;
                    $forecastRemainingActualDays = $currentStageEfficiency > 0 ? $remainingPlan / $currentStageEfficiency : $remainingPlan;
                    
                    // Forecast overall efficiency
                    if ($totalFactTime > 0) {
                        $factForecastOverall = $totalFactTime + $forecastRemainingActualDays;
                        $forecastOverall = round(($overallPlanTime / $factForecastOverall) * 100, 1);
                        $overallDelta = round($factForecastOverall - $overallPlanTime, 1);
                        $overallDeltaStr = $overallDelta > 0 ? "⬆️ {$overallDelta}" : ($overallDelta < 0 ? "⬇️ " . abs($overallDelta) : "0");
                        $overallIcon = $forecastOverall >= 100 ? '🟢' : ($forecastOverall >= 80 ? '🟡' : '🔴');
                        $forecastText .= "\n   Общая: {$overallIcon} {$forecastOverall}% ({$overallDeltaStr} дн.)";
                    }
                    
                    // Forecast stage efficiency
                    if ($stageFactTime > 0) {
                        $factForecastStage = $stageFactTime + $forecastRemainingActualDays;
                        $forecastEfficiency = round(($stagePlanTime / $factForecastStage) * 100, 1);
                        $forecastDelta = round($factForecastStage - $stagePlanTime, 1);
                        $forecastDeltaStr = $forecastDelta > 0 ? "⬆️ {$forecastDelta}" : ($forecastDelta < 0 ? "⬇️ " . abs($forecastDelta) : "0");
                        $colorIcon = $forecastEfficiency >= 100 ? '🟢' : ($forecastEfficiency >= 80 ? '🟡' : '🔴');
                        $forecastText .= "\n   Этап: {$colorIcon} {$forecastEfficiency}% ({$forecastDeltaStr} дн.)";
                    }
                }
                
                return $forecastText . "\n";
            } else {
                // Forecast disabled: show only current plan/fact efficiency
                $currentText = "🏗️ СМР: 🔄 В работе\n   Эффективность:";
                
                // Current overall efficiency
                if ($totalFactTime > 0 && $overallPlanTime > 0) {
                    $overallDelta = round($totalFactTime - $overallPlanTime, 1);
                    $overallDeltaStr = $overallDelta > 0 ? "⬆️ {$overallDelta}" : ($overallDelta < 0 ? "⬇️ " . abs($overallDelta) : "0");
                    $overallIcon = $overallEff >= 100 ? '🟢' : ($overallEff >= 80 ? '🟡' : '🔴');
                    $currentText .= "\n   Общая: {$overallIcon} {$overallEff}% ({$overallDeltaStr} дн.)";
                }
                
                // Current stage efficiency
                if ($stageFactTime > 0 && $stagePlanTime > 0) {
                    $stageDelta = round($stageFactTime - $stagePlanTime, 1);
                    $stageDeltaStr = $stageDelta > 0 ? "⬆️ {$stageDelta}" : ($stageDelta < 0 ? "⬇️ " . abs($stageDelta) : "0");
                    $stageIcon = $stageEff >= 100 ? '🟢' : ($stageEff >= 80 ? '🟡' : '🔴');
                    $currentText .= "\n   Этап: {$stageIcon} {$stageEff}% ({$stageDeltaStr} дн.)";
                }
                
                return $currentText . "\n";
            }
            
        } catch (\Exception $e) {
            error_log("calculateCMREfficiencySummary error: " . $e->getMessage());
            return "🏗️ СМР: — ошибка расчёта\n";
        }
    }
    
    /**
     * Calculate combined efficiency across all work types (ШМР, СМР, ПНР)
     * Returns formatted string with current and forecast efficiency
     */
    private function calculateCombinedEfficiency(string $projectCode, $conn): ?string
    {
        try {
            $totalPlan = 0;
            $totalFact = 0;
            $totalForecastFact = 0;
            $hasAnyData = false;
            
            // ===== ШМР (Шеф-монтаж) =====
            $smrData = $this->getWorkTypeDetailedData($projectCode, $conn, 'ШМР');
            if ($smrData) {
                $totalPlan += $smrData['overallPlan'];
                $totalFact += $smrData['totalFact'];
                $totalForecastFact += $smrData['forecastFact'];
                $hasAnyData = true;
            }
            
            // ===== СМР (Мобильная бригада) =====
            $cmrData = $this->getWorkTypeDetailedData($projectCode, $conn, 'СМР');
            if ($cmrData) {
                $totalPlan += $cmrData['overallPlan'];
                $totalFact += $cmrData['totalFact'];
                $totalForecastFact += $cmrData['forecastFact'];
                $hasAnyData = true;
            }
            
            // ===== ПНР =====
            $pnrData = $this->getWorkTypeDetailedData($projectCode, $conn, 'ПНР');
            if ($pnrData) {
                $totalPlan += $pnrData['overallPlan'];
                $totalFact += $pnrData['totalFact'];
                $totalForecastFact += $pnrData['forecastFact'];
                $hasAnyData = true;
            }
            
            if (!$hasAnyData || $totalPlan == 0) {
                return null;
            }
            
            $result = "";
            
            // Total plan/fact display
            $totalPlanInt = round($totalPlan);
            $totalFactInt = round($totalFact);
            $result .= "   План: {$totalPlanInt} дн. | Факт: {$totalFactInt} дн.\n";
            
            // Current efficiency
            if ($totalFact > 0) {
                $currentEff = round(($totalPlan / $totalFact) * 100, 1);
                $currentIcon = $currentEff >= 100 ? '🟢' : ($currentEff >= 80 ? '🟡' : '🔴');
                $result .= "   Текущая: {$currentIcon} {$currentEff}%\n";
            }
            
            // Forecast efficiency
            if ($totalForecastFact > 0) {
                $forecastEff = round(($totalPlan / $totalForecastFact) * 100, 1);
                $forecastIcon = $forecastEff >= 100 ? '🟢' : ($forecastEff >= 80 ? '🟡' : '🔴');
                $forecastDelta = round($totalForecastFact - $totalPlan, 1);
                $forecastDeltaStr = $forecastDelta > 0 ? "⬆️ {$forecastDelta}" : ($forecastDelta < 0 ? "⬇️ " . abs($forecastDelta) : "0");
                $result .= "   Прогнозная: {$forecastIcon} {$forecastEff}% ({$forecastDeltaStr} дн.)\n";
            }
            
            return $result ?: null;
            
        } catch (\Exception $e) {
            error_log("calculateCombinedEfficiency error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get detailed efficiency data for a work type (ШМР, СМР, ПНР)
     * Calculates values matching the individual summary methods
     */
    private function getWorkTypeDetailedData(string $projectCode, $conn, string $workType): ?array
    {
        try {
            // For ШМР, get valid sub-milestone names to filter orphaned records
            $validSubMilestoneNames = [];
            if ($workType === 'ШМР') {
                $milestoneStructure = $this->getSMRMilestoneStructure();
                foreach ($milestoneStructure as $topName => $subs) {
                    foreach ($subs as $subName => $activities) {
                        $validSubMilestoneNames[] = $subName;
                    }
                }
            }
            
            // Get plan from project_milestones
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = ?
            ");
            $stmt->bind_param('ss', $projectCode, $workType);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $stagePlan = 0;
            while ($row = $result->fetch_assoc()) {
                // For ШМР, only count valid sub-milestones (ignore orphaned records)
                if ($workType === 'ШМР' && !in_array($row['milestone_name'], $validSubMilestoneNames)) {
                    continue;
                }
                if (!$row['is_not_applicable'] && $row['planned_hours'] > 0) {
                    if ($row['unit'] === 'hours') {
                        $stagePlan += ceil($row['planned_hours'] / 8);
                    } else {
                        $stagePlan += $row['planned_hours'];
                    }
                }
            }
            
            // Get auxiliary plan from project_cards based on work type
            $auxPlanColumns = match($workType) {
                'ШМР' => ['smr_mobilization', 'smr_project_activity', 'smr_master_duties'],
                'СМР' => ['cmr_mobilization', 'cmr_project_activity'],
                'ПНР' => ['pnr_mobilization', 'pnr_project_activity'],
                default => []
            };
            
            $auxPlan = 0;
            if (!empty($auxPlanColumns)) {
                $auxSelect = implode(', ', $auxPlanColumns);
                $stmt = $conn->prepare("SELECT {$auxSelect} FROM project_cards WHERE project_code = ?");
                $stmt->bind_param('s', $projectCode);
                $stmt->execute();
                $result = $stmt->get_result();
                $cardData = $result->fetch_assoc();
                if ($cardData) {
                    foreach ($auxPlanColumns as $col) {
                        $auxPlan += $cardData[$col] ?? 0;
                    }
                }
            }
            
            $overallPlan = $stagePlan + $auxPlan;
            
            // Get total fact from tabel by functional_direction
            $functionalDirection = match($workType) {
                'ШМР' => 'Шеф-монтаж',
                'СМР' => 'Мобильная бригада',
                'ПНР' => 'Пусконаладка',
                default => null
            };
            
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT ot.id) as total_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = ?
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                AND ot.message NOT LIKE '%Этап наладки завершен%'
                AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
            ");
            $stmt->bind_param('ss', $projectCode, $functionalDirection);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $totalFact = $row['total_count'] ?? 0;
            
            // Return null only if there's no plan AND no fact
            if ($overallPlan == 0 && $totalFact == 0) {
                return null;
            }
            
            // Calculate forecast fact
            // For ШМР and ПНР: use remaining work / current efficiency
            // For СМР: use SMR milestone progress to calculate forecast
            $forecastFact = $totalFact;
            
            if ($workType === 'СМР' && $totalFact > 0) {
                // Check if forecast is enabled for this project
                $forecastEnabledForCMR = true;
                $stmtFe = $conn->prepare("SELECT cmr_forecast_enabled FROM project_cards WHERE project_code = ?");
                $stmtFe->bind_param('s', $projectCode);
                $stmtFe->execute();
                $resultFe = $stmtFe->get_result();
                $rowFe = $resultFe->fetch_assoc();
                if ($rowFe && isset($rowFe['cmr_forecast_enabled'])) {
                    $forecastEnabledForCMR = (bool)$rowFe['cmr_forecast_enabled'];
                }
                
                if ($forecastEnabledForCMR) {
                    // СМР forecast based on related ШМР milestones progress
                    $cmrForecast = $this->calculateOverallCMRDosborkForecast($projectCode, $conn);
                    if ($cmrForecast && $cmrForecast['overallProgress'] > 0) {
                        $smrProgress = $cmrForecast['overallProgress'];
                        if ($smrProgress >= 100) {
                            // SMR completed - CMR forecast = current fact
                            $forecastFact = $totalFact;
                        } else {
                            // Calculate forecast: fact / (progress / 100)
                            $forecastFact = round($totalFact / ($smrProgress / 100), 1);
                        }
                    }
                }
                // When forecast disabled: $forecastFact stays = $totalFact (current fact)
            } elseif (($workType === 'ШМР' || $workType === 'ПНР') && $stagePlan > 0) {
                // Get milestone-level facts and calculate remaining work
                $milestoneFact = 0;
                $milestoneNames = match($workType) {
                    'ШМР' => $this->getSMRMilestoneNames(),
                    'ПНР' => $this->getPNRMilestoneNames(),
                    default => []
                };
                $milestoneStructure = match($workType) {
                    'ШМР' => $this->getSMRMilestoneStructure(),
                    'ПНР' => [], // PNR has flat structure
                    default => []
                };
                
                if ($workType === 'ШМР') {
                    // SMR has sub-milestones
                    foreach ($milestoneNames as $topName) {
                        $subs = $milestoneStructure[$topName] ?? [];
                        foreach ($subs as $subName => $activities) {
                            $activityConditions = [];
                            foreach ($activities as $activity) {
                                $activityConditions[] = "ot.message LIKE '%" . $conn->real_escape_string($activity) . "%'";
                            }
                            $activityFilter = '(' . implode(' OR ', $activityConditions) . ')';
                            
                            $sql = "
                                SELECT COUNT(DISTINCT ot.id) as days_count
                                FROM ozna_tabel ot
                                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                                WHERE ot.message LIKE CONCAT('%', ?, '%')
                                AND {$activityFilter}
                                AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Шеф-монтаж'
                            ";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('s', $projectCode);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            $milestoneFact += $row['days_count'] ?? 0;
                        }
                    }
                } else {
                    // PNR has flat milestone structure
                    foreach ($milestoneNames as $milestoneName) {
                        $sql = "
                            SELECT COUNT(DISTINCT ot.id) as days_count
                            FROM ozna_tabel ot
                            INNER JOIN ozna_data od ON ot.user_id = od.user_id
                            WHERE ot.message LIKE CONCAT('%', ?, '%')
                            AND ot.message LIKE CONCAT('%', ?, '%')
                            AND ot.message NOT LIKE '%Этап наладки завершен%'
                            AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        ";
                        $stmt = $conn->prepare($sql);
                        $escapedMilestone = $conn->real_escape_string($milestoneName);
                        $stmt->bind_param('ss', $projectCode, $escapedMilestone);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        $milestoneFact += $row['days_count'] ?? 0;
                    }
                }
                
                // Calculate remaining plan and forecast
                $remainingPlan = max(0, $stagePlan - $milestoneFact);
                if ($milestoneFact > 0 && $remainingPlan > 0) {
                    $currentEfficiency = $stagePlan / $milestoneFact;
                    $forecastRemaining = $currentEfficiency > 0 ? $remainingPlan / $currentEfficiency : $remainingPlan;
                    $forecastFact = $totalFact + $forecastRemaining;
                } elseif ($remainingPlan <= 0) {
                    // Stage complete - forecast = current
                    $forecastFact = $totalFact;
                } else {
                    // No work done yet
                    $forecastFact = $totalFact + $remainingPlan;
                }
            }
            
            return [
                'overallPlan' => $overallPlan,
                'stagePlan' => $stagePlan,
                'totalFact' => $totalFact,
                'forecastFact' => $forecastFact
            ];
            
        } catch (\Exception $e) {
            error_log("getWorkTypeDetailedData error for {$workType}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Show ПНР page of project card
     */
    private function showProjectCardPNRPage(string $chatId, int $userId, string $projectCode, array $project, $conn): ServerResponse
    {
        try {
            // Get PNR milestones with plan from project_milestones
            $milestoneNames = $this->getPNRMilestoneNames();
            
            $stmt = $conn->prepare("
                SELECT milestone_name, planned_hours, is_not_applicable, unit
                FROM project_milestones
                WHERE project_code = ? AND work_type = 'ПНР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $pnrMilestonesPlan = [];
            while ($row = $result->fetch_assoc()) {
                $pnrMilestonesPlan[$row['milestone_name']] = [
                    'planned_hours' => $row['planned_hours'],
                    'is_not_applicable' => $row['is_not_applicable'],
                    'unit' => $row['unit']
                ];
            }
            
            // Get PNR milestone fact days from tabel (excluding testers and confirmations)
            $pnrMilestonesFact = [];
            $pnrMilestonesConfirmed = [];
            
            // Milestones that are recorded as standalone activities (without "Работы по графику" wrapper)
            $standaloneActivityMilestones = ['ИИ на рабочей среде', 'Комплексное опробование'];
            
            foreach ($milestoneNames as $name) {
                // For "ИИ на рабочей среде" and "Комплексное опробование" — also match standalone activity name
                if (in_array($name, $standaloneActivityMilestones)) {
                    $stmt = $conn->prepare("
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND (ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                             OR ot.message LIKE CONCAT('%Формирование ИД%', ?, '%')
                             OR ot.message LIKE CONCAT('%', ?, '%'))
                        AND ot.message NOT LIKE '%Этап наладки завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ");
                    $stmt->bind_param('ssss', $projectCode, $name, $name, $name);
                } else {
                    // Standard milestones — match "Работы по графику" and "Формирование ИД"
                    $stmt = $conn->prepare("
                        SELECT COUNT(DISTINCT ot.id) as days_count
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND (ot.message LIKE CONCAT('%Работы по графику%', ?, '%')
                             OR ot.message LIKE CONCAT('%Формирование ИД%', ?, '%'))
                        AND ot.message NOT LIKE '%Этап наладки завершен%'
                        AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                    ");
                    $stmt->bind_param('sss', $projectCode, $name, $name);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $pnrMilestonesFact[$name] = $row['days_count'] ?? 0;
                
                // Check if milestone is confirmed
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as confirmed_count
                    FROM ozna_tabel ot
                    INNER JOIN ozna_data od ON ot.user_id = od.user_id
                    WHERE ot.message LIKE CONCAT('%', ?, '%')
                    AND ot.message LIKE '%Этап наладки завершен%'
                    AND ot.message LIKE CONCAT('%', ?, '%')
                    AND (JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         OR ot.message LIKE '%[Конструктор]%')
                ");
                $stmt->bind_param('ss', $projectCode, $name);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $pnrMilestonesConfirmed[$name] = ($row['confirmed_count'] ?? 0) > 0;
            }
            
            // Get PNR additional work counts from tabel
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Простой%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as downtime_pnr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Устранени%', '%замечаний РК%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as rk_comments_pnr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Работы по гарантии%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as warranty_pnr,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Дорога%' 
                         OR ot.message LIKE '%Инструктаж%' 
                         OR ot.message LIKE '%Подготовка к служебной поездке%'
                         OR ot.message LIKE '%Сдача авансового отчета%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as mobilization_pnr,
                    COUNT(DISTINCT CASE WHEN (ot.message LIKE '%Разработка графика работ%'
                         OR ot.message LIKE '%Разработка программы работ%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as project_activity_pnr,
                    COUNT(DISTINCT CASE WHEN ot.message LIKE CONCAT('%', ?, '%Участие в ЗПИ%')
                         AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                         THEN ot.id END) as zpi_pnr
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
            ");
            $stmt->bind_param('sssss', $projectCode, $projectCode, $projectCode, $projectCode, $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $pnrAdditional = $result->fetch_assoc();
            
            // Get TOTAL PNR fact count (excluding confirmations and ZPI - ZPI is informational only)
            $stmt = $conn->prepare("
                SELECT COUNT(DISTINCT ot.id) as pnr_total_count
                FROM ozna_tabel ot
                INNER JOIN ozna_data od ON ot.user_id = od.user_id
                WHERE ot.message LIKE CONCAT('%', ?, '%')
                AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                AND JSON_EXTRACT(od.data, '$.functional_direction') != 'Тестировщик'
                AND ot.message NOT LIKE '%Этап наладки завершен%'
                AND ot.message NOT LIKE '%Этап шеф-монтажа завершен%'
                AND ot.message NOT LIKE '%Подтвердить достижение вехи%'
                AND ot.message NOT LIKE '%Участие в ЗПИ%'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $pnrTotalRow = $result->fetch_assoc();
            $pnrTotalFactFromTabel = $pnrTotalRow['pnr_total_count'] ?? 0;
            
            // Get milestone progress estimates from survey data
            $milestoneEstimates = [];
            $stmt = $conn->prepare("
                SELECT milestone_name, estimated_percent, efficiency_at_estimate, 
                       estimator_fio, estimated_at
                FROM milestone_progress_estimates
                WHERE project_code = ? AND work_type = 'ПНР'
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $milestoneEstimates[$row['milestone_name']] = $row;
            }
            
            // Calculate total plan
            $pnrTotalPlan = 0;
            foreach ($pnrMilestonesPlan as $name => $data) {
                if (!$data['is_not_applicable'] && $data['planned_hours'] !== null) {
                    if ($data['unit'] === 'hours') {
                        $pnrTotalPlan += ceil($data['planned_hours'] / 8);
                    } else {
                        $pnrTotalPlan += $data['planned_hours'];
                    }
                }
            }
            
            // Get additional work plan from project_cards
            $stmt = $conn->prepare("
                SELECT pnr_mobilization, pnr_project_activity
                FROM project_cards
                WHERE project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $pnrMobilizationPlan = ($cardData && $cardData['pnr_mobilization']) ? $cardData['pnr_mobilization'] : 0;
            $pnrProjectActivityPlan = ($cardData && $cardData['pnr_project_activity']) ? $cardData['pnr_project_activity'] : 0;
            $pnrTotalPlan += $pnrMobilizationPlan + $pnrProjectActivityPlan;
            
            // Get additional work facts
            $mobFact = $pnrAdditional['mobilization_pnr'] ?? 0;
            $projActFact = $pnrAdditional['project_activity_pnr'] ?? 0;
            $downtimeFact = $pnrAdditional['downtime_pnr'] ?? 0;
            $warrantyFact = $pnrAdditional['warranty_pnr'] ?? 0;
            $rkCommentsFact = $pnrAdditional['rk_comments_pnr'] ?? 0;
            
            // Calculate milestone totals
            $milestonesPlanTotal = 0;
            $milestonesFactTotal = 0;
            $milestonesEffectiveWork = 0;
            $forecastRemainingActualDays = 0; // predicted actual days to complete remaining work
            
            foreach ($milestoneNames as $name) {
                $planData = $pnrMilestonesPlan[$name] ?? null;
                $plan = 0;
                if ($planData && !$planData['is_not_applicable'] && $planData['planned_hours'] > 0) {
                    $plan = $planData['planned_hours'];
                    if ($planData['unit'] === 'hours') {
                        $plan = ceil($plan / 8);
                    }
                    $milestonesPlanTotal += $plan;
                }
                
                $fact = $pnrMilestonesFact[$name] ?? 0;
                $milestonesFactTotal += $fact;
                
                // Calculate effective work COMPLETED (only actual progress, not assumptions)
                $estimate = $milestoneEstimates[$name] ?? null;
                $isConfirmed = $pnrMilestonesConfirmed[$name] ?? false;
                if ($plan > 0) {
                    if ($isConfirmed) {
                        // Milestone confirmed = 100% completion, no remaining work
                        $milestonesEffectiveWork += $plan;
                        // remaining = 0, no forecast days needed
                    } elseif ($fact > 0 && $estimate) {
                        // Milestone has work done - use survey estimate
                        $effectiveDone = ($estimate['estimated_percent'] * $plan) / 100;
                        $milestonesEffectiveWork += $effectiveDone;
                        // Forecast remaining using current milestone efficiency
                        $remainingPlan = $plan - $effectiveDone;
                        if ($effectiveDone > 0 && $remainingPlan > 0) {
                            $milestoneEfficiency = $effectiveDone / $fact;
                            $forecastRemainingActualDays += $remainingPlan / $milestoneEfficiency;
                        } elseif ($remainingPlan > 0) {
                            $forecastRemainingActualDays += $remainingPlan;
                        }
                    } elseif ($fact > 0) {
                        // Has fact but no estimate - use min(fact, plan) as conservative estimate
                        $effectiveDone = min($fact, $plan);
                        $milestonesEffectiveWork += $effectiveDone;
                        $remainingPlan = $plan - $effectiveDone;
                        if ($effectiveDone > 0 && $remainingPlan > 0) {
                            $milestoneEfficiency = $effectiveDone / $fact;
                            $forecastRemainingActualDays += $remainingPlan / $milestoneEfficiency;
                        } elseif ($remainingPlan > 0) {
                            $forecastRemainingActualDays += $remainingPlan;
                        }
                    } else {
                        // Not started - assume 100% efficiency for forecast
                        $forecastRemainingActualDays += $plan;
                    }
                    // If fact == 0, milestone not started - contributes 0 to effective work completed
                }
            }
            
            // Stage = milestones only (mob/projAct treated as auxiliary, like additional work)
            $stagePlanTime = $milestonesPlanTotal;
            $stageFactTime = $milestonesFactTotal;
            $stageEffectiveWork = $milestonesEffectiveWork;
            
            // Overall plan includes auxiliary activities (mob + projAct)
            $overallPlanTime = $milestonesPlanTotal + $pnrMobilizationPlan + $pnrProjectActivityPlan;
            
            // Calculate unaccounted work (everything not categorized)
            $unaccountedWorksFact = $pnrTotalFactFromTabel - ($milestonesFactTotal + $mobFact + $projActFact + $downtimeFact + $rkCommentsFact + $warrantyFact);
            $unaccountedWorksFact = max(0, $unaccountedWorksFact); // Ensure non-negative
            
            // Auxiliary fact (mob + projAct) + additional work — not part of stage
            $auxiliaryFact = $mobFact + $projActFact;
            $additionalWorkFact = $downtimeFact + $warrantyFact + $rkCommentsFact + $unaccountedWorksFact;
            
            // Total time = stage + auxiliary + additional work
            $totalFactTime = $stageFactTime + $auxiliaryFact + $additionalWorkFact;
            
            // Build message
            $text = "⚙️ <b>ПНР (Пуско-наладочные работы)</b>\n\n";
            $text .= "<b>Шифр:</b> " . $project['project_code'] . "\n";
            $text .= "<b>Наименование:</b> " . $project['project_name'] . "\n\n";
            
            // Overall metrics
            $text .= "📊 <b>План:</b> " . $pnrTotalPlan . " дн. | <b>Факт:</b> " . $pnrTotalFactFromTabel . " дн.\n";
            if ($stagePlanTime > 0 && $stageEffectiveWork >= $stagePlanTime) {
                $text .= "✅ Этап завершен\n";
            }
            $text .= "\n";
            
            // ============ CURRENT EFFICIENCY (resource usage = fact/plan) ============
            $text .= "<b>📈 Текущая эффективность:</b>\n";
            
            // Current overall efficiency
            if ($totalFactTime > 0 && $overallPlanTime > 0) {
                $currentOverall = round(($overallPlanTime / $totalFactTime) * 100, 1);
                $overallDelta = $totalFactTime - $overallPlanTime;
                $overallDeltaSign = $overallDelta > 0 ? '⬆️' : '⬇️';
                $overallDeltaAbs = abs($overallDelta);
                
                if ($currentOverall >= 100) {
                    $text .= "🟢 Общая: {$currentOverall}% ({$overallDeltaSign} {$overallDeltaAbs} дн.)\n";
                } elseif ($currentOverall >= 80) {
                    $text .= "🟡 Общая: {$currentOverall}% ({$overallDeltaSign} {$overallDeltaAbs} дн.)\n";
                } else {
                    $text .= "🔴 Общая: {$currentOverall}% ({$overallDeltaSign} {$overallDeltaAbs} дн.)\n";
                }
            }
            
            // Current stage efficiency
            if ($stageFactTime > 0 && $stagePlanTime > 0) {
                $currentStage = round(($stagePlanTime / $stageFactTime) * 100, 1);
                $stageDelta = $stageFactTime - $stagePlanTime;
                $stageDeltaSign = $stageDelta > 0 ? '⬆️' : '⬇️';
                $stageDeltaAbs = abs($stageDelta);
                
                if ($currentStage >= 100) {
                    $text .= "🟢 Этап: {$currentStage}% ({$stageDeltaSign} {$stageDeltaAbs} дн.)\n";
                } elseif ($currentStage >= 80) {
                    $text .= "🟡 Этап: {$currentStage}% ({$stageDeltaSign} {$stageDeltaAbs} дн.)\n";
                } else {
                    $text .= "🔴 Этап: {$currentStage}% ({$stageDeltaSign} {$stageDeltaAbs} дн.)\n";
                }
            } elseif ($stagePlanTime == 0) {
                $text .= "⚠️ План не установлен\n";
            }
            
            // ============ FORECAST EFFICIENCY (with completion %) ============
            // Forecast = plan / (current_fact + forecast_remaining_actual_days)
            // Uses current per-milestone efficiency to project remaining work
            // Only show forecasts if stage is NOT yet completed
            if ($stagePlanTime > 0) {
                $remainingPlanWork = $stagePlanTime - $stageEffectiveWork;
                $remainingPlanWork = max(0, $remainingPlanWork);
                
                if ($remainingPlanWork > 0) {
                    $text .= "\n<b>🔮 Прогноз эффективности:</b>\n";
                    
                    // Forecast overall efficiency
                    if ($totalFactTime > 0) {
                        $factForecastOverall = $totalFactTime + $forecastRemainingActualDays;
                        $forecastOverall = round(($overallPlanTime / $factForecastOverall) * 100, 1);
                        $overallForecastDelta = round($factForecastOverall - $overallPlanTime, 1);
                        $overallForecastDeltaStr = $overallForecastDelta > 0 ? "⬆️ {$overallForecastDelta}" : ($overallForecastDelta < 0 ? "⬇️ " . abs($overallForecastDelta) : "0");
                        
                        if ($forecastOverall >= 100) {
                            $text .= "🟢 Общая: {$forecastOverall}% ({$overallForecastDeltaStr} дн.)\n";
                        } elseif ($forecastOverall >= 80) {
                            $text .= "🟡 Общая: {$forecastOverall}% ({$overallForecastDeltaStr} дн.)\n";
                        } else {
                            $text .= "🔴 Общая: {$forecastOverall}% ({$overallForecastDeltaStr} дн.)\n";
                        }
                    }
                    
                    // Forecast stage efficiency
                    if ($stageFactTime > 0) {
                        $factForecastStage = $stageFactTime + $forecastRemainingActualDays;
                        $forecastStage = round(($stagePlanTime / $factForecastStage) * 100, 1);
                        $stageForecastDelta = round($factForecastStage - $stagePlanTime, 1);
                        $stageForecastDeltaStr = $stageForecastDelta > 0 ? "⬆️ {$stageForecastDelta}" : ($stageForecastDelta < 0 ? "⬇️ " . abs($stageForecastDelta) : "0");
                        
                        if ($forecastStage >= 100) {
                            $text .= "🟢 Этап: {$forecastStage}% ({$stageForecastDeltaStr} дн.)\n";
                        } elseif ($forecastStage >= 80) {
                            $text .= "🟡 Этап: {$forecastStage}% ({$stageForecastDeltaStr} дн.)\n";
                        } else {
                            $text .= "🔴 Этап: {$forecastStage}% ({$stageForecastDeltaStr} дн.)\n";
                        }
                    }
                    
                    // ============ COMPLETION DATE FORECAST ============
                    // Get unique commissioning specialists count from last 3 days
                    $personnelCount = 0;
                    $stmt = $conn->prepare("
                        SELECT COUNT(DISTINCT JSON_EXTRACT(od.data, '$.fio')) as unique_specialists
                        FROM ozna_tabel ot
                        INNER JOIN ozna_data od ON ot.user_id = od.user_id
                        WHERE ot.message LIKE CONCAT('%', ?, '%')
                        AND JSON_EXTRACT(od.data, '$.functional_direction') = 'Пусконаладка'
                        AND ot.dt >= DATE_SUB(NOW(), INTERVAL 3 DAY)
                    ");
                    $stmt->bind_param('s', $projectCode);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $personnelRow = $result->fetch_assoc();
                    $personnelCount = $personnelRow['unique_specialists'] ?? 0;
                    
                    // Calculate completion date: forecast remaining actual days / personnel count
                    if ($personnelCount > 0) {
                        $daysRemaining = ceil($forecastRemainingActualDays / $personnelCount);
                        
                        // Calculate target date
                        $completionDate = new \DateTime();
                        $completionDate->modify("+{$daysRemaining} days");
                        $completionDateStr = $completionDate->format('d.m.Y');
                        
                        $text .= "📅 Завершение: ~{$completionDateStr} (при {$personnelCount} чел.)\n";
                        $text .= "   ⏱️ Осталось: {$remainingPlanWork} дн.\n";
                    } else {
                        $text .= "📅 Нет данных о персонале за последние 3 дня\n";
                        $text .= "   ⏱️ Осталось: {$remainingPlanWork} дн.\n";
                    }
                }
            }
            $text .= "\n";
            
            // Milestones with individual efficiency (skip if both plan and fact are 0)
            $text .= "<b>Вехи:</b>\n";
            $displayedCount = 0;
            foreach ($milestoneNames as $name) {
                $planData = $pnrMilestonesPlan[$name] ?? null;
                
                // Skip not applicable
                if ($planData && $planData['is_not_applicable']) {
                    continue;
                }
                
                $fact = $pnrMilestonesFact[$name] ?? 0;
                
                // Get plan value
                $plan = 0;
                if ($planData && $planData['planned_hours'] !== null) {
                    $plan = $planData['planned_hours'];
                    if ($planData['unit'] === 'hours') {
                        $plan = ceil($plan / 8);
                    }
                }
                
                // Skip if both plan and fact are 0
                if ($plan == 0 && $fact == 0) {
                    continue;
                }
                
                $displayedCount++;
                $isConfirmed = $pnrMilestonesConfirmed[$name] ?? false;
                
                // Get survey estimate for this milestone
                $estimate = $milestoneEstimates[$name] ?? null;
                $progressText = '';
                $completionPercent = null;
                if ($isConfirmed) {
                    // Confirmed milestone = 100% completion
                    $completionPercent = 100;
                    $progressText = " ✅ [100%]";
                } elseif ($estimate) {
                    $completionPercent = $estimate['estimated_percent'];
                    $estimateDate = date('d.m', strtotime($estimate['estimated_at']));
                    if ($completionPercent >= 100) {
                        $progressText = " ✅ [100%]";
                    } else {
                        $progressText = " [{$completionPercent}%]";
                    }
                }
                
                $text .= "  {$displayedCount}. <b>{$name}</b>{$progressText}\n";
                $text .= "     📊 План: {$plan} дн. | Факт: {$fact} дн.\n";
                
                // Calculate real efficiency using completion percentage
                if ($plan > 0 && $fact > 0) {
                    $milestoneDelta = $fact - $plan;
                    $milestoneDeltaSign = $milestoneDelta > 0 ? '⬆️' : '⬇️';
                    $milestoneDeltaAbs = abs($milestoneDelta);
                    
                    if ($completionPercent !== null) {
                        // Real efficiency: (% completion * plan) / fact
                        $milestoneEfficiency = round(($completionPercent * $plan) / ($fact * 100) * 100, 1);
                        
                        if ($milestoneEfficiency >= 100) {
                            $text .= "     🟢 Эффективность: {$milestoneEfficiency}% ({$milestoneDeltaSign} {$milestoneDeltaAbs} дн.)\n";
                        } elseif ($milestoneEfficiency >= 80) {
                            $text .= "     🟡 Эффективность: {$milestoneEfficiency}% ({$milestoneDeltaSign} {$milestoneDeltaAbs} дн.)\n";
                        } else {
                            $text .= "     🔴 Эффективность: {$milestoneEfficiency}% ({$milestoneDeltaSign} {$milestoneDeltaAbs} дн.)\n";
                        }
                        
                        // Forecast deviation: only show if milestone is NOT yet completed
                        if ($completionPercent < 100) {
                            $effectiveDone = ($completionPercent * $plan) / 100;
                            // Use current efficiency rate to project remaining work
                            // Efficiency = effectiveDone / fact, so remaining actual = remaining / efficiency
                            if ($fact > 0 && $effectiveDone > 0) {
                                $milestoneEfficiency = $effectiveDone / $fact;
                                $remainingMilestone = $plan - $effectiveDone;
                                $forecastFactMilestone = $fact + ($remainingMilestone / $milestoneEfficiency);
                            } else {
                                $forecastFactMilestone = $fact + max(0, $plan - $effectiveDone);
                            }
                            $forecastDeviationM = round($forecastFactMilestone - $plan, 1);
                            if ($forecastDeviationM != 0) {
                                $forecastDevSign = $forecastDeviationM > 0 ? '⬆️' : '⬇️';
                                $text .= "     🔮 Прогноз отклонения: {$forecastDevSign} " . abs($forecastDeviationM) . " дн.\n";
                            } else {
                                $text .= "     🔮 Прогноз отклонения: 0 дн.\n";
                            }
                        }
                    } else {
                        // No completion estimate - show resource usage only
                        $resourceUsage = round(($fact / $plan) * 100, 1);
                        $text .= "     ⚪ Расход ресурсов: {$resourceUsage}% ({$milestoneDeltaSign} {$milestoneDeltaAbs} дн.)\n";
                        
                        // Forecast deviation using min(fact, plan) as effective work
                        $effectiveDone = min($fact, $plan);
                        $remainingMilestone = $plan - $effectiveDone;
                        $forecastFactMilestone = $fact + max(0, $remainingMilestone);
                        $forecastDeviationM = round($forecastFactMilestone - $plan, 1);
                        if ($forecastDeviationM != 0) {
                            $forecastDevSign = $forecastDeviationM > 0 ? '⬆️' : '⬇️';
                            $text .= "     🔮 Прогноз отклонения: {$forecastDevSign} " . abs($forecastDeviationM) . " дн.\n";
                        } else {
                            $text .= "     🔮 Прогноз отклонения: 0 дн.\n";
                        }
                    }
                } elseif ($fact > 0) {
                    // Plan not set but there's fact
                    $text .= "     ⚠️ План не установлен (⬆️ {$fact} дн.)\n";
                }
                $text .= "\n";
            }
            $text .= "\n";
            
            // Mobilization with description (only if plan or fact > 0)
            $mobFact = $pnrAdditional['mobilization_pnr'] ?? 0;
            if ($pnrMobilizationPlan > 0 || $mobFact > 0) {
                $text .= "🚗 <b>Мобилизационные мероприятия</b>\n";
                $text .= "(Дорога, Инструктаж, Подготовка к служебной поездке, Сдача авансового отчета)\n";
                $text .= "📊 План: {$pnrMobilizationPlan} дн. | Факт: {$mobFact} дн.\n";
                if ($pnrMobilizationPlan > 0 && $mobFact > 0) {
                    $mobDelta = $mobFact - $pnrMobilizationPlan;
                    if ($mobDelta != 0) {
                        $mobDeltaSign = $mobDelta > 0 ? '⬆️' : '⬇️';
                        $text .= "⚠️ Отклонение: {$mobDeltaSign} " . abs($mobDelta) . " дн.\n";
                    }
                } elseif ($mobFact > 0) {
                    $text .= "⚠️ План не установлен (⬆️ {$mobFact} дн.)\n";
                }
                $text .= "\n";
            }
            
            // Project activity with description (only if plan or fact > 0)
            $projActFact = $pnrAdditional['project_activity_pnr'] ?? 0;
            if ($pnrProjectActivityPlan > 0 || $projActFact > 0) {
                $text .= "📝 <b>Проектная деятельность</b>\n";
                $text .= "(Разработка графика работ, Разработка программы работ)\n";
                $text .= "📊 План: {$pnrProjectActivityPlan} дн. | Факт: {$projActFact} дн.\n";
                if ($pnrProjectActivityPlan > 0 && $projActFact > 0) {
                    $projDelta = $projActFact - $pnrProjectActivityPlan;
                    if ($projDelta != 0) {
                        $projDeltaSign = $projDelta > 0 ? '⬆️' : '⬇️';
                        $text .= "⚠️ Отклонение: {$projDeltaSign} " . abs($projDelta) . " дн.\n";
                    }
                } elseif ($projActFact > 0) {
                    $text .= "⚠️ План не установлен (⬆️ {$projActFact} дн.)\n";
                }
                $text .= "\n";
            }
            
            // Calculate unaccounted works (total - milestones - mobilization - project_activity - downtime - rk - warranty)
            $downtimeFact = $pnrAdditional['downtime_pnr'] ?? 0;
            $rkCommentsFact = $pnrAdditional['rk_comments_pnr'] ?? 0;
            $warrantyFact = $pnrAdditional['warranty_pnr'] ?? 0;
            $mobFact = $pnrAdditional['mobilization_pnr'] ?? 0;
            $projActFact = $pnrAdditional['project_activity_pnr'] ?? 0;
            
            // Display additional work section (unaccountedWorksFact already calculated earlier)
            if ($downtimeFact > 0 || $rkCommentsFact > 0 || $warrantyFact > 0 || $unaccountedWorksFact > 0) {
                $text .= "📋 <b>Дополнительные работы:</b>\n";
                
                if ($downtimeFact > 0) {
                    $text .= "  • Простои: {$downtimeFact} дн.\n";
                }
                
                if ($rkCommentsFact > 0) {
                    $text .= "  • Устранение замечаний РК: {$rkCommentsFact} дн.\n";
                }
                
                if ($warrantyFact > 0) {
                    $text .= "  • Гарантийные работы: {$warrantyFact} дн.\n";
                }
                
                if ($unaccountedWorksFact > 0) {
                    $text .= "  • Прочие неучтённые работы: {$unaccountedWorksFact} дн.\n";
                }
            }
            
            // Справочно section (informational data not included in totals)
            $zpiFact = $pnrAdditional['zpi_pnr'] ?? 0;
            if ($zpiFact > 0) {
                $text .= "\n📚 <b>Справочно:</b>\n";
                $text .= "  • Участие в ЗПИ: {$zpiFact} дн.\n";
            }
            $navButtons = $this->createCardNavigationButtons($projectCode, 'pnr');
            $keyboard = [
                $navButtons,
                [
                    ['text' => '⬅️ К списку', 'callback_data' => 'proj_card_list']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showProjectCardPNRPage error: " . $e->getMessage());
            error_log("ProjectHandler::showProjectCardPNRPage trace: " . $e->getTraceAsString());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка загрузки ПНР: ' . $e->getMessage());
        }
    }
    
    /**
     * Add new project
     */
    private function addNewProject(string $chatId, int $userId, Conversation $conversation): ServerResponse
    {
        try {
            // Start conversation for project creation
            $conversation->notes['state'] = 'awaiting_project_code';
            $conversation->update();
                
            $text = "➥ <b>Добавление нового проекта</b>\n\n";
            $text .= "🔢 Введите шифр проекта:\n\n";
            $text .= "<i>Формат: буквы, цифры и дефис</i>\n\n";
            $text .= "⚠️ Шифр должен содержать только заглавные буквы, цифры и дефис";
                
            $keyboard = [
                [
                    ['text' => '❌ Отмена', 'callback_data' => 'proj_card_cancel']
                ]
            ];
                
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
                
        } catch (\Exception $e) {
            error_log("ProjectHandler::addNewProject error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, 'Ошибка: ' . $e->getMessage());
        }
    }
        
    /**
     * Handle text input from user (called by GenericmessageCommand)
     */
    public function handleMessage(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        $state = $conversation->notes['state'] ?? null;
            
        error_log("ProjectHandler::handleMessage - user {$userId}, state: {$state}, text: {$text}");
            
        if ($state === 'awaiting_project_code') {
            return $this->handleProjectCodeInput($chatId, $userId, $text, $conversation);
        }
            
        // State not recognized
        error_log("ProjectHandler::handleMessage - unknown state: {$state}");
        return $this->messageService->sendMessage($chatId, 'Неизвестное состояние');
    }
    
    /**
     * Handle project code input
     */
    private function handleProjectCodeInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        // Validate project code format: only letters, numbers, and hyphens
        if (!preg_match('/^[A-ZА-Я0-9\-]+$/u', $text)) {
            $errorText = "❌ <b>Неправильный формат шифра</b>\n\n";
            $errorText .= "⚠️ Шифр должен содержать только:\n";
            $errorText .= "  • Заглавные буквы (A-Z, А-Я)\n";
            $errorText .= "  • Цифры (0-9)\n";
            $errorText .= "  • Дефис (-)\n\n";
            $errorText .= "🔢 Попробуйте снова или нажмите Отмена";
            
            $keyboard = [
                [
                    ['text' => '❌ Отмена', 'callback_data' => 'proj_card_cancel']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
        }
        
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = strtoupper($text);
            
            // Check if project already exists (including inactive)
            $stmt = $conn->prepare("SELECT code, is_active FROM project WHERE code = ? LIMIT 1");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $existingProject = $result->fetch_assoc();
            
            if ($existingProject) {
                // If project exists but is inactive, reactivate it
                if ($existingProject['is_active'] == 0) {
                    $stmt = $conn->prepare("
                        UPDATE project 
                        SET is_active = 1, name = ? 
                        WHERE code = ?
                    ");
                    $stmt->bind_param('ss', $projectCode, $projectCode);
                    $stmt->execute();
                    
                    // Clear conversation
                    $conversation->stop();
                    
                    $successText = "✅ <b>Проект реактивирован!</b>\n\n";
                    $successText .= "🔹 Шифр: {$projectCode}\n\n";
                    $successText .= "📄 Проект был удалён ранее, теперь он снова активен.\n";
                    $successText .= "📊 Используйте <b>Конструктор</b> для заполнения данных";
                    
                    $keyboard = [
                        [
                            ['text' => '🗂️ Открыть карточку', 'callback_data' => 'proj_card_view_' . $projectCode]
                        ],
                        [
                            ['text' => '📋 Список проектов', 'callback_data' => 'proj_card_list']
                        ],
                        [
                            ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_КарточкаПроекта']
                        ]
                    ];
                    
                    return $this->messageService->sendMessage($chatId, $successText, $keyboard);
                } else {
                    // Project is active - cannot add duplicate
                    $errorText = "❌ <b>Проект уже существует</b>\n\n";
                    $errorText .= "Проект с шифром <b>{$projectCode}</b> уже есть в системе.\n\n";
                    $errorText .= "🔢 Введите другой шифр или нажмите Отмена";
                    
                    $keyboard = [
                        [
                            ['text' => '❌ Отмена', 'callback_data' => 'proj_card_cancel']
                        ]
                    ];
                    
                    return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
                }
            }
            
            // Insert new project (name = code initially)
            $stmt = $conn->prepare("
                INSERT INTO project (code, name, is_active, created_at) 
                VALUES (?, ?, 1, NOW())
            ");
            $stmt->bind_param('ss', $projectCode, $projectCode);
            $stmt->execute();
            
            // Clear conversation
            $conversation->stop();
            
            $successText = "✅ <b>Проект успешно добавлен!</b>\n\n";
            $successText .= "🔹 Шифр: {$projectCode}\n\n";
            $successText .= "📊 Теперь откройте карточку проекта и используйте <b>Конструктор</b> для заполнения всех данных";
            
            $keyboard = [
                [
                    ['text' => '🗂️ Открыть карточку', 'callback_data' => 'proj_card_view_' . $projectCode]
                ],
                [
                    ['text' => '📋 Список проектов', 'callback_data' => 'proj_card_list']
                ],
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Проекты_КарточкаПроекта']
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleProjectCodeInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка при добавлении проекта: ' . $e->getMessage());
        }
    }
    
    /**
     * Show project constructor menu
     */
    private function showProjectConstructor(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get project info
            $stmt = $conn->prepare("SELECT code as project_code, name as project_name FROM project WHERE code = ? LIMIT 1");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $project = $result->fetch_assoc();
            
            if (!$project) {
                return $this->messageService->sendMessage($chatId, 'Проект не найден');
            }
            
            // Get additional data from project_cards if exists
            $stmt = $conn->prepare("
                SELECT pc.*, c.name as customer_name, bu.name as business_unit_name
                FROM project_cards pc
                LEFT JOIN customers c ON pc.customer_id = c.id
                LEFT JOIN business_units bu ON pc.business_unit_id = bu.id
                WHERE pc.project_code = ?
            ");
            $stmt->bind_param('s', $projectCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $cardData = $result->fetch_assoc();
            
            $text = '⚙️ <b>Конструктор проекта</b>

';
            $text .= '<b>Шифр:</b> ' . $project['project_code'] . '

';
            $text .= 'Выберите, что хотите редактировать:';
            
            $keyboard = [
                [
                    ['text' => '📝 Наименование проекта', 'callback_data' => 'proj_constructor_name_' . $projectCode]
                ],
                [
                    ['text' => '👤 Руководитель проекта', 'callback_data' => 'proj_constructor_pm_' . $projectCode]
                ],
                [
                    ['text' => '🏢 Заказчик', 'callback_data' => 'proj_constructor_customer_' . $projectCode]
                ],
                [
                    ['text' => '🏭 Бизнес-направление', 'callback_data' => 'proj_constructor_bu_' . $projectCode]
                ],
                [
                    ['text' => '🔧 ШМР: Плановые трудозатраты', 'callback_data' => 'proj_constructor_smr_hours_' . $projectCode]
                ],
                [
                    ['text' => '⚙️ ПНР: Плановые трудозатраты', 'callback_data' => 'proj_constructor_pnr_hours_' . $projectCode]
                ],
                [
                    ['text' => '🏗️ СМР: Плановые трудозатраты', 'callback_data' => 'proj_cmr_milestones_' . $projectCode]
                ],
                [
                    ['text' => '💼 Наименование 1С', 'callback_data' => 'proj_constructor_1c_name_' . $projectCode]
                ],
                [
                    ['text' => '⬅️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showProjectConstructor error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка загрузки конструктора: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle constructor callbacks
     */
    private function handleConstructorCallback($callbackQuery, string $callbackData, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Clear any pending input state when user presses a callback button (acts as cancel)
        $state = $conversation->notes['state'] ?? null;
        if ($state && str_starts_with($state, 'awaiting_')) {
            $conversation->notes['state'] = null;
            $conversation->update();
        }
        
        // Extract action and project code
        if (str_starts_with($callbackData, 'proj_constructor_name_')) {
            $projectCode = str_replace('proj_constructor_name_', '', $callbackData);
            return $this->showNomenclatureGroupsMenu($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_constructor_pm_')) {
            $projectCode = str_replace('proj_constructor_pm_', '', $callbackData);
            return $this->showProjectManagersMenu($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_constructor_customer_')) {
            $projectCode = str_replace('proj_constructor_customer_', '', $callbackData);
            return $this->showCustomersMenu($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_constructor_bu_')) {
            $projectCode = str_replace('proj_constructor_bu_', '', $callbackData);
            return $this->showBusinessUnitsMenu($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_constructor_1c_name_')) {
            $projectCode = str_replace('proj_constructor_1c_name_', '', $callbackData);
            return $this->show1CNameMenu($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_constructor_pnr_hours_')) {
            $projectCode = str_replace('proj_constructor_pnr_hours_', '', $callbackData);
            return $this->showPNRMilestonesMenu($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_constructor_smr_hours_')) {
            $projectCode = str_replace('proj_constructor_smr_hours_', '', $callbackData);
            return $this->showSMRMilestonesMenu($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_nomenclature_select_')) {
            // Format: proj_nomenclature_select_{groupId}_{projectCode}
            $parts = explode('_', str_replace('proj_nomenclature_select_', '', $callbackData), 2);
            if (count($parts) === 2) {
                $groupId = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->selectNomenclatureGroup($chatId, $userId, $projectCode, $groupId);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_nomenclature_add_')) {
            $projectCode = str_replace('proj_nomenclature_add_', '', $callbackData);
            return $this->promptAddNomenclatureGroup($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_nomenclature_back_')) {
            $projectCode = str_replace('proj_nomenclature_back_', '', $callbackData);
            return $this->showProjectConstructor($chatId, $userId, $projectCode);
        }
        
        // Project Manager callbacks
        if (str_starts_with($callbackData, 'proj_pm_select_')) {
            // Format: proj_pm_select_{pmId}_{projectCode}
            $remaining = substr($callbackData, strlen('proj_pm_select_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $pmId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->selectProjectManager($chatId, $userId, $projectCode, $pmId);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pm_add_')) {
            $projectCode = str_replace('proj_pm_add_', '', $callbackData);
            return $this->promptAddProjectManager($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_pm_back_')) {
            $projectCode = str_replace('proj_pm_back_', '', $callbackData);
            return $this->showProjectConstructor($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_pm_manage_')) {
            $projectCode = str_replace('proj_pm_manage_', '', $callbackData);
            return $this->showManageProjectManagers($chatId, $userId, $projectCode);
        }
        
        // Check specific delete callbacks first
        if (str_starts_with($callbackData, 'proj_pm_delete_yes_')) {
            $remaining = substr($callbackData, strlen('proj_pm_delete_yes_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $pmId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->deleteProjectManager($chatId, $userId, $projectCode, $pmId);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pm_delete_no_')) {
            $remaining = substr($callbackData, strlen('proj_pm_delete_no_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->showManageProjectManagers($chatId, $userId, $projectCode);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pm_delete_')) {
            $remaining = substr($callbackData, strlen('proj_pm_delete_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $pmId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->confirmDeleteProjectManager($chatId, $userId, $projectCode, $pmId);
            }
        }
        
        // Customer callbacks
        if (str_starts_with($callbackData, 'proj_customer_select_')) {
            $remaining = substr($callbackData, strlen('proj_customer_select_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $customerId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->selectCustomer($chatId, $userId, $projectCode, $customerId);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_customer_add_')) {
            $projectCode = str_replace('proj_customer_add_', '', $callbackData);
            return $this->promptAddCustomer($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_customer_back_')) {
            $projectCode = str_replace('proj_customer_back_', '', $callbackData);
            return $this->showProjectConstructor($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_customer_manage_')) {
            $projectCode = str_replace('proj_customer_manage_', '', $callbackData);
            return $this->showManageCustomers($chatId, $userId, $projectCode);
        }
        
        // Check specific delete callbacks first
        if (str_starts_with($callbackData, 'proj_customer_delete_yes_')) {
            $remaining = substr($callbackData, strlen('proj_customer_delete_yes_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $customerId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->deleteCustomer($chatId, $userId, $projectCode, $customerId);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_customer_delete_no_')) {
            $remaining = substr($callbackData, strlen('proj_customer_delete_no_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->showManageCustomers($chatId, $userId, $projectCode);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_customer_delete_')) {
            $remaining = substr($callbackData, strlen('proj_customer_delete_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $customerId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->confirmDeleteCustomer($chatId, $userId, $projectCode, $customerId);
            }
        }
        
        // Business Unit callbacks
        if (str_starts_with($callbackData, 'proj_bu_select_')) {
            $remaining = substr($callbackData, strlen('proj_bu_select_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $buId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->selectBusinessUnit($chatId, $userId, $projectCode, $buId);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_bu_add_')) {
            $projectCode = str_replace('proj_bu_add_', '', $callbackData);
            return $this->promptAddBusinessUnit($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_bu_back_')) {
            $projectCode = str_replace('proj_bu_back_', '', $callbackData);
            return $this->showProjectConstructor($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_bu_manage_')) {
            $projectCode = str_replace('proj_bu_manage_', '', $callbackData);
            return $this->showManageBusinessUnits($chatId, $userId, $projectCode);
        }
        
        // Check specific delete callbacks first
        if (str_starts_with($callbackData, 'proj_bu_delete_yes_')) {
            $remaining = substr($callbackData, strlen('proj_bu_delete_yes_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $buId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->deleteBusinessUnit($chatId, $userId, $projectCode, $buId);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_bu_delete_no_')) {
            $remaining = substr($callbackData, strlen('proj_bu_delete_no_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->showManageBusinessUnits($chatId, $userId, $projectCode);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_bu_delete_')) {
            $remaining = substr($callbackData, strlen('proj_bu_delete_'));
            $firstUnderscore = strpos($remaining, '_');
            if ($firstUnderscore !== false) {
                $buId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                return $this->confirmDeleteBusinessUnit($chatId, $userId, $projectCode, $buId);
            }
        }
        
        // 1C Name callbacks
        if (str_starts_with($callbackData, 'proj_1c_set_')) {
            $projectCode = str_replace('proj_1c_set_', '', $callbackData);
            return $this->prompt1CName($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_1c_clear_')) {
            $projectCode = str_replace('proj_1c_clear_', '', $callbackData);
            return $this->clear1CName($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_1c_back_')) {
            $projectCode = str_replace('proj_1c_back_', '', $callbackData);
            return $this->showProjectConstructor($chatId, $userId, $projectCode);
        }
        
        // PNR Milestones callbacks
        if (str_starts_with($callbackData, 'proj_pnr_milestone_')) {
            $remaining = substr($callbackData, strlen('proj_pnr_milestone_'));
            $parts = explode('_', $remaining, 2);
            if (count($parts) === 2) {
                $milestoneIndex = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->showEditPNRMilestone($chatId, $userId, $projectCode, $milestoneIndex, $conversation);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_set_')) {
            $remaining = substr($callbackData, strlen('proj_pnr_set_'));
            $parts = explode('_', $remaining, 2);
            if (count($parts) === 2) {
                $milestoneIndex = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->promptPNRMilestoneHours($chatId, $userId, $projectCode, $milestoneIndex, $conversation);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_notappl_')) {
            $remaining = substr($callbackData, strlen('proj_pnr_notappl_'));
            $parts = explode('_', $remaining, 2);
            if (count($parts) === 2) {
                $milestoneIndex = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->setPNRMilestoneNotApplicable($chatId, $userId, $projectCode, $milestoneIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_clear_')) {
            $remaining = substr($callbackData, strlen('proj_pnr_clear_'));
            $parts = explode('_', $remaining, 2);
            if (count($parts) === 2) {
                $milestoneIndex = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->clearPNRMilestone($chatId, $userId, $projectCode, $milestoneIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_conf_')) {
            $remaining = substr($callbackData, strlen('proj_pnr_conf_'));
            $parts = explode('_', $remaining, 2);
            if (count($parts) === 2) {
                $milestoneIndex = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->confirmPNRMilestone($chatId, $userId, $projectCode, $milestoneIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_unconf_')) {
            $remaining = substr($callbackData, strlen('proj_pnr_unconf_'));
            $parts = explode('_', $remaining, 2);
            if (count($parts) === 2) {
                $milestoneIndex = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->unconfirmPNRMilestone($chatId, $userId, $projectCode, $milestoneIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_back_')) {
            $projectCode = str_replace('proj_pnr_back_', '', $callbackData);
            return $this->showPNRMilestonesMenu($chatId, $userId, $projectCode);
        }
        
        // PNR Mobilization callbacks
        if (str_starts_with($callbackData, 'proj_pnr_mobilization_')) {
            $projectCode = str_replace('proj_pnr_mobilization_', '', $callbackData);
            return $this->showEditPNRMobilization($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_mob_set_')) {
            $projectCode = str_replace('proj_pnr_mob_set_', '', $callbackData);
            return $this->promptPNRMobilization($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_mob_clear_')) {
            $projectCode = str_replace('proj_pnr_mob_clear_', '', $callbackData);
            return $this->clearPNRMobilization($chatId, $userId, $projectCode);
        }
        
        // PNR Project Activity callbacks
        if (str_starts_with($callbackData, 'proj_pnr_project_activity_')) {
            $projectCode = str_replace('proj_pnr_project_activity_', '', $callbackData);
            return $this->showEditPNRProjectActivity($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_projact_set_')) {
            $projectCode = str_replace('proj_pnr_projact_set_', '', $callbackData);
            return $this->promptPNRProjectActivity($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_pnr_projact_clear_')) {
            $projectCode = str_replace('proj_pnr_projact_clear_', '', $callbackData);
            return $this->clearPNRProjectActivity($chatId, $userId, $projectCode);
        }
        
        // SMR Milestones callbacks - top-level milestone opens sub-milestones list
        if (str_starts_with($callbackData, 'proj_smr_milestone_')) {
            $remaining = substr($callbackData, strlen('proj_smr_milestone_'));
            $parts = explode('_', $remaining, 2);
            if (count($parts) === 2) {
                $milestoneIndex = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->showSMRSubMilestonesMenu($chatId, $userId, $projectCode, $milestoneIndex);
            }
        }
        
        // SMR Sub-milestone callbacks
        if (str_starts_with($callbackData, 'proj_smr_sub_')) {
            $remaining = substr($callbackData, strlen('proj_smr_sub_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->showEditSMRSubMilestone($chatId, $userId, $projectCode, $milestoneIndex, $subIndex, $conversation);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_smr_sset_')) {
            $remaining = substr($callbackData, strlen('proj_smr_sset_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->promptSMRMilestoneHours($chatId, $userId, $projectCode, $milestoneIndex, $subIndex, $conversation);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_smr_sna_')) {
            $remaining = substr($callbackData, strlen('proj_smr_sna_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->setSMRMilestoneNotApplicable($chatId, $userId, $projectCode, $milestoneIndex, $subIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_smr_sclr_')) {
            $remaining = substr($callbackData, strlen('proj_smr_sclr_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->clearSMRMilestone($chatId, $userId, $projectCode, $milestoneIndex, $subIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_smr_conf_')) {
            $remaining = substr($callbackData, strlen('proj_smr_conf_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->confirmSMRMilestone($chatId, $userId, $projectCode, $milestoneIndex, $subIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_smr_unconf_')) {
            $remaining = substr($callbackData, strlen('proj_smr_unconf_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->unconfirmSMRMilestone($chatId, $userId, $projectCode, $milestoneIndex, $subIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_smr_back_')) {
            $projectCode = str_replace('proj_smr_back_', '', $callbackData);
            return $this->showSMRMilestonesMenu($chatId, $userId, $projectCode);
        }
        
        // SMR Mobilization callbacks
        if (str_starts_with($callbackData, 'proj_smr_mobilization_')) {
            $projectCode = str_replace('proj_smr_mobilization_', '', $callbackData);
            return $this->showEditSMRMobilization($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_smr_mob_set_')) {
            $projectCode = str_replace('proj_smr_mob_set_', '', $callbackData);
            return $this->promptSMRMobilization($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_smr_mob_clear_')) {
            $projectCode = str_replace('proj_smr_mob_clear_', '', $callbackData);
            return $this->clearSMRMobilization($chatId, $userId, $projectCode);
        }
        
        // SMR Project Activity callbacks
        if (str_starts_with($callbackData, 'proj_smr_project_activity_')) {
            $projectCode = str_replace('proj_smr_project_activity_', '', $callbackData);
            return $this->showEditSMRProjectActivity($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_smr_projact_set_')) {
            $projectCode = str_replace('proj_smr_projact_set_', '', $callbackData);
            return $this->promptSMRProjectActivity($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_smr_projact_clear_')) {
            $projectCode = str_replace('proj_smr_projact_clear_', '', $callbackData);
            return $this->clearSMRProjectActivity($chatId, $userId, $projectCode);
        }
        
        // SMR Master Duties callbacks
        if (str_starts_with($callbackData, 'proj_smr_master_duties_')) {
            $projectCode = str_replace('proj_smr_master_duties_', '', $callbackData);
            return $this->showEditSMRMasterDuties($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_smr_master_set_')) {
            $projectCode = str_replace('proj_smr_master_set_', '', $callbackData);
            return $this->promptSMRMasterDuties($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_smr_master_clear_')) {
            $projectCode = str_replace('proj_smr_master_clear_', '', $callbackData);
            return $this->clearSMRMasterDuties($chatId, $userId, $projectCode);
        }
        
        // CMR Milestones callbacks - main menu
        if (str_starts_with($callbackData, 'proj_cmr_milestones_')) {
            $projectCode = str_replace('proj_cmr_milestones_', '', $callbackData);
            return $this->showCMRMilestonesMenu($chatId, $userId, $projectCode);
        }
        
        // CMR top-level milestone opens sub-milestones list
        if (str_starts_with($callbackData, 'proj_cmr_milestone_')) {
            $remaining = substr($callbackData, strlen('proj_cmr_milestone_'));
            $parts = explode('_', $remaining, 2);
            if (count($parts) === 2) {
                $milestoneIndex = (int)$parts[0];
                $projectCode = $parts[1];
                return $this->showCMRSubMilestonesMenu($chatId, $userId, $projectCode, $milestoneIndex);
            }
        }
        
        // CMR Sub-milestone callbacks
        if (str_starts_with($callbackData, 'proj_cmr_sub_')) {
            $remaining = substr($callbackData, strlen('proj_cmr_sub_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->showEditCMRSubMilestone($chatId, $userId, $projectCode, $milestoneIndex, $subIndex, $conversation);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_cmr_sset_')) {
            $remaining = substr($callbackData, strlen('proj_cmr_sset_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->promptCMRMilestoneHours($chatId, $userId, $projectCode, $milestoneIndex, $subIndex, $conversation);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_cmr_sna_')) {
            $remaining = substr($callbackData, strlen('proj_cmr_sna_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->setCMRMilestoneNotApplicable($chatId, $userId, $projectCode, $milestoneIndex, $subIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_cmr_sclr_')) {
            $remaining = substr($callbackData, strlen('proj_cmr_sclr_'));
            $parts = explode('_', $remaining, 3);
            if (count($parts) === 3) {
                $milestoneIndex = (int)$parts[0];
                $subIndex = (int)$parts[1];
                $projectCode = $parts[2];
                return $this->clearCMRMilestone($chatId, $userId, $projectCode, $milestoneIndex, $subIndex);
            }
        }
        
        if (str_starts_with($callbackData, 'proj_cmr_back_')) {
            $projectCode = str_replace('proj_cmr_back_', '', $callbackData);
            return $this->showCMRMilestonesMenu($chatId, $userId, $projectCode);
        }
        
        // CMR Forecast toggle callback
        if (str_starts_with($callbackData, 'proj_cmr_forecast_toggle_')) {
            $projectCode = str_replace('proj_cmr_forecast_toggle_', '', $callbackData);
            return $this->toggleCMRForecast($chatId, $userId, $projectCode);
        }
        
        // CMR Mobilization callbacks
        if (str_starts_with($callbackData, 'proj_cmr_mobilization_')) {
            $projectCode = str_replace('proj_cmr_mobilization_', '', $callbackData);
            return $this->showEditCMRMobilization($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_cmr_mob_set_')) {
            $projectCode = str_replace('proj_cmr_mob_set_', '', $callbackData);
            return $this->promptCMRMobilization($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_cmr_mob_clear_')) {
            $projectCode = str_replace('proj_cmr_mob_clear_', '', $callbackData);
            return $this->clearCMRMobilization($chatId, $userId, $projectCode);
        }
        
        // CMR Project Activity callbacks
        if (str_starts_with($callbackData, 'proj_cmr_project_activity_')) {
            $projectCode = str_replace('proj_cmr_project_activity_', '', $callbackData);
            return $this->showEditCMRProjectActivity($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_cmr_projact_set_')) {
            $projectCode = str_replace('proj_cmr_projact_set_', '', $callbackData);
            return $this->promptCMRProjectActivity($chatId, $userId, $projectCode, $conversation);
        }
        
        if (str_starts_with($callbackData, 'proj_cmr_projact_clear_')) {
            $projectCode = str_replace('proj_cmr_projact_clear_', '', $callbackData);
            return $this->clearCMRProjectActivity($chatId, $userId, $projectCode);
        }
        
        if (str_starts_with($callbackData, 'proj_nomenclature_manage_')) {
            $projectCode = str_replace('proj_nomenclature_manage_', '', $callbackData);
            return $this->showManageNomenclatureGroups($chatId, $userId, $projectCode);
        }
        
        // Check more specific prefixes FIRST before generic 'delete_'
        if (str_starts_with($callbackData, 'proj_nomenclature_delete_yes_')) {
            // Format: proj_nomenclature_delete_yes_{groupId}_{projectCode}
            error_log("DELETE YES callback: " . $callbackData);
            $remaining = substr($callbackData, strlen('proj_nomenclature_delete_yes_'));
            error_log("Remaining after prefix: " . $remaining);
            $firstUnderscore = strpos($remaining, '_');
            error_log("First underscore position: " . ($firstUnderscore !== false ? $firstUnderscore : 'NOT FOUND'));
            if ($firstUnderscore !== false) {
                $groupId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                error_log("Parsed - GroupId: {$groupId}, ProjectCode: {$projectCode}");
                return $this->deleteNomenclatureGroup($chatId, $userId, $projectCode, $groupId);
            } else {
                error_log("Failed to parse delete_yes callback data");
                return $this->messageService->sendMessage($chatId, '❌ Ошибка парсинга данных');
            }
        }
        
        if (str_starts_with($callbackData, 'proj_nomenclature_delete_no_')) {
            // Format: proj_nomenclature_delete_no_{groupId}_{projectCode}
            error_log("DELETE NO callback: " . $callbackData);
            $remaining = substr($callbackData, strlen('proj_nomenclature_delete_no_'));
            error_log("Remaining after prefix: " . $remaining);
            $firstUnderscore = strpos($remaining, '_');
            error_log("First underscore position: " . ($firstUnderscore !== false ? $firstUnderscore : 'NOT FOUND'));
            if ($firstUnderscore !== false) {
                $projectCode = substr($remaining, $firstUnderscore + 1);
                error_log("Parsed - ProjectCode: {$projectCode}");
                return $this->showManageNomenclatureGroups($chatId, $userId, $projectCode);
            } else {
                error_log("Failed to parse delete_no callback data");
                return $this->messageService->sendMessage($chatId, '❌ Ошибка парсинга данных');
            }
        }
        
        if (str_starts_with($callbackData, 'proj_nomenclature_delete_')) {
            // Format: proj_nomenclature_delete_{groupId}_{projectCode}
            error_log("DELETE CONFIRM callback: " . $callbackData);
            $remaining = substr($callbackData, strlen('proj_nomenclature_delete_'));
            error_log("Remaining after prefix: " . $remaining);
            $firstUnderscore = strpos($remaining, '_');
            error_log("First underscore position: " . ($firstUnderscore !== false ? $firstUnderscore : 'NOT FOUND'));
            if ($firstUnderscore !== false) {
                $groupId = (int)substr($remaining, 0, $firstUnderscore);
                $projectCode = substr($remaining, $firstUnderscore + 1);
                error_log("Parsed - GroupId: {$groupId}, ProjectCode: {$projectCode}");
                return $this->confirmDeleteNomenclatureGroup($chatId, $userId, $projectCode, $groupId);
            } else {
                error_log("Failed to parse delete callback data");
                return $this->messageService->sendMessage($chatId, '❌ Ошибка парсинга данных');
            }
        }
        
        return $this->messageService->sendMessage($chatId, 'Функция в разработке');
    }
    
    /**
     * Show nomenclature groups selection menu
     */
    private function showNomenclatureGroupsMenu(string $chatId, int $userId, string $projectCode): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Create table if not exists
            $conn->query("
                CREATE TABLE IF NOT EXISTS project_nomenclature_groups (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    created_by BIGINT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Get all nomenclature groups
            $result = $conn->query("SELECT id, name FROM project_nomenclature_groups ORDER BY name");
            $groups = [];
            while ($row = $result->fetch_assoc()) {
                $groups[] = $row;
            }
            
            $text = '📝 <b>Наименование проекта</b>

';
            $text .= 'Выберите номенклатурную группу:';
            
            $keyboard = [];
            foreach ($groups as $group) {
                $keyboard[] = [
                    ['text' => $group['name'], 'callback_data' => 'proj_nomenclature_select_' . $group['id'] . '_' . $projectCode]
                ];
            }
            
            $keyboard[] = [
                ['text' => '➕ Добавить группу', 'callback_data' => 'proj_nomenclature_add_' . $projectCode]
            ];
            $keyboard[] = [
                ['text' => '🗑️ Управление группами', 'callback_data' => 'proj_nomenclature_manage_' . $projectCode]
            ];
            $keyboard[] = [
                ['text' => '⬅️ Назад', 'callback_data' => 'proj_nomenclature_back_' . $projectCode]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::showNomenclatureGroupsMenu error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Select nomenclature group for project
     */
    private function selectNomenclatureGroup(string $chatId, int $userId, string $projectCode, int $groupId): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            
            // Get group name
            $stmt = $conn->prepare("SELECT name FROM project_nomenclature_groups WHERE id = ?");
            $stmt->bind_param('i', $groupId);
            $stmt->execute();
            $result = $stmt->get_result();
            $group = $result->fetch_assoc();
            
            if (!$group) {
                return $this->messageService->sendMessage($chatId, '❌ Группа не найдена');
            }
            
            // Update project name
            $stmt = $conn->prepare("UPDATE project SET name = ? WHERE code = ?");
            $stmt->bind_param('ss', $group['name'], $projectCode);
            $stmt->execute();
            
            $text = '✅ <b>Наименование обновлено</b>

';
            $text .= 'Проект: ' . $projectCode . '
';
            $text .= 'Наименование: ' . $group['name'];
            
            $keyboard = [
                [
                    ['text' => '⬅️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К карточке', 'callback_data' => 'proj_card_view_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::selectNomenclatureGroup error: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Prompt user to add new nomenclature group
     */
    private function promptAddNomenclatureGroup(string $chatId, int $userId, string $projectCode, Conversation $conversation): ServerResponse
    {
        $conversation->notes['state'] = 'awaiting_nomenclature_group_name';
        $conversation->notes['project_code'] = $projectCode;
        $conversation->update();
        
        $text = '➕ <b>Добавить номенклатурную группу</b>

';
        $text .= 'Введите название новой группы:';
        
        $keyboard = [
            [
                ['text' => '❌ Отмена', 'callback_data' => 'proj_nomenclature_back_' . $projectCode]
            ]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * Handle nomenclature group name input
     */
    public function handleNomenclatureGroupNameInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        try {
            $conn = $this->databaseService->getConnection();
            $projectCode = $conversation->notes['project_code'] ?? null;
            
            if (!$projectCode) {
                $conversation->stop();
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
            }
            
            $groupName = trim($text);
            
            if (empty($groupName)) {
                $errorText = '❌ Название группы не может быть пустым.

Введите название группы:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_nomenclature_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Check if group already exists
            $stmt = $conn->prepare("SELECT id FROM project_nomenclature_groups WHERE name = ?");
            $stmt->bind_param('s', $groupName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->fetch_assoc()) {
                $errorText = '❌ Группа с таким названием уже существует.

Введите другое название:';
                $keyboard = [
                    [
                        ['text' => '❌ Отмена', 'callback_data' => 'proj_nomenclature_back_' . $projectCode]
                    ]
                ];
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Insert new group
            $stmt = $conn->prepare("INSERT INTO project_nomenclature_groups (name, created_by) VALUES (?, ?)");
            $stmt->bind_param('si', $groupName, $userId);
            $stmt->execute();
            
            $conversation->stop();
            
            $successText = '✅ <b>Группа добавлена</b>

';
            $successText .= 'Название: ' . $groupName;
            
            $keyboard = [
                [
                    ['text' => '⬅️ К выбору группы', 'callback_data' => 'proj_constructor_name_' . $projectCode]
                ],
                [
                    ['text' => '🗂️ К конструктору', 'callback_data' => 'proj_card_edit_' . $projectCode]
                ]
            ];
            
            return $this->messageService->sendMessage($chatId, $successText, $keyboard);
            
        } catch (\Exception $e) {
            error_log("ProjectHandler::handleNomenclatureGroupNameInput error: " . $e->getMessage());
            $conversation->stop();
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: ' . $e->getMessage());
        }
    }
}

