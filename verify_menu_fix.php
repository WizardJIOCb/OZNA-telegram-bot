<?php

/**
 * Server-side verification script for menu button fix
 */

// Include necessary files
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

echo "Menu Button Fix Verification\n";
echo "==========================\n\n";

// Test 1: Check if classes can be loaded
echo "1. Testing class loading:\n";
$classes = [
    'Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService',
    'Longman\TelegramBot\Commands\SystemCommands\Services\AdminService',
    'Longman\TelegramBot\Commands\SystemCommands\Services\MessageService',
    'Longman\TelegramBot\Commands\SystemCommands\TextmenuCommand',
    'Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand'
];

$allLoaded = true;
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "  ✓ $class\n";
    } else {
        echo "  ✗ $class\n";
        $allLoaded = false;
    }
}

echo "\n";

// Test 2: Check command names
echo "2. Testing command names:\n";
try {
    // Test TextmenuCommand
    $textmenuCommand = new \Longman\TelegramBot\Commands\SystemCommands\TextmenuCommand(null, null);
    echo "  TextmenuCommand name: " . $textmenuCommand->getName() . "\n";
    
    // Test GenericmessageCommand
    $genericMessageCommand = new \Longman\TelegramBot\Commands\SystemCommands\GenericmessageCommand(null, null);
    echo "  GenericmessageCommand name: " . $genericMessageCommand->getName() . "\n";
    
    if ($textmenuCommand->getName() !== $genericMessageCommand->getName()) {
        echo "  ✓ Command names are correctly different\n";
    } else {
        echo "  ✗ Command names are the same (conflict!)\n";
    }
} catch (Exception $e) {
    echo "  ✗ Error testing command names: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check menu keyboard creation
echo "3. Testing menu keyboard creation:\n";
try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    // Test main menu keyboard
    $mainMenuKeyboard = $messageService->createMainMenuReplyKeyboard();
    echo "  Main menu keyboard created with " . count($mainMenuKeyboard) . " rows\n";
    
    // Check if all expected buttons are present
    $buttons = [];
    foreach ($mainMenuKeyboard as $row) {
        foreach ($row as $button) {
            $buttons[] = $button['text'];
        }
    }
    
    $expectedButtons = ['📊 Табель', '❓ Помощь', '👤 Профиль', '⚙️ Админка'];
    foreach ($expectedButtons as $button) {
        if (in_array($button, $buttons)) {
            echo "  ✓ Button '$button' found\n";
        } else {
            echo "  ✗ Button '$button' missing\n";
        }
    }
} catch (Exception $e) {
    echo "  ✗ Error testing menu keyboard creation: " . $e->getMessage() . "\n";
}

echo "\nVerification complete!\n";

// Summary
if ($allLoaded) {
    echo "✓ All classes loaded successfully\n";
} else {
    echo "✗ Some classes failed to load\n";
}