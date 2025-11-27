<?php
/**
 * Verify that the additional works buttons have been updated correctly
 */

echo "Verifying additional works buttons...\n\n";

// Read the MessageService.php file
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Check if the updated button names exist
$buttons = [
    'Изменение схемы подключения',
    'Прокладка кабеля',
    'Подключение кабеля'
];

echo "Checking button names in MessageService.php:\n";
foreach ($buttons as $button) {
    if (strpos($messageServiceContent, $button) !== false) {
        echo "  ✓ '$button' found\n";
    } else {
        echo "  ✗ '$button' not found\n";
    }
}

// Check if the old button names are gone from the additional works electrical keyboard
$oldButtons = [
    '🔀 Изменение схем подключения',
    '🔌 Монтаж кабельных линий',
    '📊 Расключение кабельных линий'
];

echo "\nChecking that old button names are removed from additional works electrical keyboard in MessageService.php:\n";
// Look specifically in the createAdditionalWorksElectricalKeyboard method
$pattern = '/function createAdditionalWorksElectricalKeyboard\(\).*?return \[(.*?)\];/s';
if (preg_match($pattern, $messageServiceContent, $matches)) {
    $keyboardContent = $matches[1];
    foreach ($oldButtons as $button) {
        if (strpos($keyboardContent, $button) !== false) {
            echo "  ✗ '$button' still exists (should be removed)\n";
        } else {
            echo "  ✓ '$button' correctly removed\n";
        }
    }
} else {
    echo "  ? Could not find createAdditionalWorksElectricalKeyboard method\n";
}

// Read the TabelEntry.php file
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Check if the updated activity type names exist
$activityTypes = [
    'Изменение схемы подключения (Электрооборудование)',
    'Прокладка кабеля (Электрооборудование)',
    'Подключение кабеля (Электрооборудование)'
];

echo "\nChecking activity type names in TabelEntry.php:\n";
foreach ($activityTypes as $activityType) {
    if (strpos($tabelEntryContent, $activityType) !== false) {
        echo "  ✓ '$activityType' found\n";
    } else {
        echo "  ✗ '$activityType' not found\n";
    }
}

// Check confirmation message names
$confirmationMessages = [
    'Изменение схемы подключения (Электрооборудование)',
    'Прокладка кабеля (Электрооборудование)',
    'Подключение кабеля (Электрооборудование)'
];

echo "\nChecking confirmation message names in TabelEntry.php:\n";
foreach ($confirmationMessages as $message) {
    if (strpos($tabelEntryContent, $message) !== false) {
        echo "  ✓ '$message' found\n";
    } else {
        echo "  ✗ '$message' not found\n";
    }
}

echo "\nVerification completed.\n";