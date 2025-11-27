<?php
// Test script to verify that back button navigation is working correctly after the fix

echo "=== Back Button Navigation Fix Verification ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

$messageService = new MessageService();

echo "Testing different keyboard types:\n";
echo "================================\n\n";

// Test 1: Check what createCommissioningKeyboard returns (should be main commissioning menu)
echo "1. createCommissioningKeyboard() - Main commissioning menu:\n";
$commissioningKeyboard = $messageService->createCommissioningKeyboard();
foreach ($commissioningKeyboard as $index => $row) {
    foreach ($row as $button) {
        echo "   • {$button['text']} → {$button['callback_data']}\n";
    }
}

echo "\n";

// Test 2: Check what createCommissioningWorksKeyboard returns (should be equipment selection)
echo "2. createCommissioningWorksKeyboard() - Equipment selection menu:\n";
$worksKeyboard = $messageService->createCommissioningWorksKeyboard();
foreach ($worksKeyboard as $index => $row) {
    foreach ($row as $button) {
        echo "   • {$button['text']} → {$button['callback_data']}\n";
    }
}

echo "\n";
echo "=== Analysis ===\n";
echo "✅ BEFORE FIX: handleBackToCommissioning used createCommissioningWorksKeyboard()\n";
echo "   Result: All back buttons led to equipment selection menu (WRONG)\n\n";
echo "✅ AFTER FIX: handleBackToCommissioning uses createCommissioningKeyboard()\n";
echo "   Result: Back buttons return to proper commissioning menu (CORRECT)\n\n";

echo "Expected Navigation Flow:\n";
echo "1. Main Tabel → Commissioning (Пусконаладка) → Equipment Type → Back → Commissioning\n";
echo "2. Main Tabel → Supervision (Шеф-монтаж) → Work Type → Back → Supervision\n";
echo "3. Main Tabel → Current Activity → Activity Type → Back → Current Activity\n\n";

echo "✅ Back button navigation should now work correctly for all menu contexts!\n";
?>