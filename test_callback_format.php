<?php
/**
 * URGENT: Test the exact callback format being sent by confirmation keyboard
 */

echo "=== TESTING EXACT CALLBACK FORMAT ===\n\n";

// Include the MessageService to test the exact output
require_once __DIR__ . '/vendor/autoload.php';

// Use the actual MessageService from the project
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

try {
    $messageService = new MessageService();
    
    echo "1. Testing createConfirmationKeyboard with 'новое_прочее_yes':\n";
    $keyboard = $messageService->createConfirmationKeyboard('новое_прочее_yes');
    
    echo "Keyboard structure:\n";
    echo json_encode($keyboard, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    
    // Extract the exact callback data that will be sent
    $yesButtonCallback = $keyboard[0][0]['callback_data'];
    $noButtonCallback = $keyboard[0][1]['callback_data'];
    
    echo "2. Exact callback data:\n";
    echo "   'Да' button callback: '{$yesButtonCallback}'\n";
    echo "   'Нет' button callback: '{$noButtonCallback}'\n\n";
    
    echo "3. Verification:\n";
    if ($yesButtonCallback === 'новое_прочее_yes') {
        echo "   ✅ 'Да' button sends exactly: 'новое_прочее_yes'\n";
    } else {
        echo "   ❌ 'Да' button sends: '{$yesButtonCallback}' (NOT 'новое_прочее_yes')\n";
    }
    
    echo "\n4. Byte analysis:\n";
    echo "   Expected: 'новое_прочее_yes'\n";
    echo "   Expected hex: " . bin2hex('новое_прочее_yes') . "\n";
    echo "   Actual: '{$yesButtonCallback}'\n";
    echo "   Actual hex: " . bin2hex($yesButtonCallback) . "\n";
    
    if (bin2hex($yesButtonCallback) === bin2hex('новое_прочее_yes')) {
        echo "   ✅ Exact binary match\n";
    } else {
        echo "   ❌ Binary mismatch - encoding issue!\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING ALTERNATIVE CALLBACK FORMAT ===\n";

// Test if we should use a different callback format
echo "Testing standard format 'cat_ТабЭлОб_Проч_yes':\n";
try {
    $alternativeKeyboard = $messageService->createConfirmationKeyboard('cat_ТабЭлОб_Проч_yes');
    $altCallback = $alternativeKeyboard[0][0]['callback_data'];
    echo "Alternative callback: '{$altCallback}'\n";
    
    if ($altCallback === 'cat_ТабЭлОб_Проч_yes') {
        echo "✅ Standard format works correctly\n";
        echo "RECOMMENDATION: Use 'cat_ТабЭлОб_Проч_yes' instead of 'новое_прочее_yes'\n";
    }
} catch (Exception $e) {
    echo "ERROR testing alternative: " . $e->getMessage() . "\n";
}

echo "\n✅ CALLBACK FORMAT TEST COMPLETED\n";
?>