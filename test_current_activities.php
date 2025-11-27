<?php

echo "Testing Current Activities button functionality...\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

try {
    echo "Testing createWorkTypeKeyboard()...\n";
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    $keyboard = $messageService->createWorkTypeKeyboard();
    
    echo "Main tabel menu buttons:\n";
    $currentActivityFound = false;
    foreach ($keyboard as $index => $row) {
        foreach ($row as $button) {
            $text = $button['text'];
            $callback = $button['callback_data'];
            echo sprintf("  %d. %-40s → %s\n", $index + 1, $text, $callback);
            
            if (str_contains($text, 'Текущая деятельность') || str_contains($callback, 'Текущая')) {
                $currentActivityFound = true;
            }
        }
    }
    
    if ($currentActivityFound) {
        echo "✅ PASS: 'Текущая деятельность' button found in main menu!\n";
    } else {
        echo "❌ FAIL: 'Текущая деятельность' button NOT found in main menu\n";
    }
    
    echo "\nTesting createCurrentActivityKeyboard()...\n";
    if (method_exists($messageService, 'createCurrentActivityKeyboard')) {
        $currentKeyboard = $messageService->createCurrentActivityKeyboard();
        echo "✅ createCurrentActivityKeyboard method exists\n";
        echo "Current activity buttons:\n";
        foreach ($currentKeyboard as $index => $row) {
            foreach ($row as $button) {
                echo sprintf("  %d. %-35s → %s\n", $index + 1, $button['text'], $button['callback_data']);
            }
        }
    } else {
        echo "❌ createCurrentActivityKeyboard method does NOT exist\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}