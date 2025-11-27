<?php

// Test if automation additional works can be accessed

echo "=== TESTING AUTOMATION ADDITIONAL WORKS ACCESS ===\n\n";

// Check if TabelHandler responds to the automation additional works callback
$automationAdditionalWorksCallback = 'cat_ТабАвтом_ДопРаб';

// Check what callback is used to trigger the additional works menu
require_once 'CustomCommands/Services/MessageService.php';
use Longman\TelegramBot\Commands\SystemCommands\Services\MessageService;

$messageService = new MessageService();

echo "1. Automation Equipment Main Menu:\n";
$automationKeyboard = $messageService->createAutomationEquipmentKeyboard();
foreach ($automationKeyboard as $row) {
    foreach ($row as $button) {
        echo "   Button: '{$button['text']}' -> Callback: '{$button['callback_data']}'\n";
    }
}

echo "\n2. Automation Additional Works Menu:\n";
$additionalWorksKeyboard = $messageService->createAdditionalWorksAutomationKeyboard();
foreach ($additionalWorksKeyboard as $row) {
    foreach ($row as $button) {
        echo "   Button: '{$button['text']}' -> Callback: '{$button['callback_data']}'\n";
    }
}

echo "\n3. Critical Analysis:\n";
echo "   The 'Дополнительные работы' button in automation main menu generates: 'cat_ТабАвтом_ДопРаб'\n";
echo "   The individual buttons in automation additional works menu generate: 'category_Табель_*_Автоматизация'\n";
echo "   This means clicking 'Дополнительные работы' should lead to the submenu.\n";
echo "   But if that menu isn't showing, then 'cat_ТабАвтом_ДопРаб' callback is not working.\n";

?>