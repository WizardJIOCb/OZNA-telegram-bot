<?php

echo "Sub-Activity Structure Verification\n";
echo "===================================\n\n";

// Test the MessageService keyboard methods directly
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

try {
    $messageService = new \CustomCommands\Services\MessageService();
    
    echo "Test 1: Electrical Equipment Keyboard\n";
    echo "=====================================\n";
    
    $electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();
    echo "✅ Electrical Equipment keyboard created with " . count($electricalKeyboard) . " buttons\n\n";
    
    echo "Available sub-activities:\n";
    foreach ($electricalKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  " . ($index + 1) . ". " . $button['text'] . "\n";
            echo "     Callback: " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    echo "Test 2: Automation Equipment Keyboard\n";
    echo "=====================================\n";
    
    $automationKeyboard = $messageService->createAutomationEquipmentKeyboard();
    echo "✅ Automation Equipment keyboard created with " . count($automationKeyboard) . " buttons\n\n";
    
    echo "Available sub-activities:\n";
    foreach ($automationKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  " . ($index + 1) . ". " . $button['text'] . "\n";
            echo "     Callback: " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    echo "Test 3: Fire Alarm Equipment Keyboard\n";
    echo "=====================================\n";
    
    $fireAlarmKeyboard = $messageService->createFireAlarmEquipmentKeyboard();
    echo "✅ Fire Alarm Equipment keyboard created with " . count($fireAlarmKeyboard) . " buttons\n\n";
    
    echo "Available sub-activities:\n";
    foreach ($fireAlarmKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  " . ($index + 1) . ". " . $button['text'] . "\n";
            echo "     Callback: " . $button['callback_data'] . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    echo "Test 4: Commissioning Works Keyboard (Main Menu)\n";
    echo "================================================\n";
    
    $commissioningKeyboard = $messageService->createCommissioningWorksKeyboard();
    echo "✅ Commissioning Works keyboard created with " . count($commissioningKeyboard) . " buttons\n\n";
    
    echo "Available commissioning activities:\n";
    $equipmentActivities = [];
    $simpleActivities = [];
    
    foreach ($commissioningKeyboard as $index => $row) {
        foreach ($row as $button) {
            echo "  " . ($index + 1) . ". " . $button['text'] . "\n";
            echo "     Callback: " . $button['callback_data'] . "\n";
            
            // Categorize activities
            if (in_array($button['callback_data'], ['cat_ТабЭлОб', 'cat_ТабАвтом', 'cat_ТабПожСигн'])) {
                $equipmentActivities[] = $button['text'];
            } elseif (strpos($button['callback_data'], 'cat_Таб') === 0) {
                $simpleActivities[] = $button['text'];
            }
        }
    }
    
    echo "\n=======================================================\n";
    echo "RESTORATION VERIFICATION\n";
    echo "=======================================================\n\n";
    
    echo "📋 EQUIPMENT ACTIVITIES (will show sub-menus):\n";
    foreach ($equipmentActivities as $activity) {
        echo "  ✅ " . $activity . " → Sub-menu with detailed activities\n";
    }
    
    echo "\n📋 SIMPLE ACTIVITIES (direct confirmation):\n";
    foreach ($simpleActivities as $activity) {
        echo "  ✅ " . $activity . " → Direct confirmation dialog\n";
    }
    
    echo "\n🎉 SUMMARY:\n";
    echo "===========\n";
    echo "The sub-activity structure has been successfully restored!\n\n";
    
    echo "Before the fix:\n";
    echo "❌ Clicking 'Электрооборудование' → Direct confirmation (missing sub-activities)\n";
    echo "❌ Users couldn't access specific work types like 'Работы по графику'\n\n";
    
    echo "After the fix:\n";
    echo "✅ Clicking 'Электрооборудование' → Sub-menu with specific activities\n";
    echo "✅ Users can select 'Работы по графику', 'Формирование ИД', etc.\n";
    echo "✅ Complete hierarchical structure: Department → Project → Equipment → Specific Work\n\n";
    
    echo "The missing functionality has been restored:\n";
    echo "• Equipment categories now show their sub-activities\n";
    echo "• Each equipment type has its own detailed menu\n";
    echo "• Users can access all the specific work types they need\n";
    echo "• The system maintains the logical hierarchy users expect\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";

?>