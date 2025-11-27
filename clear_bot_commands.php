<?php

/**
 * Clear all bot commands except /start and /menu
 * This updates the command list in Telegram
 */

require_once __DIR__ . '/vendor/autoload.php';

use Longman\TelegramBot\Request;

try {
    // Load configuration
    $config = require __DIR__ . '/config.php';
    
    // Initialize the Telegram bot
    $telegram = new Longman\TelegramBot\Telegram($config['api_key'], $config['bot_username']);
    
    // Define only the commands we want to keep
    $commands = [
        [
            'command' => 'start',
            'description' => 'Start the bot'
        ],
        [
            'command' => 'menu',
            'description' => 'Show main menu'
        ]
    ];
    
    // Set the new command list (this will remove all other commands)
    $result = Request::setMyCommands([
        'commands' => json_encode($commands)
    ]);
    
    if ($result->isOk()) {
        echo "✅ Bot commands updated successfully!\n\n";
        echo "Active commands:\n";
        echo "  /start - Start the bot\n";
        echo "  /menu - Show main menu\n\n";
        echo "All other commands have been removed from Telegram.\n";
    } else {
        echo "❌ Failed to update commands: " . $result->getDescription() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
