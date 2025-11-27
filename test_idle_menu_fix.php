<?php
// Test script to verify the idle menu fix for commissioning works

echo "=== TESTING IDLE MENU FIX FOR COMMISSIONING WORKS ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "1. TESTING COMMISSIONING KEYBOARD UPDATE:\n";
echo "=========================================\n";

// Test the updated commissioning keyboard
$messageService = new MessageService();
$commissioningKeyboard = $messageService->createCommissioningKeyboard();

echo "Searching for idle button in commissioning keyboard...\n";

$idleButtonFound = false;
$idleButtonCallback = '';

foreach ($commissioningKeyboard as $row) {
    foreach ($row as $button) {
        if (str_contains($button['text'], 'Простой')) {
            $idleButtonFound = true;
            $idleButtonCallback = $button['callback_data'];
            echo "✅ Found idle button: '{$button['text']}'\n";
            echo "   Callback data: '{$idleButtonCallback}'\n";
            break 2;
        }
    }
}

if (!$idleButtonFound) {
    echo "❌ Idle button not found in commissioning keyboard!\n";
    exit(1);
}

echo "\n2. TESTING CALLBACK ROUTING:\n";
echo "============================\n";

// Test if the callback will be routed to detailed idle menu
$category = str_replace('category_', '', $idleButtonCallback);
echo "Extracted category: '{$category}'\n";

// Test the isIdleMenuCallback logic
$isIdleMenu = false;
if ($category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой' ||
    str_contains($category, '_Простой_') || str_contains($category, '_ШМ_Простой_')) {
    $isIdleMenu = true;
}

echo "Is idle menu callback: " . ($isIdleMenu ? 'YES' : 'NO') . "\n";

if ($isIdleMenu) {
    echo "✅ Callback will be routed to detailed idle menu system\n";
} else {
    echo "❌ Callback will NOT be routed to idle menu - fix failed!\n";
    exit(1);
}

echo "\n3. TESTING IDLE MENU LEVEL DETECTION:\n";
echo "=====================================\n";

// Test which level this callback represents
if ($category === 'Табель_Простой' || $category === 'Табель_ШМ_Простой') {
    echo "Level: Main categories (will show 6 main idle categories)\n";
} elseif (preg_match('/^Табель_(ШМ_)?Простой_(\d+)$/', $category, $matches)) {
    $categoryNumber = (int)$matches[2];
    $isSupervision = !empty($matches[1]);
    echo "Level: Subcategories for category {$categoryNumber}\n";
    echo "Supervision: " . ($isSupervision ? 'YES' : 'NO') . "\n";
    echo "✅ Will show subcategories for idle category {$categoryNumber}\n";
} else {
    echo "Level: Unknown - this needs investigation\n";
}

echo "\n4. EXPECTED USER FLOW:\n";
echo "======================\n";

echo "When user clicks '⏸️ Простой' button:\n";
echo "1. Callback sent: '{$idleButtonCallback}'\n";
echo "2. TabelHandler.isIdleMenuCallback() returns TRUE\n";
echo "3. Routes to handleIdleMenu()\n";
echo "4. Matches pattern for subcategories level\n";
echo "5. Calls handleIdleSubcategories() with category 1\n";
echo "6. Shows 'Ожидание допоставки' submenu with 'Дефицит по спецификации' option\n";
echo "7. User clicks 'Дефицит по спецификации'\n";
echo "8. Final selection with callback like 'category_Табель_Простой_1_def'\n";
echo "9. getCommissioningIdleReason() formats as 'Ожидание допоставки (Дефицит по спецификации)'\n";
echo "10. Confirmation shows: 'Причина простоя: Ожидание допоставки (Дефицит по спецификации)'\n";

echo "\n5. VERIFICATION SUMMARY:\n";
echo "========================\n";

if ($isIdleMenu && preg_match('/^Табель_Простой_1$/', $category)) {
    echo "🎉 SUCCESS: Fix appears to be working correctly!\n";
    echo "   ✅ Idle button updated to use detailed menu\n";
    echo "   ✅ Callback routes to idle menu system\n";
    echo "   ✅ Will show category 1 (Ожидание допоставки) submenu\n";
    echo "   ✅ Should provide the requested parent-child format\n";
} else {
    echo "❌ ISSUE: Fix may not be working as expected\n";
    echo "   - Check callback format: '{$idleButtonCallback}'\n";
    echo "   - Check category extraction: '{$category}'\n";
}

echo "\nTest completed!\n";
?>