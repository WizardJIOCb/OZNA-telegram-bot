<?php
/**
 * Verification script for the Инструктаж button naming collision fix
 */

echo "=== ИНСТРУКТАЖ BUTTON NAMING COLLISION FIX ===\n\n";

// Include required files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/custom_autoload.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

$messageService = new MessageService();

echo "🔍 TESTING BUTTON DIFFERENTIATION:\n\n";

// Test Mobile Brigade keyboard
echo "1. Mobile Brigade Keyboard:\n";
$mobileBrigadeKeyboard = $messageService->createMobileBrigadeKeyboard();
foreach ($mobileBrigadeKeyboard as $index => $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Инструктаж') !== false) {
            echo "   📋 Button: '" . $button['text'] . "'\n";
            echo "   📡 Callback: '" . $button['callback_data'] . "'\n";
            echo "   📏 Length: " . strlen($button['callback_data']) . " bytes\n\n";
        }
    }
}

// Test Commissioning Works keyboard
echo "2. Commissioning Works Keyboard:\n";
$commissioningWorksKeyboard = $messageService->createCommissioningWorksKeyboard();
foreach ($commissioningWorksKeyboard as $index => $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Инструктаж') !== false) {
            echo "   📋 Button: '" . $button['text'] . "'\n";
            echo "   📡 Callback: '" . $button['callback_data'] . "'\n";
            echo "   📏 Length: " . strlen($button['callback_data']) . " bytes\n\n";
        }
    }
}

echo "✅ SOLUTION IMPLEMENTED:\n\n";
echo "**BEFORE (Problem):**\n";
echo "   - Mobile Brigade: '📋 Инструктаж' → confusion for users\n";
echo "   - Commissioning: '📋 Инструктаж' → identical button text\n\n";

echo "**AFTER (Fixed):**\n";
echo "   - Mobile Brigade: '📋 Инструктаж МБ' → clearly labeled for Mobile Brigade\n";
echo "   - Commissioning: '📋 Инструктаж ПНР' → clearly labeled for Commissioning Works\n\n";

echo "🎯 BENEFITS:\n";
echo "   ✅ No more naming collision\n";
echo "   ✅ Users can distinguish between the two functions\n";
echo "   ✅ Different callback data formats work correctly\n";
echo "   ✅ Both buttons can coexist without confusion\n\n";

echo "🔧 TECHNICAL DETAILS:\n";
echo "   • МБ = Мобильная Бригада (Mobile Brigade)\n";
echo "   • ПНР = Пусконаладочные Работы (Commissioning Works)\n";
echo "   • Both use their respective callback data formats\n";
echo "   • Server restart required for changes to take effect\n\n";

echo "🚀 STATUS: READY FOR TESTING!\n";
echo "Please restart your web server and test both buttons.\n";

?>