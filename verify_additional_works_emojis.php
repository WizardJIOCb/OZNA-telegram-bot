<?php
/**
 * Verify that emojis have been added to the additional works activities
 */

echo "Verifying that emojis have been added to the additional works activities...\n\n";

// Read the MessageService.php file
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Check if the updated button names with emojis exist
$buttons = [
    '🔀 Изменение схемы подключения',
    '🔌 Прокладка кабеля',
    '📊 Подключение кабеля',
    '📝 Прочее'
];

echo "Checking button names with emojis in MessageService.php:\n";
foreach ($buttons as $button) {
    if (strpos($messageServiceContent, $button) !== false) {
        echo "  ✓ '$button' found\n";
    } else {
        echo "  ✗ '$button' not found\n";
    }
}

// Check if the "Прочее" button exists in the additional works electrical keyboard
if (strpos($messageServiceContent, '📝 Прочее') !== false && strpos($messageServiceContent, 'cat_ТабЭлОб_Проч') !== false) {
    echo "  ✓ 'Прочее' button found in additional works electrical keyboard\n";
} else {
    echo "  ✗ 'Прочее' button not found in additional works electrical keyboard\n";
}

// Read the TabelEntry.php file
$tabelEntryContent = file_get_contents(__DIR__ . '/CustomCommands/Services/TabelEntry.php');

// Check if the updated activity type names with emojis exist
$activityTypes = [
    '🔀 Изменение схемы подключения (Электрооборудование)',
    '🔌 Прокладка кабеля (Электрооборудование)',
    '📊 Подключение кабеля (Электрооборудование)',
    '📝 Прочее (Электрооборудование)'
];

echo "\nChecking activity type names with emojis in TabelEntry.php:\n";
foreach ($activityTypes as $activityType) {
    if (strpos($tabelEntryContent, $activityType) !== false) {
        echo "  ✓ '$activityType' found\n";
    } else {
        echo "  ✗ '$activityType' not found\n";
    }
}

echo "\nVerification completed.\n";