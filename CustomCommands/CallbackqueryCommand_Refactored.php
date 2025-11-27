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

// Import our new services and handlers
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
use Longman\TelegramBot\Commands\SystemCommands\Handlers\SupervisorHandler;

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

        // Register handlers (order matters for priority)
        $this->handlerFactory->registerHandler(
            new AdminHandler($adminService, $this->messageService)
        );
        $this->handlerFactory->registerHandler(
            new AccessLevelHandler($adminService, $this->messageService)
        );
        $this->handlerFactory->registerHandler(
            new SupervisorHandler($this->messageService, $adminService)
        );
        $this->handlerFactory->registerHandler(
            new TabelHandler($this->databaseService, $this->messageService, $projectSelector, $tabelEntry, $adminService)
        );
        $this->handlerFactory->registerHandler(
            new ProfileHandler($this->messageService)
        );
        $this->handlerFactory->registerHandler(
            new FunctionalDirectionHandler($this->messageService)
        );
        $this->handlerFactory->registerHandler(
            new HelpHandler($this->messageService)
        );
    }

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws \Exception
     */    
    public function execute(): ServerResponse
    {
        // Initialize services and handlers
        $this->initializeServices();

        // Get callback query data
        $update = $this->getUpdate();
        $callback_query = $update->getCallbackQuery();
        $callback_data = $callback_query->getData();

        // Early exit if no category specified
        if (strpos($callback_data, 'category_') !== 0) {
            return Request::emptyResponse();
        }

        // Set up conversation for data persistence
        $conversation = new Conversation(
            $callback_query->getMessage()->getChat()->getId(), 
            $callback_query->getMessage()->getChat()->getId(), 
            "/callback"
        );

        // Save callback data for potential future use
        $conversation->notes['some-data'] = json_encode($callback_data);
        $conversation->update();

        // Handle the callback using our handler factory
        return $this->handlerFactory->handleCallback($callback_query, $callback_data, $conversation);
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