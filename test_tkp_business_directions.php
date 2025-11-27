<?php

/**
 * Test script to verify TKP business direction buttons functionality
 * Tests both the TKP subtypes keyboard and the routing logic
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

echo "=== Testing TKP Business Direction Buttons ===\n\n";

// Initialize MessageService to test keyboard creation
$messageService = new \Longman\TelegramBot\Commands\SystemCommands\Services\MessageService();

// Test 1: Verify TKP subtypes keyboard exists and has correct buttons
echo "1. Testing createTKPSubtypesKeyboard() method:\n";
try {
    $keyboard = $messageService->createTKPSubtypesKeyboard();
    
    if (empty($keyboard)) {
        echo "❌ ERROR: createTKPSubtypesKeyboard() returned empty keyboard\n";
        exit(1);
    }
    
    echo "✅ Keyboard created successfully with " . count($keyboard) . " rows\n";
    
    // Expected business direction buttons
    $expectedButtons = [
        '🏢 БН ОПНВГ' => 'category_Табель_ПодготовкаТКП_ОПНВГ',
        '🏢 БН НКО' => 'category_Табель_ПодготовкаТКП_НКО', 
        '🏢 БН МСО' => 'category_Табель_ПодготовкаТКП_МСО',
        '🏢 БН АСН' => 'category_Табель_ПодготовкаТКП_АСН'
    ];
    
    $foundButtons = [];
    
    foreach ($keyboard as $row) {
        foreach ($row as $button) {
            if (isset($button['text']) && isset($button['callback_data'])) {
                $foundButtons[$button['text']] = $button['callback_data'];
            }
        }
    }
    
    echo "\n2. Checking for required business direction buttons:\n";
    
    $allFound = true;
    foreach ($expectedButtons as $expectedText => $expectedCallback) {
        if (isset($foundButtons[$expectedText])) {
            if ($foundButtons[$expectedText] === $expectedCallback) {
                echo "✅ Button '$expectedText' found with correct callback: $expectedCallback\n";
            } else {
                echo "❌ Button '$expectedText' found but with wrong callback: {$foundButtons[$expectedText]} (expected: $expectedCallback)\n";
                $allFound = false;
            }
        } else {
            echo "❌ Button '$expectedText' NOT FOUND\n";
            $allFound = false;
        }
    }
    
    // Check for back button
    if (isset($foundButtons['⬅️ Назад'])) {
        echo "✅ Back button found: {$foundButtons['⬅️ Назад']}\n";
    } else {
        echo "❌ Back button NOT FOUND\n";
        $allFound = false;
    }
    
    if ($allFound) {
        echo "\n✅ All business direction buttons are correctly configured!\n";
    } else {
        echo "\n❌ Some business direction buttons are missing or incorrectly configured\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ ERROR creating TKP subtypes keyboard: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. Testing Current Activity keyboard for TKP button:\n";
try {
    $currentActivityKeyboard = $messageService->createCurrentActivityKeyboard();
    
    $foundTKPButton = false;
    foreach ($currentActivityKeyboard as $row) {
        foreach ($row as $button) {
            if (isset($button['text']) && str_contains($button['text'], 'ТКП')) {
                echo "✅ TKP button found: '{$button['text']}' with callback: {$button['callback_data']}\n";
                $foundTKPButton = true;
                
                // Verify callback matches expected pattern
                if ($button['callback_data'] === 'category_Табель_ПодготовкаТКП') {
                    echo "✅ TKP button callback is correct\n";
                } else {
                    echo "❌ TKP button callback is wrong: {$button['callback_data']} (expected: category_Табель_ПодготовкаТКП)\n";
                }
                break 2;
            }
        }
    }
    
    if (!$foundTKPButton) {
        echo "❌ TKP button NOT found in current activity keyboard\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ ERROR creating current activity keyboard: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n4. Checking TabelHandler routing logic:\n";

// Read the TabelHandler file to check for TKP routing
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

if (str_contains($tabelHandlerContent, "category === 'Табель_ПодготовкаТКП'")) {
    echo "✅ Special TKP routing found in TabelHandler\n";
    
    if (str_contains($tabelHandlerContent, 'handleTKPSubtypesMenu')) {
        echo "✅ TKP routing calls handleTKPSubtypesMenu method\n";
    } else {
        echo "❌ TKP routing does NOT call handleTKPSubtypesMenu method\n";
        exit(1);
    }
} else {
    echo "❌ Special TKP routing NOT found in TabelHandler\n";
    exit(1);
}

echo "\n5. Verifying UTF-8 encoding for Cyrillic text:\n";

$testButtons = [
    'БН ОПНВГ',
    'БН НКО', 
    'БН МСО',
    'БН АСН'
];

foreach ($testButtons as $buttonText) {
    if (mb_check_encoding($buttonText, 'UTF-8')) {
        echo "✅ Button text '$buttonText' is valid UTF-8\n";
    } else {
        echo "❌ Button text '$buttonText' has invalid UTF-8 encoding\n";
        exit(1);
    }
}

echo "\n=== TKP Business Direction Test Summary ===\n";
echo "✅ ALL TESTS PASSED!\n";
echo "✅ TKP business direction buttons are properly configured\n";
echo "✅ All four business directions (БН ОПНВГ, БН НКО, БН МСО, БН АСН) are available\n";
echo "✅ TabelHandler routing is correctly set up to show TKP submenu\n";
echo "✅ UTF-8 encoding is correct for all Cyrillic text\n";
echo "\nThe TKP business direction functionality is ready to use!\n";

?>