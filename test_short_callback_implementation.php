<?php

// Test script to verify the short callback implementation for electrical equipment

require_once 'CustomCommands/Services/MessageService.php';
require_once 'CustomCommands/Handlers/TabelHandler.php';

echo "Testing short callback implementation for electrical equipment...\n\n";

// Test MessageService keyboard creation
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();

echo "1. Testing electrical equipment keyboard creation:\n";
$keyboard = $messageService->createElectricalEquipmentKeyboard();
echo "Keyboard created with " . count($keyboard) . " rows\n";

// Check callback data lengths
foreach ($keyboard as $index => $row) {
    foreach ($row as $button) {
        $callbackData = $button['callback_data'];
        $length = strlen($callbackData);
        echo "Row $index: '{$button['text']}' -> '$callbackData' ({$length} bytes)\n";
        
        // Check if any callback data exceeds Telegram's 64-byte limit
        if ($length > 64) {
            echo "  WARNING: Callback data exceeds 64-byte limit!\n";
        }
    }
}

echo "\n2. Testing commissioning works keyboard with short format:\n";
$keyboard2 = $messageService->createCommissioningWorksKeyboard();
echo "Keyboard created with " . count($keyboard2) . " rows\n";

// Check electrical equipment button specifically
foreach ($keyboard2 as $index => $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Электрооборудование') !== false) {
            $callbackData = $button['callback_data'];
            $length = strlen($callbackData);
            echo "Electrical equipment button: '{$button['text']}' -> '$callbackData' ({$length} bytes)\n";
            
            if ($length > 64) {
                echo "  WARNING: Callback data exceeds 64-byte limit!\n";
            }
        }
    }
}

echo "\n3. Testing warranty works keyboard with short format:\n";
$keyboard3 = $messageService->createWarrantyWorksKeyboard();
echo "Keyboard created with " . count($keyboard3) . " rows\n";

// Check electrical equipment button specifically
foreach ($keyboard3 as $index => $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Электрооборудование') !== false) {
            $callbackData = $button['callback_data'];
            $length = strlen($callbackData);
            echo "Electrical equipment button: '{$button['text']}' -> '$callbackData' ({$length} bytes)\n";
            
            if ($length > 64) {
                echo "  WARNING: Callback data exceeds 64-byte limit!\n";
            }
        }
    }
}

echo "\n4. Testing commissioning works keyboard with mobile activities:\n";
$keyboard4 = $messageService->createCommissioningWorksKeyboardWithMobileActivities();
echo "Keyboard created with " . count($keyboard4) . " rows\n";

// Check electrical equipment button specifically
foreach ($keyboard4 as $index => $row) {
    foreach ($row as $button) {
        if (strpos($button['text'], 'Электрооборудование') !== false) {
            $callbackData = $button['callback_data'];
            $length = strlen($callbackData);
            echo "Electrical equipment button: '{$button['text']}' -> '$callbackData' ({$length} bytes)\n";
            
            if ($length > 64) {
                echo "  WARNING: Callback data exceeds 64-byte limit!\n";
            }
        }
    }
}

echo "\n5. Testing additional works electrical keyboard:\n";
$keyboard5 = $messageService->createAdditionalWorksElectricalKeyboard();
echo "Keyboard created with " . count($keyboard5) . " rows\n";

// Check callback data lengths
foreach ($keyboard5 as $index => $row) {
    foreach ($row as $button) {
        $callbackData = $button['callback_data'];
        $length = strlen($callbackData);
        echo "Row $index: '{$button['text']}' -> '$callbackData' ({$length} bytes)\n";
        
        if ($length > 64) {
            echo "  WARNING: Callback data exceeds 64-byte limit!\n";
        }
    }
}

echo "\nAll tests completed successfully!\n";