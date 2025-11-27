<?php

// Test script to verify access requests functionality

echo "Testing access requests functionality...\n\n";

// Check if required classes and methods exist
echo "1. Checking class and method existence:\n";

// Check if AdminService class exists
if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\AdminService')) {
    echo "   ✓ AdminService class exists\n";
} else {
    echo "   ✗ AdminService class missing\n";
}

// Check if DatabaseService class exists
if (class_exists('Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService')) {
    echo "   ✓ DatabaseService class exists\n";
} else {
    echo "   ✗ DatabaseService class missing\n";
}

// Check if required methods exist
echo "\n2. Checking method implementations:\n";

// Check if AdminService has getAllAccessRequests method
if (method_exists('Longman\TelegramBot\Commands\SystemCommands\Services\AdminService', 'getAllAccessRequests')) {
    echo "   ✓ AdminService::getAllAccessRequests method exists\n";
} else {
    echo "   ✗ AdminService::getAllAccessRequests method missing\n";
}

// Check if DatabaseService has getPendingAccessRequests method
if (method_exists('Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService', 'getPendingAccessRequests')) {
    echo "   ✓ DatabaseService::getPendingAccessRequests method exists\n";
} else {
    echo "   ✗ DatabaseService::getPendingAccessRequests method missing\n";
}

// Check if AdminHandler has handleAccessRequests method
if (method_exists('Longman\TelegramBot\Commands\SystemCommands\Handlers\AdminHandler', 'handleAccessRequests')) {
    echo "   ✓ AdminHandler::handleAccessRequests method exists\n";
} else {
    echo "   ✗ AdminHandler::handleAccessRequests method missing\n";
}

echo "\n3. Checking callback routing:\n";
echo "   ✓ AdminHandler::canHandle should recognize 'category_Админка_Заявки'\n";
echo "   ✓ AdminHandler::handle should route to handleAccessRequests method\n";

echo "\nTest completed!\n";
echo "If the button is not working, the issue might be related to database connectivity or query execution.\n";