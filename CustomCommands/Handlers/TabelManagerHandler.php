<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;

/**
 * Handler for managing tabel entries (delete/edit)
 * Available only for Admin and SuperAdmin users
 */
class TabelManagerHandler implements CallbackHandlerInterface
{
    private DatabaseService $databaseService;
    private MessageService $messageService;
    private AdminService $adminService;

    private const ENTRIES_PER_PAGE = 10;

    public function __construct(
        DatabaseService $databaseService,
        MessageService $messageService,
        AdminService $adminService
    ) {
        $this->databaseService = $databaseService;
        $this->messageService = $messageService;
        $this->adminService = $adminService;
    }

    private const SETTINGS_FILE = __DIR__ . '/../../tabel_settings.json';

    public function canHandle(string $callbackData): bool
    {
        return str_starts_with($callbackData, 'category_Админка_Табель') ||
               str_starts_with($callbackData, 'tbl_mgr_') ||
               str_starts_with($callbackData, 'tbl_set_');
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $messageId = $callbackQuery->getMessage()->getMessageId();
        $userId = $callbackQuery->getFrom()->getId();
        $callbackData = $callbackQuery->getData();

        error_log("TabelManagerHandler: handle called with category: '{$category}', callback: '{$callbackData}'");

        // Check admin access
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        if ($userAccessLevel < DatabaseService::ACCESS_LEVEL_ADMIN) {
            return $this->messageService->sendOrEditMessage(
                $chatId,
                "Доступ к управлению табелем ограничен администраторами.",
                null,
                $messageId
            );
        }

        // Route to appropriate method
        if ($category === 'Админка_Табель' || $callbackData === 'category_Админка_Табель') {
            return $this->showTopMenu($chatId, $messageId);
        }

        // Top-level submenu navigation
        if ($callbackData === 'tbl_mgr_records') {
            return $this->showMainMenu($chatId, $messageId);
        }

        if ($callbackData === 'tbl_mgr_settings') {
            return $this->showReportingSettings($chatId, $messageId);
        }

        // Settings: Toggle "Другая дата"
        if ($callbackData === 'tbl_set_toggle_other_date') {
            return $this->toggleOtherDate($chatId, $messageId);
        }

        // Back to top menu
        if ($callbackData === 'tbl_mgr_top') {
            return $this->showTopMenu($chatId, $messageId);
        }

        // Filter by user
        if (str_starts_with($callbackData, 'tbl_mgr_users')) {
            return $this->showUsersList($chatId, $messageId);
        }

        if (str_starts_with($callbackData, 'tbl_mgr_user_')) {
            $targetUserId = (int)str_replace('tbl_mgr_user_', '', $callbackData);
            return $this->showUserEntries($chatId, $messageId, $targetUserId);
        }

        // Filter by project
        if (str_starts_with($callbackData, 'tbl_mgr_projects')) {
            return $this->showProjectsList($chatId, $messageId);
        }

        if (str_starts_with($callbackData, 'tbl_mgr_proj_')) {
            $projectCode = str_replace('tbl_mgr_proj_', '', $callbackData);
            return $this->showProjectEntries($chatId, $messageId, $projectCode);
        }

        // Filter by date - show date selection menu
        if ($callbackData === 'tbl_mgr_dates') {
            return $this->showDatesList($chatId, $messageId);
        }

        // Single date selection
        if ($callbackData === 'tbl_mgr_date_single') {
            $this->setAwaitingDateInput($conversation);
            return $this->promptDateInput($chatId, $messageId);
        }

        // Date range selection
        if ($callbackData === 'tbl_mgr_date_range') {
            $this->setAwaitingDateRangeStart($conversation);
            return $this->promptDateRangeStart($chatId, $messageId);
        }

        // Handle date range entries with pagination: tbl_mgr_range_YYYY-MM-DD_YYYY-MM-DD or tbl_mgr_range_YYYY-MM-DD_YYYY-MM-DD_page
        if (str_starts_with($callbackData, 'tbl_mgr_range_')) {
            $rangePart = str_replace('tbl_mgr_range_', '', $callbackData);
            $page = 0;
            
            // Check if there's a page number at the end (format: YYYY-MM-DD_YYYY-MM-DD_page)
            if (preg_match('/^(\d{4}-\d{2}-\d{2})_(\d{4}-\d{2}-\d{2})_(\d+)$/', $rangePart, $matches)) {
                $dateFrom = $matches[1];
                $dateTo = $matches[2];
                $page = (int)$matches[3];
            } elseif (preg_match('/^(\d{4}-\d{2}-\d{2})_(\d{4}-\d{2}-\d{2})$/', $rangePart, $matches)) {
                $dateFrom = $matches[1];
                $dateTo = $matches[2];
            } else {
                return $this->showMainMenu($chatId, $messageId);
            }
            
            error_log("TabelManagerHandler: range callback parsed - from: {$dateFrom}, to: {$dateTo}, page: {$page}");
            return $this->showDateRangeEntries($chatId, $messageId, $dateFrom, $dateTo, $page);
        }

        // Handle single date entries with pagination: tbl_mgr_date_YYYY-MM-DD or tbl_mgr_date_YYYY-MM-DD_page
        if (str_starts_with($callbackData, 'tbl_mgr_date_')) {
            $datePart = str_replace('tbl_mgr_date_', '', $callbackData);
            $page = 0;
            
            // Check if there's a page number at the end (format: YYYY-MM-DD_page)
            if (preg_match('/^(\d{4}-\d{2}-\d{2})_(\d+)$/', $datePart, $matches)) {
                $date = $matches[1];
                $page = (int)$matches[2];
            } elseif (preg_match('/^(\d{4}-\d{2}-\d{2})$/', $datePart, $matches)) {
                $date = $matches[1];
            } else {
                return $this->showMainMenu($chatId, $messageId);
            }
            
            error_log("TabelManagerHandler: date callback parsed - date: {$date}, page: {$page}");
            return $this->showDateEntries($chatId, $messageId, $date, $page);
        }

        // Search
        if ($callbackData === 'tbl_mgr_search') {
            return $this->promptSearch($chatId, $messageId, $conversation);
        }

        // View all entries (paginated)
        if (str_starts_with($callbackData, 'tbl_mgr_all')) {
            $page = 0;
            if (preg_match('/tbl_mgr_all_(\d+)/', $callbackData, $matches)) {
                $page = (int)$matches[1];
            }
            return $this->showAllEntries($chatId, $messageId, $page);
        }

        // View entry details
        if (str_starts_with($callbackData, 'tbl_mgr_view_')) {
            $entryId = (int)str_replace('tbl_mgr_view_', '', $callbackData);
            return $this->showEntryDetails($chatId, $messageId, $entryId);
        }

        // Delete entry confirmation
        if (str_starts_with($callbackData, 'tbl_mgr_del_')) {
            $entryId = (int)str_replace('tbl_mgr_del_', '', $callbackData);
            return $this->confirmDelete($chatId, $messageId, $entryId);
        }

        // Confirm delete
        if (str_starts_with($callbackData, 'tbl_mgr_cdel_')) {
            $entryId = (int)str_replace('tbl_mgr_cdel_', '', $callbackData);
            return $this->executeDelete($chatId, $messageId, $entryId);
        }

        // Edit entry - show project selection
        if (str_starts_with($callbackData, 'tbl_mgr_edit_')) {
            $entryId = (int)str_replace('tbl_mgr_edit_', '', $callbackData);
            return $this->showEditProjectSelection($chatId, $messageId, $entryId);
        }

        // Execute project change
        if (preg_match('/tbl_mgr_chprj_(\d+)_(.+)/', $callbackData, $matches)) {
            $entryId = (int)$matches[1];
            $newProjectCode = $matches[2];
            return $this->executeProjectChange($chatId, $messageId, $entryId, $newProjectCode);
        }

        // Change date - prompt for new date
        if (str_starts_with($callbackData, 'tbl_mgr_chdate_')) {
            $entryId = (int)str_replace('tbl_mgr_chdate_', '', $callbackData);
            $this->setAwaitingEntryDateChange($conversation, $entryId);
            return $this->promptEntryDateChange($chatId, $messageId, $entryId);
        }

        // Back to main menu
        if ($callbackData === 'tbl_mgr_back') {
            // Сбрасываем флаг ожидания ввода даты
            $conversation->notes['awaiting_tabel_mgr_date'] = false;
            $conversation->update();
            return $this->showMainMenu($chatId, $messageId);
        }

        // Back to admin menu
        if ($callbackData === 'tbl_mgr_admin') {
            return $this->backToAdminMenu($chatId, $messageId, $userId);
        }

        return $this->showMainMenu($chatId, $messageId);
    }

    public function getPriority(): int
    {
        return 26; // Higher than AdminHandler to catch tabel management callbacks first
    }

    /**
     * Show top-level tabel management menu with two submenus
     */
    private function showTopMenu(string $chatId, int $messageId): ServerResponse
    {
        $text = "<b>📋 Управление табелем</b>\n\n";
        $text .= "Выберите раздел:";

        $keyboard = [
            [['text' => '📝 Менеджер записей', 'callback_data' => 'tbl_mgr_records']],
            [['text' => '⚙️ Настройка отчетности', 'callback_data' => 'tbl_mgr_settings']],
            [['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']]
        ];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Show main records manager menu (previously showMainMenu)
     */
    private function showMainMenu(string $chatId, int $messageId): ServerResponse
    {
        $totalEntries = $this->databaseService->getTabelEntriesCount();

        $text = "<b>📝 Менеджер записей</b>\n\n";
        $text .= "Всего записей: <b>{$totalEntries}</b>\n\n";
        $text .= "Выберите способ поиска записей:";

        $keyboard = [
            [['text' => '👤 По сотруднику', 'callback_data' => 'tbl_mgr_users']],
            [['text' => '🏗 По проекту', 'callback_data' => 'tbl_mgr_projects']],
            [['text' => '📅 По дате', 'callback_data' => 'tbl_mgr_dates']],
            [['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_top']]
        ];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Show list of users with tabel entries
     */
    private function showUsersList(string $chatId, int $messageId): ServerResponse
    {
        $users = $this->databaseService->getTabelUsers();

        error_log("TabelManagerHandler::showUsersList - Got " . count($users) . " users");

        $text = "<b>👤 Выберите сотрудника</b>\n\n";
        $text .= "Найдено сотрудников с записями: " . count($users);

        $keyboard = [];
        foreach ($users as $user) {
            $fio = $user['fio'] ?: "ID: {$user['user_id']}";
            // Truncate long names for button
            if (mb_strlen($fio) > 30) {
                $fio = mb_substr($fio, 0, 27) . '...';
            }
            $keyboard[] = [['text' => $fio, 'callback_data' => 'tbl_mgr_user_' . $user['user_id']]];
        }

        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Show entries for specific user
     */
    private function showUserEntries(string $chatId, int $messageId, int $targetUserId, int $page = 0): ServerResponse
    {
        $filters = ['user_id' => $targetUserId];
        $totalCount = $this->databaseService->getTabelEntriesCount($filters);
        $entries = $this->databaseService->getTabelEntriesFiltered($filters, self::ENTRIES_PER_PAGE, $page * self::ENTRIES_PER_PAGE);

        // Get user name
        $userData = $this->databaseService->getUserData($targetUserId);
        $userName = $userData['fio'] ?? "ID: {$targetUserId}";

        return $this->renderEntriesList($chatId, $messageId, $entries, $totalCount, $page, "👤 Записи: {$userName}", "tbl_mgr_user_{$targetUserId}");
    }

    /**
     * Show list of projects from tabel entries
     */
    private function showProjectsList(string $chatId, int $messageId): ServerResponse
    {
        $projects = $this->databaseService->getTabelProjects();

        $text = "<b>🏗 Выберите проект</b>\n\n";
        $text .= "Найдено проектов: " . count($projects);

        $keyboard = [];
        // Show projects in rows of 2
        $row = [];
        foreach ($projects as $index => $projectCode) {
            $row[] = ['text' => $projectCode, 'callback_data' => 'tbl_mgr_proj_' . $projectCode];
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }

        $keyboard[] = [['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Show entries for specific project
     */
    private function showProjectEntries(string $chatId, int $messageId, string $projectCode, int $page = 0): ServerResponse
    {
        $filters = ['project_code' => $projectCode];
        $totalCount = $this->databaseService->getTabelEntriesCount($filters);
        $entries = $this->databaseService->getTabelEntriesFiltered($filters, self::ENTRIES_PER_PAGE, $page * self::ENTRIES_PER_PAGE);

        return $this->renderEntriesList($chatId, $messageId, $entries, $totalCount, $page, "🏗 Проект: {$projectCode}", "tbl_mgr_proj_{$projectCode}");
    }

    /**
     * Show list of dates with tabel entries
     */
    private function showDatesList(string $chatId, int $messageId): ServerResponse
    {
        // Показываем меню выбора: одна дата или диапазон
        return $this->showDateSelectionMenu($chatId, $messageId);
    }

    /**
     * Show date selection menu (single date or range)
     */
    private function showDateSelectionMenu(string $chatId, int $messageId): ServerResponse
    {
        $text = "<b>📅 Поиск по дате</b>\n\n";
        $text .= "Выберите тип поиска:";

        $keyboard = [
            [['text' => '📅 Одна дата', 'callback_data' => 'tbl_mgr_date_single']],
            [['text' => '📅 Диапазон дат', 'callback_data' => 'tbl_mgr_date_range']],
            [['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]
        ];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Prompt user to input single date
     */
    private function promptDateInput(string $chatId, int $messageId): ServerResponse
    {
        $text = "<b>📅 Поиск по дате</b>\n\n";
        $text .= "Введите дату в формате <b>ДД.ММ.ГГГГ</b>\n";
        $text .= "Например: <code>15.01.2026</code>";

        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'tbl_mgr_back']]
        ];
        
        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Prompt user to input date range start
     */
    private function promptDateRangeStart(string $chatId, int $messageId): ServerResponse
    {
        $text = "<b>📅 Поиск по диапазону дат</b>\n\n";
        $text .= "Введите <b>начальную</b> дату (От) в формате <b>ДД.ММ.ГГГГ</b>\n";
        $text .= "Например: <code>01.01.2026</code>";

        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'tbl_mgr_back']]
        ];
        
        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Prompt user to input date range end
     */
    private function promptDateRangeEnd(string $chatId, int $messageId, string $startDate): ServerResponse
    {
        $displayStartDate = date('d.m.Y', strtotime($startDate));
        
        $text = "<b>📅 Поиск по диапазону дат</b>\n\n";
        $text .= "Дата От: <b>{$displayStartDate}</b>\n\n";
        $text .= "Введите <b>конечную</b> дату (До) в формате <b>ДД.ММ.ГГГГ</b>\n";
        $text .= "Например: <code>31.01.2026</code>";

        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'tbl_mgr_back']]
        ];
        
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Set conversation state for single date input
     */
    public function setAwaitingDateInput(Conversation $conversation): void
    {
        $conversation->notes['awaiting_tabel_mgr_date'] = true;
        $conversation->notes['awaiting_tabel_mgr_date_type'] = 'single';
        $conversation->update();
        error_log("TabelManagerHandler: Set awaiting_tabel_mgr_date = true (single)");
    }

    /**
     * Set conversation state for date range start input
     */
    public function setAwaitingDateRangeStart(Conversation $conversation): void
    {
        $conversation->notes['awaiting_tabel_mgr_date'] = true;
        $conversation->notes['awaiting_tabel_mgr_date_type'] = 'range_start';
        $conversation->notes['tabel_mgr_date_from'] = null;
        $conversation->update();
        error_log("TabelManagerHandler: Set awaiting_tabel_mgr_date = true (range_start)");
    }

    /**
     * Set conversation state for date range end input
     */
    public function setAwaitingDateRangeEnd(Conversation $conversation, string $startDate): void
    {
        $conversation->notes['awaiting_tabel_mgr_date'] = true;
        $conversation->notes['awaiting_tabel_mgr_date_type'] = 'range_end';
        $conversation->notes['tabel_mgr_date_from'] = $startDate;
        $conversation->update();
        error_log("TabelManagerHandler: Set awaiting_tabel_mgr_date = true (range_end), startDate: {$startDate}");
    }

    /**
     * Handle date input from user
     */
    public function handleDateInput(string $chatId, string $text, Conversation $conversation, int $userId): ServerResponse
    {
        error_log("TabelManagerHandler::handleDateInput called with text: '{$text}'");
        
        $dateType = $conversation->notes['awaiting_tabel_mgr_date_type'] ?? 'single';
        error_log("TabelManagerHandler::handleDateInput date type: '{$dateType}'");

        // Парсим дату
        $text = trim($text);
        
        // Поддерживаем форматы: ДД.ММ.ГГГГ, ДД/ММ/ГГГГ, ДД-ММ-ГГГГ
        $date = null;
        if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/', $text, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];
            
            if (checkdate($month, $day, $year)) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (!$date) {
            $errorText = "❌ Неверный формат даты.\n\n";
            $errorText .= "Введите дату в формате <b>ДД.ММ.ГГГГ</b>\n";
            $errorText .= "Например: <code>15.01.2026</code>";
            
            $keyboard = [
                [['text' => '❌ Отмена', 'callback_data' => 'tbl_mgr_back']]
            ];
            
            return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
        }

        // Обрабатываем в зависимости от типа ввода
        if ($dateType === 'single') {
            // Сбрасываем флаг ожидания
            $conversation->notes['awaiting_tabel_mgr_date'] = false;
            $conversation->update();
            
            // Показываем записи за указанную дату
            return $this->showDateEntries($chatId, 0, $date);
        } elseif ($dateType === 'range_start') {
            // Сохраняем начальную дату и запрашиваем конечную
            $this->setAwaitingDateRangeEnd($conversation, $date);
            return $this->promptDateRangeEnd($chatId, 0, $date);
        } elseif ($dateType === 'range_end') {
            // Получаем начальную дату
            $startDate = $conversation->notes['tabel_mgr_date_from'] ?? null;
            
            if (!$startDate) {
                // Если начальной даты нет, начинаем заново
                $conversation->notes['awaiting_tabel_mgr_date'] = false;
                $conversation->update();
                return $this->showMainMenu($chatId, 0);
            }
            
            // Проверяем что конечная дата не раньше начальной
            if ($date < $startDate) {
                $displayStartDate = date('d.m.Y', strtotime($startDate));
                $errorText = "❌ Конечная дата не может быть раньше начальной.\n\n";
                $errorText .= "Дата От: <b>{$displayStartDate}</b>\n\n";
                $errorText .= "Введите дату <b>позднее или равную</b> начальной.";
                
                $keyboard = [
                    [['text' => '❌ Отмена', 'callback_data' => 'tbl_mgr_back']]
                ];
                
                return $this->messageService->sendMessage($chatId, $errorText, $keyboard);
            }
            
            // Сбрасываем флаг ожидания
            $conversation->notes['awaiting_tabel_mgr_date'] = false;
            $conversation->notes['tabel_mgr_date_from'] = null;
            $conversation->update();
            
            // Показываем записи за диапазон дат
            return $this->showDateRangeEntries($chatId, 0, $startDate, $date);
        } elseif ($dateType === 'entry_change') {
            // Изменение даты в существующей записи
            $entryId = $conversation->notes['tabel_mgr_entry_id'] ?? null;
            
            if (!$entryId) {
                $conversation->notes['awaiting_tabel_mgr_date'] = false;
                $conversation->update();
                return $this->showMainMenu($chatId, 0);
            }
            
            // Сбрасываем флаг ожидания
            $conversation->notes['awaiting_tabel_mgr_date'] = false;
            $conversation->notes['tabel_mgr_entry_id'] = null;
            $conversation->update();
            
            // Выполняем изменение даты
            return $this->executeEntryDateChange($chatId, 0, $entryId, $date);
        }

        // Fallback
        $conversation->notes['awaiting_tabel_mgr_date'] = false;
        $conversation->update();
        return $this->showMainMenu($chatId, 0);
    }

    /**
     * Set conversation state for entry date change
     */
    public function setAwaitingEntryDateChange(Conversation $conversation, int $entryId): void
    {
        $conversation->notes['awaiting_tabel_mgr_date'] = true;
        $conversation->notes['awaiting_tabel_mgr_date_type'] = 'entry_change';
        $conversation->notes['tabel_mgr_entry_id'] = $entryId;
        $conversation->update();
        error_log("TabelManagerHandler: Set awaiting_tabel_mgr_date = true (entry_change), entryId: {$entryId}");
    }

    /**
     * Prompt user to input new date for entry
     */
    private function promptEntryDateChange(string $chatId, int $messageId, int $entryId): ServerResponse
    {
        $entry = $this->databaseService->getTabelEntryById($entryId);
        
        if (!$entry) {
            return $this->messageService->sendOrEditMessage(
                $chatId,
                "Запись не найдена.",
                [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]],
                $messageId
            );
        }

        // Извлекаем текущую дату из сообщения
        $currentDate = $this->extractDateFromMessage($entry['message']);
        $currentDateDisplay = $currentDate ? date('d.m.Y', strtotime($currentDate)) : 'не определена';

        $text = "<b>Изменение даты записи #{$entryId}</b>\n\n";
        $text .= "Текущая дата: <b>{$currentDateDisplay}</b>\n\n";
        $text .= "Введите новую дату в формате <b>ДД.ММ.ГГГГ</b>\n";
        $text .= "Например: <code>15.01.2026</code>";

        $keyboard = [
            [['text' => '❌ Отмена', 'callback_data' => 'tbl_mgr_view_' . $entryId]]
        ];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Execute entry date change
     */
    private function executeEntryDateChange(string $chatId, int $messageId, int $entryId, string $newDate): ServerResponse
    {
        $entry = $this->databaseService->getTabelEntryById($entryId);

        if (!$entry) {
            return $this->messageService->sendMessage(
                $chatId,
                "Запись не найдена.",
                [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]]
            );
        }

        // Извлекаем текущую дату из сообщения
        $oldDate = $this->extractDateFromMessage($entry['message']);
        
        if (!$oldDate) {
            return $this->messageService->sendMessage(
                $chatId,
                "Не удалось определить текущую дату в записи.",
                [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_view_' . $entryId]]]
            );
        }

        $success = $this->databaseService->updateTabelEntryDate($entryId, $oldDate, $newDate);
        
        $oldDateDisplay = date('d.m.Y', strtotime($oldDate));
        $newDateDisplay = date('d.m.Y', strtotime($newDate));

        if ($success) {
            $text = "Дата записи #{$entryId} изменена:\n";
            $text .= "<b>{$oldDateDisplay}</b> → <b>{$newDateDisplay}</b>";
            error_log("TabelManagerHandler: Entry #{$entryId} date changed from '{$oldDate}' to '{$newDate}'");
        } else {
            $text = "Не удалось изменить дату записи #{$entryId}.";
            error_log("TabelManagerHandler: Failed to change date for entry #{$entryId}");
        }

        $keyboard = [
            [['text' => '🔍 Просмотреть запись', 'callback_data' => 'tbl_mgr_view_' . $entryId]],
            [['text' => '⬅️ В меню управления', 'callback_data' => 'tbl_mgr_back']]
        ];

        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    /**
     * Extract date from message text (format DD.MM.YYYY)
     */
    private function extractDateFromMessage(string $message): ?string
    {
        // Ищем дату в формате ДД.ММ.ГГГГ
        if (preg_match('/(\d{2})\.(\d{2})\.(\d{4})/', $message, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            return "{$year}-{$month}-{$day}";
        }
        return null;
    }

    /**
     * Show entries for specific date
     */
    private function showDateEntries(string $chatId, int $messageId, string $date, int $page = 0): ServerResponse
    {
        // Используем message_date для поиска по дате в тексте записи
        $filters = ['message_date' => $date];
        $totalCount = $this->databaseService->getTabelEntriesCount($filters);
        $entries = $this->databaseService->getTabelEntriesFiltered($filters, self::ENTRIES_PER_PAGE, $page * self::ENTRIES_PER_PAGE);

        $displayDate = date('d.m.Y', strtotime($date));
        return $this->renderEntriesList($chatId, $messageId, $entries, $totalCount, $page, "Дата: {$displayDate}", "tbl_mgr_date_{$date}");
    }

    /**
     * Show entries for date range
     */
    private function showDateRangeEntries(string $chatId, int $messageId, string $dateFrom, string $dateTo, int $page = 0): ServerResponse
    {
        // Используем message_date_range для поиска по диапазону дат в тексте записи
        $filters = ['message_date_from' => $dateFrom, 'message_date_to' => $dateTo];
        $totalCount = $this->databaseService->getTabelEntriesCount($filters);
        $entries = $this->databaseService->getTabelEntriesFiltered($filters, self::ENTRIES_PER_PAGE, $page * self::ENTRIES_PER_PAGE);

        $displayDateFrom = date('d.m.Y', strtotime($dateFrom));
        $displayDateTo = date('d.m.Y', strtotime($dateTo));
        return $this->renderEntriesList($chatId, $messageId, $entries, $totalCount, $page, "Даты: {$displayDateFrom} - {$displayDateTo}", "tbl_mgr_range_{$dateFrom}_{$dateTo}");
    }

    /**
     * Show all entries with pagination
     */
    private function showAllEntries(string $chatId, int $messageId, int $page = 0): ServerResponse
    {
        $totalCount = $this->databaseService->getTabelEntriesCount();
        $entries = $this->databaseService->getTabelEntriesFiltered([], self::ENTRIES_PER_PAGE, $page * self::ENTRIES_PER_PAGE);

        return $this->renderEntriesList($chatId, $messageId, $entries, $totalCount, $page, "Все записи", "tbl_mgr_all");
    }

    /**
     * Render entries list with pagination
     */
    private function renderEntriesList(string $chatId, int $messageId, array $entries, int $totalCount, int $page, string $title, string $baseCallback): ServerResponse
    {
        $totalPages = ceil($totalCount / self::ENTRIES_PER_PAGE);
        $currentPage = $page + 1;

        $text = "<b>{$title}</b>\n";
        $text .= "Страница {$currentPage} из {$totalPages} (всего: {$totalCount})\n\n";

        if (empty($entries)) {
            $text .= "<i>Записей не найдено</i>";
        }

        $keyboard = [];

        foreach ($entries as $entry) {
            // Parse message to extract key info
            $message = $entry['message'];
            $dt = date('d.m.Y H:i', strtotime($entry['dt']));
            $userName = $entry['user_fio'] ?: "ID:{$entry['user_id']}";

            // Extract project code (first word)
            $projectCode = explode(' ', $message)[0];
            if (mb_strlen($projectCode) > 15) {
                $projectCode = mb_substr($projectCode, 0, 12) . '...';
            }

            // Shorten user name for button
            if (mb_strlen($userName) > 15) {
                $userName = mb_substr($userName, 0, 12) . '...';
            }

            // Format: "date | project | user"
            $buttonText = "{$dt} | {$projectCode}";

            $keyboard[] = [['text' => $buttonText, 'callback_data' => 'tbl_mgr_view_' . $entry['id']]];
        }

        // Pagination
        $navRow = [];
        if ($page > 0) {
            $navRow[] = ['text' => '⬅️ Назад', 'callback_data' => "{$baseCallback}_" . ($page - 1)];
        }
        if ($page < $totalPages - 1) {
            $navRow[] = ['text' => 'Вперед ➡️', 'callback_data' => "{$baseCallback}_" . ($page + 1)];
        }
        if (!empty($navRow)) {
            $keyboard[] = $navRow;
        }

        $keyboard[] = [['text' => '⬅️ В меню управления', 'callback_data' => 'tbl_mgr_back']];

        // Если messageId = 0, отправляем новое сообщение, иначе редактируем
        if ($messageId > 0) {
            return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
        } else {
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
    }

    /**
     * Show entry details with edit/delete options
     */
    private function showEntryDetails(string $chatId, int $messageId, int $entryId): ServerResponse
    {
        $entry = $this->databaseService->getTabelEntryById($entryId);

        if (!$entry) {
            return $this->messageService->sendOrEditMessage(
                $chatId,
                "Запись не найдена (возможно, уже удалена).",
                [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]],
                $messageId
            );
        }

        $dt = date('d.m.Y H:i', strtotime($entry['dt']));
        $userName = $entry['user_fio'] ?: "ID: {$entry['user_id']}";

        $text = "<b>Запись #{$entryId}</b>\n\n";
        $text .= "<b>Дата:</b> {$dt}\n";
        $text .= "<b>Сотрудник:</b> {$userName}\n";
        $text .= "<b>ID сотрудника:</b> {$entry['user_id']}\n\n";
        $text .= "<b>Содержание:</b>\n<code>{$entry['message']}</code>";

        $keyboard = [
            [
                ['text' => '✏️ Изменить проект', 'callback_data' => 'tbl_mgr_edit_' . $entryId],
                ['text' => '📅 Изменить дату', 'callback_data' => 'tbl_mgr_chdate_' . $entryId]
            ],
            [
                ['text' => '🗑 Удалить', 'callback_data' => 'tbl_mgr_del_' . $entryId]
            ],
            [['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]
        ];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Confirm entry deletion
     */
    private function confirmDelete(string $chatId, int $messageId, int $entryId): ServerResponse
    {
        $entry = $this->databaseService->getTabelEntryById($entryId);

        if (!$entry) {
            return $this->messageService->sendOrEditMessage(
                $chatId,
                "Запись не найдена.",
                [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]],
                $messageId
            );
        }

        $dt = date('d.m.Y H:i', strtotime($entry['dt']));
        $userName = $entry['user_fio'] ?: "ID: {$entry['user_id']}";

        $text = "<b>Подтвердите удаление</b>\n\n";
        $text .= "Вы уверены, что хотите удалить запись?\n\n";
        $text .= "<b>Дата:</b> {$dt}\n";
        $text .= "<b>Сотрудник:</b> {$userName}\n";
        $text .= "<b>Содержание:</b>\n<code>{$entry['message']}</code>\n\n";
        $text .= "<b>Это действие нельзя отменить!</b>";

        $keyboard = [
            [
                ['text' => '⚠️ Да, удалить', 'callback_data' => 'tbl_mgr_cdel_' . $entryId],
                ['text' => '❌ Отмена', 'callback_data' => 'tbl_mgr_view_' . $entryId]
            ]
        ];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Execute entry deletion
     */
    private function executeDelete(string $chatId, int $messageId, int $entryId): ServerResponse
    {
        $entry = $this->databaseService->getTabelEntryById($entryId);
        
        if (!$entry) {
            return $this->messageService->sendOrEditMessage(
                $chatId,
                "Запись не найдена.",
                [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]],
                $messageId
            );
        }

        $success = $this->databaseService->deleteTabelEntry($entryId);

        if ($success) {
            $text = "Запись #{$entryId} успешно удалена.";
            error_log("TabelManagerHandler: Entry #{$entryId} deleted by admin");
        } else {
            $text = "Не удалось удалить запись #{$entryId}.";
            error_log("TabelManagerHandler: Failed to delete entry #{$entryId}");
        }

        $keyboard = [[['text' => '⬅️ В меню управления', 'callback_data' => 'tbl_mgr_back']]];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Show project selection for editing
     */
    private function showEditProjectSelection(string $chatId, int $messageId, int $entryId): ServerResponse
    {
        $entry = $this->databaseService->getTabelEntryById($entryId);

        if (!$entry) {
            return $this->messageService->sendOrEditMessage(
                $chatId,
                "Запись не найдена.",
                [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]],
                $messageId
            );
        }

        // Extract current project from message
        $currentProject = explode(' ', $entry['message'])[0];

        // Get list of active projects
        $projects = $this->databaseService->getActiveProjects();

        $text = "<b>Изменение проекта</b>\n\n";
        $text .= "Текущий проект: <b>{$currentProject}</b>\n\n";
        $text .= "Выберите новый проект:";

        $keyboard = [];
        $row = [];
        foreach ($projects as $project) {
            $projectCode = $project['current_name'];
            // Skip current project
            if ($projectCode === $currentProject) {
                continue;
            }
            
            // Truncate long project codes
            $displayCode = $projectCode;
            if (mb_strlen($displayCode) > 15) {
                $displayCode = mb_substr($displayCode, 0, 12) . '...';
            }

            $row[] = ['text' => $displayCode, 'callback_data' => "tbl_mgr_chprj_{$entryId}_{$projectCode}"];
            
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }

        $keyboard[] = [['text' => '❌ Отмена', 'callback_data' => 'tbl_mgr_view_' . $entryId]];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Execute project change
     */
    private function executeProjectChange(string $chatId, int $messageId, int $entryId, string $newProjectCode): ServerResponse
    {
        $entry = $this->databaseService->getTabelEntryById($entryId);

        if (!$entry) {
            return $this->messageService->sendOrEditMessage(
                $chatId,
                "Запись не найдена.",
                [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]],
                $messageId
            );
        }

        // Extract current project from message
        $oldProjectCode = explode(' ', $entry['message'])[0];

        $success = $this->databaseService->updateTabelEntryProject($entryId, $oldProjectCode, $newProjectCode);

        if ($success) {
            $text = "Проект записи #{$entryId} изменен:\n";
            $text .= "<b>{$oldProjectCode}</b> -> <b>{$newProjectCode}</b>";
            error_log("TabelManagerHandler: Entry #{$entryId} project changed from '{$oldProjectCode}' to '{$newProjectCode}'");
        } else {
            $text = "Не удалось изменить проект записи #{$entryId}.";
            error_log("TabelManagerHandler: Failed to change project for entry #{$entryId}");
        }

        $keyboard = [
            [['text' => '🔍 Просмотреть запись', 'callback_data' => 'tbl_mgr_view_' . $entryId]],
            [['text' => '⬅️ В меню управления', 'callback_data' => 'tbl_mgr_back']]
        ];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Show reporting settings submenu
     */
    private function showReportingSettings(string $chatId, int $messageId): ServerResponse
    {
        $isOtherDateEnabled = $this->isOtherDateEnabled();
        $statusText = $isOtherDateEnabled ? 'Включена' : 'Выключена';
        $statusIcon = $isOtherDateEnabled ? '✅' : '❌';

        $text = "<b>⚙️ Настройка отчетности</b>\n\n";
        $text .= "Управление параметрами отчетности табеля.\n\n";
        $text .= "<b>Другая дата:</b> {$statusIcon} {$statusText}\n";
        $text .= "<i>Когда включена, в меню выбора даты табеля появляется кнопка \"Другая дата\" для ввода произвольной даты.</i>";

        $toggleText = $isOtherDateEnabled ? 'Выключить \"Другая дата\"' : 'Включить \"Другая дата\"';
        $toggleIcon = $isOtherDateEnabled ? '❌' : '✅';

        $keyboard = [
            [['text' => "{$toggleIcon} {$toggleText}", 'callback_data' => 'tbl_set_toggle_other_date']],
            [['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_top']]
        ];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Toggle "Другая дата" setting
     */
    private function toggleOtherDate(string $chatId, int $messageId): ServerResponse
    {
        $current = $this->isOtherDateEnabled();
        $this->setOtherDateEnabled(!$current);

        $newStatus = !$current ? 'включена' : 'выключена';
        error_log("TabelManagerHandler: 'Другая дата' toggled to: {$newStatus}");

        return $this->showReportingSettings($chatId, $messageId);
    }

    /**
     * Check if "Другая дата" option is enabled
     */
    public static function isOtherDateEnabled(): bool
    {
        $settingsFile = self::SETTINGS_FILE;
        if (!file_exists($settingsFile)) {
            return false;
        }

        $settings = json_decode(file_get_contents($settingsFile), true);
        return $settings['other_date_enabled'] ?? false;
    }

    /**
     * Set "Другая дата" enabled/disabled
     */
    private function setOtherDateEnabled(bool $enabled): void
    {
        $settingsFile = self::SETTINGS_FILE;
        $settings = [];

        if (file_exists($settingsFile)) {
            $settings = json_decode(file_get_contents($settingsFile), true) ?: [];
        }

        $settings['other_date_enabled'] = $enabled;
        file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Prompt for search (placeholder - needs conversation handling)
     */
    private function promptSearch(string $chatId, int $messageId, Conversation $conversation): ServerResponse
    {
        $text = "<b>Поиск записей</b>\n\n";
        $text .= "Функция поиска по тексту в разработке.\n";
        $text .= "Пока используйте фильтрацию по сотруднику или проекту.";

        $keyboard = [[['text' => '⬅️ Назад', 'callback_data' => 'tbl_mgr_back']]];

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }

    /**
     * Return to admin menu
     */
    private function backToAdminMenu(string $chatId, int $messageId, int $userId): ServerResponse
    {
        // This will redirect to AdminHandler
        $accessLevel = $this->adminService->getUserAccessLevel($userId);
        $levelName = $this->adminService->getAccessLevelName($accessLevel);

        $keyboard = [
            [['text' => 'Управление проектами', 'callback_data' => 'category_Админка_Проекты']],
            [['text' => 'Заявки на доступ', 'callback_data' => 'category_Админка_Заявки']],
            [['text' => 'Управление табелем', 'callback_data' => 'category_Админка_Табель']]
        ];

        if ($accessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
            $keyboard[] = [['text' => 'Управление уровнями доступа', 'callback_data' => 'category_УровеньДоступа_Управление']];
        }

        $text = "<b>Панель администратора</b>\n";
        $text .= "Ваш уровень доступа: {$levelName}\n\n";
        $text .= "Выберите действие:";

        return $this->messageService->sendOrEditMessage($chatId, $text, $keyboard, $messageId);
    }
}
