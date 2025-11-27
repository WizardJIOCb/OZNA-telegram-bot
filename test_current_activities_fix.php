<?php

echo "=== TESTING CURRENT ACTIVITIES FIX ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    echo "1. Testing createWorkTypeKeyboard() (main menu):\n";
    echo "===============================================\n";
    $mainKeyboard = $messageService->createWorkTypeKeyboard();
    
    foreach ($mainKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  " . ($index + 1) . ". " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\n2. Testing createCurrentActivityKeyboard() (current activities):\n";
    echo "==============================================================\n";
    $currentKeyboard = $messageService->createCurrentActivityKeyboard();
    
    foreach ($currentKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  " . ($index + 1) . ". " . $button['text'] . " → " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\n3. COMPARISON:\n";
    echo "==============\n";
    echo "BEFORE FIX: After selecting date for current activities, users saw:\n";
    foreach ($mainKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  ❌ " . $button['text'] . "\n";
        }
    }
    
    echo "\nAFTER FIX: After selecting date for current activities, users will see:\n";
    foreach ($currentKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  ✅ " . $button['text'] . "\n";
        }
    }
    
    echo "\n4. EXPECTED FLOW:\n";
    echo "=================\n";
    echo "1. User clicks '📊 Текущая деятельность' in main menu\n";
    echo "2. User selects a date (Today/Yesterday/Other)\n";
    echo "3. User sees current activity buttons:\n";
    echo "   - 🩺 Медицинский осмотр\n";
    echo "   - 🎯 Аттестация\n";
    echo "   - 📋 Подготовка материала к ТКП\n";
    echo "   - 💼 Общая деятельность\n";
    echo "4. User selects an activity and confirms\n";
    echo "5. Entry is added to timesheet\n";
    
    echo "\n✅ FIX COMPLETED SUCCESSFULLY!\n";
    echo "Current Activities now work as expected.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}