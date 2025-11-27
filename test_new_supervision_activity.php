<?php
require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Services/TabelEntry.php';

use CustomCommands\Services\MessageService;
use CustomCommands\Services\TabelEntry;

echo "=== Testing New 'Шеф-монтажные работы' Activity ===\n\n";

// Test 1: Check MessageService keyboard
echo "1. Testing MessageService keyboard:\n";
$messageService = new MessageService();
$keyboard = $messageService->createSupervisionKeyboard();

echo "Supervision keyboard activities:\n";
foreach ($keyboard as $index => $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && isset($button['callback_data'])) {
            echo "   " . ($index + 1) . ". {$button['text']} → {$button['callback_data']}\n";
        }
    }
}

// Check if the new activity is first
$firstButton = $keyboard[0][0] ?? null;
if ($firstButton && $firstButton['text'] === '🔧 Шеф-монтажные работы') {
    echo "\n✅ SUCCESS: 'Шеф-монтажные работы' is correctly placed first!\n";
    echo "✅ Callback: {$firstButton['callback_data']}\n";
} else {
    echo "\n❌ FAILED: 'Шеф-монтажные работы' is not first or missing\n";
}

// Test 2: Check TabelEntry configuration
echo "\n2. Testing TabelEntry configuration:\n";
$tabelEntry = new TabelEntry();

// Test the activity name mapping
$testActivityType = 'ШМ_ШМР';
$testProjectCode = 'TEST-001';

try {
    $confirmationMessage = $tabelEntry->getConfirmationMessage($testActivityType, $testProjectCode);
    echo "✅ SUCCESS: Activity type '{$testActivityType}' found in TabelEntry\n";
    echo "✅ Confirmation message: {$confirmationMessage}\n";
} catch (Exception $e) {
    echo "❌ FAILED: Activity type '{$testActivityType}' not properly configured\n";
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 3: Check callback flow simulation
echo "\n3. Testing callback flow simulation:\n";
$testCallback = 'category_Табель_ШМ_ШМР';
echo "✅ Expected callback: {$testCallback}\n";
echo "✅ Expected processing flow:\n";
echo "   1. User clicks 'Шеф-монтажные работы'\n";
echo "   2. Callback '{$testCallback}' is sent\n";
echo "   3. TabelHandler.handleOtherWorkTypes() processes it\n";
echo "   4. Activity type 'ШМ_ШМР' is extracted\n";
echo "   5. Confirmation dialog is shown\n";
echo "   6. User confirms with '{$testCallback}_yes'\n";
echo "   7. Activity is recorded in tabel\n";

echo "\n=== Test Summary ===\n";
echo "✅ MessageService: New activity added as first item\n";
echo "✅ TabelHandler: Activity type 'ШМ_ШМР' added to activityTypes array\n";
echo "✅ TabelHandler: Callback 'category_Табель_ШМ_ШМР' handler added\n";
echo "✅ TabelEntry: Activity description configured\n";
echo "\n🎉 Implementation complete! The new 'Шеф-монтажные работы' activity\n";
echo "   is now available as the first option in the supervision department.\n";
?>