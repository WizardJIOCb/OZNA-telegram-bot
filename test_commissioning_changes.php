<?php
// Test script to verify the commissioning works keyboard changes
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "=== Testing Commissioning Works Keyboard Changes ===\n";

// Test creating the commissioning works keyboard
$messageService = new MessageService();

// Test 1: Check createCommissioningWorksKeyboard
echo "\nTest 1: Testing createCommissioningWorksKeyboard()\n";
$keyboard1 = $messageService->createCommissioningWorksKeyboard();
echo "Number of rows: " . count($keyboard1) . "\n";

$buttonCount = 0;
foreach ($keyboard1 as $rowIndex => $row) {
    echo "Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $buttonIndex => $button) {
        $buttonCount++;
        echo "  Button " . ($buttonIndex + 1) . ": " . $button['text'] . "\n";
        echo "    Callback: " . $button['callback_data'] . "\n";
    }
}

echo "Total buttons: $buttonCount\n";

// Test 2: Check createCommissioningWorksKeyboardWithMobileActivities
echo "\nTest 2: Testing createCommissioningWorksKeyboardWithMobileActivities()\n";
$keyboard2 = $messageService->createCommissioningWorksKeyboardWithMobileActivities();
echo "Number of rows: " . count($keyboard2) . "\n";

$buttonCount = 0;
foreach ($keyboard2 as $rowIndex => $row) {
    echo "Row " . ($rowIndex + 1) . ":\n";
    foreach ($row as $buttonIndex => $button) {
        $buttonCount++;
        echo "  Button " . ($buttonIndex + 1) . ": " . $button['text'] . "\n";
        echo "    Callback: " . $button['callback_data'] . "\n";
    }
}

echo "Total buttons: $buttonCount\n";

// Verify that only "Электрооборудование" and "Назад" buttons are present
$hasElectricalEquipment = false;
$hasBackButton = false;

foreach ($keyboard2 as $row) {
    foreach ($row as $button) {
        if (isset($button['text']) && $button['text'] === '⚡ Электрооборудование') {
            $hasElectricalEquipment = true;
        }
        if (isset($button['text']) && strpos($button['text'], 'Назад') !== false) {
            $hasBackButton = true;
        }
    }
}

echo "\nVerification:\n";
echo "Has electrical equipment button: " . ($hasElectricalEquipment ? 'YES' : 'NO') . "\n";
echo "Has back button: " . ($hasBackButton ? 'YES' : 'NO') . "\n";

if ($hasElectricalEquipment && $hasBackButton && $buttonCount == 2) {
    echo "\n✅ SUCCESS: Changes applied correctly! Only 'Электрооборудование' and 'Назад' buttons are present.\n";
} else {
    echo "\n❌ FAILURE: Changes not applied correctly.\n";
}

echo "\n=== Test completed ===\n";
?>