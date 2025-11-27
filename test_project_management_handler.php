<?php

// Test script to verify project management handler functionality
require_once __DIR__ . '/CustomCommands/Handlers/ProjectManagementHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectManagementHandler;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Testing project management handler functionality...\n";

// Test 1: Check if handler can handle the correct callback
echo "\n1. Testing callback handling...\n";
$messageService = new MessageService();
$handler = new ProjectManagementHandler($messageService);

// Test with correct callback
$canHandle = $handler->canHandle('category_УправлениеПроектом');
if ($canHandle) {
    echo "✅ Handler correctly identifies project management callback\n";
} else {
    echo "❌ Handler fails to identify project management callback\n";
}

// Test with incorrect callback
$canHandleWrong = $handler->canHandle('category_Админка');
if (!$canHandleWrong) {
    echo "✅ Handler correctly rejects non-project management callback\n";
} else {
    echo "❌ Handler incorrectly accepts non-project management callback\n";
}

// Test 2: Check handler priority
echo "\n2. Testing handler priority...\n";
$priority = $handler->getPriority();
echo "Handler priority: {$priority}\n";
if ($priority > 0) {
    echo "✅ Handler has valid priority\n";
} else {
    echo "❌ Handler has invalid priority\n";
}

echo "\nTest completed!\n";