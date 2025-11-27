<?php
/**
 * Test script to check keyboard back button configurations
 */

echo "=== KEYBOARD BACK BUTTON TEST ===\n\n";

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

$messageService = new MessageService();

echo "1. Supervision Installation Works Keyboard:\n";
echo "=========================================\n";
$keyboard = $messageService->createSupervisionInstallationWorksKeyboard();
$backButton = end($keyboard);
echo "Back button text: " . $backButton[0]['text'] . "\n";
echo "Back button callback: " . $backButton[0]['callback_data'] . "\n";
if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
}

echo "\n2. Supervision Project Activity Keyboard:\n";
echo "========================================\n";
$keyboard = $messageService->createSupervisionProjectActivityKeyboard();
$backButton = end($keyboard);
echo "Back button text: " . $backButton[0]['text'] . "\n";
echo "Back button callback: " . $backButton[0]['callback_data'] . "\n";
if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
}

echo "\n3. Warranty Equipment Keyboard (supervision context):\n";
echo "==================================================\n";
$keyboard = $messageService->createWarrantyEquipmentKeyboard('supervision');
$backButton = end($keyboard);
echo "Back button text: " . $backButton[0]['text'] . "\n";
echo "Back button callback: " . $backButton[0]['callback_data'] . "\n";
if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
}

echo "\n4. Electrical Equipment Keyboard:\n";
echo "===============================\n";
$keyboard = $messageService->createElectricalEquipmentKeyboard();
$backButton = end($keyboard);
echo "Back button text: " . $backButton[0]['text'] . "\n";
echo "Back button callback: " . $backButton[0]['callback_data'] . "\n";
if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
}

echo "\n5. Heating and Ventilation Equipment Keyboard:\n";
echo "============================================\n";
$keyboard = $messageService->createHeatingVentilationEquipmentKeyboard();
$backButton = end($keyboard);
echo "Back button text: " . $backButton[0]['text'] . "\n";
echo "Back button callback: " . $backButton[0]['callback_data'] . "\n";
if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
}

echo "\n6. Automation Equipment Keyboard:\n";
echo "===============================\n";
$keyboard = $messageService->createAutomationEquipmentKeyboard();
$backButton = end($keyboard);
echo "Back button text: " . $backButton[0]['text'] . "\n";
echo "Back button callback: " . $backButton[0]['callback_data'] . "\n";
if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
}

echo "\n7. Fire Alarm Equipment Keyboard:\n";
echo "===============================\n";
$keyboard = $messageService->createFireAlarmEquipmentKeyboard();
$backButton = end($keyboard);
echo "Back button text: " . $backButton[0]['text'] . "\n";
echo "Back button callback: " . $backButton[0]['callback_data'] . "\n";
if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
    echo "✅ CORRECT\n";
} else {
    echo "❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
}

echo "\n8. Security Alarm Equipment Keyboard:\n";
echo "===================================\n";
// Note: This method might not exist, so we'll check if it exists first
if (method_exists($messageService, 'createSecurityAlarmEquipmentKeyboard')) {
    $keyboard = $messageService->createSecurityAlarmEquipmentKeyboard();
    $backButton = end($keyboard);
    echo "Back button text: " . $backButton[0]['text'] . "\n";
    echo "Back button callback: " . $backButton[0]['callback_data'] . "\n";
    if ($backButton[0]['callback_data'] === 'Табель_ШМ_Back') {
        echo "✅ CORRECT\n";
    } else {
        echo "❌ INCORRECT - Expected 'Табель_ШМ_Back'\n";
    }
} else {
    echo "Method createSecurityAlarmEquipmentKeyboard does not exist\n";
}

echo "\nTest completed.\n";
?>