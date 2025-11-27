<?php
/**
 * Verification script for the updated electrical equipment menu structure
 * Tests that the "Прочее" button has been moved to the additional works submenu
 */

require_once __DIR__ . '/CustomCommands/Services/MessageService.php';

use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

echo "Verifying updated electrical equipment menu structure...\n\n";

// Create MessageService instance
$messageService = new MessageService();

// Test the main electrical equipment keyboard
echo "1. Testing main electrical equipment keyboard:\n";
$electricalKeyboard = $messageService->createElectricalEquipmentKeyboard();

$mainButtons = [];
foreach ($electricalKeyboard as $row) {
    foreach ($row as $button) {
        $mainButtons[] = $button['text'];
    }
}

echo "Main menu buttons: " . implode(', ', $mainButtons) . "\n";

// Check that "Прочее" is NOT in the main menu
$hasOtherInMain = false;
foreach ($mainButtons as $button) {
    if (strpos($button, 'Прочее') !== false) {
        $hasOtherInMain = true;
        break;
    }
}

if ($hasOtherInMain) {
    echo "❌ 'Прочее' button found in main menu (should be removed)\n";
} else {
    echo "✅ 'Прочее' button correctly removed from main menu\n";
}

// Test the additional works electrical keyboard
echo "\n2. Testing additional works electrical keyboard:\n";
$additionalWorksKeyboard = $messageService->createAdditionalWorksElectricalKeyboard();

$additionalButtons = [];
foreach ($additionalWorksKeyboard as $row) {
    foreach ($row as $button) {
        $additionalButtons[] = $button['text'];
    }
}

echo "Additional works buttons: " . implode(', ', $additionalButtons) . "\n";

// Check that "Прочее" IS in the additional works menu
$hasOtherInAdditional = false;
foreach ($additionalButtons as $button) {
    if (strpos($button, 'Прочее') !== false) {
        $hasOtherInAdditional = true;
        break;
    }
}

if ($hasOtherInAdditional) {
    echo "✅ 'Прочее' button correctly present in additional works menu\n";
} else {
    echo "❌ 'Прочее' button missing from additional works menu (should be added)\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
if (!$hasOtherInMain && $hasOtherInAdditional) {
    echo "🎉 VERIFICATION SUCCESSFUL!\n";
    echo "The electrical equipment menu structure has been correctly updated.\n\n";
    echo "Final structure:\n";
    echo "Main Electrical Menu:\n";
    echo "  - Работы по графику\n";
    echo "  - Формирование ИД\n";
    echo "  - Дополнительные работы →\n";
    echo "  - Подтвердить достижение вехи\n";
    echo "  - Назад\n";
    echo "\n";
    echo "Additional Works Submenu:\n";
    echo "  - Изменение схем подключения\n";
    echo "  - Монтаж кабельных линий\n";
    echo "  - Расключение кабельных линий\n";
    echo "  - Прочее\n";
    echo "  - Назад\n";
} else {
    echo "❌ VERIFICATION FAILED!\n";
    echo "Please check the implementation.\n";
}
echo str_repeat("=", 50) . "\n";