<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Services;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

/**
 * Interface for callback query handlers
 * Provides contract for handling different types of callback queries
 */
interface CallbackHandlerInterface
{
    /**
     * Check if this handler can handle the given callback data
     *
     * @param string $callbackData The callback data from the query
     * @return bool True if this handler can process the callback
     */
    public function canHandle(string $callbackData): bool;

    /**
     * Handle the callback query
     *
     * @param CallbackQuery $callbackQuery The callback query object
     * @param string $category The parsed category from callback data
     * @param Conversation $conversation The conversation object for storing data
     * @return ServerResponse The response to send back
     */
    public function handle(CallbackQuery $callbackQuery, string $category, Conversation $conversation): ServerResponse;

    /**
     * Get the priority of this handler (higher number = higher priority)
     * Used when multiple handlers could potentially handle the same callback
     *
     * @return int The priority level
     */
    public function getPriority(): int;
}