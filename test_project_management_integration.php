<?php

// Test script to verify project management integration
require_once __DIR__ . '/CustomCommands/Handlers/ProjectManagementHandler.php';

use Longman\TelegramBot\Commands\SystemCommands\Handlers\ProjectManagementHandler;

echo "Testing project management integration...\n";

// Test 1: Check if handler can handle the correct callback
echo "\n1. Testing callback handling...\n";

// Create a mock message service
class MockMessageService {
    public function sendMessage($chatId, $text, $keyboard = null) {
        // Mock implementation
        return new \stdClass();
    }
}

$messageService = new MockMessageService();
$handler = new ProjectManagementHandler($messageService);

// Test with correct callback
$canHandle = $handler->canHandle('category_УправлениеПроектом');
if ($canHandle) {
    echo "✅ Handler correctly identifies project management callback\n";
} else {
    echo "❌ Handler fails to identify project management callback\n";
}

// Test with subcategory callback
$canHandleSub = $handler->canHandle('category_УправлениеПроектом_Персонал');
if ($canHandleSub) {
    echo "✅ Handler correctly identifies project management personnel callback\n";
} else {
    echo "❌ Handler fails to identify project management personnel callback\n";
}

// Test with incorrect callback
$canHandleWrong = $handler->canHandle('category_Админка');
if (!$canHandleWrong) {
    echo "✅ Handler correctly rejects non-project management callback\n";
} else {
    echo "❌ Handler incorrectly accepts non-project management callback\n";
}

echo "\nTest completed!\n";