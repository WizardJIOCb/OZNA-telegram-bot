<?php
/**
 * Verify that the "дополнительные работы" button correctly displays the submenu
 */

echo "Verifying that the 'дополнительные работы' button correctly displays the submenu...\n\n";

// Read the TabelHandler.php file
$tabelHandlerContent = file_get_contents(__DIR__ . '/CustomCommands/Handlers/TabelHandler.php');

// Check if the handler calls handleAdditionalWorksElectricalMenu
if (strpos($tabelHandlerContent, 'handleAdditionalWorksElectricalMenu') !== false) {
    echo "  ✓ Handler correctly calls handleAdditionalWorksElectricalMenu\n";
} else {
    echo "  ✗ Handler does not call handleAdditionalWorksElectricalMenu\n";
}

// Check if the handler no longer tries to create a confirmation dialog
$confirmationPattern = '/cat_ТабЭлОб_ДопРаб.*?createConfirmationKeyboard/';
if (preg_match($confirmationPattern, $tabelHandlerContent)) {
    echo "  ✗ Handler still tries to create a confirmation dialog (should display submenu instead)\n";
} else {
    echo "  ✓ Handler no longer tries to create a confirmation dialog\n";
}

// Check if the "ДопРаб_ЭлектрОб" activity type is removed from the activityTypes array
if (strpos($tabelHandlerContent, "'ДопРаб_ЭлектрОб'") !== false && strpos($tabelHandlerContent, "// 'ДопРаб_ЭлектрОб'") === false) {
    echo "  ✗ 'ДопРаб_ЭлектрОб' still exists in activityTypes array (should be removed)\n";
} else {
    echo "  ✓ 'ДопРаб_ЭлектрОб' correctly removed from activityTypes array\n";
}

// Read the MessageService.php file
$messageServiceContent = file_get_contents(__DIR__ . '/CustomCommands/Services/MessageService.php');

// Check if the button exists in the electrical equipment keyboard
if (strpos($messageServiceContent, 'Дополнительные работы') !== false && strpos($messageServiceContent, 'cat_ТабЭлОб_ДопРаб') !== false) {
    echo "  ✓ 'Дополнительные работы' button found in electrical equipment keyboard\n";
} else {
    echo "  ✗ 'Дополнительные работы' button not found in electrical equipment keyboard\n";
}

echo "\nVerification completed.\n";