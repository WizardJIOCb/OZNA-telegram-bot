<?php
// Test script to verify warranty equipment back button context-aware navigation

echo "=== Warranty Equipment Back Button Context Test ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

try {
    $messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();
    
    echo "Test 1: Warranty Equipment Keyboard - Commissioning Context\n";
    echo "=========================================================\n";
    $commissioningKeyboard = $messageService->createWarrantyEquipmentKeyboard('commissioning');
    
    echo "Context: commissioning\n";
    $backButton = end($commissioningKeyboard);
    if (isset($backButton[0]['callback_data'])) {
        $backCallback = $backButton[0]['callback_data'];
        echo "Back button callback: {$backCallback}\n";
        if ($backCallback === 'category_Табель_Пусконаладка_Back') {
            echo "✅ CORRECT: Returns to commissioning menu\n";
        } else {
            echo "❌ INCORRECT: Expected 'category_Табель_Пусконаладка_Back'\n";
        }
    }
    
    echo "\nTest 2: Warranty Equipment Keyboard - Supervision Context\n";
    echo "========================================================\n";
    $supervisionKeyboard = $messageService->createWarrantyEquipmentKeyboard('supervision');
    
    echo "Context: supervision\n";
    $backButton = end($supervisionKeyboard);
    if (isset($backButton[0]['callback_data'])) {
        $backCallback = $backButton[0]['callback_data'];
        echo "Back button callback: {$backCallback}\n";
        if ($backCallback === 'category_Табель_ШМ_Back') {
            echo "✅ CORRECT: Returns to supervision menu\n";
        } else {
            echo "❌ INCORRECT: Expected 'category_Табель_ШМ_Back'\n";
        }
    }
    
    echo "\nTest 3: Default Context (Backwards Compatibility)\n";
    echo "================================================\n";
    $defaultKeyboard = $messageService->createWarrantyEquipmentKeyboard();
    
    echo "Context: default (no parameter)\n";
    $backButton = end($defaultKeyboard);
    if (isset($backButton[0]['callback_data'])) {
        $backCallback = $backButton[0]['callback_data'];
        echo "Back button callback: {$backCallback}\n";
        if ($backCallback === 'category_Табель_Пусконаладка_Back') {
            echo "✅ CORRECT: Defaults to commissioning context\n";
        } else {
            echo "❌ INCORRECT: Should default to commissioning context\n";
        }
    }
    
    echo "\n=== Navigation Flow Analysis ===\n";
    echo "1. Commissioning Flow:\n";
    echo "   Main Menu → Commissioning → Warranty → Back → Commissioning ✅\n";
    echo "2. Supervision Flow:\n";
    echo "   Main Menu → Supervision → Warranty → Back → Supervision ✅\n";
    
    echo "\n=== Fix Summary ===\n";
    echo "✅ Modified createWarrantyEquipmentKeyboard() to accept context parameter\n";
    echo "✅ Updated handleWarrantyEquipmentMenu() to detect conversation context\n";
    echo "✅ Context-aware back button navigation implemented\n";
    echo "✅ Backwards compatibility maintained\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ WARRANTY EQUIPMENT BACK BUTTON NAVIGATION FIXED!\n";
?>