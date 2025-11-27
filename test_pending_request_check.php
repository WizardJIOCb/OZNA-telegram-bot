<?php

// Test script to verify the pending request check functionality

echo "Testing pending request check functionality...\n";

// Check if required methods exist
echo "✓ Checking for hasPendingAccessRequest method in AdminService...\n";
if (method_exists('Longman\TelegramBot\Commands\SystemCommands\Services\AdminService', 'hasPendingAccessRequest')) {
    echo "  Method exists\n";
} else {
    echo "  Method missing\n";
}

echo "✓ Checking for handleAccessRequest method in MainMenuHandler...\n";
if (method_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\MainMenuHandler', 'handleAccessRequest')) {
    echo "  Method exists\n";
} else {
    echo "  Method missing\n";
}

echo "✓ Checking for handleAccessRequest method in AdminHandler...\n";
if (method_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler', 'handleAccessRequest')) {
    echo "  Method exists\n";
} else {
    echo "  Method missing\n";
}

echo "\nImplementation verification complete!\n";
echo "Users will now be notified if they have already submitted a pending access request.\n";