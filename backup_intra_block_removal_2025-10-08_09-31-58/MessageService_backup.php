<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Telegram;
use Exception;
use Error;

/**
 * Message service to handle all message sending operations
 * Centralizes message formatting and keyboard creation
 */
class MessageService
{
    private const HISTORY_CHAT_ID = '-1002722176147';
    
    // Group and Thread IDs for different functional directions
    private const MOBILE_BRIGADE_GROUP_ID = -1002722176147;
    private const MOBILE_BRIGADE_THREAD_ID = 2;      // Мобильная бригада
    
    private const COMMISSIONING_GROUP_ID = -1001400593888;
    private const COMMISSIONING_THREAD_ID = 338;     // Пусконаладка  
    
    private const SUPERVISION_GROUP_ID = -1001865074576;
    private const SUPERVISION_THREAD_ID = 14627;     // Шеф-монтаж
    
    // Default thread ID for backward compatibility
    private const DEFAULT_THREAD_ID = 2;
    
    /**
     * @var Telegram|null
     */
    private static $telegram = null;
    
    /**
     * Initialize Telegram bot instance for standalone message sending
     */
    private function initializeBotIfNeeded(): void
    {
        if (self::$telegram === null) {
            try {
                // Load config to get bot token
                $config = include __DIR__ . '/../../config.php';
                if (isset($config['api_key']) && isset($config['bot_username'])) {
                    self::$telegram = new Telegram($config['api_key'], $config['bot_username']);
                    error_log("MessageService: Bot initialized successfully");
                }
            } catch (\Exception $e) {
                error_log("MessageService: Failed to initialize bot - " . $e->getMessage());
            }
        }
    }

    /**
     * Send message to specific chat
     */
    public function sendMessage(string $chatId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): ServerResponse
    {
        try {
            error_log("MessageService sendMessage START for chatId: {$chatId}");
            error_log("MessageService sendMessage text: " . mb_substr($text, 0, 100) . "...");
            if ($keyboard) {
                error_log("MessageService sendMessage keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE));
            }
            
            // Initialize bot if needed for standalone execution
            $this->initializeBotIfNeeded();
            
            // Убеждаемся, что текст в правильной кодировке UTF-8
            if (function_exists('mb_convert_encoding')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            } else {
                // Fallback for systems without mbstring
                $text = utf8_encode($text);
            }
            
            $data = [
                'chat_id' => $chatId,
                'text' => $text,
                'disable_web_page_preview' => true,
                'parse_mode' => $parseMode,
            ];

            if ($keyboard) {
                // Убеждаемся, что клавиатура в правильной кодировке UTF-8
                // Преобразуем все строки в клавиатуре в UTF-8
                $keyboard = $this->ensureUtf8Encoding($keyboard);
                
                // Для inline-клавиатур просто передаем массив напрямую
                $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE);
                error_log("MessageService sendMessage keyboard JSON: " . $data['reply_markup']);
            }
            
            error_log("MessageService sendMessage data: " . json_encode($data, JSON_UNESCAPED_UNICODE));

            $result = Request::sendMessage($data);
            
            error_log("MessageService sendMessage result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'success' : 'failed') : 'unknown'));
            
            if (is_object($result) && method_exists($result, 'isOk') && !$result->isOk()) {
                error_log("MessageService sendMessage error: " . $result->getDescription());
                error_log("MessageService sendMessage error code: " . $result->getErrorCode());
            }
            
            error_log("MessageService sendMessage END");
            return $result;
            
        } catch (Exception $e) {
            error_log("MessageService sendMessage EXCEPTION: " . $e->getMessage());
            error_log("MessageService sendMessage EXCEPTION trace: " . $e->getTraceAsString());
            throw $e;
        } catch (Error $e) {
            error_log("MessageService sendMessage ERROR: " . $e->getMessage());
            error_log("MessageService sendMessage ERROR trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Send message with reply keyboard (persistent menu)
     */
    public function sendMessageWithReplyKeyboard(string $chatId, string $text, array $keyboard, string $parseMode = 'HTML'): ServerResponse
    {
        try {
            error_log("MessageService sendMessageWithReplyKeyboard START for chatId: {$chatId}");
            error_log("MessageService sendMessageWithReplyKeyboard text: " . mb_substr($text, 0, 100) . "...");
            if ($keyboard) {
                error_log("MessageService sendMessageWithReplyKeyboard keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE));
            }
            
            // Убеждаемся, что текст в правильной кодировке UTF-8
            if (function_exists('mb_convert_encoding')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            } else {
                // Fallback for systems without mbstring
                $text = utf8_encode($text);
            }
            
            $data = [
                'chat_id' => $chatId,
                'text' => $text,
                'disable_web_page_preview' => true,
                'parse_mode' => $parseMode,
            ];

            if ($keyboard) {
                // Убеждаемся, что клавиатура в правильной кодировке UTF-8
                // Преобразуем все строки в клавиатуре в UTF-8
                $keyboard = $this->ensureUtf8Encoding($keyboard);
                
                // Для reply-клавиатур передаем массив напрямую как keyboard
                $data['reply_markup'] = json_encode(['keyboard' => $keyboard, 'resize_keyboard' => true], JSON_UNESCAPED_UNICODE);
                error_log("MessageService sendMessageWithReplyKeyboard keyboard JSON: " . $data['reply_markup']);
            }
            
            error_log("MessageService sendMessageWithReplyKeyboard data: " . json_encode($data, JSON_UNESCAPED_UNICODE));

            $result = Request::sendMessage($data);
            
            error_log("MessageService sendMessageWithReplyKeyboard result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'success' : 'failed') : 'unknown'));
            
            if (is_object($result) && method_exists($result, 'isOk') && !$result->isOk()) {
                error_log("MessageService sendMessageWithReplyKeyboard error: " . $result->getDescription());
                error_log("MessageService sendMessageWithReplyKeyboard error code: " . $result->getErrorCode());
            }
            
            error_log("MessageService sendMessageWithReplyKeyboard END");
            return $result;
            
        } catch (Exception $e) {
            error_log("MessageService sendMessageWithReplyKeyboard EXCEPTION: " . $e->getMessage());
            error_log("MessageService sendMessageWithReplyKeyboard EXCEPTION trace: " . $e->getTraceAsString());
            throw $e;
        } catch (Error $e) {
            error_log("MessageService sendMessageWithReplyKeyboard ERROR: " . $e->getMessage());
            error_log("MessageService sendMessageWithReplyKeyboard ERROR trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Ensure all strings in keyboard array are UTF-8 encoded
     */
    private function ensureUtf8Encoding(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
            } elseif (is_array($value)) {
                $data[$key] = $this->ensureUtf8Encoding($value);
            }
        }
        return $data;
    }

    /**
     * Send message to history chat (group mobile brigade chat)
     */
    public function sendToHistoryChat(string $message, int $threadId = self::DEFAULT_THREAD_ID): ServerResponse
    {
        $data = [
            'chat_id' => self::HISTORY_CHAT_ID,
            'message_thread_id' => $threadId,
            'text' => $message,
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML',
        ];

        return Request::sendMessage($data);
    }
    
    /**
     * Send message to history chat based on user's functional direction
     */
    public function sendToHistoryChatByFunctionalDirection(string $message, string $functionalDirection): ServerResponse
    {
        $groupAndThread = $this->getGroupAndThreadByFunctionalDirection($functionalDirection);
        
        return Request::sendMessage([
            'chat_id' => $groupAndThread['group_id'],
            'text' => $message,
            'parse_mode' => 'HTML',
            'message_thread_id' => $groupAndThread['thread_id']
        ]);
    }
    
    /**
     * Send message to specific group with thread ID based on user's functional direction
     */
    public function sendMessageToGroupWithUserThread(string $groupId, string $message, string $functionalDirection, ?array $keyboard = null): ServerResponse
    {
        // Initialize bot if needed for standalone execution
        $this->initializeBotIfNeeded();
        
        // Get thread ID based on user's functional direction
        $groupAndThread = $this->getGroupAndThreadByFunctionalDirection($functionalDirection);
        
        // If the target group matches the functional direction group, use the thread
        $threadId = null;
        if ($groupId == $groupAndThread['group_id']) {
            $threadId = $groupAndThread['thread_id'];
        }
        
        $data = [
            'chat_id' => $groupId,
            'text' => $message,
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML',
        ];
        
        // Add thread ID if applicable
        if ($threadId) {
            $data['message_thread_id'] = $threadId;
        }
        
        // Add keyboard if provided
        if ($keyboard) {
            $keyboard = $this->ensureUtf8Encoding($keyboard);
            $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE);
        }
        
        return Request::sendMessage($data);
    }
    
    /**
     * Get group and thread ID based on functional direction
     */
    private function getGroupAndThreadByFunctionalDirection(string $functionalDirection): array
    {
        switch ($functionalDirection) {
            case 'Мобильная бригада':
                return [
                    'group_id' => self::MOBILE_BRIGADE_GROUP_ID,
                    'thread_id' => self::MOBILE_BRIGADE_THREAD_ID
                ];
            case 'Пусконаладка':
                return [
                    'group_id' => self::COMMISSIONING_GROUP_ID,
                    'thread_id' => self::COMMISSIONING_THREAD_ID
                ];
            case 'Шеф-монтаж':
                return [
                    'group_id' => self::SUPERVISION_GROUP_ID,
                    'thread_id' => self::SUPERVISION_THREAD_ID
                ];
            default:
                return [
                    'group_id' => self::MOBILE_BRIGADE_GROUP_ID,
                    'thread_id' => self::MOBILE_BRIGADE_THREAD_ID
                ]; // Default fallback
        }
    }

    /**
     * Send document to chat
     */
    public function sendDocument(string $chatId, string $filePath): ServerResponse
    {
        return Request::sendDocument([
            'chat_id' => $chatId,
            'document' => Request::encodeFile($filePath),
        ]);
    }

    /**
     * Create inline keyboard for project selection
     * Uses user's personal project list if available, otherwise shows all projects
     */
    public function createProjectSelectionKeyboard(?int $userId = null): array
    {
        $projects = [];
        
        // If userId is provided, try to get their personal project list first
        if ($userId !== null) {
            try {
                $databaseService = new DatabaseService();
                $userData = $databaseService->getUserData($userId);
                $personalProjects = $userData['report_projects'] ?? [];
                
                error_log("MessageService: createProjectSelectionKeyboard for user {$userId}");
                error_log("MessageService: user's personal projects: " . json_encode($personalProjects));
                
                // Use personal projects if they exist
                if (!empty($personalProjects)) {
                    $projects = $personalProjects;
                    error_log("MessageService: using personal project list with " . count($projects) . " projects");
                } else {
                    error_log("MessageService: user has no personal projects, falling back to all projects");
                }
            } catch (Exception $e) {
                error_log("MessageService: Error getting personal projects: " . $e->getMessage());
            }
        }
        
        // Fallback to all projects if no personal projects or no userId provided
        if (empty($projects)) {
            $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
            $projects = $projectSelector->getAllProjects();
            error_log("MessageService: using all projects list with " . count($projects) . " projects");
        }
        
        $keyboard = [];
        $row = [];
        
        foreach ($projects as $index => $project) {
            $row[] = ['text' => $project, 'callback_data' => "category_Табель_ШифрПроекта_{$project}"];
            
            // Каждые 4 кнопки создаем новый ряд
            if (count($row) == 4 || $index == count($projects) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        error_log("MessageService: created project keyboard with " . count($keyboard) . " rows");
        return $keyboard;
    }

    /**
     * Create inline keyboard for work type selection (main tabel menu)
     */
    public function createWorkTypeKeyboard(): array
    {
        return [
            [
                ['text' => '💼 Мобильная бригада', 'callback_data' => 'category_Табель_МобБриг']
            ],
            [
                ['text' => '🔧 Отдел ШМР', 'callback_data' => 'category_Табель_Шеф_монтаж']
            ],
            [
                ['text' => '⚙️ Отдел ПНР', 'callback_data' => 'category_Табель_Пусконаладка']
            ],
            [
                ['text' => '📊 Текущая деятельность', 'callback_data' => 'category_Табель_Текущая']
            ]
        ];
    }

    /**
     * Create main menu keyboard based on user access level
     */
    public function createMainMenuKeyboard(int $userId): array
    {
        // Initialize AdminService to check user access level
        $adminService = new AdminService($this);
        
        // Get user access level
        $userAccessLevel = $adminService->getUserAccessLevel($userId);
        
        // Create base menu
        $keyboard = [
            [
                ['text' => '📊 Табель', 'callback_data' => 'category_Табель']
            ],
            [
                ['text' => '👤 Профиль', 'callback_data' => 'category_Профиль']
            ]
        ];
        
        // Add projects menu for all registered users (level 1+)
        if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_USER) {
            $keyboard[] = [
                ['text' => '📁 Проекты', 'callback_data' => 'category_Проекты']
            ];
        }
        
        // Add supervisor menu for supervisors and above
        if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
            $keyboard[] = [
                ['text' => '📋 Супервайзер', 'callback_data' => 'category_Супервайзер']
            ];
        }
        
        // Add admin menu for admins and above
        if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_ADMIN) {
            $keyboard[] = [
                ['text' => '⚙️ Админка', 'callback_data' => 'category_Админка']
            ];
        }
        
        // Add help menu
        $keyboard[] = [
            ['text' => '❓ Помощь', 'callback_data' => 'category_Помощь']
        ];
        
        return $keyboard;
    }

    /**
     * Create main menu reply keyboard (persistent menu)
     */
    public function createMainMenuReplyKeyboard(): array
    {
        return [
            [
                ['text' => '📊 Табель'], 
                ['text' => '❓ Помощь']
            ],
            [
                ['text' => '👤 Профиль'], 
                ['text' => '📁 Проекты']
            ],
            [
                ['text' => '⚙️ Админка']
            ]
        ];
    }

    /**
     * Create main menu reply keyboard (persistent menu) for guests
     */
    public function createGuestMainMenuReplyKeyboard(): array
    {
        return [
            [
                ['text' => '👤 Профиль'], 
                ['text' => '❓ Помощь']
            ]
        ];
    }

    /**
     * Create inline keyboard for mobile brigade activities
     */
    public function createMobileBrigadeKeyboard(): array
    {
        error_log("MessageService: createMobileBrigadeKeyboard called");
        $keyboard = [
            [
                ['text' => '🚗 Подготовка к служебной поездке', 'callback_data' => 'category_Табель_Подготовка']
            ],
            [
                ['text' => '🚗 Дорога', 'callback_data' => 'category_Табель_Дорога']
            ],
            [
                ['text' => '📋 Инструктаж', 'callback_data' => 'category_Табель_Инструктаж']
            ],
            [
                ['text' => '⚡ Электромонтажные работы', 'callback_data' => 'category_Табель_Электромонтажные']
            ],
            [
                ['text' => '🔧 Слесарные работы', 'callback_data' => 'category_Табель_Слесарные']
            ],
            [
                ['text' => '🔥 Сварочные работы', 'callback_data' => 'category_Табель_Сварочные']
            ],
            [
                ['text' => '🧱 Теплоизоляция', 'callback_data' => 'category_Табель_Теплоизоляция']
            ],
            [
                ['text' => '📂 Сдача отчетных документов', 'callback_data' => 'category_Табель_Документы']
            ],
            [
                ['text' => '💰 Сдача авансового отчета', 'callback_data' => 'category_Табель_АвансовыйОтчет']
            ],
            [
                ['text' => '⏸️ Простой', 'callback_data' => 'category_Табель_Простой']
            ],
            [
                ['text' => '🛡️ Гарантия', 'callback_data' => 'category_Табель_Гарантия']
            ],
            [
                ['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']
            ]
        ];
        error_log("MessageService: createMobileBrigadeKeyboard returning keyboard with " . count($keyboard) . " rows");
        foreach ($keyboard as $index => $row) {
            error_log("MessageService: createMobileBrigadeKeyboard row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE));
        }
        return $keyboard;
    }

    /**
     * Create inline keyboard for supervision (Шеф-монтаж) activities
     */
    public function createSupervisionKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Шеф-монтажные работы', 'callback_data' => 'category_Табель_ШМР']
            ],
            [
                ['text' => '📂 Проектная деятельность', 'callback_data' => 'cat_ТабПроектнаяДеят_ШМР']
            ],
            [
                ['text' => '🔧 Устранение замечаний РК', 'callback_data' => 'category_Табель_ШМ_УЗ_РК']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'category_Табель_ШМ_РабГарантии']
            ],
            [
                ['text' => '⏸️ Простой', 'callback_data' => 'category_Табель_ШМ_Простой']
            ],
            [
                ['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']
            ]
        ];
    }

    /**
     * Create inline keyboard for supervision works (Шеф-монтажные работы) sub-activities
     */
    public function createSupervisionWorksKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Подготовительные работы', 'callback_data' => 'cat_supervision_prep']
            ],
            [
                ['text' => '🏗️ Монтаж блоков', 'callback_data' => 'cat_ШМР_МонтБлок']
            ],
            [
                ['text' => '🏢 Сборка здания', 'callback_data' => 'cat_ШМР_СборЗдан']
            ],
            [
                ['text' => '⚙️ Внутриблочный монтаж', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ],
            [
                ['text' => '📋 Подписание акта ШМР', 'callback_data' => 'cat_ШМР_ПодпАкт']
            ],
            [
                ['text' => '🚗 Подготовка к служебной поездке', 'callback_data' => 'cat_ШМР_ПодгПоезд']
            ],
            [
                ['text' => '🛣️ Дорога', 'callback_data' => 'cat_ШМР_Дорога']
            ],
            [
                ['text' => '📋 Инструктаж', 'callback_data' => 'cat_ШМР_Инстр']
            ],
            [
                ['text' => '💰 Сдача авансового отчета', 'callback_data' => 'cat_ШМР_СдачаОтч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for preparatory works (Подготовительные работы)
     */
    public function createPreparatoryWorksKeyboard(): array
    {
        return [
            [
                ['text' => '🔍 Входной контроль', 'callback_data' => 'cat_ШМР_ВхКонтр']
            ],
            [
                ['text' => '🚚 Завоз оборудования на площадку', 'callback_data' => 'cat_ШМР_ЗавОбор']
            ],
            [
                ['text' => '🏗️ Проверка монтажной площадки', 'callback_data' => 'cat_ШМР_АналФунд']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for TKP preparation subtypes
     */
    public function createTKPSubtypesKeyboard(): array
    {
        return [
            [
                ['text' => '🏢 БН ОПНВГ', 'callback_data' => 'category_Табель_ПодготовкаТКП_ОПНВГ']
            ],
            [
                ['text' => '🏢 БН НКО', 'callback_data' => 'category_Табель_ПодготовкаТКП_НКО']
            ],
            [
                ['text' => '🏢 БН МСО', 'callback_data' => 'category_Табель_ПодготовкаТКП_МСО']
            ],
            [
                ['text' => '🏢 БН АСН', 'callback_data' => 'category_Табель_ПодготовкаТКП_АСН']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Текущая_Назад']
            ]
        ];
    }

    /**
     * Create warranty equipment selection keyboard
     */
    public function createWarrantyEquipmentKeyboard(string $context = 'commissioning'): array
    {
        // Determine the correct back button based on context
        $backButtonCallback = $context === 'supervision' 
            ? 'category_Табель_ШМ_Back' 
            : 'category_Табель_Пусконаладка_Back';
            
        return [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'cat_Гарантии_ЭлектрОб']
            ],
            [
                ['text' => '📎 Средства измерений', 'callback_data' => 'cat_Гарантии_СрИзм']
            ],
            [
                ['text' => '💧 Насосное оборудование', 'callback_data' => 'cat_Гарантии_НасОб']
            ],
            [
                ['text' => '🔧 Трубопроводная арматура', 'callback_data' => 'cat_Гарантии_ТрубАрм']
            ],
            [
                ['text' => '⚙️ Исполнительные механизмы', 'callback_data' => 'cat_Гарантии_ИспМех']
            ],
            [
                ['text' => '💻 Контроллерное оборудование', 'callback_data' => 'cat_Гарантии_КонтрОб']
            ],
            [
                ['text' => '📦 Прочее оборудование', 'callback_data' => 'cat_Гарантии_Прочее']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => $backButtonCallback]
            ]
        ];
    }

    /**
     * Create inline keyboard for functional direction selection
     */
    public function createFunctionalDirectionKeyboard(): array
    {
        return [
            [
                ['text' => '💼 Мобильная бригада', 'callback_data' => 'category_Профиль_ФН_МобильнаяБригада']
            ],
            [
                ['text' => '⚙️ Пусконаладка', 'callback_data' => 'category_Профиль_ФН_Пусконаладка']
            ],
            [
                ['text' => '🔧 Шеф-монтаж', 'callback_data' => 'category_Профиль_ФН_ШефМонтаж']
            ]
        ];
    }

    /**
     * Create inline keyboard for commissioning (Пусконаладка) activities
     */
    public function createCommissioningKeyboard(): array
    {
        return [
            [
                ['text' => '⚙️ Пусконаладочные работы', 'callback_data' => 'cat_ТабПНРаботы']
            ],
            [
                ['text' => '📂 Проектная деятельность', 'callback_data' => 'cat_ТабПроектнаяДеят']
            ],
            [
                ['text' => '🔧 Устранение замечаний РК', 'callback_data' => 'cat_ТабУстрЗамРК']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'cat_ТабГарантии']
            ],
            [
                ['text' => '⏸️ Простой', 'callback_data' => 'category_Табель_Простой']
            ],
            [
                ['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for commissioning works selection
     */
    public function createCommissioningWorksKeyboard(): array
    {
        // Original commissioning works activities as requested by user
        $keyboard = [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'category_Табель_Электрооборудование']
            ],
            [
                ['text' => '🤖 Автоматизация', 'callback_data' => 'cat_ТабАвтом']
            ],
            [
                ['text' => '🔥 Пожарная сигнализация', 'callback_data' => 'cat_ТабПожСигн']
            ],
            [
                ['text' => '🛡️ Охранная сигнализация', 'callback_data' => 'cat_ТабОхрСигн']
            ],
            [
                ['text' => '❄️ Отопление и вентиляция', 'callback_data' => 'cat_ТабВентОтопл']
            ],
            [
                ['text' => '⚙️ Технологическое оборудование', 'callback_data' => 'cat_ТабТехОб']
            ],
            [          
                ['text' => '📏 Метрология', 'callback_data' => 'cat_ТабМетрол']
            ],
            [
                ['text' => '📊 Режимная наладка', 'callback_data' => 'cat_ТабРежНал']
            ],
            [
                ['text' => '🔄 Комплексное опробование', 'callback_data' => 'cat_ТабКомплОпр']
            ],
            [
                ['text' => '🚗 Подготовка к служебной поездке', 'callback_data' => 'cat_ТабПодгПоезд']
            ],
            [
                ['text' => '🛣️ Дорога', 'callback_data' => 'cat_ТабДорога']
            ],
            [
                ['text' => '📋 Инструктаж', 'callback_data' => 'cat_ТабИнстр']
            ],
            [
                ['text' => '💰 Сдача авансового отчета', 'callback_data' => 'cat_ТабСдачаОтч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
            ]
        ];
        
        error_log("MessageService: createCommissioningWorksKeyboard returning keyboard with " . count($keyboard) . " rows");
        foreach ($keyboard as $index => $row) {
            error_log("MessageService: createCommissioningWorksKeyboard row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE));
        }
        
        return $keyboard;
    }

    /**
     * Create inline keyboard for commissioning works selection with mobile brigade activities
     */
    public function createCommissioningWorksKeyboardWithMobileActivities(): array
    {
        return [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'category_Табель_Электрооборудование']
            ],
            [
                ['text' => '🤖 Автоматизация', 'callback_data' => 'category_Табель_Автоматизация']
            ],
            [
                ['text' => '🔥 Пожарная сигнализация', 'callback_data' => 'category_Табель_ПожарнаяСигнализация']
            ],
            [
                ['text' => '🛡️ Охранная сигнализация', 'callback_data' => 'category_Табель_ОхраннаяСигнализация']
            ],
            [
                ['text' => '❄️ Вентиляция и отопление', 'callback_data' => 'cat_ТабВентОтопл']
            ],

            [
                ['text' => '🔄 Комплексное опробование', 'callback_data' => 'category_Табель_КомплОпробование']
            ],
            [
                ['text' => '📊 Режимная наладка', 'callback_data' => 'category_Табель_РежимнаяНаладка']
            ],
            [
                ['text' => '🚗 Подготовка к служебной поездке', 'callback_data' => 'category_Табель_Подготовка']
            ],
            [
                ['text' => '🚗 Дорога', 'callback_data' => 'category_Табель_Дорога']
            ],
            [
                ['text' => '📋 Инструктаж', 'callback_data' => 'category_Табель_Инструктаж']
            ],
            [
                ['text' => '💰 Сдача авансового отчета', 'callback_data' => 'category_Табель_АвансовыйОтчет']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for elimination of remarks RK
     */
    public function createEliminationRemarksRKKeyboard(string $context = 'commissioning'): array
    {
        // Determine the correct back button based on context
        $backButtonCallback = $context === 'supervision' 
            ? 'category_Табель_ШМ_Back' 
            : 'category_Табель_Пусконаладка_Back';
            
        return [
            [
                ['text' => '🔌 Раздел ЭС', 'callback_data' => 'category_Табель_УстрЗам_ЭС']
            ],
            [
                ['text' => '🤖 Раздел АТХ', 'callback_data' => 'category_Табель_УстрЗам_АТХ']
            ],
            [
                ['text' => '❄️ Раздел ОВКВ', 'callback_data' => 'category_Табель_УстрЗам_ОВКВ']
            ],
            [
                ['text' => '🔥 Раздел ПС/ОС', 'callback_data' => 'category_Табель_УстрЗам_ПСОС']
            ],
            [
                ['text' => '⚙️ Раздел ТХ', 'callback_data' => 'category_Табель_УстрЗам_ТХ']
            ],
            [
                ['text' => '📦 Прочее оборудование', 'callback_data' => 'category_Табель_УстрЗам_Прочее']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => $backButtonCallback]
            ]
        ];
    }

    /**
     * Create inline keyboard for warranty works
     */
    public function createWarrantyWorksKeyboard(): array
    {
        // Warranty works keyboard with short callback format
        $keyboard = [
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'cat_ТабЭлОб'],
                ['text' => '🔧 ТОиР', 'callback_data' => 'category_Табель_Тоир']
            ],
            [
                ['text' => '🛠 Ремонт', 'callback_data' => 'category_Табель_Ремонт'],
                ['text' => '📋 Работы по графику', 'callback_data' => 'category_Табель_РаботыПоГрафику']
            ],
            [
                ['text' => '📝 Формирование ИД', 'callback_data' => 'category_Табель_ФормированиеИД'],
                ['text' => '🔧 Доп. работы', 'callback_data' => 'category_Табель_ДопРаботы']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
        
        error_log("MessageService: createWarrantyWorksKeyboard returning keyboard with " . count($keyboard) . " rows");
        foreach ($keyboard as $index => $row) {
            error_log("MessageService: createWarrantyWorksKeyboard returning keyboard with " . count($keyboard) . " rows");
        }
        
        return $keyboard;
    }

    /**
     * Create inline keyboard for project activities (Проектная деятельность)
     */
    public function createProjectActivityKeyboard(): array
    {
        return [
            [
                ['text' => '🏢 Участие в ЗПИ', 'callback_data' => 'category_Табель_УчастиеВЗПИ']
            ],
            [
                ['text' => '📝 Разработка программы работ', 'callback_data' => 'category_Табель_РазрПрограммы']
            ],
            [
                ['text' => '📅 Разработка графика работ', 'callback_data' => 'category_Табель_РазрГрафика']
            ],
            [
                ['text' => '📦 Организация допоставки', 'callback_data' => 'category_Табель_ОргДопоставки']
            ],
            [
                ['text' => '🏭 Участие в ОПИ', 'callback_data' => 'category_Табель_УчастиеВОПИ']
            ],
            [
                ['text' => '🔧 Шеф-наладочные работы', 'callback_data' => 'category_Табель_ШефНаладочныеРаботы']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабПроектнаяДеят_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Пусконаладка_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for current activities (Текущая деятельность)
     */
    public function createCurrentActivityKeyboard(): array
    {
        return [
            [
                ['text' => '🩺 Медицинский осмотр', 'callback_data' => 'category_Табель_МедОсмотр']
            ],
            [
                ['text' => '🎯 Аттестация', 'callback_data' => 'category_Табель_Аттестация']
            ],
            [
                ['text' => '📋 Подготовка материала к ТКП', 'callback_data' => 'category_Табель_ПодготовкаТКП']
            ],
            [
                ['text' => '💼 Общая деятельность', 'callback_data' => 'category_Табель_Общая']
            ],
            [
                ['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']
            ]
        ];
    }

    /**
     * Create inline keyboard for project activity selection
     */
    /**
     * Create inline keyboard for supervision project activities (Проектная деятельность ШМР)
     */
    public function createSupervisionProjectActivityKeyboard(): array
    {
        return [
            [
                ['text' => '🏢 Участие в ЗПИ', 'callback_data' => 'category_Табель_УчастиеВЗПИ']
            ],
            [
                ['text' => '📝 Разработка программы работ', 'callback_data' => 'category_Табель_РазрПрограммы']
            ],
            [
                ['text' => '📅 Разработка графика работ', 'callback_data' => 'category_Табель_РазрГрафика']
            ],
            [
                ['text' => '📦 Организация допоставки', 'callback_data' => 'category_Табель_ОргДопоставки']
            ],
            [
                ['text' => '🏭 Участие в ОПИ', 'callback_data' => 'category_Табель_УчастиеВОПИ']
            ],
            [
                ['text' => '👷 Исполнение обязанностей мастера', 'callback_data' => 'category_Табель_ИспОбМаст']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабПроектнаяДеят_Проч_ШМР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ШМ_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for electrical equipment selection
     */
    public function createElectricalEquipmentKeyboard(): array
    {
        $keyboard = [
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
        
        error_log("MessageService: createElectricalEquipmentKeyboard returning keyboard with " . count($keyboard) . " rows");
        foreach ($keyboard as $index => $row) {
            error_log("MessageService: createElectricalEquipmentKeyboard row {$index}: " . json_encode($row, JSON_UNESCAPED_UNICODE));
        }
        
        return $keyboard;
    }

    /**
     * Create inline keyboard for additional works - electrical equipment
     */
    public function createAdditionalWorksElectricalKeyboard(): array
    {
        return [
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабЭлОб_ИзмСхПд']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабЭлОб_МонтКбЛн']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'cat_ТабЭлОб_РаскКбЛн']
            ],
            [
                ['text' => '⚙️ Монтаж оборудования', 'callback_data' => 'cat_ТабЭлОб_МонтОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабЭлОб_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_Электрооборудование']
            ]
        ];
    }



    /**
     * Create inline keyboard for heating and ventilation equipment selection
     */
    public function createHeatingVentilationEquipmentKeyboard(): array
    {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабВентОтопл_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабВентОтопл_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабВентОтопл_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабВентОтопл_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }

    /**
     * Create inline keyboard for additional works - heating and ventilation equipment
     */
    public function createAdditionalWorksHeatingVentilationKeyboard(): array
    {
        return [
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабВентОтопл_ИзмСх']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабВентОтопл_МонтКб']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'cat_ТабВентОтопл_РаскКб']
            ],
            [
                ['text' => '🖥️ Монтаж оборудования', 'callback_data' => 'cat_ТабВентОтопл_МонтАСУ']
            ],
            [
                ['text' => '📡 Удаленный ПНР ПО', 'callback_data' => 'cat_ТабВентОтопл_УдалПО']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабВентОтопл_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабВентОтопл']
            ]
        ];
    }



    /**
     * Create inline keyboard for automation equipment selection
     */
    public function createAutomationEquipmentKeyboard(): array
    {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабАвтом_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабАвтом_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабАвтом_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабАвтом_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }

    /**
     * Create inline keyboard for fire alarm equipment selection
     */
    public function createFireAlarmEquipmentKeyboard(): array
    {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабПожСигн_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабПожСигн_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабПожСигн_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабПожСигн_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }

    /**
     * Create inline keyboard for security alarm equipment selection
     */
    public function createSecurityAlarmEquipmentKeyboard(): array
    {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабОхрСиг_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабОхрСиг_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабОхрСиг_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабОхрСиг_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }

    /**
     * Create inline keyboard for additional works - automation equipment
     */
    public function createAdditionalWorksAutomationKeyboard(): array
    {
        return [
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабАвтом_ИзмСх']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабАвтом_МонтКб']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'cat_ТабАвтом_РаскКб']
            ],
            [
                ['text' => '🖥️ Монтаж оборудования', 'callback_data' => 'cat_ТабАвтом_МонтАСУ']
            ],
            [
                ['text' => '📡 Удаленный ПНР ПО', 'callback_data' => 'cat_ТабАвтом_УдалПО']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабАвтом_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабАвтом']
            ]
        ];
    }

    /**
     * Create inline keyboard for additional works - fire alarm equipment
     */
    public function createAdditionalWorksFireAlarmKeyboard(): array
    {
        return [
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабПожСигн_ИзмСх']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабПожСигн_МонтКб']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'cat_ТабПожСигн_РаскКб']
            ],
            [
                ['text' => '⚙️ Монтаж оборудования', 'callback_data' => 'cat_ТабПожСигн_МонтОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабПожСигн_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПожСигн']
            ]
        ];
    }

    /**
     * Create inline keyboard for additional works - security alarm equipment
     */
    public function createAdditionalWorksSecurityAlarmKeyboard(): array
    {
        return [
            [
                ['text' => '🔀 Изменение схем подключения', 'callback_data' => 'cat_ТабОхрСиг_ИзмСх']
            ],
            [
                ['text' => '🔌 Монтаж кабельных линий', 'callback_data' => 'cat_ТабОхрСиг_МонтКб']
            ],
            [
                ['text' => '📊 Расключение кабельных линий', 'callback_data' => 'cat_ТабОхрСиг_РаскКб']
            ],
            [
                ['text' => '⚙️ Монтаж оборудования', 'callback_data' => 'cat_ТабОхрСиг_МонтОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабОхрСиг_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабОхрСигн']
            ]
        ];
    }

    /**
    /**
     * Create inline keyboard for technological equipment selection
     */
    public function createTechnologicalEquipmentKeyboard(): array
    {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабТехОб_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабТехОб_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабТехОб_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабТехОб_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }

    /**
     * Create inline keyboard for additional works - technological equipment
     */
    public function createAdditionalWorksTechnologicalKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Демонтаж/ревизия арматуры', 'callback_data' => 'cat_ТабТехОб_ДемАрм']
            ],
            [
                ['text' => '⚙️ Демонтаж/ревизия техн. оборудования', 'callback_data' => 'cat_ТабТехОб_ДемОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабТехОб_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабТехОб']
            ]
        ];
    }

    /**
     * Create inline keyboard for metrology activities
     */
    public function createMetrologyKeyboard(): array
    {
        return [
            [
                ['text' => '📅 Работы по графику', 'callback_data' => 'cat_ТабМетрол_РабПоГр']
            ],
            [
                ['text' => '📄 Формирование ИД', 'callback_data' => 'cat_ТабМетрол_ФормИД']
            ],
            [
                ['text' => '🛠️ Дополнительные работы', 'callback_data' => 'cat_ТабМетрол_ДопРаб']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ТабМетрол_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабПНРаботы']
            ]
        ];
    }

    /**
     * Create inline keyboard for metrology additional works
     */
    public function createAdditionalWorksMetrologyKeyboard(): array
    {
        return [
            [
                ['text' => '⚙️ Изменение конфигурации СИ', 'callback_data' => 'cat_ТабМетрол_ИзмКонф']
            ],
            [
                ['text' => '🔍 Калибровка/поверка оборудования', 'callback_data' => 'cat_ТабМетрол_КалПов']
            ],
            [
                ['text' => '📈 КМХ СИ', 'callback_data' => 'cat_ТабМетрол_КМХ']
            ],
            [
                ['text' => '🔧 Коррекция характеристик СИ', 'callback_data' => 'cat_ТабМетрол_КоррХар']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ТабМетрол_Проч']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ТабМетрол']
            ]
        ];
    }

    /**
     * Create inline keyboard for date selection
     */
    public function createDateSelectionKeyboard(string $prefix = 'category_Табель'): array
    {
        return [
            [
                ['text' => '📅 Сегодня', 'callback_data' => "{$prefix}_Сегодня"]
            ],
            [
                ['text' => '📆 Вчера', 'callback_data' => "{$prefix}_Вчера"]
            ],
            [
                ['text' => '📆 Позавчера', 'callback_data' => "{$prefix}_Позавчера"]
            ],
            [
                ['text' => '📆 Другая дата', 'callback_data' => "{$prefix}_ДругаяДата"]
            ]
        ];
    }

    /**
     * Create inline keyboard for confirmation
     */
    public function createConfirmationKeyboard(string $confirmCallbackData): array
    {
        return [
            [
                ['text' => '✅ Да', 'callback_data' => $confirmCallbackData],
                ['text' => '❌ Нет', 'callback_data' => 'category_Табель']
            ]
        ];
    }

    /**
     * Create inline keyboard for access request
     */
    public function createAccessRequestKeyboard(): array
    {
        return [
            [
                ['text' => '🔐 Запросить доступ', 'callback_data' => 'category_Админка_ЗапросДоступ']
            ]
        ];
    }

    /**
     * Create inline keyboard for project management
     */
    public function createProjectManagementKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Список проектов', 'callback_data' => 'category_Админка_Проекты_Список']
            ],
            [
                ['text' => '➕ Добавить проект', 'callback_data' => 'category_Админка_Проекты_Добавить']
            ],
            [
                ['text' => '🗑️ Удалить проект', 'callback_data' => 'category_Админка_Проекты_Удалить']
            ],
            [
                ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
            ]
        ];
    }

    /**
     * Create inline keyboard for back to projects
     */
    public function createBackToProjectsKeyboard(): array
    {
        return [
            [
                ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Админка_Проекты']
            ]
        ];
    }

    /**
     * Create inline keyboard for delete project
     */
    public function createDeleteProjectKeyboard(array $projects): array
    {
        $keyboard = [];
        
        foreach ($projects as $project) {
            // Используем сокращенный формат callback_data чтобы не превысить лимит Telegram (64 байта)
            // Формат: Адм_УдПр_{projectCode}
            $shortCallbackData = "category_Адм_УдПр_{$project}";
            $keyboard[] = [
                ['text' => $project, 'callback_data' => $shortCallbackData]
            ];
        }
        
        $keyboard[] = [
            ['text' => '⬅️ Назад к проектам', 'callback_data' => 'category_Админка_Проекты']
        ];
        
        return $keyboard;
    }

    /**
     * Create inline keyboard for input control (Входной контроль)
     */
    public function createInputControlKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работа по графику', 'callback_data' => 'cat_ШМР_ВхКонтр_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_ВхКонтр_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ПодгРаб']
            ]
        ];
    }

    /**
     * Create inline keyboard for equipment delivery (Завоз оборудования на площадку)
     */
    public function createEquipmentDeliveryKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работа по графику', 'callback_data' => 'cat_ШМР_ЗавОбор_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_ЗавОбор_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ПодгРаб']
            ]
        ];
    }

    /**
     * Create inline keyboard for block installation (Монтаж блоков)
     */
    public function createBlockInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '🏗️ Расстановка блоков', 'callback_data' => 'cat_ШМР_РасстБлок']
            ],
            [
                ['text' => '🛠️ Монтаж площадок обслуживания', 'callback_data' => 'cat_ШМР_МонтПлощ']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for block arrangement (Расстановка блоков)
     */
    public function createBlockArrangementKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_РасстБлок_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_РасстБлок_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_МонтБлок']
            ]
        ];
    }

    /**
     * Create inline keyboard for service platform installation (Монтаж площадок обслуживания)
     */
    public function createServicePlatformInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_МонтПлощ_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_МонтПлощ_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_МонтБлок']
            ]
        ];
    }

    /**
     * Create inline keyboard for shelter elements installation (Монтаж элементов укрытия)
     */
    public function createShelterElementsInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_МонтЭлУкр_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_МонтЭлУкр_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_МонтБлок']
            ]
        ];
    }

    /**
     * Create inline keyboard for roof assembly (Сборка кровли)
     */
    public function createRoofAssemblyKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_СборКровл_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_СборКровл_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_МонтБлок']
            ]
        ];
    }

    /**
     * Create inline keyboard for pipeline installation (Монтаж трубопроводов)
     */
    public function createPipelineInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_МонтТруб_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_МонтТруб_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }

    /**
     * Create inline keyboard for hydro/pneumatic testing (Гидро/пневмо испытания)
     */
    public function createHydroPneumaticTestingKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ГидрИсп_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_ГидрИсп_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }

    /**
     * Create inline keyboard for thermal insulation installation (Монтаж теплоизоляции)
     */
    public function createThermalInsulationInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_МонтТеплИз_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_МонтТеплИз_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }

    /**
     * Create inline keyboard for ventilation installation (Монтаж вентиляции)
     */
    public function createVentilationInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_МонтВент_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_МонтВент_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }

    /**
     * Create inline keyboard for building assembly (Сборка здания)
     */
    public function createBuildingAssemblyKeyboard(): array
    {
        return [
            [
                ['text' => '🏠 Монтаж элементов укрытия', 'callback_data' => 'cat_ШМР_МонтЭлУкр']
            ],
            [
                ['text' => '🏠 Сборка кровли', 'callback_data' => 'cat_ШМР_СборКровл']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for intra-block installation (Внутриблочный монтаж)
     */
    public function createIntraBlockInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Монтаж трубопроводов', 'callback_data' => 'cat_ШМР_МонтТруб']
            ],
            [
                ['text' => '💧 Гидро/пневмо испытания', 'callback_data' => 'cat_ШМР_ГидрИсп']
            ],
            [
                ['text' => '🏠 Монтаж теплоизоляции', 'callback_data' => 'cat_ШМР_МонтТеплИз']
            ],
            [
                ['text' => '💨 Монтаж вентиляции', 'callback_data' => 'cat_ШМР_МонтВент']
            ],
            [
                ['text' => '⚙️ Монтаж снятого оборудования', 'callback_data' => 'cat_ШМР_МонтСнятОб']
            ],
            [
                ['text' => '🔌 Монтаж кабельной продукции', 'callback_data' => 'cat_ШМР_МонтКаб']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_Back']
            ]
        ];
    }
}