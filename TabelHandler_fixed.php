<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;

/**
 * Handler for tabel related callback queries
 */
class TabelHandler implements CallbackHandlerInterface
{
    private DatabaseService $databaseService;
    private MessageService $messageService;
    private ProjectSelector $projectSelector;
    private TabelEntry $tabelEntry;
    private AdminService $adminService;
    
    // Define activity types for validation
    private array $activityTypes = [
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
        // Fix for commissioning works issue - added missing activity type
        'ПусконаладочныеРаботы'
    ];
    
    // Сокращенные названия для кнопок (максимум 20 символов для избежания обрезки)
    private array $shortWorkTypeNames = [
        // Новые типы для Мобильной бригады (сокращенные названия)
        'МобБриг' => 'Мобильная бригада',
        'ПодгТКП' => 'Подготовка ТКП',
        'ПНЛ' => 'Пусконаладка',
        'ШМ' => 'Шеф-монтаж',
        // Старые типы для совместимости
        'МобильнаяБригада' => 'Мобильная бригада',
        'ПодготовкаТКП' => 'Подготовка ТКП',
        'Пусконаладка' => 'Пусконаладка',
        'ШефМонтаж' => 'Шеф-монтаж'
    ];
    
    // Полные названия типов работ для отображения в сообщениях
    private array $fullWorkTypeNames = [
        // Новые типы для Мобильной бригады (сокращенные названия)
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        // Новые типы для Подготовки ТКП (сокращенные названия)
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        // Новые типы для Пусконаладки (сокращенные названия)
        'ПНР', 'УЗ_РК', 'РабГарантии', 'Простой',
        // Новые типы для Шеф-монтажа (сокращенные названия)
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии', 'ШМ_Простой'
    ];

    public function __construct(
        DatabaseService $databaseService, 
        MessageService $messageService,
        ProjectSelector $projectSelector,
        TabelEntry $tabelEntry,
        AdminService $adminService
    ) {
        $this->databaseService = $databaseService;
        $this->messageService = $messageService;
        $this->projectSelector = $projectSelector;
        $this->tabelEntry = $tabelEntry;
        $this->adminService = $adminService;
    }

    public function canHandle(string $callbackData): bool
    {
        // Fix encoding issues with callback data
        $callbackData = $this->ensureUtf8Encoding($callbackData);
        $canHandle = str_starts_with($callbackData, 'category_Табель');
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);

        // Check user access level - Guests (level 0) should not have access to tabel functionality
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        if ($userAccessLevel == 0) { // ACCESS_LEVEL_GUEST
            $text = "⚠️ <b>Доступ ограничен</b>\n\nУ вас уровень доступа \"Гость\". Обратитесь к администратору для получения доступа к функциональности табеля.";
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Check for duplicate entry attempts first
        // Only apply this restriction to non-admin users (levels 1-2)
        // Users with admin level 3+ and super admins (level 4) can bypass this restriction
        if ($this->isConfirmationCallback($category) && $userAccessLevel < 3) {
            $isSuperAdmin = $this->adminService->isSuperAdmin($userId);
            $recentEntry = $this->databaseService->hasRecentTabelEntry($userId, $isSuperAdmin);
            if ($recentEntry) {
                $text = $this->tabelEntry->formatRecentEntryError($recentEntry);
                $text = $this->ensureUtf8Encoding($text);
                return $this->messageService->sendMessage($chatId, $text);
            }
        }

        // Check if user has FIO filled for tabel operations
        if (str_contains($category, 'Табель') && !$this->isMainTabelMenu($category)) {
            $userData = \pDB::getUserData($userId);
            if (!isset($userData['fio']) || $userData['fio'] === 'Без ФИО') {
                $text = '<b>Заполните ФИО (один раз) командой /fio Фамилия Имя Отчество</b>';
                $text = $this->ensureUtf8Encoding($text);
                return $this->messageService->sendMessage($chatId, $text);
            }
            
            // Check if user has functional direction filled
            if (!isset($userData['functional_direction']) || $userData['functional_direction'] === 'Не указано') {
                $text = '<b>Укажите функциональное направление профиля. Используйте /profile</b>';
                $text = $this->ensureUtf8Encoding($text);
                return $this->messageService->sendMessage($chatId, $text);
            }
        }

        return $this->routeTabelCallback($callbackQuery, $category, $conversation);
    }

    public function getPriority(): int
    {
        return 5;
    }

    private function isConfirmationCallback(string $category): bool
    {
        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        return str_contains($category, '_yes') && str_contains($category, 'Табель_');
    }

    private function isMainTabelMenu(string $category): bool
    {
        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        return in_array($category, ['Табель', 'Табель_Мобильная_Бригада_Дата']);
    }

    private function routeTabelCallback(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();

        // Main tabel menu
        if ($category === 'Табель') {
            return $this->handleMainTabel($chatId);
        }

        // Date selection flows
        if ($this->projectSelector->isDateSelection($category)) {
            return $this->handleDateSelection($callbackQuery, $category, $conversation);
        }

        // Project selection
        if ($this->projectSelector->isProjectSelection($category)) {
            return $this->handleProjectSelection($callbackQuery, $category, $conversation);
        }

        // Work type selection
        if ($category === 'Табель_Мобильная_Бригада_Дата') {
            return $this->handleDateSelectionMenu($chatId);
        }

        // TKP preparation menu
        if ($category === 'Табель_ПодготовкаТКП_Меню') {
            return $this->handleTKPSubtypesMenu($chatId);
        }

        // Back to current activity menu
        if ($category === 'Табель_Текущая_Назад') {
            return $this->handleBackToCurrentActivity($chatId, $conversation);
        }
        
        // Back to commissioning menu
        if ($category === 'Табель_Пусконаладка_Back') {
            return $this->handleBackToCommissioning($chatId, $conversation);
        }
        
        // Back to supervision menu
        if ($category === 'Табель_ШМ_Back') {
            return $this->handleBackToSupervision($chatId, $conversation);
        }

        // Handle Простой (Idle/Downtime) - простая обработка без детального меню
        if ($this->isIdleMenuCallback($category)) {
            return $this->handleActivityConfirmation($callbackQuery, $category, $conversation);
        }

        // Activity execution
        if ($this->isActivityExecution($category)) {
            return $this->handleActivityExecution($callbackQuery, $category, $conversation);
        }

        // Activity confirmation
        if ($this->isActivityConfirmation($category)) {
            return $this->handleActivityConfirmation($callbackQuery, $category, $conversation);
        }

        // Other work types
        return $this->handleOtherWorkTypes($chatId, $category, $conversation);
    }

    private function handleMainTabel(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createWorkTypeKeyboard();
        $text = '<b>Выберите вид работ</b>';
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function handleDateSelectionMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createDateSelectionKeyboard();
        $text = '<b>Выберите дату</b>';
        $text = $this->ensureUtf8Encoding($text);
        return $this->messageService->sendMessage($chatId, $text, $keyboard);
    }

    private function handleDateSelection(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $date = $this->projectSelector->extractDate($category);
        
        if ($date) {
            $conversation->notes['tabel_data'] = $date;
            
            // Save if this is current activity flow
            if (str_contains($category, 'Текущая')) {
                $conversation->notes['is_current_activity'] = true;
                $conversation->notes['project_code'] = 'Текущая деятельность'; // Текущая деятельность не привязана к проектам
                $conversation->update();
                
                // Для текущей деятельности сразу показываем типы активности
                $keyboard = $this->messageService->createCurrentActivityKeyboard();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>💼 Табель - Текущая деятельность за {$date}</b>",
                    $keyboard
                );
            } elseif (str_contains($category, 'Пусконаладка') || str_contains($category, 'ПНЛ')) {
                // Для Пусконаладки сразу переходим к выбору проекта
                $conversation->notes['is_commissioning'] = true;
                $conversation->notes['is_supervision'] = false;
                $conversation->notes['is_current_activity'] = false;
                $conversation->update();
                
                $text = "<b>⚙️ Табель Пусконаладка за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } elseif (str_contains($category, 'Шеф-монтаж') || str_contains($category, 'ШМ')) {
                // Для Шеф-монтажа сразу переходим к выбору проекта
                $conversation->notes['is_supervision'] = true;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_current_activity'] = false;
                $conversation->update();
                
                $text = "<b>🔧 Табель Шеф-монтаж за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } else {
                // Для мобильной бригады сбрасываем флаги
                $conversation->notes['is_current_activity'] = false;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_supervision'] = false;
            }
            $conversation->update();
            
            $text = "<b>Табель за {$date}, выбор проекта</b>";
            
            $keyboard = $this->messageService->createProjectSelectionKeyboard();
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        return $this->messageService->sendMessage($chatId, 'Ошибка выбора даты');
    }

    private function handleProjectSelection(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $projectCode = $this->projectSelector->extractProjectCode($category);
        
        if ($projectCode) {
            $conversation->notes['project_code'] = $projectCode;
            $conversation->notes['current_category'] = $category;
            $conversation->update();
            
            // Check if this is current activity flow
            $isCurrentActivity = isset($conversation->notes['is_current_activity']) && $conversation->notes['is_current_activity'];
            
            // Check if this is commissioning flow
            $isCommissioning = isset($conversation->notes['is_commissioning']) && $conversation->notes['is_commissioning'];
            
            // Check if this is supervision flow
            $isSupervision = isset($conversation->notes['is_supervision']) && $conversation->notes['is_supervision'];
            
            if ($isCurrentActivity) {
                $keyboard = $this->messageService->createCurrentActivityKeyboard();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>💼 Табель - Текущая деятельность для проекта {$projectCode}</b>",
                    $keyboard
                );
            } elseif ($isCommissioning) {
                $keyboard = $this->messageService->createCommissioningKeyboard();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>⚙️ Табель - Пусконаладка для проекта {$projectCode}</b>",
                    $keyboard
                );
            } elseif ($isSupervision) {
                $keyboard = $this->messageService->createSupervisionKeyboard();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>🔧 Табель - Шеф-монтаж для проекта {$projectCode}</b>",
                    $keyboard
                );
            } else {
                $keyboard = $this->messageService->createMobileBrigadeKeyboard();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>💼 Табель - Мобильная бригада для проекта {$projectCode}</b>",
                    $keyboard
                );
            }
        }
        
        return $this->messageService->sendMessage($chatId, 'Ошибка выбора проекта');
    }

    private function isActivityConfirmation(string $category): bool
    {
        // Check standard activity types
        foreach ($this->activityTypes as $activity) {
            if ($category === "category_Табель_{$activity}") {
                return true;
            }
        }
        
        // Check idle menu final confirmations (when user confirms after selecting detailed reason)
        if (str_contains($category, '_yes') && 
            (str_contains($category, 'Табель_Простой') || str_contains($category, 'Табель_ШМ_Простой'))) {
            return true;
        }
        
        return false;
    }

    private function handleActivityConfirmation(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Extract activity type from category
        $activityType = str_replace('Табель_', '', $category);
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
        $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
        
        return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
    }

    private function isActivityExecution(string $category): bool
    {
        // Check standard activity execution
        if (str_contains($category, '_yes') && 
            preg_match('/^category_Табель_(' . implode('|', $this->activityTypes) . ')_yes$/', $category)) {
            return true;
        }
        
        // Check idle menu execution (when user confirms idle activity with detailed reason)
        if (str_contains($category, '_yes') && 
            (str_contains($category, 'Табель_Простой_yes') || str_contains($category, 'Табель_ШМ_Простой_yes'))) {
            return true;
        }
        
        return false;
    }

    private function handleActivityExecution(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        // Extract activity type
        $activityType = str_replace(['Табель_', '_yes'], '', $category);
        $userData = \pDB::getUserData($userId);
        $fio = $userData['fio'] ?? 'Без ФИО';
        $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        // Get idle reason if available
        $idleReason = $conversation->notes['idle_reason'] ?? '';
        
        // Create and send tabel entry
        $success = $this->tabelEntry->addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason);
        
        if ($success) {
            $message = $this->tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
            return $this->messageService->sendMessage($chatId, $message);
        }
        
        return $this->messageService->sendMessage($chatId, 'Ошибка при добавлении записи в табель');
    }

    private function handleOtherWorkTypes(string $chatId, string $category, Conversation $conversation): ServerResponse
    {
        if ($category === 'Табель_Пусконаладка') {
            return $this->handleCommissioningDateSelection($chatId);
        }
        
        if ($category === 'Табель_Шеф_монтаж') {
            return $this->handleSupervisionDateSelection($chatId);
        }
        
        // Handle Mobile Brigade
        if ($category === 'Табель_Мобильная_Бригада') {
            return $this->handleMobileBrigadeDateSelection($chatId);
        }
        
        // Handle Current Activity (Текущая деятельность)
        if ($category === 'Табель_Текущая') {
            return $this->handleCurrentActivityDateSelection($chatId);
        }
        
        // Handle work type buttons that appear after date and project selection
        // These are the activity types that should be confirmed
        if (in_array(str_replace('Табель_', '', $category), $this->activityTypes)) {
            // This is a work type selection, treat it as activity confirmation
            // We need to create a proper callback_data format for confirmation
            $activityType = str_replace('Табель_', '', $category);
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
            
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        return $this->messageService->sendMessage($chatId, 'Неизвестная команда табеля');
    }

    /**
     * Handle current activity date selection
     */
    private function handleCurrentActivityDateSelection(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_Текущая');
        return $this->messageService->sendMessage($chatId, '<b>Выберите дату текущей деятельности</b>', $keyboard);
    }

    /**
     * Handle mobile brigade date selection
     */
    private function handleMobileBrigadeDateSelection(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_Мобильная_Бригада');
        return $this->messageService->sendMessage($chatId, '💼 <b>Выберите дату для Мобильной бригады</b>', $keyboard);
    }

    private function handleTKPSubtypesMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createTKPSubtypesKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>📋 Выберите тип Подготовки материала к ТКП</b>',
            $keyboard
        );
    }

    private function handleBackToCurrentActivity(string $chatId, Conversation $conversation): ServerResponse
    {
        $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
        $keyboard = $this->messageService->createCurrentActivityKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>💼 Табель - Текущая деятельность за {$date}</b>",
            $keyboard
        );
    }
    
    /**
     * Handle back to commissioning menu
     */
    private function handleBackToCommissioning(string $chatId, Conversation $conversation): ServerResponse
    {
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $keyboard = $this->messageService->createCommissioningKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>⚙️ Табель - Пусконаладка для проекта {$projectCode}</b>",
            $keyboard
        );
    }
    
    /**
     * Handle back to supervision menu
     */
    private function handleBackToSupervision(string $chatId, Conversation $conversation): ServerResponse
    {
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $keyboard = $this->messageService->createSupervisionKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>🔧 Табель - Шеф-монтаж для проекта {$projectCode}</b>",
            $keyboard
        );
    }

    /**
     * Handle commissioning date selection
     */
    private function handleCommissioningDateSelection(string $chatId): ServerResponse
    {
        // Используем короткий префикс для избежания ограничения в 64 байта
        $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_ПНЛ');
        return $this->messageService->sendMessage($chatId, '⚙️ <b>Выберите дату для Пусконаладки</b>', $keyboard);
    }

    /**
     * Handle supervision (Шеф-монтаж) date selection
     */
    private function handleSupervisionDateSelection(string $chatId): ServerResponse
    {
        // Используем короткий префикс для избежания ограничения в 64 байта
        $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_ШМ');
        return $this->messageService->sendMessage($chatId, '🔧 <b>Выберите дату для Шеф-монтажа</b>', $keyboard);
    }

    /**
     * Check if this is an idle menu callback
     */
    private function isIdleMenuCallback(string $category): bool
    {
        return $category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой';
    }

    /**
     * Handle idle/downtime menu system (3-level hierarchy)
     */
    private function handleIdleMenu(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // ВРЕМЕННАЯ ОТЛАДКА - удалить после проверки
        $this->messageService->sendMessage($chatId, "DEBUG handleIdleMenu: {$category}");
        
        // Level 1: Main categories (1-6)
        if ($category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой') {
            return $this->handleIdleMainCategories($chatId, $category);
        }
        
        // Обработка подтверждений простоя
        if ($category === 'Табель_Простой_yes' || $category === 'Табель_ШМ_Простой_yes') {
            // Перенаправляем на обычную обработку выполнения активности
            return $this->handleActivityExecution($callbackQuery, $category, $conversation);
        }
        
        // Level 2: Subcategories (single dash)
        if (preg_match('/^Табель_(ШМ_)?Простой_(\d+)$/', $category, $matches)) {
            $categoryNumber = (int)$matches[2];
            $isSupervision = !empty($matches[1]);
            $this->messageService->sendMessage($chatId, "DEBUG: Обрабатываем подкатегорию {$categoryNumber}, supervision: " . ($isSupervision ? 'да' : 'нет'));
            return $this->handleIdleSubcategories($chatId, $categoryNumber, $isSupervision);
        }
        
        // Level 3: Sub-subcategories (double dash) or final selection
        if (preg_match('/^Табель_(ШМ_)?Простой_(\d+)_(.+)$/', $category, $matches)) {
            $categoryNumber = (int)$matches[2];
            $subcategory = $matches[3];
            $isSupervision = !empty($matches[1]);
            
            // Check if this subcategory has sub-subcategories
            if ($this->hasSubSubcategories($categoryNumber, $subcategory)) {
                return $this->handleIdleSubSubcategories($chatId, $categoryNumber, $subcategory, $isSupervision);
            } else {
                // This is a final selection - treat as activity confirmation
                return $this->handleIdleFinalSelection($callbackQuery, $category, $conversation);
            }
        }
        
        // Level 4: Final selection from sub-subcategories
        if (preg_match('/^Табель_(ШМ_)?Простой_(\d+)_(.+)_(.+)$/', $category)) {
            return $this->handleIdleFinalSelection($callbackQuery, $category, $conversation);
        }
        
        return $this->messageService->sendMessage($chatId, 'Неизвестная команда простоя');
    }

    /**
     * Show main idle categories (Level 1)
     */
    private function handleIdleMainCategories(string $chatId, string $category): ServerResponse
    {
        $isSupervision = str_contains($category, 'ШМ_');
        $prefix = $isSupervision ? 'Табель_ШМ_Простой' : 'Табель_Простой';
        
        $keyboard = [
            [
                ['text' => '📦 Допоставка', 'callback_data' => "category_{$prefix}_1"]
            ],
            [
                ['text' => '📞 Отсутствие обратной связи', 'callback_data' => "category_{$prefix}_2"]
            ],
            [
                ['text' => '🏭 Готовность Заказчика', 'callback_data' => "category_{$prefix}_3"]
            ],
            [
                ['text' => '🚗 Транспорт', 'callback_data' => "category_{$prefix}_4"]
            ],
            [
                ['text' => '🌨️ Погодные условия', 'callback_data' => "category_{$prefix}_5"]
            ],
            [
                ['text' => '🔧 Прочее', 'callback_data' => "category_{$prefix}_6"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => $isSupervision ? 'category_Табель_ШМ_Back' : 'category_Табель_Пусконаладка_Back']
            ]
        ];
        
        $title = $isSupervision ? '🔧 Простой - Шеф-монтаж' : '⚙️ Простой - Пусконаладка';
        return $this->messageService->sendMessage($chatId, "<b>{$title}</b>\n\nВыберите причину простоя:", $keyboard);
    }

    /**
     * Show subcategories for a specific main category (Level 2)
     */
    private function handleIdleSubcategories(string $chatId, int $categoryNumber, bool $isSupervision): ServerResponse
    {
        // ВРЕМЕННАЯ ОТЛАДКА - удалить после проверки
        $this->messageService->sendMessage($chatId, "DEBUG handleIdleSubcategories: категория {$categoryNumber}, supervision: " . ($isSupervision ? 'да' : 'нет'));
        
        $prefix = $isSupervision ? 'Табель_ШМ_Простой' : 'Табель_Простой';
        $backPrefix = $isSupervision ? 'Табель_ШМ_Простой' : 'Табель_Простой';
        
        $categories = [
            1 => [
                'title' => 'Допоставка',
                'items' => [
                    'def' => 'Дефицит по спецификации',
                    'ost' => 'Отсутствие в поставке',
                    'izm' => 'Изменение ТР'
                ]
            ],
            2 => [
                'title' => 'Отсутствие обратной связи',
                'items' => [
                    'teh' => 'Техническое решение',
                    'sog' => 'Согласование плана работ'
                ]
            ],
            3 => [
                'title' => 'Готовность Заказчика',
                'items' => [
                    'ene' => 'Отсутствие энергоресурсов',
                    'rab' => 'Отсутствие рабочих сред',
                    'per' => 'Перенос предъявления работ',
                    'str' => 'Отсутствие строительной готовности'
                ]
            ],
            4 => [
                'title' => 'Транспорт',
                'items' => [
                    'pol' => 'Поломка',
                    'mes' => 'Отсутствие свободных мест'
                ]
            ],
            5 => [
                'title' => 'Погодные условия',
                'items' => [
                    'bur' => 'Буран/снегопад',
                    'mor' => 'Мороз',
                    'ras' => 'Распутица'
                ]
            ],
            6 => [
                'title' => 'Прочее',
                'items' => [
                    'po' => 'Разработка ПО',
                    'dop' => 'Отсутствие допуска к работам',
                    'svy' => 'Отсутствие связи',
                    'kom' => 'Свой комментарий'
                ]
            ]
        ];
        
        if (!isset($categories[$categoryNumber])) {
            $this->messageService->sendMessage($chatId, "DEBUG: Категория {$categoryNumber} не найдена!");
            return $this->messageService->sendMessage($chatId, 'Неизвестная категория');
        }
        
        $category = $categories[$categoryNumber];
        $keyboard = [];
        
        foreach ($category['items'] as $key => $text) {
            $callbackData = "category_{$prefix}_{$categoryNumber}_{$key}";
            // ВРЕМЕННАЯ ОТЛАДКА - проверяем длину callback_data
            $this->messageService->sendMessage($chatId, "DEBUG callback: {$callbackData} (длина: " . strlen($callbackData) . ")");
            
            $keyboard[] = [
                ['text' => $text, 'callback_data' => $callbackData]
            ];
        }
        
        // Add back button
        $keyboard[] = [
            ['text' => '⬅️ Назад к категориям', 'callback_data' => "category_{$backPrefix}"]
        ];
        
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>{$category['title']}</b>\n\nВыберите подкатегорию:", 
            $keyboard
        );
    }

    /**
     * Check if subcategory has sub-subcategories
     */
    private function hasSubSubcategories(int $categoryNumber, string $subcategory): bool
    {
        $subSubcategories = [
            2 => ['teh'], // Category 2: Technical solution has sub-subcategories
            3 => ['str'] // Category 3: Construction readiness has sub-subcategories
        ];
        
        return isset($subSubcategories[$categoryNumber]) && 
               in_array($subcategory, $subSubcategories[$categoryNumber]);
    }

    /**
     * Show sub-subcategories (Level 3)
     */
    private function handleIdleSubSubcategories(string $chatId, int $categoryNumber, string $subcategory, bool $isSupervision): ServerResponse
    {
        $prefix = $isSupervision ? 'Табель_ШМ_Простой' : 'Табель_Простой';
        
        $subSubcategories = [
            2 => [
                'teh' => [
                    'title' => 'Техническое решение',
                    'items' => [
                        'es' => 'Раздел ЭС',
                        'ath' => 'Раздел АТХ',
                        'ov' => 'Раздел ОВ',
                        'ps' => 'Раздел ПС/ОС',
                        'th' => 'Раздел ТХ',
                        'pki' => 'Поставщик ПКИ'
                    ]
                ]
            ],
            3 => [
                'str' => [
                    'title' => 'Отсутствие строительной готовности',
                    'items' => [
                        'es' => 'Раздел ЭС',
                        'ath' => 'Раздел АТХ',
                        'ov' => 'Раздел ОВ',
                        'ps' => 'Раздел ПС/ОС',
                        'th' => 'Раздел ТХ'
                    ]
                ]
            ]
        ];
        
        if (!isset($subSubcategories[$categoryNumber][$subcategory])) {
            return $this->messageService->sendMessage($chatId, 'Неизвестная подкатегория');
        }
        
        $subSubcategory = $subSubcategories[$categoryNumber][$subcategory];
        $keyboard = [];
        
        foreach ($subSubcategory['items'] as $key => $text) {
            $keyboard[] = [
                ['text' => $text, 'callback_data' => "category_{$prefix}_{$categoryNumber}_{$subcategory}_{$key}"]
            ];
        }
        
        // Add back button
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => "category_{$prefix}_{$categoryNumber}"]
        ];
        
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>{$subSubcategory['title']}</b>\n\nВыберите раздел:", 
            $keyboard
        );
    }

    /**
     * Handle final selection from idle menu
     */
    private function handleIdleFinalSelection(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Convert the detailed idle selection to a simple "Простой" activity type
        $activityType = str_contains($category, 'ШМ_') ? 'ШМ_Простой' : 'Простой';
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        // Store the detailed idle reason in conversation notes for confirmation
        $conversation->notes['idle_reason'] = $this->getIdleReasonFromCallback($category);
        $conversation->update();
        
        $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
        $confirmationText .= "\n\n<b>Причина простоя:</b> " . $conversation->notes['idle_reason'];
        
        $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
        
        return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
    }

    /**
     * Extract idle reason from callback data
     */
    private function getIdleReasonFromCallback(string $category): string
    {
        // Parse the callback to build a readable reason
        $parts = explode('_', str_replace('category_Табель_', '', $category));
        
        $categories = [
            '1' => 'Допоставка',
            '2' => 'Отсутствие обратной связи', 
            '3' => 'Готовность Заказчика',
            '4' => 'Транспорт',
            '5' => 'Погодные условия',
            '6' => 'Прочее'
        ];
        
        // Новые короткие ключи
        $subcategories = [
            // Категория 1: Допоставка
            'def' => 'Дефицит по спецификации',
            'ost' => 'Отсутствие в поставке',
            'izm' => 'Изменение ТР',
            // Категория 2: Отсутствие обратной связи
            'teh' => 'Техническое решение',
            'sog' => 'Согласование плана работ',
            // Категория 3: Готовность Заказчика
            'ene' => 'Отсутствие энергоресурсов',
            'rab' => 'Отсутствие рабочих сред',
            'per' => 'Перенос предъявления работ',
            'str' => 'Отсутствие строительной готовности',
            // Категория 4: Транспорт
            'pol' => 'Поломка',
            'mes' => 'Отсутствие свободных мест',
            // Категория 5: Погодные условия
            'bur' => 'Буран/снегопад',
            'mor' => 'Мороз',
            'ras' => 'Распутица',
            // Категория 6: Прочее
            'po' => 'Разработка ПО',
            'dop' => 'Отсутствие допуска к работам',
            'svy' => 'Отсутствие связи',
            'kom' => 'Свой комментарий',
            // Подподкатегории (разделы)
            'es' => 'Раздел ЭС',
            'ath' => 'Раздел АТХ',
            'ov' => 'Раздел ОВ',
            'ps' => 'Раздел ПС/ОС',
            'th' => 'Раздел ТХ',
            'pki' => 'Поставщик ПКИ'
        ];
        
        $reason = '';
        
        // Skip supervision prefix if present
        $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
        
        // Main category
        if (isset($parts[$startIndex]) && isset($categories[$parts[$startIndex]])) {
            $reason = $categories[$parts[$startIndex]];
        }
        
        // Subcategory
        if (isset($parts[$startIndex + 1]) && isset($subcategories[$parts[$startIndex + 1]])) {
            $reason .= ' → ' . $subcategories[$parts[$startIndex + 1]];
        }
        
        // Sub-subcategory
        if (isset($parts[$startIndex + 2]) && isset($subcategories[$parts[$startIndex + 2]])) {
            $reason .= ' → ' . $subcategories[$parts[$startIndex + 2]];
        }
        
        return $reason ?: 'Простой';
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