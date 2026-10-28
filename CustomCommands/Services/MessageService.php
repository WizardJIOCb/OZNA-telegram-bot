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
    
    private const TESTER_GROUP_ID = -1003160935400;
    private const TESTER_THREAD_ID = 2;              // Тестировщик
    
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
            return Request::sendMessage($data);
            
        } catch (Exception $e) {
            error_log("MessageService sendMessage ERROR: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Edit existing message text and keyboard
     * Used to update menus without creating new messages
     */
    public function editMessage(string $chatId, int $messageId, string $text, ?array $keyboard = null, string $parseMode = 'HTML'): ServerResponse
    {
        try {
            error_log("MessageService editMessage START for chatId: {$chatId}, messageId: {$messageId}");
            error_log("MessageService editMessage text: " . mb_substr($text, 0, 100) . "...");
            if ($keyboard) {
                error_log("MessageService editMessage keyboard: " . json_encode($keyboard, JSON_UNESCAPED_UNICODE));
            }
            
            // Initialize bot if needed for standalone execution
            $this->initializeBotIfNeeded();
            
            // Ensure text is properly UTF-8 encoded
            if (function_exists('mb_convert_encoding')) {
                $text = mb_convert_encoding($text, 'UTF-8', 'auto');
            } else {
                $text = utf8_encode($text);
            }
            
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'disable_web_page_preview' => true,
                'parse_mode' => $parseMode,
            ];

            if ($keyboard) {
                // Ensure keyboard is properly UTF-8 encoded
                $keyboard = $this->ensureUtf8Encoding($keyboard);
                
                // For inline keyboards pass the array directly
                $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard], JSON_UNESCAPED_UNICODE);
                error_log("MessageService editMessage keyboard JSON: " . $data['reply_markup']);
            }
            
            error_log("MessageService editMessage data: " . json_encode($data, JSON_UNESCAPED_UNICODE));

            $result = Request::editMessageText($data);
            
            error_log("MessageService editMessage result: " . (is_object($result) && method_exists($result, 'isOk') ? ($result->isOk() ? 'success' : 'failed') : 'unknown'));
            
            if (is_object($result) && method_exists($result, 'isOk') && !$result->isOk()) {
                error_log("MessageService editMessage error: " . $result->getDescription());
                error_log("MessageService editMessage error code: " . $result->getErrorCode());
            }
            
            error_log("MessageService editMessage END");
            return $result;
            
        } catch (Exception $e) {
            error_log("MessageService editMessage EXCEPTION: " . $e->getMessage());
            error_log("MessageService editMessage EXCEPTION trace: " . $e->getTraceAsString());
            throw $e;
        } catch (Error $e) {
            error_log("MessageService editMessage ERROR: " . $e->getMessage());
            error_log("MessageService editMessage ERROR trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Delete message and send a new one
     * This creates a "clean" effect by removing the old message first
     * @param int $delayMs - Delay in milliseconds between delete and send (default: 300ms for visible animation)
     */
    public function deleteAndSendMessage(string $chatId, int $messageId, string $text, ?array $keyboard = null, string $parseMode = 'HTML', int $delayMs = 300): ServerResponse
    {
        try {
            error_log("MessageService: deleteAndSendMessage - deleting message {$messageId} with {$delayMs}ms delay before sending new one");
            
            // First, delete the old message
            $deleteResult = Request::deleteMessage([
                'chat_id' => $chatId,
                'message_id' => $messageId
            ]);
            
            error_log("MessageService: delete result: " . ($deleteResult->isOk() ? 'success' : 'failed'));
            
            // Add delay to make the deletion visible (creates animation effect)
            if ($delayMs > 0) {
                usleep($delayMs * 1000); // Convert milliseconds to microseconds
            }
            
            // Then send the new message
            return $this->sendMessage($chatId, $text, $keyboard, $parseMode);
            
        } catch (Exception $e) {
            error_log("MessageService: deleteAndSendMessage EXCEPTION: " . $e->getMessage());
            // If delete fails, just send the new message
            return $this->sendMessage($chatId, $text, $keyboard, $parseMode);
        }
    }

    /**
     * Smart message sending with multiple strategies
     * @param bool $deleteOld - if true, deletes old message and sends new one (clean effect)
     *                          if false, edits existing message (default)
     */
    public function sendOrEditMessage(string $chatId, string $text, ?array $keyboard = null, ?int $messageId = null, string $parseMode = 'HTML', bool $deleteOld = false): ServerResponse
    {
        if ($messageId !== null) {
            if ($deleteOld) {
                error_log("MessageService: sendOrEditMessage - deleting old and sending new (messageId: {$messageId})");
                return $this->deleteAndSendMessage($chatId, $messageId, $text, $keyboard, $parseMode);
            } else {
                error_log("MessageService: sendOrEditMessage - editing message {$messageId}");
                return $this->editMessage($chatId, $messageId, $text, $keyboard, $parseMode);
            }
        } else {
            error_log("MessageService: sendOrEditMessage - sending new message");
            return $this->sendMessage($chatId, $text, $keyboard, $parseMode);
        }
    }


    /**
     * Send message with reply keyboard
     */
    public function sendMessageWithReplyKeyboard(string $chatId, string $text, ?array $replyKeyboard = null): ServerResponse
    {
        error_log("MessageService: sendMessageWithReplyKeyboard called for chatId: {$chatId}");
        error_log("MessageService: message text: '" . substr($text, 0, 50) . "...'");
        
        if ($replyKeyboard) {
            error_log("MessageService: reply keyboard has " . count($replyKeyboard) . " rows");
            foreach ($replyKeyboard as $rowIndex => $row) {
                $rowText = "";
                foreach ($row as $buttonIndex => $button) {
                    $rowText .= "'" . $button['text'] . "'";
                    if ($buttonIndex < count($row) - 1) $rowText .= ", ";
                }
                error_log("MessageService: reply keyboard row {$rowIndex}: {$rowText}");
            }
        } else {
            error_log("MessageService: no reply keyboard provided");
        }
        
        $data = [
            'chat_id' => $chatId,
            'text' => $this->ensureUtf8Encoding($text),
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML',
        ];

        // Add reply keyboard if provided
        if ($replyKeyboard) {
            $replyKeyboard = $this->ensureUtf8Encoding($replyKeyboard);
            $data['reply_markup'] = json_encode(['keyboard' => $replyKeyboard, 'resize_keyboard' => true], JSON_UNESCAPED_UNICODE);
        }

        return Request::sendMessage($data);
    }

    /**
     * Ensure all strings in keyboard array are UTF-8 encoded
     */
    private function ensureUtf8Encoding($data)
    {
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'auto');
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $data[$key] = mb_convert_encoding($value, 'UTF-8', 'auto');
                } elseif (is_array($value)) {
                    $data[$key] = $this->ensureUtf8Encoding($value);
                }
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
            case 'Тестировщик':
                return [
                    'group_id' => self::TESTER_GROUP_ID,
                    'thread_id' => self::TESTER_THREAD_ID
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
     * 
     * @param int|null $userId User ID to get personal projects for
     * @param bool $usePersonalList If true, use report_projects list; if false, use all projects + pending
     * @return array Keyboard array
     */
    public function createProjectSelectionKeyboard(?int $userId = null, bool $usePersonalList = true): array
    {
        $projects = [];
        $pendingProjects = [];
        
        // If userId is provided, determine which project list to use
        if ($userId !== null) {
            try {
                // ALWAYS get user's pending projects from database
                $databaseService = new DatabaseService();
                $pendingProjectsData = $databaseService->getUserProjectsWithPending($userId);
                
                // Extract pending projects (status = 'pending')
                foreach ($pendingProjectsData as $project) {
                    if ($project['status'] === 'pending') {
                        $pendingProjects[] = $project['project_code'];
                    }
                }
                
                error_log("MessageService: User {$userId} has " . count($pendingProjects) . " pending projects: " . json_encode($pendingProjects));
                
                // For personal report list - use report_projects from user data
                if ($usePersonalList) {
                    $userData = $databaseService->getUserData($userId);
                    $personalProjects = $userData['report_projects'] ?? [];
                    
                    error_log("MessageService: createProjectSelectionKeyboard for user {$userId} (personal list)");
                    error_log("MessageService: user's personal projects: " . json_encode($personalProjects));
                    
                    // Use personal projects if they exist
                    if (!empty($personalProjects)) {
                        $projects = $personalProjects;
                        error_log("MessageService: using personal project list with " . count($projects) . " projects");
                    } else {
                        error_log("MessageService: user has no personal projects, falling back to all projects");
                    }
                } 
                // For tabel/work entries - use all projects + user's pending projects
                else {
                    $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector($databaseService);
                    $projects = $projectSelector->getAllProjectsIncludingPending();
                    
                    error_log("MessageService: createProjectSelectionKeyboard for user {$userId} (all projects including pending)");
                    error_log("MessageService: got " . count($projects) . " projects from database");
                }
                
            } catch (Exception $e) {
                error_log("MessageService: Error getting user projects: " . $e->getMessage());
            }
        }
        
        // Fallback to all projects if no userId provided or no projects found
        if (empty($projects)) {
            // Need DatabaseService for getAllProjectsIncludingPending
            if (!isset($databaseService)) {
                $databaseService = new DatabaseService();
            }
            $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector($databaseService);
            $projects = $projectSelector->getAllProjectsIncludingPending();
            error_log("MessageService: using all projects list (including pending) with " . count($projects) . " projects");
        }
        
        // ALWAYS add user's pending projects to the list (if any)
        if (!empty($pendingProjects)) {
            foreach ($pendingProjects as $pendingProject) {
                if (!in_array($pendingProject, $projects)) {
                    $projects[] = $pendingProject;
                    error_log("MessageService: Added pending project to list: {$pendingProject}");
                }
            }
            error_log("MessageService: Total projects after adding pending: " . count($projects));
        }
        
        // Apply intelligent sorting to final project list
        if (!empty($projects)) {
            // Need to create DatabaseService instance if not already available
            if (!isset($databaseService)) {
                $databaseService = new DatabaseService();
            }
            $projects = $databaseService->sortProjectsIntelligently($projects);
            error_log("MessageService: Applied intelligent sorting to " . count($projects) . " projects");
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
     * Create inline keyboard for project selection with pagination
     * 
     * @param int|null $userId User ID to get personal projects for
     * @param bool $usePersonalList If true, use report_projects list; if false, use all projects + pending
     * @param int $page Page number for pagination
     * @param int $perPage Number of projects per page
     * @return array Keyboard array
     */
    public function createProjectSelectionKeyboardWithPagination(?int $userId = null, bool $usePersonalList = true, int $page = 1, int $perPage = 33): array
    {
        $projects = [];
        $pendingProjects = [];
        
        // If userId is provided, determine which project list to use
        if ($userId !== null) {
            try {
                // ALWAYS get user's pending projects from database
                $databaseService = new DatabaseService();
                $pendingProjectsData = $databaseService->getUserProjectsWithPending($userId);
                
                // Extract pending projects (status = 'pending')
                foreach ($pendingProjectsData as $project) {
                    if ($project['status'] === 'pending') {
                        $pendingProjects[] = $project['project_code'];
                    }
                }
                
                error_log("MessageService: User {$userId} has " . count($pendingProjects) . " pending projects: " . json_encode($pendingProjects));
                
                // For personal report list - use report_projects from user data
                if ($usePersonalList) {
                    $userData = $databaseService->getUserData($userId);
                    $personalProjects = $userData['report_projects'] ?? [];
                    
                    error_log("MessageService: createProjectSelectionKeyboardWithPagination for user {$userId} (personal list)");
                    error_log("MessageService: user's personal projects: " . json_encode($personalProjects));
                    
                    // Use personal projects if they exist
                    if (!empty($personalProjects)) {
                        $projects = $personalProjects;
                        error_log("MessageService: using personal project list with " . count($projects) . " projects");
                    } else {
                        error_log("MessageService: user has no personal projects, falling back to all projects");
                    }
                } 
                // For tabel/work entries - use all projects + user's pending projects
                else {
                    $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector($databaseService);
                    $projects = $projectSelector->getAllProjectsIncludingPending();
                    
                    error_log("MessageService: createProjectSelectionKeyboardWithPagination for user {$userId} (all projects including pending)");
                    error_log("MessageService: got " . count($projects) . " projects from database");
                }
                
            } catch (Exception $e) {
                error_log("MessageService: Error getting user projects: " . $e->getMessage());
            }
        }
        
        // Fallback to all projects if no userId provided or no projects found
        if (empty($projects)) {
            // Need DatabaseService for getAllProjectsIncludingPending
            if (!isset($databaseService)) {
                $databaseService = new DatabaseService();
            }
            $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector($databaseService);
            $projects = $projectSelector->getAllProjectsIncludingPending();
            error_log("MessageService: using all projects list (including pending) with " . count($projects) . " projects");
        }
        
        // ALWAYS add user's pending projects to the list (if any)
        if (!empty($pendingProjects)) {
            foreach ($pendingProjects as $pendingProject) {
                if (!in_array($pendingProject, $projects)) {
                    $projects[] = $pendingProject;
                    error_log("MessageService: Added pending project to list: {$pendingProject}");
                }
            }
            error_log("MessageService: Total projects after adding pending: " . count($projects));
        }
        
        // Apply intelligent sorting to final project list
        if (!empty($projects)) {
            // Need to create DatabaseService instance if not already available
            if (!isset($databaseService)) {
                $databaseService = new DatabaseService();
            }
            $projects = $databaseService->sortProjectsIntelligently($projects);
            error_log("MessageService: Applied intelligent sorting to " . count($projects) . " projects");
        }
        
        // Calculate pagination
        $totalProjects = count($projects);
        $totalPages = (int)ceil($totalProjects / $perPage);
        $page = max(1, min($page, $totalPages)); // Ensure page is within valid range
        $offset = ($page - 1) * $perPage;
        
        // Get projects for current page
        $pageProjects = array_slice($projects, $offset, $perPage);
        
        error_log("MessageService: Pagination - Total: {$totalProjects}, Page: {$page}/{$totalPages}, PerPage: {$perPage}, Showing: " . count($pageProjects));
        
        $keyboard = [];
        $row = [];
        
        foreach ($pageProjects as $index => $project) {
            $row[] = ['text' => $project, 'callback_data' => "category_Табель_ШифрПроекта_{$project}"];
            
            // Каждые 4 кнопки создаем новый ряд
            if (count($row) == 4 || $index == count($pageProjects) - 1) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        
        // Add pagination buttons if needed
        if ($totalPages > 1) {
            $paginationRow = [];
            
            // Previous page button
            if ($page > 1) {
                $paginationRow[] = ['text' => '◀️ Назад', 'callback_data' => "tabel_proj_page_" . ($page - 1)];
            }
            
            // Page indicator
            $paginationRow[] = ['text' => "📄 {$page}/{$totalPages}", 'callback_data' => 'noop'];
            
            // Next page button
            if ($page < $totalPages) {
                $paginationRow[] = ['text' => 'Вперед ▶️', 'callback_data' => "tabel_proj_page_" . ($page + 1)];
            }
            
            $keyboard[] = $paginationRow;
        }
        
        // Add "Back to Main Menu" button at the bottom
        $keyboard[] = [
            ['text' => '🏠 Вернуться в главное меню', 'callback_data' => 'main_menu']
        ];
        
        error_log("MessageService: created paginated project keyboard with " . count($keyboard) . " rows");
        return $keyboard;
    }

    /**
     * Create inline keyboard for work type selection (main tabel menu)
     */
    public function createWorkTypeKeyboard(): array
    {
        return [
            [
                ['text' => '👷‍♂️ Мобильная бригада', 'callback_data' => 'category_Табель_МобБриг']
            ],
            [
                ['text' => '🔧 Отдел ШМР', 'callback_data' => 'category_Табель_Шеф_монтаж']
            ],
            [
                ['text' => '⚙️ Отдел ПНР', 'callback_data' => 'category_Табель_Пусконаладка']
            ],
            [
                ['text' => '📊 Текущая деятельность', 'callback_data' => 'category_Табель_Текущая']
            ],
            [
                ['text' => '📋 Отсутствие 🏥🏖', 'callback_data' => 'category_Табель_Отсутствие']
            ]
        ];
    }

    /**
     * Create absence type selection keyboard (Больничный / Отпуск / Донорский день)
     */
    public function createAbsenceTypeKeyboard(): array
    {
        return [
            [
                ['text' => '🏥 Больничный', 'callback_data' => 'category_Табель_Отсутствие_Больничный']
            ],
            [
                ['text' => '🏖 Отпуск', 'callback_data' => 'category_Табель_Отсутствие_Отпуск']
            ],
            [
                ['text' => '🩸 Донорский день', 'callback_data' => 'category_Табель_Отсутствие_Донорский']
            ],
            [
                ['text' => '⬅️ Вернуться в табель', 'callback_data' => 'category_Табель']
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
        
        // UPDATED: Add admin menu for supervisors and above (level 2+)
        // Supervisors can access admin panel but with limited functions (only project moderation)
        if ($userAccessLevel >= DatabaseService::ACCESS_LEVEL_SUPERVISOR) {
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
     * Create main menu reply keyboard (persistent menu) for registered users
     */
    public function createMainMenuReplyKeyboard(int $userAccessLevel = 99): array
    {
        error_log("MessageService: createMainMenuReplyKeyboard called with userAccessLevel: {$userAccessLevel}");
        
        $keyboard = [
            [
                ['text' => '📊 Табель'], 
                ['text' => '❓ Помощь']
            ],
            [
                ['text' => '👤 Профиль'], 
                ['text' => '📁 Проекты']
            ]
        ];
        
        // Add Adminка button only for users with access level 2 or higher (Admin and above)
        if ($userAccessLevel >= 2) {
            error_log("MessageService: adding admin button for userAccessLevel: {$userAccessLevel}");
            $keyboard[] = [
                ['text' => '⚙️ Админка']
            ];
        }
        
        // Add Project Management button for supervisors and above (access level 2+)
        if ($userAccessLevel >= 2) {
            error_log("MessageService: adding project management button for userAccessLevel: {$userAccessLevel}");
            $keyboard[] = [
                ['text' => '🛠️ Управление проектом']
            ];
        }
        
        error_log("MessageService: created reply keyboard with " . count($keyboard) . " rows");
        foreach ($keyboard as $rowIndex => $row) {
            $rowText = "";
            foreach ($row as $buttonIndex => $button) {
                $rowText .= "'" . $button['text'] . "'";
                if ($buttonIndex < count($row) - 1) $rowText .= ", ";
            }
            error_log("MessageService: reply keyboard row {$rowIndex}: {$rowText}");
        }
        
        return $keyboard;
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
            ],
            [
                ['text' => '🔐 Запросить доступ']
            ]
        ];
    }

    /**
     * Create notes reminder keyboard
     * @param string $context - 'ПНР', 'ШМР', or 'Проект'
     */
    public function createNotesReminderKeyboard(string $context): array
    {
        return [
            [
                ['text' => '✍️ Оставить заметку', 'callback_data' => 'notes_create_' . $context]
            ],
            [
                ['text' => '📋 Посмотреть заметки', 'callback_data' => 'notes_view_list']
            ],
            [
                ['text' => '➖ Пропустить', 'callback_data' => 'noop']
            ]
        ];
    }

    /**
     * Create inline keyboard for mobile brigade activities - ENHANCED VERSION
     */
    public function createMobileBrigadeKeyboard(): array
    {
        error_log("MessageService: createMobileBrigadeKeyboard called");
        $keyboard = [
            // ОСНОВНЫЕ ПРОИЗВОДСТВЕННЫЕ РАБОТЫ (в технологическом порядке)
            [
                ['text' => '⚡ Электромонтажные работы', 'callback_data' => 'cat_МобБриг_Электр']
            ],
            [
                ['text' => '🔧 Слесарные работы', 'callback_data' => 'cat_МобБриг_Слес']
            ],
            [
                ['text' => '🔥 Сварочные работы', 'callback_data' => 'cat_МобБриг_Свар']
            ],
            [
                ['text' => '🧥 Работы по теплоизоляции', 'callback_data' => 'cat_МобБриг_Тепло']
            ],
            [
                ['text' => '🖌️ Малярные работы', 'callback_data' => 'cat_МобБриг_Маляр']
            ],
            // СЛУЖЕБНЫЕ АКТИВНОСТИ
            [
                ['text' => '🚗 Подготовка к служебной поездке', 'callback_data' => 'category_Табель_Подготовка']
            ],
            [
                ['text' => '🛣️ Дорога', 'callback_data' => 'category_Табель_Дорога']
            ],
            [
                ['text' => '📋 Инструктаж', 'callback_data' => 'category_Табель_Инструктаж']
            ],
            // АДМИНИСТРАТИВНЫЕ РАБОТЫ
            [
                ['text' => '💰 Сдача авансового отчета', 'callback_data' => 'category_Табель_АвансовыйОтчет']
            ],
            // ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ
            [
                ['text' => '⏸️ Простой', 'callback_data' => 'category_Табель_МобБриг_Простой']
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
                ['text' => '📌 Заметки', 'callback_data' => 'notes_menu_ШМР']
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
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ШефМонт_Back']
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
    public function createWarrantyEquipmentKeyboard(string $context = 'commissioning', array $claims = []): array
    {
        // Determine the correct back button based on context
        $backButtonCallback = $context === 'supervision' 
            ? 'category_Табель_ШМ_Back' 
            : 'category_Табель_Пусконаладка_Back';
            
        $keyboard = [];
        
        // Insert claim buttons if any
        foreach ($claims as $claim) {
            $icon = $claim['status'] === 'pending_moderation' ? '⚠️' : '✅';
            $subject = $claim['subject'] ?? 'Без темы';
            if (mb_strlen($subject) > 38) {
                $subject = mb_substr($subject, 0, 37) . '…';
            }
            $keyboard[] = [
                ['text' => "{$icon} {$subject}", 'callback_data' => 'claim_view_' . $claim['id']]
            ];
        }
        
        $keyboard[] = [
            ['text' => '➕ Добавить рекламацию', 'callback_data' => 'claim_ucreate']
        ];
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => $backButtonCallback]
        ];
        
        return $keyboard;
    }

    /**
     * Create submenu keyboard for Mobile Brigade warranty works
     */
    public function createMobileBrigadeWarrantySubmenuKeyboard(string $tradeBackCallback, array $claims = []): array
    {
        $keyboard = [];
        
        // Insert claim buttons if any
        foreach ($claims as $claim) {
            $icon = $claim['status'] === 'pending_moderation' ? '⚠️' : '✅';
            $subject = $claim['subject'] ?? 'Без темы';
            if (mb_strlen($subject) > 38) {
                $subject = mb_substr($subject, 0, 37) . '…';
            }
            $keyboard[] = [
                ['text' => "{$icon} {$subject}", 'callback_data' => 'claim_view_' . $claim['id']]
            ];
        }
        
        $keyboard[] = [
            ['text' => '➕ Добавить рекламацию', 'callback_data' => 'claim_ucreate']
        ];
        $keyboard[] = [
            ['text' => '⬅️ Назад', 'callback_data' => $tradeBackCallback]
        ];
        
        return $keyboard;
    }

    /**
     * Create inline keyboard for functional direction selection
     */
    public function createFunctionalDirectionKeyboard(): array
    {
        return [
            [
                ['text' => '👷‍♂️ Мобильная бригада', 'callback_data' => 'category_Профиль_ФН_МобильнаяБригада']
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
                ['text' => '⚙️ ИИ на рабочей среде', 'callback_data' => 'cat_ТабРежНал']
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
                ['text' => '📌 Заметки', 'callback_data' => 'notes_menu_ПНР']
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
                ['text' => '⚙️ ИИ на рабочей среде', 'callback_data' => 'category_Табель_РежимнаяНаладка']
            ],
            [
                ['text' => '🚗 Подготовка к служебной поездке', 'callback_data' => 'category_Табель_Подготовка']
            ],
            [
                ['text' => '🛣️ Дорога', 'callback_data' => 'category_Табель_Дорога']
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
                ['text' => '🏢 Участие в ЗПИ', 'callback_data' => 'category_Табель_ЗПИ_Меню']
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
                ['text' => '📌 Заметки', 'callback_data' => 'notes_menu_Проект']
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
                ['text' => '🏢 Участие в ЗПИ', 'callback_data' => 'category_Табель_ЗПИ_Меню_ШМР']
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
                ['text' => '📌 Заметки', 'callback_data' => 'notes_menu_ШМР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'category_Табель_ШМ_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for ZPI sub-activities (Подактивности ЗПИ) - ПНР context (all 5)
     * @param string $backCallback Callback for the back button
     */
    public function createZPISubActivitiesKeyboard(string $backCallback = 'category_Табель_Пусконаладка_ПроектнаяДеятельность'): array
    {
        return [
            [
                ['text' => '🔍 Проверка/наладка шкафов', 'callback_data' => 'category_Табель_ЗПИ_ПровШк']
            ],
            [
                ['text' => '⚙️ Настройка/наладка оборудования', 'callback_data' => 'category_Табель_ЗПИ_НастОб']
            ],
            [
                ['text' => '🔬 Функциональная проверка систем', 'callback_data' => 'category_Табель_ЗПИ_ФункПров']
            ],
            [
                ['text' => '🏭 Контроль производства', 'callback_data' => 'category_Табель_ЗПИ_КонтрПр']
            ],
            [
                ['text' => '✅ Комиссионная приёмка', 'callback_data' => 'category_Табель_ЗПИ_КомисПр']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => $backCallback]
            ]
        ];
    }

    /**
     * Create inline keyboard for ZPI sub-activities - ШМР context (only 2: Контроль производства, Комиссионная приёмка)
     * @param string $backCallback Callback for the back button
     */
    public function createZPISubActivitiesKeyboardSMR(string $backCallback = 'cat_ТабПроектнаяДеят_ШМР'): array
    {
        return [
            [
                ['text' => '🏭 Контроль производства', 'callback_data' => 'category_Табель_ЗПИ_КонтрПр']
            ],
            [
                ['text' => '✅ Комиссионная приёмка', 'callback_data' => 'category_Табель_ЗПИ_КомисПр']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => $backCallback]
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
     * Create submenu keyboard for electrical installation works
     */
    public function createElectricalWorksSubmenuKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Досборка', 'callback_data' => 'cat_МобБриг_Электр_Досб']
            ],
            [
                ['text' => '⚙️ Реализация ТР', 'callback_data' => 'cat_МобБриг_Электр_РеалТР']
            ],
            [
                ['text' => '🔍 Устранение замечаний РК', 'callback_data' => 'cat_МобБриг_Электр_УстрЗам']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'cat_МобБриг_Электр_РабГар']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_МобБриг_Назад']
            ]
        ];
    }

    /**
     * Create submenu keyboard for locksmith works
     */
    public function createLocksmithWorksSubmenuKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Досборка', 'callback_data' => 'cat_МобБриг_Слес_Досб']
            ],
            [
                ['text' => '⚙️ Реализация ТР', 'callback_data' => 'cat_МобБриг_Слес_РеалТР']
            ],
            [
                ['text' => '🔍 Устранение замечаний РК', 'callback_data' => 'cat_МобБриг_Слес_УстрЗам']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'cat_МобБриг_Слес_РабГар']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_МобБриг_Назад']
            ]
        ];
    }

    /**
     * Create submenu keyboard for welding works
     */
    public function createWeldingWorksSubmenuKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Досборка', 'callback_data' => 'cat_МобБриг_Свар_Досб']
            ],
            [
                ['text' => '⚙️ Реализация ТР', 'callback_data' => 'cat_МобБриг_Свар_РеалТР']
            ],
            [
                ['text' => '🔍 Устранение замечаний РК', 'callback_data' => 'cat_МобБриг_Свар_УстрЗам']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'cat_МобБриг_Свар_РабГар']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_МобБриг_Назад']
            ]
        ];
    }

    /**
     * Create submenu keyboard for thermal insulation works
     */
    public function createThermalInsulationWorksSubmenuKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Досборка', 'callback_data' => 'cat_МобБриг_Тепло_Досб']
            ],
            [
                ['text' => '⚙️ Реализация ТР', 'callback_data' => 'cat_МобБриг_Тепло_РеалТР']
            ],
            [
                ['text' => '🔍 Устранение замечаний РК', 'callback_data' => 'cat_МобБриг_Тепло_УстрЗам']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'cat_МобБриг_Тепло_РабГар']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_МобБриг_Назад']
            ]
        ];
    }

    /**
     * Create submenu keyboard for painting works
     */
    public function createPaintingWorksSubmenuKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Досборка', 'callback_data' => 'cat_МобБриг_Маляр_Досб']
            ],
            [
                ['text' => '⚙️ Реализация ТР', 'callback_data' => 'cat_МобБриг_Маляр_РеалТР']
            ],
            [
                ['text' => '🔍 Устранение замечаний РК', 'callback_data' => 'cat_МобБриг_Маляр_УстрЗам']
            ],
            [
                ['text' => '🛡️ Работы по гарантии', 'callback_data' => 'cat_МобБриг_Маляр_РабГар']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_МобБриг_Назад']
            ]
        ];
    }

    /**
     * Create inline keyboard for date selection
     */
    public function createDateSelectionKeyboard(string $prefix = 'category_Табель'): array
    {
        $keyboard = [
            [
                ['text' => '📅 Сегодня', 'callback_data' => "{$prefix}_Сегодня"]
            ],
            [
                ['text' => '📆 Вчера', 'callback_data' => "{$prefix}_Вчера"]
            ],
            [
                ['text' => '📆 Позавчера', 'callback_data' => "{$prefix}_Позавчера"]
            ]
        ];

        // Check if "Другая дата" option is enabled in tabel settings
        if (\Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelManagerHandler::isOtherDateEnabled()) {
            $keyboard[] = [
                ['text' => '📆 Другая дата', 'callback_data' => "{$prefix}_ДругаяДата"]
            ];
        }

        return $keyboard;
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
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ШефМонт_Back']
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
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_МонтПлощ_НеучВОР']
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
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_МонтЭлУкр_НеучВОР']
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
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_СборКровл_НеучВОР']
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
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ШефМонт_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for intra-block installation (Внутриблочный монтаж)
     * Updated with 7 new sub-activities for ШМР (unique, not duplicating ПНР)
     */
    public function createIntraBlockInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '⚙️ Технологическое оборудование', 'callback_data' => 'cat_ШМР_ТехОб']
            ],
            [
                ['text' => '⚡ Электрооборудование', 'callback_data' => 'cat_ШМР_ЭлектрОб']
            ],
            [
                ['text' => '🌡️ Отопление и вентиляция', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ],
            [
                ['text' => '🔥 Пожарная сигнализация', 'callback_data' => 'cat_ШМР_ПожСигн']
            ],
            [
                ['text' => '🛡️ Охранная сигнализация', 'callback_data' => 'cat_ШМР_ОхрСигн']
            ],
            [
                ['text' => '🤖 Автоматизация', 'callback_data' => 'cat_ШМР_Автом']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ШефМонт_Back']
            ]
        ];
    }

    /**
     * Create inline keyboard for ШМР technological equipment sub-activities
     */
    public function createSMRTechnologicalEquipmentKeyboard(): array
    {
        return [
            [
                ['text' => '🔧 Монтаж трубопроводов', 'callback_data' => 'cat_ШМР_МонтТруб']
            ],
            [
                ['text' => '💧 Гидро/пневмо испытания', 'callback_data' => 'cat_ШМР_ГидрИсп']
            ],
            [
                ['text' => '⚙️ Монтаж технологического оборудования', 'callback_data' => 'cat_ШМР_МонтТехОб']
            ],
            [
                ['text' => '🎛️ Монтаж ЗРА', 'callback_data' => 'cat_ШМР_МонтЗРА']
            ],
            [
                ['text' => '🔗 Протяжка соединений', 'callback_data' => 'cat_ШМР_ПротСоед']
            ],
            [
                ['text' => '🧱 Монтаж теплоизоляции', 'callback_data' => 'cat_ШМР_МонтТеплИзол']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ШМР_ТехОб_Проч_РабГр']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_ТехОб_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
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
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_МонтТруб_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ТехОб']
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
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ГидрИсп_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ТехОб']
            ]
        ];
    }

    /**
     * Create inline keyboard for technological equipment installation (Монтаж технологического оборудования)
     */
    public function createTechnologicalEquipmentInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_МонтТехОб_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_МонтТехОб_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ТехОб']
            ]
        ];
    }

    /**
     * Create inline keyboard for ZRA installation (Монтаж ЗРА)
     */
    public function createZRAInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_МонтЗРА_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_МонтЗРА_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ТехОб']
            ]
        ];
    }

    /**
     * Create inline keyboard for connection stretching (Протяжка соединений)
     */
    public function createConnectionStretchingKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ПротСоед_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ПротСоед_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ТехОб']
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
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_МонтТеплИзол_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_МонтТеплИзол_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ТехОб']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Heating and Ventilation (Отопление и вентиляция)
     */
    public function createSMRHeatingVentilationKeyboard(): array
    {
        return [
            [
                ['text' => '🌡️ Монтаж оборудования', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтОб']
            ],
            [
                ['text' => '🌬️ Монтаж воздуховодов/труб', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтВозд']
            ],
            [
                ['text' => '❄️ Монтаж кондиционеров', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтКонд']
            ],
            [
                ['text' => '🔥 Монтаж ИТП', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтИТП']
            ],
            [
                ['text' => '💧 Проведение пневмо/гидро испытаний', 'callback_data' => 'cat_ШМР_ОтоплВент_ПневмГидр']
            ],
            [
                ['text' => '🚰 Монтаж КНС', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтКНС']
            ],
            [
                ['text' => '🔌 Прокладка кабеля', 'callback_data' => 'cat_ШМР_ОтоплВент_ПроклКб']
            ],
            [
                ['text' => '🔗 Подключение кабеля', 'callback_data' => 'cat_ШМР_ОтоплВент_ПодклКб']
            ],
            [
                ['text' => '🏷️ Маркировка оборудования/КЛ', 'callback_data' => 'cat_ШМР_ОтоплВент_МаркОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ШМР_ОтоплВент_Проч']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_ОтоплВент_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Heating/Ventilation Equipment Installation (Монтаж оборудования)
     */
    public function createSMRHVACEquipmentInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтОб_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтОб_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Air Ducts/Pipes Installation (Монтаж воздуховодов/труб)
     */
    public function createSMRAirDuctsPipesInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтВозд_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтВозд_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Air Conditioners Installation (Монтаж кондиционеров)
     */
    public function createSMRAirConditionersInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтКонд_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтКонд_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР ITP Installation (Монтаж ИТП)
     */
    public function createSMRITPInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтИТП_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтИТП_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Pneumo/Hydro Testing (Проведение пневмо/гидро испытаний)
     */
    public function createSMRHVACPneumoHydroTestingKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_ПневмГидр_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_ПневмГидр_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР KNS Installation (Монтаж КНС)
     */
    public function createSMRKNSInstallationKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтКНС_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_МонтКНС_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР HVAC Cable Laying (Прокладка кабеля)
     */
    public function createSMRHVACCableLayingKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_ПроклКб_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_ПроклКб_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР HVAC Cable Connection (Подключение кабеля)
     */
    public function createSMRHVACCableConnectionKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_ПодклКб_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_ПодклКб_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР HVAC Equipment Marking (Маркировка оборудования/КЛ)
     */
    public function createSMRHVACEquipmentMarkingKeyboard(): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => 'cat_ШМР_ОтоплВент_МаркОб_РабГр']
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => 'cat_ШМР_ОтоплВент_МаркОб_НеучВОР']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ОтоплВент']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Electrical Equipment (Электрооборудование)
     */
    public function createSMRElectricalEquipmentKeyboard(): array
    {
        return [
            [
                ['text' => '⚡ Монтаж оборудования', 'callback_data' => 'cat_ШМР_ЭлОб_МонтОб']
            ],
            [
                ['text' => '📦 Монтаж кабеленесущих систем', 'callback_data' => 'cat_ШМР_ЭлОб_МонтКбСист']
            ],
            [
                ['text' => '🔌 Прокладка кабеля', 'callback_data' => 'cat_ШМР_ЭлОб_ПроклКб']
            ],
            [
                ['text' => '🔗 Подключение оборудования', 'callback_data' => 'cat_ШМР_ЭлОб_ПодклОб']
            ],
            [
                ['text' => '🏷️ Маркировка оборудования/КЛ', 'callback_data' => 'cat_ШМР_ЭлОб_МаркОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ШМР_ЭлОб_Проч']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_ЭлОб_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Fire Alarm (Пожарная сигнализация)
     */
    public function createSMRFireAlarmKeyboard(): array
    {
        return [
            [
                ['text' => '🔥 Монтаж оборудования', 'callback_data' => 'cat_ШМР_ПожСигн_МонтОб']
            ],
            [
                ['text' => '📦 Монтаж кабеленесущих систем', 'callback_data' => 'cat_ШМР_ПожСигн_МонтКбСист']
            ],
            [
                ['text' => '🔌 Прокладка кабеля', 'callback_data' => 'cat_ШМР_ПожСигн_ПроклКб']
            ],
            [
                ['text' => '🔗 Подключение оборудования', 'callback_data' => 'cat_ШМР_ПожСигн_ПодклОб']
            ],
            [
                ['text' => '🏷️ Маркировка оборудования/КЛ', 'callback_data' => 'cat_ШМР_ПожСигн_МаркОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ШМР_ПожСигн_Проч']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_ПожСигн_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Security Alarm (Охранная сигнализация)
     */
    public function createSMRSecurityAlarmKeyboard(): array
    {
        return [
            [
                ['text' => '🛡️ Монтаж оборудования', 'callback_data' => 'cat_ШМР_ОхрСигн_МонтОб']
            ],
            [
                ['text' => '📦 Монтаж кабеленесущих систем', 'callback_data' => 'cat_ШМР_ОхрСигн_МонтКбСист']
            ],
            [
                ['text' => '🔌 Прокладка кабеля', 'callback_data' => 'cat_ШМР_ОхрСигн_ПроклКб']
            ],
            [
                ['text' => '🔗 Подключение оборудования', 'callback_data' => 'cat_ШМР_ОхрСигн_ПодклОб']
            ],
            [
                ['text' => '🏷️ Маркировка оборудования/КЛ', 'callback_data' => 'cat_ШМР_ОхрСигн_МаркОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ШМР_ОхрСигн_Проч']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_ОхрСигн_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }
    
    /**
     * Create inline keyboard for ШМР Automation (Автоматизация)
     */
    public function createSMRAutomationKeyboard(): array
    {
        return [
            [
                ['text' => '🤖 Монтаж оборудования', 'callback_data' => 'cat_ШМР_Автом_МонтОб']
            ],
            [
                ['text' => '📦 Монтаж кабеленесущих систем', 'callback_data' => 'cat_ШМР_Автом_МонтКбСист']
            ],
            [
                ['text' => '🔌 Прокладка кабеля', 'callback_data' => 'cat_ШМР_Автом_ПроклКб']
            ],
            [
                ['text' => '🔗 Подключение оборудования', 'callback_data' => 'cat_ШМР_Автом_ПодклОб']
            ],
            [
                ['text' => '🏷️ Маркировка оборудования/КЛ', 'callback_data' => 'cat_ШМР_Автом_МаркОб']
            ],
            [
                ['text' => '📝 Прочее', 'callback_data' => 'cat_ШМР_Автом_Проч']
            ],
            [
                ['text' => '✅ Подтвердить достижение вехи', 'callback_data' => 'cat_ШМР_Автом_ПодтВех']
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => 'cat_ШМР_ВнутрМонт']
            ]
        ];
    }
    
    /**
     * Create generic 3-button submenu for ШМР equipment sub-activities
     * @param string $equipmentType Short equipment type key (e.g., 'ЭлОб', 'ПожСигн', 'ОхрСигн', 'Автом')
     * @param string $activityType Short activity type key (e.g., 'МонтОб', 'МонтКбСист', 'ПроклКб', 'ПодклОб', 'МаркОб')
     * @param string $backCallback The callback for the "Back" button
     */
    public function createSMREquipmentActivityKeyboard(string $equipmentType, string $activityType, string $backCallback): array
    {
        return [
            [
                ['text' => '📋 Работы по графику', 'callback_data' => "cat_ШМР_{$equipmentType}_{$activityType}_РабГр"]
            ],
            [
                ['text' => '📄 Работы неучтенные в ВОР', 'callback_data' => "cat_ШМР_{$equipmentType}_{$activityType}_НеучВОР"]
            ],
            [
                ['text' => '⬅️ Назад', 'callback_data' => $backCallback]
            ]
        ];
    }
}