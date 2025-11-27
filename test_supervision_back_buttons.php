<?php
// Test script to verify that all supervision back buttons are correctly configured

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

// Create an instance of MessageService
$messageService = new MessageService();

// Test all the keyboard methods that should have 'Табель_ШМ_Back' as the back button callback
$methodsToTest = [
    'createElectricalEquipmentKeyboard',
    'createHeatingVentilationEquipmentKeyboard',
    'createAutomationEquipmentKeyboard',
    'createFireAlarmEquipmentKeyboard',
    'createSecurityAlarmEquipmentKeyboard',
    'createTechnologicalEquipmentKeyboard',
    'createMetrologyKeyboard'
];

echo "Testing supervision department back buttons...\n\n";

foreach ($methodsToTest as $method) {
    echo "Testing {$method}...\n";
    
    // Call the method
    $keyboard = $messageService->$method();
    
    // Check the last row which should contain the back button
    $lastRow = end($keyboard);
    $backButton = $lastRow[0]; // First button in the last row
    
    echo "Back button text: {$backButton['text']}\n";
    echo "Back button callback: {$backButton['callback_data']}\n";
    
    // Verify that the callback data is correct
    if ($backButton['callback_data'] === 'Табель_ШМ_Back') {
        echo "✅ PASS: Back button correctly configured\n\n";
    } else {
        echo "❌ FAIL: Back button incorrectly configured. Expected 'Табель_ШМ_Back', got '{$backButton['callback_data']}'\n\n";
    }
}

echo "Test completed.\n";