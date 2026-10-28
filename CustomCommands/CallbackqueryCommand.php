<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Callback query command - Refactored version
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 * Now uses a clean architecture with separate handlers for different callback types.
 *
 * @see InlinekeyboardCommand.php
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;

// Import our new services and handlers with full namespace
use Longman\TelegramBot\Commands\SystemCommands\Services\CallbackHandlerFactory;
use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\ProjectSelector;
use Longman\TelegramBot\Commands\SystemCommands\Services\TabelEntry;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\FunctionalDirectionHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProfileHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\HelpHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\AccessLevelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\PersonnelHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectModerationHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\NotesHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\GrokHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectManagementHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectCardHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\TabelManagerHandler;
use Longman\TelegramBot\Commands\SystemCommands\Handlers\ClaimHandler;

class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Handle the callback query';

    /**
     * @var string
     */
    protected $version = '2.0.0';

    private CallbackHandlerFactory $handlerFactory;
    private DatabaseService $databaseService;
    private MessageService $messageService;

    /**
     * Initialize services and register handlers
     */
    private function initializeServices(): void
    {
        if (isset($this->handlerFactory)) {
            return; // Already initialized
        }

        // Initialize core services
        $this->databaseService = new DatabaseService();
        $this->messageService = new MessageService();
        $projectSelector = new ProjectSelector();
        $tabelEntry = new TabelEntry($this->databaseService, $this->messageService);
        $adminService = new AdminService($this->messageService);

        // Initialize handler factory
        $this->handlerFactory = new CallbackHandlerFactory();

        // Register ProjectManagementHandler FIRST to avoid timeout issues
        error_log("CallbackqueryCommand: Registering ProjectManagementHandler - FIRST");
        try {
            $this->handlerFactory->registerHandler(
                new ProjectManagementHandler($this->messageService)
            );
            error_log("CallbackqueryCommand: ProjectManagementHandler registered successfully - FIRST");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering ProjectManagementHandler: " . $e->getMessage());
            error_log("CallbackqueryCommand: ProjectManagementHandler trace: " . $e->getTraceAsString());
        }

        // Register handlers (order matters for priority)
        $this->handlerFactory->registerHandler(
            new AdminHandler($adminService, $this->messageService)
        );
        
        // Register TabelManagerHandler (must be before AdminHandler in priority to catch tabel management callbacks)
        error_log("CallbackqueryCommand: Registering TabelManagerHandler");
        try {
            $this->handlerFactory->registerHandler(
                new TabelManagerHandler($this->databaseService, $this->messageService, $adminService)
            );
            error_log("CallbackqueryCommand: TabelManagerHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering TabelManagerHandler: " . $e->getMessage());
        }
        
        $this->handlerFactory->registerHandler(
            new AccessLevelHandler($adminService, $this->messageService)
        );
        $this->handlerFactory->registerHandler(
            new PersonnelHandler($adminService, $this->messageService)
        );
        $this->handlerFactory->registerHandler(
            new SupervisorHandler($this->messageService, $adminService)
        );
        $this->handlerFactory->registerHandler(
            new ProjectModerationHandler($this->databaseService, $this->messageService, $adminService)
        );
        
        // Register ClaimHandler for claims management
        error_log("CallbackqueryCommand: Registering ClaimHandler");
        try {
            $this->handlerFactory->registerHandler(
                new ClaimHandler($this->messageService, $adminService)
            );
            error_log("CallbackqueryCommand: ClaimHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering ClaimHandler: " . $e->getMessage());
            error_log("CallbackqueryCommand: ClaimHandler trace: " . $e->getTraceAsString());
        }
        
        error_log("CallbackqueryCommand: Registering NotesHandler");
        try {
            $this->handlerFactory->registerHandler(
                new NotesHandler($this->messageService, true)
            );
            error_log("CallbackqueryCommand: NotesHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering NotesHandler: " . $e->getMessage());
            error_log("CallbackqueryCommand: NotesHandler trace: " . $e->getTraceAsString());
        }
        
        error_log("CallbackqueryCommand: Registering ProjectHandler");
        try {
            $this->handlerFactory->registerHandler(
                new ProjectHandler($adminService, $this->messageService)
            );
            error_log("CallbackqueryCommand: ProjectHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering ProjectHandler: " . $e->getMessage());
            error_log("CallbackqueryCommand: ProjectHandler trace: " . $e->getTraceAsString());
        }
        
        error_log("CallbackqueryCommand: Registering TabelHandler");
        try {
            $this->handlerFactory->registerHandler(
                new TabelHandler($this->databaseService, $this->messageService, $projectSelector, $tabelEntry, $adminService)
            );
            error_log("CallbackqueryCommand: TabelHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering TabelHandler: " . $e->getMessage());
            error_log("CallbackqueryCommand: TabelHandler trace: " . $e->getTraceAsString());
        }
        
        error_log("CallbackqueryCommand: Registering ProfileHandler");
        try {
            $this->handlerFactory->registerHandler(
                new ProfileHandler($this->messageService)
            );
            error_log("CallbackqueryCommand: ProfileHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering ProfileHandler: " . $e->getMessage());
        }
        
        error_log("CallbackqueryCommand: Registering FunctionalDirectionHandler");
        try {
            $this->handlerFactory->registerHandler(
                new FunctionalDirectionHandler($this->messageService)
            );
            error_log("CallbackqueryCommand: FunctionalDirectionHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering FunctionalDirectionHandler: " . $e->getMessage());
        }
        
        error_log("CallbackqueryCommand: Registering HelpHandler");
        try {
            $this->handlerFactory->registerHandler(
                new HelpHandler($this->messageService)
            );
            error_log("CallbackqueryCommand: HelpHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering HelpHandler: " . $e->getMessage());
        }
        
        error_log("CallbackqueryCommand: Registering GrokHandler");
        try {
            $this->handlerFactory->registerHandler(
                new GrokHandler($this->databaseService, $this->messageService, $adminService)
            );
            error_log("CallbackqueryCommand: GrokHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering GrokHandler: " . $e->getMessage());
        }
        
        error_log("CallbackqueryCommand: Registering MainMenuHandler");
        try {
            $this->handlerFactory->registerHandler(
                new MainMenuHandler($this->messageService)
            );
            error_log("CallbackqueryCommand: MainMenuHandler registered successfully");
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: ERROR registering MainMenuHandler: " . $e->getMessage());
        }
    }

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws \Exception
     */    
    public function execute(): ServerResponse
    {
        error_log("CallbackqueryCommand::execute - START");
        
        // Initialize services and handlers
        error_log("CallbackqueryCommand::execute - calling initializeServices()");
        $this->initializeServices();
        error_log("CallbackqueryCommand::execute - initializeServices() COMPLETE");

        // Get callback query data
        $update = $this->getUpdate();
        $callback_query = $update->getCallbackQuery();
        $callback_data = $callback_query->getData();
        
        // DEEP CALLBACK MONITOR - Log all received callbacks
        $deepLogFile = __DIR__ . "/../../deep_callback_monitor.log";
        $timestamp = date("Y-m-d H:i:s");
        $userId = $callback_query->getFrom()->getId();
        $data = $callback_query->getData();
        $firstName = $callback_query->getFrom()->getFirstName();
        
        // Log everything about this callback
        $logEntry = "=== CALLBACK RECEIVED AT $timestamp ===\n";
        $logEntry .= "User ID: $userId\n";
        $logEntry .= "User Name: $firstName\n";
        $logEntry .= "Callback Data: '$data'\n";
        $logEntry .= "Callback Length: " . strlen($data) . " bytes\n";
        $logEntry .= "Callback Hex: " . bin2hex($data) . "\n";
        
        // Check which patterns match
        $patterns = [
            "category_" => strpos($data, "category_") === 0 ? "MATCH" : "NO MATCH",
            "adm_req_" => strpos($data, "adm_req_") === 0 ? "MATCH" : "NO MATCH",
            "acc_lvl_" => strpos($data, "acc_lvl_") === 0 ? "MATCH" : "NO MATCH",
            "cat_Таб" => strpos($data, "cat_Таб") === 0 ? "MATCH" : "NO MATCH",
            "proj_ctrl_" => strpos($data, "proj_ctrl_") === 0 ? "MATCH" : "NO MATCH"
        ];
        
        $logEntry .= "Pattern Matching:\n";
        foreach ($patterns as $pattern => $result) {
            $logEntry .= "  $pattern -> $result\n";
        }
        
        $logEntry .= "Will pass through early exit filter: " .
            (strpos($data, "category_") === 0 ||
             strpos($data, "adm_req_") === 0 ||
             strpos($data, "acc_lvl_") === 0 ||
             strpos($data, "cat_Таб") === 0 ||
             strpos($data, "proj_ctrl_") === 0 ? "YES" : "NO") . "\n";
        
        $logEntry .= "===================================\n\n";
        
        file_put_contents($deepLogFile, $logEntry, FILE_APPEND);
        error_log("DEEP CALLBACK: $data from user $userId");
        
        // Простое логирование для диагностики кнопки управления пользователями
        $simpleLogFile = __DIR__ . "/../../simple_callback_test.log";
        if ($data === 'acc_lvl_manage_users') {
            $simpleEntry = date("Y-m-d H:i:s") . " - НАЙДЕН CALLBACK ДЛЯ УПРАВЛЕНИЯ ПОЛЬЗОВАТЕЛЯМИ!\n";
            $simpleEntry .= "User ID: $userId, Data: '$data'\n";
            $simpleEntry .= "---\n";
            file_put_contents($simpleLogFile, $simpleEntry, FILE_APPEND);
        } else {
            $simpleEntry = date("Y-m-d H:i:s") . " - Другой callback: '$data'\n";
            file_put_contents($simpleLogFile, $simpleEntry, FILE_APPEND);
        }
        
        // Add detailed logging
        error_log("CallbackqueryCommand: received callback_data: '{$callback_data}'");
        error_log("CallbackqueryCommand: received callback_data length: " . strlen($callback_data));
        error_log("CallbackqueryCommand: received callback_data hex: " . bin2hex($callback_data));
        
        // Check specifically for electrical equipment callback
        if (strpos($callback_data, 'Электрооборудование') !== false) {
            error_log("CallbackqueryCommand: detected electrical equipment callback data: '{$callback_data}'");
            error_log("CallbackqueryCommand: strpos 'category_Табель_Электрооборудование' result: " . (strpos($callback_data, 'category_Табель_Электрооборудование') === 0 ? 'true' : 'false'));
        }
        
        // CRITICAL FIX: Temporarily disable early exit to debug callback issues
        // This will allow ALL callbacks to pass through to handlers
        $shouldExit = false; // Changed from complex check to false to allow all callbacks
        
        // Log what would have happened with old logic
        $hasValidPrefix = 
            strpos($callback_data, 'category_') === 0 || 
            strpos($callback_data, 'adm_req_') === 0 || 
            strpos($callback_data, 'acc_lvl_') === 0 || 
            strpos($callback_data, 'cat_Таб') === 0 ||
            strpos($callback_data, 'cat_ШМР_') === 0 ||
            strpos($callback_data, 'cat_РЁРњР') === 0 ||
            strpos($callback_data, 'cat_supervision_') === 0 ||
            strpos($callback_data, 'cat_МобБриг') === 0 ||
            strpos($callback_data, 'proj_ctrl_') === 0 ||
            strpos($callback_data, 'proj_list_') === 0 ||
            strpos($callback_data, 'proj_report_') === 0 ||
            strpos($callback_data, 'proj_notes_') === 0 ||
            strpos($callback_data, 'proj_mod_') === 0 ||
            strpos($callback_data, 'tbl_set_') === 0 ||
            strpos($callback_data, 'pm_') === 0 ||
            strpos($callback_data, 'claim_') === 0 ||
            strpos($callback_data, 'cat_Гарантии') === 0;
        
        if (!$hasValidPrefix) {
            error_log("CallbackqueryCommand: WARNING - callback_data '{$callback_data}' does NOT match any known prefix, but allowing it through for debugging");
        } else {
            error_log("CallbackqueryCommand: callback_data '{$callback_data}' has valid prefix, processing normally");
        }
        
        if ($shouldExit) {
            error_log("CallbackqueryCommand: exiting - no category specified, callback_data: '{$callback_data}'");
            return Request::emptyResponse();
        }

        // Set up conversation for data persistence
        // IMPORTANT: Use userId as first parameter for consistency with GenericmessageCommand
        $conversation = new Conversation(
            $callback_query->getFrom()->getId(),  // Use userId, not chatId
            $callback_query->getMessage()->getChat()->getId(), 
            "/callback"
        );

        // Save callback data for potential future use
        $conversation->notes['some-data'] = json_encode($callback_data);
        // Note: We don't call $conversation->update() here to preserve existing conversation state set by handlers

        // Handle the callback using our handler factory
        error_log("CallbackqueryCommand: handling callback with factory, callback_data: '{$callback_data}'");
        
        try {
            $result = $this->handlerFactory->handleCallback($callback_query, $callback_data, $conversation);
            error_log("CallbackqueryCommand: factory handling complete");
            return $result;
        } catch (\Exception $e) {
            error_log("CallbackqueryCommand: EXCEPTION in handleCallback: " . $e->getMessage());
            error_log("CallbackqueryCommand: Exception file: " . $e->getFile());
            error_log("CallbackqueryCommand: Exception line: " . $e->getLine());
            error_log("CallbackqueryCommand: Stack trace: " . $e->getTraceAsString());
            
            return Request::sendMessage([
                'chat_id' => $callback_query->getMessage()->getChat()->getId(),
                'text' => '❌ Ошибка при обработке команды. Проверьте логи.',
            ]);
        } catch (\Error $e) {
            error_log("CallbackqueryCommand: ERROR in handleCallback: " . $e->getMessage());
            error_log("CallbackqueryCommand: Error file: " . $e->getFile());
            error_log("CallbackqueryCommand: Error line: " . $e->getLine());
            error_log("CallbackqueryCommand: Stack trace: " . $e->getTraceAsString());
            
            return Request::sendMessage([
                'chat_id' => $callback_query->getMessage()->getChat()->getId(),
                'text' => '❌ Критическая ошибка. Проверьте логи.',
            ]);
        }
    }

    /**
     * Legacy method for backward compatibility - Send message to history chat
     * @deprecated Use MessageService::sendToHistoryChat() instead
     */
    public function sendToHistoryChat($message, $history_thread_id = 2): ServerResponse
    {
        if (!isset($this->messageService)) {
            $this->messageService = new MessageService();
        }
        
        return $this->messageService->sendToHistoryChat($message, $history_thread_id);
    }

    /**
     * Legacy method for backward compatibility - Add user tabel entry
     * @deprecated Use TabelEntry::addEntry() instead
     */
    public function addUserTabel($user_id, $tabel_message): bool
    {
        if (!isset($this->databaseService)) {
            $this->databaseService = new DatabaseService();
        }
        
        return $this->databaseService->addUserTabel($user_id, $tabel_message);
    }
}