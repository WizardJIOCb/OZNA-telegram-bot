<?php
// Test script to verify the PNL (Пусконаладочные работы) button fix
require_once __DIR__ . '/encoding_helper.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';
require_once __DIR__ . '/CustomCommands/Handlers/TabelHandler.php';

echo "=== Testing PNL Button Fix ===\n";

// Test the MessageService keyboard creation
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();

// Test creating the commissioning keyboard
echo "\n1. Testing commissioning keyboard creation:\n";
$commissioningKeyboard = $messageService->createCommissioningKeyboard();
$button = $commissioningKeyboard[0][0];
echo "Button text: " . $button['text'] . "\n";
echo "Button callback: " . $button['callback_data'] . "\n";

if ($button['callback_data'] === 'category_Табель_ПусконаладочныеРаботы') {
    echo "SUCCESS: Commissioning works button now uses unique callback!\n";
} else {
    echo "FAILURE: Commissioning works button still uses old callback\n";
}

// Test creating the electrical equipment keyboard
echo "\n2. Testing electrical equipment keyboard back button:\n";
$electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();
$backButton = $electricalKeyboard[count($electricalKeyboard)-1][0];
echo "Back button text: " . $backButton['text'] . "\n";
echo "Back button callback: " . $backButton['callback_data'] . "\n";

if ($backButton['callback_data'] === 'category_Табель_ПусконаладочныеРаботы') {
    echo "SUCCESS: Electrical equipment back button now uses correct callback!\n";
} else {
    echo "FAILURE: Electrical equipment back button still uses old callback\n";
}

// Test creating the automation equipment keyboard
echo "\n3. Testing automation equipment keyboard back button:\n";
$automationKeyboard = $messageService->createAutomationEquipmentKeyboard();
$backButton = $automationKeyboard[count($automationKeyboard)-1][0];
echo "Back button text: " . $backButton['text'] . "\n";
echo "Back button callback: " . $backButton['callback_data'] . "\n";

if ($backButton['callback_data'] === 'category_Табель_ПусконаладочныеРаботы') {
    echo "SUCCESS: Automation equipment back button now uses correct callback!\n";
} else {
    echo "FAILURE: Automation equipment back button still uses old callback\n";
}

echo "\n=== Test Complete ===\n";
echo "The callback conflict should now be resolved!\n";
?>