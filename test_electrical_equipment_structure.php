<?php
/**
 * Test script to verify the electrical equipment menu structure
 * Tests that the menu now shows only the required buttons
 */

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Testing electrical equipment menu structure...\n\n";

// Create MessageService instance
$messageService = new MessageService();

// Test the electrical equipment keyboard
echo "1. Testing main electrical equipment keyboard:\n";
$electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();

$expectedButtons = [
    'Работы по графику',
    'Формирование ИД',
    'Дополнительные работы',
    'Подтвердить достижение вехи',
    'Прочее',
    'Назад'
];

$foundButtons = [];
foreach ($electricalKeyboard as $row) {
    foreach ($row as $button) {
        $foundButtons[] = $button['text'];
    }
}

echo "Expected buttons: " . implode(', ', $expectedButtons) . "\n";
echo "Found buttons: " . implode(', ', $foundButtons) . "\n";

// Check if all expected buttons are present
$allPresent = true;
foreach ($expectedButtons as $expected) {
    $found = false;
    foreach ($foundButtons as $foundButton) {
        if (strpos($foundButton, $expected) !== false) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo "❌ Missing button: {$expected}\n";
        $allPresent = false;
    } else {
        echo "✅ Found button: {$expected}\n";
    }
}

// Check that removed buttons are not present
$removedButtons = [
    'Изменение схем подключения',
    'Монтаж кабельных линий',
    'Расключение кабельных линий'
];

foreach ($removedButtons as $removed) {
    $stillPresent = false;
    foreach ($foundButtons as $foundButton) {
        if (strpos($foundButton, $removed) !== false) {
            $stillPresent = true;
            break;
        }
    }
    if ($stillPresent) {
        echo "❌ Button should not be present: {$removed}\n";
        $allPresent = false;
    } else {
        echo "✅ Button correctly removed: {$removed}\n";
    }
}

echo "\n2. Testing additional works electrical keyboard:\n";
$additionalWorksKeyboard = $messageService->createAdditionalWorksElectricalKeyboard();

$expectedAdditionalButtons = [
    'Изменение схем подключения',
    'Монтаж кабельных линий',
    'Расключение кабельных линий',
    'Прочее',
    'Назад'
];

$foundAdditionalButtons = [];
foreach ($additionalWorksKeyboard as $row) {
    foreach ($row as $button) {
        $foundAdditionalButtons[] = $button['text'];
    }
}

echo "Expected additional buttons: " . implode(', ', $expectedAdditionalButtons) . "\n";
echo "Found additional buttons: " . implode(', ', $foundAdditionalButtons) . "\n";

// Check if all expected additional buttons are present
$allAdditionalPresent = true;
foreach ($expectedAdditionalButtons as $expected) {
    $found = false;
    foreach ($foundAdditionalButtons as $foundButton) {
        if (strpos($foundButton, $expected) !== false) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo "❌ Missing additional button: {$expected}\n";
        $allAdditionalPresent = false;
    } else {
        echo "✅ Found additional button: {$expected}\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
if ($allPresent && $allAdditionalPresent) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "The electrical equipment menu structure has been successfully updated.\n\n";
    echo "Summary of changes:\n";
    echo "1. Main electrical equipment menu now shows only:\n";
    echo "   - Работы по графику\n";
    echo "   - Формирование ИД\n";
    echo "   - Дополнительные работы\n";
    echo "   - Подтвердить достижение вехи\n";
    echo "   - Прочее\n";
    echo "   - Назад\n";
    echo "2. Activities moved to 'Дополнительные работы' submenu:\n";
    echo "   - Изменение схем подключения\n";
    echo "   - Монтаж кабельных линий\n";
    echo "   - Расключение кабельных линий\n";
    echo "\nNavigation flow:\n";
    echo "Electro Menu -> Дополнительные работы -> [Activities] -> Back to Electro Menu\n";
} else {
    echo "❌ SOME TESTS FAILED!\n";
    echo "Please check the implementation.\n";
}
echo str_repeat("=", 50) . "\n";