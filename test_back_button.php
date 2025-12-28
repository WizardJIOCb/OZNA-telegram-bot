<?php
// Test script to verify the "Назад в главное меню" button in the tabel list

require_once 'CustomCommands/Services/MessageService.php';

echo "Testing the 'Назад в главное меню' button in tabel list...\n\n";

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    // Get the work type keyboard
    $keyboard = $messageService->createWorkTypeKeyboard();
    
    echo "Work type keyboard created with " . count($keyboard) . " rows:\n";
    
    $hasBackButton = false;
    $backButtonIndex = 0;
    
    foreach ($keyboard as $index => $row) {
        echo "Row " . ($index + 1) . ": ";
        foreach ($row as $button) {
            echo "[{$button['text']}] ";
            if (isset($button['text']) && strpos($button['text'], 'Назад в главное меню') !== false) {
                $hasBackButton = true;
                $backButtonIndex = $index + 1;
                echo "(BACK BUTTON FOUND) ";
            }
        }
        echo "\n";
    }
    
    if ($hasBackButton) {
        echo "\n✅ SUCCESS: 'Назад в главное меню' button found in row $backButtonIndex\n";
        echo "✅ Callback data: " . $keyboard[$backButtonIndex-1][0]['callback_data'] . "\n";
        
        // Verify the callback data is correct
        if ($keyboard[$backButtonIndex-1][0]['callback_data'] === 'category_Главное') {
            echo "✅ Callback data is correct - will return to main menu\n";
        } else {
            echo "❌ Callback data is incorrect - should be 'category_Главное'\n";
        }
    } else {
        echo "\n❌ ERROR: 'Назад в главное меню' button NOT found in the keyboard\n";
    }
    
    echo "\nTest completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}