<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
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
        // Standard activity types
        'Дорога', 'Подготовка', 'Инструктаж', 'Ожидание', 'Гарантия', 'Простой',
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМ_Простой',
        'ШМР', 'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы',
        'АвансовыйОтчет',

        // New electrical equipment activity types (short format)
        'РабПоГр_ЭлектрОб',
        'ФормИД_ЭлектрОб',
        'ПодтВех_ЭлектрОб',
        'ИзмСхПд_ЭлектрОб',
        'МонтКбЛн_ЭлектрОб',
        'РаскКбЛн_ЭлектрОб',
        'Прочее_ЭлектрОб',
        // Automation equipment activity types
        'РабПоГр_Автом',
        'ФормИД_Автом',
        'ПодтВех_Автом',
        'ДопРаб_Автом',
        'ИзмСх_Автом',
        'МонтКб_Автом',
        'РаскКб_Автом',
        'МонтАСУ_Автом',
        'УдалПО_Автом',
        'Прочее_Автом',
        // Fire alarm activity types
        'РабПоГр_ПожСигн',
        'ФормИД_ПожСигн',
        'ПодтВех_ПожСигн',
        'ДопРаб_ПожСигн',
        'ИзмСх_ПожСигн',
        'МонтКб_ПожСигн',
        'РаскКб_ПожСигн',
        'МонтАСУ_ПожСигн',
        'УдалПО_ПожСигн',
        'Прочее_ПожСигн'
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
        // Add detailed logging
        error_log("TabelHandler: canHandle called with callbackData: '{$callbackData}'");
        
        // Special handling for electrical equipment callback with encoding issues
        // Check for both correctly encoded and corrupted versions
        if (strpos($callbackData, 'Электрооборудование') !== false || 
            strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
            strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
            strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
            error_log("TabelHandler: canHandle detected electrical equipment callback");
            return true;
        }
        
        // Check for both original and short callback formats
        $result = strpos($callbackData, 'category_Табель') === 0 || strpos($callbackData, 'cat_Таб') === 0;
        error_log("TabelHandler: canHandle result for '{$callbackData}': " . ($result ? 'true' : 'false'));
        return $result;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $userFirstName = $callbackQuery->getFrom()->getFirstName();
        $callbackData = $callbackQuery->getData();
        
        // Add detailed logging
        error_log("TabelHandler: handle called with category: '{$category}' and callbackData: '{$callbackData}'");
        
        // Special handling for electrical equipment with encoding issues
        if (strpos($callbackData, 'Электрооборудование') !== false || 
            strpos($callbackData, 'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') !== false ||
            strpos($callbackData, 'category_Табель_Электрооборудование') === 0 ||
            strpos($callbackData, 'category_РўР°Р±РµР»СЊ_Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') === 0) {
            error_log("TabelHandler: handle - detected electrical equipment callback, showing menu");
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        // Extract the actual category from the callback data
        $actualCategory = $this->extractCategoryFromCallback($callbackData);
        error_log("TabelHandler: extracted actual category: '{$actualCategory}'");
        
        // If we couldn't extract a category, use the one passed in
        if (empty($actualCategory)) {
            $actualCategory = $category;
            error_log("TabelHandler: using passed category: '{$actualCategory}'");
        }
        
        // Handle main tabel menu
        if ($actualCategory === 'Табель') {
            return $this->handleMainMenu($chatId, $userId);
        }

        // Handle electrical equipment menu with short callback format
        if ($actualCategory === 'Табель_Электрооборудование' || 
            $actualCategory === 'cat_ТабЭлОб' || 
            $actualCategory === 'cat_ТабЭлОб_Наз') {
            error_log("TabelHandler: handle - handling electrical equipment menu with short callback");
            return $this->handleElectricalEquipmentMenu($chatId);
        }

        // Handle specific electrical equipment activities BEFORE activity execution check
        // This ensures that 'cat_ТабЭлОб_РабПоГр' and similar callbacks are processed correctly
        if (strpos($actualCategory, 'Табель_РабПоГр_ЭлектрОб') === 0 ||
            $callbackData === 'cat_ТабЭлОб_РабПоГр' ||
            strpos($actualCategory, 'Табель_ФормИД_ЭлектрОб') === 0 ||
            $callbackData === 'cat_ТабЭлОб_ФормИД' ||
            strpos($actualCategory, 'Табель_ПодтВех_ЭлектрОб') === 0 ||
            $callbackData === 'cat_ТабЭлОб_ПодтВех' ||
            $callbackData === 'cat_ТабЭлОб_Проч' || // Added this line to handle "Прочее" button
            // Handle specific fire alarm equipment activities BEFORE activity execution check
            // This ensures that 'cat_ТабПожСигн_РабПоГр' and similar callbacks are processed correctly
            strpos($actualCategory, 'Табель_РабПоГр_ПожСигн') === 0 ||
            $callbackData === 'cat_ТабПожСигн_РабПоГр' ||
            strpos($actualCategory, 'Табель_ФормИД_ПожСигн') === 0 ||
            $callbackData === 'cat_ТабПожСигн_ФормИД' ||
            strpos($actualCategory, 'Табель_ПодтВех_ПожСигн') === 0 ||
            $callbackData === 'cat_ТабПожСигн_ПодтВех' ||
            $callbackData === 'cat_ТабПожСигн_Проч' ||
            $callbackData === 'cat_ТабПожСигн_ДопРаб') {
            error_log("TabelHandler: handle - handling specific electrical/fire alarm equipment activity before activity execution check");
            return $this->handleOtherWorkTypes($chatId, $actualCategory, $conversation);
        }
        
        // Handle specific electrical equipment confirmation activities
        // This ensures that 'cat_ТабЭлОб_РабПоГр_yes' and similar callbacks are processed correctly
        if ($callbackData === 'cat_ТабЭлОб_РабПоГр_yes' ||
            $callbackData === 'cat_ТабЭлОб_ФормИД_yes' ||
            $callbackData === 'cat_ТабЭлОб_ИзмСхПд_yes' ||
            $callbackData === 'cat_ТабЭлОб_МонтКбЛн_yes' ||
            $callbackData === 'cat_ТабЭлОб_РаскКбЛн_yes' ||
            $callbackData === 'cat_ТабЭлОб_ДопРаб_yes' ||
            $callbackData === 'cat_ТабЭлОб_ПодтВех_yes' ||
            $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
            $callbackData === 'cat_ТабПожСигн_РабПоГр_yes' ||
            $callbackData === 'cat_ТабПожСигн_ФормИД_yes' ||
            $callbackData === 'cat_ТабПожСигн_ИзмСх_yes' ||
            $callbackData === 'cat_ТабПожСигн_МонтКб_yes' ||
            $callbackData === 'cat_ТабПожСигн_РаскКб_yes' ||
            $callbackData === 'cat_ТабПожСигн_МонтАСУ_yes' ||
            $callbackData === 'cat_ТабПожСигн_УдалПО_yes' ||
            $callbackData === 'cat_ТабПожСигн_Проч_yes' ||
            $callbackData === 'cat_ТабПожСигн_ДопРаб_yes' ||
            $callbackData === 'cat_ТабПожСигн_ПодтВех_yes' ||
            $actualCategory === 'Табель_РабПоГр_ЭлектрОб_yes' ||
            $actualCategory === 'Табель_ФормИД_ЭлектрОб_yes' ||
            $actualCategory === 'Табель_ИзмСхПд_ЭлектрОб_yes' ||
            $actualCategory === 'Табель_МонтКбЛн_ЭлектрОб_yes' ||
            $actualCategory === 'Табель_РаскКбЛн_ЭлектрОб_yes' ||
            $actualCategory === 'Табель_ДопРаб_ЭлектрОб_yes' ||
            $actualCategory === 'Табель_ПодтВех_ЭлектрОб_yes' ||
            $actualCategory === 'Табель_Прочее_ЭлектрОб_yes') {
            error_log("TabelHandler: handle - handling specific equipment confirmation activity");
            // These are activity execution callbacks, so we should handle them as such
            if ($this->isActivityExecution($callbackData)) {
                error_log("TabelHandler: handle - isActivityExecution returned true for: {$callbackData}");
                return $this->handleActivityExecution($callbackQuery, $callbackData, $conversation);
            } else {
                error_log("TabelHandler: handle - isActivityExecution returned false for: {$callbackData}");
                // If isActivityExecution returns false, fall back to handleOtherWorkTypes
                return $this->handleOtherWorkTypes($chatId, $callbackData, $conversation);
            }
        }

        

        // Date selection flows
        if ($this->projectSelector->isDateSelection($actualCategory)) {
            error_log("TabelHandler: handle - handling date selection");
            return $this->handleDateSelection($callbackQuery, $actualCategory, $conversation);
        }

        // Project selection
        if ($this->projectSelector->isProjectSelection($actualCategory)) {
            error_log("TabelHandler: handle - handling project selection");
            return $this->handleProjectSelection($callbackQuery, $actualCategory, $conversation);
        }

        // Work type selection
        if ($actualCategory === 'Табель_Мобильная_Бригада_Дата') {
            error_log("TabelHandler: handle - handling mobile brigade date selection menu");
            return $this->handleDateSelectionMenu($chatId);
        }

        // TKP preparation menu
        if ($actualCategory === 'Табель_ПодготовкаТКП_Меню') {
            error_log("TabelHandler: handle - handling TKP subtypes menu");
            return $this->handleTKPSubtypesMenu($chatId);
        }


        
        // Back to commissioning menu
        if ($actualCategory === 'Табель_Пусконаладка_Back') {
            error_log("TabelHandler: handle - handling back to commissioning");
            return $this->handleBackToCommissioning($chatId, $conversation);
        }
        
        // Back to supervision menu
        if ($actualCategory === 'Табель_ШМ_Back') {
            error_log("TabelHandler: handle - handling back to supervision");
            return $this->handleBackToSupervision($chatId, $conversation);
        }

        // Handle Простой (Idle/Downtime) - простая обработка без детального меню
        if ($this->isIdleMenuCallback($actualCategory)) {
            error_log("TabelHandler: handle - handling idle menu callback");
            return $this->handleActivityConfirmation($callbackQuery, $actualCategory, $conversation);
        }

        
        // Activity execution
        if ($this->isActivityExecution($actualCategory)) {
            error_log("TabelHandler: handle - handling activity execution");
            return $this->handleActivityExecution($callbackQuery, $actualCategory, $conversation);
        }

        // Activity confirmation
        if ($this->isActivityConfirmation($actualCategory)) {
            error_log("TabelHandler: handle - handling activity confirmation");
            return $this->handleActivityConfirmation($callbackQuery, $actualCategory, $conversation);
        }

        // Other work types
        error_log("TabelHandler: handle - handling other work types");
        return $this->handleOtherWorkTypes($chatId, $actualCategory, $conversation);
    }

    /**
     * Extract category from callback data for both original and short formats
     */
    private function extractCategoryFromCallback(string $callbackData): string
    {
        // Handle original category format
        if (strpos($callbackData, 'category_') === 0) {
            return substr($callbackData, strlen('category_'));
        }
        
        // Handle short category format for electrical equipment
        if (strpos($callbackData, 'cat_Таб') === 0) {
            // Map short formats to full category names
            $shortFormats = [
                'cat_ТабЭлОб' => 'Табель_Электрооборудование',
                'cat_ТабЭлОб_Наз' => 'Табель_Электрооборудование',
                'cat_ТабЭлОб_РабПоГр' => 'Табель_РабПоГр_ЭлектрОб',
                'cat_ТабЭлОб_ФормИД' => 'Табель_ФормИД_ЭлектрОб',
                'cat_ТабЭлОб_ИзмСхПд' => 'Табель_ИзмСхПд_ЭлектрОб',
                'cat_ТабЭлОб_МонтКбЛн' => 'Табель_МонтКбЛн_ЭлектрОб',
                'cat_ТабЭлОб_РаскКбЛн' => 'Табель_РаскКбЛн_ЭлектрОб',
                'cat_ТабЭлОб_ДопРаб' => 'Табель_ДопРаб_ЭлектрОб',
                'cat_ТабЭлОб_ПодтВех' => 'Табель_ПодтВех_ЭлектрОб',
                'cat_ТабЭлОб_Проч' => 'Табель_Прочее_ЭлектрОб',
                'cat_ТабПожСигн_Наз' => 'Табель_ПожарнаяСигнализация',
                'cat_ТабПожСигн_РабПоГр' => 'Табель_РабПоГр_ПожСигн',
                'cat_ТабПожСигн_ФормИД' => 'Табель_ФормИД_ПожСигн',
                'cat_ТабПожСигн_ИзмСх' => 'Табель_ИзмСх_ПожСигн',
                'cat_ТабПожСигн_МонтКб' => 'Табель_МонтКб_ПожСигн',
                'cat_ТабПожСигн_РаскКб' => 'Табель_РаскКб_ПожСигн',
                'cat_ТабПожСигн_МонтАСУ' => 'Табель_МонтАСУ_ПожСигн',
                'cat_ТабПожСигн_УдалПО' => 'Табель_УдалПО_ПожСигн',
                'cat_ТабПожСигн_Проч' => 'Табель_Прочее_ПожСигн',
                'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн',
                'cat_ТабПожСигн_ПодтВех' => 'Табель_ПодтВех_ПожСигн',
                // Handle confirmation callbacks as well
                'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
                'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes',
                'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
                'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ЭлектрОб_yes',
                'cat_ТабЭлОб_РаскКбЛн_yes' => 'Табель_РаскКбЛн_ЭлектрОб_yes',
                'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes',
                'cat_ТабЭлОб_ПодтВех_yes' => 'Табель_ПодтВех_ЭлектрОб_yes',
                'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes',
                'cat_ТабПожСигн_РабПоГр_yes' => 'Табель_РабПоГр_ПожСигн_yes',
                'cat_ТабПожСигн_ФормИД_yes' => 'Табель_ФормИД_ПожСигн_yes',
                'cat_ТабПожСигн_ИзмСх_yes' => 'Табель_ИзмСх_ПожСигн_yes',
                'cat_ТабПожСигн_МонтКб_yes' => 'Табель_МонтКб_ПожСигн_yes',
                'cat_ТабПожСигн_РаскКб_yes' => 'Табель_РаскКб_ПожСигн_yes',
                'cat_ТабПожСигн_МонтАСУ_yes' => 'Табель_МонтАСУ_ПожСигн_yes',
                'cat_ТабПожСигн_УдалПО_yes' => 'Табель_УдалПО_ПожСигн_yes',
                'cat_ТабПожСигн_Проч_yes' => 'Табель_Прочее_ПожСигн_yes',
                'cat_ТабПожСигн_ДопРаб_yes' => 'Табель_ДопРаб_ПожСигн_yes',
                'cat_ТабПожСигн_ПодтВех_yes' => 'Табель_ПодтВех_ПожСигн_yes'
            ];
            
            if (isset($shortFormats[$callbackData])) {
                return $shortFormats[$callbackData];
            }
            
            // Handle other short formats as needed
            return substr($callbackData, strlen('cat_'));
        }
        
        return '';
    }

    public function getPriority(): int
    {
        return 20; // Same as before, higher than ProfileHandler (15) and FunctionalDirectionHandler (10)
    }

    private function isConfirmationCallback(string $category): bool
    {
        return str_contains($category, '_yes') && str_contains($category, 'Табель_');
    }

    private function isMainTabelMenu(string $category): bool
    {
        return in_array($category, ['Табель', 'Табель_Мобильная_Бригада_Дата']);
    }

    /**
     * Check if this is an electrical equipment menu category
     */
    private function isElectricalEquipmentMenu(string $category): bool
    {
        // List of electrical equipment menu categories that should not require FIO/functional direction
        $electricalEquipmentMenus = [
            'Табель_Электрооборудование',
            'Табель_РабПоГр_ЭлектрОб',
            'Табель_ФормИД_ЭлектрОб',
            'Табель_ИзмСхПд_ЭлектрОб',
            'Табель_МонтКбЛн_ЭлектрОб',
            'Табель_РаскКбЛн_ЭлектрОб',
            'Табель_ДопРаб_ЭлектрОб',
            'Табель_ПодтВех_ЭлектрОб',
            'Табель_Прочее_ЭлектрОб'
        ];
        
        return in_array($category, $electricalEquipmentMenus);
    }

    private function routeTabelCallback(string $passedCategory, int $chatId, int $userId, string $userFirstName): ServerResponse
    {
        // Extract the actual category from the callback data
        $callbackData = "category_{$passedCategory}";
        $category = $this->extractCategoryFromCallback($callbackData);
        
        // If we couldn't extract a category, use the one passed in
        if (empty($category)) {
            $category = $passedCategory;
        }
        
        error_log("TabelHandler: routeTabelCallback called with passedCategory: '{$passedCategory}', extracted category: '{$category}'");
        
        // Handle main tabel menu
        if ($category === 'Табель') {
            error_log("TabelHandler: routeTabelCallback: handling main tabel menu");
            return $this->handleMainMenu($chatId, $userId);
        }
        
        // Handle other work types submenu
        if ($this->isOtherWorkTypesSubmenu($category)) {
            error_log("TabelHandler: routeTabelCallback: handling other work types submenu for category: '{$category}'");
            return $this->handleOtherWorkTypesSubmenu($chatId, $category);
        }

        // Work type selection
        if ($category === 'Табель_Мобильная_Бригада_Дата') {
            error_log("TabelHandler: routeTabelCallback: handling mobile brigade date selection menu");
            return $this->handleDateSelectionMenu($chatId);
        }

        // TKP preparation menu
        if ($category === 'Табель_ПодготовкаТКП_Меню') {
            error_log("TabelHandler: routeTabelCallback: handling TKP subtypes menu");
            return $this->handleTKPSubtypesMenu($chatId);
        }


        
        // Back to commissioning menu
        if ($category === 'Табель_Пусконаладка_Back') {
            error_log("TabelHandler: routeTabelCallback: handling back to commissioning");
            // Create a mock conversation for this case
            $conversation = new Conversation($userId, $chatId);
            return $this->handleBackToCommissioning($chatId, $conversation);
        }
        
        // Back to supervision menu
        if ($category === 'Табель_ШМ_Back') {
            error_log("TabelHandler: routeTabelCallback: handling back to supervision");
            // Create a mock conversation for this case
            $conversation = new Conversation($userId, $chatId);
            return $this->handleBackToSupervision($chatId, $conversation);
        }

        // Handle Простой (Idle/Downtime) - простая обработка без детального меню
        if ($this->isIdleMenuCallback($category)) {
            error_log("TabelHandler: routeTabelCallback: handling idle menu callback");
            // Create a mock callback query and conversation for this case
            $callbackQuery = new CallbackQuery([]);
            $conversation = new Conversation($userId, $chatId);
            return $this->handleActivityConfirmation($callbackQuery, $category, $conversation);
        }

        
        // Activity execution
        if ($this->isActivityExecution($category)) {
            error_log("TabelHandler: routeTabelCallback: handling activity execution");
            // Create a mock callback query and conversation for this case
            $callbackQuery = new CallbackQuery([]);
            $conversation = new Conversation($userId, $chatId);
            return $this->handleActivityExecution($callbackQuery, $category, $conversation);
        }

        // Activity confirmation
        if ($this->isActivityConfirmation($category)) {
            error_log("TabelHandler: routeTabelCallback: handling activity confirmation");
            // Create a mock callback query and conversation for this case
            $callbackQuery = new CallbackQuery([]);
            $conversation = new Conversation($userId, $chatId);
            return $this->handleActivityConfirmation($callbackQuery, $category, $conversation);
        }

        // Other work types
        error_log("TabelHandler: routeTabelCallback: handling other work types");
        // Create a mock conversation for this case
        $conversation = new Conversation($userId, $chatId);
        return $this->handleOtherWorkTypes($chatId, $category, $conversation);
    }

    /**
     * Show main tabel menu
     */
    private function handleMainMenu(string $chatId, int $userId): ServerResponse
    {
        error_log("TabelHandler: handleMainMenu called for chatId: {$chatId}, userId: {$userId}");
        $keyboard = $this->messageService->createWorkTypeKeyboard();
        $text = '<b>📊 Табель</b> Выберите вид работ:';
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
        
        // Handle "Other date" selection
        if (str_contains($category, '_ДругаяДата')) {
            // For "Other date", we need to ask the user to input a date
            $conversation->notes['awaiting_date_input'] = true;
            $conversation->update();
            
            $text = "Пожалуйста, введите дату в формате ДД.ММ.ГГГГ (например, 25.12.2023):";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        if ($date) {
            $conversation->notes['tabel_data'] = $date;
            
            // Save if this is current activity flow
            if (str_contains($category, 'Пусконаладка') || str_contains($category, 'ПНЛ')) {
                // Для Пусконаладки сразу переходим к выбору проекта
                $conversation->notes['is_commissioning'] = true;
                $conversation->notes['is_supervision'] = false;
                $conversation->update();
                
                $text = "<b>⚙️ Табель Пусконаладка за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } elseif (str_contains($category, 'Шеф-монтаж') || str_contains($category, 'ШМ')) {
                // Для Шеф-монтажа сразу переходим к выбору проекта
                $conversation->notes['is_supervision'] = true;
                $conversation->notes['is_commissioning'] = false;
                $conversation->update();
                
                $text = "<b>🔧 Табель Шеф-монтаж за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } elseif (str_contains($category, 'Мобильная_Бригада') || str_contains($category, 'МобБриг')) {
                // Для мобильной бригады сбрасываем флаги
                $conversation->notes['is_current_activity'] = false;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_supervision'] = false;
                $conversation->update();
                
                $text = "<b>Табель за {$date}, выбор проекта</b>";
                
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } else {
                // Для мобильной бригады сбрасываем флаги
                $conversation->notes['is_current_activity'] = false;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_supervision'] = false;
                $conversation->update();
                
                $text = "<b>Табель за {$date}, выбор проекта</b>";
                
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
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
            
            // Check if this is project activity flow
            $isProjectActivity = isset($conversation->notes['is_project_activity']) && $conversation->notes['is_project_activity'];
            
            // Check if this is commissioning flow
            $isCommissioning = isset($conversation->notes['is_commissioning']) && $conversation->notes['is_commissioning'];
            
            // Check if this is supervision flow
            $isSupervision = isset($conversation->notes['is_supervision']) && $conversation->notes['is_supervision'];
            
            if ($isProjectActivity) {
                $keyboard = $this->messageService->createCommissioningWorksKeyboardWithMobileActivities();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>📂 Табель для проекта {$projectCode}</b>",
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
                $keyboard = $this->messageService->createMobileBrigadeKeyboard();
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
        // Categories that should show submenus instead of confirmation
        $submenuCategories = [
            'Метрология',
            'КомплОпробование',
            'РежимнаяНаладка'
        ];
        
        // Check standard activity types
        foreach ($this->activityTypes as $activity) {
            // Skip submenu categories
            if (in_array($activity, $submenuCategories)) {
                continue;
            }
            
            if ($category === "Табель_{$activity}") {
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
        
        error_log("TabelHandler: handleActivityConfirmation called with category: {$category}");
        
        // Extract activity type from category
        $activityType = '';
        if (preg_match('/^Табель_(.+)$/', $category, $matches)) {
            $activityType = $matches[1];
        } else {
            // Fallback for old method
            $activityType = str_replace('Табель_', '', $category);
        }
        
        error_log("TabelHandler: handleActivityConfirmation extracted activityType: {$activityType}");
        
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
        $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
        
        return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
    }

    private function isActivityExecution(string $category): bool
    {
        error_log("TabelHandler: isActivityExecution called with category: {$category}");
        
        // Check standard activity execution
        if (str_contains($category, '_yes')) {
            // Handle short callback format for electrical equipment first (this should come before other checks)
            if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
                // Handle short callback format for electrical equipment
                $shortActivityKey = $matches[1];
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РабПоГр_ЭлектрОб',
                    'ФормИД' => 'ФормИД_ЭлектрОб',
                    'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
                    'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
                    'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
                    'ДопРаб' => 'ДопРаб_ЭлектрОб',
                    'ПодтВех' => 'ПодтВех_ЭлектрОб',
                    'Проч' => 'Прочее_ЭлектрОб'
                ];
                
                if (isset($shortToFullMap[$shortActivityKey])) {
                    $activityType = $shortToFullMap[$shortActivityKey];
                    $result = in_array($activityType, $this->activityTypes);
                    error_log("TabelHandler: isActivityExecution: short electrical category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                    return $result;
                }
            }
            
            // Handle short callback format for fire alarm equipment
            if (preg_match('/^cat_ТабПожСигн_(.+)_yes$/', $category, $matches)) {
                // Handle short callback format for fire alarm equipment
                $shortActivityKey = $matches[1];
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РабПоГр_ПожСигн',
                    'ФормИД' => 'ФормИД_ПожСигн',
                    'ИзмСх' => 'ИзмСх_ПожСигн',
                    'МонтКб' => 'МонтКб_ПожСигн',
                    'РаскКб' => 'РаскКб_ПожСигн',
                    'МонтАСУ' => 'МонтАСУ_ПожСигн',
                    'УдалПО' => 'УдалПО_ПожСигн',
                    'ДопРаб' => 'ДопРаб_ПожСигн',
                    'ПодтВех' => 'ПодтВех_ПожСигн',
                    'Проч' => 'Прочее_ПожСигн'
                ];
                
                if (isset($shortToFullMap[$shortActivityKey])) {
                    $activityType = $shortToFullMap[$shortActivityKey];
                    $result = in_array($activityType, $this->activityTypes);
                    error_log("TabelHandler: isActivityExecution: short fire alarm category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                    return $result;
                }
            }
            
            // Handle long callback format for electrical equipment
            if (preg_match('/^category_Табель_(.+)_yes$/', $category, $matches)) {
                $activityType = $matches[1];
                // Check if this is an electrical equipment activity with the new format
                if (in_array($activityType, [
                    'РабПоГр_ЭлектрОб',
                    'ФормИД_ЭлектрОб',
                    'ПодтВех_ЭлектрОб',
                    'ДопРаб_ЭлектрОб',
                    'ИзмСхПд_ЭлектрОб',
                    'МонтКбЛн_ЭлектрОб',
                    'РаскКбЛн_ЭлектрОб',
                    'Прочее_ЭлектрОб'
                ])) {
                    $result = in_array($activityType, $this->activityTypes);
                    error_log("TabelHandler: isActivityExecution: long electrical category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                    return $result;
                }
                // Check if this is an automation equipment activity
                if (in_array($activityType, [
                    'РабПоГр_Автом',
                    'ФормИД_Автом',
                    'ПодтВех_Автом',
                    'ДопРаб_Автом',
                    'ИзмСх_Автом',
                    'МонтКб_Автом',
                    'РаскКб_Автом',
                    'МонтАСУ_Автом',
                    'УдалПО_Автом',
                    'Прочее_Автом'
                ])) {
                    $result = in_array($activityType, $this->activityTypes);
                    error_log("TabelHandler: isActivityExecution: automation category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                    return $result;
                }
                // For other activity types (including current activities), check if they're in our activityTypes array
                $result = in_array($activityType, $this->activityTypes);
                error_log("TabelHandler: isActivityExecution: other category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                
                // Special handling for electrical equipment activities with underscore
                if (!$result && strpos($activityType, '_') !== false) {
                    $result = in_array($activityType, $this->activityTypes);
                    error_log("TabelHandler: isActivityExecution: checking compound activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                }
                
                return $result;
            }
            
            // Special handling for "Прочее" activities with custom input
            if (str_ends_with($category, '_Прочее_Электрооборудование_yes')) {
                $activityType = 'Прочее_ЭлектрОб';
                $result = in_array($activityType, $this->activityTypes);
                error_log("TabelHandler: isActivityExecution: custom input electrical category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                return $result;
            }
            
            // Extract the activity type from the category
            $pattern = '/^Табель_(.+)_yes$/';
            if (preg_match($pattern, $category, $matches)) {
                $activityType = $matches[1];
                // Check if this activity type is in our list
                $result = in_array($activityType, $this->activityTypes);
                error_log("TabelHandler: isActivityExecution: category={$category}, activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                
                // Special handling for electrical equipment activities with underscore
                if (!$result && strpos($activityType, '_') !== false) {
                    $result = in_array($activityType, $this->activityTypes);
                    error_log("TabelHandler: isActivityExecution: checking compound activityType={$activityType}, in_array=" . ($result ? 'true' : 'false'));
                }
                
                return $result;
            }
        }
        
        // Check idle menu execution (when user confirms idle activity with detailed reason)
        if (str_contains($category, '_yes') && 
            (str_contains($category, 'Табель_Простой_yes') || str_contains($category, 'Табель_ШМ_Простой_yes'))) {
            error_log("TabelHandler: isActivityExecution: idle menu execution detected");
            return true;
        }
        
        error_log("TabelHandler: isActivityExecution: returning false for category: {$category}");
        return false;
    }

    private function handleActivityExecution(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("TabelHandler: handleActivityExecution called with category: {$category}");
        
        // Extract activity type using regex
        $activityType = '';
        
        // Handle short callback format for electrical equipment FIRST (this should come before other checks)
        if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/', $category, $matches)) {
            // Handle short callback format for electrical equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_ЭлектрОб',
                'ФормИД' => 'ФормИД_ЭлектрОб',
                'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
                'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
                'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
                'ДопРаб' => 'ДопРаб_ЭлектрОб',
                'ПодтВех' => 'ПодтВех_ЭлектрОб',
                'Проч' => 'Прочее_ЭлектрОб'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            } else {
                // Fallback for old method
                $activityType = str_replace(['Табель_', '_yes'], ['', ''], $category);
            }
        }
        // Handle short callback format for fire alarm equipment (must come before standard handling)
        else if (preg_match('/^cat_ТабПожСигн_(.+)_yes$/', $category, $matches)) {
            // Handle short callback format for fire alarm equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_ПожСигн',
                'ФормИД' => 'ФормИД_ПожСигн',
                'ИзмСх' => 'ИзмСх_ПожСигн',
                'МонтКб' => 'МонтКб_ПожСигн',
                'РаскКб' => 'РаскКб_ПожСигн',
                'МонтАСУ' => 'МонтАСУ_ПожСигн',
                'УдалПО' => 'УдалПО_ПожСигн',
                'ДопРаб' => 'ДопРаб_ПожСигн',
                'ПодтВех' => 'ПодтВех_ПожСигн',
                'Проч' => 'Прочее_ПожСигн'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            } else {
                // Fallback for old method
                $activityType = str_replace(['Табель_', '_yes'], ['', ''], $category);
            }
        }
        // Handle long callback format for electrical equipment (must come before standard handling)
        else if (preg_match('/^category_Табель_(.+)_yes$/', $category, $matches)) {
            $activityType = $matches[1];
            // Check if this is an electrical equipment activity with the new format
            if (in_array($activityType, [
                'РабПоГр_ЭлектрОб',
                'ФормИД_ЭлектрОб',
                'ПодтВех_ЭлектрОб',
                'ДопРаб_ЭлектрОб',
                'ИзмСхПд_ЭлектрОб',
                'МонтКбЛн_ЭлектрОб',
                'РаскКбЛн_ЭлектрОб',
                'Прочее_ЭлектрОб'
            ])) {
                // This is already the correct activityType
                error_log("TabelHandler: handleActivityExecution: handling long format electrical equipment activity: {$activityType}");
            }
            // Check if this is an automation equipment activity
            else if (in_array($activityType, [
                'РабПоГр_Автом',
                'ФормИД_Автом',
                'ПодтВех_Автом',
                'ДопРаб_Автом',
                'ИзмСх_Автом',
                'МонтКб_Автом',
                'РаскКб_Автом',
                'МонтАСУ_Автом',
                'УдалПО_Автом',
                'Прочее_Автом'
            ])) {
                // This is already the correct activityType
                error_log("TabelHandler: handleActivityExecution: handling automation equipment activity: {$activityType}");
            }
            // For other activity types (including current activities), keep the extracted activityType
        }
        // Special handling for "Прочее" activities with custom input (must come before standard handling)
        else if (str_ends_with($category, '_Прочее_Электрооборудование_yes')) {
            $activityType = 'Прочее_ЭлектрОб';
        }
        // Special handling for electrical equipment activities (must come before standard handling)
        else if (preg_match('/^category_Табель_(.+)_yes$/', $category, $matches)) {
            $activityType = $matches[1];
            // Check if this is an electrical equipment activity
            if (in_array($activityType, [
                'РабПоГр_Электрооборудование',
                'ФормИД_Электрооборудование',
                'ПодтВех_Электрооборудование',
                'ДопРаб_Электрооборудование',
                'ИзмСх_Электрооборудование',
                'МонтКб_Электрооборудование',
                'РаскКб_Электрооборудование',
                'Прочее_Электрооборудование'
            ])) {
                // This is already the correct activityType
            }
            // Check if this is an automation equipment activity
            else if (in_array($activityType, [
                'РабПоГр_Автом',
                'ФормИД_Автом',
                'ПодтВех_Автом',
                'ДопРаб_Автом',
                'ИзмСх_Автом',
                'МонтКб_Автом',
                'РаскКб_Автом',
                'МонтАСУ_Автом',
                'УдалПО_Автом',
                'Прочее_Автом'
            ])) {
                // This is already the correct activityType
            }
            // For other activity types (including current activities), keep the extracted activityType
        } else if (preg_match('/^Табель_(.+)_yes$/', $category, $matches)) {
            $activityType = $matches[1];
        } else {
            // Fallback for old method
            $activityType = str_replace(['Табель_', '_yes'], ['', ''], $category);
        }
        
        error_log("TabelHandler: handleActivityExecution extracted activityType: {$activityType}");
        error_log("TabelHandler: handleActivityExecution activityType in activityTypes: " . (in_array($activityType, $this->activityTypes) ? 'true' : 'false'));
        
        // Check if the extracted activity type is valid
        if (!in_array($activityType, $this->activityTypes)) {
            error_log("TabelHandler: handleActivityExecution invalid activityType: {$activityType}");
            return $this->messageService->sendMessage($chatId, "Неизвестный тип деятельности: {$activityType}");
        }
        
        $userData = $this->databaseService->getUserData($userId);
        $fio = $userData['fio'] ?? 'Без ФИО';
        $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        // Get idle reason if available
        $idleReason = $conversation->notes['idle_reason'] ?? '';
        
        // Get custom input text if available (for "Прочее" activities)
        $customInputText = $conversation->notes['custom_input_text'] ?? '';
        
        // For "Прочее" activities, use the custom input as the activity description
        if (str_ends_with($activityType, '_ЭлектрОб') && str_starts_with($activityType, 'Прочее')) {
            // Add the custom input to the idle reason field for processing
            $idleReason = $customInputText;
        }
        // For "Прочее" activities in electrical equipment, use the custom input as the activity description
        if (str_ends_with($activityType, '_Электрооборудование') && str_starts_with($activityType, 'Прочее')) {
            // Add the custom input to the idle reason field for processing
            $idleReason = $customInputText;
        }
        // For "Прочее" activities in automation, use the custom input as the activity description
        if (str_ends_with($activityType, '_Автом') && str_starts_with($activityType, 'Прочее')) {
            // Add the custom input to the idle reason field for processing
            $idleReason = $customInputText;
        }
        
        // Special handling for electrical equipment activities
        if (strpos($activityType, '_') !== false && strpos($activityType, 'ЭлектрОб') !== false) {
            error_log("TabelHandler: handleActivityExecution: handling electrical equipment activity: {$activityType}");
        }
        
        // Create and send tabel entry
        $success = $this->tabelEntry->addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason);
        
        error_log("TabelHandler: handleActivityExecution addEntry result: " . ($success ? 'true' : 'false'));
        
        if ($success) {
            $message = $this->tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
            error_log("TabelHandler: handleActivityExecution created message: " . $message);
            // Clear conversation notes after successful entry
            $conversation->notes = [];
            $conversation->update();
            return $this->messageService->sendMessage($chatId, $message);
        }
        
        return $this->messageService->sendMessage($chatId, 'Ошибка при добавлении записи в табель');
    }

    private function handleOtherWorkTypes(string $chatId, string $category, Conversation $conversation): ServerResponse
    {
        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        
        error_log("TabelHandler: handleOtherWorkTypes called with category: {$category}");
        error_log("TabelHandler: handleOtherWorkTypes category length: " . strlen($category));
        error_log("TabelHandler: handleOtherWorkTypes category hex: " . bin2hex($category));
        
        // Handle electrical equipment menu with new short callback format
        if ($category === 'Табель_Электрооборудование' || 
            $category === 'cat_ТабЭлОб' ||
            $category === 'cat_ТабЭлОб_Наз') {
            error_log("TabelHandler: handleOtherWorkTypes: handling electrical equipment menu");
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        // Handle works by schedule for electrical equipment
        if ($category === 'Табель_РабПоГр_ЭлектрОб' || 
            $category === 'cat_ТабЭлОб_РабПоГр') {
            error_log("TabelHandler: handleOtherWorkTypes: handling works by schedule for electrical equipment");
            $strippedCategory = 'РабПоГр_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            // Use short callback format for electrical equipment to maintain consistency
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle ID formation for electrical equipment
        if ($category === 'Табель_ФормИД_ЭлектрОб' || 
            $category === 'cat_ТабЭлОб_ФормИД') {
            error_log("TabelHandler: handleOtherWorkTypes: handling ID formation for electrical equipment");
            $strippedCategory = 'ФормИД_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            // Use short callback format for electrical equipment to maintain consistency
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle milestone confirmation for electrical equipment
        if ($category === 'Табель_ПодтВех_ЭлектрОб' || 
            $category === 'cat_ТабЭлОб_ПодтВех') {
            error_log("TabelHandler: handleOtherWorkTypes: handling milestone confirmation for electrical equipment");
            // Instead of showing confirmation dialog, show "в разработке" message
            $message = "в разработке";
            return $this->messageService->sendMessage($chatId, $message);
        }
        // Handle scheme changes for electrical equipment
        if ($category === 'Табель_ИзмСхПд_ЭлектрОб' || 
            $category === 'cat_ТабЭлОб_ИзмСхПд') {
            error_log("TabelHandler: handleOtherWorkTypes: handling scheme changes for electrical equipment");
            $strippedCategory = 'ИзмСхПд_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            // Use short callback format for electrical equipment to maintain consistency
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_ИзмСхПд_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle cable installation for electrical equipment
        if ($category === 'Табель_МонтКбЛн_ЭлектрОб' || 
            $category === 'cat_ТабЭлОб_МонтКбЛн') {
            error_log("TabelHandler: handleOtherWorkTypes: handling cable installation for electrical equipment");
            $strippedCategory = 'МонтКбЛн_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            // Use short callback format for electrical equipment to maintain consistency
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_МонтКбЛн_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle cable wiring for electrical equipment
        if ($category === 'Табель_РаскКбЛн_ЭлектрОб' || 
            $category === 'cat_ТабЭлОб_РаскКбЛн') {
            error_log("TabelHandler: handleOtherWorkTypes: handling cable wiring for electrical equipment");
            $strippedCategory = 'РаскКбЛн_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            // Use short callback format for electrical equipment to maintain consistency
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_РаскКбЛн_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle scheme changes for electrical equipment (from additional works menu)
        if ($category === 'cat_ТабЭлОб_ИзмСхПд') {
            error_log("TabelHandler: handleOtherWorkTypes: handling scheme changes for electrical equipment (from additional works)");
            $strippedCategory = 'ИзмСхПд_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            // Use short callback format for electrical equipment to maintain consistency
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_ИзмСхПд_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle cable installation for electrical equipment (from additional works menu)
        if ($category === 'cat_ТабЭлОб_МонтКбЛн') {
            error_log("TabelHandler: handleOtherWorkTypes: handling cable installation for electrical equipment (from additional works)");
            $strippedCategory = 'МонтКбЛн_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            // Use short callback format for electrical equipment to maintain consistency
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_МонтКбЛн_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle cable wiring for electrical equipment (from additional works menu)
        if ($category === 'cat_ТабЭлОб_РаскКбЛн') {
            error_log("TabelHandler: handleOtherWorkTypes: handling cable wiring for electrical equipment (from additional works)");
            $strippedCategory = 'РаскКбЛн_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            // Use short callback format for electrical equipment to maintain consistency
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_РаскКбЛн_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle additional works for electrical equipment
        if ($category === 'Табель_ДопРаб_ЭлектрОб' || 
            $category === 'cat_ТабЭлОб_ДопРаб') {
            error_log("TabelHandler: handleOtherWorkTypes: handling additional works for electrical equipment");
            return $this->handleAdditionalWorksElectricalMenu($chatId);
        }

        // Handle other works for electrical equipment - prompt for custom input
        if ($category === 'Табель_Прочее_ЭлектрОб' || 
            $category === 'cat_ТабЭлОб_Проч') {
            error_log("TabelHandler: handleOtherWorkTypes: handling other works for electrical equipment");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ЭлектрОб';
            $conversation->update();
            
            // Prompt user for custom input
            $text = "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Handle schedule works for automation equipment
        if ($category === 'Табель_РабПоГр_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling schedule works for automation equipment");
            $strippedCategory = 'РабПоГр_Автом';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle ID formation for automation equipment
        if ($category === 'Табель_ФормИД_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling ID formation for automation equipment");
            $strippedCategory = 'ФормИД_Автом';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle milestone confirmation for automation equipment
        if ($category === 'Табель_ПодтВех_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling milestone confirmation for automation equipment");
            // Instead of showing confirmation dialog, show "в разработке" message
            $message = "в разработке";
            return $this->messageService->sendMessage($chatId, $message);
        }

        // Handle additional works for automation equipment
        if ($category === 'Табель_ДопРаб_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling additional works for automation equipment");
            return $this->handleAdditionalWorksAutomationMenu($chatId);
        }

        // Handle scheme changes for automation equipment (from additional works menu)
        if ($category === 'Табель_ИзмСх_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling scheme changes for automation equipment (from additional works)");
            $strippedCategory = 'ИзмСх_Автом';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle cable installation for automation equipment (from additional works menu)
        if ($category === 'Табель_МонтКб_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling cable installation for automation equipment (from additional works)");
            $strippedCategory = 'МонтКб_Автом';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle cable wiring for automation equipment (from additional works menu)
        if ($category === 'Табель_РаскКб_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling cable wiring for automation equipment (from additional works)");
            $strippedCategory = 'РаскКб_Автом';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle ASUTP equipment installation for automation equipment (from additional works menu)
        if ($category === 'Табель_МонтАСУ_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling ASUTP equipment installation for automation equipment (from additional works)");
            $strippedCategory = 'МонтАСУ_Автом';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle remote commissioning for automation equipment (from additional works menu)
        if ($category === 'Табель_УдалПО_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling remote commissioning for automation equipment (from additional works)");
            $strippedCategory = 'УдалПО_Автом';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$strippedCategory}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works for automation equipment - prompt for custom input
        if ($category === 'Табель_Прочее_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling other works for automation equipment");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_Автом';
            $conversation->update();
            
            // Prompt user for custom input
            $text = "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Handle schedule works for fire alarm equipment
        if ($category === 'Табель_РабПоГр_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_РабПоГр') {
            error_log("TabelHandler: handleOtherWorkTypes: handling schedule works for fire alarm equipment");
            $strippedCategory = 'РабПоГр_ПожСигн';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle ID formation for fire alarm equipment
        if ($category === 'Табель_ФормИД_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_ФормИД') {
            error_log("TabelHandler: handleOtherWorkTypes: handling ID formation for fire alarm equipment");
            $strippedCategory = 'ФормИД_ПожСигн';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle milestone confirmation for fire alarm equipment
        if ($category === 'Табель_ПодтВех_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_ПодтВех') {
            error_log("TabelHandler: handleOtherWorkTypes: handling milestone confirmation for fire alarm equipment");
            // Instead of showing "в разработке" message, show additional works menu
            return $this->handleAdditionalWorksFireAlarmMenu($chatId);
        }

        // Handle additional works for fire alarm equipment
        if ($category === 'Табель_ДопРаб_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_ДопРаб') {
            error_log("TabelHandler: handleOtherWorkTypes: handling additional works for fire alarm equipment");
            return $this->handleAdditionalWorksFireAlarmMenu($chatId);
        }

        // Handle scheme changes for fire alarm equipment (from additional works menu)
        if ($category === 'Табель_ИзмСх_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_ИзмСх') {
            error_log("TabelHandler: handleOtherWorkTypes: handling scheme changes for fire alarm equipment (from additional works)");
            $strippedCategory = 'ИзмСх_ПожСигн';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_ИзмСх_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle cable installation for fire alarm equipment (from additional works menu)
        if ($category === 'Табель_МонтКб_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_МонтКб') {
            error_log("TabelHandler: handleOtherWorkTypes: handling cable installation for fire alarm equipment (from additional works)");
            $strippedCategory = 'МонтКб_ПожСигн';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_МонтКб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle cable wiring for fire alarm equipment (from additional works menu)
        if ($category === 'Табель_РаскКб_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_РаскКб') {
            error_log("TabelHandler: handleOtherWorkTypes: handling cable wiring for fire alarm equipment (from additional works)");
            $strippedCategory = 'РаскКб_ПожСигн';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_РаскКб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle ASUTP equipment installation for fire alarm equipment (from additional works menu)
        if ($category === 'Табель_МонтАСУ_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_МонтАСУ') {
            error_log("TabelHandler: handleOtherWorkTypes: handling ASUTP equipment installation for fire alarm equipment (from additional works)");
            $strippedCategory = 'МонтАСУ_ПожСигн';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_МонтАСУ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle remote commissioning for fire alarm equipment (from additional works menu)
        if ($category === 'Табель_УдалПО_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_УдалПО') {
            error_log("TabelHandler: handleOtherWorkTypes: handling remote commissioning for fire alarm equipment (from additional works)");
            $strippedCategory = 'УдалПО_ПожСигн';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_УдалПО_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works for fire alarm equipment - prompt for custom input
        if ($category === 'Табель_Прочее_ПожарнаяСигнализация' || 
            $category === 'cat_ТабПожСигн_Проч') {
            error_log("TabelHandler: handleOtherWorkTypes: handling other works for fire alarm equipment");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ПожСигн';
            $conversation->update();
            
            // Prompt user for custom input
            $text = "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Handle back button from additional works electrical equipment menu
        if ($category === 'Табель_ЭлектрОб') {
            error_log("TabelHandler: handleOtherWorkTypes: handling back button from additional works electrical equipment menu");
            return $this->handleElectricalEquipmentMenu($chatId);
        }

        // Handle back button from electrical equipment menu
        if ($category === 'Табель_ПусконаладочныеРаботы') {
            error_log("TabelHandler: handleOtherWorkTypes: handling back button from electrical equipment menu");
            return $this->handleCommissioningWorksMenu($chatId);
        }
        
        // Handle electrical equipment menu - This check must come BEFORE other checks
        // Use strict comparison and also check for exact match after 'Табель_'
        if ($category === 'Табель_Электрооборудование' || 
            (strpos($category, 'Табель_') === 0 && substr($category, 8) === 'Электрооборудование')) {
            error_log("TabelHandler: handleOtherWorkTypes: handling electrical equipment menu");
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        // Additional check for electrical equipment in category
        if (strpos($category, 'Электрооборудование') !== false) {
            error_log("TabelHandler: handleOtherWorkTypes: category contains электрооборудование: {$category}");
            // If it contains электрооборудование, handle it as electrical equipment
            if (strpos($category, 'Табель_Электрооборудование') !== false || $category === 'Электрооборудование') {
                error_log("TabelHandler: handleOtherWorkTypes: treating as electrical equipment category");
                return $this->handleElectricalEquipmentMenu($chatId);
            }
        }
        
        // Handle automation equipment menu
        if ($category === 'Табель_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling automation equipment menu");
            return $this->handleAutomationEquipmentMenu($chatId);
        }
        
        // Handle additional works for automation equipment
        if ($category === 'Табель_ДополнительныеРаботы_Автоматизация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling additional works for automation equipment");
            return $this->handleAdditionalWorksAutomationMenu($chatId);
        }
        
        // Handle back button from additional works automation equipment menu
        if ($category === 'Табель_Автоматизация_Back') {
            error_log("TabelHandler: handleOtherWorkTypes: handling back button from additional works automation equipment menu");
            return $this->handleAutomationEquipmentMenu($chatId);
        }
        
        // Handle fire alarm equipment menu
        if ($category === 'Табель_ПожарнаяСигнализация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling fire alarm equipment menu");
            return $this->handleFireAlarmEquipmentMenu($chatId);
        }
        
        // Handle additional works for fire alarm equipment
        if ($category === 'Табель_ДополнительныеРаботы_ПожарнаяСигнализация') {
            error_log("TabelHandler: handleOtherWorkTypes: handling additional works for fire alarm equipment");
            return $this->handleAdditionalWorksFireAlarmMenu($chatId);
        }
        
        // Handle back button from additional works fire alarm equipment menu
        if ($category === 'Табель_ПожарнаяСигнализация_Back' || 
            $category === 'cat_ТабПожСигн_Наз') {
            error_log("TabelHandler: handleOtherWorkTypes: handling back button from additional works fire alarm equipment menu");
            return $this->handleFireAlarmEquipmentMenu($chatId);
        }
        
        if ($category === 'Табель_Пусконаладка') {
            error_log("TabelHandler: handleOtherWorkTypes: handling commissioning date selection");
            return $this->handleCommissioningDateSelection($chatId);
        }
        
        if ($category === 'Табель_Шеф_монтаж') {
            error_log("TabelHandler: handleOtherWorkTypes: handling supervision date selection");
            return $this->handleSupervisionDateSelection($chatId);
        }
        
        // Handle Mobile Brigade
        if ($category === 'Табель_Мобильная_Бригада') {
            error_log("TabelHandler: handleOtherWorkTypes: handling mobile brigade date selection");
            return $this->handleMobileBrigadeDateSelection($chatId);
        }
        

        
        // Handle commissioning works menu
        if ($category === 'Табель_ПусконаладочныеРаботы') {
            error_log("TabelHandler: handleOtherWorkTypes: handling commissioning works menu");
            return $this->handleCommissioningWorksMenu($chatId);
        }
        
        // Categories that should show submenus instead of confirmation
        $submenuCategories = [
            'Метрология',
            'КомплОпробование',
            'РежимнаяНаладка'
        ];
        
        // Handle work type buttons that appear after date and project selection
        // These are the activity types that should be confirmed
        $strippedCategory = str_replace('Табель_', '', $category);
        error_log("TabelHandler: handleOtherWorkTypes: stripped category: {$strippedCategory}");
        error_log("TabelHandler: handleOtherWorkTypes: stripped category in activityTypes: " . (in_array($strippedCategory, $this->activityTypes) ? 'true' : 'false'));
        
        // Only treat as activity confirmation if it's not a submenu category
        if (in_array($strippedCategory, $this->activityTypes) && !in_array($strippedCategory, $submenuCategories)) {
            error_log("TabelHandler: handleOtherWorkTypes: handling activity type confirmation");
            // This is a work type selection, treat it as activity confirmation
            // We need to create a proper callback_data format for confirmation
            $activityType = $strippedCategory;
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
            
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        error_log("TabelHandler: handleOtherWorkTypes: unknown command");
        return $this->messageService->sendMessage($chatId, 'Неизвестная команда табеля');
    }

    /**
     * Handle custom input for "Прочее" activities
     */
    public function handleCustomInput(string $chatId, string $userInput, Conversation $conversation): ServerResponse
    {
        // Check if we're waiting for custom input
        if (!isset($conversation->notes['awaiting_custom_input']) || !$conversation->notes['awaiting_custom_input']) {
            // Not waiting for custom input, return normal handling
            return $this->messageService->sendMessage($chatId, 'Неизвестная команда табеля');
        }
        
        // Get the activity type for which we're collecting custom input
        $activityType = $conversation->notes['custom_input_activity_type'] ?? '';
        
        if (empty($activityType)) {
            return $this->messageService->sendMessage($chatId, 'Ошибка: не удалось определить тип активности');
        }
        
        // Store the custom input in conversation notes
        $conversation->notes['custom_input_text'] = $userInput;
        $conversation->notes['awaiting_custom_input'] = false;
        $conversation->update();
        
        // Show confirmation message with the custom input
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
        $confirmationText .= "\n\n<b>Описание работы:</b> " . $userInput;
        
        // Use short callback format for electrical equipment to maintain consistency
        if ($activityType === 'Прочее_ЭлектрОб') {
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_Проч_yes");
        } else if ($activityType === 'Прочее_Автоматизация') {
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
        } else {
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
        }
        return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
    }

    /**
     * Handle custom input for dates
     */
    public function handleCustomDateInput(string $chatId, string $userInput, Conversation $conversation): ServerResponse
    {
        // Validate date format (DD.MM.YYYY)
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $userInput)) {
            // Validate that it's a real date
            $dateParts = explode('.', $userInput);
            if (checkdate((int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2])) {
                // Valid date, proceed with normal flow
                $conversation->notes['tabel_data'] = $userInput;
                $conversation->notes['awaiting_date_input'] = false;
                $conversation->update();
                
                // Continue with project selection
                $text = "<b>Табель за {$userInput}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard();
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
        }
        
        // Invalid date format, ask again
        $text = "Неверный формат даты. Пожалуйста, введите дату в формате ДД.ММ.ГГГГ (например, 25.12.2023):";
        return $this->messageService->sendMessage($chatId, $text);
    }

    /**
     * Handle mobile brigade date selection
     */
    private function handleMobileBrigadeDateSelection(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_МобБриг');
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
        $keyboard = $this->messageService->createMobileBrigadeKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>🔧 Табель - Шеф-монтаж для проекта {$projectCode}</b>",
            $keyboard
        );
    }

    /**
     * Handle commissioning works menu
     */
    private function handleCommissioningWorksMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createCommissioningWorksKeyboardWithMobileActivities();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>⚙️ Пусконаладочные работы:</b> Выберите тип оборудования или активность:',
            $keyboard
        );
    }

    /**
     * Handle electrical equipment menu
     */
    private function handleElectricalEquipmentMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createElectricalEquipmentKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>⚡ Электрооборудование</b> Выберите тип работ:',
            $keyboard
        );
    }

    /**
     * Handle additional works for electrical equipment
     */
    private function handleAdditionalWorksElectricalMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createAdditionalWorksElectricalKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🛠️ Дополнительные работы - Электрооборудование</b> Выберите тип дополнительных работ:',
            $keyboard
        );
    }

    /**
     * Handle automation equipment menu
     */
    private function handleAutomationEquipmentMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createAutomationEquipmentKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🤖 Автоматизация</b> Выберите тип работ:',
            $keyboard
        );
    }

    /**
     * Handle fire alarm equipment menu
     */
    private function handleFireAlarmEquipmentMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createFireAlarmEquipmentKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🔥 Пожарная сигнализация</b> Выберите тип работ:',
            $keyboard
        );
    }

    /**
     * Handle additional works for automation equipment
     */
    private function handleAdditionalWorksAutomationMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createAdditionalWorksAutomationKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🛠️ Дополнительные работы - Автоматизация</b> Выберите тип дополнительных работ:',
            $keyboard
        );
    }

    /**
     * Handle additional works for fire alarm equipment
     */
    private function handleAdditionalWorksFireAlarmMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createAdditionalWorksFireAlarmKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🛠️ Дополнительные работы - Пожарная сигнализация</b> Выберите тип дополнительных работ:',
            $keyboard
        );
    }

    /**
     * Handle address selection
     */
    private function handleAddressSelection(string $chatId): ServerResponse
    {
        // Используем короткий префикс для избежания ограничения в 64 байта
        $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_Адрес');
        return $this->messageService->sendMessage($chatId, '📍 <b>Укажите адрес</b>', $keyboard);
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
                ['text' => ' Респект Готовность Заказчика', 'callback_data' => "category_{$prefix}_3"]
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
        return $this->messageService->sendMessage($chatId, "<b>{$title}</b> Выберите причину простоя:", $keyboard);
    }

    /**
     * Show subcategories for a specific main category (Level 2)
     */
    private function handleIdleSubcategories(string $chatId, int $categoryNumber, bool $isSupervision): ServerResponse
    {
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
            return $this->messageService->sendMessage($chatId, 'Неизвестная категория');
        }
        
        $category = $categories[$categoryNumber];
        $keyboard = [];
        
        foreach ($category['items'] as $key => $text) {
            $callbackData = "category_{$prefix}_{$categoryNumber}_{$key}";
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
            "<b>{$category['title']}</b> Выберите подкатегорию:", 
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
            "<b>{$subSubcategory['title']}</b> Выберите раздел:", 
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
     * Check if this is an other work types submenu
     */
    private function isOtherWorkTypesSubmenu(string $category): bool
    {
        // Categories that have submenus
        $submenuCategories = [
            'Метрология',
            'КомплОпробование',
            'РежимнаяНаладка'
        ];
        
        return in_array(str_replace('Табель_', '', $category), $submenuCategories);
    }

    /**
     * Handle other work types submenu
     */
    private function handleOtherWorkTypesSubmenu(string $chatId, string $category): ServerResponse
    {
        error_log("TabelHandler: handleOtherWorkTypesSubmenu called for category: {$category}");
        
        // Handle specific submenus
        switch ($category) {
            // Add other submenu cases as needed
            default:
                $text = 'Неизвестное подменю';
                $text = $this->ensureUtf8Encoding($text);
                return $this->messageService->sendMessage($chatId, $text);
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