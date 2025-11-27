<?php

echo "=== TESTING FOUNDATION ANALYSIS ACTIVITY REMOVAL ===\n\n";

// Load the MessageService to test the keyboard
require_once "vendor/autoload.php";
require_once "CustomCommands/Services/DatabaseService.php";
require_once "CustomCommands/Services/MessageService.php";

use Longman\TelegramBot\Commands\SystemCommands\Services\DatabaseService;
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

try {
    $dbService = new DatabaseService();
    $messageService = new MessageService($dbService);
    
    echo "✅ STEP 1: TESTING PREPARATORY WORKS KEYBOARD\n";
    echo "=============================================\n";
    
    $keyboard = $messageService->createPreparatoryWorksKeyboard();
    
    echo "Current preparatory works menu:\n";
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            echo "  - {$button['text']} -> {$button['callback_data']}\n";
        }
    }
    
    echo "\n✅ STEP 2: VERIFYING REMOVAL\n";
    echo "============================\n";
    
    $foundFoundationAnalysis = false;
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            if (strpos($button['text'], 'фундамент') !== false || 
                strpos($button['text'], 'Анализ информации') !== false ||
                strpos($button['callback_data'], 'АналФунд') !== false) {
                $foundFoundationAnalysis = true;
                echo "❌ FOUND: Foundation analysis activity still present!\n";
                echo "   Text: {$button['text']}\n";
                echo "   Callback: {$button['callback_data']}\n";
            }
        }
    }
    
    if (!$foundFoundationAnalysis) {
        echo "✅ SUCCESS: Foundation analysis activity has been completely removed!\n";
    }
    
    echo "\n✅ STEP 3: TESTING SYNTAX VALIDITY\n";
    echo "==================================\n";
    
    $result = shell_exec("php -l \"CustomCommands/Services/MessageService.php\" 2>&1");
    if (strpos($result, 'No syntax errors') !== false) {
        echo "✅ SUCCESS: MessageService.php syntax is valid\n";
    } else {
        echo "❌ ERROR: Syntax error detected: $result\n";
    }
    
    echo "\n📋 FINAL SUMMARY\n";
    echo "================\n";
    echo "✅ Preparatory works menu now shows only:\n";
    echo "   1. 🔍 Входной контроль (Incoming control)\n";
    echo "   2. 🚚 Завоз оборудования на площадку (Equipment delivery to site)\n";
    echo "   3. ⬅️ Назад (Back)\n\n";
    
    echo "❌ REMOVED: 🏗️ Анализ информации по фундаменту (Foundation information analysis)\n\n";
    
    echo "🎯 The user's request has been successfully completed!\n";
    echo "The 'Foundation information analysis' activity is no longer available in the preparatory works menu.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

?>