<?php

// Test script to verify main menu functionality
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Manual includes for our custom classes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Services/AdminService.php';
require_once __DIR__ . '/CustomCommands/TextmenuCommand.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;
use Longman\TelegramBot\Commands\SystemCommands\Services\AdminService;

echo "Testing main menu functionality...\n";

// Test 1: Check if main menu keyboard is created correctly
echo "\n1. Testing main menu keyboard creation...\n";
$messageService = new MessageService();
$mainMenuKeyboard = $messageService->createMainMenuReplyKeyboard();

echo "Main menu keyboard structure:\n";
foreach ($mainMenuKeyboard as $rowIndex => $row) {
    echo "  Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $buttonIndex => $button) {
        echo "    Button " . ($buttonIndex + 1) . ": " . $button['text'] . "\n";
    }
}

// Expected buttons
$expectedButtons = ['📊 Табель', '❓ Помощь', '👤 Профиль', '⚙️ Админка'];
$foundButtons = [];

foreach ($mainMenuKeyboard as $row) {
    foreach ($row as $button) {
        $foundButtons[] = $button['text'];
    }
}

$allFound = true;
foreach ($expectedButtons as $expectedButton) {
    if (!in_array($expectedButton, $foundButtons)) {
        echo "❌ Missing button: $expectedButton\n";
        $allFound = false;
    }
}

if ($allFound) {
    echo "✅ All expected buttons found in main menu\n";
}

// Test 2: Check TextmenuCommand handling
echo "\n2. Testing TextmenuCommand button handling...\n";

// Read TextmenuCommand content
$textmenuContent = file_get_contents(__DIR__ . '/CustomCommands/TextmenuCommand.php');

$buttonCases = [
    '📊 Табель' => 'Табель',
    'Табель' => 'Табель',
    '❓ Помощь' => 'Помощь',
    'Помощь' => 'Помощь',
    '👤 Профиль' => 'Профиль',
    'Профиль' => 'Профиль',
    '⚙️ Админка' => 'Админка',
    'Админка' => 'Админка'
];

$allHandled = true;
foreach ($buttonCases as $buttonWithEmoji => $buttonWithoutEmoji) {
    $caseFound = false;
    
    // Check for case statement with emoji
    if (strpos($textmenuContent, "case '$buttonWithEmoji':") !== false) {
        $caseFound = true;
    }
    
    // Check for case statement without emoji
    if (strpos($textmenuContent, "case '$buttonWithoutEmoji':") !== false) {
        $caseFound = true;
    }
    
    if ($caseFound) {
        echo "✅ TextmenuCommand handles: $buttonWithEmoji\n";
    } else {
        echo "❌ TextmenuCommand does not handle: $buttonWithEmoji\n";
        $allHandled = false;
    }
}

if ($allHandled) {
    echo "✅ TextmenuCommand handles all main menu buttons correctly\n";
}

echo "\nMain menu functionality test completed.\n";
echo "The main menu should now work correctly in Telegram.\n";