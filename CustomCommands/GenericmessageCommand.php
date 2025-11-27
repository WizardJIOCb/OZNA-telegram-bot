<?php

/**
 * Generic message command
 * 
 * Handles all text messages that are not commands
 * Used for processing user input in specific states (like adding projects)
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;

// Manual includes for our custom classes
require_once __DIR__ . '/Services/AdminService.php';
require_once __DIR__ . '/Services/MessageService.php';
require_once __DIR__ . '/Services/DatabaseService.php';
require_once __DIR__ . '/Services/ExportSecurityService.php';
require_once __DIR__ . '/Services/ManualInputStateManager.php';
require_once __DIR__ . '/Services/NotesService.php';
require_once __DIR__ . '/Handlers/NotesHandler.php';
require_once __DIR__ . '/Handlers/ExportKeyHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ExportSecurityService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ManualInputStateManager;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\NotesHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ExportKeyHandler;

class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle all text messages that are not commands';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    private AdminService $adminService;
    private MessageService $messageService;
    private DatabaseService $databaseService;
    private ExportSecurityService $exportSecurityService;
    private ManualInputStateManager $stateManager;
    private ?NotesHandler $notesHandler = null;
    private ?ExportKeyHandler $exportKeyHandler = null;

    /**
     * Initialize services
     */
    private function initializeServices(): void
    {
        if (isset($this->adminService)) {
            return; // Already initialized
        }

        $this->databaseService = new DatabaseService();
        $this->messageService = new MessageService();
        $this->adminService = new AdminService($this->messageService);
        $this->exportSecurityService = new ExportSecurityService($this->databaseService);
        $this->stateManager = new ManualInputStateManager();
        $this->notesHandler = new NotesHandler($this->messageService, true);
        $this->exportKeyHandler = new ExportKeyHandler(
            $this->messageService,
            $this->databaseService,
            $this->exportSecurityService
        );
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
     * Main command execution
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse
    {
        try {
            $this->initializeServices();
        } catch (\Exception $e) {
            error_log("GenericmessageCommand: Service initialization failed: " . $e->getMessage());
            return Request::emptyResponse();
        }

        $message = $this->getMessage();
        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = trim($message->getText(true));
        
        $chatId = $chat->getId();
        $userId = $user->getId();

        // Log which command is handling the message
        error_log("GenericmessageCommand: Handling text from user {$userId}");
        
        // Fix encoding issues
        $text = $this->ensureUtf8Encoding($text);
        
        // Additional encoding fix for Cyrillic characters
        if (function_exists('mb_convert_encoding')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }

        // Get conversation to check user state - используем то же имя что в CallbackqueryCommand
        $conversation = new Conversation($userId, $chatId, '/callback');
        
        // Check if user is in notes input state (create or edit)
        if ($this->isNotesInputState($conversation)) {
            return $this->handleNotesInput($message, $conversation, $chatId, $userId);
        }
        
        // Check if user is in project card creation flow
        if ($this->isInProjectCardCreation($conversation, $userId)) {
            return $this->handleProjectCardInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is in admin project adding state
        if ($this->isAddingProject($conversation, $userId)) {
            return $this->handleAddProject($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is in admin adding state
        if ($this->isAddingAdmin($conversation, $userId)) {
            return $this->handleAddAdmin($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting Grok AI question
        if ($this->isAwaitingGrokQuestion($conversation, $userId)) {
            return $this->handleGrokQuestion($chatId, $userId, $text, $conversation);
        }

        // Check if user is in project moderation state (adding/renaming/rejecting)
        if ($this->isInProjectModerationState($conversation, $userId)) {
            return $this->handleProjectModerationInput($chatId, $userId, $text, $conversation);
        }

        // Check if user is awaiting FIO input
        if ($this->isAwaitingFioInput($conversation, $userId)) {
            return $this->handleFioInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting export key input
        if ($this->isAwaitingExportKey($conversation, $userId)) {
            return $this->exportKeyHandler->handleExportKeyInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting export key verification
        if (isset($conversation->notes['awaiting_export_key_verify']) && $conversation->notes['awaiting_export_key_verify']) {
            // Verify the key
            $exportType = $conversation->notes['export_pending_type'] ?? 'notes';
            
            if ($this->exportSecurityService->verifyExportKey($userId, $text)) {
                // Key is correct! Clear verification state and get resume callback
                $conversation->notes['awaiting_export_key_verify'] = false;
                $resumeCallback = $conversation->notes['export_resume_callback'] ?? null;
                $conversation->update();
                
                // Send confirmation message
                $confirmText = "✅ <b>Кодовое слово принято</b>\n\nПродолжаем выгрузку...";
                $this->messageService->sendMessage($chatId, $confirmText);
                
                // Resume the export flow by simulating callback
                if ($resumeCallback) {
                    $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
                        $this->adminService,
                        $this->messageService
                    );
                    
                    // Create a fake callback query with the resume callback
                    $callbackQuery = new \Longman\TelegramBot\Entities\CallbackQuery([
                        'id' => 'fake_callback_' . time(),
                        'from' => $this->update->getMessage()->getFrom()->getRawData(),
                        'message' => $this->update->getMessage()->getRawData(),
                        'data' => $resumeCallback
                    ]);
                    
                    // Extract category from callback
                    $category = str_replace('category_', '', $resumeCallback);
                    $category = str_replace('proj_report_', '', $category);
                    
                    // Handle the callback
                    return $projectHandler->handle($callbackQuery, $category, $conversation);
                }
                
                // Fallback if no resume callback
                return $this->messageService->sendMessage($chatId, $confirmText);
            } else {
                // Wrong key
                $text = "❌ <b>Неверное кодовое слово</b>\n\n";
                $text .= "Попробуйте снова или обратитесь к суперадминистратору для сброса.";
                
                $keyboard = [
                    [['text' => '🔄 Попробовать снова', 'callback_data' => 'retry_export']],
                    [['text' => '⬅️ Отмена', 'callback_data' => 'category_Проекты']]
                ];
                
                return $this->messageService->sendMessage($chatId, $text, $keyboard);
            }
        }
        
        // Check if user is awaiting custom input for "Прочее" activity (NEW implementation)
        if ($this->isAwaitingNewProcheeInput($conversation, $userId)) {
            return $this->handleNewProcheeInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting custom input for "Прочее" activity (LEGACY implementation)
        if ($this->isAwaitingCustomInput($conversation, $userId)) {
            return $this->handleCustomInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting date input
        if ($this->isAwaitingDateInput($conversation, $userId)) {
            return $this->handleDateInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting simple report date input
        if ($this->isAwaitingSimpleReportDate($conversation, $userId)) {
            return $this->handleSimpleReportDateInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting project report date input
        if ($this->isAwaitingProjectReportDate($conversation, $userId)) {
            return $this->handleProjectReportDateInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting notes export date input
        if ($this->isAwaitingNotesExportDate($conversation, $userId)) {
            return $this->handleNotesExportDateInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting manual group ID input (file-based state)
        $isAwaitingGroupInput = $this->stateManager->isAwaitingGroupInput($userId);
        
        if ($isAwaitingGroupInput) {
            return $this->handleManualGroupInputFromFile($chatId, $userId, $text);
        }
        
        // Check if user is awaiting manual chat ID input (file-based state)
        $isAwaitingChatInput = $this->stateManager->isAwaitingChatInput($userId);
        
        if ($isAwaitingChatInput) {
            return $this->handleManualChatInputFromFile($chatId, $userId, $text);
        }
        
        // Check if user is awaiting manual group ID input (conversation-based - legacy)
        if ($this->isAwaitingManualGroupInput($conversation, $userId)) {
            return $this->handleManualGroupInput($chatId, $userId, $text, $conversation);
        }
        
        // Check if user is awaiting manual chat ID input (conversation-based - legacy)
        if ($this->isAwaitingManualChatInput($conversation, $userId)) {
            return $this->handleManualChatInput($chatId, $userId, $text, $conversation);
        }

        // For reply keyboard menu selections, let TextmenuCommand handle them
        // Check if this is a menu selection by checking for menu text
        $menuItems = [
            '📊 Табель', 'Табель', 
            '❓ Помощь', 'Помощь', 
            '👤 Профиль', 'Профиль', 
            '📁 Проекты', 'Проекты', 
            '⚙️ Админка', 'Админка',
            '🔐 Запросить доступ', 'Запросить доступ',
            '🛠️ Управление проектом', 'Управление проектом',
            '📋 Супервайзер', 'Супервайзер'
        ];
        if (in_array($text, $menuItems)) {
            // This is a menu selection, route to TextmenuCommand
            error_log("GenericmessageCommand routing menu selection: '{$text}' to TextmenuCommand");
            
            // Create a new TextmenuCommand instance and execute it
            $textmenu_command = new \Longman\TelegramBot\Commands\SystemCommands\TextmenuCommand($this->telegram, $this->update);
            return $textmenu_command->execute();
        }

        // Handle other text messages as needed
        // If we get here, it's not a menu selection, so we can handle it as a regular message
        // For now, we'll just ignore it as we're only interested in specific states
        return Request::emptyResponse();
    }

    /**
     * Check if user is in project adding state
     */
    private function isAddingProject(Conversation $conversation, int $userId): bool
    {
        // Check if user is administrator (level 3) or super admin (level 4)
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        $hasAdminAccess = $userAccessLevel >= 3; // Level 3 (Administrator) or higher
        
        if (!$hasAdminAccess) {
            return false;
        }

        $adminAction = $conversation->notes['admin_action'] ?? null;
        $isAddingProject = $adminAction === 'add_project';
        
        return $isAddingProject;
    }
    
    /**
     * Check if user is in admin adding state
     */
    private function isAddingAdmin(Conversation $conversation, int $userId): bool
    {
        // Check if user is administrator (level 3) or super admin (level 4)
        $userAccessLevel = $this->adminService->getUserAccessLevel($userId);
        $hasAdminAccess = $userAccessLevel >= 3; // Level 3 (Administrator) or higher
        
        if (!$hasAdminAccess) {
            return false;
        }

        $adminAction = $conversation->notes['admin_action'] ?? null;
        $isAddingAdmin = $adminAction === 'add_admin';
        
        return $isAddingAdmin;
    }

    /**
     * Check if user is awaiting FIO input
     */
    private function isAwaitingFioInput(Conversation $conversation, int $userId): bool
    {
        $awaitingFioInput = $conversation->notes['awaiting_fio_input'] ?? false;
        return $awaitingFioInput;
    }
    
    /**
     * Check if user is awaiting export key input
     */
    private function isAwaitingExportKey(Conversation $conversation, int $userId): bool
    {
        $awaitingExportKey = $conversation->notes['awaiting_export_key'] ?? false;
        return $awaitingExportKey;
    }
    
    /**
     * Handle FIO input
     */
    private function handleFioInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        // Reset FIO input state
        $conversation->notes['awaiting_fio_input'] = false;
        $conversation->update();

        // Fix encoding issues
        $text = $this->ensureUtf8Encoding($text);

        // Validate FIO length
        if (strlen(trim($text)) < 3) {
            $responseText = '<b>❌ ФИО слишком короткое!</b>' . PHP_EOL .
                'Минимум 3 символа.' . PHP_EOL . PHP_EOL .
                'Пожалуйста, введите ваше полное ФИО в формате:' . PHP_EOL .
                'Фамилия Имя Отчество' . PHP_EOL . PHP_EOL .
                '<b>Пример:</b>' . PHP_EOL .
                'Иванов Иван Иванович';
                
            $responseText = $this->ensureUtf8Encoding($responseText);
            
            // Set the conversation state to expect FIO input again
            $conversation->notes['awaiting_fio_input'] = true;
            $conversation->update();
            
            return $this->messageService->sendMessage($chatId, $responseText);
        }

        // Update FIO in database
        $success = \pDB::updateUserFio($userId, trim($text));
        
        if ($success) {
            $responseText = "<b>✅ ФИО обновлено:</b> " . trim($text) . PHP_EOL . PHP_EOL .
                '📝 Используйте кнопку "👤 Просмотр профиля" для проверки изменений';
                
            $responseText = $this->ensureUtf8Encoding($responseText);
            
            return $this->messageService->sendMessage($chatId, $responseText);
        } else {
            $responseText = '<b>❌ Ошибка при обновлении ФИО</b>';
            $responseText = $this->ensureUtf8Encoding($responseText);
            
            return $this->messageService->sendMessage($chatId, $responseText);
        }
    }

    /**
     * Handle adding a new project
     */
    private function handleAddProject(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling add project for user {$userId} with text: '{$text}'");
        
        // Reset admin state
        $conversation->notes['admin_action'] = null;
        $conversation->update();

        // Fix encoding issues
        $text = $this->ensureUtf8Encoding($text);
        error_log("GenericmessageCommand: Handling add project for user {$userId} with text (after encoding fix): '{$text}'");

        // Validate project code
        if (empty($text)) {
            $keyboard = $this->adminService->createBackToProjectsKeyboard();
            return $this->messageService->sendMessage(
                $chatId,
                '❌ <b>Ошибка</b>

Код проекта не может быть пустым.',
                $keyboard
            );
        }

        // Check if project already exists
        if ($this->adminService->projectExists($text)) {
            $keyboard = $this->adminService->createBackToProjectsKeyboard();
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Проект уже существует</b>

Проект с кодом <b>{$text}</b> уже есть в списке.",
                $keyboard
            );
        }

        // Try to add project
        $success = $this->adminService->addProject($text);

        if ($success) {
            $keyboard = $this->adminService->createBackToProjectsKeyboard();
            return $this->messageService->sendMessage(
                $chatId,
                "✅ <b>Проект добавлен</b>

Проект <b>{$text}</b> успешно добавлен в список.

Теперь он будет доступен при выборе проектов в табеле.",
                $keyboard
            );
        } else {
            $keyboard = $this->adminService->createBackToProjectsKeyboard();
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Ошибка добавления</b>

Не удалось добавить проект <b>{$text}</b>. Попробуйте позже.",
                $keyboard
            );
        }
    }
    
    /**
     * Handle adding a new administrator
     */
    private function handleAddAdmin(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling add admin for user {$userId} with text: '{$text}'");
        
        // Reset admin state
        $conversation->notes['admin_action'] = null;
        $conversation->update();

        // Fix encoding issues
        $text = $this->ensureUtf8Encoding($text);
        error_log("GenericmessageCommand: Handling add admin for user {$userId} with text (after encoding fix): '{$text}'");

        // Validate user ID
        if (empty($text) || !is_numeric($text)) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                ]
            ];
            return $this->messageService->sendMessage(
                $chatId,
                '❌ <b>Ошибка</b>

User ID должен быть числом.',
                $keyboard
            );
        }

        $newAdminId = (int)$text;
        
        // Check if user is trying to add themselves as admin
        if ($newAdminId === $userId) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                ]
            ];
            return $this->messageService->sendMessage(
                $chatId,
                '❌ <b>Ошибка</b>

Вы не можете добавить себя как администратора.',
                $keyboard
            );
        }
        
        // Check if user is trying to add a super admin as admin
        if ($this->adminService->isSuperAdmin($newAdminId)) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                ]
            ];
            return $this->messageService->sendMessage(
                $chatId,
                '❌ <b>Ошибка</b>

Этот пользователь уже является супер-администратором.',
                $keyboard
            );
        }

        // Try to add administrator
        $success = $this->adminService->addAdministrator($newAdminId);

        if ($success) {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                ]
            ];
            return $this->messageService->sendMessage(
                $chatId,
                "✅ <b>Администратор добавлен</b>

Пользователь с ID <b>{$newAdminId}</b> успешно добавлен как администратор.",
                $keyboard
            );
        } else {
            $keyboard = [
                [
                    ['text' => '⬅️ Назад в админку', 'callback_data' => 'category_Админка']
                ]
            ];
            return $this->messageService->sendMessage(
                $chatId,
                "❌ <b>Ошибка добавления</b>

Не удалось добавить пользователя с ID <b>{$newAdminId}</b> как администратора. Попробуйте позже.",
                $keyboard
            );
        }
    }

    /**
     * Check if user is awaiting NEW Prochee custom input
     */
    private function isAwaitingNewProcheeInput(Conversation $conversation, int $userId): bool
    {
        $awaitingNewProcheeInput = $conversation->notes['awaiting_custom_prochee_input'] ?? false;
        error_log("GenericmessageCommand: User {$userId} awaiting NEW Prochee input: " . ($awaitingNewProcheeInput ? "true" : "false"));
        
        return $awaitingNewProcheeInput;
    }
    
    /**
     * Handle NEW Prochee custom input
     */
    private function handleNewProcheeInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling NEW Prochee custom input for user {$userId} with text: '{$text}'");
        
        // Create instances of required services
        $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
        $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
        $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
        
        // Create TabelHandler instance to properly handle the NEW Prochee custom input
        $tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
            $databaseService, 
            $messageService, 
            $projectSelector, 
            $tabelEntry, 
            $adminService
        );
        
        // Use TabelHandler's handleCustomInput method which now handles both old and new states
        return $tabelHandler->handleCustomInput($chatId, $text, $conversation);
    }

    /**
     * Check if user is awaiting custom input for "Прочее" activity
     */
    private function isAwaitingCustomInput(Conversation $conversation, int $userId): bool
    {
        $awaitingCustomInput = $conversation->notes['awaiting_custom_input'] ?? false;
        error_log("GenericmessageCommand: User {$userId} awaiting custom input: " . ($awaitingCustomInput ? "true" : "false"));
        
        return $awaitingCustomInput;
    }
    
    /**
     * Handle custom input for "Прочее" activity
     */
    private function handleCustomInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling custom input for user {$userId} with text: '{$text}'");
        
        // Get the activity type for which we're collecting custom input
        $activityType = $conversation->notes['custom_input_activity_type'] ?? '';
        error_log("GenericmessageCommand: Activity type detected: '{$activityType}'");
        
        if (empty($activityType)) {
            error_log("GenericmessageCommand: ERROR - Empty activity type!");
            return $this->messageService->sendMessage($chatId, 'Ошибка: не удалось определить тип активности');
        }
        
        // Special debug for idle comments
        if ($activityType === 'Простой_Комментарий') {
            error_log("GenericmessageCommand: IDLE COMMENT DETECTED - processing custom idle reason: '{$text}'");
        }
        
        // Store the custom input in conversation notes
        $conversation->notes['custom_input_text'] = $text;
        // Note: We don't set awaiting_custom_input to false here because TabelHandler will handle that
        $conversation->update();
        
        // Create instances of required services
        $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
        $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
        $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
        $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
        
        // Create TabelHandler instance to properly handle the custom input
        $tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
            $databaseService, 
            $messageService, 
            $projectSelector, 
            $tabelEntry, 
            $adminService
        );
        
        // Use TabelHandler's handleCustomInput method
        return $tabelHandler->handleCustomInput($chatId, $text, $conversation);
    }

    /**
     * Check if user is awaiting manual group ID input
     */
    private function isAwaitingManualGroupInput(Conversation $conversation, int $userId): bool
    {
        $manualInputType = $conversation->notes['manual_input_type'] ?? '';
        $hasProject = !empty($conversation->notes['manual_input_project'] ?? '');
        
        $isAwaiting = ($manualInputType === 'group_id' && $hasProject);
        error_log("GenericmessageCommand: User {$userId} awaiting group input: " . ($isAwaiting ? "true" : "false"));
        
        return $isAwaiting;
    }
    
    /**
     * Check if user is awaiting manual chat ID input
     */
    private function isAwaitingManualChatInput(Conversation $conversation, int $userId): bool
    {
        $manualInputType = $conversation->notes['manual_input_type'] ?? '';
        $hasProject = !empty($conversation->notes['manual_input_project'] ?? '');
        
        $isAwaiting = ($manualInputType === 'chat_id' && $hasProject);
        error_log("GenericmessageCommand: User {$userId} awaiting chat input: " . ($isAwaiting ? "true" : "false"));
        
        return $isAwaiting;
    }
    
    /**
     * Check if user is awaiting date input
     */
    private function isAwaitingDateInput(Conversation $conversation, int $userId): bool
    {
        $awaitingDateInput = $conversation->notes['awaiting_date_input'] ?? false;
        error_log("GenericmessageCommand: User {$userId} awaiting date input: " . ($awaitingDateInput ? "true" : "false"));
        
        return $awaitingDateInput;
    }
    
    /**
     * Handle date input
     */
    private function handleDateInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling date input for user {$userId} with text: '{$text}'");
        
        // Check if this is current activity flow - delegate to TabelHandler for proper handling
        // IMPORTANT: Only delegate if ONLY is_current_activity is true (not other work types)
        $isCurrentActivity = isset($conversation->notes['is_current_activity']) && $conversation->notes['is_current_activity'];
        $isCommissioning = isset($conversation->notes['is_commissioning']) && $conversation->notes['is_commissioning'];
        $isSupervision = isset($conversation->notes['is_supervision']) && $conversation->notes['is_supervision'];
        $isMobileBrigade = isset($conversation->notes['is_mobile_brigade']) && $conversation->notes['is_mobile_brigade'];
        
        // Only delegate to TabelHandler if this is EXCLUSIVELY current activity flow
        if ($isCurrentActivity && !$isCommissioning && !$isSupervision && !$isMobileBrigade) {
            error_log("GenericmessageCommand: Current activity flow detected (exclusive), delegating to TabelHandler");
            
            // Create required services
            $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
            $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
            $projectSelector = new \Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector();
            $tabelEntry = new \Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry($databaseService, $messageService);
            $adminService = new \Longman\TelegramBot\Commands\SystemCommands\Services\AdminService($messageService);
            
            // Create TabelHandler and delegate
            $tabelHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler(
                $databaseService, 
                $messageService, 
                $projectSelector, 
                $tabelEntry, 
                $adminService
            );
            
            return $tabelHandler->handleCustomDateInput($chatId, $text, $conversation, $userId);
        }
        
        error_log("GenericmessageCommand: Normal flow (not exclusive current activity), continuing with project selection");
        
        // Validate date format (DD.MM.YYYY)
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $text)) {
            // Validate that it's a real date
            $dateParts = explode('.', $text);
            if (checkdate((int)$dateParts[1], (int)$dateParts[0], (int)$dateParts[2])) {
                // Valid date, proceed with normal flow
                $conversation->notes['tabel_data'] = $text;
                $conversation->notes['awaiting_date_input'] = false;
                $conversation->update();
                
                // Continue with project selection
                $text = "<b>Табель за {$text}, выбор проекта</b>";
                
                // We need to create a MessageService instance to get the project selection keyboard
                $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
                $keyboard = $messageService->createProjectSelectionKeyboard($userId);
                return $messageService->sendMessage($chatId, $text, $keyboard);
            }
        }
        
        // Invalid date format, ask again
        $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
        $text = "Неверный формат даты. Пожалуйста, введите дату в формате ДД.ММ.ГГГГ (например, 25.12.2023):";
        return $messageService->sendMessage($chatId, $text);
    }
    
    /**
     * Handle manual group ID input
     */
    private function handleManualGroupInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling manual group ID input for user {$userId} with text: '{$text}'");
        
        // Get project code from conversation
        $projectCode = $conversation->notes['manual_input_project'] ?? '';
        
        if (empty($projectCode)) {
            // Clear manual input state
            unset($conversation->notes['manual_input_type']);
            unset($conversation->notes['manual_input_project']);
            $conversation->update();
            
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: не найден проект для настройки.');
        }
        
        // Validate group ID format
        $text = trim($text);
        if (!preg_match('/^-100\d+$/', $text)) {
            $responseText = "❌ <b>Неверный формат ID группы</b>\n\n";
            $responseText .= "ID должен начинаться с \"-100\".\n\n";
            $responseText .= "<b>Пример:</b> -1001234567890\n\n";
            $responseText .= "📝 <b>Введите корректный ID:</b>";
            return $this->messageService->sendMessage($chatId, $responseText);
        }
        
        // Save to database
        $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        $userData = $databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = [];
        }
        
        $controlProjects[$projectCode]['group_id'] = $text;
        $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
        
        $userData['control_projects'] = $controlProjects;
        $databaseService->updateUserData($userId, $userData);
        
        // Clear manual input state
        unset($conversation->notes['manual_input_type']);
        unset($conversation->notes['manual_input_project']);
        $conversation->update();
        
        $responseText = "✅ <b>ID группы сохранён</b>\n\n";
        $responseText .= "Проект <b>{$projectCode}</b> настроен.\n";
        $responseText .= "<b>ID группы:</b> {$text}";
        
        $keyboard = [[
            ['text' => '⬅️ К настройкам', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $responseText, $keyboard);
    }
    
    /**
     * Handle manual chat ID input
     */
    private function handleManualChatInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling manual chat ID input for user {$userId} with text: '{$text}'");
        
        // Get project code from conversation
        $projectCode = $conversation->notes['manual_input_project'] ?? '';
        
        if (empty($projectCode)) {
            // Clear manual input state
            unset($conversation->notes['manual_input_type']);
            unset($conversation->notes['manual_input_project']);
            $conversation->update();
            
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: не найден проект для настройки.');
        }
        
        // Validate chat ID format (can be user ID or group/channel ID)
        $text = trim($text);
        if (!preg_match('/^-?\d+$/', $text)) {
            $responseText = "❌ <b>Неверный формат ID чата</b>\n\n";
            $responseText .= "ID чата должен содержать только цифры и может начинаться с минуса.\n\n";
            $responseText .= "<b>Примеры правильных ID:</b>\n";
            $responseText .= "• 123456789 (User ID)\n";
            $responseText .= "• -1001234567890 (Группа)\n";
            $responseText .= "• -1009876543210 (Канал)\n\n";
            $responseText .= "💡 <b>Варианты ID чата:</b>\n";
            $responseText .= "• Для личного чата: ваш User ID\n";
            $responseText .= "• Для группы: ID начинается с \"-100\"\n";
            $responseText .= "• Для канала: ID начинается с \"-100\"\n\n";
            $responseText .= "📝 <b>Введите корректный ID чата:</b>";
            
            return $this->messageService->sendMessage($chatId, $responseText);
        }
        
        // Save the chat ID to user's control projects
        $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        $userData = $databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = [];
        }
        
        $controlProjects[$projectCode]['chat_id'] = $text;
        $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
        
        $userData['control_projects'] = $controlProjects;
        $databaseService->updateUserData($userId, $userData);
        
        // Clear manual input state
        unset($conversation->notes['manual_input_type']);
        unset($conversation->notes['manual_input_project']);
        $conversation->update();
        
        // Show success message with navigation back to project settings
        $responseText = "✅ <b>ID чата сохранён</b>\n\n";
        $responseText .= "Чат для проекта <b>{$projectCode}</b> успешно настроен.\n\n";
        $responseText .= "<b>ID чата:</b> {$text}";
        
        $keyboard = [[
            ['text' => '⬅️ Вернуться к настройкам проекта', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $responseText, $keyboard);
    }
    
    /**
     * Handle manual group ID input using file-based state
     */
    private function handleManualGroupInputFromFile(string $chatId, int $userId, string $text): ServerResponse
    {
        // Get project code from file-based state
        $projectCode = $this->stateManager->getProjectCode($userId);
        
        if (empty($projectCode)) {
            // Clear state and show error
            $this->stateManager->clearState($userId);
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: не найден проект для настройки.');
        }
        
        // Validate group ID format
        $text = trim($text);
        if (!preg_match('/^-100\d+$/', $text)) {
            $responseText = "❌ <b>Неверный формат ID группы</b>\n\n";
            $responseText .= "ID должен начинаться с \"-100\".\n\n";
            $responseText .= "<b>Пример:</b> -1001234567890\n\n";
            $responseText .= "📝 <b>Введите корректный ID:</b>";
            return $this->messageService->sendMessage($chatId, $responseText);
        }
        
        // Save to database
        $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        $userData = $databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = [];
        }
        
        $controlProjects[$projectCode]['group_id'] = $text;
        $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
        
        $userData['control_projects'] = $controlProjects;
        $databaseService->updateUserData($userId, $userData);
        
        // Clear file-based state
        $this->stateManager->clearState($userId);
        
        $responseText = "✅ <b>ID группы сохранён</b>\n\n";
        $responseText .= "Для проекта <b>{$projectCode}</b>:\n";
        $responseText .= "<b>ID группы:</b> {$text}\n\n";
        $responseText .= "💡 Теперь уведомления будут отправляться в указанную группу.";
        
        $keyboard = [[
            ['text' => '⬅️ К настройкам проекта', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $responseText, $keyboard);
    }
    
    /**
     * Handle manual chat ID input using file-based state
     */
    private function handleManualChatInputFromFile(string $chatId, int $userId, string $text): ServerResponse
    {
        // Get project code from file-based state
        $projectCode = $this->stateManager->getProjectCode($userId);
        
        if (empty($projectCode)) {
            // Clear state and show error
            $this->stateManager->clearState($userId);
            return $this->messageService->sendMessage($chatId, '❌ Ошибка: не найден проект для настройки.');
        }
        
        // Validate chat ID format (can be user ID or group/channel ID)
        $text = trim($text);
        if (!preg_match('/^-?\d+$/', $text)) {
            $responseText = "❌ <b>Неверный формат ID чата</b>\n\n";
            $responseText .= "ID чата должен содержать только цифры и может начинаться с минуса.\n\n";
            $responseText .= "<b>Примеры правильных ID:</b>\n";
            $responseText .= "• 123456789 (User ID)\n";
            $responseText .= "• -1001234567890 (Группа)\n";
            $responseText .= "• -1009876543210 (Канал)\n\n";
            $responseText .= "💡 <b>Что означает ID чата:</b>\n";
            $responseText .= "• Для личного чата: ваш User ID\n";
            $responseText .= "• Для группы: ID начинается с \"-100\"\n";
            $responseText .= "• Для канала: ID начинается с \"-100\"\n";
            $responseText .= "• Для темы в группе: ID темы (числовое значение)\n\n";
            $responseText .= "📝 <b>Введите корректный ID чата:</b>";
            
            return $this->messageService->sendMessage($chatId, $responseText);
        }
        
        // Save the chat ID to user's control projects
        $databaseService = new \Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService();
        $userData = $databaseService->getUserData($userId);
        $controlProjects = $userData['control_projects'] ?? [];
        
        if (!isset($controlProjects[$projectCode])) {
            $controlProjects[$projectCode] = [];
        }
        
        $controlProjects[$projectCode]['chat_id'] = $text;
        $controlProjects[$projectCode]['updated_at'] = date('Y-m-d H:i:s');
        
        $userData['control_projects'] = $controlProjects;
        $databaseService->updateUserData($userId, $userData);
        
        // Clear file-based state
        $this->stateManager->clearState($userId);
        
        // Show success message with navigation back to project settings
        $responseText = "✅ <b>ID чата сохранён</b>\n\n";
        $responseText .= "Для проекта <b>{$projectCode}</b>:\n";
        $responseText .= "<b>ID чата:</b> {$text}\n\n";
        $responseText .= "💡 Теперь уведомления будут отправляться в указанный чат.";
        
        $keyboard = [[
            ['text' => '⬅️ Вернуться к настройкам проекта', 'callback_data' => "proj_ctrl_set_{$projectCode}"]
        ]];
        
        return $this->messageService->sendMessage($chatId, $responseText, $keyboard);
    }

    /**
     * Check if user is awaiting simple report date input
     */
    private function isAwaitingSimpleReportDate(Conversation $conversation, int $userId): bool
    {
        $isAwaiting = isset($conversation->notes['awaiting_simple_report_start_date']) && $conversation->notes['awaiting_simple_report_start_date'];
        if (!$isAwaiting) {
            $isAwaiting = isset($conversation->notes['awaiting_simple_report_end_date']) && $conversation->notes['awaiting_simple_report_end_date'];
        }
        
        return $isAwaiting;
    }

    /**
     * Handle simple report date input
     */
    private function handleSimpleReportDateInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        // Load ProjectHandler to handle the date input
        require_once __DIR__ . '/Handlers/ProjectHandler.php';
        
        $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
            $this->adminService,
            $this->messageService
        );
        
        // Call the date input handler
        $result = $projectHandler->handleSimpleReportDateInput($chatId, $userId, $text, $conversation);
        
        if ($result !== null) {
            return $result;
        }
        
        // If handler returned null, something went wrong
        $errorText = '⚠️ Ошибка обработки даты. Попробуйте ещё раз.';
        return $this->messageService->sendMessage($chatId, $errorText);
    }

    /**
     * Check if user is awaiting project report date input
     */
    private function isAwaitingProjectReportDate(Conversation $conversation, int $userId): bool
    {
        $isAwaiting = isset($conversation->notes['awaiting_project_report_start_date']) && $conversation->notes['awaiting_project_report_start_date'];
        if (!$isAwaiting) {
            $isAwaiting = isset($conversation->notes['awaiting_project_report_end_date']) && $conversation->notes['awaiting_project_report_end_date'];
        }
        
        return $isAwaiting;
    }

    /**
     * Handle project report date input
     */
    private function handleProjectReportDateInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        // Load ProjectHandler to handle the date input
        require_once __DIR__ . '/Handlers/ProjectHandler.php';
        
        $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
            $this->adminService,
            $this->messageService
        );
        
        // Call the date input handler
        $result = $projectHandler->handleProjectReportDateInput($chatId, $userId, $text, $conversation);
        
        if ($result !== null) {
            return $result;
        }
        
        // If handler returned null, something went wrong
        $errorText = '⚠️ Ошибка обработки даты. Попробуйте ещё раз.';
        return $this->messageService->sendMessage($chatId, $errorText);
    }
    
    /**
     * Check if user is awaiting notes export date input
     */
    private function isAwaitingNotesExportDate(Conversation $conversation, int $userId): bool
    {
        $isAwaiting = isset($conversation->notes['awaiting_notes_export_start_date']) && $conversation->notes['awaiting_notes_export_start_date'];
        if (!$isAwaiting) {
            $isAwaiting = isset($conversation->notes['awaiting_notes_export_end_date']) && $conversation->notes['awaiting_notes_export_end_date'];
        }
        
        return $isAwaiting;
        return $isAwaiting;
    }
    
    /**
     * Handle notes export date input
     */
    private function handleNotesExportDateInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling notes export date input for user {$userId}: {$text}");
        
        // Load ProjectHandler to handle the date input
        require_once __DIR__ . '/Handlers/ProjectHandler.php';
        
        $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
            $this->adminService,
            $this->messageService
        );
        
        // Call the date input handler
        $result = $projectHandler->handleNotesExportDateInput($chatId, $userId, $text, $conversation);
        
        if ($result !== null) {
            return $result;
        }
        
        // If handler returned null, something went wrong
        $errorText = '⚠️ Ошибка обработки даты. Попробуйте ещё раз.';
        return $this->messageService->sendMessage($chatId, $errorText);
    }

    /**
     * Check if user is in project moderation state
     */
    private function isInProjectModerationState(Conversation $conversation, int $userId): bool
    {
        $projectModAction = $conversation->notes['project_mod_action'] ?? null;
        error_log("GenericmessageCommand: Current project_mod_action: " . ($projectModAction ?? "null"));
        
        return in_array($projectModAction, ['add_new', 'rename', 'reject']);
    }

    /**
     * Handle project moderation text input
     */
    private function handleProjectModerationInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        $projectModAction = $conversation->notes['project_mod_action'] ?? null;
        error_log("GenericmessageCommand: Handling project moderation input for action: {$projectModAction}");

        // CRITICAL FIX: Check if user accidentally clicked main menu button
        // These should NOT be processed as project names
        $menuCommands = [
            '📊 Табель', 'Табель',
            '❓ Помощь', 'Помощь',
            '👤 Профиль', 'Профиль',
            '📁 Проекты', 'Проекты',
            '⚙️ Админка', 'Админка',
            '🔐 Запросить доступ', 'Запросить доступ',
            '🛠️ Управление проектом', 'Управление проектом',
            '📋 Супервайзер', 'Супервайзер'
        ];
        
        if (in_array($text, $menuCommands)) {
            error_log("GenericmessageCommand: User {$userId} clicked menu button '{$text}' during project input - clearing state and routing to menu");
            
            // Clear project moderation state
            $conversation->notes['project_mod_action'] = null;
            $conversation->notes['project_mod_id'] = null;
            $conversation->update();
            
            // Send informational message
            $infoText = "⚠️ <b>Ввод отменён</b>\n\nВы нажали кнопку меню. Ввод названия проекта прерван.\n\nИспользуйте меню ниже для навигации.";
            $this->messageService->sendMessage($chatId, $infoText);
            
            // Route to the menu command they clicked
            $textmenu_command = new \Longman\TelegramBot\Commands\SystemCommands\TextmenuCommand($this->telegram, $this->update);
            return $textmenu_command->execute();
        }

        // Load ProjectModerationHandler
        require_once __DIR__ . '/Handlers/ProjectModerationHandler.php';
        require_once __DIR__ . '/Services/DatabaseService.php';
        
        $databaseService = $this->adminService->getDatabaseService();
        
        $projectModerationHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectModerationHandler(
            $databaseService,
            $this->messageService,
            $this->adminService
        );

        // Route to appropriate handler based on action
        switch ($projectModAction) {
            case 'add_new':
                // User is adding a new project
                $conversation->notes['project_mod_action'] = null;
                $conversation->update();
                return $projectModerationHandler->processNewProjectName($chatId, $userId, $text);

            case 'rename':
                // Admin is renaming a project
                $projectId = $conversation->notes['project_mod_id'] ?? null;
                if (!$projectId) {
                    $conversation->notes['project_mod_action'] = null;
                    $conversation->update();
                    return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
                }
                // ADDITIONAL CHECK: Validate rename input (minimum 3 chars)
                if (mb_strlen(trim($text)) < 3) {
                    $conversation->notes['project_mod_action'] = null;
                    $conversation->update();
                    return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nНазвание проекта должно быть от 3 до 50 символов.");
                }
                $conversation->notes['project_mod_action'] = null;
                $conversation->notes['project_mod_id'] = null;
                $conversation->update();
                return $projectModerationHandler->processRenameProject($chatId, $userId, $projectId, $text);

            case 'reject':
                // Admin is rejecting a project with reason
                $projectId = $conversation->notes['project_mod_id'] ?? null;
                if (!$projectId) {
                    $conversation->notes['project_mod_action'] = null;
                    $conversation->update();
                    return $this->messageService->sendMessage($chatId, '❌ Ошибка: проект не найден');
                }
                // ADDITIONAL CHECK: Ensure rejection reason is not empty
                if (mb_strlen(trim($text)) < 3) {
                    $conversation->notes['project_mod_action'] = null;
                    $conversation->update();
                    return $this->messageService->sendMessage($chatId, "❌ <b>Ошибка</b>\n\nПричина отклонения должна содержать минимум 3 символа.");
                }
                $conversation->notes['project_mod_action'] = null;
                $conversation->notes['project_mod_id'] = null;
                $conversation->update();
                return $projectModerationHandler->processRejectProject($chatId, $userId, $projectId, $text);

            default:
                $conversation->notes['project_mod_action'] = null;
                $conversation->update();
                return $this->messageService->sendMessage($chatId, '❌ Неизвестное действие');
        }
    }
    
    /**
     * Check if user is in notes input state
     */
    private function isNotesInputState(Conversation $conversation): bool
    {
        $notesAction = $conversation->notes['notes_action'] ?? null;
        return in_array($notesAction, ['create', 'edit']);
    }
    
    /**
     * Handle notes text input (create or edit)
     */
    private function handleNotesInput($message, Conversation $conversation, string $chatId, int $userId): ServerResponse
    {
        if (!$this->notesHandler) {
            error_log("GenericmessageCommand: NotesHandler not initialized");
            return Request::emptyResponse();
        }
        
        $response = $this->notesHandler->handleNoteTextInput($message, $conversation, $chatId, $userId);
        
        if ($response) {
            return $response;
        }
        
        return Request::emptyResponse();
    }
    
    /**
     * Check if user is awaiting Grok AI question
     */
    private function isAwaitingGrokQuestion(Conversation $conversation, int $userId): bool
    {
        $awaitingGrokQuestion = $conversation->notes['awaiting_grok_question'] ?? false;
        return $awaitingGrokQuestion;
    }
    
    /**
     * Handle Grok AI question input
     */
    private function handleGrokQuestion(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        error_log("GenericmessageCommand: Handling Grok question for user {$userId} with text: '{$text}'");
        
        // Reset Grok question state
        $conversation->notes['awaiting_grok_question'] = false;
        $conversation->update();
        error_log("GenericmessageCommand: Reset awaiting_grok_question state");

        // Fix encoding issues
        $text = $this->ensureUtf8Encoding($text);
        error_log("GenericmessageCommand: Text after encoding fix: '{$text}'");

        // Validate question
        if (empty(trim($text))) {
            error_log("GenericmessageCommand: Question is empty after trimming");
            $keyboard = [
                [
                    ['text' => '⬅️ Назад', 'callback_data' => 'category_Grok_Main']
                ]
            ];
            return $this->messageService->sendMessage(
                $chatId,
                '❌ <b>Ошибка</b>\n\nВопрос не может быть пустым.',
                $keyboard
            );
        }

        error_log("GenericmessageCommand: Creating GrokHandler instance");
        
        try {
            // Create GrokHandler instance
            $grokHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\GrokHandler(
                $this->databaseService,
                $this->messageService,
                $this->adminService
            );
            
            error_log("GenericmessageCommand: GrokHandler created successfully");
            
            // Process the question through GrokHandler
            // Get user's current project (optional context)
            $projectCode = ''; // Could enhance later to get from user's recent activity
            
            error_log("GenericmessageCommand: Calling GrokHandler->processQuestion()");
            $result = $grokHandler->processQuestion($text, $chatId, $projectCode);
            error_log("GenericmessageCommand: GrokHandler->processQuestion() completed");
            
            return $result;
        } catch (\Exception $e) {
            error_log("GenericmessageCommand: EXCEPTION in handleGrokQuestion: " . $e->getMessage());
            error_log("GenericmessageCommand: Exception trace: " . $e->getTraceAsString());
            
            return $this->messageService->sendMessage(
                $chatId,
                '❌ <b>Ошибка обработки запроса</b>\n\nПроизошла техническая ошибка. Попробуй позже.'
            );
        }
    }
    
    /**
     * Check if user is in project card creation flow
     */
    private function isInProjectCardCreation(Conversation $conversation, int $userId): bool
    {
        $state = $conversation->notes['state'] ?? null;
        return $state === 'awaiting_project_code';
    }
    
    /**
     * Handle project card input (code or name)
     */
    private function handleProjectCardInput(string $chatId, int $userId, string $text, Conversation $conversation): ServerResponse
    {
        // Load ProjectHandler
        require_once __DIR__ . '/Handlers/ProjectHandler.php';
        
        $projectHandler = new \Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler(
            $this->adminService,
            $this->messageService
        );
        
        return $projectHandler->handleMessage($chatId, $userId, $text, $conversation);
    }
}