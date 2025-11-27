<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Handlers;

use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerInterface;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Message;
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
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
        'ПНР', 'УЗ_РК', 'РабГарантии', 'ШМР', 'ШМ_Простой',
        'ШМ_УЗ_РК', 'ШМ_РабГарантии',
        'МедОсмотр', 'Аттестация', 'Общая',
        'Электромонтажные', 'Сварочные', 'Слесарные', 'Теплоизоляция', 'Документы', 'АвансовыйОтчет',
        // Mobile brigade activity types
        'Мобильная бригада',
        // Electrical equipment activity types
        'РабПоГр_ЭлектрОб', 'ФормИД_ЭлектрОб', 'ПодтВех_ЭлектрОб',
        'ИзмСхПд_ЭлектрОб', 'МонтКбЛн_ЭлектрОб', 'РаскКбЛн_ЭлектрОб', 'МонтОб_ЭлектрОб', 'Прочее_ЭлектрОб',
        // Electrical equipment main category
        'Электрооборудование',
        // Automation equipment activity types
        'РабПоГр_Автом', 'ФормИД_Автом', 'ПодтВех_Автом',
        // 'ДопРаб_Автом', // Removed - this is a submenu, not direct activity
        'ИзмСх_Автом', 'МонтКб_Автом', 'РаскКб_Автом',
        'МонтАСУ_Автом', 'УдалПО_Автом', 'Прочее_Автом',
        // Fire alarm equipment activity types
        'РабПоГр_ПожСигн', 'ФормИД_ПожСигн', 'ПодтВех_ПожСигн',
        // 'ДопРаб_ПожСигн', // Removed - this is a submenu, not direct activity
        'ИзмСх_ПожСигн', 'МонтКб_ПожСигн', 'РаскКб_ПожСигн',
        'МонтОб_ПожСигн', 'Прочее_ПожСигн',
        // Security alarm equipment activity types
        'РабПоГр_ОхрСигн', 'ФормИД_ОхрСигн', 'ПодтВех_ОхрСигн',
        // 'ДопРаб_ОхрСигн', // Removed - this is a submenu, not direct activity
        'Прочее_ОхрСигн',
        'ИзмСх_ОхрСигн', 'МонтКб_ОхрСигн', 'РаскКб_ОхрСигн', 'МонтОб_ОхрСигн',
        // Heating and ventilation equipment activity types
        'РабПоГр_ВентОтопл', 'ФормИД_ВентОтопл', 'ПодтВех_ВентОтопл',
        // 'ДопРаб_ВентОтопл', // Removed - this is a submenu, not direct activity
        'ИзмСх_ВентОтопл', 'МонтКб_ВентОтопл', 'РаскКб_ВентОтопл',
        'МонтАСУ_ВентОтопл', 'УдалПО_ВентОтопл', 'Прочее_ВентОтопл',
        // Technological equipment activity types
        'РабПоГр_ТехОб', 'ФормИД_ТехОб', 'ПодтВех_ТехОб',
        'ДемАрм_ТехОб', 'ДемОб_ТехОб', 'Прочее_ТехОб',
        // 'ДопРаб_ТехОб', // Removed - this is a submenu, not direct activity
        // Fix for commissioning works issue - added missing activity type
        'ПусконаладочныеРаботы',
        // Missing commissioning activity types
        'КомплексноеОпробование',
        'РежимнаяНаладка',
        'ПодготовкаКПоездке',
        'СдачаАвансовогоОтчета',
        'ОхраннаяСигнализация',
        'ОтоплениеИВентиляция',
        'ТехнологическоеОборудование',
        'Метрология',
        // Project activity types
        'УчастиеВЗПИ',
        'РазрПрограммы',
        'РазрГрафика',
        'ОргДопоставки',
        'УчастиеВОПИ',
        'ИспОбМаст',
        'ШефНаладочныеРаботы',
        'Прочее_ПроектнаяДеятельность',
        'Прочее_ПроектнаяДеятельность_ШМР',
        // RK comments elimination activity types (individual sections only, not the main menu)
        'УстрЗам_ЭС',     // ES Section
        'УстрЗам_АТХ',    // ATX Section
        'УстрЗам_ОВКВ',   // OVKV Section
        'УстрЗам_ПСОС',  // PS/OS Section
        'УстрЗам_ТХ',     // TX Section
        'УстрЗам_Прочее',   // Other Equipment
        // Warranty equipment activity types
        'РабГарантии_ЭлектрОб', 'РабГарантии_СрИзм', 'РабГарантии_НасОб',
        'РабГарантии_ТрубАрм', 'РабГарантии_ИспМех', 'РабГарантии_КонтрОб', 'РабГарантии_Прочее',
        // Supervision works activity types
        'ШМР_РаботыПоГрафику',
        'ШМР_ПодготовкаПоездки',
        'ШМР_Дорога',
        'ШМР_Инструктаж',
        'ШМР_СдачаОтчета',
        // New supervision installation works activity types
        'ШМР_ВходнойКонтроль_РабПоГр', 'ШМР_ВходнойКонтроль_ПодтВех',
        'ШМР_ЗавозОборудования_РабПоГр', 'ШМР_ЗавозОборудования_ПодтВех',
        'ШМР_ПроверкаМонтажнойПлощадки',
        'ПроверкаМонтажнойПлощадки',
        'ПодписаниеАктаШМР',
        'ШМР_РасстановкаБлоков_РабПоГр', 'ШМР_РасстановкаБлоков_ПодтВех',
        'ШМР_МонтажПлощадок_РабПоГр', 'ШМР_МонтажПлощадок_ПодтВех',
        'ШМР_МонтажЭлементовУкрытия_РабПоГр', 'ШМР_МонтажЭлементовУкрытия_ПодтВех',
        'ШМР_СборкаКровли_РабПоГр', 'ШМР_СборкаКровли_ПодтВех',
        'ШМР_МонтажТрубопроводов_РабПоГр', 'ШМР_МонтажТрубопроводов_ПодтВех',
        'ШМР_ГидроПневмоИспытания_РабПоГр', 'ШМР_ГидроПневмоИспытания_ПодтВех',
        'ШМР_МонтажТеплоизоляции_РабПоГр', 'ШМР_МонтажТеплоизоляции_ПодтВех',
        'ШМР_МонтажВентиляции_РабПоГр', 'ШМР_МонтажВентиляции_ПодтВех',
        'ШМР_МонтажСнятогоОборудования_РабПоГр', 'ШМР_МонтажСнятогоОборудования_ПодтВех',
        'ШМР_МонтажКабельнойПродукции_РабПоГр', 'ШМР_МонтажКабельнойПродукции_ПодтВех',
        'ШМР_ПодписаниеАкта'
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
        'ПодготовкаТКП_ОПНВГ', 'ПодготовкаТКП_НКО', 'ПодготовкаТКП_МСО', 'ПодготовкаТКП_АСН',
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
        
        // 🚨 CRITICAL DEBUG FOR SUPERVISION WORKS CALLBACK
        if ($callbackData === 'cat_ШМР_ПодгРаб') {
            error_log("🚨🚨🚨 PREPARATORY WORKS CALLBACK DETECTED IN canHandle!");
            error_log("🚨 Exact callback: '{$callbackData}'");
            error_log("🚨 Callback hex: " . bin2hex($callbackData));
            error_log("🚨 TabelHandler will handle this: YES");
        }
        
        // 🔥 SITE INSPECTION CALLBACK DEBUG
        if (str_contains($callbackData, 'АналФунд')) {
            error_log("🔥🔥🔥 SITE INSPECTION CALLBACK DETECTED IN canHandle!");
            error_log("🔥 Exact callback: '{$callbackData}'");
            error_log("🔥 Callback hex: " . bin2hex($callbackData));
            error_log("🔥 TabelHandler will handle this: YES");
        }
        
        // Also check for corrupted UTF-8 version and fix if needed
        if (strpos($callbackData, 'category_Табель') === false && strpos($callbackData, 'category_РўР°Р±РµР»СЊ') !== false) {
            $callbackData = $this->fixKnownCyrillicPatterns($callbackData);
        }
        
        // Apply the same fix for all cases to ensure consistent handling
        $callbackData = $this->fixKnownCyrillicPatterns($callbackData);
        
        error_log("TabelHandler: canHandle called with callbackData: '{$callbackData}'");
        
        // Specific check for mobile brigade button
        if ($callbackData === 'category_Табель_Мобильная_Бригада' || $callbackData === 'category_Табель_МобБриг') {
            error_log("TabelHandler: detected mobile brigade button callback");
        }
        
        // Specific check for commissioning works button (short format)
        if ($callbackData === 'cat_ТабПНРаботы') {
            error_log("TabelHandler: detected commissioning works button callback");
        }
        
        $canHandle = str_starts_with($callbackData, 'category_Табель') || 
                     str_starts_with($callbackData, 'cat_ТабПНРаботы') ||
                     str_starts_with($callbackData, 'cat_Таб') ||
                     str_starts_with($callbackData, 'cat_Гарантии_') ||
                     str_starts_with($callbackData, 'cat_ШМР_') ||  // New supervision works callbacks
                     str_starts_with($callbackData, 'ШМР_') ||  // Supervision milestone/schedule callbacks
                     str_starts_with($callbackData, 'cat_supervision_') ||  // Alternative supervision callbacks
                     str_starts_with($callbackData, 'cat_РЁРњР _') ||  // Corrupted supervision callbacks
                     $callbackData === 'новое_прочее_yes' ||
                     $callbackData === 'cat_ТабЭлОб_Проч_yes' ||
                     $callbackData === 'cat_ТабВентОтопл' ||
                     $callbackData === 'cat_ТабОтоплВент' ||
                     str_starts_with($callbackData, 'cat_ТабВентОтопл_') ||
                     str_starts_with($callbackData, 'cat_ТабОтоплВент_') ||
                     $callbackData === 'cat_ТабГарантии';
        error_log("TabelHandler: canHandle result: " . ($canHandle ? 'true' : 'false'));
        return $canHandle;
    }

    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        $callbackData = $callbackQuery->getData();

        error_log("TabelHandler: handle called with category: '{$category}' and callbackData: '{$callbackData}' for user: {$userId}");
        
        // 🔥 SUPERVISION WORKS RAW CALLBACK DEBUG
        if (str_contains($callbackData, 'ШМР') || str_contains($callbackData, 'cat_ШМР_')) {
            error_log("🔥🔥🔥 SUPERVISION WORKS RAW CALLBACK DETECTED!");
            error_log("🔥 Raw callbackData: '{$callbackData}'");
            error_log("🔥 Raw category: '{$category}'");
            error_log("🔥 CallbackData hex: " . bin2hex($callbackData));
            error_log("🔥 Category hex: " . bin2hex($category));
        }
        
        // 🎆 SITE INSPECTION SPECIFIC DEBUG
        if (str_contains($callbackData, 'АналФунд')) {
            error_log("🎆🎆🎆 SITE INSPECTION CALLBACK IN HANDLE!");
            error_log("🎆 Raw callbackData: '{$callbackData}'");
            error_log("🎆 Raw category: '{$category}'");
            error_log("🎆 User: {$userId}, Chat: {$chatId}");
        }
        
        // Add special debug for master duties
        if (str_contains($callbackData, 'ТабИсполнОбяз') || str_contains($category, 'ИспОбМаст')) {
            error_log("🎯 MASTER DUTIES DEBUG: Raw callback: '{$callbackData}', Category: '{$category}'");
        }
        
        // 🚨 URGENT METROLOGY DEBUGGING
        if (strpos($callbackData, 'ТабМетрол') !== false) {
            error_log("🚨🚨🚨 METROLOGY CALLBACK DETECTED: '{$callbackData}'");
            error_log("🚨🚨🚨 METROLOGY CATEGORY: '{$category}'");
            error_log("🚨🚨🚨 USER: {$userId}, CHAT: {$chatId}");
        }
        
        // Extract the actual category from the callback data using our mapping method
        // This handles both long and short callback formats properly
        $actualCategory = $this->extractCategoryFromCallback($callbackData);
        error_log("TabelHandler: extracted actual category: '{$actualCategory}'");
        

        
        // Use the properly extracted category instead of the passed category
        if (!empty($actualCategory)) {
            $category = $actualCategory;
            error_log("TabelHandler: using extracted category: '{$category}'");
            

        }
        
        // Specific check for mobile brigade
        if ($category === 'Табель_Мобильная_Бригада') {
            error_log("TabelHandler: handling mobile brigade callback for user: {$userId}");
        }

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        
        // Also fix known Cyrillic pattern corruptions
        $category = $this->fixKnownCyrillicPatterns($category);

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
        // Level 1 users (Пользователь) can also bypass this restriction when explicitly confirming
        if ($this->isConfirmationCallback($category) && $userAccessLevel < 3 && $userAccessLevel != 1) {
            $isSuperAdmin = $this->adminService->isSuperAdmin($userId);
            $recentEntry = $this->databaseService->hasRecentTabelEntry($userId, $isSuperAdmin);
            if ($recentEntry) {
                $text = $this->tabelEntry->formatRecentEntryError($recentEntry);
                $text = $this->ensureUtf8Encoding($text);
                return $this->messageService->sendMessage($chatId, $text);
            }
        }

        // Check if user has FIO filled for tabel operations
        // Exclude equipment menu access from FIO requirements to allow browsing
        $isEquipmentMenu = ($category === 'Табель_Электрооборудование' || 
                           $category === 'Табель_Автоматизация' || 
                           $category === 'Табель_ПожарнаяСигнализация');
        
        if (str_contains($category, 'Табель') && !$this->isMainTabelMenu($category) && !$isEquipmentMenu) {
            try {
                $userData = $this->databaseService->getUserData($userId);
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
            } catch (\Exception $e) {
                error_log('TabelHandler: Error getting user data: ' . $e->getMessage());
                // Allow to continue if database error occurs
            }
        }

        return $this->routeTabelCallback($callbackQuery, $category, $conversation);
    }

    public function getPriority(): int
    {
        return 70; // Highest priority to ensure proper routing
    }

    private function isConfirmationCallback(string $category): bool
    {
        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        
        // Check for new Prochee confirmation callback
        if ($category === 'новое_прочее_yes') {
            return true;
        }
        
        // Check for old Prochee confirmation callback (backward compatibility)
        if ($category === 'cat_ТабЭлОб_Проч_yes') {
            return true;
        }
        
        // Check for short format commissioning activity confirmations
        if (preg_match('/^cat_(ТабИнстр|ТабДорога|ТабПодгПоезд|ТабСдачаОтч|ТабОхрСигн|ТабОтоплВент|ТабТехОб|ТабМетрол|ТабКомплОпр|ТабРежНал|ТабПНРаботы)_yes$/u', $category)) {
            return true;
        }
        
        // Check for electrical equipment short format confirmations
        if (preg_match('/^cat_ТабЭлОб_.+_yes$/u', $category)) {
            return true;
        }
        
        // Check for automation equipment short format confirmations
        if (preg_match('/^cat_ТабАвтом_.+_yes$/u', $category)) {
            return true;
        }
        
        // Check for fire alarm equipment short format confirmations
        if (preg_match('/^cat_ТабПожСигн_.+_yes$/u', $category)) {
            return true;
        }
        
        // Check for security alarm equipment short format confirmations
        if (preg_match('/^cat_ТабОхрСиг_.+_yes$/u', $category)) {
            return true;
        }
        
        // Check for warranty equipment short format confirmations (CRITICAL FIX)
        if (preg_match('/^cat_Гарантии_.+_yes$/u', $category)) {
            return true;
        }
        
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
        $callbackData = $callbackQuery->getData();
        

        
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // URGENT: Check if this is a metrology confirmation callback
        if (str_contains($category, 'ТабМетрол') && str_contains($category, '_yes')) {
            error_log("🚨 URGENT: METROLOGY CONFIRMATION DETECTED: '{$category}'");
            error_log("🚨 URGENT: This should be routed to isActivityExecution!");
        }
        
        // DEBUG: Check for automation additional works callbacks specifically
        $automationCallbacks = [
            'category_Табель_МонтКб_Автоматизация',
            'category_Табель_РаскКб_Автоматизация', 
            'category_Табель_МонтАСУ_Автоматизация',
            'category_Табель_УдалПО_Автоматизация'
        ];
        
        if (in_array($category, $automationCallbacks)) {
            error_log("TabelHandler: AUTOMATION CALLBACK DETECTED: {$category}");
        }
        
        error_log("TabelHandler: Checking if category matches 'новое_прочее_yes': " . ($category === 'новое_прочее_yes' ? 'YES' : 'NO'));

        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        
        // Also fix known Cyrillic pattern corruptions
        $category = $this->fixKnownCyrillicPatterns($category);

        // Removed conflicting NEW handler for "Прочее" button
        // The OLD handler further down in handleOtherWorkTypes will handle cat_ТабЭлОб_Проч properly

        // НОВЫЙ ОБРАБОТЧИК КНОПКИ "ПРОЧЕЕ" ДЛЯ ПОЖАРНОЙ СИГНАЛИЗАЦИИ
        // Обрабатываем все варианты кнопки "Прочее" для пожарной сигнализации  
        if ($category === 'cat_ТабПожСигн_Проч' || $category === 'Табель_Прочее_ПожСигн' || 
            $category === 'ТабПожСигн_Проч' || $category === 'Прочее_ПожСигн' ||
            $category === 'Табель_Прочее_ПожарнаяСигнализация') {
            error_log("TabelHandler: НОВЫЙ обработчик кнопки Прочее для пожарной сигнализации - запрос пользовательского ввода для: {$category}");
            
            // ВАЖНО: Очищаем все старые данные о Прочее перед установкой новых
            unset($conversation->notes['custom_input_text']);
            unset($conversation->notes['awaiting_custom_input']);
            unset($conversation->notes['custom_input_activity_type']);
            
            // Устанавливаем состояние ожидания пользовательского ввода (используем старую систему для совместимости)
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ПожСигн';
            $conversation->update();
            
            error_log("TabelHandler: Set awaiting custom input state for Fire Alarm Equipment");
            
            // Отправляем запрос на ввод описания работы
            $text = "<b>📝 Прочее - Дополнительные работы по пожарной сигнализации</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // НОВЫЙ ОБРАБОТЧИК КНОПКИ "ПРОЧЕЕ" ДЛЯ ОХРАННОЙ СИГНАЛИЗАЦИИ
        // Обрабатываем все варианты кнопки "Прочее" для охранной сигнализации
        if ($category === 'cat_ТабОхрСиг_Проч' || $category === 'Табель_Прочее_ОхрСигн' || 
            $category === 'ТабОхрСиг_Проч' || $category === 'Прочее_ОхрСигн' ||
            $category === 'Табель_Прочее_ОхраннаяСигнализация') {
            error_log("TabelHandler: НОВЫЙ обработчик кнопки Прочее для охранной сигнализации - запрос пользовательского ввода для: {$category}");
            
            // ВАЖНО: Очищаем все старые данные о Прочее перед установкой новых
            unset($conversation->notes['custom_input_text']);
            unset($conversation->notes['awaiting_custom_input']);
            unset($conversation->notes['custom_input_activity_type']);
            
            // Устанавливаем состояние ожидания пользовательского ввода (используем старую систему для совместимости)
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ОхрСигн';
            $conversation->update();
            
            error_log("TabelHandler: Set awaiting custom input state for Security Alarm Equipment");
            
            // Prompt user for custom input
            $text = "<b>📝 Прочее - Дополнительные работы по охранной сигнализации</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // НОВЫЙ ОБРАБОТЧИК КНОПКИ "ПРОЧЕЕ" ДЛЯ ОХРАННОЙ СИГНАЛИЗАЦИИ
        // Обрабатываем все варианты кнопки "Прочее" для охранной сигнализации
        // Кнопка "Прочее" удалена из главного меню, теперь доступна только через "Дополнительные работы"
        // if ($category === 'cat_ТабОхрСиг_Проч' || $category === 'Табель_Прочее_ОхрСигн' || 
        //     $category === 'ТабОхрСиг_Проч' || $category === 'Прочее_ОхрСигн' ||
        //     $category === 'Табель_Прочее_ОхраннаяСигнализация') {
        //     // Обработчик удален - кнопка доступна только через "Дополнительные работы"
        // }

        // НОВЫЙ ОБРАБОТЧИК ПОДТВЕРЖДЕНИЯ "ПРОЧЕЕ" - СОЗДАНИЕ ЗАПИСИ В ТАБЕЛЬ
        // IMPORTANT: This must come BEFORE isActivityExecution check to prevent conflicts
        if ($category === 'cat_ТабЭлОб_Проч_yes' || $category === 'новое_прочее_yes' || $category === 'cat_ТабАвтом_Проч_yes' || $category === 'cat_ТабВентОтопл_Проч_yes') {
            error_log("=== URGENT DEBUG: CALLBACK RECEIVED ===");
            error_log("TabelHandler: CALLBACK '{$category}' RECEIVED AND RECOGNIZED!");
            error_log("TabelHandler: About to call handleNewProcheeConfirmation()");
            error_log("TabelHandler: НОВЫЙ обработчик подтверждения Прочее - создание записи");
            return $this->handleNewProcheeConfirmation($callbackQuery, $conversation);
        }

        // IMMEDIATE ELECTRICAL EQUIPMENT HANDLING - HIGHEST PRIORITY
        if ($category === 'cat_ТабЭлОб' || $category === 'Табель_Электрооборудование') {
            error_log("TabelHandler: IMMEDIATE electrical equipment menu handling for: {$category}");
            error_log("📞 URGENT FIX: Electrical equipment button clicked! Routing to handleElectricalEquipmentMenu");
            
            try {
                return $this->handleElectricalEquipmentMenu($chatId);
            } catch (\Exception $e) {
                error_log("🚨 CRITICAL ERROR in handleElectricalEquipmentMenu: " . $e->getMessage());
                error_log("🚨 Stack trace: " . $e->getTraceAsString());
                
                // Fallback: Create a simple electrical equipment menu manually
                $fallbackKeyboard = [
                    [
                        ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабЭлОб_РабПоГр']
                    ],
                    [
                        ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабЭлОб_ФормИД']
                    ],
                    [
                        ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабЭлОб_ДопРаб']
                    ],
                    [
                        ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабЭлОб_ПодтВех']
                    ],
                    [
                        ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
                    ]
                ];
                
                error_log("🛠️ Using fallback electrical equipment keyboard");
                return $this->messageService->sendMessage(
                    $chatId, 
                    '<b>⚡ Электрооборудование</b> Выберите тип работ:',
                    $fallbackKeyboard
                );
            }
        }
        
        // IMMEDIATE AUTOMATION EQUIPMENT HANDLING - HIGHEST PRIORITY
        if ($category === 'cat_ТабАвтом' || $category === 'Табель_Автоматизация') {
            error_log("TabelHandler: IMMEDIATE automation equipment menu handling for: {$category}");
            return $this->handleAutomationEquipmentMenu($chatId);
        }
        
        // IMMEDIATE HEATING AND VENTILATION EQUIPMENT HANDLING - HIGHEST PRIORITY
        if ($category === 'cat_ТабВентОтопл' || $category === 'Табель_ОтоплениеИВентиляция' || $category === 'cat_ТабОтоплВент') {
            error_log("TabelHandler: IMMEDIATE heating and ventilation equipment menu handling for: {$category}");
            return $this->handleHeatingVentilationEquipmentMenu($chatId);
        }
        
        // IMMEDIATE FIRE ALARM EQUIPMENT HANDLING - HIGHEST PRIORITY
        if ($category === 'cat_ТабПожСигн' || $category === 'Табель_ПожарнаяСигнализация') {
            error_log("TabelHandler: IMMEDIATE fire alarm equipment menu handling for: {$category}");
            return $this->handleFireAlarmEquipmentMenu($chatId);
        }
        
        // IMMEDIATE SECURITY ALARM EQUIPMENT HANDLING - HIGHEST PRIORITY
        if ($category === 'cat_ТабОхрСигн' || $category === 'Табель_ОхраннаяСигнализация') {
            error_log("TabelHandler: IMMEDIATE security alarm equipment menu handling for: {$category}");
            return $this->handleSecurityAlarmEquipmentMenu($chatId);
        }
        
        // IMMEDIATE TECHNOLOGICAL EQUIPMENT HANDLING - HIGHEST PRIORITY
        if ($category === 'cat_ТабТехОб' || $category === 'Табель_ТехнологическоеОборудование') {
            error_log("TabelHandler: IMMEDIATE technological equipment menu handling for: {$category}");
            return $this->handleTechnologicalEquipmentMenu($chatId);
        }
        
        // IMMEDIATE METROLOGY HANDLING - HIGHEST PRIORITY
        if ($category === 'cat_ТабМетрол' || $category === 'Табель_Метрология') {
            error_log("TabelHandler: IMMEDIATE metrology menu handling for: {$category}");
            return $this->handleMetrologyMenu($chatId);
        }
        
        // IMMEDIATE FIRE ALARM EQUIPMENT ADDITIONAL WORKS HANDLING - HIGHEST PRIORITY
        if ($category === 'cat_ТабПожСигн_ДопРаб' || $category === 'Табель_ДопРаб_ПожСигн' || 
            $callbackQuery->getData() === 'cat_ТабПожСигн_ДопРаб') {
            error_log("TabelHandler: IMMEDIATE fire alarm additional works handling for: {$category}");
            return $this->handleAdditionalWorksFireAlarmMenu($chatId);
        }

        // Main tabel menu
        if ($category === 'Табель') {
            error_log("TabelHandler: routing to handleMainTabel");
            return $this->handleMainTabel($chatId);
        }

        // Date selection flows
        if ($this->projectSelector->isDateSelection($category)) {
            error_log("TabelHandler: routing to handleDateSelection");
            return $this->handleDateSelection($callbackQuery, $category, $conversation);
        }

        // Project selection
        if ($this->projectSelector->isProjectSelection($category)) {
            error_log("TabelHandler: routing to handleProjectSelection");
            return $this->handleProjectSelection($callbackQuery, $category, $conversation);
        }

        // Work type selection
        if ($category === 'Табель_Мобильная_Бригада_Дата' || $category === 'Табель_МобБриг_Дата') {
            error_log("TabelHandler: routing to handleDateSelectionMenu");
            return $this->handleDateSelectionMenu($chatId);
        }

        // TKP preparation main button - show submenu
        if ($category === 'Табель_ПодготовкаТКП') {
            error_log("TabelHandler: routing TKP main button to handleTKPSubtypesMenu");
            return $this->handleTKPSubtypesMenu($chatId);
        }
        
        // TKP preparation menu (alternative callback)
        if ($category === 'Табель_ПодготовкаТКП_Меню') {
            error_log("TabelHandler: routing to handleTKPSubtypesMenu");
            return $this->handleTKPSubtypesMenu($chatId);
        }

        // Back to current activity menu
        if ($category === 'Табель_Текущая_Назад') {
            error_log("TabelHandler: routing to handleBackToCurrentActivity");
            return $this->handleBackToCurrentActivity($chatId, $conversation);
        }
        
        // Back to commissioning menu
        if ($category === 'Табель_Пусконаладка_Back') {
            error_log("TabelHandler: routing to handleBackToCommissioning");
            return $this->handleBackToCommissioning($chatId, $conversation);
        }
        
        // Back to supervision menu
        if ($category === 'Табель_ШМ_Back') {
            error_log("TabelHandler: routing to handleBackToSupervision");
            return $this->handleBackToSupervision($chatId, $conversation);
        }

        // Handle Простой (Idle/Downtime) - route to detailed idle menu system
        if ($this->isIdleMenuCallback($category) || $category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой') {
            error_log("TabelHandler: routing to handleIdleMenu (detailed idle system)");
            return $this->handleIdleMenu($callbackQuery, $category, $conversation);
        }

        // Handle supervision warranty equipment (EARLY ROUTING)
        if ($category === 'Табель_ШМ_РабГарантии') {
            error_log("TabelHandler: EARLY routing to supervision warranty equipment menu");
            return $this->handleWarrantyEquipmentMenu($chatId, $conversation, 'supervision');
        }
        
        // Handle supervision RK elimination (EARLY ROUTING)
        if ($category === 'Табель_ШМ_УЗ_РК') {
            error_log("TabelHandler: EARLY routing to supervision RK elimination menu");
            return $this->handleSupervisionRKCommentsEliminationMenu($chatId, $conversation);
        }
        
        // REMOVED: Early ШМР schedule routing that bypassed confirmation dialog
        // This was causing "Работы по графику" buttons to auto-execute without confirmation
        
        // REMOVED: Early ШМР milestone routing that bypassed confirmation dialog
        // Now ШМР milestone confirmations go through normal flow for proper handling

        // Activity execution
        error_log("TabelHandler: DEBUG - About to call isActivityExecution with: '{$category}'");
        

        
        // 🚨 URGENT SITE INSPECTION DEBUG
        if (str_contains($category, 'АналФунд')) {
            error_log("🚨🚨🚨 SITE INSPECTION ACTIVITY EXECUTION CHECK!");
            error_log("🚨 Category: '{$category}'");
            error_log("🚨 Expected pattern: cat_ШМР_АналФунд_yes");
            error_log("🚨 About to call isActivityExecution...");
        }
        if ($this->isActivityExecution($category)) {
            error_log("TabelHandler: DEBUG - isActivityExecution returned TRUE - routing to handleActivityExecution");
            error_log("🚨 URGENT: Category '{$category}' detected as ACTIVITY EXECUTION!");
            error_log("TabelHandler: routing to handleActivityExecution");
            
            // 🚨 URGENT SITE INSPECTION SUCCESS DEBUG
            if (str_contains($category, 'АналФунд')) {
                error_log("🎆🎆🎆 SITE INSPECTION SUCCESS: Routing to handleActivityExecution!");
            }
            
            return $this->handleActivityExecution($callbackQuery, $category, $conversation);
        }
        error_log("TabelHandler: DEBUG - isActivityExecution returned FALSE");
        
        // 🚨 URGENT SITE INSPECTION FAILURE DEBUG
        if (str_contains($category, 'АналФунд')) {
            error_log("🚨🚨🚨 SITE INSPECTION FAILED: isActivityExecution returned FALSE!");
            error_log("🚨 This means the callback pattern is not being recognized!");
            error_log("🚨 Check the isActivityExecution method for pattern: cat_ШМР_АналФунд_yes");
        }
        
        // URGENT DEBUG: Check if metrology confirmation reached this point
        if (str_contains($category, 'ТабМетрол') && str_contains($category, '_yes')) {
            error_log("🚨 CRITICAL: METROLOGY CONFIRMATION '{$category}' was NOT detected as activity execution!");
            error_log("🚨 CRITICAL: This is why you get 'Unknown command'!");
        }

        // Activity confirmation
        error_log("TabelHandler: DEBUG - About to call isActivityConfirmation with: '{$category}'");
        

        
        if ($this->isActivityConfirmation($category)) {
            error_log("TabelHandler: DEBUG - isActivityConfirmation returned TRUE - routing to handleActivityConfirmation");
            error_log("TabelHandler: routing to handleActivityConfirmation");
            

            
            return $this->handleActivityConfirmation($callbackQuery, $category, $conversation);
        }
        error_log("TabelHandler: DEBUG - isActivityConfirmation returned FALSE");

        // DEBUG: Log unhandled category before routing to handleOtherWorkTypes
        error_log("🎯 DEBUG: UNHANDLED CATEGORY: '{$category}' - routing to handleOtherWorkTypes");
        

        
        // Other work types
        error_log("TabelHandler: routing to handleOtherWorkTypes");
        return $this->handleOtherWorkTypes($chatId, $category, $conversation);
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
        
        // Handle short category format for commissioning equipment and supervision works
        if (strpos($callbackData, 'cat_Таб') === 0 || strpos($callbackData, 'cat_Гарантии_') === 0 || strpos($callbackData, 'cat_ШМР_') === 0 || strpos($callbackData, 'cat_supervision_') === 0 || $callbackData === 'supervision_prep') {
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
                'cat_ТабАвтом' => 'Табель_Автоматизация',
                'cat_ТабАвтом_РабПоГр' => 'Табель_РабПоГр_Автом',
                'cat_ТабАвтом_ФормИД' => 'Табель_ФормИД_Автом',
                'cat_ТабАвтом_ДопРаб' => 'Табель_ДопРаб_Автом',
                'cat_ТабАвтом_ПодтВех' => 'Табель_ПодтВех_Автом',
                'cat_ТабАвтом_Проч' => 'Табель_Прочее_Автоматизация',
                'cat_ТабПожСигн' => 'Табель_ПожарнаяСигнализация',
                'cat_ТабПожСигн_Наз' => 'Табель_ПожарнаяСигнализация',
                'cat_ТабОхрСигн' => 'Табель_ОхраннаяСигнализация',
                'cat_ТабТехОб' => 'Табель_ТехнологическоеОборудование',
                'cat_ТабТехОб_РабПоГр' => 'Табель_РабПоГр_ТехОб',
                'cat_ТабТехОб_ФормИД' => 'Табель_ФормИД_ТехОб',
                'cat_ТабТехОб_ДопРаб' => 'Табель_ДопРаб_ТехОб',
                'cat_ТабТехОб_ПодтВех' => 'Табель_ПодтВех_ТехОб',
                // Metrology equipment mappings
                'cat_ТабМетрол' => 'Табель_Метрология',
                'cat_ТабМетрол_РабПоГр' => 'Табель_РабПоГр_Метрол',
                'cat_ТабМетрол_ФормИД' => 'Табель_ФормИД_Метрол',
                'cat_ТабМетрол_ПодтВех' => 'Табель_ПодтВех_Метрол',
                'cat_ТабМетрол_ДопРаб' => 'Табель_ДопРаб_Метрол',
                // Metrology additional works mappings
                'cat_ТабМетрол_ИзмКонф' => 'Табель_ИзмКонф_Метрол',
                'cat_ТабМетрол_КалПов' => 'Табель_КалПов_Метрол',
                'cat_ТабМетрол_КМХ' => 'Табель_КМХ_Метрол',
                'cat_ТабМетрол_КоррХар' => 'Табель_КоррХар_Метрол',
                'cat_ТабМетрол_Проч' => 'Табель_Проч_Метрол',
                // Warranty equipment main menu
                'cat_ТабГарантии' => 'Табель_РаботыПоГарантии',
                // Warranty equipment individual callbacks (CRITICAL FIX FOR WARRANTY BUTTONS)
                'category_Табель_РабГарантии_ЭлектрОб' => 'Табель_РабГарантии_ЭлектрОб',
                'category_Табель_РабГарантии_СрИзм' => 'Табель_РабГарантии_СрИзм',
                'category_Табель_РабГарантии_НасОб' => 'Табель_РабГарантии_НасОб',
                'category_Табель_РабГарантии_ТрубАрм' => 'Табель_РабГарантии_ТрубАрм',
                'category_Табель_РабГарантии_ИспМех' => 'Табель_РабГарантии_ИспМех',
                'category_Табель_РабГарантии_КонтрОб' => 'Табель_РабГарантии_КонтрОб',
                'category_Табель_РабГарантии_Прочее' => 'Табель_РабГарантии_Прочее',
                'cat_ТабОхрСиг_РабПоГр' => 'Табель_РабПоГр_ОхрСигн',
                'cat_ТабОхрСиг_ФормИД' => 'Табель_ФормИД_ОхрСигн',
                'cat_ТабОхрСиг_ДопРаб' => 'Табель_ДопРаб_ОхрСигн',
                'cat_ТабОхрСиг_ПодтВех' => 'Табель_ПодтВех_ОхрСигн',
                'cat_ТабОхрСиг_Проч' => 'Табель_Прочее_ОхрСигн',
                'cat_ТабОхрСиг_ИзмСх' => 'Табель_ИзмСх_ОхрСигн',
                'cat_ТабОхрСиг_МонтКб' => 'Табель_МонтКб_ОхрСигн',
                'cat_ТабОхрСиг_РаскКб' => 'Табель_РаскКб_ОхрСигн',
                'cat_ТабОхрСиг_МонтОб' => 'Табель_МонтОб_ОхрСигн',
                'cat_ТабПожСигн_РабПоГр' => 'Табель_РабПоГр_ПожСигн',
                'cat_ТабПожСигн_ФормИД' => 'Табель_ФормИД_ПожСигн',
                'cat_ТабПожСигн_МонтОб' => 'Табель_МонтОб_ПожСигн',
                'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн',
                'cat_ТабПожСигн_ПодтВех' => 'Табель_ПодтВех_ПожСигн',
                'cat_ТабПожСигн_ИзмСх' => 'Табель_ИзмСх_ПожСигн',
                'cat_ТабПожСигн_МонтКб' => 'Табель_МонтКб_ПожСигн',
                'cat_ТабПожСигн_РаскКб' => 'Табель_РаскКб_ПожСигн',
                'cat_ТабПожСигн_МонтОб' => 'Табель_МонтОб_ПожСигн',
                'cat_ТабПожСигн_Проч' => 'Табель_Прочее_ПожСигн',
                'cat_ТабПожСигн_ДопРаб' => 'Табель_ДопРаб_ПожСигн',
                'cat_ТабПожСигн_ПодтВех' => 'Табель_ПодтВех_ПожСигн',
                // Handle confirmation callbacks as well
                'cat_ТабЭлОб_РабПоГр_yes' => 'Табель_РабПоГр_ЭлектрОб_yes',
                'cat_ТабЭлОб_ФормИД_yes' => 'Табель_ФормИД_ЭлектрОб_yes',
                'cat_ТабЭлОб_ИзмСхПд_yes' => 'Табель_ИзмСхПд_ЭлектрОб_yes',
                'cat_ТабЭлОб_МонтКбЛн_yes' => 'Табель_МонтКбЛн_ЭлектрОб_yes',
                'cat_ТабЭлОб_РаскКбЛн_yes' => 'Табель_РаскКбЛн_ЭлектрОб_yes',
                'cat_ТабЭлОб_МонтОб_yes' => 'Табель_МонтОб_ЭлектрОб_yes',
                'cat_ТабЭлОб_ДопРаб_yes' => 'Табель_ДопРаб_ЭлектрОб_yes',
                'cat_ТабЭлОб_ПодтВех_yes' => 'Табель_ПодтВех_ЭлектрОб_yes',
                'cat_ТабЭлОб_Проч_yes' => 'Табель_Прочее_ЭлектрОб_yes',
                'cat_ТабПожСигн_РабПоГр_yes' => 'Табель_РабПоГр_ПожСигн_yes',
                'cat_ТабПожСигн_ФормИД_yes' => 'Табель_ФормИД_ПожСигн_yes',
                'cat_ТабПожСигн_МонтОб_yes' => 'Табель_МонтОб_ПожСигн_yes',
                'cat_ТабПожСигн_ДопРаб_yes' => 'Табель_ДопРаб_ПожСигн_yes',
                'cat_ТабПожСигн_ПодтВех_yes' => 'Табель_ПодтВех_ПожСигн_yes',
                'cat_ТабПожСигн_ИзмСх_yes' => 'Табель_ИзмСх_ПожСигн_yes',
                'cat_ТабПожСигн_МонтКб_yes' => 'Табель_МонтКб_ПожСигн_yes',
                'cat_ТабПожСигн_РаскКб_yes' => 'Табель_РаскКб_ПожСигн_yes',
                'cat_ТабПожСигн_МонтОб_yes' => 'Табель_МонтОб_ПожСигн_yes',
                'cat_ТабПожСигн_Проч_yes' => 'Табель_Прочее_ПожСигн_yes',
                'cat_ТабПожСигн_ДопРаб_yes' => 'Табель_ДопРаб_ПожСигн_yes',
                'cat_ТабПожСигн_ПодтВех_yes' => 'Табель_ПодтВех_ПожСигн_yes',
                // Heating and Ventilation Equipment mappings
                'cat_ТабВентОтопл' => 'Табель_ОтоплениеИВентиляция',
                'cat_ТабОтоплВент' => 'Табель_ОтоплениеИВентиляция',
                'cat_ТабВентОтопл_РабПоГр' => 'Табель_РабПоГр_ВентОтопл',
                'cat_ТабВентОтопл_ФормИД' => 'Табель_ФормИД_ВентОтопл',
                'cat_ТабВентОтопл_ДопРаб' => 'Табель_ДопРаб_ВентОтопл',
                'cat_ТабВентОтопл_ПодтВех' => 'Табель_ПодтВех_ВентОтопл',
                'cat_ТабВентОтопл_Проч' => 'Табель_Прочее_ВентОтопл',
                // Heating and Ventilation Equipment confirmation callbacks
                'cat_ТабВентОтопл_РабПоГр_yes' => 'Табель_РабПоГр_ВентОтопл_yes',
                'cat_ТабВентОтопл_ФормИД_yes' => 'Табель_ФормИД_ВентОтопл_yes',
                'cat_ТабВентОтопл_ДопРаб_yes' => 'Табель_ДопРаб_ВентОтопл_yes',
                'cat_ТабВентОтопл_ПодтВех_yes' => 'Табель_ПодтВех_ВентОтопл_yes',
                // Heating and Ventilation Equipment additional works confirmation callbacks
                'cat_ТабВентОтопл_ИзмСх_yes' => 'Табель_ИзмСх_ВентОтопл_yes',
                'cat_ТабВентОтопл_МонтКб_yes' => 'Табель_МонтКб_ВентОтопл_yes',
                'cat_ТабВентОтопл_РаскКб_yes' => 'Табель_РаскКб_ВентОтопл_yes',
                'cat_ТабВентОтопл_МонтАСУ_yes' => 'Табель_МонтАСУ_ВентОтопл_yes',
                'cat_ТабВентОтопл_УдалПО_yes' => 'Табель_УдалПО_ВентОтопл_yes',
                'cat_ТабВентОтопл_Проч_yes' => 'Табель_Прочее_ВентОтопл_yes',
                // Security Alarm Equipment confirmation callbacks
                'cat_ТабОхрСиг_РабПоГр_yes' => 'Табель_РабПоГр_ОхрСигн_yes',
                'cat_ТабОхрСиг_ФормИД_yes' => 'Табель_ФормИД_ОхрСигн_yes',
                'cat_ТабОхрСиг_ДопРаб_yes' => 'Табель_ДопРаб_ОхрСигн_yes',
                'cat_ТабОхрСиг_ПодтВех_yes' => 'Табель_ПодтВех_ОхрСигн_yes',
                'cat_ТабОхрСиг_Проч_yes' => 'Табель_Прочее_ОхрСигн_yes',
                'cat_ТабОхрСиг_ИзмСх_yes' => 'Табель_ИзмСх_ОхрСигн_yes',
                'cat_ТабОхрСиг_МонтКб_yes' => 'Табель_МонтКб_ОхрСигн_yes',
                'cat_ТабОхрСиг_РаскКб_yes' => 'Табель_РаскКб_ОхрСигн_yes',
                'cat_ТабОхрСиг_МонтОб_yes' => 'Табель_МонтОб_ОхрСигн_yes',
                // Automation Equipment confirmation callbacks
                'cat_ТабАвтом_РабПоГр_yes' => 'Табель_РабПоГр_Автом_yes',
                'cat_ТабАвтом_ФормИД_yes' => 'Табель_ФормИД_Автом_yes',
                'cat_ТабАвтом_ДопРаб_yes' => 'Табель_ДопРаб_Автом_yes',
                'cat_ТабАвтом_ПодтВех_yes' => 'Табель_ПодтВех_Автом_yes',
                'cat_ТабАвтом_ИзмСх_yes' => 'Табель_ИзмСх_Автом_yes',
                'cat_ТабАвтом_МонтКб_yes' => 'Табель_МонтКб_Автом_yes',
                'cat_ТабАвтом_РаскКб_yes' => 'Табель_РаскКб_Автом_yes',
                'cat_ТабАвтом_МонтАСУ_yes' => 'Табель_МонтАСУ_Автом_yes',
                'cat_ТабАвтом_УдалПО_yes' => 'Табель_УдалПО_Автом_yes',
                'cat_ТабАвтом_Проч_yes' => 'Табель_Прочее_Автом_yes',
                'cat_ТабАвтом_Проч_yes' => 'Табель_Прочее_Автом_yes',
                // Automation Equipment confirmation callbacks
                'cat_ТабАвтом_РабПоГр_yes' => 'Табель_РабПоГр_Автом_yes',
                'cat_ТабАвтом_ФормИД_yes' => 'Табель_ФормИД_Автом_yes',
                'cat_ТабАвтом_ДопРаб_yes' => 'Табель_ДопРаб_Автом_yes',
                'cat_ТабАвтом_ПодтВех_yes' => 'Табель_ПодтВех_Автом_yes',
                // Automation Equipment additional works confirmation callbacks
                'category_Табель_ИзмСх_Автоматизация_yes' => 'Табель_ИзмСх_Автом_yes',
                'category_Табель_МонтКб_Автоматизация_yes' => 'Табель_МонтКб_Автом_yes',
                'category_Табель_РаскКб_Автоматизация_yes' => 'Табель_РаскКб_Автом_yes',
                'category_Табель_МонтАСУ_Автоматизация_yes' => 'Табель_МонтАСУ_Автом_yes',
                'category_Табель_УдалПО_Автоматизация_yes' => 'Табель_УдалПО_Автом_yes',
                'category_Табель_Прочее_Автоматизация_yes' => 'Табель_Прочее_Автом_yes',
                // Handle commissioning activity confirmations
                'cat_ТабИнстр_yes' => 'cat_ТабИнстр_yes',
                'cat_ТабДорога_yes' => 'cat_ТабДорога_yes',
                'cat_ТабПодгПоезд_yes' => 'cat_ТабПодгПоезд_yes',
                'cat_ТабСдачаОтч_yes' => 'cat_ТабСдачаОтч_yes',
                'cat_ТабОхрСигн_yes' => 'cat_ТабОхрСигн_yes',
                'cat_ТабОтоплВент_yes' => 'cat_ТабОтоплВент_yes',
                'cat_ТабТехОб_yes' => 'cat_ТабТехОб_yes',
                'cat_ТабМетрол_yes' => 'cat_ТабМетрол_yes',
                // Metrology equipment confirmation callbacks
                'cat_ТабМетрол_РабПоГр_yes' => 'Табель_РабПоГр_Метрол_yes',
                'cat_ТабМетрол_ФормИД_yes' => 'Табель_ФормИД_Метрол_yes',
                'cat_ТабМетрол_ПодтВех_yes' => 'Табель_ПодтВех_Метрол_yes',
                'cat_ТабМетрол_ДопРаб_yes' => 'Табель_ДопРаб_Метрол_yes',
                // Metrology additional works confirmation callbacks
                'cat_ТабМетрол_ИзмКонф_yes' => 'Табель_ИзмКонф_Метрол_yes',
                'cat_ТабМетрол_КалПов_yes' => 'Табель_КалПов_Метрол_yes',
                'cat_ТабМетрол_КМХ_yes' => 'Табель_КМХ_Метрол_yes',
                'cat_ТабМетрол_КоррХар_yes' => 'Табель_КоррХар_Метрол_yes',
                'cat_ТабМетрол_Проч_yes' => 'Табель_Проч_Метрол_yes',
                'cat_ТабКомплОпр_yes' => 'cat_ТабКомплОпр_yes',
                'cat_ТабРежНал_yes' => 'cat_ТабРежНал_yes',
                'cat_ТабПНРаботы_yes' => 'cat_ТабПНРаботы_yes',
                // Technological equipment confirmations
                'cat_ТабТехОб_РабПоГр_yes' => 'Табель_РабПоГр_ТехОб_yes',
                'cat_ТабТехОб_ФормИД_yes' => 'Табель_ФормИД_ТехОб_yes',
                'cat_ТабТехОб_ДопРаб_yes' => 'Табель_ДопРаб_ТехОб_yes',
                'cat_ТабТехОб_ПодтВех_yes' => 'Табель_ПодтВех_ТехОб_yes',
                'cat_ТабТехОб_ДемАрм_yes' => 'Табель_ДемАрм_ТехОб_yes',
                'cat_ТабТехОб_ДемОб_yes' => 'Табель_ДемОб_ТехОб_yes',
                'cat_ТабТехОб_Проч_yes' => 'Табель_Прочее_ТехОб_yes',
                'cat_ТабПроектнаяДеят_Проч_yes' => 'Табель_Прочее_ПроектнаяДеятельность_yes',
                'cat_ТабПроектнаяДеят_Проч_ШМР_yes' => 'Табель_Прочее_ПроектнаяДеятельность_ШМР_yes',
                'cat_ТабПроектнаяДеят' => 'Табель_ПроектнаяДеятельность',
            'cat_ТабПроектнаяДеят_ШМР' => 'Табель_ПроектнаяДеятельность_ШМР',
                // Project activity callbacks with short format
                'cat_ТабИсполнОбяз' => 'Табель_ИспОбМаст',
                'cat_ТабИсполнОбяз_yes' => 'Табель_ИспОбМаст_yes',
                // Project activity callbacks with full format (from supervision project activity keyboard)
                'category_Табель_ИспОбМаст' => 'Табель_ИспОбМаст',
                'category_Табель_ИспОбМаст_yes' => 'Табель_ИспОбМаст_yes',
                // RK comments elimination mappings
                'cat_ТабУстрЗамРК' => 'ТабУстрЗамРК', // Keep short format for menu handler
                'cat_ТабУстрЗамЭС' => 'ТабУстрЗамЭС',
                'cat_ТабУстрЗамАТХ' => 'ТабУстрЗамАТХ',
                'cat_ТабУстрЗамОВКВ' => 'ТабУстрЗамОВКВ',
                'cat_ТабУстрЗамПСОС' => 'ТабУстрЗамПСОС',
                'cat_ТабУстрЗамТХ' => 'ТабУстрЗамТХ',
                'cat_ТабУстрЗамПрочее' => 'ТабУстрЗамПрочее',
                // RK comments elimination with category_ prefix (from MessageService keyboard)
                'category_Табель_УстрЗам_ЭС' => 'ТабУстрЗамЭС',
                'category_Табель_УстрЗам_АТХ' => 'ТабУстрЗамАТХ',
                'category_Табель_УстрЗам_ОВКВ' => 'ТабУстрЗамОВКВ',
                'category_Табель_УстрЗам_ПСОС' => 'ТабУстрЗамПСОС',
                'category_Табель_УстрЗам_ТХ' => 'ТабУстрЗамТХ',
                'category_Табель_УстрЗам_Прочее' => 'ТабУстрЗамПрочее',
                // RK comments elimination confirmation callbacks
                'cat_ТабУстрЗамЭС_yes' => 'ТабУстрЗамЭС_yes',
                'cat_ТабУстрЗамАТХ_yes' => 'ТабУстрЗамАТХ_yes',
                'cat_ТабУстрЗамОВКВ_yes' => 'ТабУстрЗамОВКВ_yes',
                'cat_ТабУстрЗамПСОС_yes' => 'ТабУстрЗамПСОС_yes',
                'cat_ТабУстрЗамТХ_yes' => 'ТабУстрЗамТХ_yes',
                // Warranty equipment confirmation callbacks (CRITICAL FIX FOR WARRANTY BUTTONS)
                'cat_Гарантии_ЭлектрОб_yes' => 'РабГарантии_ЭлектрОб_yes',
                'cat_Гарантии_СрИзм_yes' => 'РабГарантии_СрИзм_yes',
                'cat_Гарантии_НасОб_yes' => 'РабГарантии_НасОб_yes',
                'cat_Гарантии_ТрубАрм_yes' => 'РабГарантии_ТрубАрм_yes',
                'cat_Гарантии_ИспМех_yes' => 'РабГарантии_ИспМех_yes',
                'cat_Гарантии_КонтрОб_yes' => 'РабГарантии_КонтрОб_yes',
                'cat_Гарантии_Прочее_yes' => 'РабГарантии_Прочее_yes',
                // Warranty equipment menu item callbacks
                'cat_Гарантии_ЭлектрОб' => 'Табель_РабГарантии_ЭлектрОб',
                'cat_Гарантии_СрИзм' => 'Табель_РабГарантии_СрИзм',
                'cat_Гарантии_НасОб' => 'Табель_РабГарантии_НасОб',
                'cat_Гарантии_ТрубАрм' => 'Табель_РабГарантии_ТрубАрм',
                'cat_Гарантии_ИспМех' => 'Табель_РабГарантии_ИспМех',
                'cat_Гарантии_КонтрОб' => 'Табель_РабГарантии_КонтрОб',
                'cat_Гарантии_Прочее' => 'Табель_РабГарантии_Прочее',
                // Supervision department callback mappings (CRITICAL FIX)
                'category_Табель_ШМ_РабГарантии' => 'Табель_ШМ_РабГарантии',
                'category_Табель_ШМ_УЗ_РК' => 'Табель_ШМ_УЗ_РК',
                // Supervision works callback mapping
                'category_Табель_ШМР' => 'Табель_ШМР',
                // Supervision works sub-activities callback mappings
                'category_Табель_ШМР_РаботыПоГрафику' => 'Табель_ШМР_РаботыПоГрафику',
                'category_Табель_ШМР_ПодготовкаПоездки' => 'Табель_ШМР_ПодготовкаПоездки',
                'category_Табель_ШМР_Дорога' => 'Табель_ШМР_Дорога',
                'category_Табель_ШМР_Инструктаж' => 'Табель_ШМР_Инструктаж',
                'category_Табель_ШМР_СдачаОтчета' => 'Табель_ШМР_СдачаОтчета',
                // Supervision works sub-activities confirmation callback mappings
                'category_Табель_ШМР_РаботыПоГрафику_yes' => 'Табель_ШМР_РаботыПоГрафику_yes',
                'category_Табель_ШМР_ПодготовкаПоездки_yes' => 'Табель_ШМР_ПодготовкаПоездки_yes',
                'category_Табель_ШМР_Дорога_yes' => 'Табель_ШМР_Дорога_yes',
                'category_Табель_ШМР_Инструктаж_yes' => 'Табель_ШМР_Инструктаж_yes',
                'category_Табель_ШМР_СдачаОтчета_yes' => 'Табель_ШМР_СдачаОтчета_yes',
                // Supervision works sub-activities short format confirmation callback mappings
                'cat_ТабШМР_РаботыПоГрафику_yes' => 'Табель_ШМР_РаботыПоГрафику_yes',
                'cat_ТабШМР_ПодготовкаПоездки_yes' => 'Табель_ШМР_ПодготовкаПоездки_yes',
                'cat_ТабШМР_Дорога_yes' => 'Табель_ШМР_Дорога_yes',
                'cat_ТабШМР_Инструктаж_yes' => 'Табель_ШМР_Инструктаж_yes',
                'cat_ТабШМР_СдачаОтчета_yes' => 'Табель_ШМР_СдачаОтчета_yes',
                
                // New short format supervision works callbacks
                'cat_ШМР_ПодгПоезд' => 'Табель_ШМР_ПодготовкаПоездки',
                'cat_ШМР_Дорога' => 'Табель_ШМР_Дорога',
                'cat_ШМР_Инстр' => 'Табель_ШМР_Инструктаж',
                'cat_ШМР_СдачаОтч' => 'Табель_ШМР_СдачаОтчета',
                
                // NEW SUPERVISION WORKS HIERARCHICAL STRUCTURE MAPPINGS
                'cat_supervision_prep' => 'Табель_ПодготовительныеРаботы',
                'cat_ШМР_ПодгРаб' => 'Табель_ПодготовительныеРаботы',
                
                // Supervision works milestone confirmation callbacks
                'cat_ШМР_ВхКонтр_ПодтВех' => 'ШМР_ВходнойКонтроль_ПодтВех',
                'cat_ШМР_ЗавОбор_ПодтВех' => 'ШМР_ЗавозОборудования_ПодтВех',
                'cat_ШМР_АналФунд_ПодтВех' => 'ПроверкаМонтажнойПлощадки',
                'cat_ШМР_РасстБлок_ПодтВех' => 'ШМР_РасстановкаБлоков_ПодтВех',
                'cat_ШМР_МонтПлощ_ПодтВех' => 'ШМР_МонтПлощ_ПодтВех',
                'cat_ШМР_МонтЭлУкр_ПодтВех' => 'ШМР_МонтажЭлементовУкрытия_ПодтВех',
                'cat_ШМР_СборКровл_ПодтВех' => 'ШМР_СборкаКровли_ПодтВех',
                'cat_ШМР_МонтТруб_ПодтВех' => 'ШМР_МонтажТрубопроводов_ПодтВех',
                'cat_ШМР_ГидрИсп_ПодтВех' => 'ШМР_ГидрИсп_ПодтВех',
                'cat_ШМР_МонтТеплИз_ПодтВех' => 'ШМР_МонтТеплИз_ПодтВех',
                'cat_ШМР_МонтТеплИз_РабГр' => 'ШМР_МонтТеплИз_РабПоГр',
                'cat_ШМР_МонтВент_ПодтВех' => 'ШМР_МонтВент_ПодтВех',
                'cat_ШМР_МонтВент_РабГр' => 'ШМР_МонтВент_РабПоГр',
                'cat_ШМР_МонтВент_ПодтВех' => 'ШМР_МонтВент_ПодтВех',
                'cat_ШМР_МонтСнятОб_ПодтВех' => 'ШМР_МонтажСнятогоОборудования_ПодтВех',
                'cat_ШМР_МонтКаб_ПодтВех' => 'ШМР_МонтажКабельнойПродукции_ПодтВех',
                
                // Supervision works schedule confirmation callbacks - SHORT FORM (matching TabelEntry mappings)
                'cat_ШМР_ВхКонтр_РабГр' => 'ШМР_ВхКонтр_РабПоГр',
                'cat_ШМР_ЗавОбор_РабГр' => 'ШМР_ЗавОбор_РабПоГр',
                'cat_ШМР_АналФунд_РабГр' => 'ШМР_АналФунд_РабПоГр',
                'cat_ШМР_РасстБлок_РабГр' => 'ШМР_РасстБлок_РабПоГр',
                'cat_ШМР_МонтПлощ_РабГр' => 'ШМР_МонтПлощ_РабПоГр',
                'cat_ШМР_МонтЭлУкр_РабГр' => 'ШМР_МонтЭлУкр_РабПоГр',
                'cat_ШМР_СборКровл_РабГр' => 'ШМР_СборКровл_РабПоГр',
                'cat_ШМР_МонтТруб_РабГр' => 'ШМР_МонтТруб_РабПоГр',
                'cat_ШМР_ГидрИсп_РабГр' => 'ШМР_ГидрИсп_РабПоГр',
                'cat_ШМР_МонтТеплИз_РабГр' => 'ШМР_МонтТеплИз_РабПоГр',
                'cat_ШМР_МонтВент_РабГр' => 'ШМР_МонтВент_РабПоГр',
                'cat_ШМР_МонтСнятОб_РабГр' => 'ШМР_МонтСнятОб_РабПоГр',
                'cat_ШМР_МонтКаб_РабГр' => 'ШМР_МонтКаб_РабПоГр',
                'supervision_prep' => 'Табель_ПодготовительныеРаботы',
                'cat_ШМР_МонтБлок' => 'Табель_МонтажБлоков',
                'cat_ШМР_СборЗдан' => 'Табель_СборкаЗдания',
                'cat_ШМР_ВнутрМонт' => 'Табель_ВнутриблочныйМонтаж',
                'cat_ШМР_ПодпАкт' => 'Табель_ПодписаниеАктаШМР',
                'cat_ШМР_Back' => 'Табель_ШМ_Back',
                
                // Sub-activity mappings
                'cat_ШМР_ВхКонтр' => 'Табель_ВходнойКонтроль',
                'cat_ШМР_ЗавОбор' => 'Табель_ЗавозОборудования',
                'cat_ШМР_АналФунд' => 'Табель_ПроверкаМонтажнойПлощадки',
                'cat_ШМР_РасстБлок' => 'Табель_РасстановкаБлоков',
                'cat_ШМР_МонтПлощ' => 'Табель_МонтажПлощадокОбслуживания',
                'cat_ШМР_МонтЭлУкр' => 'Табель_МонтажЭлементовУкрытия',
                'cat_ШМР_СборКровл' => 'Табель_СборкаКровли',
                'cat_ШМР_МонтТруб' => 'Табель_МонтажТрубопроводов',
                'cat_ШМР_ГидрИсп' => 'Табель_ГидропневмоИспытания',
                'cat_ШМР_МонтТеплИз' => 'Табель_МонтажТеплоизоляции',
                'cat_ШМР_МонтВент' => 'Табель_МонтажВентиляции',
                
                // CRITICAL FIX: Add missing confirmation callback mapping for АналФунд
                'cat_ШМР_АналФунд_yes' => 'Табель_ПроверкаМонтажнойПлощадки_yes'
            ];
            
            if (isset($shortFormats[$callbackData])) {
                return $shortFormats[$callbackData];
            }
            
            // Handle other short formats as needed
            return substr($callbackData, strlen('cat_'));
        }
        
        return '';
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
        
        error_log("TabelHandler: handleDateSelection called with category: '{$category}'");
        
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
                // Special handling for short format commissioning works
                if (str_contains($category, 'cat_ТабПНРаботы')) {
                    // For short format commissioning works, go directly to confirmation
                    $conversation->notes['is_commissioning'] = false;
                    $conversation->notes['is_supervision'] = false;
                    $conversation->notes['is_current_activity'] = false;
                    $conversation->notes['is_mobile_brigade'] = false;
                    $conversation->notes['project_code'] = 'Общие работы'; // Default project for commissioning works
                    $conversation->update();
                    
                    // Go directly to confirmation
                    $activityType = 'ПусконаладочныеРаботы';
                    $projectCode = 'Общие работы';
                    $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
                    $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПНРаботы_yes");
                    return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
                } else {
                    // Для Пусконаладки сразу переходим к выбору проекта
                    $conversation->notes['is_commissioning'] = true;
                    $conversation->notes['is_supervision'] = false;
                    $conversation->notes['is_current_activity'] = false;
                    $conversation->notes['is_mobile_brigade'] = false;
                    $conversation->update();
                    
                    $text = "<b>⚙️ Табель Пусконаладка за {$date}, выбор проекта</b>";
                    $keyboard = $this->messageService->createProjectSelectionKeyboard($callbackQuery->getFrom()->getId());
                    return $this->messageService->sendMessage($chatId, $text, $keyboard);
                }
            } elseif (str_contains($category, 'Шеф-монтаж') || str_contains($category, 'ШМ')) {
                // Для Шеф-монтажа сразу переходим к выбору проекта
                $conversation->notes['is_supervision'] = true;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_current_activity'] = false;
                $conversation->notes['is_mobile_brigade'] = false;
                $conversation->update();
                
                $text = "<b>🔧 Табель Шеф-монтаж за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard($callbackQuery->getFrom()->getId());
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } elseif (str_contains($category, 'Мобильная_Бригада') || str_contains($category, 'МобБриг')) {
                error_log("TabelHandler: detected mobile brigade flow");
                // Для мобильной бригады устанавливаем флаг мобильной бригады
                $conversation->notes['is_current_activity'] = false;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_supervision'] = false;
                $conversation->notes['is_mobile_brigade'] = true;
                $conversation->update();
                error_log("TabelHandler: mobile brigade flag set to true");
                
                $text = "<b>💼 Табель Мобильная бригада за {$date}, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard($callbackQuery->getFrom()->getId());
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            } else {
                error_log("TabelHandler: defaulting to mobile brigade flow");
                // Для мобильной бригады устанавливаем флаг мобильной бригады
                $conversation->notes['is_current_activity'] = false;
                $conversation->notes['is_commissioning'] = false;
                $conversation->notes['is_supervision'] = false;
                $conversation->notes['is_mobile_brigade'] = true;
                $conversation->update();
                error_log("TabelHandler: mobile brigade flag set to true (default)");
                
                $text = "<b>Табель за {$date}, выбор проекта</b>";
                
                $keyboard = $this->messageService->createProjectSelectionKeyboard($callbackQuery->getFrom()->getId());
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
        }
        
        return $this->messageService->sendMessage($chatId, 'Ошибка выбора даты');
    }

    private function handleProjectSelection(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $projectCode = $this->projectSelector->extractProjectCode($category);
        
        error_log("TabelHandler: handleProjectSelection called with category: '{$category}', projectCode: '{$projectCode}'");
        
        if ($projectCode) {
            $conversation->notes['project_code'] = $projectCode;
            $conversation->notes['current_category'] = $category;
            $conversation->update();
            
            // CRITICAL FIX: Check if this is a pending idle flow that needs to complete
            if (isset($conversation->notes['pending_idle_category'])) {
                error_log("🎯 IDLE FLOW: Found pending idle category, completing idle flow now");
                $pendingCategory = $conversation->notes['pending_idle_category'];
                
                // Clear the pending state
                unset($conversation->notes['pending_idle_category']);
                $conversation->update();
                
                // Continue with the idle final selection now that we have project_code
                return $this->handleIdleFinalSelection($callbackQuery, $pendingCategory, $conversation);
            }
            
            // Check if this is current activity flow
            $isCurrentActivity = isset($conversation->notes['is_current_activity']) && $conversation->notes['is_current_activity'];
            
            // Check if this is commissioning flow
            $isCommissioning = isset($conversation->notes['is_commissioning']) && $conversation->notes['is_commissioning'];
            
            // Check if this is supervision flow
            $isSupervision = isset($conversation->notes['is_supervision']) && $conversation->notes['is_supervision'];
            
            // Check if this is mobile brigade flow
            $isMobileBrigade = isset($conversation->notes['is_mobile_brigade']) && $conversation->notes['is_mobile_brigade'];
            error_log("TabelHandler: isMobileBrigade flag: " . ($isMobileBrigade ? 'true' : 'false'));
            
            error_log("TabelHandler: flow flags - isCurrentActivity: " . ($isCurrentActivity ? 'true' : 'false') . 
                      ", isCommissioning: " . ($isCommissioning ? 'true' : 'false') . 
                      ", isSupervision: " . ($isSupervision ? 'true' : 'false') . 
                      ", isMobileBrigade: " . ($isMobileBrigade ? 'true' : 'false'));
            
            if ($isCurrentActivity) {
                error_log("TabelHandler: showing current activity keyboard");
                $keyboard = $this->messageService->createCurrentActivityKeyboard();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>💼 Табель - Текущая деятельность для проекта {$projectCode}</b>",
                    $keyboard
                );
            } elseif ($isCommissioning) {
                error_log("TabelHandler: showing commissioning keyboard");
                $keyboard = $this->messageService->createCommissioningKeyboard();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>⚙️ Табель - Пусконаладка для проекта {$projectCode}</b>",
                    $keyboard
                );
            } elseif ($isSupervision) {
                error_log("TabelHandler: showing supervision keyboard");
                $keyboard = $this->messageService->createSupervisionKeyboard();
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>🔧 Табель - Шеф-монтаж для проекта {$projectCode}</b>",
                    $keyboard
                );
            } elseif ($isMobileBrigade) {
                error_log("TabelHandler: showing mobile brigade keyboard");
                $keyboard = $this->messageService->createMobileBrigadeKeyboard();
                error_log("TabelHandler: mobile brigade keyboard created with " . count($keyboard) . " rows");
                return $this->messageService->sendMessage(
                    $chatId, 
                    "<b>💼 Табель - Мобильная бригада для проекта {$projectCode}</b>",
                    $keyboard
                );
            } else {
                error_log("TabelHandler: defaulting to mobile brigade keyboard");
                $keyboard = $this->messageService->createMobileBrigadeKeyboard();
                error_log("TabelHandler: mobile brigade keyboard created with " . count($keyboard) . " rows");
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
        error_log("TabelHandler: DEBUG - isActivityConfirmation called with: '{$category}'");
        error_log("TabelHandler: DEBUG - category bytes: " . bin2hex($category));
        
        // Check standard activity types
        foreach ($this->activityTypes as $activity) {
            // Skip "Прочее" activities - they should trigger custom input, not confirmation
            if (str_contains($activity, 'Прочее')) {
                continue;
            }
            
            // Skip "ПодготовкаТКП" - it should show submenu, not direct confirmation
            if ($activity === 'ПодготовкаТКП') {
                continue;
            }
            
            // Skip "ШМР" - it should show supervision works submenu, not direct confirmation
            if ($activity === 'ШМР') {
                continue;
            }
            
            // Check both full and short callback formats
            $pattern1 = "category_Табель_{$activity}";
            $pattern2 = "Табель_{$activity}";
            
            error_log("TabelHandler: DEBUG - Testing activity '{$activity}' patterns: '{$pattern1}' and '{$pattern2}'");
            
            if ($category === $pattern1 || $category === $pattern2) {
                error_log("TabelHandler: DEBUG - MATCH FOUND! Returning true for activity: {$activity}");
                return true;
            }
        }
        
        // Check idle menu final confirmations (when user confirms after selecting detailed reason)
        if (str_contains($category, '_yes') && 
            (str_contains($category, 'Табель_Простой') || str_contains($category, 'Табель_ШМ_Простой'))) {
            return true;
        }
        
        // Handle short callback format for electrical equipment
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_ЭлектрОб',
            'ФормИД' => 'ФормИД_ЭлектрОб',
            'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
            'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
            'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
            'ДопРаб' => 'ДопРаб_ЭлектрОб',
            'ПодтВех' => 'ПодтВех_ЭлектрОб',
            // Removed 'Проч' mapping to allow custom input handling
            // Handle short callback format for commissioning works
            'ТабПНРаботы' => 'ПусконаладочныеРаботы',
            // Handle basic commissioning activities
            'ТабИнстр' => 'Инструктаж',
            'ТабДорога' => 'Дорога',
            'ТабПодгПоезд' => 'ПодготовкаКПоездке',
            'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
            'ТабОхрСигн' => 'ОхраннаяСигнализация',
            'ТабОтоплВент' => 'ОтоплениеИВентиляция',
            'ТабТехОб' => 'ТехнологическоеОборудование',
            'ТабМетрол' => 'Метрология',
            'ТабКомплОпр' => 'КомплексноеОпробование',
            'ТабРежНал' => 'РежимнаяНаладка'
        ];
        
        foreach ($shortToFullMap as $shortKey => $fullActivity) {
            if ($category === "cat_ТабЭлОб_{$shortKey}") {
                error_log("TabelHandler: isActivityConfirmation returning true for short electrical activity: {$shortKey}");
                return true;
            }
            // Handle short callback format for commissioning works
            if ($category === "cat_{$shortKey}") {
                error_log("TabelHandler: isActivityConfirmation returning true for short commissioning works: {$shortKey}");
                return true;
            }
        }
        
        error_log("TabelHandler: isActivityConfirmation returning false for category: {$category}");
        return false;
    }

    private function handleActivityConfirmation(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        

        
        // Extract activity type from category
        $activityType = '';
        
        // Handle short callback format for electrical equipment and commissioning works
        $shortToFullMap = [
            'РабПоГр' => 'РабПоГр_ЭлектрОб',
            'ФормИД' => 'ФормИД_ЭлектрОб',
            'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
            'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
            'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
            'ДопРаб' => 'ДопРаб_ЭлектрОб',
            'ПодтВех' => 'ПодтВех_ЭлектрОб',
            // Removed 'Проч' mapping to allow custom input handling
            // Handle short callback format for commissioning works
            'ТабПНРаботы' => 'ПусконаладочныеРаботы',
            // Handle basic commissioning activities
            'ТабИнстр' => 'Инструктаж',
            'ТабДорога' => 'Дорога',
            'ТабПодгПоезд' => 'ПодготовкаКПоездке',
            'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
            'ТабОхрСигн' => 'ОхраннаяСигнализация',
            'ТабОтоплВент' => 'ОтоплениеИВентиляция',
            'ТабТехОб' => 'ТехнологическоеОборудование',
            'ТабМетрол' => 'Метрология',
            'ТабКомплОпр' => 'КомплексноеОпробование',
            'ТабРежНал' => 'РежимнаяНаладка'
        ];
        
        // Check if this is a short callback format
        if (preg_match('/^cat_ТабЭлОб_(.+)$/u', $category, $matches)) {
            $shortKey = $matches[1];
            if (isset($shortToFullMap[$shortKey])) {
                $activityType = $shortToFullMap[$shortKey];
            }
        } 
        // Handle short callback format for commissioning works
        elseif (preg_match('/^cat_(.+)$/u', $category, $matches)) {
            $shortKey = $matches[1];
            if (isset($shortToFullMap[$shortKey])) {
                $activityType = $shortToFullMap[$shortKey];
            } else {
                // Fallback for direct mapping
                $activityType = $shortKey === 'ТабПНРаботы' ? 'ПусконаладочныеРаботы' : str_replace('Табель_', '', $category);
            }
        }
        // Handle full callback format
        else {
            $activityType = str_replace('Табель_', '', $category);
        }
        

        
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
        
        // Create appropriate callback data based on the format
        $callbackData = '';
        if (preg_match('/^cat_ТабЭлОб_(.+)$/u', $category, $matches)) {
            // Use short format for confirmation
            $shortKey = $matches[1];
            $callbackData = "cat_ТабЭлОб_{$shortKey}_yes";
        } elseif (preg_match('/^cat_(.+)$/u', $category, $matches)) {
            // Use short format for commissioning works confirmation
            $shortKey = $matches[1];
            $callbackData = "cat_{$shortKey}_yes";
        } elseif (strpos($activityType, 'РабГарантии_') === 0) {
            // Use short format for warranty equipment activities to avoid BUTTON_DATA_INVALID error
            // Extract the equipment type (e.g., "ЭлектрОб" from "РабГарантии_ЭлектрОб")
            $equipmentType = substr($activityType, strlen('РабГарантии_'));
            $callbackData = "cat_Гарантии_{$equipmentType}_yes";
        } elseif (preg_match('/^Табель_ШМР_(.+)$/', $category)) {
            // Use short format for supervision works to avoid BUTTON_DATA_INVALID error
            $supervisionWorkKey = preg_replace('/^Табель_ШМР_/', '', $category);
            $callbackData = "cat_ТабШМР_{$supervisionWorkKey}_yes";
        } elseif ($category === 'Табель_ПроверкаМонтажнойПлощадки') {
            // CRITICAL FIX: Use short format for mounting area inspection to avoid BUTTON_DATA_INVALID error
            $callbackData = "cat_ШМР_АналФунд_yes";
        } else {
            // Use full format for confirmation
            $callbackData = "category_Табель_{$activityType}_yes";
        }
        
        $keyboard = $this->messageService->createConfirmationKeyboard($callbackData);
        

        
        return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
    }

    private function isActivityExecution(string $category): bool
    {
        error_log("TabelHandler: URGENT DEBUG - isActivityExecution called with: '{$category}'");
        error_log("TabelHandler: URGENT DEBUG - category bytes: " . bin2hex($category));
        error_log("TabelHandler: isActivityExecution called with category: {$category}");
        
        // Check if this is a menu callback that should NOT be treated as activity execution
        // These are the main equipment category callbacks that should show menus, not create reports
        $menuCallbacks = [
            'cat_ТабЭлОб',     // Electrical equipment main menu (short format)
            'cat_ТабАвтом',    // Automation equipment main menu (short format)
            'cat_ТабПожСигн',  // Fire alarm equipment main menu (short format)
            'Табель_Электрооборудование',  // Electrical equipment main menu (full format)
            'Табель_Автоматизация',        // Automation equipment main menu (full format)
            'Табель_ПожарнаяСигнализация',  // Fire alarm equipment main menu (full format)
            // Supervision department menu callbacks (CRITICAL FIX)
            'Табель_ШМ_РабГарантии',  // Supervision warranty works menu
            'Табель_ШМ_УЗ_РК'         // Supervision RK elimination menu
        ];
        
        if (in_array($category, $menuCallbacks)) {
            error_log("TabelHandler: isActivityExecution returning false for menu callback: {$category}");
            return false;
        }
        
        // Check standard activity execution
        if (str_contains($category, '_yes')) {
            // Handle both full and short callback formats
            if (preg_match('/^(category_)?Табель_(' . implode('|', $this->activityTypes) . ')_yes$/', $category, $matches)) {
                error_log("TabelHandler: isActivityExecution returning true for standard activity");
                return true;
            }
            
            // URGENT FIX: Handle mapped metrology confirmation callbacks
            if (preg_match('/^Табель_(.+)_Метрол_yes$/u', $category, $matches)) {
                error_log("🚨 URGENT: MAPPED METROLOGY CONFIRMATION DETECTED: '{$category}'");
                error_log("TabelHandler: isActivityExecution returning TRUE for mapped metrology activity");
                return true;
            }
            
            // URGENT FIX: Handle mapped metrology additional works confirmation callbacks  
            if (preg_match('/^Табель_(ИзмКонф|КалПов|КМХ|КоррХар|Проч)_Метрол_yes$/u', $category, $matches)) {
                error_log("🚨 URGENT: MAPPED METROLOGY ADDITIONAL WORKS CONFIRMATION DETECTED: '{$category}'");
                error_log("TabelHandler: isActivityExecution returning TRUE for mapped metrology additional works");
                return true;
            }
            
            // Handle short callback format for electrical equipment
            if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $category, $matches)) {
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РабПоГр_ЭлектрОб',
                    'ФормИД' => 'ФормИД_ЭлектрОб',
                    'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
                    'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
                    'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
                    'МонтОб' => 'МонтОб_ЭлектрОб',
                    'ДопРаб' => 'ДопРаб_ЭлектрОб',
                    'ПодтВех' => 'ПодтВех_ЭлектрОб'
                    // Removed 'Проч' mapping to allow custom input handling
                ];
                
                $shortActivityKey = $matches[1];
                if (isset($shortToFullMap[$shortActivityKey])) {
                    error_log("TabelHandler: isActivityExecution returning true for short electrical activity: {$shortActivityKey}");
                    return true;
                }
            }
            
            // Handle short callback format for fire alarm equipment
            if (preg_match('/^cat_ТабПожСигн_(.+)_yes$/u', $category, $matches)) {
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РабПоГр_ПожСигн',
                    'ФормИД' => 'ФормИД_ПожСигн',
                    'ИзмСх' => 'ИзмСх_ПожСигн',
                    'МонтКб' => 'МонтКб_ПожСигн',
                    'РаскКб' => 'РаскКб_ПожСигн',
                    'МонтОб' => 'МонтОб_ПожСигн',
                    'ДопРаб' => 'ДопРаб_ПожСигн',
                    'ПодтВех' => 'ПодтВех_ПожСигн',
                    'Проч' => 'Прочее_ПожСигн'  // Added back for confirmation handling
                ];
                
                $shortActivityKey = $matches[1];
                if (isset($shortToFullMap[$shortActivityKey])) {
                    error_log("TabelHandler: isActivityExecution returning true for short fire alarm activity: {$shortActivityKey}");
                    return true;
                }
            }
            
            // Handle short callback format for automation equipment
            if (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $category, $matches)) {
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РабПоГр_Автом',
                    'ФормИД' => 'ФормИД_Автом',
                    'ДопРаб' => 'ДопРаб_Автом',
                    'ПодтВех' => 'ПодтВех_Автом',
                    'ИзмСх' => 'ИзмСх_Автом',
                    'МонтКб' => 'МонтКб_Автом',
                    'РаскКб' => 'РаскКб_Автом',
                    'МонтАСУ' => 'МонтАСУ_Автом',
                    'УдалПО' => 'УдалПО_Автом',
                    'Проч' => 'Прочее_Автом'
                ];
                
                $shortActivityKey = $matches[1];
                if (isset($shortToFullMap[$shortActivityKey])) {
                    error_log("TabelHandler: isActivityExecution returning true for short automation activity: {$shortActivityKey}");
                    return true;
                }
            }
            
            // Handle short callback format for security alarm equipment
            if (preg_match('/^cat_ТабОхрСиг_(.+)_yes$/u', $category, $matches)) {
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РабПоГр_ОхрСигн',
                    'ФормИД' => 'ФормИД_ОхрСигн',
                    'ИзмСх' => 'ИзмСх_ОхрСигн',
                    'МонтКб' => 'МонтКб_ОхрСигн',
                    'РаскКб' => 'РаскКб_ОхрСигн',
                    'МонтОб' => 'МонтОб_ОхрСигн',
                    // Removed 'ДопРаб' mapping - it's a submenu, not direct activity execution
                    'ПодтВех' => 'ПодтВех_ОхрСигн'
                    // Removed 'Проч' mapping to allow custom input handling
                ];
                
                $shortActivityKey = $matches[1];
                if (isset($shortToFullMap[$shortActivityKey])) {
                    error_log("TabelHandler: isActivityExecution returning true for short security alarm activity: {$shortActivityKey}");
                    return true;
                }
            }
            
            // Handle short callback format for supervision works milestone confirmations
            if (preg_match('/^ШМР_(.+)_ПодтВех_yes$/u', $category, $matches)) {
                $activityName = $matches[1];
                error_log("TabelHandler: isActivityExecution returning true for supervision works milestone: {$activityName}");
                error_log("🔍 MILESTONE EXECUTION DETECTED: Full category='{$category}'");
                return true;
            }
            
            // Handle short callback format for supervision works schedule confirmations
            if (preg_match('/^ШМР_(.+)_РабПоГр_yes$/u', $category, $matches)) {
                $activityName = $matches[1];
                error_log("TabelHandler: isActivityExecution returning true for supervision works schedule: {$activityName}");
                error_log("🔍 SCHEDULE EXECUTION DETECTED: Full category='{$category}'");
                return true;
            }
            
            // Handle short callback format for supervision works milestone confirmations (short format)
            // IMPORTANT: Only match _yes callbacks to avoid auto-execution of confirmation dialogs
            if (preg_match('/^cat_ШМР_(.+)_ПодтВех_yes$/u', $category, $matches)) {
                error_log("TabelHandler: isActivityExecution returning true for short supervision works milestone CONFIRMATION");
                error_log("🔍 SHORT MILESTONE EXECUTION DETECTED: Full category='{$category}'");
                return true;
            }
            
            // Handle short callback format for supervision works schedule confirmations (short format)
            // IMPORTANT: Only match _yes callbacks to avoid auto-execution of confirmation dialogs
            if (preg_match('/^cat_ШМР_(.+)_РабПоГр_yes$/u', $category, $matches)) {
                error_log("TabelHandler: isActivityExecution returning true for short supervision works schedule CONFIRMATION");
                error_log("🔍 SHORT SCHEDULE EXECUTION DETECTED: Full category='{$category}'");
                return true;
            }
            
            // CRITICAL FIX: Handle milestone confirmation callbacks with format cat_ШМР_(.+)_ПодтВех_yes
            if (preg_match('/^cat_ШМР_(.+)_ПодтВех_yes$/u', $category, $matches)) {
                error_log("🎯 MILESTONE: isActivityExecution detected milestone confirmation callback: '{$category}'");
                $shortActivityKey = $matches[1];
                error_log("🎯 MILESTONE: Extracted key: '{$shortActivityKey}'");
                
                // Map milestone short keys to milestone activity types
                $milestoneMap = [
                    'ВхКонтр' => 'ШМР_ВходнойКонтроль_ПодтВех',
                    'ЗавОбор' => 'ШМР_ЗавозОборудования_ПодтВех',
                    'АналФунд' => 'ПроверкаМонтажнойПлощадки',
                    'РасстБлок' => 'ШМР_РасстановкаБлоков_ПодтВех',
                    'МонтПлощ' => 'ШМР_МонтажПлощадок_ПодтВех',
                    'МонтЭлУкр' => 'ШМР_МонтажЭлементовУкрытия_ПодтВех',
                    'СборКровл' => 'ШМР_СборкаКровли_ПодтВех',
                    'МонтТруб' => 'ШМР_МонтажТрубопроводов_ПодтВех',
                    'ГидрИсп' => 'ШМР_ГидропневмоИспытания_ПодтВех',
                    'МонтТеплИз' => 'ШМР_МонтажТеплоизоляции_ПодтВех',
                    'МонтВент' => 'ШМР_МонтажВентиляции_ПодтВех',
                    'МонтСнятОб' => 'ШМР_МонтажСнятогоОборудования_ПодтВех',
                    'МонтКаб' => 'ШМР_МонтажКабельнойПродукции_ПодтВех'
                ];
                
                if (isset($milestoneMap[$shortActivityKey])) {
                    error_log("🎯 MILESTONE: Key found in map, activity: {$milestoneMap[$shortActivityKey]}");
                    error_log("TabelHandler: isActivityExecution returning true for milestone activity: {$shortActivityKey}");
                    return true;
                } else {
                    error_log("🎯 MILESTONE: Key NOT found in milestone map!");
                }
            }
            
            // Handle simple supervision works confirmation callbacks (not schedule or milestone)
            if (preg_match('/^ШМР_(.+)_yes$/u', $category, $matches)) {
                error_log("🎯 SUPERVISION: isActivityExecution detected simple supervision callback: '{$category}'");
                $shortActivityKey = $matches[1];
                error_log("🎯 SUPERVISION: Extracted key: '{$shortActivityKey}'");
                // Map simple supervision activities - INCLUDES MILESTONE CONFIRMATION
                $simpleSupervisionMap = [
                    'АналФунд' => 'ПроверкаМонтажнойПлощадки',
                    'ПодпАкт' => 'ПодписаниеАктаШМР',
                    'ПодтВех' => 'ПодтверждениеВехи_ШМР'
                ];
                
                if (isset($simpleSupervisionMap[$shortActivityKey])) {
                    error_log("🎯 SUPERVISION: Key found in map, activity: {$simpleSupervisionMap[$shortActivityKey]}");
                    error_log("TabelHandler: isActivityExecution returning true for simple supervision activity: {$shortActivityKey}");
                    return true;
                } else {
                    error_log("🎯 SUPERVISION: Key NOT found in map!");
                }
            }
            
            // Handle short callback format for supervision works
            if (preg_match('/^cat_ТабШМР_(.+)_yes$/u', $category, $matches)) {
                // Map short format to full activity type
                $shortToFullMap = [
                    'РаботыПоГрафику' => 'ШМР_РаботыПоГрафику',
                    'ПодготовкаПоездки' => 'ШМР_ПодготовкаПоездки',
                    'Дорога' => 'ШМР_Дорога',
                    'Инструктаж' => 'ШМР_Инструктаж',
                    'СдачаОтчета' => 'ШМР_СдачаОтчета'
                ];
                
                $shortActivityKey = $matches[1];
                if (isset($shortToFullMap[$shortActivityKey])) {
                    error_log("TabelHandler: isActivityExecution returning true for short supervision works activity: {$shortActivityKey}");
                    return true;
                }
            }
            
            // Handle short callback format for technological equipment
            if (preg_match('/^cat_ТабТехОб_(.+)_yes$/u', $category, $matches)) {
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РабПоГр_ТехОб',
                    'ФормИД' => 'ФормИД_ТехОб',
                    'ДопРаб' => 'ДопРаб_ТехОб',
                    'ПодтВех' => 'ПодтВех_ТехОб',
                    'ДемАрм' => 'ДемАрм_ТехОб',
                    'ДемОб' => 'ДемОб_ТехОб',
                    'Проч' => 'Прочее_ТехОб'
                ];
                
                $shortActivityKey = $matches[1];
                if (isset($shortToFullMap[$shortActivityKey])) {
                    error_log("TabelHandler: isActivityExecution returning true for short technological equipment activity: {$shortActivityKey}");
                    return true;
                }
            }
            
            // Handle short callback format for metrology equipment
            if (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $category, $matches)) {
                error_log("TabelHandler: URGENT DEBUG - METROLOGY CONFIRMATION DETECTED: '{$category}'");
                // Map short format to full activity type
                $shortToFullMap = [
                    'РабПоГр' => 'РабПоГр_Метрол',
                    'ФормИД' => 'ФормИД_Метрол',
                    'ДопРаб' => 'ДопРаб_Метрол',
                    'ПодтВех' => 'ПодтВех_Метрол',
                    'ИзмКонф' => 'ИзмКонф_Метрол',
                    'КалПов' => 'КалПов_Метрол',
                    'КМХ' => 'КМХ_Метрол',
                    'КоррХар' => 'КоррХар_Метрол',
                    'Проч' => 'Проч_Метрол'
                ];
                
                $shortActivityKey = $matches[1];
                error_log("TabelHandler: URGENT DEBUG - METROLOGY extracted key: '{$shortActivityKey}'");
                if (isset($shortToFullMap[$shortActivityKey])) {
                    error_log("TabelHandler: isActivityExecution returning true for short metrology activity: {$shortActivityKey}");
                    return true;
                } else {
                    error_log("TabelHandler: URGENT DEBUG - METROLOGY key '{$shortActivityKey}' NOT FOUND in map!");
                }
            }
            
            // Handle short callback format for warranty equipment - CRITICAL FOR WARRANTY BUTTON FUNCTIONALITY
            if (preg_match('/^cat_Гарантии_(.+)_yes$/u', $category, $matches)) {
                $equipmentType = $matches[1];
                error_log("TabelHandler: short warranty equipment confirmation detected: '{$equipmentType}'");
                
                // Map equipment types to activity types
                $warrantyEquipmentMap = [
                    'ЭлектрОб' => 'РабГарантии_ЭлектрОб',
                    'СрИзм' => 'РабГарантии_СрИзм',
                    'НасОб' => 'РабГарантии_НасОб',
                    'ТрубАрм' => 'РабГарантии_ТрубАрм',
                    'ИспМех' => 'РабГарантии_ИспМех',
                    'КонтрОб' => 'РабГарантии_КонтрОб',
                    'Прочее' => 'РабГарантии_Прочее'
                ];
                
                if (isset($warrantyEquipmentMap[$equipmentType])) {
                    error_log("TabelHandler: isActivityExecution returning true for short warranty equipment: {$equipmentType}");
                    return true;
                }
            }
            
            // Handle transformed warranty equipment confirmation callbacks (after mapping from short format)
            if (preg_match('/^РабГарантии_(.+)_yes$/u', $category, $matches)) {
                $equipmentType = $matches[1];
                error_log("TabelHandler: transformed warranty equipment confirmation detected: '{$equipmentType}'");
                
                // Check if this is a valid warranty equipment type
                $validWarrantyEquipment = [
                    'ЭлектрОб', 'СрИзм', 'НасОб', 'ТрубАрм', 'ИспМех', 'КонтрОб', 'Прочее'
                ];
                
                if (in_array($equipmentType, $validWarrantyEquipment)) {
                    error_log("TabelHandler: isActivityExecution returning true for transformed warranty equipment: {$equipmentType}");
                    return true;
                }
            }
            
            // Handle short callback format for project activity
            if (preg_match('/^cat_ТабПроектнаяДеят_(.+)_yes$/u', $category, $matches)) {
                // Map short format to full activity type
                $shortToFullMap = [
                    'Проч' => 'Прочее_ПроектнаяДеятельность',
                    'Проч_ШМР' => 'Прочее_ПроектнаяДеятельность_ШМР'
                ];
                
                $shortActivityKey = $matches[1];
                if (isset($shortToFullMap[$shortActivityKey])) {
                    error_log("TabelHandler: isActivityExecution returning true for short project activity: {$shortActivityKey}");
                    return true;
                }
            }
            
            // Handle short callback format for commissioning works
            if (preg_match('/^cat_(.+)_yes$/u', $category, $matches)) {
                $shortActivityKey = $matches[1];
                if ($shortActivityKey === 'ТабПНРаботы') {
                    error_log("TabelHandler: isActivityExecution returning true for short commissioning works: {$shortActivityKey}");
                    return true;
                }
                
                // Handle basic commissioning activities (Инструктаж, Дорога, etc.)
                $basicCommissioningActivities = [
                    'ТабИнстр' => 'Инструктаж',
                    'ТабДорога' => 'Дорога',
                    'ТабПодгПоезд' => 'ПодготовкаКПоездке',
                    'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
                    'ТабОхрСигн' => 'ОхраннаяСигнализация',
                    'ТабОтоплВент' => 'ОтоплениеИВентиляция',
                    'ТабТехОб' => 'ТехнологическоеОборудование',
                    'ТабМетрол' => 'Метрология',
                    'ТабКомплОпр' => 'КомплексноеОпробование',
                    'ТабРежНал' => 'РежимнаяНаладка'
                ];
                
                if (isset($basicCommissioningActivities[$shortActivityKey])) {
                    $activityType = $basicCommissioningActivities[$shortActivityKey];
                    if (in_array($activityType, $this->activityTypes)) {
                        error_log("TabelHandler: isActivityExecution returning true for basic commissioning activity: {$shortActivityKey} -> {$activityType}");
                        return true;
                    }
                }
                
                // Handle project activity buttons with short format
                $projectActivityCallbacks = [
                    'ТабИсполнОбяз' => 'ИспОбМаст'
                ];
                
                if (isset($projectActivityCallbacks[$shortActivityKey])) {
                    $activityType = $projectActivityCallbacks[$shortActivityKey];
                    if (in_array($activityType, $this->activityTypes)) {
                        error_log("TabelHandler: isActivityExecution returning true for project activity: {$shortActivityKey} -> {$activityType}");
                        return true;
                    }
                }
                
                // Handle supervision works activities
                if (preg_match('/^Табель_ШМР_(.+)$/', $shortActivityKey, $matches)) {
                    $supervisionWorkKey = $matches[1];
                    $supervisionWorksMap = [
                        'РаботыПоГрафику' => 'ШМР_РаботыПоГрафику',
                        'ПодготовкаПоездки' => 'ШМР_ПодготовкаПоездки',
                        'Дорога' => 'ШМР_Дорога',
                        'Инструктаж' => 'ШМР_Инструктаж',
                        'СдачаОтчета' => 'ШМР_СдачаОтчета'
                    ];
                    
                    if (isset($supervisionWorksMap[$supervisionWorkKey])) {
                        error_log("TabelHandler: isActivityExecution returning true for supervision works activity: {$shortActivityKey} -> {$supervisionWorksMap[$supervisionWorkKey]}");
                        return true;
                    }
                }
                
                // NOTE: Removed 'cat_ТабЭлОб_Проч_yes' handling here 
                // because it's now handled by handleNewProcheeConfirmation in routeTabelCallback
            }
        }
        
        // Check idle menu execution (when user confirms idle activity with detailed reason)
        if (str_contains($category, '_yes') && 
            (str_contains($category, 'Табель_Простой_yes') || str_contains($category, 'Табель_ШМ_Простой_yes'))) {
            error_log("TabelHandler: isActivityExecution returning true for idle activity");
            return true;
        }
        
        error_log("TabelHandler: isActivityExecution returning false");
        return false;
    }

    private function handleActivityExecution(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        error_log("=== МЕТРОЛОГИЯ DEBUG: handleActivityExecution CALLED ===");
        error_log("TabelHandler: handleActivityExecution called with category: '{$category}'");
        
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("TabelHandler: chatId: {$chatId}, userId: {$userId}");
        
        // Extract activity type using regex to handle both formats correctly
        $activityType = '';
        
        // Handle short callback format for electrical equipment FIRST (this should come before other checks)
        if (preg_match('/^cat_ТабЭлОб_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for electrical equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_ЭлектрОб',
                'ФормИД' => 'ФормИД_ЭлектрОб',
                'ИзмСхПд' => 'ИзмСхПд_ЭлектрОб',
                'МонтКбЛн' => 'МонтКбЛн_ЭлектрОб',
                'РаскКбЛн' => 'РаскКбЛн_ЭлектрОб',
                'МонтОб' => 'МонтОб_ЭлектрОб',
                'ДопРаб' => 'ДопРаб_ЭлектрОб',
                'ПодтВех' => 'ПодтВех_ЭлектрОб'
                // Removed 'Проч' mapping to allow custom input handling
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        } 
        // Handle short callback format for fire alarm equipment
        elseif (preg_match('/^cat_ТабПожСигн_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for fire alarm equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_ПожСигн',
                'ФормИД' => 'ФормИД_ПожСигн',
                'ИзмСх' => 'ИзмСх_ПожСигн',
                'МонтКб' => 'МонтКб_ПожСигн',
                'РаскКб' => 'РаскКб_ПожСигн',
                'МонтОб' => 'МонтОб_ПожСигн',
                'ДопРаб' => 'ДопРаб_ПожСигн',
                'ПодтВех' => 'ПодтВех_ПожСигн',
                'Проч' => 'Прочее_ПожСигн'  // Added back for confirmation handling
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        }
        // Handle short callback format for automation equipment
        elseif (preg_match('/^cat_ТабАвтом_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for automation equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_Автом',
                'ФормИД' => 'ФормИД_Автом',
                'ДопРаб' => 'ДопРаб_Автом',
                'ПодтВех' => 'ПодтВех_Автом',
                'ИзмСх' => 'ИзмСх_Автом',
                'МонтКб' => 'МонтКб_Автом',
                'РаскКб' => 'РаскКб_Автом',
                'МонтАСУ' => 'МонтАСУ_Автом',
                'УдалПО' => 'УдалПО_Автом',
                'Проч' => 'Прочее_Автом'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        }
        // Handle short callback format for security alarm equipment
        elseif (preg_match('/^cat_ТабОхрСиг_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for security alarm equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_ОхрСигн',
                'ФормИД' => 'ФормИД_ОхрСигн',
                'ИзмСх' => 'ИзмСх_ОхрСигн',
                'МонтКб' => 'МонтКб_ОхрСигн',
                'РаскКб' => 'РаскКб_ОхрСигн',
                'МонтОб' => 'МонтОб_ОхрСигн',
                // Removed 'ДопРаб' mapping - it's a submenu, not direct activity execution
                'ПодтВех' => 'ПодтВех_ОхрСигн'
                // Removed 'Проч' mapping to allow custom input handling
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        }
        // Handle supervision works milestone confirmations
        elseif (preg_match('/^ШМР_(.+)_ПодтВех_yes$/u', $category, $matches)) {
            $activityName = $matches[1];
            $activityType = "ШМР_{$activityName}_ПодтВех";
            error_log("TabelHandler: handleActivityExecution mapped supervision milestone: {$activityName} -> {$activityType}");
        }
        // Handle supervision works schedule confirmations
        elseif (preg_match('/^ШМР_(.+)_РабПоГр_yes$/u', $category, $matches)) {
            $activityName = $matches[1];
            $activityType = "ШМР_{$activityName}_РабПоГр";
            error_log("TabelHandler: handleActivityExecution mapped supervision schedule: {$activityName} -> {$activityType}");
        }
        // Handle short callback format for supervision works milestone confirmations
        elseif (preg_match('/^cat_ШМР_(.+)_ПодтВех_yes$/u', $category, $matches)) {
            // Map short format to full activity type
            $shortToFullMap = [
                'ВхКонтр' => 'ШМР_ВходнойКонтроль_ПодтВех',
                'ЗавОбор' => 'ШМР_ЗавозОборудования_ПодтВех',
                'АналФунд' => 'ПроверкаМонтажнойПлощадки',
                'РасстБлок' => 'ШМР_РасстановкаБлоков_ПодтВех',
                'МонтПлощ' => 'ШМР_МонтажПлощадокОбслуживания_ПодтВех',
                'МонтЭлУкр' => 'ШМР_МонтажЭлементовУкрытия_ПодтВех',
                'СборКровл' => 'ШМР_СборкаКровли_ПодтВех',
                'МонтТруб' => 'ШМР_МонтажТрубопроводов_ПодтВех',
                'ГидрИсп' => 'ШМР_ГидропневмоИспытания_ПодтВех',
                'МонтТеплИз' => 'ШМР_МонтажТеплоизоляции_ПодтВех',
                'МонтВент' => 'ШМР_МонтажВентиляции_ПодтВех',
                'МонтСнятОб' => 'ШМР_МонтажСнятогоОборудования_ПодтВех',
                'МонтКаб' => 'ШМР_МонтажКабельнойПродукции_ПодтВех'
            ];
            
            $shortActivityKey = $matches[1];
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
                error_log("TabelHandler: handleActivityExecution mapped short supervision milestone: {$shortActivityKey} -> {$activityType}");
            } else {
                error_log("TabelHandler: handleActivityExecution could not map short supervision milestone: {$shortActivityKey}");
            }
        }
        // Handle short callback format for supervision works schedule confirmations
        elseif (preg_match('/^cat_ШМР_(.+)_РабПоГр_yes$/u', $category, $matches)) {
            // Map short format to full activity type
            $shortToFullMap = [
                'ВхКонтр' => 'ШМР_ВхОднойКонтроль_РабПоГр',
                'ЗавОбор' => 'ШМР_ЗавозОборудования_РабПоГр',
                'АналФунд' => 'ПроверкаМонтажнойПлощадки',
                'РасстБлок' => 'ШМР_РасстановкаБлоков_РабПоГр',
                'МонтПлощ' => 'ШМР_МонтажПлощадок_РабПоГр',
                'МонтЭлУкр' => 'ШМР_МонтажЭлементовУкрытия_РабПоГр',
                'СборКровл' => 'ШМР_СборкаКровли_РабПоГр',
                'МонтТруб' => 'ШМР_МонтажТрубопроводов_РабПоГр',
                'ГидрИсп' => 'ШМР_ГидропневмоИспытания_РабПоГр',
                'МонтТеплИз' => 'ШМР_МонтажТеплоизоляции_РабПоГр',
                'МонтВент' => 'ШМР_МонтажВентиляции_РабПоГр',
                'МонтСнятОб' => 'ШМР_МонтажСнятогоОборудования_РабПоГр',
                'МонтКаб' => 'ШМР_МонтажКабельнойПродукции_РабПоГр'
            ];
            
            $shortActivityKey = $matches[1];
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
                error_log("TabelHandler: handleActivityExecution mapped short supervision schedule: {$shortActivityKey} -> {$activityType}");
            } else {
                error_log("TabelHandler: handleActivityExecution could not map short supervision schedule: {$shortActivityKey}");
            }
        }
        // Handle simple supervision works confirmation callbacks (not schedule or milestone)
        elseif (preg_match('/^cat_ШМР_(.+)_yes$/u', $category, $matches)) {
            $shortActivityKey = $matches[1];
            // Map simple supervision activities - INCLUDES MILESTONE CONFIRMATION
            $simpleSupervisionMap = [
                'АналФунд' => 'ПроверкаМонтажнойПлощадки',
                'ПодпАкт' => 'ПодписаниеАктаШМР',
                'ПодтВех' => 'ПодтверждениеВехи_ШМР'
            ];
            
            if (isset($simpleSupervisionMap[$shortActivityKey])) {
                $activityType = $simpleSupervisionMap[$shortActivityKey];
                error_log("TabelHandler: handleActivityExecution mapped simple supervision activity: {$shortActivityKey} -> {$activityType}");
                error_log("📢 SUPERVISION DEBUG: Successfully mapped callback to activity type: {$activityType}");
            } else {
                error_log("TabelHandler: handleActivityExecution could not map simple supervision activity: {$shortActivityKey}");
            }
        }
        // Handle short callback format for supervision works
        elseif (preg_match('/^cat_ТабШМР_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for supervision works
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РаботыПоГрафику' => 'ШМР_РаботыПоГрафику',
                'ПодготовкаПоездки' => 'ШМР_ПодготовкаПоездки',
                'Дорога' => 'ШМР_Дорога',
                'Инструктаж' => 'ШМР_Инструктаж',
                'СдачаОтчета' => 'ШМР_СдачаОтчета'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        }
        // Handle short callback format for technological equipment
        elseif (preg_match('/^cat_ТабТехОб_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for technological equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_ТехОб',
                'ФормИД' => 'ФормИД_ТехОб',
                'ДопРаб' => 'ДопРаб_ТехОб',
                'ПодтВех' => 'ПодтВех_ТехОб',
                'ДемАрм' => 'ДемАрм_ТехОб',
                'ДемОб' => 'ДемОб_ТехОб',
                'Проч' => 'Прочее_ТехОб'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        }
        // Handle short callback format for heating and ventilation equipment
        elseif (preg_match('/^cat_ТабВентОтопл_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for heating and ventilation equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_ВентОтопл',
                'ФормИД' => 'ФормИД_ВентОтопл',
                'ИзмСх' => 'ИзмСх_ВентОтопл',
                'МонтКб' => 'МонтКб_ВентОтопл',
                'РаскКб' => 'РаскКб_ВентОтопл',
                'МонтАСУ' => 'МонтАСУ_ВентОтопл',
                'УдалПО' => 'УдалПО_ВентОтопл',
                'ДопРаб' => 'ДопРаб_ВентОтопл',
                'ПодтВех' => 'ПодтВех_ВентОтопл',
                'Проч' => 'Прочее_ВентОтопл'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
            }
        }
        // Handle short callback format for metrology equipment
        elseif (preg_match('/^cat_ТабМетрол_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for metrology equipment
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'РабПоГр' => 'РабПоГр_Метрол',
                'ФормИД' => 'ФормИД_Метрол',
                'ДопРаб' => 'ДопРаб_Метрол',
                'ПодтВех' => 'ПодтВех_Метрол',
                'ИзмКонф' => 'ИзмКонф_Метрол',
                'КалПов' => 'КалПов_Метрол',
                'КМХ' => 'КМХ_Метрол',
                'КоррХар' => 'КоррХар_Метрол',
                'Проч' => 'Проч_Метрол'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
                error_log("TabelHandler: МЕТРОЛОГИЯ DEBUG - Mapped metrology activity: {$shortActivityKey} -> {$activityType}");
            }
        }
        // Handle short callback format for project activity
        elseif (preg_match('/^cat_ТабПроектнаяДеят_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for project activity
            $shortActivityKey = $matches[1];
            // Map short format to full activity type
            $shortToFullMap = [
                'Проч' => 'Прочее_ПроектнаяДеятельность'
            ];
            
            if (isset($shortToFullMap[$shortActivityKey])) {
                $activityType = $shortToFullMap[$shortActivityKey];
                error_log("TabelHandler: handleActivityExecution mapped project activity: {$shortActivityKey} -> {$activityType}");
            }
        }
        // Handle warranty equipment confirmation callbacks (after mapping from short format)
        elseif (preg_match('/^РабГарантии_(.+)_yes$/u', $category, $matches)) {
            // Handle warranty equipment confirmation callbacks that have been mapped from short format
            $equipmentType = $matches[1];
            $activityType = 'РабГарантии_' . $equipmentType;
            error_log("🔧 WARRANTY DEBUG: Mapped warranty equipment confirmation (mapped format): {$equipmentType} -> {$activityType}");
            
            // For warranty Прочее equipment, get custom input text
            if ($equipmentType === 'Прочее' && !empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom equipment name for warranty: '{$idleReason}'");
            }
        }
        // URGENT FIX: Handle mapped metrology confirmation callbacks
        elseif (preg_match('/^Табель_(.+)_Метрол_yes$/u', $category, $matches)) {
            error_log("🚨 URGENT: PROCESSING MAPPED METROLOGY CONFIRMATION: '{$category}'");
            $activityKey = $matches[1];
            error_log("🚨 URGENT: Extracted activity key: '{$activityKey}'");
            
            // Map the extracted key to full activity type
            $mappedToFullMap = [
                'РабПоГр' => 'РабПоГр_Метрол',
                'ФормИД' => 'ФормИД_Метрол',
                'ПодтВех' => 'ПодтВех_Метрол',
                'ДопРаб' => 'ДопРаб_Метрол',
                'ИзмКонф' => 'ИзмКонф_Метрол',
                'КалПов' => 'КалПов_Метрол',
                'КМХ' => 'КМХ_Метрол',
                'КоррХар' => 'КоррХар_Метрол',
                'Проч' => 'Проч_Метрол'
            ];
            
            if (isset($mappedToFullMap[$activityKey])) {
                $activityType = $mappedToFullMap[$activityKey];
                error_log("🚨 URGENT: MAPPED TO ACTIVITY TYPE: '{$activityType}'");
            } else {
                error_log("🚨 ERROR: Activity key '{$activityKey}' not found in mapping!");
            }
        }
        // URGENT FIX: Handle mapped metrology additional works confirmation callbacks
        elseif (preg_match('/^Табель_(ИзмКонф|КалПов|КМХ|КоррХар|Проч)_Метрол_yes$/u', $category, $matches)) {
            error_log("🚨 URGENT: PROCESSING MAPPED METROLOGY ADDITIONAL WORKS: '{$category}'");
            $additionalWorkKey = $matches[1];
            error_log("🚨 URGENT: Extracted additional work key: '{$additionalWorkKey}'");
            
            // Map the additional work key to full activity type
            $additionalWorkMap = [
                'ИзмКонф' => 'ИзмКонф_Метрол',
                'КалПов' => 'КалПов_Метрол',
                'КМХ' => 'КМХ_Метрол',
                'КоррХар' => 'КоррХар_Метрол',
                'Проч' => 'Проч_Метрол'
            ];
            
            if (isset($additionalWorkMap[$additionalWorkKey])) {
                $activityType = $additionalWorkMap[$additionalWorkKey];
                error_log("🚨 URGENT: MAPPED ADDITIONAL WORK TO ACTIVITY TYPE: '{$activityType}'");
            } else {
                error_log("🚨 ERROR: Additional work key '{$additionalWorkKey}' not found in mapping!");
            }
        }
        // Handle short callback format for warranty equipment - CRITICAL FOR WARRANTY BUTTON FUNCTIONALITY
        elseif (preg_match('/^cat_Гарантии_(.+)_yes$/u', $category, $matches)) {
            // Handle short callback format for warranty equipment
            $equipmentType = $matches[1];
            $activityType = 'РабГарантии_' . $equipmentType;
            error_log("🔧 WARRANTY DEBUG: Mapped warranty equipment confirmation: {$equipmentType} -> {$activityType}");
            
            // For warranty Прочее equipment, get custom input text
            if ($equipmentType === 'Прочее' && !empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom equipment name for warranty: '{$idleReason}'");
            }
        }
        // Handle short callback format for commissioning works
        elseif (preg_match('/^cat_(.+)_yes$/u', $category, $matches)) {
            $shortActivityKey = $matches[1];
            if ($shortActivityKey === 'ТабПНРаботы') {
                $activityType = 'ПусконаладочныеРаботы';
            }
            
            // Handle basic commissioning activities (Инструктаж, Дорога, etc.)
            $basicCommissioningActivities = [
                'ТабИнстр' => 'Инструктаж',
                'ТабДорога' => 'Дорога',
                'ТабПодгПоезд' => 'ПодготовкаКПоездке',
                'ТабСдачаОтч' => 'СдачаАвансовогоОтчета',
                'ТабОхрСигн' => 'ОхраннаяСигнализация',
                'ТабОтоплВент' => 'ОтоплениеИВентиляция',
                'ТабТехОб' => 'ТехнологическоеОборудование',
                'ТабМетрол' => 'Метрология',
                'ТабКомплОпр' => 'КомплексноеОпробование',
                'ТабРежНал' => 'РежимнаяНаладка'
            ];
            
            if (isset($basicCommissioningActivities[$shortActivityKey])) {
                $activityType = $basicCommissioningActivities[$shortActivityKey];
            }
            
            // Handle project activity buttons with short format
            $projectActivityCallbacks = [
                'ТабИсполнОбяз' => 'ИспОбМаст'
            ];
            
            if (isset($projectActivityCallbacks[$shortActivityKey])) {
                $activityType = $projectActivityCallbacks[$shortActivityKey];
            }
                            
            // Handle automation equipment Прочее confirmation
            if ($shortActivityKey === 'ТабАвтом_Проч') {
                $activityType = 'Прочее_Автом';
            }
                            
            // Handle fire alarm equipment Прочее confirmation  
            if ($shortActivityKey === 'ТабПожСигн_Проч') {
                $activityType = 'Прочее_ПожСигн';
            }
                            
            // Handle security alarm equipment Прочее confirmation
            if ($shortActivityKey === 'ТабОхрСиг_Проч') {
                $activityType = 'Прочее_ОхрСигн';
            }
            // Handle supervision works activities
            if (preg_match('/^Табель_ШМР_(.+)$/', $shortActivityKey, $matches)) {
                $supervisionWorkKey = $matches[1];
                $supervisionWorksMap = [
                    'РаботыПоГрафику' => 'ШМР_РаботыПоГрафику',
                    'ПодготовкаПоездки' => 'ШМР_ПодготовкаПоездки',
                    'Дорога' => 'ШМР_Дорога',
                    'Инструктаж' => 'ШМР_Инструктаж',
                    'СдачаОтчета' => 'ШМР_СдачаОтчета'
                ];
                
                if (isset($supervisionWorksMap[$supervisionWorkKey])) {
                    $activityType = $supervisionWorksMap[$supervisionWorkKey];
                }
            }
            // NOTE: Removed handling for 'ТабЭлОб_Проч' here
            // because it's now handled by handleNewProcheeConfirmation
        }
        // Handle full callback format
        elseif (preg_match('/^(category_)?Табель_(.+)_yes$/', $category, $matches)) {
            $activityType = $matches[2];
            
            // Handle automation equipment additional works confirmations
            if (preg_match('/^(.+)_Автоматизация$/', $activityType, $subMatches)) {
                $workType = $subMatches[1];
                $automationWorksMap = [
                    'ИзмСх' => 'ИзмСх_Автом',
                    'МонтКб' => 'МонтКб_Автом',
                    'РаскКб' => 'РаскКб_Автом',
                    'МонтАСУ' => 'МонтАСУ_Автом',
                    'УдалПО' => 'УдалПО_Автом',
                    'Прочее' => 'Прочее_Автом'
                ];
                
                if (isset($automationWorksMap[$workType])) {
                    $activityType = $automationWorksMap[$workType];
                }
            }
            
            // Handle supervision works activities
            // Map supervision works sub-activities to their proper activity types
            $supervisionWorksMap = [
                'category_Табель_ШМР_РаботыПоГрафику_yes' => 'ШМР_РаботыПоГрафику',
                'category_Табель_ШМР_ПодготовкаПоездки_yes' => 'ШМР_ПодготовкаПоездки',
                'category_Табель_ШМР_Дорога_yes' => 'ШМР_Дорога',
                'category_Табель_ШМР_Инструктаж_yes' => 'ШМР_Инструктаж',
                'category_Табель_ШМР_СдачаОтчета_yes' => 'ШМР_СдачаОтчета'
            ];
            
            if (isset($supervisionWorksMap[$category])) {
                $activityType = $supervisionWorksMap[$category];
            }
        }
        
        $userData = \pDB::getUserData($userId);
        $fio = $userData['fio'] ?? 'Без ФИО';
        $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        // For commissioning activities, provide better defaults if missing
        if ($projectCode === 'Unknown' && !empty($activityType)) {
            // Check if this is a commissioning activity
            $commissioningActivities = [
                'Инструктаж', 'Дорога', 'ПодготовкаКПоездке', 'СдачаАвансовогоОтчета',
                'ОхраннаяСигнализация', 'ОтоплениеИВентиляция', 'ТехнологическоеОборудование',
                'Метрология', 'КомплексноеОпробование', 'РежимнаяНаладка', 'ПусконаладочныеРаботы',
                'ШМР_РаботыПоГрафику', 'ШМР_ПодготовкаПоездки', 'ШМР_Дорога', 'ШМР_Инструктаж', 'ШМР_СдачаОтчета'
            ];
            
            if (in_array($activityType, $commissioningActivities)) {
                $projectCode = 'Пусконаладочные работы';
                error_log("TabelHandler: Using default project code for commissioning activity: {$activityType}");
            }
        }
        
        // Get idle reason if available
        $idleReason = $conversation->notes['idle_reason'] ?? '';
        error_log("TabelHandler: handleActivityExecution - Retrieved idleReason: '{$idleReason}'");
        
        // For "Прочее" activities with custom input, use the custom input as idle reason
        // Check both NEW and OLD storage systems for backward compatibility
        if ($activityType === 'Прочее_ЭлектрОб') {
            // First check NEW system (prochee_custom_text)
            if (!empty($conversation->notes['prochee_custom_text'])) {
                $idleReason = $conversation->notes['prochee_custom_text'];
                error_log("TabelHandler: Using NEW system custom text: '{$idleReason}'");
            }
            // Fallback to OLD system (custom_input_text) for backward compatibility
            elseif (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using OLD system custom text: '{$idleReason}'");
            }
        }
        
        // For Fire Alarm Equipment "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'Прочее_ПожСигн') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Fire Alarm: '{$idleReason}'");
            }
        }
        
        // For Security Alarm Equipment "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'Прочее_ОхрСигн') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Security Alarm: '{$idleReason}'");
            }
        }
        
        // For Automation Equipment "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'Прочее_Автом') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Automation: '{$idleReason}'");
            }
        }
        
        // For Heating and Ventilation Equipment "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'Прочее_ВентОтопл') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Heating and Ventilation: '{$idleReason}'");
            }
        }
        
        // For Project Activity "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'Прочее_ПроектнаяДеятельность') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Project Activity: '{$idleReason}'");
            }
        }
        
        // For Supervision Project Activity "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'Прочее_ПроектнаяДеятельность_ШМР') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Supervision Project Activity: '{$idleReason}'");
            }
        }
        
        // For Technological Equipment "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'Прочее_ТехОб') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Technological Equipment: '{$idleReason}'");
            }
        }
        
        // For Metrology Equipment "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'Проч_Метрол') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Metrology Equipment: '{$idleReason}'");
            }
        }
        
        // For RK Comments Elimination "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'УстрЗам_Прочее') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for RK Comments Elimination: '{$idleReason}'");
            }
        }
        
        // For Warranty Equipment "Прочее" activities with custom input, use the custom input as idle reason
        if ($activityType === 'РабГарантии_Прочее') {
            if (!empty($conversation->notes['custom_input_text'])) {
                $idleReason = $conversation->notes['custom_input_text'];
                error_log("TabelHandler: Using custom text for Warranty Equipment: '{$idleReason}'");
            }
        }
        
        // Check if user has necessary data filled
        if (!isset($userData['fio']) || $userData['fio'] === 'Без ФИО') {
            error_log("🔥 ШМР DEBUG: User validation FAILED - missing FIO: '" . ($userData['fio'] ?? 'NOT_SET') . "'");
            $text = '🚫 <b>Ошибка: Не указано ФИО</b>\n\nДля отправки отчета необходимо заполнить ФИО.\nКоманда: /fio Фамилия Имя Отчество';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Check if user has functional direction filled
        if (!isset($userData['functional_direction']) || $userData['functional_direction'] === 'Не указано') {
            error_log("🔥 ШМР DEBUG: User validation FAILED - missing functional_direction: '" . ($userData['functional_direction'] ?? 'NOT_SET') . "'");
            $text = '🚫 <b>Ошибка: Не указано функциональное направление</b>\n\nИспользуйте команду /profile для настройки.';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        error_log("🔥 ШМР DEBUG: User validation PASSED - FIO: '{$userData['fio']}', functional_direction: '{$userData['functional_direction']}'");
        
        error_log("🔥 ШМР DEBUG - About to call addEntry with: activityType='{$activityType}', userId={$userId}, fio='{$fio}', date='{$date}', projectCode='{$projectCode}', idleReason='{$idleReason}'");
        $success = $this->tabelEntry->addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $idleReason);
        
        if ($success) {
            // CRITICAL DEBUG FOR MOUNTING AREA INSPECTION
            if ($activityType === 'ПроверкаМонтажнойПлощадки') {
                error_log("🏗️ КРИТИЧЕСКАЯ ОТЛАДКА: Успешно сохранили в базу, создаем отчет!");
                error_log("🏗️ Параметры для createTabelMessage:");
                error_log("🏗️   fio: '{$fio}'");
                error_log("🏗️   date: '{$date}'");
                error_log("🏗️   projectCode: '{$projectCode}'");
                error_log("🏗️   activityType: '{$activityType}'");
                error_log("🏗️   idleReason: '{$idleReason}'");
            }
            
            $message = $this->tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $idleReason);
            
            if (empty($message)) {
                return $this->messageService->sendMessage($chatId, "🚫 Ошибка: Не удалось создать сообщение отчета");
            }
            
            return $this->messageService->sendMessage($chatId, $message);
        }
        
        return $this->messageService->sendMessage($chatId, '🚫 <b>Ошибка базы данных</b>\nНе удалось сохранить запись в табель');
    }

    private function handleOtherWorkTypes(string $chatId, string $category, Conversation $conversation): ServerResponse
    {
        error_log("🎯 DEBUG: handleOtherWorkTypes called with category: '{$category}'");
        
        // 🔥 SUPERVISION WORKS CRITICAL DEBUG
        if (str_contains($category, 'Подготовительные') || str_contains($category, 'ШМР') || $category === 'cat_ШМР_ПодгРаб') {
            error_log("🔥🔥🔥 SUPERVISION WORKS CATEGORY DETECTED IN handleOtherWorkTypes!");
            error_log("🔥 Raw category: '{$category}'");
            error_log("🔥 Category hex: " . bin2hex($category));
            error_log("🔥 Contains 'Подготовительные': " . (str_contains($category, 'Подготовительные') ? 'TRUE' : 'FALSE'));
            error_log("🔥 Contains 'ШМР': " . (str_contains($category, 'ШМР') ? 'TRUE' : 'FALSE'));
            error_log("🔥 Exact match 'cat_ШМР_ПодгРаб': " . (($category === 'cat_ШМР_ПодгРаб') ? 'TRUE' : 'FALSE'));
        }
        
        // 🔥 HIGH PRIORITY MASTER DUTIES DEBUG
        if ($category === 'Табель_ИспОбМаст' || str_contains($category, 'ИспОбМаст')) {
            error_log("🔥🔥🔥 MASTER DUTIES CATEGORY DETECTED AT START OF handleOtherWorkTypes!");
            error_log("🔥 Category: '{$category}'");
            error_log("🔥 Category bytes: " . bin2hex($category));
            error_log("🔥 Category length: " . strlen($category));
        }
        error_log("TabelHandler: handleOtherWorkTypes called with category: '{$category}'");
        error_log("TabelHandler: URGENT DEBUG - category bytes: " . bin2hex($category));
        error_log("TabelHandler: URGENT DEBUG - category length: " . strlen($category));
        
        // CRITICAL DEBUG: Check for exact commissioning match FIRST
        if ($category === 'Табель_Пусконаладка') {
            error_log("TabelHandler: CRITICAL - Direct commissioning match found! Category: '{$category}' - Bypassing all other logic.");
            return $this->handleCommissioningDateSelection($chatId);
        }
        
        // Fix encoding issues with category
        $category = $this->ensureUtf8Encoding($category);
        
        // Also fix known Cyrillic pattern corruptions
        $category = $this->fixKnownCyrillicPatterns($category);
        
        error_log("TabelHandler: URGENT DEBUG - after encoding fixes: '{$category}'");
        
        // Handle RK comments elimination menu (HIGHEST PRIORITY)
        error_log("TabelHandler: DEBUG - Checking RK routing conditions");
        error_log("TabelHandler: DEBUG - category === 'cat_ТабУстрЗамРК': " . ($category === 'cat_ТабУстрЗамРК' ? 'true' : 'false'));
        error_log("TabelHandler: DEBUG - category === 'ТабУстрЗамРК': " . ($category === 'ТабУстрЗамРК' ? 'true' : 'false'));
        
        if ($category === 'cat_ТабУстрЗамРК' || $category === 'ТабУстрЗамРК') {
            error_log("TabelHandler: routing to RK comments elimination sub-menu");
            return $this->handleCommissioningRKCommentsEliminationMenu($chatId, $conversation);
        }
        
        // Handle supervision warranty equipment (ШМР warranty)
        if ($category === 'Табель_ШМ_РабГарантии') {
            error_log("TabelHandler: routing to supervision warranty equipment menu");
            return $this->handleWarrantyEquipmentMenu($chatId, $conversation, 'supervision');
        }
        
        // Handle supervision RK elimination (ШМР RK)
        if ($category === 'Табель_ШМ_УЗ_РК') {
            error_log("TabelHandler: routing to supervision RK elimination menu");
            return $this->handleSupervisionRKCommentsEliminationMenu($chatId, $conversation);
        }
        
        if ($category === 'Табель_Пусконаладка') {
            error_log("TabelHandler: DEBUG - EXACT MATCH for commissioning button! Calling handleCommissioningDateSelection");
            return $this->handleCommissioningDateSelection($chatId);
        }
        
        if ($category === 'Табель_Шеф_монтаж') {
            error_log("TabelHandler: handling supervision");
            return $this->handleSupervisionDateSelection($chatId);
        }
        
        // DIRECT ELECTRICAL EQUIPMENT HANDLING - HIGHEST PRIORITY
        // Handle electrical equipment menu IMMEDIATELY (both formats)
        if ($category === 'cat_ТабЭлОб' || $category === 'Табель_Электрооборудование') {
            error_log("TabelHandler: DIRECT electrical equipment menu handling");
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        // DIRECT FIRE ALARM EQUIPMENT HANDLING - HIGHEST PRIORITY
        // Handle fire alarm equipment menu IMMEDIATELY (both formats)
        if ($category === 'cat_ТабПожСигн' || $category === 'Табель_ПожарнаяСигнализация') {
            error_log("TabelHandler: DIRECT fire alarm equipment menu handling");
            return $this->handleFireAlarmEquipmentMenu($chatId);
        }
        
        // DIRECT ADDITIONAL WORKS HANDLING - HIGHEST PRIORITY
        // Handle additional works for electrical equipment IMMEDIATELY
        if ($category === 'cat_ТабЭлОб_ДопРаб' || $category === 'ТабЭлОб_ДопРаб' || $category === 'Табель_ДопРаб_ЭлектрОб') {
            error_log("TabelHandler: DIRECT additional works menu handling");
            return $this->handleAdditionalWorksElectricalMenu($chatId);
        }
        
        // Handle Mobile Brigade
        if ($category === 'Табель_Мобильная_Бригада' || $category === 'Табель_МобБриг') {
            error_log("TabelHandler: handling mobile brigade - calling handleMobileBrigadeDateSelection");
            $result = $this->handleMobileBrigadeDateSelection($chatId);
            error_log("TabelHandler: handleMobileBrigadeDateSelection returned");
            return $result;
        }
        
        // Handle Commissioning Works (short format)
        if ($category === 'ТабПНРаботы') {
            error_log("TabelHandler: handling commissioning works - showing commissioning works menu");
            return $this->handleCommissioningWorksMenu($chatId, $conversation);
        }
        
        // Handle electrical equipment activities
        // Works by schedule for electrical equipment
        if ($category === 'ТабЭлОб_РабПоГр' || $category === 'cat_ТабЭлОб_РабПоГр') {
            error_log("TabelHandler: handling works by schedule for electrical equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РаботыПоГрафику_Электрооборудование', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // ID formation for electrical equipment
        if ($category === 'ТабЭлОб_ФормИД' || $category === 'cat_ТабЭлОб_ФормИД') {
            error_log("TabelHandler: handling ID formation for electrical equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ФормированиеИД_Электрооборудование', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Additional works for electrical equipment
        if ($category === 'ТабЭлОб_ДопРаб' || $category === 'cat_ТабЭлОб_ДопРаб') {
            error_log("TabelHandler: handling additional works for electrical equipment");
            return $this->handleAdditionalWorksElectricalMenu($chatId);
        }

        // Milestone confirmation for electrical equipment
        if ($category === 'ТабЭлОб_ПодтВех' || $category === 'cat_ТабЭлОб_ПодтВех') {
            error_log("TabelHandler: handling milestone confirmation for electrical equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПодтвДостВехи_ЭлектрОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_ПодтВех_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Individual commissioning activities with short callbacks
        // Route to sub-menus for equipment categories that have sub-activities
        if ($category === 'ТабЭлОб') {
            error_log("TabelHandler: routing to electrical equipment sub-menu");
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        if ($category === 'ТабАвтом') {
            error_log("TabelHandler: routing to automation equipment sub-menu");
            return $this->handleAutomationEquipmentMenu($chatId);
        }
        
        if ($category === 'ТабПожСигн') {
            error_log("TabelHandler: routing to fire alarm equipment sub-menu");
            return $this->handleFireAlarmEquipmentMenu($chatId);
        }
        
        // Removed redundant electrical equipment handling - now handled at top of method
        
        // Handle automation equipment menu (short format)
        if ($category === 'cat_ТабАвтом') {
            error_log("TabelHandler: routing to automation equipment sub-menu (short format)");
            return $this->handleAutomationEquipmentMenu($chatId);
        }
        
        // Handle heating and ventilation equipment menu (short format)
        if ($category === 'cat_ТабВентОтопл') {
            error_log("TabelHandler: routing to heating and ventilation equipment sub-menu (short format)");
            return $this->handleHeatingVentilationEquipmentMenu($chatId);
        }
        
        // Handle heating and ventilation equipment menu (mapped format)
        if ($category === 'Табель_ОтоплениеИВентиляция') {
            error_log("TabelHandler: routing to heating and ventilation equipment sub-menu (mapped format)");
            return $this->handleHeatingVentilationEquipmentMenu($chatId);
        }
        
        // Handle fire alarm equipment menu (short format)
        if ($category === 'cat_ТабПожСигн') {
            error_log("TabelHandler: routing to fire alarm equipment sub-menu (short format)");
            return $this->handleFireAlarmEquipmentMenu($chatId);
        }
        
        // HANDLE WARRANTY EQUIPMENT MENU - HIGHEST PRIORITY
        if ($category === 'cat_ТабГарантии' || $category === 'Табель_РаботыПоГарантии' || str_starts_with($category, 'cat_ТабГарант')) {
            error_log("TabelHandler: WARRANTY equipment menu handling for: {$category}");
            return $this->handleWarrantyEquipmentMenu($chatId, $conversation);
        }
        
        // Handle security alarm equipment menu (short format)
        if ($category === 'cat_ТабОхрСигн' || $category === 'Табель_ОхраннаяСигнализация') {
            error_log("TabelHandler: routing to security alarm equipment sub-menu (short format)");
            return $this->handleSecurityAlarmEquipmentMenu($chatId);
        }
        
        // Handle technological equipment menu (short format)
        if ($category === 'cat_ТабТехОб' || $category === 'Табель_ТехнологическоеОборудование') {
            error_log("TabelHandler: routing to technological equipment sub-menu (short format)");
            return $this->handleTechnologicalEquipmentMenu($chatId);
        }
        
        // Handle project activity menu
        if ($category === 'Табель_ПроектнаяДеятельность') {
            error_log("TabelHandler: routing to project activity menu");
            return $this->handleProjectActivityMenu($chatId, $conversation);
        }
        
        // Handle supervision project activity menu
        if ($category === 'Табель_ПроектнаяДеятельность_ШМР') {
            error_log("TabelHandler: routing to supervision project activity menu");
            return $this->handleSupervisionProjectActivityMenu($chatId, $conversation);
        }
        
        // ======= NEW SUPERVISION WORKS HIERARCHICAL ROUTING =======
        
        // Handle main supervision works menu
        if ($category === 'Табель_ПодготовительныеРаботы') {
            error_log("🔥 CRITICAL DEBUG: Preparatory works category detected! Routing to submenu");
            error_log("🔥 Category: '{$category}'");
            $keyboard = $this->messageService->createPreparatoryWorksKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🔧 Подготовительные работы для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_МонтажБлоков') {
            error_log("TabelHandler: routing to block installation submenu");
            $keyboard = $this->messageService->createBlockInstallationKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🏗️ Монтаж блоков для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_СборкаЗдания') {
            error_log("TabelHandler: routing to building assembly submenu");
            $keyboard = $this->messageService->createBuildingAssemblyKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🏠 Сборка здания для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_ВнутриблочныйМонтаж') {
            error_log("TabelHandler: routing to intra-block installation submenu");
            $keyboard = $this->messageService->createIntraBlockInstallationKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>⚙️ Внутриблочный монтаж для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        // Handle direct supervision works activities (that have no sub-menus)
        if ($category === 'Табель_ПодписаниеАктаШМР') {
            error_log("TabelHandler: handling ШМР act signing confirmation");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПодписаниеАктаШМР', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ШМР_ПодпАкт_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        if ($category === 'Табель_ПроверкаМонтажнойПлощадки') {
            error_log("TabelHandler: handling mounting area inspection confirmation");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПроверкаМонтажнойПлощадки', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ШМР_АналФунд_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Handle sub-activity menus (level 3)
        if ($category === 'Табель_ВходнойКонтроль') {
            error_log("TabelHandler: routing to input control submenu");
            $keyboard = $this->messageService->createInputControlKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🔍 Входной контроль для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_ЗавозОборудования') {
            error_log("TabelHandler: routing to equipment delivery submenu");
            $keyboard = $this->messageService->createEquipmentDeliveryKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🚚 Завоз оборудования на площадку для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_РасстановкаБлоков') {
            error_log("TabelHandler: routing to block arrangement submenu");
            $keyboard = $this->messageService->createBlockArrangementKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🏗️ Расстановка блоков для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_МонтажПлощадокОбслуживания') {
            error_log("TabelHandler: routing to service platform installation submenu");
            $keyboard = $this->messageService->createServicePlatformInstallationKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🛠️ Монтаж площадок обслуживания для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_МонтажЭлементовУкрытия') {
            error_log("TabelHandler: routing to shelter elements installation submenu");
            $keyboard = $this->messageService->createShelterElementsInstallationKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🏠 Монтаж элементов укрытия для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_СборкаКровли') {
            error_log("TabelHandler: routing to roof assembly submenu");
            $keyboard = $this->messageService->createRoofAssemblyKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🏠 Сборка кровли для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_МонтажТрубопроводов') {
            error_log("TabelHandler: routing to pipeline installation submenu");
            $keyboard = $this->messageService->createPipelineInstallationKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🔧 Монтаж трубопроводов для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_ГидропневмоИспытания') {
            error_log("TabelHandler: routing to hydro/pneumatic testing submenu");
            $keyboard = $this->messageService->createHydroPneumaticTestingKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>💧 Гидро/пневмо испытания для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_МонтажТеплоизоляции') {
            error_log("TabelHandler: routing to thermal insulation installation submenu");
            $keyboard = $this->messageService->createThermalInsulationInstallationKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🌡️ Монтаж теплоизоляции для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        if ($category === 'Табель_МонтажВентиляции') {
            error_log("TabelHandler: routing to ventilation installation submenu");
            $keyboard = $this->messageService->createVentilationInstallationKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🌬️ Монтаж вентиляции для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        // Handle terminal supervision works activities (level 4 - final activities that generate reports)
        if (str_contains($category, 'ШМР_') && str_contains($category, '_РабПоГр')) {
            error_log("TabelHandler: handling supervision works 'work by schedule' confirmation");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            // Extract activity name for confirmation - handle both Табель_ШМР_ and direct ШМР_ formats
            if (str_contains($category, 'Табель_ШМР_')) {
                // Format: Табель_ШМР_ВходнойКонтроль_РабПоГр -> ВходнойКонтроль
                $activityName = str_replace(['Табель_ШМР_', '_РабПоГр'], '', $category);
                $confirmationText = $this->tabelEntry->getConfirmationMessage("ШМР_{$activityName}_РабПоГр", $projectCode);
            } else {
                // Format: ШМР_ВходнойКонтроль_РабПоГр -> use as is
                $confirmationText = $this->tabelEntry->getConfirmationMessage($category, $projectCode);
            }
            // Use short format for supervision works schedule confirmation to avoid BUTTON_DATA_INVALID error
            $shortFormatMap = [
                'ШМР_ВхКонтр_РабПоГр' => 'cat_ШМР_ВхКонтр_РабПоГр_yes',
                'ШМР_ЗавОбор_РабПоГр' => 'cat_ШМР_ЗавОбор_РабПоГр_yes',
                'ШМР_ПроверкаМонтажнойПлощадки_РабПоГр' => 'cat_ШМР_АналФунд_РабПоГр_yes',
                'ШМР_РасстБлок_РабПоГр' => 'cat_ШМР_РасстБлок_РабПоГр_yes',
                'ШМР_МонтПлощ_РабПоГр' => 'cat_ШМР_МонтПлощ_РабПоГр_yes',
                'ШМР_МонтЭлУкр_РабПоГр' => 'cat_ШМР_МонтЭлУкр_РабПоГр_yes',
                'ШМР_СборКровл_РабПоГр' => 'cat_ШМР_СборКровл_РабПоГр_yes',
                'ШМР_МонтТруб_РабПоГр' => 'cat_ШМР_МонтТруб_РабПоГр_yes',
                'ШМР_ГидрИсп_РабПоГр' => 'cat_ШМР_ГидрИсп_РабПоГр_yes',
                'ШМР_МонтТеплИз_РабПоГр' => 'cat_ШМР_МонтТеплИз_РабПоГр_yes',
                'ШМР_МонтВент_РабПоГр' => 'cat_ШМР_МонтВент_РабПоГр_yes',
                'ШМР_МонтСнятОб_РабПоГр' => 'cat_ШМР_МонтСнятОб_РабПоГр_yes',
                'ШМР_МонтКаб_РабПоГр' => 'cat_ШМР_МонтКаб_РабПоГр_yes'
            ];
            
            $callbackData = $shortFormatMap[$category] ?? "{$category}_yes";
            $keyboard = $this->messageService->createConfirmationKeyboard($callbackData);
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        if (str_contains($category, 'ШМР_') && str_contains($category, '_ПодтВех')) {
            error_log("TabelHandler: handling supervision works 'milestone confirmation' confirmation");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            // Extract activity name for confirmation - handle both Табель_ШМР_ and direct ШМР_ formats
            if (str_contains($category, 'Табель_ШМР_')) {
                // Format: Табель_ШМР_ВходнойКонтроль_ПодтВех -> ВходнойКонтроль
                $activityName = str_replace(['Табель_ШМР_', '_ПодтВех'], '', $category);
                $confirmationText = $this->tabelEntry->getConfirmationMessage("ШМР_{$activityName}_ПодтВех", $projectCode);
            } else {
                // Format: ШМР_ВходнойКонтроль_ПодтВех -> use as is
                $confirmationText = $this->tabelEntry->getConfirmationMessage($category, $projectCode);
            }
            // Use short format for supervision works milestone confirmation to avoid BUTTON_DATA_INVALID error
            $shortFormatMap = [
                'ШМР_ВхКонтр_ПодтВех' => 'cat_ШМР_ВхКонтр_ПодтВех_yes',
                'ШМР_ЗавОбор_ПодтВех' => 'cat_ШМР_ЗавОбор_ПодтВех_yes',
                'ШМР_АналФунд_ПодтВех' => 'cat_ШМР_АналФунд_ПодтВех_yes',
                'ШМР_РасстБлок_ПодтВех' => 'cat_ШМР_РасстБлок_ПодтВех_yes',
                'ШМР_МонтПлощ_ПодтВех' => 'cat_ШМР_МонтПлощ_ПодтВех_yes',
                'ШМР_МонтЭлУкр_ПодтВех' => 'cat_ШМР_МонтЭлУкр_ПодтВех_yes',
                'ШМР_СборКровл_ПодтВех' => 'cat_ШМР_СборКровл_ПодтВех_yes',
                'ШМР_МонтТруб_ПодтВех' => 'cat_ШМР_МонтТруб_ПодтВех_yes',
                'ШМР_ГидрИсп_ПодтВех' => 'cat_ШМР_ГидрИсп_ПодтВех_yes',
                'ШМР_МонтТеплИз_ПодтВех' => 'cat_ШМР_МонтТеплИз_ПодтВех_yes',
                'ШМР_МонтТеплИз_ПодтВех' => 'cat_ШМР_МонтТеплИз_ПодтВех_yes',
                'ШМР_МонтВент_ПодтВех' => 'cat_ШМР_МонтВент_ПодтВех_yes',
                'ШМР_МонтСнятОб_ПодтВех' => 'cat_ШМР_МонтСнятОб_ПодтВех_yes',
                'ШМР_МонтКаб_ПодтВех' => 'cat_ШМР_МонтКаб_ПодтВех_yes',
                // FULL NAMES - CRITICAL FIX FOR MILESTONE CONFIRMATION
                'ШМР_ВходнойКонтроль_ПодтВех' => 'cat_ШМР_ВхКонтр_ПодтВех_yes',
                'ШМР_ЗавозОборудования_ПодтВех' => 'cat_ШМР_ЗавОбор_ПодтВех_yes',
                'ШМР_АнализИнформацииПоФундаменту_ПодтВех' => 'cat_ШМР_АналФунд_ПодтВех_yes',
                'ШМР_РасстановкаБлоков_ПодтВех' => 'cat_ШМР_РасстБлок_ПодтВех_yes',
                'ШМР_МонтажПлощадок_ПодтВех' => 'cat_ШМР_МонтПлощ_ПодтВех_yes',
                'ШМР_МонтажЭлементовУкрытия_ПодтВех' => 'cat_ШМР_МонтЭлУкр_ПодтВех_yes',
                'ШМР_СборкаКровли_ПодтВех' => 'cat_ШМР_СборКровл_ПодтВех_yes',
                'ШМР_МонтажТрубопроводов_ПодтВех' => 'cat_ШМР_МонтТруб_ПодтВех_yes',
                'ШМР_ГидропневмоИспытания_ПодтВех' => 'cat_ШМР_ГидрИсп_ПодтВех_yes',
                'ШМР_МонтажТеплоизоляции_ПодтВех' => 'cat_ШМР_МонтТеплИз_ПодтВех_yes',
                'ШМР_МонтажВентиляции_ПодтВех' => 'cat_ШМР_МонтВент_ПодтВех_yes',
                'ШМР_МонтажСнятогоОборудования_ПодтВех' => 'cat_ШМР_МонтСнятОб_ПодтВех_yes',
                'ШМР_МонтажКабельнойПродукции_ПодтВех' => 'cat_ШМР_МонтКаб_ПодтВех_yes',
                // General milestone confirmation for ШМР - FIXED: Use simple pattern without cat_ prefix
                'ПодтверждениеВехи_ШМР' => 'ШМР_ПодтВех_yes'
            ];
            
            $callbackData = $shortFormatMap[$category] ?? "{$category}_yes";
            $keyboard = $this->messageService->createConfirmationKeyboard($callbackData);
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // ======= END SUPERVISION WORKS HIERARCHICAL ROUTING =======
        
        // Handle supervision project activity menu
        
        // Handle direct electrical equipment callback (without cat_ prefix)
        if ($category === 'Электрооборудование') {
            error_log("TabelHandler: routing to electrical equipment sub-menu (direct format)");
            return $this->handleElectricalEquipmentMenu($chatId);
        }
        
        // Handle simple commissioning activities that don't have sub-menus
        $shortCallbackMappings = [
            'ТабКомплОпр' => 'КомплексноеОпробование',
            'ТабРежНал' => 'РежимнаяНаладка',
            'ТабПодгПоезд' => 'ПодготовкаКПоездке',
            'ТабДорога' => 'Дорога',
            'ТабИнстр' => 'Инструктаж',
            'ТабСдачаОтч' => 'СдачаАвансовогоОтчета'
        ];
        
        if (isset($shortCallbackMappings[$category])) {
            error_log("TabelHandler: handling commissioning activity with short callback: {$category}");
            $activityType = $shortCallbackMappings[$category];
            
            // For commissioning activities, ensure we have proper conversation context
            if (!isset($conversation->notes['project_code'])) {
                // Set default project code for commissioning activities
                $conversation->notes['project_code'] = 'Пусконаладочные работы';
                $conversation->update();
                error_log("TabelHandler: Set default project code for commissioning activity: {$category}");
            }
            
            if (!isset($conversation->notes['tabel_data'])) {
                // Set current date if not set
                $conversation->notes['tabel_data'] = date('d.m.Y');
                $conversation->update();
                error_log("TabelHandler: Set current date for commissioning activity: {$category}");
            }
            
            $projectCode = $conversation->notes['project_code'];
            $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
            
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_{$category}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Handle Current Activity (Текущая деятельность)
        if ($category === 'Табель_Текущая') {
            error_log("TabelHandler: handling current activity");
            return $this->handleCurrentActivityDateSelection($chatId);
        }
        
        // Handle warranty equipment activities (terminal activities - direct to confirmation)
        
        // Warranty work for electrical equipment
        if ($category === 'Табель_РабГарантии_ЭлектрОб') {
            error_log("TabelHandler: handling warranty work for electrical equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабГарантии_ЭлектрОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_Гарантии_ЭлектрОб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Warranty work for measuring instruments
        if ($category === 'Табель_РабГарантии_СрИзм') {
            error_log("TabelHandler: handling warranty work for measuring instruments");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабГарантии_СрИзм', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_Гарантии_СрИзм_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Warranty work for pump equipment
        if ($category === 'Табель_РабГарантии_НасОб') {
            error_log("TabelHandler: handling warranty work for pump equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабГарантии_НасОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_Гарантии_НасОб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Warranty work for pipeline fittings
        if ($category === 'Табель_РабГарантии_ТрубАрм') {
            error_log("TabelHandler: handling warranty work for pipeline fittings");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабГарантии_ТрубАрм', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_Гарантии_ТрубАрм_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Warranty work for actuators
        if ($category === 'Табель_РабГарантии_ИспМех') {
            error_log("TabelHandler: handling warranty work for actuators");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабГарантии_ИспМех', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_Гарантии_ИспМех_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Warranty work for controller equipment
        if ($category === 'Табель_РабГарантии_КонтрОб') {
            error_log("TabelHandler: handling warranty work for controller equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабГарантии_КонтрОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_Гарантии_КонтрОб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Warranty work for other equipment
        if ($category === 'Табель_РабГарантии_Прочее') {
            error_log("TabelHandler: handling warranty work for other equipment - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'РабГарантии_Прочее';
            $conversation->update();
            
            // Prompt user for custom input
            $text = "<b>📦 Прочее оборудование - Работы по гарантии</b>\n\n" .
                    "Пожалуйста, введите наименование оборудования:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // URGENT: Test handler for supervision prep works (alternative callback)
        if ($category === 'Табель_ПодготовительныеРаботы') {
            error_log("🎆 URGENT: Alternative supervision prep callback received: '{$category}'");
            $keyboard = $this->messageService->createPreparatoryWorksKeyboard();
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            return $this->messageService->sendMessage($chatId, "<b>🔧 Подготовительные работы для проекта {$projectCode}</b>\n\nВыберите тип работ:", $keyboard);
        }
        
        // Handle Supervision Work Types (exclude warranty and RK - they have specific handlers)
        if ($category === 'Табель_ШМР') {
            error_log("TabelHandler: handling supervision works menu: {$category}");
            return $this->handleSupervisionWorksMenu($chatId, $conversation);
        }
        
        if ($category === 'Табель_ШМ_Простой') {
            error_log("TabelHandler: handling supervision work type: {$category}");
            // Extract the activity type
            $strippedCategory = str_replace('Табель_', '', $category);
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            
            $keyboard = $this->messageService->createConfirmationKeyboard("{$category}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Handle Supervision Works sub-activities
        if (in_array($category, [
            'Табель_ШМР_РаботыПоГрафику',
            'Табель_ШМР_ПодготовкаПоездки',
            'Табель_ШМР_Дорога',
            'Табель_ШМР_Инструктаж',
            'Табель_ШМР_СдачаОтчета'
        ])) {
            error_log("TabelHandler: handling supervision works sub-activity: {$category}");
            // Extract the activity type
            $strippedCategory = str_replace('Табель_', '', $category);
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($strippedCategory, $projectCode);
            
            // Use the correct short format for supervision works confirmation
            $shortFormatMap = [
                'ШМР_РаботыПоГрафику' => 'cat_ТабШМР_РаботыПоГрафику_yes',
                'ШМР_ПодготовкаПоездки' => 'cat_ТабШМР_ПодготовкаПоездки_yes',
                'ШМР_Дорога' => 'cat_ТабШМР_Дорога_yes',
                'ШМР_Инструктаж' => 'cat_ТабШМР_Инструктаж_yes',
                'ШМР_СдачаОтчета' => 'cat_ТабШМР_СдачаОтчета_yes'
            ];
            
            $callbackData = $shortFormatMap[$strippedCategory] ?? "{$category}_yes";
            $keyboard = $this->messageService->createConfirmationKeyboard($callbackData);
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Handle work type buttons that appear after date and project selection
        // These are the activity types that should be confirmed
        $strippedCategory = str_replace('Табель_', '', $category);
        error_log("TabelHandler: stripped category: '{$strippedCategory}'");
        error_log("TabelHandler: activityTypes array: " . json_encode($this->activityTypes, JSON_UNESCAPED_UNICODE));
        error_log("TabelHandler: stripped category in activityTypes: " . (in_array($strippedCategory, $this->activityTypes) ? 'true' : 'false'));
        
        // Special handling for commissioning works with short callback format
        // This should only trigger when we already have a project_code (i.e., after project selection)
        if ($category === 'ТабПНРаботы' && isset($conversation->notes['project_code'])) {
            error_log("TabelHandler: handling activity type confirmation for commissioning works (short format)");
            $activityType = 'ПусконаладочныеРаботы';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
            
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПНРаботы_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Handle additional works activities for electrical equipment
        // Scheme changes for electrical equipment
        if ($category === 'ТабЭлОб_ИзмСхПд' || $category === 'cat_ТабЭлОб_ИзмСхПд') {
            error_log("TabelHandler: handling scheme changes for electrical equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ИзмСхемПодкл_ЭлектрОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_ИзмСхПд_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Cable installation for electrical equipment
        if ($category === 'ТабЭлОб_МонтКбЛн' || $category === 'cat_ТабЭлОб_МонтКбЛн') {
            error_log("TabelHandler: handling cable installation for electrical equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('МонтажКабЛиний_ЭлектрОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_МонтКбЛн_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Cable wiring for electrical equipment
        if ($category === 'ТабЭлОб_РаскКбЛн' || $category === 'cat_ТабЭлОб_РаскКбЛн') {
            error_log("TabelHandler: handling cable wiring for electrical equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РасклКабЛиний_ЭлектрОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_РаскКбЛн_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Equipment installation for electrical equipment
        if ($category === 'ТабЭлОб_МонтОб' || $category === 'cat_ТабЭлОб_МонтОб') {
            error_log("TabelHandler: handling equipment installation for electrical equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('МонтОб_ЭлектрОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_МонтОб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works (Прочее) for electrical equipment - prompt for custom input
        if ($category === 'ТабЭлОб_Проч' || $category === 'cat_ТабЭлОб_Проч' || 
            $category === 'Табель_Прочее_Электрооборудование' || $category === 'Табель_Прочее_ЭлектрОб') {
            error_log("TabelHandler: handling other works (Прочее) for electrical equipment - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ЭлектрОб';
            $conversation->update();
            
            // Prompt user for custom input
            $text = "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Handle fire alarm equipment activities
        // Works by schedule for fire alarm equipment
        if ($category === 'ТабПожСигн_РабПоГр' || $category === 'cat_ТабПожСигн_РабПоГр') {
            error_log("TabelHandler: handling works by schedule for fire alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабПоГр_ПожСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle automation equipment activities
        // Works by schedule for automation equipment
        if ($category === 'ТабАвтом_РабПоГр' || $category === 'cat_ТабАвтом_РабПоГр') {
            error_log("TabelHandler: handling works by schedule for automation equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабПоГр_Автом', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle metrology equipment activities - MOVED TO HIGH PRIORITY
        // Works by schedule for metrology equipment
        error_log("TabelHandler: DEBUGGING METROLOGY - checking category: '{$category}'");
        error_log("TabelHandler: DEBUGGING METROLOGY - checking for: 'ТабМетрол_РабПоГр', 'cat_ТабМетрол_РабПоГр', 'Табель_РабПоГр_Метрол'");
        if ($category === 'ТабМетрол_РабПоГр' || $category === 'cat_ТабМетрол_РабПоГр' || $category === 'Табель_РабПоГр_Метрол') {
            error_log("TabelHandler: ✅ METROLOGY MATCH FOUND! handling works by schedule for metrology equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабПоГр_Метрол', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабМетрол_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        error_log("TabelHandler: ❌ METROLOGY NO MATCH - category '{$category}' did not match metrology schedule handlers");

        // ID formation for metrology equipment
        if ($category === 'ТабМетрол_ФормИД' || $category === 'cat_ТабМетрол_ФормИД' || $category === 'Табель_ФормИД_Метрол') {
            error_log("TabelHandler: handling ID formation for metrology equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ФормИД_Метрол', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабМетрол_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Milestone confirmation for metrology equipment
        if ($category === 'ТабМетрол_ПодтВех' || $category === 'cat_ТабМетрол_ПодтВех' || $category === 'Табель_ПодтВех_Метрол') {
            error_log("TabelHandler: handling milestone confirmation for metrology equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПодтВех_Метрол', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабМетрол_ПодтВех_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Additional works for metrology equipment
        if ($category === 'ТабМетрол_ДопРаб' || $category === 'cat_ТабМетрол_ДопРаб' || $category === 'Табель_ДопРаб_Метрол') {
            error_log("TabelHandler: handling additional works for metrology equipment");
            return $this->handleAdditionalWorksMetrologyMenu($chatId);
        }

        // Handle metrology equipment additional works activities
        // Configuration changes for metrology equipment
        if ($category === 'cat_ТабМетрол_ИзмКонф' || $category === 'ТабМетрол_ИзмКонф' || $category === 'Табель_ИзмКонф_Метрол') {
            error_log("TabelHandler: handling configuration changes for metrology equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ИзмКонф_Метрол', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабМетрол_ИзмКонф_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Calibration/verification for metrology equipment
        if ($category === 'cat_ТабМетрол_КалПов' || $category === 'ТабМетрол_КалПов' || $category === 'Табель_КалПов_Метрол') {
            error_log("TabelHandler: handling calibration/verification for metrology equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('КалПов_Метрол', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабМетрол_КалПов_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // KMH SI for metrology equipment
        if ($category === 'cat_ТабМетрол_КМХ' || $category === 'ТабМетрол_КМХ' || $category === 'Табель_КМХ_Метрол') {
            error_log("TabelHandler: handling KMH SI for metrology equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('КМХ_Метрол', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабМетрол_КМХ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Characteristics correction for metrology equipment
        if ($category === 'cat_ТабМетрол_КоррХар' || $category === 'ТабМетрол_КоррХар' || $category === 'Табель_КоррХар_Метрол') {
            error_log("TabelHandler: handling characteristics correction for metrology equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('КоррХар_Метрол', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабМетрол_КоррХар_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works (Прочее) for metrology equipment
        if ($category === 'cat_ТабМетрол_Проч' || $category === 'ТабМетрол_Проч' ||
            $category === 'Табель_Прочее_Метрология' || $category === 'Табель_Проч_Метрол') {
            error_log("TabelHandler: handling other works (Прочее) for metrology equipment - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Проч_Метрол';
            $conversation->update();
            
            // Prompt user for custom input with formatted message
            $text = "<b>📝 Прочее - Дополнительные работы по метрологии</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // ID formation for automation equipment
        if ($category === 'ТабАвтом_ФормИД' || $category === 'cat_ТабАвтом_ФормИД') {
            error_log("TabelHandler: handling ID formation for automation equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ФормИД_Автом', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Additional works for automation equipment
        if ($category === 'ТабАвтом_ДопРаб' || $category === 'cat_ТабАвтом_ДопРаб' || $category === 'Табель_ДопРаб_Автом') {
            error_log("TabelHandler: CRITICAL DEBUG - handling additional works for automation equipment");
            error_log("TabelHandler: CRITICAL DEBUG - category matched: {$category}");
            error_log("TabelHandler: CRITICAL DEBUG - calling handleAdditionalWorksAutomationMenu");
            return $this->handleAdditionalWorksAutomationMenu($chatId);
        }
        
        // Milestone confirmation for automation equipment
        if ($category === 'ТабАвтом_ПодтВех' || $category === 'cat_ТабАвтом_ПодтВех') {
            error_log("TabelHandler: handling milestone confirmation for automation equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПодтВех_Автом', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_ПодтВех_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // ID formation for fire alarm equipment
        if ($category === 'ТабПожСигн_ФормИД' || $category === 'cat_ТабПожСигн_ФормИД') {
            error_log("TabelHandler: handling ID formation for fire alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ФормИД_ПожСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Additional works for fire alarm equipment
        if ($category === 'ТабПожСигн_ДопРаб' || $category === 'cat_ТабПожСигн_ДопРаб' || $category === 'Табель_ДопРаб_ПожСигн') {
            error_log("TabelHandler: handling additional works for fire alarm equipment");
            return $this->handleAdditionalWorksFireAlarmMenu($chatId);
        }
        
        // Milestone confirmation for fire alarm equipment
        if ($category === 'ТабПожСигн_ПодтВех' || $category === 'cat_ТабПожСигн_ПодтВех') {
            error_log("TabelHandler: handling milestone confirmation for fire alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПодтВех_ПожСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_ПодтВех_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works (Прочее) for fire alarm equipment - prompt for custom input
        if ($category === 'ТабПожСигн_Проч' || $category === 'cat_ТабПожСигн_Проч' || 
            $category === 'Табель_Прочее_ПожарнаяСигнализация' || $category === 'Табель_Прочее_ПожСигн') {
            error_log("TabelHandler: handling other works (Прочее) for fire alarm equipment - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ПожСигн';
            $conversation->update();
            
            // Prompt user for custom input
            $text = "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Handle automation equipment additional works activities (short format)
        // Scheme changes for automation equipment
        if ($category === 'cat_ТабАвтом_ИзмСх' || $category === 'ТабАвтом_ИзмСх') {
            error_log("TabelHandler: handling scheme changes for automation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ИзмСх_Автом', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_ИзмСх_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Cable installation for automation equipment
        if ($category === 'cat_ТабАвтом_МонтКб' || $category === 'ТабАвтом_МонтКб') {
            error_log("TabelHandler: handling cable installation for automation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('МонтКб_Автом', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_МонтКб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Cable wiring for automation equipment
        if ($category === 'cat_ТабАвтом_РаскКб' || $category === 'ТабАвтом_РаскКб') {
            error_log("TabelHandler: handling cable wiring for automation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РаскКб_Автом', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_РаскКб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Equipment installation for automation equipment
        if ($category === 'cat_ТабАвтом_МонтАСУ' || $category === 'ТабАвтом_МонтАСУ') {
            error_log("TabelHandler: handling equipment installation for automation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('МонтАСУ_Автом', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_МонтАСУ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Remote software commissioning for automation equipment
        if ($category === 'cat_ТабАвтом_УдалПО' || $category === 'ТабАвтом_УдалПО') {
            error_log("TabelHandler: handling remote software commissioning for automation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УдалПО_Автом', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_УдалПО_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works (Прочее) for automation equipment - both formats
        if ($category === 'Табель_Прочее_Автоматизация' || $category === 'cat_ТабАвтом_Проч') {
            error_log("TabelHandler: handling other works (Прочее) for automation equipment - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_Автом';
            $conversation->update();
            
            // Prompt user for custom input with formatted message
            $text = "<b>📝 Прочее - Дополнительные работы по автоматизации</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Handle project activities (terminal activities - direct to confirmation)
        // Participation in ZPI
        if ($category === 'Табель_УчастиеВЗПИ') {
            error_log("TabelHandler: handling participation in ZPI");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УчастиеВЗПИ', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_УчастиеВЗПИ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Development of work program
        if ($category === 'Табель_РазрПрограммы') {
            error_log("TabelHandler: handling development of work program");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РазрПрограммы', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_РазрПрограммы_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Development of work schedule
        if ($category === 'Табель_РазрГрафика') {
            error_log("TabelHandler: handling development of work schedule");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РазрГрафика', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_РазрГрафика_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Organization of additional delivery
        if ($category === 'Табель_ОргДопоставки') {
            error_log("TabelHandler: handling organization of additional delivery");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ОргДопоставки', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_ОргДопоставки_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Participation in OPI
        if ($category === 'Табель_УчастиеВОПИ') {
            error_log("TabelHandler: handling participation in OPI");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УчастиеВОПИ', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_УчастиеВОПИ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Supervision commissioning works
        if ($category === 'Табель_ШефНаладочныеРаботы') {
            error_log("TabelHandler: handling supervision commissioning works");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ШефНаладочныеРаботы', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_ШефНаладочныеРаботы_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Handle master duties execution - HIGH PRIORITY DEBUG
        error_log("🎯 MASTER DUTIES CHECK: Current category: '{$category}'");
        error_log("🎯 MASTER DUTIES CHECK: Exact match test: " . ($category === 'Табель_ИспОбМаст' ? 'TRUE' : 'FALSE'));
        
        if ($category === 'Табель_ИспОбМаст') {
            error_log("🎯 MASTER DUTIES: ✅ HANDLER TRIGGERED! Category: '{$category}'");
            error_log("TabelHandler: handling master duties execution");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            error_log("🎯 MASTER DUTIES: Project code: '{$projectCode}'");
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ИспОбМаст', $projectCode);
            error_log("🎯 MASTER DUTIES: Confirmation text generated: '{$confirmationText}'");
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_ИспОбМаст_yes");
            error_log("🎯 MASTER DUTIES: Keyboard created, sending message to chat: {$chatId}");
            $response = $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
            error_log("🎯 MASTER DUTIES: Message sent, response: " . ($response ? 'SUCCESS' : 'FAILED'));
            return $response;
        } else {
            error_log("🎯 MASTER DUTIES: ❌ Handler NOT triggered. Category '{$category}' does not match 'Табель_ИспОбМаст'");
        }
        
        // Handle other works (Прочее) for project activity - prompt for custom input
        if ($category === 'ТабПроектнаяДеят_Проч' || $category === 'cat_ТабПроектнаяДеят_Проч') {
            error_log("TabelHandler: handling other works (Прочее) for project activity - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ПроектнаяДеятельность';
            $conversation->update();
            
            // Prompt user for custom input with formatted message
            $text = "<b>📝 Прочее - Проектная деятельность</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Handle other works (Прочее) for supervision project activity - prompt for custom input
        if ($category === 'ТабПроектнаяДеят_Проч_ШМР' || $category === 'cat_ТабПроектнаяДеят_Проч_ШМР') {
            error_log("TabelHandler: handling other works (Прочее) for supervision project activity - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ПроектнаяДеятельность_ШМР';
            $conversation->update();
            
            // Prompt user for custom input with formatted message
            $text = "<b>📝 Прочее - Проектная деятельность</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // DEBUG: Final check - log if we reach the end without handling
        error_log("TabelHandler: END OF AUTOMATION HANDLERS - category '{$category}' not handled by any automation handler");

        // Handle security alarm equipment activities
        // Works by schedule for security alarm equipment
        if ($category === 'ТабОхрСиг_РабПоГр' || $category === 'cat_ТабОхрСиг_РабПоГр') {
            error_log("TabelHandler: handling works by schedule for security alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабПоГр_ОхрСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабОхрСиг_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // ID formation for security alarm equipment
        if ($category === 'ТабОхрСиг_ФормИД' || $category === 'cat_ТабОхрСиг_ФормИД') {
            error_log("TabelHandler: handling ID formation for security alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ФормИД_ОхрСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабОхрСиг_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Additional works for security alarm equipment
        if ($category === 'ТабОхрСиг_ДопРаб' || $category === 'cat_ТабОхрСиг_ДопРаб' || $category === 'Табель_ДопРаб_ОхрСигн') {
            error_log("TabelHandler: handling additional works for security alarm equipment");
            return $this->handleAdditionalWorksSecurityAlarmMenu($chatId);
        }
        
        // Milestone confirmation for security alarm equipment
        if ($category === 'ТабОхрСиг_ПодтВех' || $category === 'cat_ТабОхрСиг_ПодтВех') {
            error_log("TabelHandler: handling milestone confirmation for security alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПодтВех_ОхрСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабОхрСиг_ПодтВех_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works (Прочее) for security alarm equipment - removed from main menu
        // Now only accessible through "Дополнительные работы" submenu
        // if ($category === 'ТабОхрСиг_Проч' || $category === 'cat_ТабОхрСиг_Проч' || 
        //     $category === 'Табель_Прочее_ОхраннаяСигнализация' || $category === 'Табель_Прочее_ОхрСигн') {
        //     // This handler is no longer needed since Прочее button removed from main menu
        // }
        
        // Handle heating and ventilation equipment activities
        // Works by schedule for heating and ventilation equipment
        if ($category === 'ТабВентОтопл_РабПоГр' || $category === 'cat_ТабВентОтопл_РабПоГр') {
            error_log("TabelHandler: handling works by schedule for heating and ventilation equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабПоГр_ВентОтопл', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабВентОтопл_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // ID formation for heating and ventilation equipment
        if ($category === 'ТабВентОтопл_ФормИД' || $category === 'cat_ТабВентОтопл_ФормИД') {
            error_log("TabelHandler: handling ID formation for heating and ventilation equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ФормИД_ВентОтопл', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабВентОтопл_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Additional works for heating and ventilation equipment
        if ($category === 'ТабВентОтопл_ДопРаб' || $category === 'cat_ТабВентОтопл_ДопРаб' || $category === 'Табель_ДопРаб_ВентОтопл') {
            error_log("TabelHandler: handling additional works for heating and ventilation equipment");
            return $this->handleAdditionalWorksHeatingVentilationMenu($chatId);
        }
        
        // Milestone confirmation for heating and ventilation equipment
        if ($category === 'ТабВентОтопл_ПодтВех' || $category === 'cat_ТабВентОтопл_ПодтВех') {
            error_log("TabelHandler: handling milestone confirmation for heating and ventilation equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПодтВех_ВентОтопл', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабВентОтопл_ПодтВех_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Handle heating and ventilation equipment additional works activities (short format)
        // Scheme changes for heating and ventilation equipment
        if ($category === 'cat_ТабВентОтопл_ИзмСх' || $category === 'ТабВентОтопл_ИзмСх') {
            error_log("TabelHandler: handling scheme changes for heating and ventilation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ИзмСх_ВентОтопл', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабВентОтопл_ИзмСх_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle technological equipment activities
        // Works by schedule for technological equipment
        if ($category === 'ТабТехОб_РабПоГр' || $category === 'cat_ТабТехОб_РабПоГр' || $category === 'Табель_РабПоГр_ТехОб') {
            error_log("TabelHandler: handling works by schedule for technological equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РабПоГр_ТехОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабТехОб_РабПоГр_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // ID formation for technological equipment
        if ($category === 'ТабТехОб_ФормИД' || $category === 'cat_ТабТехОб_ФормИД' || $category === 'Табель_ФормИД_ТехОб') {
            error_log("TabelHandler: handling ID formation for technological equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ФормИД_ТехОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабТехОб_ФормИД_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Additional works for technological equipment
        if ($category === 'ТабТехОб_ДопРаб' || $category === 'cat_ТабТехОб_ДопРаб' || $category === 'Табель_ДопРаб_ТехОб') {
            error_log("TabelHandler: handling additional works for technological equipment");
            return $this->handleAdditionalWorksTechnologicalMenu($chatId);
        }

        // Milestone confirmation for technological equipment
        if ($category === 'ТабТехОб_ПодтВех' || $category === 'cat_ТабТехОб_ПодтВех' || $category === 'Табель_ПодтВех_ТехОб') {
            error_log("TabelHandler: handling milestone confirmation for technological equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ПодтВех_ТехОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабТехОб_ПодтВех_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle technological equipment additional works activities
        // Dismantling/revision of fittings for technological equipment
        if ($category === 'ТабТехОб_ДемАрм' || $category === 'cat_ТабТехОб_ДемАрм') {
            error_log("TabelHandler: handling dismantling/revision of fittings for technological equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ДемАрм_ТехОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабТехОб_ДемАрм_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Dismantling/revision of technological equipment
        if ($category === 'ТабТехОб_ДемОб' || $category === 'cat_ТабТехОб_ДемОб') {
            error_log("TabelHandler: handling dismantling/revision of technological equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ДемОб_ТехОб', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабТехОб_ДемОб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works (Прочее) for technological equipment - prompt for custom input
        if ($category === 'ТабТехОб_Проч' || $category === 'cat_ТабТехОб_Проч' || 
            $category === 'Табель_Прочее_ТехнологическоеОборудование' || $category === 'Табель_Прочее_ТехОб') {
            error_log("TabelHandler: handling other works (Прочее) for technological equipment - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ТехОб';
            $conversation->update();
            
            // Prompt user for custom input with formatted message
            $text = "<b>📝 Прочее - Дополнительные работы по технологическому оборудованию</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Handle RK comments elimination section activities
        // ES Section (Electrical Systems)
        if ($category === 'cat_ТабУстрЗамЭС' || $category === 'ТабУстрЗамЭС') {
            error_log("TabelHandler: handling RK comments elimination - ES section");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УстрЗам_ЭС', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабУстрЗамЭС_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // ATX Section (Automation)
        if ($category === 'cat_ТабУстрЗамАТХ' || $category === 'ТабУстрЗамАТХ') {
            error_log("TabelHandler: handling RK comments elimination - ATX section");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УстрЗам_АТХ', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабУстрЗамАТХ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // OVKV Section (Heating, Ventilation, Air Conditioning)
        if ($category === 'cat_ТабУстрЗамОВКВ' || $category === 'ТабУстрЗамОВКВ') {
            error_log("TabelHandler: handling RK comments elimination - OVKV section");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УстрЗам_ОВКВ', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабУстрЗамОВКВ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // PS/OS Section (Fire/Security Systems)
        if ($category === 'cat_ТабУстрЗамПСОС' || $category === 'ТабУстрЗамПСОС') {
            error_log("TabelHandler: handling RK comments elimination - PS/OS section");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УстрЗам_ПСОС', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабУстрЗамПСОС_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // TX Section (Technological Equipment)
        if ($category === 'cat_ТабУстрЗамТХ' || $category === 'ТабУстрЗамТХ') {
            error_log("TabelHandler: handling RK comments elimination - TX section");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УстрЗам_ТХ', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабУстрЗамТХ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Other Equipment (Прочее оборудование) - prompt for custom input
        if ($category === 'cat_ТабУстрЗамПрочее' || $category === 'ТабУстрЗамПрочее' || $category === 'Табель_УстрЗам_Прочее') {
            error_log("TabelHandler: handling RK comments elimination - Other Equipment - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'УстрЗам_Прочее';
            $conversation->update();
            
            // Prompt user for custom input with formatted message
            $text = "<b>📦 Прочее оборудование - Устранения замечаний РК</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Cable installation for heating and ventilation equipment
        if ($category === 'cat_ТабВентОтопл_МонтКб' || $category === 'ТабВентОтопл_МонтКб') {
            error_log("TabelHandler: handling cable installation for heating and ventilation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('МонтКб_ВентОтопл', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабВентОтопл_МонтКб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Cable wiring for heating and ventilation equipment
        if ($category === 'cat_ТабВентОтопл_РаскКб' || $category === 'ТабВентОтопл_РаскКб') {
            error_log("TabelHandler: handling cable wiring for heating and ventilation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РаскКб_ВентОтопл', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабВентОтопл_РаскКб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Equipment installation for heating and ventilation equipment
        if ($category === 'cat_ТабВентОтопл_МонтАСУ' || $category === 'ТабВентОтопл_МонтАСУ') {
            error_log("TabelHandler: handling equipment installation for heating and ventilation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('МонтАСУ_ВентОтопл', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабВентОтопл_МонтАСУ_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Remote software commissioning for heating and ventilation equipment
        if ($category === 'cat_ТабВентОтопл_УдалПО' || $category === 'ТабВентОтопл_УдалПО') {
            error_log("TabelHandler: handling remote software commissioning for heating and ventilation equipment (short format)");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('УдалПО_ВентОтопл', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабВентОтопл_УдалПО_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Handle other works (Прочее) for heating and ventilation equipment - both formats
        if ($category === 'Табель_Прочее_ВентОтопл' || $category === 'cat_ТабВентОтопл_Проч') {
            error_log("TabelHandler: handling other works (Прочее) for heating and ventilation equipment - prompting for custom input");
            // Set state to wait for custom input
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ВентОтопл';
            $conversation->update();
            
            // Prompt user for custom input with formatted message
            $text = "<b>📝 Прочее - Дополнительные работы по отоплению и вентиляции</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Handle other works (Прочее) for security alarm equipment - prompt for custom input
        // Now accessible through "Дополнительные работы" submenu
        if ($category === 'ТабОхрСиг_Проч' || $category === 'cat_ТабОхрСиг_Проч' || 
            $category === 'Табель_Прочее_ОхраннаяСигнализация' || $category === 'Табель_Прочее_ОхрСигн') {
            error_log("TabelHandler: НОВЫЙ обработчик кнопки Прочее для охранной сигнализации - запрос пользовательского ввода для: {$category}");
            
            // ВАЖНО: Очищаем все старые данные о Прочее перед установкой новых
            unset($conversation->notes['custom_input_text']);
            unset($conversation->notes['awaiting_custom_input']);
            unset($conversation->notes['custom_input_activity_type']);
            
            // Устанавливаем состояние ожидания пользовательского ввода (используем старую систему для совместимости)
            $conversation->notes['awaiting_custom_input'] = true;
            $conversation->notes['custom_input_activity_type'] = 'Прочее_ОхрСигн';
            $conversation->update();
            
            error_log("TabelHandler: Set awaiting custom input state for Security Alarm Equipment");
            
            // Prompt user for custom input
            $text = "<b>📝 Прочее - Дополнительные работы по охранной сигнализации</b>\n\n" .
                    "Пожалуйста, введите описание выполняемой работы:";
            return $this->messageService->sendMessage($chatId, $text);
        }

        // Handle security alarm equipment additional works activities
        // Scheme changes for security alarm equipment
        if ($category === 'ТабОхрСиг_ИзмСх' || $category === 'cat_ТабОхрСиг_ИзмСх') {
            error_log("TabelHandler: handling scheme changes for security alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('ИзмСх_ОхрСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабОхрСиг_ИзмСх_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Cable installation for security alarm equipment
        if ($category === 'ТабОхрСиг_МонтКб' || $category === 'cat_ТабОхрСиг_МонтКб') {
            error_log("TabelHandler: handling cable installation for security alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('МонтКб_ОхрСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабОхрСиг_МонтКб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Cable wiring for security alarm equipment
        if ($category === 'ТабОхрСиг_РаскКб' || $category === 'cat_ТабОхрСиг_РаскКб') {
            error_log("TabelHandler: handling cable wiring for security alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('РаскКб_ОхрСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабОхрСиг_РаскКб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // Additional works for security alarm equipment
        if ($category === 'ТабОхрСиг_ДопРаб' || $category === 'cat_ТабОхрСиг_ДопРаб' || $category === 'Табель_ДопРаб_ОхрСигн') {
            error_log("TabelHandler: handling additional works for security alarm equipment");
            return $this->handleAdditionalWorksSecurityAlarmMenu($chatId);
        }
        
        // Equipment installation for security alarm equipment
        if ($category === 'ТабОхрСиг_МонтОб' || $category === 'cat_ТабОхрСиг_МонтОб') {
            error_log("TabelHandler: handling equipment installation for security alarm equipment");
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage('МонтОб_ОхрСигн', $projectCode);
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабОхрСиг_МонтОб_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }

        // CRITICAL: Check for activity type AFTER specific handlers to prevent premature confirmation
        if (in_array($strippedCategory, $this->activityTypes)) {
            error_log("TabelHandler: handling activity type confirmation for: '{$strippedCategory}'");
            // This is a work type selection, treat it as activity confirmation
            // We need to create a proper callback_data format for confirmation
            $activityType = $strippedCategory;
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
            
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        error_log("TabelHandler: unknown command");
        error_log("TabelHandler: URGENT DEBUG - Category that caused unknown command: '{$category}'");
        error_log("TabelHandler: URGENT DEBUG - Category bytes: " . bin2hex($category));
        error_log("TabelHandler: URGENT DEBUG - Category length: " . strlen($category));
        
        // URGENT: Check if this looks like a warranty equipment category
        if (strpos($category, 'РабГарантии') !== false) {
            error_log("*** WARRANTY EQUIPMENT CATEGORY DETECTED BUT NOT HANDLED! ***");
            error_log("*** This should have been caught by warranty equipment handlers ***");
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
        error_log("TabelHandler: handleMobileBrigadeDateSelection called");
        $keyboard = $this->messageService->createDateSelectionKeyboard('category_Табель_МобБриг');
        error_log("TabelHandler: created date selection keyboard with " . count($keyboard) . " rows");
        $result = $this->messageService->sendMessage($chatId, '💼 <b>Выберите дату для Мобильной бригады</b>', $keyboard);
        error_log("TabelHandler: sendMessage returned");
        return $result;
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
     * Handle commissioning works menu
     */
    private function handleCommissioningWorksMenu(string $chatId, Conversation $conversation): ServerResponse
    {
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $keyboard = $this->messageService->createCommissioningWorksKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>⚙️ Пусконаладочные работы для проекта {$projectCode}</b>\n\nВыберите тип работ:",
            $keyboard
        );
    }







    /**
     * Handle project activity menu
     */
    private function handleProjectActivityMenu(string $chatId, Conversation $conversation): ServerResponse
    {
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $keyboard = $this->messageService->createProjectActivityKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>📂 Проектная деятельность для проекта {$projectCode}</b>\n\nВыберите тип проектной деятельности:",
            $keyboard
        );
    }
    
    /**
     * Handle supervision project activity menu
     */
    private function handleSupervisionProjectActivityMenu(string $chatId, Conversation $conversation): ServerResponse
    {
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $keyboard = $this->messageService->createSupervisionProjectActivityKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>📂 Проектная деятельность для проекта {$projectCode}</b>\n\nВыберите тип проектной деятельности:",
            $keyboard
        );
    }
    
    /**
     * Handle supervision works menu
     */
    private function handleSupervisionWorksMenu(string $chatId, Conversation $conversation): ServerResponse
    {
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $keyboard = $this->messageService->createSupervisionWorksKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>🔧 Шеф-монтажные работы для проекта {$projectCode}</b>\n\nВыберите тип работ:",
            $keyboard
        );
    }
    
    /**
     * Handle commissioning works date selection (short format)
     */
    private function handleCommissioningWorksDateSelection(string $chatId): ServerResponse
    {
        // Используем специальный префикс для короткого формата Пусконаладочных работ
        $keyboard = $this->messageService->createDateSelectionKeyboard('cat_ТабПНРаботы');
        return $this->messageService->sendMessage($chatId, '⚙️ <b>Выберите дату для Пусконаладочных работ</b>', $keyboard);
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
        return $category === 'Табель_Простой' || 
               $category === 'Табель_ШМ_Простой' ||
               str_contains($category, '_Простой_') ||
               str_contains($category, '_ШМ_Простой_');
    }

    /**
     * Handle idle/downtime menu system (3-level hierarchy)
     */
    private function handleIdleMenu(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Debug logging only
        error_log("TabelHandler: handleIdleMenu called with category: {$category}");
        
        // Level 1: Main categories (1-6)
        if ($category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой' || 
            $category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой') {
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
            error_log("TabelHandler: Processing subcategory {$categoryNumber}, supervision: " . ($isSupervision ? 'yes' : 'no'));
            return $this->handleIdleSubcategories($chatId, $categoryNumber, $isSupervision);
        }
        
        // Level 3: Sub-subcategories (double dash) or final selection
        if (preg_match('/^Табель_(ШМ_)?Простой_(\d+)_(.+)$/', $category, $matches)) {
            $categoryNumber = (int)$matches[2];
            $subcategory = $matches[3];
            $isSupervision = !empty($matches[1]);
            
            // Special handling for "Свой комментарий" - prompt for custom input (same pattern as "Прочее")
            if ($subcategory === 'kom') {
                // Set state to wait for custom input - SAME pattern as "Прочее" in additional works
                $conversation->notes['awaiting_custom_input'] = true;
                $conversation->notes['custom_input_activity_type'] = 'Простой_Комментарий';
                $conversation->notes['idle_custom_category'] = $category; // Store for later use
                $conversation->update();
                
                error_log("TabelHandler: handling custom idle comment - prompting for custom input (same pattern as Prochee)");
                
                // Prompt user for custom input - idle-specific text
                $text = "Пожалуйста укажите причину простоя:";
                return $this->messageService->sendMessage($chatId, $text);
            }
            
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
        // Debug logging only
        error_log("TabelHandler: handleIdleSubcategories called for category {$categoryNumber}, supervision: " . ($isSupervision ? 'yes' : 'no'));
        
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
                    'sog' => 'Отсутствие управленческого решения'
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
                    'mes' => 'Отсутствие свободных мест',
                    'ots' => 'Отсутствие транспорта'
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
                    'kom' => 'Другая причина'
                ]
            ]
        ];
        
        if (!isset($categories[$categoryNumber])) {
            error_log("TabelHandler: Category {$categoryNumber} not found!");
            return $this->messageService->sendMessage($chatId, 'Неизвестная категория');
        }
        
        $category = $categories[$categoryNumber];
        $keyboard = [];
        
        foreach ($category['items'] as $key => $text) {
            $callbackData = "category_{$prefix}_{$categoryNumber}_{$key}";
            // Debug logging only
            error_log("TabelHandler: Generated callback: {$callbackData} (length: " . strlen($callbackData) . ")");
            
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
            // Category 3: Construction readiness sub-subcategories removed - now goes directly to report
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
        // 🚨 URGENT DEBUG: Log that we reached commissioning idle final selection
        error_log("🚨🚨🚨 COMMISSIONING IDLE: handleIdleFinalSelection called with category: '{$category}'");
        error_log("🚨 COMMISSIONING IDLE: This should generate proper format with parent category!");
        
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Convert the detailed idle selection to a simple "Простой" activity type
        $activityType = str_contains($category, 'ШМ_') ? 'ШМ_Простой' : 'Простой';
        
        // CRITICAL FIX: For idle flows, check if project_code exists, if not, prompt for project selection
        $projectCode = $conversation->notes['project_code'] ?? '';
        
        if (empty($projectCode)) {
            error_log("🚨 IDLE FLOW: No project_code found in conversation, prompting for project selection");
            error_log("🚨 IDLE FLOW: Conversation notes: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE));
            
            // Store the idle information for after project selection
            $conversation->notes['pending_idle_category'] = $category;
            $conversation->notes['is_commissioning'] = true; // Set commissioning flag
            $conversation->update();
            
            // Show project selection
            $text = "<b>Табель - Простой, выбор проекта</b>";
            $keyboard = $this->messageService->createProjectSelectionKeyboard($callbackQuery->getFrom()->getId());
            return $this->messageService->sendMessage($chatId, $text, $keyboard);
        }
        
        // Store the detailed idle reason in conversation notes for confirmation
        // Use specialized commissioning idle reason formatter
        $idleReason = $this->getCommissioningIdleReason($category, $conversation);
        error_log("🚨 TabelHandler: handleIdleFinalSelection - Generated commissioning idle reason: '{$idleReason}' from category: '{$category}'");
        error_log("🚨 TabelHandler: If idle reason is 'Простой', the callback lacks subcategory data!");
        $conversation->notes['idle_reason'] = $idleReason;
        $conversation->update();
        
        $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
        $confirmationText .= "\n\n<b>Причина простоя:</b> " . $conversation->notes['idle_reason'];
        
        // 🔍 DEBUG: Force log the idle reason in confirmation message
        error_log("🔥 CONFIRMATION MESSAGE DEBUG: idle_reason = '" . $conversation->notes['idle_reason'] . "'");
        error_log("🔥 If you see only 'Дефицит по спецификации', there's a different code path!");
        
        $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
        
        return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
    }

    /**
     * Get specialized commissioning idle reason with Parent (Subcategory) format
     */
    private function getCommissioningIdleReason(string $category, Conversation $conversation = null): string
    {
        // 🚨 URGENT DEBUG: Log the EXACT callback received
        error_log("🚨 COMMISSIONING DEBUG: getCommissioningIdleReason called with EXACT category: '{$category}'");
        error_log("🚨 COMMISSIONING DEBUG: Category byte length: " . strlen($category));
        error_log("🚨 COMMISSIONING DEBUG: Category hex dump: " . bin2hex($category));
        
        // If we get just 'Простой' or something basic, force log this critical issue
        if ($category === 'Простой' || $category === 'category_Табель_Простой') {
            error_log("🚨🚨🚨 CRITICAL: Commissioning idle got basic callback without subcategory data!");
            error_log("🚨🚨🚨 This means the idle menu is not generating proper callbacks!");
            return 'DEBUG: CALLBACK MISSING SUBCATEGORY DATA';
        }
        
        error_log("🔧 TabelHandler: getCommissioningIdleReason called for commissioning works with: '{$category}'");
        
        // Parse the callback to build a readable reason with Parent (Subcategory) format
        // Handle both 'category_Табель_' and 'Табель_' prefixes
        $cleanCategory = $category;
        if (str_starts_with($category, 'category_Табель_')) {
            $cleanCategory = substr($category, strlen('category_Табель_'));
        } elseif (str_starts_with($category, 'Табель_')) {
            $cleanCategory = substr($category, strlen('Табель_'));
        }
        
        $parts = explode('_', $cleanCategory);
        error_log("TabelHandler: commissioning callback parts: " . json_encode($parts) . " from clean category: '{$cleanCategory}'");
        
        $parentCategories = [
            '1' => 'Ожидание допоставки',
            '2' => 'Отсутствие обратной связи', 
            '3' => 'Готовность Заказчика',
            '4' => 'Транспорт',
            '5' => 'Погодные условия',
            '6' => 'Прочее'
        ];
        
        // Enhanced mapping for commissioning works with parent-child relationship
        $commissioningSubcategories = [
            // Категория 1: Ожидание допоставки -> Commissioning specific format
            'def' => ['parent' => '1', 'text' => 'Дефицит по спецификации'],
            'ost' => ['parent' => '1', 'text' => 'Отсутствие в поставке'],
            'izm' => ['parent' => '1', 'text' => 'Изменение ТР'],
            // Категория 2: Отсутствие обратной связи
            'teh' => ['parent' => '2', 'text' => 'Техническое решение'],
            'sog' => ['parent' => '2', 'text' => 'Отсутствие управленческого решения'],
            // Категория 3: Готовность Заказчика
            'ene' => ['parent' => '3', 'text' => 'Отсутствие энергоресурсов'],
            'rab' => ['parent' => '3', 'text' => 'Отсутствие рабочих сред'],
            'per' => ['parent' => '3', 'text' => 'Перенос предъявления работ'],
            'str' => ['parent' => '3', 'text' => 'Отсутствие строительной готовности'],
            // Категория 4: Транспорт
            'pol' => ['parent' => '4', 'text' => 'Поломка'],
            'mes' => ['parent' => '4', 'text' => 'Отсутствие свободных мест'],
            'ots' => ['parent' => '4', 'text' => 'Отсутствие транспорта'],
            // Категория 5: Погодные условия
            'bur' => ['parent' => '5', 'text' => 'Буран/снегопад'],
            'mor' => ['parent' => '5', 'text' => 'Мороз'],
            'ras' => ['parent' => '5', 'text' => 'Распутица'],
            // Категория 6: Прочее
            'po' => ['parent' => '6', 'text' => 'Разработка ПО'],
            'dop' => ['parent' => '6', 'text' => 'Отсутствие допуска к работам'],
            'svy' => ['parent' => '6', 'text' => 'Отсутствие связи'],
            'kom' => ['parent' => '6', 'text' => 'Свой комментарий']
        ];
        
        // Sub-subcategory mappings for technical solution
        $technicalSolutionSubcategories = [
            'es' => 'Раздел ЭС',
            'ath' => 'Раздел АТХ', 
            'ov' => 'Раздел ОВ',
            'ps' => 'Раздел ПС/ОС',
            'th' => 'Раздел ТХ',
            'pki' => 'Поставщик ПКИ'
        ];
        
        $reason = '';
        
        // Skip supervision prefix if present and extract relevant parts
        // Expected format after cleaning: "Простой_1_def" or "ШМ_Простой_1_def" or "Простой_2_teh_es"
        $startIndex = 0;
        if ($parts[0] === 'ШМ' && isset($parts[1]) && $parts[1] === 'Простой') {
            $startIndex = 2; // Skip 'ШМ_Простой_'
        } elseif ($parts[0] === 'Простой') {
            $startIndex = 1; // Skip 'Простой_'
        }
        
        // Extract the relevant parts
        $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
        $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
        $subSubcategoryKey = isset($parts[$startIndex + 2]) ? $parts[$startIndex + 2] : null; // For 4-level structure
        
        error_log("TabelHandler: commissioning after parsing - categoryNum='{$categoryNum}', subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'");
        
        // Handle direct subcategory access (when callback is missing category number)
        if ($categoryNum && !isset($parentCategories[$categoryNum]) && isset($commissioningSubcategories[$categoryNum])) {
            $subcategoryKey = $categoryNum;
            $categoryNum = null;
            error_log("TabelHandler: commissioning detected direct subcategory access - subcategoryKey='{$subcategoryKey}'");
        }
        
        // Build the specialized commissioning format
        
        // Special case: Custom comment with user-provided text
        if ($subcategoryKey === 'kom') {
            // Get the custom text stored by handleCustomInput
            $customText = '';
            
            // Check multiple possible storage keys for custom text
            if ($conversation) {
                // Primary key used by handleCustomInput
                if (isset($conversation->notes['custom_idle_text'])) {
                    $customText = $conversation->notes['custom_idle_text'];
                    error_log("✅ TabelHandler: Found custom_idle_text: '{$customText}'");
                }
                // Secondary key - check if stored as general custom input
                elseif (isset($conversation->notes['custom_input_text'])) {
                    $customText = $conversation->notes['custom_input_text'];
                    error_log("✅ TabelHandler: Found custom_input_text (fallback): '{$customText}'");
                }
            }
            
            // Enhanced debug logging
            error_log("📝 TabelHandler: CUSTOM IDLE TEXT DEBUG - subcategoryKey: '{$subcategoryKey}'");
            error_log("📝 TabelHandler: conversation notes available: " . json_encode(array_keys($conversation->notes ?? []), JSON_UNESCAPED_UNICODE));
            error_log("📝 TabelHandler: final custom text value: '{$customText}'");
            
            if (!empty($customText)) {
                $parentText = $parentCategories['6']; // "Прочее"
                $reason = $parentText . ' (' . $customText . ')';
                error_log("🔧 TabelHandler: commissioning built custom comment format: '{$reason}'");
                return $reason; // CRITICAL: Return immediately to prevent further processing
            } else {
                // Fallback if no custom text found
                $reason = $parentCategories['6'] . ' (Другая причина)';
                error_log("🚨 TabelHandler: using fallback for custom comment - no custom text found");
                error_log("🚨 TabelHandler: conversation object: " . ($conversation ? 'exists' : 'null'));
                error_log("🚨 TabelHandler: full conversation notes: " . json_encode($conversation->notes ?? [], JSON_UNESCAPED_UNICODE));
                return $reason; // CRITICAL: Return immediately with fallback
            }
        }
        // Case 1: 4-level structure (Category -> Subcategory -> Sub-subcategory)
        // Format: "Parent Category (Subcategory, Sub-subcategory)"
        if ($subSubcategoryKey && isset($technicalSolutionSubcategories[$subSubcategoryKey]) && 
            $subcategoryKey === 'teh' && $categoryNum === '2') {
            
            $parentText = $parentCategories['2']; // "Отсутствие обратной связи"
            $subcategoryText = $commissioningSubcategories['teh']['text']; // "Техническое решение"
            $subSubcategoryText = $technicalSolutionSubcategories[$subSubcategoryKey]; // "Раздел ЭС"
            
            $reason = $parentText . ' (' . $subcategoryText . ', ' . $subSubcategoryText . ')';
            error_log("🔧 TabelHandler: commissioning built 4-level format: '{$reason}'");
        }
        // Case 2: 3-level structure (Category -> Subcategory)
        // Format: "Parent Category (Subcategory)"
        elseif ($subcategoryKey && isset($commissioningSubcategories[$subcategoryKey])) {
            $subcategoryData = $commissioningSubcategories[$subcategoryKey];
            $subcategoryText = $subcategoryData['text'];
            
            // Get parent category name
            if ($subcategoryData['parent'] && isset($parentCategories[$subcategoryData['parent']])) {
                $parentText = $parentCategories[$subcategoryData['parent']];
                $reason = $parentText . ' (' . $subcategoryText . ')';
                error_log("🔧 TabelHandler: commissioning built parent-child format: '{$reason}'");
            } else {
                $reason = $subcategoryText;
                error_log("TabelHandler: commissioning using subcategory only: '{$reason}'");
            }
        }
        // Case 3: 2-level structure (Category only)
        // Format: "Parent Category"
        elseif ($categoryNum && isset($parentCategories[$categoryNum])) {
            $reason = $parentCategories[$categoryNum];
            error_log("TabelHandler: commissioning using parent category only: '{$reason}'");
        }
        
        $finalReason = $reason ?: 'Простой';
        error_log("🔧 TabelHandler: COMMISSIONING FINAL IDLE REASON: '{$finalReason}' for category '{$category}'");
        
        return $finalReason;
    }

    /**
     * Handle custom idle comment input - prompt user to enter their own reason
     */
    private function handleCustomIdleCommentInput(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse
    {
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        
        // Store the category for later use when we receive the custom text
        $conversation->notes['idle_custom_category'] = $category;
        $conversation->notes['state'] = 'awaiting_custom_idle_reason';
        $conversation->update();
        
        error_log("TabelHandler: handleCustomIdleCommentInput - stored category: '{$category}'");
        
        // Create back button to return to "Прочее" subcategories
        $isSupervision = str_contains($category, 'ШМ_');
        $backCallback = $isSupervision ? 'category_Табель_ШМ_Простой_6' : 'category_Табель_Простой_6';
        
        $keyboard = [
            [
                ['text' => '⬅️ Назад к "Прочее"', 'callback_data' => $backCallback]
            ]
        ];
        
        return $this->messageService->sendMessage(
            $chatId, 
            "✍️ <b>Другая причина</b>\n\nНапишите причину простоя:", 
            $keyboard
        );
    }

    /**
     * Handle message when expecting custom idle reason input
     */
    public function handleMessage(Message $message, Conversation $conversation): ServerResponse
    {
        $chatId = $message->getChat()->getId();
        $text = $message->getText();
        
        // Check if we're expecting custom idle reason input
        if (isset($conversation->notes['state']) && $conversation->notes['state'] === 'awaiting_custom_idle_reason') {
            error_log("TabelHandler: handleMessage - received custom idle reason: '{$text}'");
            
            // Get the stored category
            $category = $conversation->notes['idle_custom_category'] ?? '';
            if (empty($category)) {
                error_log("TabelHandler: handleMessage - missing idle_custom_category in conversation");
                return $this->messageService->sendMessage($chatId, '❌ Ошибка: потеряна информация о категории.');
            }
            
            // Store the custom text
            $conversation->notes['custom_idle_text'] = $text;
            $conversation->notes['state'] = null; // Clear state
            $conversation->update();
            
            // Create a proper mock callback query with required message structure
            $mockMessage = new \Longman\TelegramBot\Entities\Message([
                'message_id' => 1,
                'date' => time(),
                'chat' => ['id' => $chatId, 'type' => 'private']
            ]);
            $callbackQuery = new \Longman\TelegramBot\Entities\CallbackQuery([
                'id' => '1',
                'from' => ['id' => $conversation->user_id ?? 1, 'is_bot' => false, 'first_name' => 'User'],
                'message' => $mockMessage,
                'data' => $category
            ]);
            return $this->handleIdleFinalSelection($callbackQuery, $category, $conversation);
        }
        
        // Not handling this message
        return new ServerResponse(['ok' => false], 'Not handled');
    }

    /**
     * Extract idle reason from callback data (legacy method for non-commissioning)
     */
    private function getIdleReasonFromCallback(string $category): string
    {
        // Debug logging
        error_log("🔍 TabelHandler: NEW LOGIC - getIdleReasonFromCallback called with: '{$category}'");
        error_log("🔍 TabelHandler: This should produce format 'Parent (Subcategory)'");
        
        // Parse the callback to build a readable reason
        $parts = explode('_', str_replace('category_Табель_', '', $category));
        error_log("TabelHandler: callback parts after parsing: " . json_encode($parts));
        
        $categories = [
            '1' => 'Ожидание допоставки', // 🔍 NEW LOGIC TEST - if you see old format, server not updated
            '2' => 'Отсутствие обратной связи', 
            '3' => 'Готовность Заказчика',
            '4' => 'Транспорт',
            '5' => 'Погодные условия',
            '6' => 'Прочее'
        ];
        
        // Mapping subcategories to their parent categories for reverse lookup
        $subcategoryToParent = [
            // Категория 1: Ожидание допоставки
            'def' => ['parent' => '1', 'text' => 'Дефицит по спецификации'],
            'ost' => ['parent' => '1', 'text' => 'Отсутствие в поставке'],
            'izm' => ['parent' => '1', 'text' => 'Изменение ТР'],
            // Категория 2: Отсутствие обратной связи
            'teh' => ['parent' => '2', 'text' => 'Техническое решение'],
            'sog' => ['parent' => '2', 'text' => 'Согласование плана работ'],
            // Категория 3: Готовность Заказчика
            'ene' => ['parent' => '3', 'text' => 'Отсутствие энергоресурсов'],
            'rab' => ['parent' => '3', 'text' => 'Отсутствие рабочих сред'],
            'per' => ['parent' => '3', 'text' => 'Перенос предъявления работ'],
            'str' => ['parent' => '3', 'text' => 'Отсутствие строительной готовности'],
            // Категория 4: Транспорт
            'pol' => ['parent' => '4', 'text' => 'Поломка'],
            'mes' => ['parent' => '4', 'text' => 'Отсутствие свободных мест'],
            // Категория 5: Погодные условия
            'bur' => ['parent' => '5', 'text' => 'Буран/снегопад'],
            'mor' => ['parent' => '5', 'text' => 'Мороз'],
            'ras' => ['parent' => '5', 'text' => 'Распутица'],
            // Категория 6: Прочее
            'po' => ['parent' => '6', 'text' => 'Разработка ПО'],
            'dop' => ['parent' => '6', 'text' => 'Отсутствие допуска к работам'],
            'svy' => ['parent' => '6', 'text' => 'Отсутствие связи'],
            'kom' => ['parent' => '6', 'text' => 'Другая причина'],
            // Подподкатегории (разделы)
            'es' => ['parent' => null, 'text' => 'Раздел ЭС'],
            'ath' => ['parent' => null, 'text' => 'Раздел АТХ'],
            'ov' => ['parent' => null, 'text' => 'Раздел ОВ'],
            'ps' => ['parent' => null, 'text' => 'Раздел ПС/ОС'],
            'th' => ['parent' => null, 'text' => 'Раздел ТХ'],
            'pki' => ['parent' => null, 'text' => 'Поставщик ПКИ']
        ];
        
        $reason = '';
        
        // Skip supervision prefix if present
        $startIndex = ($parts[0] === 'ШМ' && $parts[1] === 'Простой') ? 2 : 1;
        
        // Extract the relevant parts
        $categoryNum = isset($parts[$startIndex]) ? $parts[$startIndex] : null;
        $subcategoryKey = isset($parts[$startIndex + 1]) ? $parts[$startIndex + 1] : null;
        $subSubcategoryKey = isset($parts[$startIndex + 2]) ? $parts[$startIndex + 2] : null;
        
        error_log("TabelHandler: categoryNum='{$categoryNum}', subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'");
        
        // Special case: If categoryNum looks like a subcategory key, treat it as such
        if ($categoryNum && !isset($categories[$categoryNum]) && isset($subcategoryToParent[$categoryNum])) {
            // This is actually a subcategory, not a category number
            $subSubcategoryKey = $subcategoryKey; // shift the parts
            $subcategoryKey = $categoryNum;
            $categoryNum = null;
            error_log("TabelHandler: Detected direct subcategory access - subcategoryKey='{$subcategoryKey}', subSubcategoryKey='{$subSubcategoryKey}'");
        }
        
        // If we have a category number, use it
        if ($categoryNum && isset($categories[$categoryNum])) {
            $reason = $categories[$categoryNum];
            error_log("TabelHandler: Found main category: '{$reason}'");
        }
        // If no category number but we have a subcategory, look up its parent
        elseif ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
            $parentCategoryNum = $subcategoryToParent[$subcategoryKey]['parent'];
            if ($parentCategoryNum && isset($categories[$parentCategoryNum])) {
                $reason = $categories[$parentCategoryNum];
                error_log("TabelHandler: Found parent category via subcategory lookup: '{$reason}'");
            }
        }
        
        // Add subcategory using new logic: Parent (Subcategory)
        if ($subcategoryKey && isset($subcategoryToParent[$subcategoryKey])) {
            $subcategoryText = $subcategoryToParent[$subcategoryKey]['text'];
            if (!empty($reason)) {
                // Always use parentheses format for subcategory
                $reason .= ' (' . $subcategoryText . ')';
            } else {
                $reason = $subcategoryText;
            }
            error_log("TabelHandler: Added subcategory: '{$subcategoryText}', reason now: '{$reason}'");
        }
        
        // Add sub-subcategory using new logic: Parent (Subcategory → Sub-subcategory)
        if ($subSubcategoryKey && isset($subcategoryToParent[$subSubcategoryKey])) {
            $subSubcategoryText = $subcategoryToParent[$subSubcategoryKey]['text'];
            if (!empty($reason) && $subcategoryKey) {
                // Replace the parentheses format with arrow format for sub-subcategory
                $subcategoryText = $subcategoryToParent[$subcategoryKey]['text'];
                $parentCategory = $categories[$subcategoryToParent[$subcategoryKey]['parent']];
                $reason = $parentCategory . ' (' . $subcategoryText . ' → ' . $subSubcategoryText . ')';
            } else {
                $reason = $subSubcategoryText;
            }
            error_log("TabelHandler: Added sub-subcategory: '{$subSubcategoryText}', reason now: '{$reason}'");
        }
        
        $finalReason = $reason ?: 'Простой';
        error_log("🔍 TabelHandler: FINAL IDLE REASON DEBUG: '{$finalReason}' for category '{$category}'");
        error_log("🔍 TabelHandler: If you see only 'Дефицит по спецификации', check if callback is missing category number!");
        
        return $finalReason;
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
     * Fix known Cyrillic pattern corruptions
     */
    private function fixKnownCyrillicPatterns($str)
    {
        // Log the original string
        error_log("TabelHandler: fixKnownCyrillicPatterns input: '{$str}'");
        error_log("TabelHandler: fixKnownCyrillicPatterns input hex: " . bin2hex($str));
        
        // Special case: if string is already the correct electrical equipment callback, return as is
        if ($str === 'category_Табель_Электрооборудование' || $str === 'Табель_Электрооборудование') {
            error_log("TabelHandler: fixKnownCyrillicPatterns returning correct electrical equipment string as is: '{$str}'");
            return $str;
        }
        
        // Handle the specific corrupted electrical equipment callback data
        if ($str === 'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ') {
            error_log("TabelHandler: fixKnownCyrillicPatterns fixing corrupted electrical equipment callback");
            return 'category_Табель_Электрооборудование';
        }
        
        // Replace the known corrupted patterns
        $replacements = [
            'РЈСЂРѕРІРµРЅСЊР”РѕСЃС‚СѓРїР°' => 'УровеньДоступа',
            'РЈРїСЂР°РІР»РµРЅРёРµ' => 'Управление',
            'РЎРІР°СЂРѕС‡РЅС‹Рµ' => 'Сварочные',
            'Р”Р°С‚Р°' => 'Дата',
            'РЎРµРіРѕРґРЅСЏ' => 'Сегодня',
            'Р’С‡РµСЂР°' => 'Вчера',
            'РЎРѕСЂРѕСЃРјРѕС‚СЂ' => 'Просмотр',
            'Р¤РРћ' => 'ФИО',
            'Р¤РЊ' => 'ФН',
            'РњРѕР±РёР»СЊРЅР°СЏР‘СЂРёРіР°РґР°' => 'МобильнаяБригада',
            'РњРѕР±РёР»СЊРЅР°СЏ_Р‘СЂРёРіР°РґР°' => 'Мобильная_Бригада',
            'РЎСѓСЃРєРѕРЅР°Р»Р°РґРєР°' => 'Пусконаладка',
            'РЁРµС„РњРѕРЅС‚Р°Р¶' => 'ШефМонтаж',
            'РўР°Р±РµР»СЊ' => 'Табель',
            'Р—Р°РїСЂРѕСЃРџРѕРІС‹С€РµРЅРёСЏ' => 'ЗапросПовышения',
            'РџРѕРІС‹СЃРёС‚СЊ' => 'Повысить',
            'РџРѕРЅРёР·РёССЊ' => 'Понизить',
            // Add missing patterns for commissioning works
            'РџРќР›' => 'ПНЛ',
            'РЎСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
            'Р Р°Р±Р±РѕС‚С‹' => 'Работы',
            // Add missing patterns for the main buttons (order matters - more specific first)
            'РЁРµС„_РјРѕРЅС‚Р°Р¶' => 'Шеф_монтаж',
            'РўРµРєСѓС‰Р°СЏ' => 'Текущая',
            // Add individual word patterns
            'РјРѕРЅС‚Р°Р¶' => 'монтаж',
            // Add pattern for commissioning works abbreviation
            'РџРќР ' => 'ПНР',
            // Add pattern for preparation
            'Р РѕРґРіРѕС‚РѕРІРєР°' => 'Подготовка',
            // Add pattern for electrical equipment - this is the key fix
            'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
            // Add pattern for full electrical equipment callback - this is the key fix
            'category_РўР°Р±РµР»СЊ_Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'category_Табель_Электрооборудование',
            // Add additional patterns for double-encoded Cyrillic that might be causing issues
            'Табель_Электрооборудование' => 'Табель_Электрооборудование',
            'category_Табель_Электрооборудование' => 'category_Табель_Электрооборудование',
            // Handle mixed encoding cases
            'РўР°Р±РµР»СЊ_Электрооборудование' => 'Табель_Электрооборудование',
            // Handle the specific partially corrupted case
            'Р­РµР»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРинРµ' => 'Электрооборудование',
            // Handle the exact double-encoded pattern we're seeing in the test
            'Р­Р»РµРєС‚СЂРѕРѕР±РѕСЂСѓРґРѕРІР°РЅРёРµ' => 'Электрооборудование',
            // Add missing patterns for commissioning works - full words
            'РџСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
            'Р Р°Р±РѕС‚С‹' => 'Работы',
            // Add missing patterns for commissioning works - partial words
            'РЎСѓСЃРєРѕРЅР°Р»Р°РґРѕС‡РЅС‹Рµ' => 'Пусконаладочные',
            'Р Р°Р±Р±РѕС‚С‹' => 'Работы'
        ];
        
        foreach ($replacements as $corrupted => $fixed) {
            if (strpos($str, $corrupted) !== false) {
                error_log("TabelHandler: found corrupted pattern '{$corrupted}' in string, replacing with '{$fixed}'");
                $str = str_replace($corrupted, $fixed, $str);
            }
        }
        
        // Log the result
        error_log("TabelHandler: fixKnownCyrillicPatterns output: '{$str}'");
        error_log("TabelHandler: fixKnownCyrillicPatterns output hex: " . bin2hex($str));
        
        return $str;
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
     * Handle heating and ventilation equipment menu
     */
    private function handleHeatingVentilationEquipmentMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createHeatingVentilationEquipmentKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>❄️ Отопление и вентиляция</b> Выберите тип работ:',
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
     * Handle security alarm equipment menu
     */
    private function handleSecurityAlarmEquipmentMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createSecurityAlarmEquipmentKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🛡️ Охранная сигнализация</b> Выберите тип работ:',
            $keyboard
        );
    }

    /**
     * Handle technological equipment menu
     */
    private function handleTechnologicalEquipmentMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createTechnologicalEquipmentKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>⚙️ Технологическое оборудование</b> Выберите тип работ:',
            $keyboard
        );
    }

    /**
     * Handle metrology menu
     */
    private function handleMetrologyMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createMetrologyKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>📏 Метрология</b> Выберите тип работ:',
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
     * Handle additional works for technological equipment
     */
    private function handleAdditionalWorksTechnologicalMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createAdditionalWorksTechnologicalKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🛠️ Дополнительные работы - Технологическое оборудование</b> Выберите тип дополнительных работ:',
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
     * Handle additional works for heating and ventilation equipment
     */
    private function handleAdditionalWorksHeatingVentilationMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createAdditionalWorksHeatingVentilationKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🛠️ Дополнительные работы - Отопление и вентиляция</b> Выберите тип дополнительных работ:',
            $keyboard
        );
    }

    /**
     * Handle additional works for security alarm equipment
     */
    private function handleAdditionalWorksSecurityAlarmMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createAdditionalWorksSecurityAlarmKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🛠️ Дополнительные работы - Охранная сигнализация</b> Выберите тип дополнительных работ:',
            $keyboard
        );
    }

    /**
     * Handle additional works for metrology
     */
    private function handleAdditionalWorksMetrologyMenu(string $chatId): ServerResponse
    {
        $keyboard = $this->messageService->createAdditionalWorksMetrologyKeyboard();
        return $this->messageService->sendMessage(
            $chatId, 
            '<b>🛠️ Дополнительные работы - Метрология</b> Выберите тип дополнительных работ:',
            $keyboard
        );
    }

    /**
     * Handle custom input for "Прочее" activities
     */
    public function handleCustomInput(string $chatId, string $userInput, Conversation $conversation): ServerResponse
    {
        // Check if we're waiting for the NEW Prochee custom input
        if (isset($conversation->notes['awaiting_custom_prochee_input']) && $conversation->notes['awaiting_custom_prochee_input']) {
            error_log("TabelHandler: Processing NEW Prochee custom input: '{$userInput}' for user");
            error_log("TabelHandler: Current conversation notes before processing: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE));
            
            // Store the custom input and clear the waiting state
            $conversation->notes['prochee_custom_text'] = $userInput;
            $conversation->notes['awaiting_custom_prochee_input'] = false;
            
            // CRITICAL: Force immediate conversation update to prevent caching issues
            $conversation->update();
            
            // VERIFICATION: Re-create conversation to verify storage
            $userId = $conversation->user_id ?? 0;
            $verifyConversation = new Conversation($userId, $chatId, '/callback');
            $verifyCustomText = $verifyConversation->notes['prochee_custom_text'] ?? 'NOT_FOUND';
            error_log("TabelHandler: VERIFICATION - Stored and re-read custom text: '{$verifyCustomText}'");
            
            if ($verifyCustomText !== $userInput) {
                error_log("TabelHandler: CRITICAL ERROR - Custom text not properly stored! Input: '{$userInput}', Retrieved: '{$verifyCustomText}'");
            }
            
            error_log("TabelHandler: Stored new custom text: '{$userInput}' and updated conversation");
            
            // Show confirmation message with custom input
            $activityType = $conversation->notes['prochee_activity_type'] ?? 'Прочее_ЭлектрОб';
            $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
            
            $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
            $confirmationText .= "\n\n<b>Описание работы:</b> " . $userInput;
            
            error_log("TabelHandler: Created confirmation text with new custom input: '{$userInput}'");
            
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_Проч_yes");
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Check if we're waiting for the OLD custom input (for backward compatibility)
        if (!isset($conversation->notes['awaiting_custom_input']) || !$conversation->notes['awaiting_custom_input']) {
            // Not waiting for custom input, return normal handling
            return $this->messageService->sendMessage($chatId, 'Неизвестная команда табеля');
        }
        
        // Get the activity type for which we're collecting custom input
        $activityType = $conversation->notes['custom_input_activity_type'] ?? '';
        
        if (empty($activityType)) {
            return $this->messageService->sendMessage($chatId, 'Ошибка: не удалось определить тип активности');
        }
        
        // Special handling for idle comments
        if ($activityType === 'Простой_Комментарий') {
            // Get the stored idle category
            $category = $conversation->notes['idle_custom_category'] ?? '';
            if (empty($category)) {
                return $this->messageService->sendMessage($chatId, 'Ошибка: потеряна информация о категории.');
            }
            
            // Store the custom text for idle reason formatting
            $conversation->notes['custom_idle_text'] = $userInput;
            $conversation->notes['awaiting_custom_input'] = false;
            $conversation->update();
            
            error_log("TabelHandler: handleCustomInput - stored custom idle text: '{$userInput}' for category: '{$category}'");
            
            // CRITICAL FIX: Process idle comment exactly like working "Прочее" equipment flow
            // Instead of calling handleIdleFinalSelection, show confirmation directly
            $projectCode = $conversation->notes['project_code'] ?? '';
            
            // If no project code, prompt for project selection (same as my previous fix)
            if (empty($projectCode)) {
                error_log("🚨 IDLE FLOW: No project_code found in conversation, prompting for project selection");
                
                // Store the idle information for after project selection
                $conversation->notes['pending_idle_category'] = $category;
                $conversation->notes['is_commissioning'] = true;
                $conversation->update();
                
                // Get user ID from conversation (since it's not in method parameters)
                $userId = $conversation->user_id ?? 0;
                
                if (!$userId) {
                    error_log("🚨 IDLE FLOW: No userId found in conversation");
                    return $this->messageService->sendMessage($chatId, 'Ошибка: не удалось определить пользователя');
                }
                
                // Show project selection
                $text = "<b>Табель - Простой, выбор проекта</b>";
                $keyboard = $this->messageService->createProjectSelectionKeyboard($userId);
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
            
            // Format idle reason using the specialized commissioning formatter
            $idleReason = $this->getCommissioningIdleReason($category, $conversation);
            error_log("TabelHandler: formatted idle reason: '{$idleReason}'");
            
            // Create confirmation message EXACTLY like working "Прочее" equipment
            $activityType = str_contains($category, 'ШМ_') ? 'ШМ_Простой' : 'Простой';
            $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
            $confirmationText .= "\n\n<b>Причина простоя:</b> " . $idleReason;
            
            error_log("TabelHandler: IDLE COMMENT - creating confirmation with formatted reason: '{$idleReason}'");
            
            // Use standard idle confirmation callback
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
            
            // Store the idle reason for later use in confirmation
            $conversation->notes['idle_reason'] = $idleReason;
            $conversation->update();
            
            return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
        }
        
        // Store the custom input in conversation notes
        $conversation->notes['custom_input_text'] = $userInput;
        $conversation->notes['awaiting_custom_input'] = false;
        $conversation->update();
        
        // Show confirmation message with the custom input
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        $confirmationText = $this->tabelEntry->getConfirmationMessage($activityType, $projectCode);
        $confirmationText .= "\n\n<b>Описание работы:</b> " . $userInput;
        
        // Use short callback format for equipment "Прочее" activities
        if ($activityType === 'Прочее_ЭлектрОб') {
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабЭлОб_Проч_yes");
        } elseif ($activityType === 'Прочее_ПожСигн') {
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПожСигн_Проч_yes");
        } elseif ($activityType === 'Прочее_Автом') {
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_Проч_yes");
        } elseif ($activityType === 'Прочее_ОхрСигн') {
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабОхрСиг_Проч_yes");
        } elseif ($activityType === 'Прочее_ПроектнаяДеятельность') {
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПроектнаяДеят_Проч_yes");
        } elseif ($activityType === 'Прочее_ПроектнаяДеятельность_ШМР') {
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабПроектнаяДеят_Проч_ШМР_yes");
        } elseif ($activityType === 'Прочее_Автом') {
            $keyboard = $this->messageService->createConfirmationKeyboard("cat_ТабАвтом_Проч_yes");
        } elseif ($activityType === 'УстрЗам_Прочее') {
            // Special handling for RK Прочее - use category format
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
        } else {
            $keyboard = $this->messageService->createConfirmationKeyboard("category_Табель_{$activityType}_yes");
        }
        return $this->messageService->sendMessage($chatId, $confirmationText, $keyboard);
    }

    /**
     * Handle confirmation and execution of the NEW "Прочее" activity with custom input
     */
    private function handleNewProcheeConfirmation(CallbackQuery $callbackQuery, Conversation $conversation): ServerResponse
    {
        // IMMEDIATE DEBUG: Log that method was called
        error_log("=== URGENT DEBUG: handleNewProcheeConfirmation ENTRY POINT ===");
        error_log("TabelHandler: handleNewProcheeConfirmation method HAS BEEN CALLED!");
        error_log("TabelHandler: This proves the callback routing is working!");
        
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $userId = $callbackQuery->getFrom()->getId();
        
        error_log("TabelHandler: chatId: {$chatId}, userId: {$userId}");
        error_log("TabelHandler: Executing NEW Prochee confirmation for user {$userId}");
        error_log("TabelHandler: Conversation notes at confirmation: " . json_encode($conversation->notes, JSON_UNESCAPED_UNICODE));
        
        // Get stored custom input
        $customText = $conversation->notes['prochee_custom_text'] ?? '';
        error_log("TabelHandler: Retrieved custom text from conversation: '{$customText}'");
        error_log("TabelHandler: Full conversation notes dump: " . print_r($conversation->notes, true));
        
        if (empty($customText)) {
            error_log("TabelHandler: ERROR - No custom text found in conversation notes");
            error_log("TabelHandler: Available conversation keys: " . implode(', ', array_keys($conversation->notes)));
            
            // Try to get from legacy custom_input_text as fallback
            $legacyCustomText = $conversation->notes['custom_input_text'] ?? '';
            error_log("TabelHandler: Checking legacy custom_input_text: '{$legacyCustomText}'");
            
            if (!empty($legacyCustomText)) {
                $customText = $legacyCustomText;
                error_log("TabelHandler: Using legacy custom text: '{$customText}'");
            } else {
                return $this->messageService->sendMessage($chatId, 'Ошибка: не найдено описание работы');
            }
        }
        
        // Get user data and required information
        try {
            $userData = $this->databaseService->getUserData($userId);
            error_log("TabelHandler: Retrieved user data for user {$userId}");
        } catch (\Exception $e) {
            error_log("TabelHandler: ERROR getting user data: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, 'Ошибка получения данных пользователя');
        }
        
        $fio = $userData['fio'] ?? 'Без ФИО';
        $date = $conversation->notes['tabel_data'] ?? date('d.m.Y');
        $projectCode = $conversation->notes['project_code'] ?? 'Unknown';
        
        // Determine activity type based on callback
        if ($callbackQuery->getData() === 'cat_ТабАвтом_Проч_yes') {
            $activityType = 'Прочее_Автом';
        } else {
            $activityType = 'Прочее_ЭлектрОб';
        }
        
        error_log("TabelHandler: Tabel entry data - FIO: '{$fio}', Date: '{$date}', Project: '{$projectCode}', Activity: '{$activityType}', Custom: '{$customText}'");
        
        // Check if user has necessary data filled
        if (!isset($userData['fio']) || $userData['fio'] === 'Без ФИО') {
            error_log("TabelHandler: ERROR - User FIO not set");
            $text = '<b>Для отправки отчета необходимо заполнить ФИО (один раз) командой /fio Фамилия Имя Отчество</b>';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Check if user has functional direction filled
        if (!isset($userData['functional_direction']) || $userData['functional_direction'] === 'Не указано') {
            error_log("TabelHandler: ERROR - User functional direction not set");
            $text = '<b>Для отправки отчета необходимо указать функциональное направление. Используйте /profile</b>';
            $text = $this->ensureUtf8Encoding($text);
            return $this->messageService->sendMessage($chatId, $text);
        }
        
        // Create and send tabel entry using the custom text as idle reason
        error_log("TabelHandler: Attempting to create tabel entry...");
        error_log("TabelHandler: About to call addEntry with customText: '{$customText}'");
        error_log("TabelHandler: Parameters - userId: {$userId}, fio: '{$fio}', date: '{$date}', projectCode: '{$projectCode}', activityType: '{$activityType}', chatId: '{$chatId}', customText: '{$customText}'");
        try {
            $success = $this->tabelEntry->addEntry($userId, $fio, $date, $projectCode, $activityType, $chatId, $customText);
            error_log("TabelHandler: TabelEntry->addEntry result: " . ($success ? 'SUCCESS' : 'FAILED'));
        } catch (\Exception $e) {
            error_log("TabelHandler: ERROR in addEntry: " . $e->getMessage());
            return $this->messageService->sendMessage($chatId, 'Ошибка при создании записи в табель: ' . $e->getMessage());
        }
        
        if ($success) {
            error_log("TabelHandler: Tabel entry created successfully with customText: '{$customText}'");
            
            // Create confirmation message for the user with the custom text
            $confirmationMessage = $this->tabelEntry->createTabelMessage($fio, $date, $projectCode, $activityType, $customText);
            
            // Add unique debug marker to identify this specific message
            $confirmationMessage = "🔧 [FIXED_VERSION] " . $confirmationMessage;
            
            error_log("TabelHandler: CRITICAL DEBUG - Created confirmation message with custom text: '{$confirmationMessage}'");
            error_log("TabelHandler: CRITICAL DEBUG - Custom text passed to createTabelMessage: '{$customText}'");
            error_log("TabelHandler: CRITICAL DEBUG - Activity type: '{$activityType}'");
            
            // Clear the conversation notes related to Prochee
            unset($conversation->notes['prochee_custom_text']);
            unset($conversation->notes['prochee_activity_type']);
            unset($conversation->notes['prochee_source_category']);
            unset($conversation->notes['awaiting_custom_prochee_input']);
            $conversation->update();
            error_log("TabelHandler: Cleared conversation notes");
            
            // Send confirmation message to user with custom text included
            error_log("TabelHandler: CRITICAL DEBUG - About to send message to user: '{$confirmationMessage}'");
            return $this->messageService->sendMessage($chatId, $confirmationMessage);
        }
        
        error_log("TabelHandler: ERROR - TabelEntry->addEntry returned false");
        return $this->messageService->sendMessage($chatId, 'Ошибка при добавлении записи в табель');
    }

    /**
     * Handle custom input for dates
     */
    public function handleCustomDateInput(string $chatId, string $userInput, Conversation $conversation, ?int $userId = null): ServerResponse
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
                $keyboard = $this->messageService->createProjectSelectionKeyboard($userId);
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
        }
        
        // Invalid date format, ask again
        $text = "Неверный формат даты. Пожалуйста, введите дату в формате ДД.ММ.ГГГГ (например, 25.12.2023):";
        return $this->messageService->sendMessage($chatId, $text);
    }

    /**
     * Handle warranty equipment menu
     */
    private function handleWarrantyEquipmentMenu(string $chatId, Conversation $conversation = null, string $context = null): ServerResponse
    {
        error_log("🔧 WARRANTY DEBUG: handleWarrantyEquipmentMenu called");
        
        // Determine context based on conversation notes or parameter
        if ($context === null) {
            $context = 'commissioning'; // default
            if ($conversation && isset($conversation->notes['is_supervision']) && $conversation->notes['is_supervision']) {
                $context = 'supervision';
                error_log("🔧 WARRANTY DEBUG: using supervision context for warranty equipment menu");
            } else {
                error_log("🔧 WARRANTY DEBUG: using commissioning context for warranty equipment menu");
            }
        } else {
            error_log("🔧 WARRANTY DEBUG: explicit context provided: {$context}");
        }
        
        $keyboard = $this->messageService->createWarrantyEquipmentKeyboard($context);
        error_log("🔧 WARRANTY DEBUG: warranty equipment keyboard created with " . count($keyboard) . " rows");
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>🛡️ Работы по гарантии</b>\n\nВыберите тип оборудования:",
            $keyboard
        );
    }

    /**
     * Handle RK comments elimination menu for commissioning department
     */
    private function handleCommissioningRKCommentsEliminationMenu(string $chatId, ?Conversation $conversation = null): ServerResponse
    {
        // For commissioning department, always use commissioning context
        $keyboard = $this->messageService->createEliminationRemarksRKKeyboard('commissioning');
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>🔧 Устранения замечаний РК</b>\n\nВыберите раздел:",
            $keyboard
        );
    }

    /**
     * Handle RK comments elimination menu (deprecated - for backward compatibility)
     */
    private function handleRKCommentsEliminationMenu(string $chatId, ?Conversation $conversation = null): ServerResponse
    {
        // Определяем контекст (отдел) на основе conversation или других данных
        $context = 'commissioning'; // значение по умолчанию
        
        if ($conversation) {
            // Проверяем, есть ли в conversation информацию о контексте
            if (isset($conversation->notes['context'])) {
                $context = $conversation->notes['context'];
            } else {
                // Пытаемся определить контекст из других данных conversation
                if (isset($conversation->notes['callback_data'])) {
                    $callbackData = $conversation->notes['callback_data'];
                    // Если callback_data содержит информацию о ШМР, устанавливаем контекст supervision
                    if (strpos($callbackData, 'ШМ') !== false || strpos($callbackData, 'Шеф') !== false || strpos($callbackData, 'category_Табель_ШМ_УЗ_РК') !== false) {
                        $context = 'supervision';
                    }
                }
                
                // Дополнительно проверяем по категории, из которой пришли
                if (isset($conversation->notes['category'])) {
                    $category = $conversation->notes['category'];
                    // Если категория содержит информацию о ШМР, устанавливаем контекст supervision
                    if ($category === 'Табель_ШМ_УЗ_РК' || $category === 'category_Табель_ШМ_УЗ_РК') {
                        $context = 'supervision';
                    }
                }
            }
        }
        
        $keyboard = $this->messageService->createEliminationRemarksRKKeyboard($context);
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>🔧 Устранения замечаний РК</b>\n\nВыберите раздел:",
            $keyboard
        );
    }

    /**
     * Handle RK comments elimination menu for supervision department
     */
    private function handleSupervisionRKCommentsEliminationMenu(string $chatId, ?Conversation $conversation = null): ServerResponse
    {
        // For supervision department, always use supervision context
        $keyboard = $this->messageService->createEliminationRemarksRKKeyboard('supervision');
        return $this->messageService->sendMessage(
            $chatId, 
            "<b>🔧 Устранения замечаний РК</b>\n\nВыберите раздел:",
            $keyboard
        );
    }
}