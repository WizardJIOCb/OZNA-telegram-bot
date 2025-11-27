<?php

// Test command loading
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

// Try to load our custom GenericmessageCommand
try {
    $genericMessageCommand = new \Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand();
    echo "Custom GenericmessageCommand loaded successfully\n";
    echo "Command name: " . $genericMessageCommand->getName() . "\n";
} catch (Exception $e) {
    echo "Error loading Custom GenericmessageCommand: " . $e->getMessage() . "\n";
}

// Try to load our custom TextmenuCommand
try {
    $textmenuCommand = new \Longman\TelegramBot\Commands\SystemCommands\TextmenuCommand();
    echo "Custom TextmenuCommand loaded successfully\n";
    echo "Command name: " . $textmenuCommand->getName() . "\n";
} catch (Exception $e) {
    echo "Error loading Custom TextmenuCommand: " . $e->getMessage() . "\n";
}

// Try to load our custom StartCommand
try {
    $startCommand = new \Longman\TelegramBot\Commands\SystemCommands\StartCommand();
    echo "Custom StartCommand loaded successfully\n";
    echo "Command name: " . $startCommand->getName() . "\n";
} catch (Exception $e) {
    echo "Error loading Custom StartCommand: " . $e->getMessage() . "\n";
}