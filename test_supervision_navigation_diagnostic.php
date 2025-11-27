<?php
/**
 * Diagnostic script to test supervision navigation buttons
 */

echo "🔍 SUPERVISION NAVIGATION DIAGNOSTIC TEST\n";
echo "========================================\n\n";

// Include the necessary files
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

$messageService = new MessageService();

echo "1. TESTING SUPERVISION KEYBOARDS:\n";
echo "---------------------------------\n\n";

// Test 1: Supervision Installation Works
echo "📋 Supervision Installation Works (Шеф-монтажные работы):\n";
$installationKeyboard = $messageService->createSupervisionInstallationWorksKeyboard();
$backButton = end($installationKeyboard)[0]; // Get the last button
echo "  Back button text: '{$backButton['text']}'\n";
echo "  Back button callback: '{$backButton['callback_data']}'\n";
if ($backButton['callback_data'] === 'Табель_ШМ_Back') {
    echo "  ✅ CORRECT - should work\n";
} else {
    echo "  ❌ WRONG - should be 'Табель_ШМ_Back'\n";
}
echo "\n";

// Test 2: Supervision Project Activities
echo "📋 Supervision Project Activities (Проектная деятельность):\n";
$projectKeyboard = $messageService->createSupervisionProjectActivityKeyboard();
$backButton = end($projectKeyboard)[0]; // Get the last button
echo "  Back button text: '{$backButton['text']}'\n";
echo "  Back button callback: '{$backButton['callback_data']}'\n";
if ($backButton['callback_data'] === 'Табель_ШМ_Back') {
    echo "  ✅ CORRECT - should work\n";
} else {
    echo "  ❌ WRONG - should be 'Табель_ШМ_Back'\n";
}
echo "\n";

// Test 3: Warranty Equipment (supervision context)
echo "📋 Warranty Equipment (supervision context):\n";
$warrantyKeyboard = $messageService->createWarrantyEquipmentKeyboard('supervision');
$backButton = end($warrantyKeyboard)[0]; // Get the last button
echo "  Back button text: '{$backButton['text']}'\n";
echo "  Back button callback: '{$backButton['callback_data']}'\n";
if ($backButton['callback_data'] === 'Табель_ШМ_Back') {
    echo "  ✅ CORRECT - should work\n";
} else {
    echo "  ❌ WRONG - should be 'Табель_ШМ_Back'\n";
}
echo "\n";

// Test 4: Main Supervision Keyboard
echo "📋 Main Supervision Menu:\n";
$supervisionKeyboard = $messageService->createSupervisionKeyboard();
$backButton = end($supervisionKeyboard)[0]; // Get the last button
echo "  Back button text: '{$backButton['text']}'\n";
echo "  Back button callback: '{$backButton['callback_data']}'\n";
if ($backButton['callback_data'] === 'Табель_ШМ_Back') {
    echo "  ✅ CORRECT - should work\n";
} else {
    echo "  ❌ WRONG - should be 'Табель_ШМ_Back'\n";
}
echo "\n";

echo "2. POSSIBLE ISSUES:\n";
echo "-------------------\n";
echo "If keyboards show CORRECT but buttons don't work:\n";
echo "• Server might need restart to reload changes\n";
echo "• TabelHandler routing might have issues\n";
echo "• Callback data might be getting corrupted\n\n";

echo "3. NEXT STEPS:\n";
echo "--------------\n";
echo "If this test shows CORRECT results:\n";
echo "1. Restart your web server (Apache/Nginx)\n";
echo "2. Clear any PHP cache (opcache, etc.)\n";
echo "3. Test again in Telegram\n\n";

echo "If buttons still don't work after restart:\n";
echo "1. Check server logs for errors\n";
echo "2. Add debug logging to TabelHandler\n";
echo "3. Verify conversation state\n\n";

echo "✅ DIAGNOSTIC COMPLETE\n";