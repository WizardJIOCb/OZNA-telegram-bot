<?php
// Test to verify both commissioning keyboards have the idle button

echo "=== TESTING BOTH COMMISSIONING KEYBOARDS ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

$messageService = new MessageService();

echo "1. TESTING createCommissioningKeyboard():\n";
echo "==========================================\n";

$commissioningKeyboard = $messageService->createCommissioningKeyboard();
$foundIdleButton1 = false;
$idleCallback1 = '';

foreach ($commissioningKeyboard as $row) {
    foreach ($row as $button) {
        if (str_contains($button['text'], 'Простой')) {
            $foundIdleButton1 = true;
            $idleCallback1 = $button['callback_data'];
            echo "✅ Found idle button: '{$button['text']}'\n";
            echo "   Callback: '{$idleCallback1}'\n";
            break 2;
        }
    }
}

if (!$foundIdleButton1) {
    echo "❌ No idle button found in createCommissioningKeyboard()\n";
}

echo "\n2. TESTING createCommissioningWorksKeyboard():\n";
echo "==============================================\n";

$commissioningWorksKeyboard = $messageService->createCommissioningWorksKeyboard();
$foundIdleButton2 = false;
$idleCallback2 = '';

foreach ($commissioningWorksKeyboard as $row) {
    foreach ($row as $button) {
        if (str_contains($button['text'], 'Простой')) {
            $foundIdleButton2 = true;
            $idleCallback2 = $button['callback_data'];
            echo "✅ Found idle button: '{$button['text']}'\n";
            echo "   Callback: '{$idleCallback2}'\n";
            break 2;
        }
    }
}

if (!$foundIdleButton2) {
    echo "❌ No idle button found in createCommissioningWorksKeyboard()\n";
}

echo "\n3. VERIFICATION:\n";
echo "================\n";

if ($foundIdleButton1 && $foundIdleButton2) {
    echo "🎉 SUCCESS: Both keyboards now have idle buttons!\n";
    
    if ($idleCallback1 === $idleCallback2 && $idleCallback1 === 'category_Табель_Простой_1') {
        echo "✅ Both callbacks are correct: '{$idleCallback1}'\n";
        echo "✅ Will route to detailed idle menu (category 1 = Ожидание допоставки)\n";
    } else {
        echo "⚠️  Callbacks differ:\n";
        echo "   createCommissioningKeyboard(): '{$idleCallback1}'\n";
        echo "   createCommissioningWorksKeyboard(): '{$idleCallback2}'\n";
    }
} else {
    echo "❌ ISSUE: Not all keyboards have idle buttons\n";
    echo "   createCommissioningKeyboard(): " . ($foundIdleButton1 ? 'HAS' : 'MISSING') . "\n";
    echo "   createCommissioningWorksKeyboard(): " . ($foundIdleButton2 ? 'HAS' : 'MISSING') . "\n";
}

echo "\n4. EXPECTED FLOW:\n";
echo "=================\n";
echo "Now when user clicks any '⏸️ Простой' button in commissioning:\n";
echo "1. Callback: 'category_Табель_Простой_1'\n";
echo "2. Routes to handleIdleMenu() → handleIdleSubcategories()\n";
echo "3. Shows 'Ожидание допоставки' submenu\n";
echo "4. User clicks 'Дефицит по спецификации'\n";
echo "5. Final callback: 'category_Табель_Простой_1_def'\n";
echo "6. getCommissioningIdleReason() formats as: 'Ожидание допоставки (Дефицит по спецификации)'\n";

echo "\nTest completed!\n";
?>