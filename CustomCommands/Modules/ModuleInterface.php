<?php

namespace Longman\TelegramBot\Commands\SystemCommands\Modules;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Conversation;

/**
 * Interface for all module classes
 * Provides contract for handling different types of callback queries in a modular way
 */
interface ModuleInterface
{
    /**
     * Get the name of this module
     *
     * @return string The module name
     */
    public function getName(): string;

    /**
     * Check if this module can handle the given callback data
     *
     * @param string $callbackData The callback data from the query
     * @return bool True if this module can process the callback
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
     * Get the priority of this module (higher number = higher priority)
     * Used when multiple modules could potentially handle the same callback
     *
     * @return int The priority level
     */
    public function getPriority(): int;

    /**
     * Get the dependencies of this module
     * Returns an array of module names that this module depends on
     *
     * @return string[] List of module dependencies
     */
    public function getDependencies(): array;
}